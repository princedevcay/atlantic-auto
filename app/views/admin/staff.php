<?php use App\Core\Auth; ?>
<h1 class="text-2xl font-bold">Staff</h1>

<?php if ($temp !== null): ?>
  <div class="card mt-5 border-2 border-primary p-5" role="status">
    <h2 class="text-lg font-semibold">Temporary password for <?= e($temp['name']) ?></h2>
    <p class="mt-1 text-sm text-muted">Give it to them in person or by phone, not by email. They must change it and set up their authenticator when they first sign in. It will not be shown again.</p>
    <div class="mt-3 flex gap-2"><input id="tmp" type="text" readonly value="<?= e($temp['password']) ?>" class="font-mono"><button type="button" class="btn-secondary btn-sm shrink-0" data-copy="tmp">Copy</button></div>
  </div>
<?php endif; ?>

<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_22rem]">
  <div class="card overflow-x-auto">
    <table class="admin-table w-full">
      <thead><tr><th scope="col">Name</th><th scope="col">Role</th><th scope="col">Status</th><th scope="col">Last sign in</th><th scope="col"><span class="sr-only">Actions</span></th></tr></thead>
      <tbody>
      <?php foreach ($staff as $m): $locked = $m['locked_until'] !== null && strtotime($m['locked_until'] . ' UTC') > time(); ?>
        <tr>
          <td><span class="font-semibold"><?= e($m['name']) ?></span><span class="block text-xs text-muted"><?= e($m['email']) ?></span></td>
          <td><?= e(ucfirst((string) $m['role'])) ?></td>
          <td>
            <?php if ((int) $m['active'] !== 1): ?><span class="chip bg-bg text-muted">Disabled</span>
            <?php elseif ($locked): ?><span class="chip bg-[#fbe4e1] text-danger">Locked</span>
            <?php elseif (!(int) $m['has_mfa'] || (int) $m['must_change_password']): ?><span class="chip bg-[#fbefdc] text-warning">Setup pending</span>
            <?php else: ?><span class="chip bg-primary-soft text-primary">Active</span><?php endif; ?>
          </td>
          <td class="whitespace-nowrap"><?= $m['last_login_at'] ? e(fmt_date((string) $m['last_login_at'], 'j M Y')) : 'Never' ?></td>
          <td class="whitespace-nowrap">
            <?php if ((int) $m['id'] !== Auth::id()): ?>
              <details>
                <summary class="cursor-pointer text-sm text-primary">Manage</summary>
                <div class="mt-2 space-y-2">
                  <form method="post" action="/admin/staff/<?= (int) $m['id'] ?>/reset"><?= csrf_field() ?><button type="submit" class="btn-secondary btn-sm w-full">Reset password and authenticator</button></form>
                  <?php if ((int) $m['active'] === 1): ?>
                    <form method="post" action="/admin/staff/<?= (int) $m['id'] ?>/toggle" class="space-y-2" data-confirm-word="DISABLE">
                      <?= csrf_field() ?>
                      <label class="text-xs font-normal" for="dis-<?= (int) $m['id'] ?>">Type DISABLE to confirm</label>
                      <input id="dis-<?= (int) $m['id'] ?>" name="confirm" type="text" autocomplete="off" class="min-h-10 py-2 text-sm">
                      <button type="submit" class="btn-danger btn-sm w-full">Disable account</button>
                    </form>
                  <?php else: ?>
                    <form method="post" action="/admin/staff/<?= (int) $m['id'] ?>/toggle"><?= csrf_field() ?><button type="submit" class="btn-secondary btn-sm w-full">Enable account</button></form>
                  <?php endif; ?>
                </div>
              </details>
            <?php else: ?><span class="text-xs text-muted">You</span><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <section class="card self-start p-5" aria-labelledby="add-h">
    <h2 id="add-h" class="flex items-center gap-2 text-lg font-semibold"><?= icon('user-plus', 'icon text-primary') ?>Add staff</h2>
    <?= App\Core\View::fetch('partials/error-summary', ['errors' => $errors]) ?>
    <form method="post" action="/admin/staff" class="mt-4 space-y-4" novalidate>
      <?= csrf_field() ?>
      <div><label for="name">Name</label><input id="name" name="name" type="text" maxlength="120" value="<?= e($old['name'] ?? '') ?>" class="mt-1"<?= field_attrs($errors, 'name') ?>><?= field_error($errors, 'name') ?></div>
      <div><label for="email">Email</label><input id="email" name="email" type="email" maxlength="190" value="<?= e($old['email'] ?? '') ?>" class="mt-1"<?= field_attrs($errors, 'email') ?>><?= field_error($errors, 'email') ?></div>
      <div>
        <label for="role">Role</label>
        <select id="role" name="role" class="mt-1"<?= field_attrs($errors, 'role', true) ?>>
          <option value="staff" <?= ($old['role'] ?? 'staff') === 'staff' ? 'selected' : '' ?>>Staff</option>
          <option value="admin" <?= ($old['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
        <p id="role-hint" class="hint">Staff manage requests and shipments. Admins can also manage staff and delete records.</p>
      </div>
      <button type="submit" class="btn-primary btn-sm">Create account</button>
    </form>
  </section>
</div>
