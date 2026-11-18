<?php
// Contact details come from config.php. An empty value is hidden; placeholders never ship (PG-015).
$dark = $dark ?? false;
$socials = social_links();
$text = $dark ? 'text-white/90 hover:text-white' : 'text-ink hover:text-primary';
$muted = $dark ? 'text-white/70' : 'text-muted';
$items = [];
if (contact('phone') !== '') {
    $items[] = ['phone', 'Call us', contact('phone'), tel_url(contact('phone'))];
}
if (whatsapp_url() !== '') {
    $items[] = ['whatsapp', 'WhatsApp', whatsapp_display(), whatsapp_url('Hello Atlantic, I have a question.')];
}
if (contact('email') !== '') {
    $items[] = ['mail', 'Email', contact('email'), 'mailto:' . contact('email')];
}
if (contact('address') !== '') {
    $items[] = ['map-pin', 'Office', contact('address'), contact('map_url')];
}
if (contact('hours') !== '') {
    $items[] = ['clock', 'Hours', contact('hours'), ''];
}
?>
<div>
  <h2 class="<?= isset($heading) ? 'text-xl font-bold' : 'text-base font-semibold' ?> <?= $dark ? 'text-white' : '' ?>"><?= e($heading ?? 'Contact us') ?></h2>
  <?php if ($items === [] && $socials === []): ?>
    <p class="mt-3 text-sm <?= e($muted) ?>">Contact details will be published here soon.</p>
  <?php else: ?>
  <?php if ($items !== []): ?>
  <ul class="mt-3 space-y-3 text-sm">
    <?php foreach ($items as [$ic, $label, $value, $href]): ?>
      <li class="flex items-start gap-3">
        <?php if ($ic === 'whatsapp'): ?>
          <img src="<?= e(asset('img/brands/whatsapp.svg')) ?>" alt="" width="20" height="20" class="mt-0.5 h-5 w-5 <?= $dark ? 'invert' : '' ?>">
        <?php else: ?>
          <?= icon($ic, 'icon mt-0.5 ' . ($dark ? 'text-white/80' : 'text-primary')) ?>
        <?php endif; ?>
        <span>
          <span class="block text-xs <?= e($muted) ?>"><?= e($label) ?></span>
          <?php if ($href !== ''): ?>
            <a href="<?= e($href) ?>" class="font-medium no-underline <?= e($text) ?>" <?= str_starts_with($href, 'http') ? 'rel="noopener"' : '' ?>><?= e($value) ?></a>
          <?php else: ?>
            <span class="font-medium <?= $dark ? 'text-white' : '' ?>"><?= e($value) ?></span>
          <?php endif; ?>
        </span>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <?php if ($socials !== []): ?>
  <p class="mt-5 text-xs <?= e($muted) ?>">Follow us</p>
  <ul class="mt-2 flex gap-2" aria-label="Social media">
    <?php foreach ($socials as $s): ?>
      <li>
        <a href="<?= e($s['url']) ?>" class="flex h-10 w-10 items-center justify-center rounded-full border <?= $dark ? 'border-white/25 hover:bg-white/10' : 'border-line hover:border-primary hover:bg-primary-soft' ?>" rel="noopener me" target="_blank" aria-label="<?= e($s['label']) ?> (opens in a new tab)" title="<?= e($s['label']) ?>">
          <img src="<?= e(asset('img/brands/' . $s['key'] . '.svg')) ?>" alt="" width="18" height="18" class="h-[18px] w-[18px] <?= $dark ? 'invert' : '' ?>">
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <?php endif; ?>
</div>
