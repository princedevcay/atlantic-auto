<?php
// Contact details come from config.php. An empty value is hidden; placeholders never ship (PG-015).
$dark = $dark ?? false;
$text = $dark ? 'text-white/90 hover:text-white' : 'text-ink hover:text-primary';
$muted = $dark ? 'text-white/70' : 'text-muted';
$items = [];
if (contact('phone') !== '') {
    $items[] = ['phone', 'Phone', contact('phone'), tel_url(contact('phone'))];
}
if (whatsapp_url() !== '') {
    $items[] = ['whatsapp', 'WhatsApp', 'Chat with us on WhatsApp', whatsapp_url()];
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
  <?php if ($items === []): ?>
    <p class="mt-3 text-sm <?= e($muted) ?>">Contact details will be published here soon.</p>
  <?php else: ?>
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
</div>
