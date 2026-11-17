<h1 class="text-2xl font-bold">Enter your code</h1>
<p class="mt-2 text-muted">Open your authenticator app and enter the 6-digit code for Atlantic Auto.</p>
<?php if ($error !== null): ?>
  <p class="mt-4 flex gap-2 rounded-md border-2 border-danger p-3 text-sm font-medium text-danger" role="alert"><?= icon('circle-alert') ?><?= e($error) ?></p>
<?php endif; ?>
<form method="post" action="/admin/mfa" class="mt-6 space-y-5">
  <?= csrf_field() ?>
  <div>
    <label for="code">Code</label>
    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="17" required autofocus class="mt-1 text-center text-2xl tracking-[0.4em]" aria-describedby="code-hint">
    <p id="code-hint" class="hint">Lost your phone? Enter one of your recovery codes instead.</p>
  </div>
  <button type="submit" class="btn-primary w-full">Sign in</button>
</form>
