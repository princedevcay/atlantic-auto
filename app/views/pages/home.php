<?php
use App\Models\Stages;

// Photo slots: client-supplied files in public/assets/img/site/ (PG-014). Missing files show a pale panel.
$hero = site_image('hero');
$heroMobile = site_image('hero-mobile');
$photo = function (string $slot, string $alt, string $class, string $fallbackIcon = 'image', string $fit = 'object-cover'): string {
    $src = site_image($slot);
    if ($src !== '') {
        return '<img src="' . e($src) . '" alt="' . e($alt) . '" class="' . e($class) . ' ' . e($fit) . '" loading="lazy">';
    }
    return '<div class="' . e($class) . ' flex items-center justify-center bg-primary-soft text-primary/40" aria-hidden="true">' . icon($fallbackIcon, 'icon h-10 w-10') . '</div>';
};
$services = [
    ['sourcing', 'search', 'Sourcing', 'We help you find the right vehicle in the US that fits your budget and needs.'],
    ['purchase', 'shopping-cart', 'Purchase', 'We guide you through the buying process and handle the purchase for you.'],
    ['pickup', 'truck', 'Pickup', 'We arrange safe pickup from the seller, dealer or auction location in the US.'],
    ['shipping', 'ship', 'Shipping', 'We ship your vehicle from the US to Ghana on a reliable ocean service.'],
    ['clearance', 'clipboard-list', 'Clearance', 'We handle import documentation, customs clearance and port formalities in Ghana.'],
    ['delivery', 'car-front', 'Delivery', 'We deliver your cleared vehicle to you at your preferred location in Ghana.'],
];
$stageIcons = ['sourcing' => 'search', 'purchased' => 'circle-check', 'picked_up' => 'truck', 'shipped' => 'ship', 'clearing' => 'clipboard-list', 'delivered' => 'car-front'];
// Car brands shown at the owner's request (PG-028); logo sources in brand/LOGOS.md.
$types = [
    ['saloon', 'car', 'Saloon cars', 'Toyota Camry', 'toyota', 'Toyota'],
    ['suv', 'car-front', 'SUVs', 'Honda CR-V', 'honda', 'Honda'],
    ['pickup', 'truck', 'Pickups', 'Ford Ranger', 'ford', 'Ford'],
    ['van', 'bus', 'Vans', 'Mercedes-Benz Sprinter', 'mercedes-benz', 'Mercedes-Benz'],
    ['truck', 'truck', 'Trucks', 'Isuzu NPR', 'isuzu', 'Isuzu'],
];
$faqs = [
    ['Can you help me find a vehicle in the US?', 'Yes. We sell vehicles sourced from the US. Tell us the make, model, year range and budget in the quote form and we share suitable options with you before anything is bought.'],
    ['How long does shipping to Ghana take?', 'It depends on where the vehicle is in the US, the shipping schedule and clearing at Tema. We give you an estimate with your quote, and we tell you the expected arrival once the vehicle is on a ship.'],
    ['Will you handle customs clearance in Ghana?', 'Yes. We handle the import documentation, customs clearance and port formalities at Tema, and tell you if we need anything from you.'],
    ['How will I know the status of my vehicle?', 'Our team tracks your vehicle at every stage, from purchase to delivery, and keeps you updated by WhatsApp or phone. You can also send a Vehicle Update request at any time.'],
    ['Can you deliver to my location in Ghana?', 'Yes. Tell us where you want the vehicle in your quote request and we will include delivery.'],
];
?>
<!-- 1. Hero -->
<section class="on-navy relative overflow-hidden bg-navy text-white">
  <?php if ($hero !== ''): ?>
    <picture>
      <?php if ($heroMobile !== ''): ?><source media="(max-width: 767px)" srcset="<?= e($heroMobile) ?>"><?php endif; ?>
      <img src="<?= e($hero) ?>" alt="" class="absolute inset-0 h-full w-full object-cover object-right" fetchpriority="high">
    </picture>
    <div class="absolute inset-0 bg-gradient-to-r from-navy/95 via-navy/75 to-navy/10 md:from-navy/90 md:via-navy/50 md:to-transparent" aria-hidden="true"></div>
  <?php endif; ?>
  <div class="container-x relative pt-14 sm:pt-20 <?= customer_tracking() ? 'pb-24 sm:pb-28 lg:pb-32' : 'pb-14 sm:pb-20 lg:pb-24' ?>">
    <div class="max-w-lg">
      <h1 class="text-4xl font-bold leading-[1.05] text-white sm:text-5xl lg:text-6xl">Import Your Vehicle from the USA</h1>
      <p class="mt-5 text-lg text-white/90">We help you source your vehicle in the US, ship it to Ghana, clear it at the port and deliver it to you, with reliable updates at every stage.</p>
      <div class="mt-8 flex flex-col gap-3 sm:flex-row">
        <a href="/quote" class="btn-primary">Request an Import Quote</a>
        <a href="/update" class="btn-on-navy">Request a Vehicle Update</a>
      </div>
    </div>
  </div>
