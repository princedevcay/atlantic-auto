<?php
/** @var array{type: string, ref: string, vehicle: string} $thanks */
$isQuote = $thanks['type'] === 'quote';
$wa = whatsapp_url($isQuote
    ? 'Hello Atlantic, I just sent an import quote request. Reference ' . $thanks['ref'] . ($thanks['vehicle'] !== '' ? ' for a ' . $thanks['vehicle'] : '') . '.'
    : 'Hello Atlantic, I just sent a vehicle update request. Reference ' . $thanks['ref'] . '.');
?>
<section class="section">
  <div class="container-x max-w-2xl">
    <div class="card p-6 text-center sm:p-10" role="status">
      <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-primary-soft text-success"><?= icon('circle-check', 'icon h-8 w-8') ?></span>
      <h1 class="mt-5 text-3xl font-bold"><?= $isQuote ? 'Your quote request has been sent' : 'Your update request has been sent' ?></h1>
      <p class="mt-3 text-muted">Our team will reply by WhatsApp or phone. Keep this reference in case you need to contact us:</p>
      <p class="mt-4 inline-block rounded-md bg-bg px-5 py-3 font-display text-2xl font-bold tracking-widest"><?= e($thanks['ref']) ?></p>
      <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
        <?php if ($wa !== ''): ?>
          <a href="<?= e($wa) ?>" class="btn-primary" rel="noopener">
            <img src="<?= e(asset('img/brands/whatsapp.svg')) ?>" alt="" width="20" height="20" class="h-5 w-5 invert"> Continue on WhatsApp
          </a>
        <?php endif; ?>
        <a href="/" class="btn-secondary">Back to home</a>
      </div>
      <?php if (!$isQuote): ?>
        <p class="mt-6 text-sm text-muted">If you have your tracking reference, you can also check your vehicle's stage on <a href="/track">Track Your Vehicle</a>.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
