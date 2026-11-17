<?php
use App\Core\Auth;
use App\Core\Session;

$section = $section ?? '';
$toast = Session::takeFlash('toast');
$u = Auth::user();
$nav = [['inbox', '/admin/inbox', 'Inbox', 'inbox'], ['shipments', '/admin/shipments', 'Shipments', 'package']];
if (Auth::isAdmin()) {
    $nav[] = ['staff', '/admin/staff', 'Staff', 'users'];
    $nav[] = ['audit', '/admin/audit', 'Audit log', 'shield-check'];
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> | Atlantic admin</title>
<link rel="stylesheet" href="<?= e(asset('css/app.css')) ?>">
<link rel="icon" href="<?= e(asset('img/favicon.png')) ?>">
<script src="<?= e(asset('js/app.js')) ?>" defer></script>
</head>
<body class="min-h-screen bg-bg">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-surface focus:px-4 focus:py-2">Skip to content</a>
<div class="md:flex">
  <aside class="on-navy bg-navy text-white md:sticky md:top-0 md:flex md:h-screen md:w-60 md:flex-col">
    <div class="flex items-center justify-between px-4 py-4 md:block">
      <a href="/admin/inbox" class="block font-display text-lg font-bold text-white no-underline">Atlantic admin</a>
      <p class="hidden text-xs text-white/70 md:mt-1 md:block"><?= e($u['name'] ?? '') ?> &middot; <?= e(ucfirst((string) ($u['role'] ?? ''))) ?></p>
    </div>
    <nav aria-label="Admin" class="flex gap-1 overflow-x-auto px-2 pb-2 md:flex-1 md:flex-col md:overflow-visible">
      <?php foreach ($nav as [$key, $href, $label, $ic]): ?>
        <a href="<?= e($href) ?>" class="flex shrink-0 items-center gap-3 rounded-md px-3 py-2.5 text-sm font-medium no-underline <?= $section === $key ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' ?>" <?= $section === $key ? 'aria-current="page"' : '' ?>><?= icon($ic) ?><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <form method="post" action="/admin/logout" class="hidden px-2 pb-4 md:block">
      <?= csrf_field() ?>
      <a href="/admin/account/password" class="flex items-center gap-3 rounded-md px-3 py-2 text-sm text-white/80 no-underline hover:text-white"><?= icon('lock') ?>Change password</a>
      <button type="submit" class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-left text-sm text-white/80 hover:bg-white/10 hover:text-white"><?= icon('log-out') ?>Sign out</button>
    </form>
  </aside>
  <div class="min-w-0 flex-1">
    <?php if (is_string($toast)): ?>
      <div class="border-b border-line bg-primary-soft px-4 py-3 text-sm font-medium text-ink sm:px-6" role="status"><?= e($toast) ?></div>
    <?php endif; ?>
    <main id="main" class="px-4 py-6 sm:px-6 lg:px-8">
      <?= $content ?>
    </main>
    <form method="post" action="/admin/logout" class="px-4 pb-8 md:hidden">
      <?= csrf_field() ?>
      <button type="submit" class="btn-secondary btn-sm w-full">Sign out</button>
    </form>
  </div>
</div>
</body>
</html>
