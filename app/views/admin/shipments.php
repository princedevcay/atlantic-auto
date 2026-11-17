<?php use App\Models\Stages; ?>
<div class="flex flex-wrap items-center justify-between gap-4">
  <h1 class="text-2xl font-bold">Shipments</h1>
  <a href="/admin/shipments/new" class="btn-primary btn-sm"><?= icon('package') ?>New shipment</a>
</div>

<form method="get" action="/admin/shipments" class="mt-5 grid gap-3 sm:grid-cols-[1fr_14rem_auto] sm:items-end">
  <div>
    <label for="q" class="text-sm">Search</label>
    <input id="q" name="q" type="search" value="<?= e($q) ?>" placeholder="Reference, VIN or customer name" class="mt-1 min-h-10 py-2 text-sm">
  </div>
  <div>
    <label for="stage" class="text-sm">Stage</label>
    <select id="stage" name="stage" class="mt-1 min-h-10 py-2 text-sm">
      <option value="">All stages</option>
      <option value="active" <?= $stage === 'active' ? 'selected' : '' ?>>Not yet delivered</option>
      <?php foreach (Stages::LABELS as $k => $l): ?>
        <option value="<?= e($k) ?>" <?= $stage === $k ? 'selected' : '' ?>><?= e($l) ?> (<?= (int) ($counts[$k] ?? 0) ?>)</option>
      <?php endforeach; ?>
    </select>
  </div>
  <button type="submit" class="btn-secondary btn-sm"><?= icon('search') ?>Filter</button>
</form>

<div class="card mt-5 overflow-x-auto">
  <?php if ($rows === []): ?>
    <div class="p-10 text-center">
      <?= icon('package', 'icon mx-auto h-10 w-10 text-muted') ?>
      <p class="mt-3 font-semibold">No shipments found</p>
      <p class="text-sm text-muted">Create one, or turn a quote request into a shipment from the inbox.</p>
    </div>
  <?php else: ?>
  <table class="admin-table w-full">
    <thead><tr><th scope="col">Reference</th><th scope="col">Customer</th><th scope="col">Vehicle</th><th scope="col">Stage</th><th scope="col">Updated</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr class="hover:bg-bg">
          <td><a href="/admin/shipments/<?= (int) $r['id'] ?>" class="font-mono font-semibold tracking-wider"><?= e($r['reference']) ?></a></td>
          <td><?= e($r['customer_name']) ?></td>
          <td><?= e(trim($r['vehicle_year'] . ' ' . $r['vehicle_make'] . ' ' . $r['vehicle_model'])) ?><?php if ($r['vin']): ?><span class="block font-mono text-xs text-muted"><?= e($r['vin']) ?></span><?php endif; ?></td>
          <td><span class="chip <?= $r['stage'] === 'delivered' ? 'bg-bg text-muted' : 'bg-primary-soft text-primary' ?>"><?= e(stage_label((string) $r['stage'])) ?></span></td>
          <td class="whitespace-nowrap"><?= e(fmt_date((string) $r['updated_at'], 'j M Y')) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div>
