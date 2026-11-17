<?php /** @var ?string $error @var array<string, string> $old */ ?>
<?= App\Core\View::fetch('partials/page-head', ['eyebrow' => 'Existing customers', 'heading' => 'Track Your Vehicle', 'lead' => 'Enter the tracking reference we gave you and the last 4 digits of your phone number.']) ?>
<section class="section pt-10">
  <div class="container-x max-w-xl">
    <?php if ($error !== null): ?>
      <div class="mb-6 rounded-md border-2 border-danger bg-surface p-5" role="alert" tabindex="-1" data-error-summary>
        <p class="flex gap-2 font-medium text-danger"><?= icon('circle-alert') ?><?= e($error) ?></p>
      </div>
    <?php endif; ?>
    <form method="post" action="/track" class="card space-y-5 p-5 sm:p-8">
      <?= csrf_field() ?>
      <div>
        <label for="reference">Tracking reference</label>
        <input id="reference" name="reference" type="text" maxlength="12" autocapitalize="characters" autocomplete="off" required value="<?= e($old['reference'] ?? '') ?>" class="mt-1 uppercase tracking-widest" aria-describedby="reference-hint">
        <p id="reference-hint" class="hint">8 letters and numbers, for example K7MP3QXA.</p>
      </div>
      <div>
        <label for="phone_last4">Last 4 digits of your phone number</label>
        <input id="phone_last4" name="phone_last4" type="text" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" required class="mt-1 max-w-[10rem]">
      </div>
      <button type="submit" class="btn-primary w-full sm:w-auto">Track my vehicle</button>
    </form>
    <p class="mt-6 text-sm text-muted">No reference yet, or a question? <a href="/update">Request a Vehicle Update</a>.</p>
  </div>
</section>
