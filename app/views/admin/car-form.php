<?php
use App\Core\Auth;
use App\Models\Cars;

/** @var ?array<string, mixed> $car @var array<string, mixed> $old @var array<string, string> $errors @var list<array<string, mixed>> $photos */
$val = fn (string $k) => e(isset($old[$k]) && is_scalar($old[$k]) ? (string) $old[$k] : '');
$sel = fn (string $k, string $v) => (string) ($old[$k] ?? '') === $v ? 'selected' : '';
$opts = function (string $k, array $list, bool $blank = false) use ($sel): string {
    $h = $blank ? '<option value="">Not set</option>' : '';
    foreach ($list as $v => $l) {
        $h .= '<option value="' . e($v) . '" ' . $sel($k, (string) $v) . '>' . e($l) . '</option>';
    }
    return $h;
};
$action = $car === null ? '/admin/cars/new' : '/admin/cars/' . (int) $car['id'];
$priceOnRequest = ($old['price_on_request'] ?? '') === '1';
?>
<a href="/admin/cars" class="text-sm">&larr; All cars</a>
<div class="mt-2 flex flex-wrap items-start justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold"><?= e($title) ?></h1>
    <?php if ($car !== null): ?>
      <p class="mt-1 text-sm text-muted">Reference <span class="font-mono font-semibold text-ink"><?= e($car['reference']) ?></span>
        &middot; <?= (int) $car['published'] === 1 ? '<span class="text-success">Published</span>' : 'Hidden' ?>
        <?= (int) $car['sample'] === 1 ? '&middot; Sample listing' : '' ?></p>
    <?php endif; ?>
  </div>
  <?php if ($car !== null && (int) $car['published'] === 1): ?>
    <a href="/cars/<?= e($car['reference']) ?>" class="btn-secondary btn-sm" target="_blank" rel="noopener">View on website</a>
  <?php endif; ?>
</div>

