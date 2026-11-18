<?php
use App\Core\Auth;
use App\Models\Stages;

/** @var array<string, mixed> $s */
$vehicle = trim($s['vehicle_year'] . ' ' . $s['vehicle_make'] . ' ' . $s['vehicle_model']);
$trackingOff = $s['link_disabled_at'] !== null;
?>
<a href="/admin/shipments" class="text-sm">&larr; All shipments</a>
<div class="mt-2 flex flex-wrap items-start justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold"><?= e($vehicle) ?></h1>
    <p class="mt-1 text-sm text-muted">Reference <span class="font-mono font-semibold tracking-wider text-ink"><?= e($s['reference']) ?></span> &middot; <?= e($s['customer_name']) ?> &middot; updated <?= e(fmt_date((string) $s['updated_at'], 'j M Y, g:ia')) ?></p>
  </div>
  <div class="flex items-center gap-2">
    <span class="chip bg-primary-soft text-primary text-sm"><?= e(stage_label((string) $s['stage'])) ?></span>
    <a href="/admin/shipments/<?= (int) $s['id'] ?>/edit" class="btn-secondary btn-sm">Edit details</a>
  </div>
</div>

<?php if ($newLink !== null && customer_tracking()): ?>
  <div class="card mt-5 border-2 border-primary p-5" role="status">
    <h2 class="flex items-center gap-2 text-lg font-semibold"><?= icon('link', 'icon text-primary') ?>Private tracking link</h2>
    <p class="mt-1 text-sm text-muted">Copy it now and send it to the customer. For security it is not stored and will not be shown again. You can always create a new one.</p>
    <div class="mt-3 flex flex-col gap-2 sm:flex-row">
      <input id="newlink" type="text" readonly value="<?= e($newLink) ?>" class="font-mono text-sm">
      <button type="button" class="btn-primary btn-sm shrink-0" data-copy="newlink"><?= icon('copy') ?>Copy link</button>
      <a class="btn-secondary btn-sm shrink-0" rel="noopener" href="<?= e('https://wa.me/' . preg_replace('/\D/', '', (string) $s['phone']) . '?text=' . rawurlencode('Hello ' . $s['customer_name'] . ', you can follow your ' . $vehicle . ' here: ' . $newLink . ' (reference ' . $s['reference'] . ')')) ?>">Send on WhatsApp</a>
    </div>
  </div>
<?php endif; ?>

