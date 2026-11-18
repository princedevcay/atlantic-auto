<?php
use App\Controllers\Admin\InboxController;
use App\Controllers\FormController;
use App\Core\Auth;

$tabs = ['all' => 'All', 'quotes' => 'Import Quotes', 'updates' => 'Vehicle Updates', 'cars' => 'Car Enquiries'];
$typeLabel = ['quote' => 'Import Quote', 'update' => 'Vehicle Update', 'car' => 'Car Enquiry'];
$statusChip = ['new' => 'bg-primary-soft text-primary', 'in_progress' => 'bg-[#fbefdc] text-warning', 'done' => 'bg-bg text-muted'];
$qs = fn (array $extra) => '/admin/inbox?' . http_build_query(array_merge(['tab' => $tab, 'status' => $status], $extra));
?>
<div class="flex flex-wrap items-end justify-between gap-4">
  <h1 class="text-2xl font-bold">Inbox</h1>
  <form method="get" action="/admin/inbox" class="flex items-end gap-2">
    <input type="hidden" name="tab" value="<?= e($tab) ?>">
    <div>
      <label for="status" class="text-sm">Show</label>
      <select id="status" name="status" class="mt-1 min-h-10 py-2 text-sm">
        <?php foreach (['open' => 'Open requests', 'new' => 'New', 'in_progress' => 'In progress', 'done' => 'Done', 'any' => 'Everything'] as $k => $l): ?>
          <option value="<?= e($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($l) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn-secondary btn-sm" type="submit">Apply</button>
  </form>
</div>

<nav aria-label="Inbox tabs" class="mt-5 flex gap-1 overflow-x-auto border-b border-line">
  <?php foreach ($tabs as $k => $label): ?>
    <a href="<?= e('/admin/inbox?' . http_build_query(['tab' => $k, 'status' => $status])) ?>" class="-mb-px flex shrink-0 items-center gap-2 border-b-2 px-4 py-3 text-sm font-semibold no-underline <?= $tab === $k ? 'border-primary text-primary' : 'border-transparent text-muted hover:text-ink' ?>" <?= $tab === $k ? 'aria-current="page"' : '' ?>>
      <?= e($label) ?>
      <?php if ($counts[$k] > 0): ?><span class="chip bg-primary text-white" aria-label="<?= (int) $counts[$k] ?> new"><?= (int) $counts[$k] ?></span><?php endif; ?>
    </a>
  <?php endforeach; ?>
</nav>

