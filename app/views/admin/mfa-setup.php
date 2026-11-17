<?php
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
?>
<?php if ($codes !== null): ?>
  <h1 class="text-2xl font-bold">Save your recovery codes</h1>
  <p class="mt-2 text-muted">If you lose your phone, each code lets you sign in once. Save them somewhere safe, such as a password manager. They will not be shown again.</p>
  <textarea id="codes" readonly rows="8" class="mt-4 font-mono text-sm"><?= e(implode("\n", $codes)) ?></textarea>
  <button type="button" class="btn-secondary btn-sm mt-3" data-copy="codes">Copy codes</button>
  <a href="/admin/inbox" class="btn-primary mt-6 w-full">I have saved them, continue</a>
<?php else: ?>
  <h1 class="text-2xl font-bold">Set up your authenticator</h1>
  <p class="mt-2 text-muted">Every staff account uses a code from an authenticator app (for example Google Authenticator or Microsoft Authenticator).</p>
  <ol class="mt-4 list-decimal space-y-2 pl-5 text-sm">
    <li>In the app, add an account and scan this code.</li>
    <li>Enter the 6-digit code the app shows.</li>
  </ol>
  <div class="mt-4 flex justify-center rounded-md bg-white p-3">
    <?php
    $renderer = new ImageRenderer(new RendererStyle(220, 1), new SvgImageBackEnd());
    $svg = (new Writer($renderer))->writeString((string) $uri);
    echo preg_replace('/^<\?xml[^>]*>\s*/', '', $svg);
    ?>
  </div>
  <details class="mt-3 text-sm">
    <summary class="cursor-pointer text-primary">Cannot scan? Enter the key instead</summary>
    <p class="mt-2 break-all rounded-md bg-bg p-3 font-mono"><?= e(trim(chunk_split((string) $secret, 4, ' '))) ?></p>
  </details>
  <?php if ($error !== null): ?>
    <p class="mt-4 flex gap-2 rounded-md border-2 border-danger p-3 text-sm font-medium text-danger" role="alert"><?= icon('circle-alert') ?><?= e($error) ?></p>
  <?php endif; ?>
  <form method="post" action="/admin/mfa/setup" class="mt-6 space-y-5">
    <?= csrf_field() ?>
    <div>
      <label for="code">6-digit code</label>
      <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="7" required class="mt-1 text-center text-2xl tracking-[0.4em]">
    </div>
    <button type="submit" class="btn-primary w-full">Turn on and sign in</button>
  </form>
<?php endif; ?>