<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_24rem]">
  <div class="space-y-6">
    <section class="card p-5" aria-labelledby="stage-h">
      <h2 id="stage-h" class="text-lg font-semibold">Update stage or add a note</h2>
      <form method="post" action="/admin/shipments/<?= (int) $s['id'] ?>/stage" class="mt-4 space-y-4">
        <?= csrf_field() ?>
        <div>
          <label for="stage">Stage</label>
          <select id="stage" name="stage" class="mt-1">
            <?php foreach (Stages::LABELS as $k => $l): ?>
              <option value="<?= e($k) ?>" <?= $s['stage'] === $k ? 'selected' : '' ?>><?= Stages::index($k) + 1 ?>. <?= e($l) ?><?= $s['stage'] === $k ? ' (current)' : '' ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label for="note">Note <span class="font-normal text-muted">(optional)</span></label>
          <textarea id="note" name="note" rows="3" maxlength="2000" class="mt-1" placeholder="For example: Picked up from the auction yard today."></textarea>
        </div>
        <?php if (customer_tracking()): ?><label class="flex items-center gap-3 font-normal"><input type="checkbox" name="is_public" value="1" checked> Show this note on the customer's tracking page</label><?php endif; ?>
        <button type="submit" class="btn-primary btn-sm">Save update</button>
      </form>
    </section>

    <section class="card p-5" aria-labelledby="hist-h">
      <h2 id="hist-h" class="text-lg font-semibold">History</h2>
      <ol class="mt-4 space-y-3">
        <?php foreach ($updates as $u): ?>
          <li class="rounded-md border border-line p-3 text-sm">
            <div class="flex flex-wrap items-center gap-2">
              <span class="chip bg-primary-soft text-primary"><?= e(stage_label((string) $u['stage'])) ?></span>
              <?php if (customer_tracking() && (int) $u['is_public'] === 0): ?><span class="chip border border-line text-muted">Internal</span><?php endif; ?>
              <span class="text-xs text-muted"><?= e(fmt_date((string) $u['created_at'], 'j M Y, g:ia')) ?><?= $u['staff_name'] ? ' by ' . e($u['staff_name']) : '' ?></span>
            </div>
            <?php if (!empty($u['note'])): ?><p class="mt-2 whitespace-pre-line"><?= e($u['note']) ?></p><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ol>
    </section>

    <section class="card p-5" aria-labelledby="photos-h">
      <h2 id="photos-h" class="text-lg font-semibold">Photos</h2>
      <form method="post" action="/admin/shipments/<?= (int) $s['id'] ?>/photos" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
        <?= csrf_field() ?>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= App\Core\Uploads::MAX_BYTES ?>">
        <div class="flex-1">
          <label for="photo">Add a photo</label>
          <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" required class="mt-1 block w-full text-sm" aria-describedby="photo-hint">
          <p id="photo-hint" class="hint">JPEG, PNG or WebP, up to 8 MB.</p>
        </div>
        <?php if (customer_tracking()): ?><label class="flex items-center gap-2 font-normal"><input type="checkbox" name="is_public" value="1" checked> Customer can see it</label><?php endif; ?>
        <button type="submit" class="btn-secondary btn-sm"><?= icon('upload') ?>Upload</button>
      </form>
      <?php if ($photos !== []): ?>
      <ul class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
        <?php foreach ($photos as $p): ?>
          <li class="overflow-hidden rounded-md border border-line">
            <a href="/admin/photos/<?= (int) $p['id'] ?>"><img src="/admin/photos/<?= (int) $p['id'] ?>" alt="Shipment photo" width="<?= (int) $p['width'] ?>" height="<?= (int) $p['height'] ?>" loading="lazy" class="h-32 w-full object-cover"></a>
            <div class="flex items-center justify-between p-2 text-xs text-muted">
              <span><?= customer_tracking() ? ((int) $p['is_public'] === 1 ? 'Customer can see' : 'Internal') : 'Team only' ?></span>
              <?php if (Auth::isAdmin()): ?>
                <form method="post" action="/admin/photos/<?= (int) $p['id'] ?>/delete"><?= csrf_field() ?><button type="submit" class="text-danger" aria-label="Delete photo"><?= icon('trash-2', 'icon h-4 w-4') ?></button></form>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </section>
  </div>

  <div class="space-y-6">
    <section class="card p-5" aria-labelledby="cust-h">
      <h2 id="cust-h" class="text-lg font-semibold">Customer</h2>
      <dl class="mt-3 space-y-2 text-sm">
        <div><dt class="text-muted">Name</dt><dd><?= e($s['customer_name']) ?></dd></div>
        <div><dt class="text-muted">Phone / WhatsApp</dt><dd><a href="<?= e(tel_url((string) $s['phone'])) ?>"><?= e($s['phone']) ?></a></dd></div>
        <?php if ($s['email']): ?><div><dt class="text-muted">Email</dt><dd><?= e($s['email']) ?></dd></div><?php endif; ?>
        <div><dt class="text-muted">VIN</dt><dd class="font-mono"><?= e($s['vin'] ?? 'Not set') ?></dd></div>
        <?php if ($s['carrier_ref']): ?><div><dt class="text-muted">Carrier reference</dt><dd class="font-mono"><?= e(strtoupper((string) $s['carrier_ref_type'])) ?> <?= e($s['carrier_ref']) ?></dd></div><?php endif; ?>
        <?php if ($s['vessel'] || $s['eta']): ?><div><dt class="text-muted">Vessel / ETA</dt><dd><?= e($s['vessel'] ?? '-') ?> / <?= e($s['eta'] ?? '-') ?></dd></div><?php endif; ?>
      </dl>
    </section>

    <?php if (customer_tracking()): ?>
    <section class="card p-5" aria-labelledby="link-h">
      <h2 id="link-h" class="text-lg font-semibold">Customer tracking</h2>
      <?php if ($trackingOff): ?>
        <p class="mt-2 text-sm text-warning">Tracking is switched off. Neither the link nor the reference lookup works.</p>
      <?php else: ?>
        <p class="mt-2 text-sm text-muted">The customer can open their page with their private link, or with reference <strong class="font-mono"><?= e($s['reference']) ?></strong> and the last 4 digits of their phone.</p>
      <?php endif; ?>
      <form method="post" action="/admin/shipments/<?= (int) $s['id'] ?>/link" class="mt-4">
        <?= csrf_field() ?>
        <button type="submit" class="btn-secondary btn-sm w-full"><?= icon('refresh-cw') ?><?= $trackingOff ? 'Turn on with a new link' : 'Create a new link' ?></button>
      </form>
      <?php if (!$trackingOff): ?>
        <form method="post" action="/admin/shipments/<?= (int) $s['id'] ?>/link/revoke" class="mt-2">
          <?= csrf_field() ?>
          <button type="submit" class="btn-ghost btn-sm w-full">Switch tracking off</button>
        </form>
      <?php endif; ?>
      <p class="mt-3 text-xs text-muted">A new link replaces the old one. Use it if a link was shared with the wrong person.</p>
    </section>
    <?php endif; ?>

    <?php if ($requests !== []): ?>
    <section class="card p-5" aria-labelledby="req-h">
      <h2 id="req-h" class="text-lg font-semibold">Related requests</h2>
      <ul class="mt-3 space-y-2 text-sm">
        <?php foreach ($requests as $r): ?>
          <li><a href="/admin/inbox?<?= e(http_build_query(['tab' => 'all', 'status' => 'any', 'open' => $r['id']])) ?>"><?= $r['type'] === 'quote' ? 'Import Quote' : 'Vehicle Update' ?> <?= e($r['reference']) ?></a> <span class="text-muted">&middot; <?= e(fmt_date((string) $r['created_at'])) ?></span></li>
        <?php endforeach; ?>
      </ul>
    </section>
    <?php endif; ?>

    <?php if ($events !== []): ?>
    <section class="card p-5"><h2 class="text-lg font-semibold">Carrier events</h2>
      <ul class="mt-3 space-y-2 text-sm"><?php foreach ($events as $ev): ?><li><span class="block text-xs text-muted"><?= e(fmt_date((string) $ev['event_time'], 'j M, g:ia')) ?></span><?= e($ev['description']) ?></li><?php endforeach; ?></ul>
    </section>
    <?php endif; ?>

    <?php if (Auth::isAdmin()): ?>
    <section class="card border-danger/40 p-5">
      <details>
        <summary class="cursor-pointer font-semibold text-danger">Delete this shipment</summary>
        <form method="post" action="/admin/shipments/<?= (int) $s['id'] ?>/delete" class="mt-3 space-y-2" data-confirm-word="<?= e($s['reference']) ?>">
          <?= csrf_field() ?>
          <label for="del" class="text-sm font-normal">Deletes the shipment, its history and photos. This cannot be undone. Type <strong class="font-mono"><?= e($s['reference']) ?></strong> to confirm.</label>
          <input id="del" name="confirm" type="text" autocomplete="off" class="min-h-10 py-2 font-mono uppercase">
          <button type="submit" class="btn-danger btn-sm">Delete permanently</button>
        </form>
      </details>
    </section>
    <?php endif; ?>
  </div>
</div>
