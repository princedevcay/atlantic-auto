<?= App\Core\View::fetch('partials/page-head', ['eyebrow' => 'Contact', 'heading' => 'Talk to the Atlantic team', 'lead' => 'WhatsApp is the quickest way to reach us.']) ?>
<section class="section">
  <div class="container-x grid gap-8 lg:grid-cols-3">
    <div class="card p-6 lg:col-span-1">
      <?= App\Core\View::fetch('partials/contact-list', ['dark' => false]) ?>
    </div>
    <a href="/quote" class="card block p-6 no-underline hover:border-primary">
      <?= icon('car', 'icon h-8 w-8 text-primary') ?>
      <h2 class="mt-3 text-xl font-semibold">Importing a vehicle?</h2>
      <p class="mt-1 text-muted">Request an Import Quote and tell us what you need.</p>
      <span class="mt-4 inline-flex items-center gap-1 font-semibold text-primary">Request a quote <?= icon('arrow-right') ?></span>
    </a>
    <a href="/update" class="card block p-6 no-underline hover:border-primary">
      <?= icon('ship', 'icon h-8 w-8 text-primary') ?>
      <h2 class="mt-3 text-xl font-semibold">Vehicle already with us?</h2>
      <p class="mt-1 text-muted">Request a Vehicle Update with your VIN or tracking reference.</p>
      <span class="mt-4 inline-flex items-center gap-1 font-semibold text-primary">Request an update <?= icon('arrow-right') ?></span>
    </a>
  </div>
</section>
