<?php
/** @var array<string, mixed> $old @var array<string, string> $errors @var ?array<string, mixed> $shipment */
$val = fn (string $k) => e(isset($old[$k]) && is_scalar($old[$k]) ? (string) $old[$k] : '');
$action = $shipment === null ? '/admin/shipments/new' : '/admin/shipments/' . (int) $shipment['id'];
?>
<a href="<?= $shipment === null ? '/admin/shipments' : '/admin/shipments/' . (int) $shipment['id'] ?>" class="text-sm">&larr; Back</a>
<h1 class="mt-2 text-2xl font-bold"><?= e($title) ?></h1>
<?= App\Core\View::fetch('partials/error-summary', ['errors' => $errors]) ?>
<form method="post" action="<?= e($action) ?>" class="card mt-5 max-w-3xl space-y-6 p-6" novalidate>
  <?= csrf_field() ?>
  <input type="hidden" name="enquiry_id" value="<?= $val('enquiry_id') ?>">
  <fieldset class="grid gap-4 sm:grid-cols-2">
    <legend class="mb-2 text-lg font-semibold">Customer</legend>
    <div><label for="customer_name">Name</label><input id="customer_name" name="customer_name" type="text" maxlength="120" value="<?= $val('customer_name') ?>" class="mt-1"<?= field_attrs($errors, 'customer_name') ?>><?= field_error($errors, 'customer_name') ?></div>
    <div><label for="phone">Phone / WhatsApp</label><input id="phone" name="phone" type="tel" maxlength="20" value="<?= $val('phone') ?>" class="mt-1"<?= field_attrs($errors, 'phone', true) ?>><p id="phone-hint" class="hint">The last 4 digits are the customer's tracking check.</p><?= field_error($errors, 'phone') ?></div>
    <div class="sm:col-span-2"><label for="email">Email <span class="font-normal text-muted">(optional)</span></label><input id="email" name="email" type="email" maxlength="190" value="<?= $val('email') ?>" class="mt-1"<?= field_attrs($errors, 'email') ?>><?= field_error($errors, 'email') ?></div>
  </fieldset>
  <fieldset class="grid gap-4 sm:grid-cols-3">
    <legend class="mb-2 text-lg font-semibold">Vehicle</legend>
    <div><label for="vehicle_year">Year</label><input id="vehicle_year" name="vehicle_year" type="text" inputmode="numeric" maxlength="4" value="<?= $val('vehicle_year') ?>" class="mt-1"<?= field_attrs($errors, 'vehicle_year') ?>><?= field_error($errors, 'vehicle_year') ?></div>
    <div><label for="vehicle_make">Make</label><input id="vehicle_make" name="vehicle_make" type="text" maxlength="60" value="<?= $val('vehicle_make') ?>" class="mt-1"<?= field_attrs($errors, 'vehicle_make') ?>><?= field_error($errors, 'vehicle_make') ?></div>
    <div><label for="vehicle_model">Model</label><input id="vehicle_model" name="vehicle_model" type="text" maxlength="80" value="<?= $val('vehicle_model') ?>" class="mt-1"<?= field_attrs($errors, 'vehicle_model') ?>><?= field_error($errors, 'vehicle_model') ?></div>
    <div class="sm:col-span-3"><label for="vin">VIN <span class="font-normal text-muted">(when known)</span></label><input id="vin" name="vin" type="text" maxlength="17" autocapitalize="characters" value="<?= $val('vin') ?>" class="mt-1 font-mono uppercase"<?= field_attrs($errors, 'vin') ?>><?= field_error($errors, 'vin') ?></div>
  </fieldset>
  <?php if ($shipment !== null): ?>
  <fieldset class="grid gap-4 sm:grid-cols-2">
    <legend class="mb-2 text-lg font-semibold">Shipping</legend>
    <div>
      <label for="carrier_ref_type">Carrier reference type</label>
      <select id="carrier_ref_type" name="carrier_ref_type" class="mt-1"<?= field_attrs($errors, 'carrier_ref_type') ?>>
        <option value="">None yet</option>
        <?php foreach (['booking' => 'Booking number', 'bl' => 'Bill of lading', 'container' => 'Container number'] as $k => $l): ?>
          <option value="<?= e($k) ?>" <?= ($old['carrier_ref_type'] ?? '') === $k ? 'selected' : '' ?>><?= e($l) ?></option>
        <?php endforeach; ?>
      </select><?= field_error($errors, 'carrier_ref_type') ?>
    </div>
    <div><label for="carrier_ref">Carrier reference</label><input id="carrier_ref" name="carrier_ref" type="text" maxlength="40" value="<?= $val('carrier_ref') ?>" class="mt-1 font-mono uppercase"<?= field_attrs($errors, 'carrier_ref') ?>><?= field_error($errors, 'carrier_ref') ?></div>
    <div><label for="vessel">Vessel</label><input id="vessel" name="vessel" type="text" maxlength="80" value="<?= $val('vessel') ?>" class="mt-1"<?= field_attrs($errors, 'vessel') ?>><?= field_error($errors, 'vessel') ?></div>
    <div><label for="eta">Expected at Tema</label><input id="eta" name="eta" type="text" placeholder="YYYY-MM-DD" maxlength="10" value="<?= $val('eta') ?>" class="mt-1"<?= field_attrs($errors, 'eta') ?>><?= field_error($errors, 'eta') ?></div>
    <p class="text-sm text-muted sm:col-span-2">Shown to the customer while the vehicle is shipped. Filled in automatically once a carrier feed is connected.</p>
  </fieldset>
  <?php endif; ?>
  <button type="submit" class="btn-primary"><?= $shipment === null ? 'Create shipment' : 'Save details' ?></button>
</form>
