<?php
use App\Models\Cars;

/** @var list<array<string, mixed>> $cars */
$qs = fn (array $p) => '/cars' . (($q = http_build_query(array_filter(array_merge(['status' => $status, 'body' => $body], $p)))) !== '' ? '?' . $q : '');
?>
<?= App\Core\View::fetch('partials/page-head', ['eyebrow' => 'Cars for sale', 'heading' => 'Vehicles from the USA', 'lead' => 'Quality used cars, SUVs, pickups and trucks sourced in the USA. Ask about any car and our team will reply by WhatsApp or phone.']) ?>
<section class="section pt-10">
  <div class="container-x">
    <form method="get" action="/cars" class="flex flex-wrap items-end gap-3">
      <div>
        <label for="f-status" class="text-sm">Availability</label>
        <select id="f-status" name="status" class="mt-1 min-h-10 py-2 text-sm">
          <option value="">All cars</option>
          <?php foreach (Cars::STATUSES as $k => $l): ?><option value="<?= e($k) ?>" <?= $status === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="f-body" class="text-sm">Type</label>
        <select id="f-body" name="body" class="mt-1 min-h-10 py-2 text-sm">
          <option value="">All types</option>
          <?php foreach (Cars::BODY_TYPES as $k => $l): ?><option value="<?= e($k) ?>" <?= $body === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn-secondary btn-sm">Show cars</button>
      <?php if ($status !== '' || $body !== ''): ?><a href="/cars" class="btn-ghost btn-sm">Clear</a><?php endif; ?>
    </form>

    <?php if ($cars === []): ?>
      <div class="card mt-8 p-10 text-center">
        <?= icon('car', 'icon mx-auto h-10 w-10 text-muted') ?>
        <h2 class="mt-3 text-xl font-semibold"><?= $status !== '' || $body !== '' ? 'No cars match those filters' : 'New cars are added regularly' ?></h2>
        <p class="mx-auto mt-2 max-w-md text-muted">Tell us the car you want and your budget, and we will find it in the USA for you.</p>
        <a href="/quote" class="btn-primary mt-5">Request an Import Quote</a>
      </div>
    <?php else: ?>
      <p class="mt-6 text-sm text-muted"><?= count($cars) ?> <?= count($cars) === 1 ? 'car' : 'cars' ?></p>
      <ul class="mt-3 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($cars as $car): ?><li><?= App\Core\View::fetch('partials/car-card', ['car' => $car]) ?></li><?php endforeach; ?>
      </ul>
      <div class="mt-10 rounded-md border border-line bg-surface p-5 text-sm">
        <p class="font-semibold">Not seeing the car you want?</p>
        <p class="mt-1 text-muted">We source vehicles from US auctions, dealers and private sellers. <a href="/quote">Request an Import Quote</a> and tell us what you are looking for.</p>
      </div>
    <?php endif; ?>
  </div>
</section>
