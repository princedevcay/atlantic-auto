<?php /** @var array<string, mixed> $old @var array<string, string> $errors */ ?>
<?= App\Core\View::fetch('partials/page-head', ['eyebrow' => 'Existing customers', 'heading' => 'Request a Vehicle Update', 'lead' => 'Ask about a vehicle that is already with us. Our team will reply by WhatsApp or phone.']) ?>
<section class="section pt-10">
  <div class="container-x max-w-2xl">
    <?php if (customer_tracking()): ?>
    <div class="mb-6 flex gap-3 rounded-md border border-line bg-surface p-4 text-sm">
      <?= icon('info', 'icon text-primary') ?>
      <p>Have your tracking reference? You can see your vehicle's stage right now on <a href="/track">Track Your Vehicle</a>.</p>
    </div>
    <?php endif; ?>
    <?= App\Core\View::fetch('partials/error-summary', ['errors' => $errors, 'notice' => $notice ?? null]) ?>
    <form method="post" action="/update" class="card space-y-5 p-5 sm:p-8" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="started" value="<?= e($started) ?>">
      <div class="hidden" aria-hidden="true"><label for="website">Leave this empty</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
      <div>
        <label for="name">Full name</label>
        <input id="name" name="name" type="text" autocomplete="name" maxlength="120" required value="<?= e(old($old, 'name')) ?>" class="mt-1"<?= field_attrs($errors, 'name') ?>>
        <?= field_error($errors, 'name') ?>
      </div>
      <div>
        <label for="phone">Phone or WhatsApp number</label>
        <input id="phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" maxlength="20" required value="<?= e(old($old, 'phone')) ?>" class="mt-1"<?= field_attrs($errors, 'phone', true) ?>>
        <p id="phone-hint" class="hint">Use the number you gave us when you started.</p>
        <?= field_error($errors, 'phone') ?>
      </div>
      <div>
        <label for="vin_or_reference">VIN or reference</label>
        <input id="vin_or_reference" name="vin_or_reference" type="text" maxlength="40" autocapitalize="characters" autocomplete="off" required value="<?= e(old($old, 'vin_or_reference')) ?>" class="mt-1 uppercase"<?= field_attrs($errors, 'vin_or_reference', true) ?>>
        <p id="vin_or_reference-hint" class="hint">The 17-character VIN, or the 8-character reference our team gave you.</p>
        <?= field_error($errors, 'vin_or_reference') ?>
      </div>
      <div>
        <label for="question">Your question</label>
        <textarea id="question" name="question" rows="5" maxlength="2000" required class="mt-1"<?= field_attrs($errors, 'question') ?>><?= e(old($old, 'question')) ?></textarea>
        <?= field_error($errors, 'question') ?>
      </div>
      <p class="text-sm text-muted">Updates are given by the Atlantic team. See our <a href="/privacy">privacy notice</a>.</p>
      <button type="submit" class="btn-primary w-full sm:w-auto" data-busy="Sending...">Send update request</button>
    </form>
  </div>
</section>
