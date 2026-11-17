<?php
use App\Models\Stages;

/** @var array<string, mixed> $s @var list<array<string, mixed>> $updates @var list<array<string, mixed>> $photos @var list<array<string, mixed>> $events */
$current = Stages::index((string) $s['stage']);
$byStage = [];
foreach ($updates as $u) {
    $byStage[(string) $u['stage']][] = $u;
}
$vehicle = trim(($s['vehicle_year'] ?? '') . ' ' . $s['vehicle_make'] . ' ' . $s['vehicle_model']);
?>
<section class="on-navy bg-navy text-white">
  <div class="container-x py-10 sm:py-14">
    <p class="text-sm font-semibold uppercase tracking-wide text-white/75">Your vehicle</p>
    <h1 class="mt-1 text-3xl font-bold text-white sm:text-4xl"><?= e($vehicle) ?></h1>
    <dl class="mt-4 flex flex-wrap gap-x-8 gap-y-2 text-sm text-white/85">
      <div><dt class="inline">Reference: </dt><dd class="inline font-semibold tracking-widest text-white"><?= e($s['reference']) ?></dd></div>
      <?php if (!empty($s['vin'])): ?><div><dt class="inline">VIN: </dt><dd class="inline font-mono text-white"><?= e(mask_vin((string) $s['vin'])) ?></dd></div><?php endif; ?>
      <div><dt class="inline">Last updated: </dt><dd class="inline text-white"><?= e(fmt_date((string) $s['updated_at'], 'j M Y, g:ia')) ?></dd></div>
    </dl>
  </div>
</section>

<section class="section pt-10">
  <div class="container-x grid gap-8 lg:grid-cols-[1.3fr_1fr]">
    <div class="card p-5 sm:p-8">
      <h2 class="text-2xl font-bold">Where your vehicle is</h2>
      <p class="mt-1 text-sm text-muted">Stages are updated by the Atlantic team<?= $s['carrier_synced_at'] !== null ? ' and the shipping line' : '' ?>. This is not live GPS.</p>
      <ol class="mt-8">
        <?php foreach (Stages::LABELS as $key => $label):
          $i = Stages::index($key);
          $done = $i < $current;
          $now = $i === $current;
        ?>
          <li class="relative flex gap-4 pb-8 last:pb-0" <?= $now ? 'aria-current="step"' : '' ?>>
            <?php if ($i < count(Stages::LABELS) - 1): ?>
              <span class="absolute left-[19px] top-10 h-[calc(100%-2.5rem)] w-0.5 <?= $done ? 'bg-primary' : 'bg-line' ?>" aria-hidden="true"></span>
            <?php endif; ?>
            <span class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-2 <?= $done ? 'border-primary bg-primary text-white' : ($now ? 'border-primary bg-surface text-primary' : 'border-line bg-surface text-muted') ?>">
              <?= $done ? icon('check', 'icon h-5 w-5') : '<span class="font-semibold">' . ($i + 1) . '</span>' ?>
            </span>
            <div class="pt-1.5">
              <h3 class="font-semibold <?= !$done && !$now ? 'text-muted' : '' ?>">
                <?= e($label) ?>
                <?php if ($now): ?><span class="chip ml-2 bg-primary-soft text-primary">Current stage</span><?php endif; ?>
                <?php if ($done): ?><span class="sr-only">(completed)</span><?php endif; ?>
              </h3>
              <?php if ($now || $done): ?><p class="mt-1 text-sm text-muted"><?= e(Stages::DESCRIPTIONS[$key]) ?></p><?php endif; ?>
              <?php if ($key === 'shipped' && ($now || $done) && (!empty($s['vessel']) || !empty($s['eta']))): ?>
                <p class="mt-2 text-sm"><?php if (!empty($s['vessel'])): ?>Vessel: <strong><?= e($s['vessel']) ?></strong>. <?php endif; ?><?php if (!empty($s['eta'])): ?>Expected at Tema: <strong><?= e(date('j M Y', (int) strtotime((string) $s['eta']))) ?></strong><?php endif; ?></p>
              <?php endif; ?>
              <?php foreach ($byStage[$key] ?? [] as $u): ?>
                <div class="mt-2 rounded-md bg-bg p-3 text-sm">
                  <p class="text-xs text-muted"><?= e(fmt_date((string) $u['created_at'], 'j M Y')) ?> &middot; from the Atlantic team</p>
                  <?php if (!empty($u['note'])): ?><p class="mt-1 whitespace-pre-line"><?= e($u['note']) ?></p><?php endif; ?>
                </div>
              <?php endforeach; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    </div>

    <div class="space-y-6">
      <?php if ($events !== []): ?>
      <div class="card p-5">
        <h2 class="text-lg font-semibold">Shipping updates</h2>
        <p class="text-xs text-muted">From the shipping line<?= $s['carrier_synced_at'] !== null ? ', last checked ' . e(fmt_date((string) $s['carrier_synced_at'], 'j M, g:ia')) : '' ?></p>
        <ul class="mt-3 space-y-3 text-sm">
          <?php foreach ($events as $ev): ?>
            <li><span class="block text-xs text-muted"><?= e(fmt_date((string) $ev['event_time'], 'j M Y, g:ia')) ?></span><?= e($ev['description']) ?><?= !empty($ev['location']) ? ', ' . e($ev['location']) : '' ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if ($photos !== []): ?>
      <div class="card p-5">
        <h2 class="text-lg font-semibold">Photos</h2>
        <div class="mt-3 grid grid-cols-2 gap-2">
          <?php foreach ($photos as $p): ?>
            <a href="/track/photo/<?= (int) $p['id'] ?>" class="block overflow-hidden rounded-md bg-bg">
              <img src="/track/photo/<?= (int) $p['id'] ?>" alt="Photo of your vehicle" width="<?= (int) $p['width'] ?>" height="<?= (int) $p['height'] ?>" loading="lazy" class="h-32 w-full object-cover">
            </a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="card p-5">
        <h2 class="text-lg font-semibold">Questions about your vehicle?</h2>
        <p class="mt-1 text-sm text-muted">Quote your reference <strong><?= e($s['reference']) ?></strong>.</p>
        <div class="mt-4 flex flex-col gap-2">
          <a href="/update" class="btn-primary">Request a Vehicle Update</a>
          <?php $wa = whatsapp_url('Hello Atlantic, I have a question about my vehicle, reference ' . $s['reference'] . '.'); ?>
          <?php if ($wa !== ''): ?>
            <a href="<?= e($wa) ?>" class="btn-secondary" rel="noopener"><img src="<?= e(asset('img/brands/whatsapp.svg')) ?>" alt="" width="20" height="20" class="h-5 w-5"> Message us on WhatsApp</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>
