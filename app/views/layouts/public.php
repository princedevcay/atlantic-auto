<?php
/** @var string $content @var string $title */
$nav = $nav ?? '';
$links = ['services' => ['/services', 'Services'], 'how' => ['/how-it-works', 'How It Works'], 'track' => ['/track', 'Track'], 'about' => ['/about', 'About'], 'contact' => ['/contact', 'Contact']];
$hideBar = in_array($nav, ['quote', 'update', 'track'], true) || isset($s);
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | Atlantic Auto and Shipping Services</title>
<meta name="description" content="Atlantic Auto and Shipping Services helps customers in Ghana import vehicles from the USA and track them from the point of sale through shipping.">
<link rel="preload" href="<?= e(asset('fonts/inter-latin-wght-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<link rel="icon" href="<?= e(asset('img/favicon.png')) ?>">
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body class="flex min-h-screen flex-col <?= $hideBar ? '' : 'pb-20 md:pb-0' ?>">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-surface focus:px-4 focus:py-2">Skip to content</a>

<header class="sticky top-0 z-40 border-b border-line bg-surface">
  <div class="container-x flex h-[72px] items-center justify-between gap-4 md:h-24">
    <a href="/" class="flex shrink-0 items-center" aria-label="Atlantic Auto and Shipping Services, home">
      <picture>
        <source srcset="<?= e(asset('img/logo.webp')) ?>" type="image/webp">
        <img src="<?= e(asset('img/logo.png')) ?>" alt="Atlantic Auto and Shipping Services" width="900" height="176" class="h-11 w-auto sm:h-12 md:h-16">
      </picture>
    </a>
    <nav aria-label="Main" class="hidden items-center gap-1 lg:flex">
      <?php foreach ($links as $key => [$href, $label]): ?>
        <a href="<?= e($href) ?>" class="rounded-md px-3 py-2 font-medium no-underline <?= $nav === $key ? 'text-primary' : 'text-ink hover:text-primary' ?>" <?= $nav === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
      <a href="/quote" class="btn-primary ml-3">Request an Import Quote</a>
    </nav>
    <details class="relative lg:hidden" data-menu>
      <summary class="btn-ghost list-none cursor-pointer px-3" aria-label="Menu"><?= icon('menu', 'icon h-6 w-6') ?><span class="sr-only">Menu</span></summary>
      <div class="absolute right-0 top-14 w-72 rounded-md border border-line bg-surface p-3 shadow-card">
        <nav aria-label="Mobile">
          <?php foreach ($links as $key => [$href, $label]): ?>
            <a href="<?= e($href) ?>" class="block rounded-md px-3 py-3 font-medium text-ink no-underline hover:bg-bg" <?= $nav === $key ? 'aria-current="page"' : '' ?>><?= e($label) ?></a>
          <?php endforeach; ?>
          <a href="/quote" class="btn-primary mt-2 w-full">Request an Import Quote</a>
          <a href="/update" class="btn-secondary mt-2 w-full">Request a Vehicle Update</a>
        </nav>
      </div>
    </details>
  </div>
</header>

<main id="main" class="flex-1">
<?= $content ?>
</main>

<footer class="on-navy bg-navy text-white">
  <div class="container-x grid gap-10 py-12 md:grid-cols-3">
    <div>
      <picture>
        <source srcset="<?= e(asset('img/logo-white.webp')) ?>" type="image/webp">
        <img src="<?= e(asset('img/logo-white.png')) ?>" alt="Atlantic Auto and Shipping Services" width="900" height="176" class="h-12 w-auto" loading="lazy">
      </picture>
      <p class="mt-4 max-w-xs text-sm text-white/80">Vehicle importation from the USA to Ghana, tracked from the point of sale through shipping.</p>
    </div>
    <nav aria-label="Footer" class="grid grid-cols-2 gap-2 text-sm">
      <a href="/services" class="text-white/90 no-underline hover:text-white">Services</a>
      <a href="/how-it-works" class="text-white/90 no-underline hover:text-white">How It Works</a>
      <a href="/track" class="text-white/90 no-underline hover:text-white">Track Your Vehicle</a>
      <a href="/about" class="text-white/90 no-underline hover:text-white">About Us</a>
      <a href="/contact" class="text-white/90 no-underline hover:text-white">Contact</a>
      <a href="/privacy" class="text-white/90 no-underline hover:text-white">Privacy</a>
    </nav>
    <?= App\Core\View::fetch('partials/contact-list', ['dark' => true]) ?>
  </div>
  <div class="border-t border-white/15">
    <p class="container-x py-5 text-sm text-white/70">&copy; <?= e(gmdate('Y')) ?> Atlantic Auto and Shipping Services.</p>
  </div>
</footer>

<?php if (!$hideBar): ?>
<div class="fixed inset-x-0 bottom-0 z-40 grid grid-cols-2 gap-2 border-t border-line bg-surface p-2 md:hidden">
  <a href="/quote" class="btn-primary btn-sm text-center leading-tight">Import Quote</a>
  <a href="/update" class="btn-secondary btn-sm text-center leading-tight">Vehicle Update</a>
</div>
<?php endif; ?>
</body>
</html>
