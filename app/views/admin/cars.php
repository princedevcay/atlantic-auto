<?php use App\Models\Cars; ?>
<div class="flex flex-wrap items-center justify-between gap-4">
  <h1 class="text-2xl font-bold">Cars for sale</h1>
  <a href="/admin/cars/new" class="btn-primary btn-sm"><?= icon('car') ?>Add a car</a>
</div>

<form method="get" action="/admin/cars" class="mt-5 flex flex-wrap items-end gap-3">
  <div>
    <label for="status" class="text-sm">Status</label>
    <select id="status" name="status" class="mt-1 min-h-10 py-2 text-sm">
      <option value="">All</option>
      <?php foreach (Cars::STATUSES as $k => $l): ?><option value="<?= e($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
    </select>
  </div>
  <div>
    <label for="vis" class="text-sm">On the website</label>
    <select id="vis" name="vis" class="mt-1 min-h-10 py-2 text-sm">
      <option value="">All</option>
      <option value="published" <?= $vis === 'published' ? 'selected' : '' ?>>Published</option>
      <option value="hidden" <?= $vis === 'hidden' ? 'selected' : '' ?>>Hidden</option>
    </select>
  </div>
  <button type="submit" class="btn-secondary btn-sm">Filter</button>
</form>

<div class="card mt-5 overflow-x-auto">
  <?php if ($rows === []): ?>
    <div class="p-10 text-center">
      <?= icon('car', 'icon mx-auto h-10 w-10 text-muted') ?>
      <p class="mt-3 font-semibold">No cars yet</p>
      <p class="text-sm text-muted">Add a car, upload photos and publish it to show it on the website.</p>
    </div>
  <?php else: ?>
  <table class="admin-table w-full">
    <thead><tr><th scope="col">Car</th><th scope="col">Price</th><th scope="col">Status</th><th scope="col">Website</th><th scope="col"><span class="sr-only">Quick actions</span></th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr class="hover:bg-bg">
          <td>
            <a href="/admin/cars/<?= (int) $r['id'] ?>" class="flex items-center gap-3 no-underline">
              <span class="flex h-12 w-16 shrink-0 items-center justify-center overflow-hidden rounded-sm border border-line bg-white">
                <?php if ($r['photo_id']): ?><img src="/admin/car-photos/<?= (int) $r['photo_id'] ?>" alt="" class="h-full w-full object-contain" loading="lazy"><?php else: ?><?= icon('image', 'icon text-muted') ?><?php endif; ?>
              </span>
              <span><span class="block font-semibold text-ink"><?= e(Cars::title($r)) ?></span><span class="block font-mono text-xs text-muted"><?= e($r['reference']) ?><?= (int) $r['sample'] === 1 ? ' · sample' : '' ?><?= (int) $r['new_enquiries'] > 0 ? ' · ' . (int) $r['new_enquiries'] . ' new enquiries' : '' ?></span></span>
            </a>
          </td>
          <td class="whitespace-nowrap"><?= e(Cars::price($r)) ?></td>
          <td>
            <form method="post" action="/admin/cars/<?= (int) $r['id'] ?>/status" class="flex items-center gap-1">
              <?= csrf_field() ?><input type="hidden" name="back" value="list">
              <label for="st-<?= (int) $r['id'] ?>" class="sr-only">Status</label>
              <select id="st-<?= (int) $r['id'] ?>" name="status" class="min-h-9 w-32 py-1 text-sm">
                <?php foreach (Cars::STATUSES as $k => $l): ?><option value="<?= e($k) ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
              </select>
              <button type="submit" class="btn-ghost btn-sm px-2">Save</button>
            </form>
          </td>
          <td>
            <?php if ((int) $r['published'] === 1): ?><span class="chip bg-[#e3f3ea] text-success">Published</span><?php else: ?><span class="chip bg-bg text-muted">Hidden</span><?php endif; ?>
            <?php if ((int) $r['featured'] === 1): ?><span class="chip ml-1 bg-primary-soft text-primary">Featured</span><?php endif; ?>
          </td>
          <td class="whitespace-nowrap">
            <form method="post" action="/admin/cars/<?= (int) $r['id'] ?>/publish" class="inline"><?= csrf_field() ?><input type="hidden" name="back" value="list"><button type="submit" class="btn-ghost btn-sm"><?= (int) $r['published'] === 1 ? 'Hide' : 'Publish' ?></button></form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