</section>

<?php if (customer_tracking()): ?>
<!-- 2. Track your vehicle -->
<section class="relative z-10 -mt-14">
  <div class="container-x">
    <form method="post" action="/track" class="card grid gap-4 p-5 lg:grid-cols-[auto_1fr_1fr_auto] lg:items-center lg:gap-6 lg:p-6">
      <?= csrf_field() ?>
      <h2 class="flex items-center gap-3 text-2xl font-bold lg:border-r lg:border-line lg:pr-6"><?= icon('map-pin', 'icon h-8 w-8 text-primary') ?>Track your vehicle</h2>
      <div class="relative">
        <label for="h-ref" class="sr-only">Tracking reference</label>
        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-muted"><?= icon('file-text') ?></span>
        <input id="h-ref" name="reference" type="text" placeholder="Tracking reference" autocomplete="off" autocapitalize="characters" maxlength="12" class="pl-12 uppercase placeholder:normal-case" required>
      </div>
      <div class="relative">
        <label for="h-last4" class="sr-only">Last 4 digits of your phone</label>
        <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-muted"><?= icon('phone') ?></span>
        <input id="h-last4" name="phone_last4" type="text" placeholder="Last 4 digits of your phone" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" class="pl-12" required>
      </div>
      <button type="submit" class="btn-primary">Track Vehicle</button>
    </form>
  </div>
</section>
<?php endif; ?>

<!-- 3. Services -->
<section class="bg-gradient-to-b from-bg to-primary-soft/60 py-14" aria-labelledby="services-h">
  <div class="container-x">
    <h2 id="services-h" class="text-center text-3xl font-bold">From a US car lot to your door in Ghana</h2>
    <div class="rule mx-auto"></div>
    <ul class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
      <?php foreach ($services as [$slot, $ic, $name, $text]): ?>
        <li class="card overflow-hidden">
          <?= $photo('svc-' . $slot, $name, 'aspect-[16/10] w-full', $ic) ?>
          <div class="p-4">
            <h3 class="flex items-center gap-2 text-lg font-semibold"><?= icon($ic, 'icon text-primary') ?><?= e($name) ?></h3>
            <p class="mt-2 text-sm text-muted"><?= e($text) ?></p>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- 3b. Featured cars for sale (PG-031): only when a car is published. -->
<?php $featured = App\Models\Cars::featured(6); ?>
<?php if ($featured !== []): ?>
<section class="bg-surface py-14" aria-labelledby="cars-h">
  <div class="container-x">
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div>
        <p class="eyebrow">Cars for sale</p>
        <h2 id="cars-h" class="mt-1 text-3xl font-bold">Featured cars for sale</h2>
        <div class="rule"></div>
      </div>
      <a href="/cars" class="btn-secondary btn-sm">See all cars <?= icon('arrow-right', 'icon h-4 w-4') ?></a>
    </div>
    <ul class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
      <?php foreach ($featured as $car): ?><li><?= App\Core\View::fetch('partials/car-card', ['car' => $car]) ?></li><?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endif; ?>

