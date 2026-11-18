<?= App\Core\View::fetch('partials/page-head', ['eyebrow' => 'Services', 'heading' => 'Everything between a US seller and your door', 'lead' => 'Use one service or all six. Tell us what you need in the quote form.']) ?>
<section class="section">
  <div class="container-x space-y-6">
    <?php foreach ([
      ['search', 'Sourcing', 'Tell us the make, model, year range and budget. We look for suitable vehicles in the USA and share the options with you before anything is bought.', 'Your preferences and budget, and a decision on the options we send.'],
      ['shopping-cart', 'Purchase', 'We buy the chosen vehicle on your behalf at auction, from a dealer or from a private seller, and collect the title and bill of sale.', 'Approval of the vehicle and payment as agreed in your quote.'],
      ['truck', 'Pickup in the USA', 'We arrange towing or transport from the seller to the port of loading.', 'Nothing, unless you bought the vehicle yourself: then the seller and pickup details.'],
      ['ship', 'Shipping to Tema', 'Your vehicle is booked, loaded and shipped to Tema. We tell you the vessel and expected arrival at Tema as soon as we have them.', 'Nothing. We keep you updated.'],
      ['file-check', 'Clearance at Tema port', 'Our team handles the documents, customs declaration and duty process, and the release of the vehicle from the port.', 'Your identification and any documents we confirm for your vehicle.'],
      ['house', 'Delivery', 'We hand the vehicle over to you, or deliver it to an agreed place in Ghana.', 'Be available to receive the vehicle.'],
    ] as $i => [$ic, $name, $what, $you]): ?>
      <article class="card grid gap-6 p-6 sm:p-8 md:grid-cols-[auto_1fr_1fr]">
        <span class="flex h-14 w-14 items-center justify-center rounded-md bg-primary-soft text-primary"><?= icon($ic, 'icon h-7 w-7') ?></span>
        <div>
          <p class="text-sm font-semibold text-primary">Step <?= $i + 1 ?></p>
          <h2 class="text-2xl font-bold"><?= e($name) ?></h2>
          <p class="mt-2 text-muted"><?= e($what) ?></p>
        </div>
        <div class="rounded-md bg-bg p-4">
          <h3 class="text-sm font-semibold uppercase tracking-wide text-muted">What we need from you</h3>
          <p class="mt-2"><?= e($you) ?></p>
        </div>
      </article>
    <?php endforeach; ?>
    <div class="flex flex-col gap-3 pt-4 sm:flex-row">
      <a href="/quote" class="btn-primary">Request an Import Quote</a>
      <a href="/how-it-works" class="btn-secondary">How it works</a>
    </div>
  </div>
</section>
