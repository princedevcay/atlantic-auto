<div class="max-w-md">
  <h1 class="text-2xl font-bold">Change your password</h1>
  <?php if ($forced): ?><p class="mt-2 rounded-md bg-primary-soft p-3 text-sm">You are using a temporary password. Choose your own before continuing.</p><?php endif; ?>
  <?php if ($error !== null): ?>
    <p class="mt-4 flex gap-2 rounded-md border-2 border-danger p-3 text-sm font-medium text-danger" role="alert"><?= icon('circle-alert') ?><?= e($error) ?></p>
  <?php endif; ?>
  <form method="post" action="/admin/account/password" class="card mt-6 space-y-5 p-6">
    <?= csrf_field() ?>
    <div>
      <label for="current_password"><?= $forced ? 'Temporary password' : 'Current password' ?></label>
      <input id="current_password" name="current_password" type="password" autocomplete="current-password" required class="mt-1">
    </div>
    <div>
      <label for="new_password">New password</label>
      <input id="new_password" name="new_password" type="password" autocomplete="new-password" minlength="12" required class="mt-1" aria-describedby="new-hint">
      <p id="new-hint" class="hint">At least 12 characters. A short sentence you will remember works well.</p>
    </div>
    <div>
      <label for="confirm_password">New password again</label>
      <input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password" required class="mt-1">
    </div>
    <button type="submit" class="btn-primary">Save password</button>
  </form>
</div>