<div class="mt-4 grid gap-6 <?= $open !== null ? 'xl:grid-cols-[1fr_28rem]' : '' ?>">
  <div class="card overflow-x-auto">
    <?php if ($rows === []): ?>
      <div class="p-10 text-center">
        <?= icon('inbox', 'icon mx-auto h-10 w-10 text-muted') ?>
        <p class="mt-3 font-semibold">No requests here</p>
        <p class="text-sm text-muted">New quote and update requests appear here as they arrive.</p>
      </div>
    <?php else: ?>
    <table class="admin-table w-full">
      <thead><tr><th scope="col">Type</th><th scope="col">Name</th><th scope="col">Vehicle or reference</th><th scope="col">Received</th><th scope="col">Status</th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="<?= $open !== null && (int) $open['id'] === (int) $r['id'] ? 'bg-primary-soft' : 'hover:bg-bg' ?>">
            <td><span class="chip <?= ['quote' => 'bg-navy text-white', 'car' => 'bg-primary text-white'][$r['type']] ?? 'border border-line text-ink' ?>"><?= e($typeLabel[$r['type']] ?? $r['type']) ?></span></td>
            <td><a href="<?= e($qs(['open' => $r['id']])) ?>" class="font-semibold"><?= e($r['name']) ?></a><span class="block text-xs text-muted"><?= e($r['reference']) ?></span></td>
            <td><?= $r['type'] !== 'update' ? e(trim($r['vehicle_year'] . ' ' . $r['vehicle_make'] . ' ' . $r['vehicle_model'])) : '<span class="font-mono text-xs">' . e($r['vin_or_reference']) . '</span>' ?></td>
            <td class="whitespace-nowrap"><?= e(fmt_date((string) $r['created_at'], 'j M, g:ia')) ?></td>
            <td><span class="chip <?= $statusChip[$r['status']] ?? '' ?>"><?= e(InboxController::STATUSES[$r['status']] ?? $r['status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <?php if ($open !== null): ?>
  <aside class="card self-start p-5" aria-labelledby="detail-h">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-xs font-semibold uppercase tracking-wide text-muted"><?= e($typeLabel[$open['type']] ?? $open['type']) ?> &middot; <?= e($open['reference']) ?></p>
        <h2 id="detail-h" class="mt-1 text-xl font-bold"><?= e($open['name']) ?></h2>
      </div>
      <a href="<?= e($qs([])) ?>" class="btn-ghost btn-sm px-2" aria-label="Close details"><?= icon('x') ?></a>
    </div>
    <dl class="mt-4 space-y-3 text-sm">
      <div><dt class="text-muted">Phone / WhatsApp</dt><dd class="font-medium"><a href="<?= e(tel_url((string) $open['phone'])) ?>"><?= e($open['phone']) ?></a></dd></div>
      <?php if (!empty($open['email'])): ?><div><dt class="text-muted">Email</dt><dd class="font-medium"><a href="mailto:<?= e($open['email']) ?>"><?= e($open['email']) ?></a></dd></div><?php endif; ?>
      <?php if ($open['type'] === 'car'): ?>
        <div><dt class="text-muted">Car</dt><dd class="font-medium"><?= e(trim($open['vehicle_year'] . ' ' . $open['vehicle_make'] . ' ' . $open['vehicle_model'])) ?></dd></div>
        <div><dt class="text-muted">Listing</dt><dd><?= $open['car_reference'] ? '<a href="/admin/cars/' . (int) $open['car_id'] . '" class="font-mono">' . e($open['car_reference']) . '</a> &middot; ' . e(App\Models\Cars::STATUSES[$open['car_status']] ?? '') : 'Deleted' ?></dd></div>
      <?php elseif ($open['type'] === 'quote'): ?>
        <div><dt class="text-muted">Vehicle</dt><dd class="font-medium"><?= e(trim($open['vehicle_year'] . ' ' . $open['vehicle_make'] . ' ' . $open['vehicle_model'])) ?></dd></div>
        <div><dt class="text-muted">Where it is</dt><dd><?= e(FormController::SOURCES[$open['source']] ?? $open['source']) ?></dd></div>
        <?php if (!empty($open['listing_url'])): ?><div><dt class="text-muted">Listing</dt><dd class="break-all"><?= e($open['listing_url']) ?></dd></div><?php endif; ?>
        <div><dt class="text-muted">Services</dt><dd><?= e(implode(', ', array_map(fn ($s) => FormController::SERVICES[$s] ?? $s, array_filter(explode(',', (string) $open['services']))))) ?></dd></div>
      <?php else: ?>
        <div><dt class="text-muted">VIN or reference</dt><dd class="font-mono"><?= e($open['vin_or_reference']) ?></dd></div>
        <div><dt class="text-muted">Matched shipment</dt><dd><?= $open['shipment_reference'] ? '<a href="/admin/shipments/' . (int) $open['shipment_id'] . '">' . e($open['shipment_reference']) . '</a>' : 'None found' ?></dd></div>
      <?php endif; ?>
      <?php if (!empty($open['message'])): ?><div><dt class="text-muted"><?= ['quote' => 'Notes', 'car' => 'Message'][$open['type']] ?? 'Question' ?></dt><dd class="whitespace-pre-line rounded-md bg-bg p-3"><?= e($open['message']) ?></dd></div><?php endif; ?>
      <div><dt class="text-muted">Received</dt><dd><?= e(fmt_date((string) $open['created_at'], 'j M Y, g:ia')) ?></dd></div>
    </dl>

    <?php $wa = 'https://wa.me/' . preg_replace('/\D/', '', (string) $open['phone']); ?>
    <a href="<?= e($wa) ?>" class="btn-primary btn-sm mt-5 w-full" rel="noopener"><img src="<?= e(asset('img/brands/whatsapp.svg')) ?>" alt="" width="18" height="18" class="h-4 w-4 invert"> Reply on WhatsApp</a>

    <form method="post" action="/admin/enquiries/<?= (int) $open['id'] ?>/status" class="mt-4 flex items-end gap-2">
      <?= csrf_field() ?><input type="hidden" name="tab" value="<?= e($tab) ?>">
      <div class="flex-1">
        <label for="st" class="text-sm">Status</label>
        <select id="st" name="status" class="mt-1 min-h-10 py-2 text-sm">
          <?php foreach (InboxController::STATUSES as $k => $l): ?><option value="<?= e($k) ?>" <?= $open['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn-secondary btn-sm">Save</button>
    </form>

    <?php if ($open['type'] === 'quote' && $open['shipment_id'] === null): ?>
      <form method="post" action="/admin/enquiries/<?= (int) $open['id'] ?>/convert" class="mt-3">
        <?= csrf_field() ?><input type="hidden" name="tab" value="<?= e($tab) ?>">
        <button type="submit" class="btn-ghost btn-sm w-full border border-line"><?= icon('package') ?>Create shipment from this request</button>
      </form>
    <?php elseif ($open['type'] === 'quote'): ?>
      <a href="/admin/shipments/<?= (int) $open['shipment_id'] ?>" class="btn-ghost btn-sm mt-3 w-full border border-line">Open shipment</a>
    <?php endif; ?>

    <?php if (Auth::isAdmin()): ?>
      <details class="mt-5 border-t border-line pt-4">
        <summary class="cursor-pointer text-sm font-semibold text-danger">Delete this request</summary>
        <form method="post" action="/admin/enquiries/<?= (int) $open['id'] ?>/delete" class="mt-3 space-y-2" data-confirm-word="DELETE">
          <?= csrf_field() ?><input type="hidden" name="tab" value="<?= e($tab) ?>">
          <label for="del-confirm" class="text-sm font-normal">This cannot be undone. Type DELETE to confirm.</label>
          <input id="del-confirm" name="confirm" type="text" autocomplete="off" class="min-h-10 py-2">
          <button type="submit" class="btn-danger btn-sm">Delete permanently</button>
        </form>
      </details>
    <?php endif; ?>
  </aside>
  <?php endif; ?>
</div>
