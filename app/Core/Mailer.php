<?php
declare(strict_types=1);

namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Every email goes into mail_queue first, then is sent; failures are retried by cron,
 * so a mail outage never loses a saved enquiry (CTL-ERR-001). Headers are set by PHPMailer;
 * user values never go into headers except a validated Reply-To (CTL-INP-001).
 */
final class Mailer
{
    public static function queue(string $to, string $subject, string $body, ?string $replyTo = null): int
    {
        $subject = preg_replace('/[\r\n]+/', ' ', $subject) ?? '';
        if ($replyTo !== null && (filter_var($replyTo, FILTER_VALIDATE_EMAIL) === false || preg_match('/[\r\n]/', $replyTo))) {
            $replyTo = null;
        }
        return Db::insert('mail_queue', [
            'to_addr' => $to,
            'subject' => mb_substr($subject, 0, 200),
            'body_text' => $body,
            'reply_to' => $replyTo,
            'attempts' => 0,
            'next_attempt_at' => Db::now(),
            'created_at' => Db::now(),
        ]);
    }

    /** Try to send one queued message now. Returns true on success. */
    public static function sendQueued(int $id): bool
    {
        $m = Db::one('SELECT * FROM mail_queue WHERE id = ? AND sent_at IS NULL', [$id]);
        if ($m === null) {
            return true;
        }
        try {
            self::deliver((string) $m['to_addr'], (string) $m['subject'], (string) $m['body_text'], $m['reply_to'] !== null ? (string) $m['reply_to'] : null);
            Db::run('UPDATE mail_queue SET sent_at = ?, attempts = attempts + 1, last_error = NULL WHERE id = ?', [Db::now(), $id]);
            return true;
        } catch (\Throwable $e) {
            $attempts = (int) $m['attempts'] + 1;
            $delay = min(3600, 60 * (2 ** min($attempts, 6)));
            Db::run(
                'UPDATE mail_queue SET attempts = ?, last_error = ?, next_attempt_at = ? WHERE id = ?',
                [$attempts, mb_substr($e->getMessage(), 0, 255), gmdate('Y-m-d H:i:s', time() + $delay), $id]
            );
            Log::error('mail send failed', ['mail_id' => $id, 'attempts' => $attempts]);
            return false;
        }
    }

    public static function retryDue(int $limit = 20): int
    {
        $rows = Db::all('SELECT id FROM mail_queue WHERE sent_at IS NULL AND attempts < 12 AND next_attempt_at <= ? ORDER BY id LIMIT ' . (int) $limit, [Db::now()]);
        $sent = 0;
        foreach ($rows as $r) {
            $sent += self::sendQueued((int) $r['id']) ? 1 : 0;
        }
        return $sent;
    }

    private static function deliver(string $to, string $subject, string $body, ?string $replyTo): void
    {
        if (Config::str('mail.host') === '' || Config::isLocal()) {
            // Local development: write to the log instead of sending.
            Log::info('mail (not sent in local mode)', ['to_domain' => substr(strrchr($to, '@') ?: '', 1), 'subject' => $subject]);
            if (Config::str('mail.host') === '') {
                return;
            }
        }
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = Config::str('mail.host');
        $mail->Port = (int) Config::get('mail.port', 465);
        $mail->SMTPAuth = true;
        $mail->Username = Config::str('mail.user');
        $mail->Password = Config::str('mail.pass');
        $mail->SMTPSecure = Config::str('mail.encryption', 'ssl') === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Timeout = 15;
        $mail->CharSet = 'UTF-8';
        $mail->setFrom(Config::str('mail.from'), Config::str('mail.from_name'));
        $mail->addAddress($to);
        if ($replyTo !== null) {
            $mail->addReplyTo($replyTo);
        }
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML(false);
        $mail->send();
    }
}
