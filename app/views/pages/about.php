<?= App\Core\View::fetch('partials/page-head', ['eyebrow' => 'About us', 'heading' => 'Atlantic Auto and Shipping Services']) ?>
<section class="section">
  <div class="container-x grid gap-10 lg:grid-cols-2 lg:items-start">
    <div>
      <p class="text-xl leading-relaxed">Atlantic Auto and Shipping Services provides vehicle importation and tracking services for customers bringing vehicles in from the USA. We help you follow your vehicle&rsquo;s progress from the point of sale through shipping, so you know where it is and what comes next.</p>
      <div class="mt-8 grid gap-4 sm:grid-cols-2">
        <div class="card p-5"><h2 class="text-lg font-semibold">What we do</h2><p class="mt-1 text-muted">Sourcing, purchase, pickup, shipping, clearance at Tema and delivery in Ghana.</p></div>
        <div class="card p-5"><h2 class="text-lg font-semibold">How we keep you informed</h2><p class="mt-1 text-muted">Our team tracks every vehicle and updates you at each stage.</p></div>
      </div>
      <div class="mt-8 flex flex-col gap-3 sm:flex-row">
        <a href="/quote" class="btn-primary">Request an Import Quote</a>
        <a href="/contact" class="btn-secondary">Contact us</a>
      </div>
    </div>
    <?php $about = site_image('about'); ?>
    <div class="aspect-[4/3] overflow-hidden rounded-lg bg-navy-2">
      <?php if ($about !== ''): ?><img src="<?= e($about) ?>" alt="Atlantic Auto and Shipping Services" class="h-full w-full object-cover" loading="lazy"><?php endif; ?>
    </div>
  </div>
</section>