<div class="mt-6 grid gap-6 xl:grid-cols-[1fr_22rem]">
  <div>
    <?= App\Core\View::fetch('partials/error-summary', ['errors' => $errors]) ?>
    <form method="post" action="<?= e($action) ?>" class="card space-y-6 p-6" novalidate>
      <?= csrf_field() ?>
      <fieldset class="grid gap-4 sm:grid-cols-3">
        <legend class="mb-2 text-lg font-semibold">Vehicle</legend>
        <div><label for="year">Year</label><input id="year" name="year" type="text" inputmode="numeric" maxlength="4" value="<?= $val('year') ?>" class="mt-1"<?= field_attrs($errors, 'year') ?>><?= field_error($errors, 'year') ?></div>
        <div><label for="make">Make</label><input id="make" name="make" type="text" maxlength="60" value="<?= $val('make') ?>" class="mt-1"<?= field_attrs($errors, 'make') ?>><?= field_error($errors, 'make') ?></div>
        <div><label for="model">Model</label><input id="model" name="model" type="text" maxlength="80" value="<?= $val('model') ?>" class="mt-1"<?= field_attrs($errors, 'model') ?>><?= field_error($errors, 'model') ?></div>
        <div><label for="trim_level">Trim <span class="font-normal text-muted">(optional)</span></label><input id="trim_level" name="trim_level" type="text" maxlength="60" value="<?= $val('trim_level') ?>" class="mt-1"<?= field_attrs($errors, 'trim_level') ?>><?= field_error($errors, 'trim_level') ?></div>
        <div><label for="body_type">Body type</label><select id="body_type" name="body_type" class="mt-1"<?= field_attrs($errors, 'body_type') ?>><option value="">Choose</option><?= $opts('body_type', Cars::BODY_TYPES) ?></select><?= field_error($errors, 'body_type') ?></div>
        <div><label for="colour">Colour</label><input id="colour" name="colour" type="text" maxlength="40" value="<?= $val('colour') ?>" class="mt-1"<?= field_attrs($errors, 'colour') ?>><?= field_error($errors, 'colour') ?></div>
        <div><label for="mileage">Mileage</label><input id="mileage" name="mileage" type="text" inputmode="numeric" maxlength="9" value="<?= $val('mileage') ?>" class="mt-1"<?= field_attrs($errors, 'mileage') ?>><?= field_error($errors, 'mileage') ?></div>
        <div><label for="mileage_unit">Unit</label><select id="mileage_unit" name="mileage_unit" class="mt-1"><?= $opts('mileage_unit', ['mi' => 'Miles', 'km' => 'Kilometres']) ?></select></div>
        <div><label for="transmission">Transmission</label><select id="transmission" name="transmission" class="mt-1"<?= field_attrs($errors, 'transmission') ?>><?= $opts('transmission', Cars::TRANSMISSIONS, true) ?></select><?= field_error($errors, 'transmission') ?></div>
        <div><label for="fuel">Fuel</label><select id="fuel" name="fuel" class="mt-1"<?= field_attrs($errors, 'fuel') ?>><?= $opts('fuel', Cars::FUELS, true) ?></select><?= field_error($errors, 'fuel') ?></div>
        <div class="sm:col-span-2"><label for="engine">Engine</label><input id="engine" name="engine" type="text" maxlength="40" placeholder="For example 2.5L 4-cylinder" value="<?= $val('engine') ?>" class="mt-1"<?= field_attrs($errors, 'engine') ?>><?= field_error($errors, 'engine') ?></div>
        <div class="sm:col-span-2"><label for="vin">VIN <span class="font-normal text-muted">(staff only, not shown on the website)</span></label><input id="vin" name="vin" type="text" maxlength="17" value="<?= $val('vin') ?>" class="mt-1 font-mono uppercase"<?= field_attrs($errors, 'vin') ?>><?= field_error($errors, 'vin') ?></div>
        <div><label for="location">Where it is now</label><select id="location" name="location" class="mt-1"<?= field_attrs($errors, 'location') ?>><?= $opts('location', Cars::LOCATIONS) ?></select><?= field_error($errors, 'location') ?></div>
      </fieldset>

      <fieldset class="grid gap-4 sm:grid-cols-3">
        <legend class="mb-2 text-lg font-semibold">Price and status</legend>
        <div><label for="currency">Currency</label><select id="currency" name="currency" class="mt-1"<?= field_attrs($errors, 'currency') ?>><?= $opts('currency', array_combine(Cars::CURRENCIES, Cars::CURRENCIES)) ?></select><?= field_error($errors, 'currency') ?></div>
        <div><label for="price_amount">Price</label><input id="price_amount" name="price_amount" type="text" inputmode="decimal" maxlength="14" placeholder="185000" value="<?= e($priceOnRequest ? '' : (string) ($old['price_amount'] ?? '')) ?>" class="mt-1"<?= field_attrs($errors, 'price_amount') ?>><?= field_error($errors, 'price_amount') ?></div>
        <div><label for="status">Status</label><select id="status" name="status" class="mt-1"<?= field_attrs($errors, 'status') ?>><?= $opts('status', Cars::STATUSES) ?></select><?= field_error($errors, 'status') ?></div>
        <label class="flex items-center gap-3 font-normal sm:col-span-3"><input type="checkbox" name="price_on_request" value="1" <?= $priceOnRequest ? 'checked' : '' ?>> Show "Price on request" instead of a price</label>
      </fieldset>

      <div>
        <label for="description">Description</label>
        <textarea id="description" name="description" rows="6" maxlength="5000" class="mt-1"<?= field_attrs($errors, 'description', true) ?>><?= $val('description') ?></textarea>
        <p id="description-hint" class="hint">Condition, features, history. Shown on the car's page.</p>
        <?= field_error($errors, 'description') ?>
      </div>
      <button type="submit" class="btn-primary"><?= $car === null ? 'Save car (hidden)' : 'Save details' ?></button>
    </form>
  </div>

  <?php if ($car !== null): ?>
  <div class="space-y-5">
    <section class="card p-5" aria-labelledby="pub-h">
      <h2 id="pub-h" class="text-lg font-semibold">On the website</h2>
      <p class="mt-1 text-sm text-muted"><?= (int) $car['published'] === 1 ? 'Published: visible on Cars for Sale.' : 'Hidden: not on the website. Add photos, then publish.' ?></p>
      <form method="post" action="/admin/cars/<?= (int) $car['id'] ?>/publish" class="mt-3"><?= csrf_field() ?>
        <button type="submit" class="<?= (int) $car['published'] === 1 ? 'btn-secondary' : 'btn-primary' ?> btn-sm w-full"><?= (int) $car['published'] === 1 ? 'Hide from website' : 'Publish on website' ?></button>
      </form>
      <form method="post" action="/admin/cars/<?= (int) $car['id'] ?>/featured" class="mt-2"><?= csrf_field() ?>
        <button type="submit" class="btn-ghost btn-sm w-full border border-line"><?= (int) $car['featured'] === 1 ? 'Remove from featured' : 'Feature on the home page' ?></button>
      </form>
      <p class="mt-2 text-xs text-muted">Featured cars show on the home page while published and available.</p>
    </section>

    <section class="card p-5" aria-labelledby="photos-h">
      <h2 id="photos-h" class="text-lg font-semibold">Photos <span class="text-sm font-normal text-muted">(<?= count($photos) ?> of <?= Cars::MAX_PHOTOS ?>)</span></h2>
      <?php if (count($photos) < Cars::MAX_PHOTOS): ?>
      <form method="post" action="/admin/cars/<?= (int) $car['id'] ?>/photos" enctype="multipart/form-data" class="mt-3 space-y-2">
        <?= csrf_field() ?>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= App\Core\Uploads::MAX_BYTES ?>">
        <label for="photo" class="text-sm">Add a photo</label>
        <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" required class="block w-full text-sm" aria-describedby="photo-hint">
        <p id="photo-hint" class="hint">JPEG, PNG or WebP, up to 8 MB.</p>
        <button type="submit" class="btn-secondary btn-sm"><?= icon('upload') ?>Upload</button>
      </form>
      <?php endif; ?>
      <?php if ($photos !== []): ?>
      <ul class="mt-4 grid grid-cols-2 gap-2">
        <?php foreach ($photos as $p): ?>
          <li class="overflow-hidden rounded-md border <?= (int) $p['is_main'] === 1 ? 'border-primary' : 'border-line' ?>">
            <img src="/admin/car-photos/<?= (int) $p['id'] ?>" alt="Car photo" width="<?= (int) $p['width'] ?>" height="<?= (int) $p['height'] ?>" loading="lazy" class="aspect-[4/3] w-full bg-white object-contain">
            <div class="flex items-center justify-between gap-1 p-1.5 text-xs">
              <?php if ((int) $p['is_main'] === 1): ?><span class="font-semibold text-primary">Main photo</span>
              <?php else: ?><form method="post" action="/admin/car-photos/<?= (int) $p['id'] ?>/main"><?= csrf_field() ?><button type="submit" class="text-primary">Make main</button></form><?php endif; ?>
              <form method="post" action="/admin/car-photos/<?= (int) $p['id'] ?>/delete"><?= csrf_field() ?><button type="submit" class="text-danger" aria-label="Delete photo"><?= icon('trash-2', 'icon h-4 w-4') ?></button></form>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </section>

    <?php if (Auth::isAdmin()): ?>
    <section class="card p-5">
      <details>
        <summary class="cursor-pointer font-semibold text-danger">Delete this car</summary>
        <form method="post" action="/admin/cars/<?= (int) $car['id'] ?>/delete" class="mt-3 space-y-2" data-confirm-word="<?= e($car['reference']) ?>">
          <?= csrf_field() ?>
          <label for="del" class="text-sm font-normal">Deletes the listing and its photos. This cannot be undone. Type <strong class="font-mono"><?= e($car['reference']) ?></strong> to confirm.</label>
          <input id="del" name="confirm" type="text" autocomplete="off" class="min-h-10 py-2 font-mono uppercase">
          <button type="submit" class="btn-danger btn-sm">Delete permanently</button>
        </form>
      </details>
    </section>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
