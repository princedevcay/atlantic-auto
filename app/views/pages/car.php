<?php
use App\Core\Session;
use App\Models\Cars;

/** @var array<string, mixed> $car @var list<array<string, mixed>> $photos @var array<string, mixed> $old @var array<string, string> $errors */
$title = Cars::title($car);
$isSample = (int) ($car['sample'] ?? 0) === 1;
$canEnquire = $car['status'] === 'available' && !$isSample;
$carNotice = Session::takeFlash('car_notice');
$badge = ['available' => 'bg-[#e3f3ea] text-success', 'reserved' => 'bg-[#fbefdc] text-warning', 'sold' => 'bg-navy text-white'][$car['status']] ?? '';
$wa = whatsapp_url('Hello Atlantic, I am interested in the ' . $title . ' (reference ' . $car['reference'] . ').');
$specs = array_filter([
    'Year' => (string) ($car['year'] ?? ''),
    'Make' => $car['make'],
    'Model' => trim($car['model'] . ' ' . ($car['trim_level'] ?? '')),
    'Body type' => Cars::BODY_TYPES[$car['body_type']] ?? '',
    'Mileage' => Cars::mileage($car),
    'Transmission' => Cars::TRANSMISSIONS[$car['transmission'] ?? ''] ?? '',
    'Fuel' => Cars::FUELS[$car['fuel'] ?? ''] ?? '',
    'Engine' => (string) ($car['engine'] ?? ''),
    'Colour' => (string) ($car['colour'] ?? ''),
    'Where it is now' => Cars::LOCATIONS[$car['location']] ?? '',
    'Reference' => $car['reference'],
], fn ($v) => $v !== '');
?>
<section class="section pt-8">
  <div class="container-x">
    <nav aria-label="Breadcrumb" class="text-sm"><a href="/cars">Cars for sale</a> <span class="text-muted">/</span> <span class="text-muted"><?= e($title) ?></span></nav>

    <?php if (is_string($carNotice)): ?>
      <div class="mt-4 rounded-md border-2 border-warning bg-surface p-4 text-sm font-medium text-warning" role="status"><?= e($carNotice) ?></div>
    <?php endif; ?>

    <div class="mt-5 grid gap-8 lg:grid-cols-[1.4fr_1fr]">
      <div>
        <div class="card overflow-hidden bg-white">
          <?php if ($photos !== []): $main = $photos[0]; ?>
            <img src="/cars/photo/<?= (int) $main['id'] ?>" alt="<?= e($title) ?>" width="<?= (int) $main['width'] ?>" height="<?= (int) $main['height'] ?>" class="aspect-[4/3] w-full object-contain p-4" fetchpriority="high">
          <?php else: ?>
            <div class="flex aspect-[4/3] items-center justify-center bg-primary-soft text-primary/40" aria-hidden="true"><?= icon('car', 'icon h-16 w-16') ?></div>
          <?php endif; ?>
        </div>
        <?php if (count($photos) > 1): ?>
          <ul class="mt-3 grid grid-cols-4 gap-2 sm:grid-cols-6">
            <?php foreach ($photos as $p): ?>
              <li><a href="/cars/photo/<?= (int) $p['id'] ?>" class="block overflow-hidden rounded-md border border-line bg-white hover:border-primary"><img src="/cars/photo/<?= (int) $p['id'] ?>" alt="Photo of the <?= e($title) ?>" width="<?= (int) $p['width'] ?>" height="<?= (int) $p['height'] ?>" loading="lazy" class="aspect-square w-full object-cover"></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <?php if (!empty($car['description'])): ?>
          <section class="mt-8" aria-labelledby="about-car">
            <h2 id="about-car" class="text-2xl font-bold">About this vehicle</h2>
            <p class="mt-3 whitespace-pre-line leading-relaxed text-muted"><?= e($car['description']) ?></p>
          </section>
        <?php endif; ?>
      </div>

      <div class="space-y-5">
        <div class="card p-5 sm:p-6">
          <div class="flex flex-wrap items-center gap-2">
            <span class="chip <?= e($badge) ?>"><?= e(Cars::STATUSES[$car['status']] ?? '') ?></span>
            <?php if ($isSample): ?><span class="chip border border-line text-muted">Sample listing</span><?php endif; ?>
          </div>
          <h1 class="mt-3 text-3xl font-bold"><?= e($title) ?></h1>
          <p class="mt-2 font-display text-3xl font-bold <?= $car['status'] === 'sold' ? 'text-muted line-through' : 'text-primary' ?>"><?= e(Cars::price($car)) ?></p>
          <?php if ($isSample): ?>
            <p class="mt-2 text-xs text-muted">Sample listing for demonstration. The price is a reference based on typical US dealer prices in November 2026, not an offer, and excludes shipping, duty and clearance.</p>
          <?php elseif ($car['price_amount'] !== null): ?>
            <p class="mt-2 text-xs text-muted">Ask us what the price includes for delivery in Ghana.</p>
          <?php endif; ?>

          <dl class="mt-5 divide-y divide-line border-y border-line text-sm">
            <?php foreach ($specs as $k => $v): ?>
              <div class="flex justify-between gap-4 py-2"><dt class="text-muted"><?= e($k) ?></dt><dd class="text-right font-medium <?= $k === 'Reference' ? 'font-mono' : '' ?>"><?= e($v) ?></dd></div>
            <?php endforeach; ?>
          </dl>

          <?php if ($wa !== ''): ?>
            <a href="<?= e($wa) ?>" class="btn-secondary mt-5 w-full" rel="noopener"><img src="<?= e(asset('img/brands/whatsapp.svg')) ?>" alt="" width="20" height="20" class="h-5 w-5"> Ask on WhatsApp</a>
          <?php endif; ?>
        </div>

        <?php if ($canEnquire): ?>
          <div class="card p-5 sm:p-6" id="enquire">
            <h2 class="text-xl font-bold">I&rsquo;m interested</h2>
            <p class="mt-1 text-sm text-muted">Leave your details and our team will contact you about this car.</p>
            <?= App\Core\View::fetch('partials/error-summary', ['errors' => $errors, 'notice' => $notice ?? null]) ?>
            <form method="post" action="/cars/<?= e($car['reference']) ?>/enquire" class="mt-4 space-y-4" novalidate>
              <?= csrf_field() ?>
              <input type="hidden" name="started" value="<?= e($started) ?>">
              <div class="hidden" aria-hidden="true"><label for="website">Leave this empty</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
              <div>
                <label for="name">Full name</label>
                <input id="name" name="name" type="text" autocomplete="name" maxlength="120" required value="<?= e(old($old, 'name')) ?>" class="mt-1"<?= field_attrs($errors, 'name') ?>>
                <?= field_error($errors, 'name') ?>
              </div>
              <div>
                <label for="phone">Phone or WhatsApp number</label>
                <input id="phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" maxlength="20" required value="<?= e(old($old, 'phone')) ?>" class="mt-1"<?= field_attrs($errors, 'phone') ?>>
                <?= field_error($errors, 'phone') ?>
              </div>
              <div>
                <label for="message">Message <span class="font-normal text-muted">(optional)</span></label>
                <textarea id="message" name="message" rows="3" maxlength="2000" class="mt-1"<?= field_attrs($errors, 'message') ?>><?= e(old($old, 'message')) ?></textarea>
                <?= field_error($errors, 'message') ?>
              </div>
              <button type="submit" class="btn-primary w-full" data-busy="Sending...">Send my interest</button>
              <p class="text-xs text-muted">See our <a href="/privacy">privacy notice</a>.</p>
            </form>
          </div>
        <?php else: ?>
          <div class="card p-5 sm:p-6">
            <h2 class="text-xl font-bold"><?= $isSample ? 'Looking for a car like this?' : ($car['status'] === 'sold' ? 'This car has been sold' : 'This car is reserved') ?></h2>
            <p class="mt-1 text-sm text-muted">We can find a similar vehicle for you in the USA.</p>
            <a href="/quote" class="btn-primary mt-4 w-full">Request an Import Quote</a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