<!-- 4. How it works -->
<section class="bg-surface py-14" aria-labelledby="how-h">
  <div class="container-x">
    <h2 id="how-h" class="text-center text-3xl font-bold"><?= customer_tracking() ? 'Six stages, and you see each one' : 'Six stages, tracked by our team' ?></h2>
    <div class="rule mx-auto"></div>
    <ol class="relative mt-10 grid grid-cols-2 gap-y-8 sm:grid-cols-3 lg:grid-cols-6">
      <span class="absolute left-[8%] right-[8%] top-8 hidden h-0.5 bg-primary/30 lg:block" aria-hidden="true"></span>
      <?php foreach (Stages::LABELS as $key => $label): ?>
        <li class="relative flex flex-col items-center text-center">
          <span class="flex h-16 w-16 items-center justify-center rounded-full border-4 border-surface bg-primary-soft text-primary shadow-card"><?= icon($stageIcons[$key], 'icon h-7 w-7') ?></span>
          <h3 class="mt-3 font-semibold"><?= e($label) ?></h3>
          <p class="mt-1 hidden max-w-[11rem] text-xs text-muted sm:block"><?= e(Stages::DESCRIPTIONS[$key]) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
    <p class="mt-8 text-center"><a href="/how-it-works" class="font-semibold">See the full process <?= icon('arrow-right', 'icon h-4 w-4 align-[-2px]') ?></a></p>
  </div>
</section>

<!-- 5. Why Atlantic -->
<section class="bg-gradient-to-b from-primary-soft/60 to-bg py-14" aria-labelledby="why-h">
  <div class="container-x">
    <h2 id="why-h" class="text-center text-3xl font-bold">A Ghanaian team that keeps you informed</h2>
    <div class="rule mx-auto"></div>
    <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4 lg:divide-x lg:divide-line">
      <?php foreach ([
        ['users', 'Ghanaian team', 'A local team in Ghana who understands your needs.'],
        ['headset', 'One contact throughout', 'A dedicated person to handle your import from start to finish.'],
        ['bell', 'Updates at every stage', 'Stay informed, from purchase to final delivery in Ghana.'],
        ['file-text', 'Paperwork handled', 'We take care of the import documentation and customs process.'],
      ] as [$ic, $t, $d]): ?>
        <li class="flex gap-4 lg:px-5 lg:first:pl-0">
          <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-surface text-primary shadow-card"><?= icon($ic, 'icon h-6 w-6') ?></span>
          <div><h3 class="font-semibold"><?= e($t) ?></h3><p class="mt-1 text-sm text-muted"><?= e($d) ?></p></div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<!-- 6. What we import -->
<section class="bg-surface py-14" aria-labelledby="import-h">
  <div class="container-x">
    <h2 id="import-h" class="text-center text-3xl font-bold">Cars, SUVs, pickups and trucks</h2>
    <div class="rule mx-auto"></div>
    <ul class="mt-8 grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
      <?php foreach ($types as [$slot, $ic, $name, $sub, $brand, $brandName]): ?>
        <li>
          <a href="/quote" class="card group block overflow-hidden no-underline hover:border-primary">
            <span class="flex h-14 items-center gap-2 px-4 pt-3">
              <?php $symbolOnly = in_array($brand, ['toyota', 'honda', 'mercedes-benz'], true); ?>
              <img src="<?= e(asset('img/brands/' . $brand . '.svg')) ?>" alt="<?= $symbolOnly ? '' : e($brandName) ?>" class="<?= $brand === 'ford' ? 'h-8' : ($brand === 'isuzu' ? 'h-5' : 'h-8') ?> w-auto max-w-[9rem]" loading="lazy">
              <?php if ($symbolOnly): ?><span class="font-display text-sm font-bold uppercase tracking-wide text-ink"><?= e($brandName) ?></span><?php endif; ?>
            </span>
            <?= $photo('type-' . $slot, $sub, 'aspect-[4/3] w-full bg-white px-2', $ic, 'object-contain') ?>
            <div class="flex items-end justify-between gap-2 p-4">
              <span><span class="block font-semibold text-ink"><?= e($name) ?></span><span class="block text-xs text-muted"><?= e($sub) ?></span></span>
              <?= icon('arrow-right', 'icon text-primary transition-transform group-hover:translate-x-0.5') ?>
            </div>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
    <p class="mt-5 rounded-md bg-primary-soft px-4 py-3 text-center text-sm font-medium text-ink">
      US auctions <span class="mx-2 text-primary" aria-hidden="true">&bull;</span> Dealers <span class="mx-2 text-primary" aria-hidden="true">&bull;</span> Private sellers <span class="mx-2 text-primary" aria-hidden="true">&bull;</span> Vehicles you already own
    </p>
  </div>
