<h1 class="text-2xl font-bold">Staff sign in</h1>
<?php if ($expired): ?><p class="mt-3 rounded-md bg-primary-soft p-3 text-sm" role="status">Your session ended. Please sign in again.</p><?php endif; ?>
<?php if ($error !== null): ?>
  <p class="mt-4 flex gap-2 rounded-md border-2 border-danger p-3 text-sm font-medium text-danger" role="alert"><?= icon('circle-alert') ?><?= e($error) ?></p>
<?php endif; ?>
<form method="post" action="/admin/login" class="mt-6 space-y-5">
  <?= csrf_field() ?>
  <div>
    <label for="email">Email</label>
    <input id="email" name="email" type="email" autocomplete="username" required value="<?= e($email) ?>" class="mt-1">
  </div>
  <div>
    <label for="password">Password</label>
    <input id="password" name="password" type="password" autocomplete="current-password" required class="mt-1">
  </div>
  <button type="submit" class="btn-primary w-full">Continue</button>
</form>
<p class="mt-6 text-sm text-muted">Forgot your password? Ask an Admin to reset your account.</p>
