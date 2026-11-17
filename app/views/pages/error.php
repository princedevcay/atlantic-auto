<section class="section">
  <div class="container-x max-w-xl text-center">
    <p class="font-display text-6xl font-bold text-primary"><?= (int) ($code ?? 500) ?></p>
    <h1 class="mt-4 text-3xl font-bold"><?= e($title) ?></h1>
    <p class="mt-3 text-muted"><?= e(($message ?? '') !== '' ? $message : 'The page you asked for is not available.') ?></p>
    <a href="<?= App\Core\Session::scope() === 'admin' ? '/admin' : '/' ?>" class="btn-primary mt-8">Go to the home page</a>
  </div>
</section>
