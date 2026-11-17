<section class="on-navy bg-navy text-white">
  <div class="container-x py-12 sm:py-16">
    <?php if (!empty($eyebrow)): ?><p class="text-sm font-semibold uppercase tracking-wide text-white/75"><?= e($eyebrow) ?></p><?php endif; ?>
    <h1 class="mt-1 text-3xl font-bold text-white sm:text-5xl"><?= e($heading) ?></h1>
    <?php if (!empty($lead)): ?><p class="mt-4 max-w-2xl text-lg text-white/85"><?= e($lead) ?></p><?php endif; ?>
  </div>
</section>