</section>

<!-- 7. What your cost is made of -->
<section class="py-14" aria-labelledby="cost-h">
  <div class="container-x">
    <h2 id="cost-h" class="text-center text-3xl font-bold">What your cost is made of</h2>
    <div class="rule mx-auto"></div>
    <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-5 lg:divide-x lg:divide-line">
      <?php foreach ([
        ['shopping-cart', 'Purchase', 'The price of the vehicle in the US.'],
        ['truck', 'US towing', 'Pickup and transport to the US port.'],
        ['ship', 'Ocean freight', 'Shipping from the US to Tema, Ghana.'],
        ['coins', 'Duty and clearance', 'Import duty, taxes and customs clearance in Ghana.'],
        ['car-front', 'Delivery', 'Transport from the port to your location in Ghana.'],
      ] as [$ic, $t, $d]): ?>
        <li class="flex gap-3 lg:px-4 lg:first:pl-0">
          <?= icon($ic, 'icon h-9 w-9 text-primary') ?>
          <div><h3 class="font-semibold"><?= e($t) ?></h3><p class="mt-1 text-sm text-muted"><?= e($d) ?></p></div>
        </li>
      <?php endforeach; ?>
    </ul>
    <p class="mt-6 text-center text-sm text-muted">Every vehicle is priced individually. <a href="/quote" class="font-semibold">Request a quote</a> and we explain each part for your vehicle.</p>
  </div>
</section>

<!-- 8. Clearing at Tema -->
<section class="bg-gradient-to-r from-surface to-primary-soft/60" aria-labelledby="tema-h">
  <div class="grid lg:grid-cols-[2fr_3fr]">
    <?= $photo('tema', 'Atlantic team at Tema port', 'h-64 w-full lg:h-full lg:min-h-[22rem]', 'anchor') ?>
    <div class="px-4 py-12 sm:px-8 lg:px-12">
      <h2 id="tema-h" class="text-3xl font-bold">We handle the port, you get the keys</h2>
      <div class="rule"></div>
      <div class="mt-6 grid gap-8 sm:grid-cols-2 sm:divide-x sm:divide-line">
        <div>
          <h3 class="font-semibold">We take care of:</h3>
          <ul class="mt-3 space-y-2 text-sm text-muted">
            <?php foreach (['Import documentation and forms', 'Customs clearance at Tema Port', 'Payment of duties and applicable taxes', 'Port release and handover for delivery'] as $t): ?>
              <li class="flex gap-2"><?= icon('check', 'icon h-4 w-4 mt-0.5 text-primary') ?><?= e($t) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="sm:pl-8">
          <h3 class="font-semibold">What you provide:</h3>
          <ul class="mt-3 space-y-2 text-sm text-muted">
            <?php foreach (["Driver's licence or valid ID", 'Your details for import and clearance', 'Payment for the vehicle and import costs (as agreed in your quote)'] as $t): ?>
              <li class="flex gap-2"><?= icon('check', 'icon h-4 w-4 mt-0.5 text-primary') ?><?= e($t) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- 9. Recent deliveries and testimonials: hidden until the client supplies real content (PG-027). -->

