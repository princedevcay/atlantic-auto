<?php use App\Models\Stages; ?>
<?= App\Core\View::fetch('partials/page-head', ['eyebrow' => 'How it works', 'heading' => 'From the point of sale to your door', 'lead' => 'Your vehicle moves through six stages. Our team tracks each one and keeps you updated.']) ?>
<section class="section">
  <div class="container-x max-w-3xl">
    <ol>
      <?php
      $hear = [
        'sourcing' => 'We send you vehicle options to choose from.',
        'purchased' => 'We confirm the purchase and give you a reference for your vehicle.',
        'picked_up' => 'We let you know when the vehicle has been collected.',
        'shipped' => 'We tell you the vessel and the expected arrival at Tema.',
        'clearing' => 'We tell you if we need any documents from you.',
        'delivered' => 'We arrange the handover with you.',
      ];
      foreach (Stages::LABELS as $key => $label): $i = Stages::index($key); ?>
        <li class="relative flex gap-5 pb-10 last:pb-0">
          <?php if ($i < 5): ?><span class="absolute left-[23px] top-12 h-[calc(100%-3rem)] w-0.5 bg-line" aria-hidden="true"></span><?php endif; ?>
          <span class="relative z-10 flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-navy font-display text-xl font-bold text-white"><?= $i + 1 ?></span>
          <div class="pt-1">
            <h2 class="text-2xl font-bold"><?= e($label) ?></h2>
            <p class="mt-1 text-muted"><?= e(Stages::DESCRIPTIONS[$key]) ?></p>
            <p class="mt-3 rounded-md bg-surface p-3 text-sm"><strong>What you hear from us:</strong> <?= e($hear[$key]) ?></p>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
    <div class="mt-10 rounded-md border border-line bg-surface p-5 text-sm text-muted">
      Our team tracks every stage. While your vehicle is at sea, shipping information comes from the shipping line. Ask for an update at any time.
    </div>
    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
      <a href="/quote" class="btn-primary">Request an Import Quote</a>
      <a href="/update" class="btn-secondary">Request a Vehicle Update</a>
    </div>
  </div>
</section>
