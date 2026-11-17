<?php
declare(strict_types=1);

namespace App\Core;

/** Server-side validation; the browser only assists (CTL-INP-001). */
final class Validator
{
    /** @var array<string, string> */
    public array $errors = [];

    public function required(string $field, string $value, string $label, int $max = 200): void
    {
        if ($value === '') {
            $this->errors[$field] = "Enter your {$label}.";
        } elseif (mb_strlen($value) > $max) {
            $this->errors[$field] = ucfirst($label) . " must be {$max} characters or fewer.";
        } elseif (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
            $this->errors[$field] = ucfirst($label) . ' contains characters we cannot accept.';
        }
    }

    public function optional(string $field, string $value, string $label, int $max = 2000): void
    {
        if ($value !== '' && mb_strlen($value) > $max) {
            $this->errors[$field] = ucfirst($label) . " must be {$max} characters or fewer.";
        } elseif (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $value)) {
            $this->errors[$field] = ucfirst($label) . ' contains characters we cannot accept.';
        }
    }

    public function email(string $field, string $value, bool $required = false): void
    {
        if ($value === '') {
            if ($required) {
                $this->errors[$field] = 'Enter your email address.';
            }
            return;
        }
        if (mb_strlen($value) > 190 || filter_var($value, FILTER_VALIDATE_EMAIL) === false || preg_match('/[\r\n]/', $value)) {
            $this->errors[$field] = 'Enter an email address like name@example.com.';
        }
    }

    public function phone(string $field, string $value): ?string
    {
        $normalised = self::normalisePhone($value);
        if ($value === '') {
            $this->errors[$field] = 'Enter your phone or WhatsApp number.';
        } elseif ($normalised === null) {
            $this->errors[$field] = 'Enter a phone number like 024 123 4567 or +233 24 123 4567.';
        }
        return $normalised;
    }

    public function in(string $field, string $value, array $allowed, string $message): void
    {
        if (!in_array($value, $allowed, true)) {
            $this->errors[$field] = $message;
        }
    }

    public function year(string $field, string $value): ?int
    {
        if ($value === '') {
            return null;
        }
        $max = (int) gmdate('Y') + 1;
        if (!ctype_digit($value) || (int) $value < 1950 || (int) $value > $max) {
            $this->errors[$field] = "Enter a year between 1950 and {$max}.";
            return null;
        }
        return (int) $value;
    }

    public function url(string $field, string $value): void
    {
        if ($value === '') {
            return;
        }
        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        if (mb_strlen($value) > 500 || filter_var($value, FILTER_VALIDATE_URL) === false || !in_array($scheme, ['http', 'https'], true)) {
            $this->errors[$field] = 'Enter a full web link starting with https://, or leave it empty.';
        }
    }

    public function ok(): bool
    {
        return $this->errors === [];
    }

    /**
     * Accepts Ghana numbers (024..., 24..., +233..., 00233...) and other international numbers with +.
     * Returns E.164 (+233241234567) or null.
     */
    public static function normalisePhone(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '' || preg_match('/[^\d\s()+.-]/', $raw)) {
            return null;
        }
        $plus = str_starts_with($raw, '+');
        $digits = preg_replace('/\D/', '', $raw) ?? '';
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
            $plus = true;
        }
        if (!$plus) {
            if (strlen($digits) === 10 && $digits[0] === '0') {
                $digits = '233' . substr($digits, 1);
            } elseif (strlen($digits) === 9 && $digits[0] !== '0') {
                $digits = '233' . $digits;
            } elseif (!str_starts_with($digits, '233')) {
                return null;
            }
        }
        if (str_starts_with($digits, '233') && strlen($digits) !== 12) {
            return null;
        }
        if (strlen($digits) < 9 || strlen($digits) > 15) {
            return null;
        }
        return '+' . $digits;
    }

    public static function normaliseVin(string $vin): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $vin) ?? '');
    }

    public static function isVin(string $vin): bool
    {
        return (bool) preg_match('/^[A-HJ-NPR-Z0-9]{17}$/', $vin);
    }
}