<!-- 10 to 12. FAQ, About Us, Contact -->
<section class="py-14" aria-label="Questions, about us and contact">
  <div class="container-x grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1.3fr)_minmax(0,0.8fr)]">
    <div class="card p-5" aria-labelledby="faq-h">
      <h2 id="faq-h" class="text-xl font-bold">Frequently asked questions</h2>
      <div class="mt-4 space-y-2">
        <?php foreach ($faqs as [$q, $a]): ?>
          <details class="group rounded-md border border-line">
            <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-2.5 text-sm font-medium">
              <?= e($q) ?><?= icon('chevron-down', 'icon h-4 w-4 text-primary transition-transform group-open:rotate-180') ?>
            </summary>
            <p class="px-3 pb-3 text-sm text-muted"><?= e($a) ?></p>
          </details>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="card p-5" aria-labelledby="about-h">
      <h2 id="about-h" class="text-xl font-bold">About Us</h2>
      <?php // CSS-only illustration of shipment progress (owner's request: no photo here). Example data. ?>
      <figure class="on-navy mt-4 overflow-hidden rounded-md bg-gradient-to-br from-navy to-navy-2 p-4 text-white" aria-label="Example of shipment progress tracked by the Atlantic team">
        <div class="rounded-md bg-white p-3 text-ink shadow-card">
          <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
              <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">Shipment progress</p>
              <p class="truncate text-sm font-bold">2020 Saloon car</p>
            </div>
            <span class="chip shrink-0 bg-primary-soft font-mono text-[10px] text-primary">K7MP3QXA</span>
          </div>
          <ol class="mt-3 grid grid-cols-6 items-start gap-1" aria-hidden="true">
            <?php foreach (Stages::LABELS as $key => $label): $i = Stages::index($key); ?>
              <li class="flex flex-col items-center gap-1">
                <span class="relative flex h-5 w-full items-center justify-center">
                  <?php if ($i > 0): ?><span class="absolute right-1/2 top-1/2 h-0.5 w-full -translate-y-1/2 <?= $i <= 3 ? 'bg-primary' : 'bg-line' ?>"></span><?php endif; ?>
                  <span class="relative z-10 flex h-5 w-5 items-center justify-center rounded-full border-2 <?= $i < 3 ? 'border-primary bg-primary text-white' : ($i === 3 ? 'border-primary bg-white text-primary' : 'border-line bg-white text-muted') ?>">
                    <?= $i < 3 ? icon('check', 'icon h-3 w-3') : '<span class="h-1.5 w-1.5 rounded-full ' . ($i === 3 ? 'bg-primary' : 'bg-line') . '"></span>' ?>
                  </span>
                </span>
                <span class="text-center text-[9px] leading-tight <?= $i === 3 ? 'font-semibold text-primary' : 'hidden text-muted lg:block' ?>"><?= e($label) ?></span>
              </li>
            <?php endforeach; ?>
          </ol>
          <div class="mt-3 flex items-center gap-2 rounded-sm bg-bg px-2 py-1.5 text-[11px]">
            <?= icon('ship', 'icon h-4 w-4 text-primary') ?><span>On the ship to Tema. Updated by the Atlantic team.</span>
          </div>
        </div>
      </figure>
      <p class="mt-4 text-sm text-muted">Atlantic Auto And Shipping Services helps individuals and businesses in Ghana import vehicles from the USA, with end-to-end support from sourcing and shipping to clearance and delivery.</p>
      <div class="mt-4 grid gap-2">
        <a href="/quote" class="btn-primary btn-sm">Request an Import Quote</a>
        <a href="/update" class="btn-secondary btn-sm">Request a Vehicle Update</a>
      </div>
    </div>

    <div class="card p-5">
      <?= App\Core\View::fetch('partials/contact-list', ['dark' => false, 'heading' => 'Contact']) ?>
    </div>
  </div>
</section>
