<!doctype html>
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
<body class="flex min-h-screen items-center justify-center bg-navy px-4 py-10">
<main id="main" class="w-full max-w-md">
  <div class="mb-6 text-center">
    <span class="inline-block rounded-md bg-white p-3"><img src="<?= e(asset('img/logo.png')) ?>" alt="Atlantic Auto And Shipping Services" width="900" height="176" class="h-12 w-auto"></span>
  </div>
  <div class="card p-6 sm:p-8">
    <?= $content ?>
  </div>
</main>
</body>
</html>
