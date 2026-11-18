<?php
use App\Models\Cars;

/** @var array<string, mixed> $car */
$badge = ['available' => 'bg-[#e3f3ea] text-success', 'reserved' => 'bg-[#fbefdc] text-warning', 'sold' => 'bg-navy text-white'][$car['status']] ?? '';
$title = Cars::title($car);
?>
<a href="/cars/<?= e($car['reference']) ?>" class="card group flex h-full flex-col overflow-hidden no-underline hover:border-primary">
  <div class="relative aspect-[4/3] bg-white">
    <?php if (!empty($car['photo_id'])): ?>
      <img src="/cars/photo/<?= (int) $car['photo_id'] ?>" alt="<?= e($title) ?>" width="<?= (int) $car['photo_w'] ?>" height="<?= (int) $car['photo_h'] ?>" loading="lazy" class="h-full w-full object-contain px-2 <?= $car['status'] === 'sold' ? 'opacity-60' : '' ?>">
    <?php else: ?>
      <div class="flex h-full items-center justify-center bg-primary-soft text-primary/40" aria-hidden="true"><?= icon('car', 'icon h-10 w-10') ?></div>
    <?php endif; ?>
    <span class="chip absolute left-3 top-3 <?= e($badge) ?>"><?= e(Cars::STATUSES[$car['status']] ?? '') ?></span>
    <?php if ((int) ($car['sample'] ?? 0) === 1): ?><span class="chip absolute right-3 top-3 border border-line bg-surface text-muted">Sample</span><?php endif; ?>
  </div>
  <div class="flex flex-1 flex-col p-4">
    <h3 class="font-semibold text-ink"><?= e($title) ?></h3>
    <p class="mt-1 text-xs text-muted"><?= e(implode(' · ', array_filter([Cars::mileage($car), Cars::TRANSMISSIONS[$car['transmission'] ?? ''] ?? '', Cars::LOCATIONS[$car['location']] ?? '']))) ?></p>
    <div class="mt-auto flex items-end justify-between gap-2 pt-3">
      <span class="font-display text-lg font-bold <?= $car['status'] === 'sold' ? 'text-muted line-through' : 'text-primary' ?>"><?= e(Cars::price($car)) ?></span>
      <?= icon('arrow-right', 'icon text-primary transition-transform group-hover:translate-x-0.5') ?>
    </div>
  </div>
</a>
