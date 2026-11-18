<?php
use App\Controllers\FormController;

/** @var array<string, mixed> $old @var array<string, string> $errors */
$svc = is_array($old['services'] ?? null) ? $old['services'] : [];
?>
<?= App\Core\View::fetch('partials/page-head', ['eyebrow' => 'Import from the USA', 'heading' => 'Request an Import Quote', 'lead' => 'Tell us about the vehicle and what you need. We will reply by WhatsApp or phone.']) ?>
<section class="section pt-10">
  <div class="container-x max-w-2xl">
    <?= App\Core\View::fetch('partials/error-summary', ['errors' => $errors, 'notice' => $notice ?? null]) ?>
    <form method="post" action="/quote" class="card space-y-6 p-5 sm:p-8" novalidate>
      <?= csrf_field() ?>
      <input type="hidden" name="started" value="<?= e($started) ?>">
      <div class="hidden" aria-hidden="true"><label for="website">Leave this empty</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>

      <fieldset class="space-y-5">
        <legend class="text-xl font-semibold">Your details</legend>
        <div>
          <label for="name">Full name</label>
          <input id="name" data-wa-label="Name" name="name" type="text" autocomplete="name" maxlength="120" required value="<?= e(old($old, 'name')) ?>" class="mt-1"<?= field_attrs($errors, 'name') ?>>
          <?= field_error($errors, 'name') ?>
        </div>
        <div>
          <label for="phone">Phone or WhatsApp number</label>
          <input id="phone" data-wa-label="Phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" maxlength="20" required value="<?= e(old($old, 'phone')) ?>" class="mt-1"<?= field_attrs($errors, 'phone', true) ?>>
          <p id="phone-hint" class="hint">For example 024 123 4567 or +233 24 123 4567.</p>
          <?= field_error($errors, 'phone') ?>
        </div>
        <div>
          <label for="email">Email <span class="font-normal text-muted">(optional)</span></label>
          <input id="email" data-wa-label="Email" name="email" type="email" autocomplete="email" maxlength="190" value="<?= e(old($old, 'email')) ?>" class="mt-1"<?= field_attrs($errors, 'email') ?>>
          <?= field_error($errors, 'email') ?>
        </div>
      </fieldset>

      <fieldset class="space-y-3" data-wa-label="Services needed"<?= isset($errors['services']) ? ' aria-describedby="services-error"' : '' ?>>
        <legend class="text-xl font-semibold">What do you need help with?</legend>
        <p class="text-sm text-muted">Choose all that apply.</p>
        <?php foreach (FormController::SERVICES as $key => $label): ?>
          <label class="flex cursor-pointer items-center gap-3 rounded-md border border-line p-3 font-normal hover:border-primary">
            <input type="checkbox" id="svc-<?= e($key) ?>" name="services[]" value="<?= e($key) ?>" <?= in_array($key, $svc, true) ? 'checked' : '' ?>>
            <span><?= e($label) ?></span>
          </label>
        <?php endforeach; ?>
        <?= field_error($errors, 'services') ?>
      </fieldset>

      <fieldset class="space-y-5">
        <legend class="text-xl font-semibold">The vehicle</legend>
        <div class="grid gap-4 sm:grid-cols-3">
          <div>
            <label for="year">Year <span class="font-normal text-muted">(optional)</span></label>
            <input id="year" data-wa-label="Year" name="year" type="text" inputmode="numeric" maxlength="4" value="<?= e(old($old, 'year')) ?>" class="mt-1"<?= field_attrs($errors, 'year') ?>>
            <?= field_error($errors, 'year') ?>
          </div>
          <div>
            <label for="make">Make</label>
            <input id="make" data-wa-label="Make" name="make" type="text" maxlength="60" required value="<?= e(old($old, 'make')) ?>" class="mt-1"<?= field_attrs($errors, 'make', true) ?>>
            <p id="make-hint" class="hint">For example Toyota</p>
            <?= field_error($errors, 'make') ?>
          </div>
          <div>
            <label for="model">Model</label>
            <input id="model" data-wa-label="Model" name="model" type="text" maxlength="80" required value="<?= e(old($old, 'model')) ?>" class="mt-1"<?= field_attrs($errors, 'model', true) ?>>
            <p id="model-hint" class="hint">For example RAV4</p>
            <?= field_error($errors, 'model') ?>
          </div>
        </div>
        <div>
          <label for="source">Where is the vehicle?</label>
          <select id="source" data-wa-label="Vehicle location" name="source" required class="mt-1"<?= field_attrs($errors, 'source') ?>>
            <option value="">Choose one</option>
            <?php foreach (FormController::SOURCES as $key => $label): ?>
              <option value="<?= e($key) ?>" <?= old($old, 'source') === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
          </select>
          <?= field_error($errors, 'source') ?>
        </div>
        <div>
          <label for="listing_url">Link to the listing <span class="font-normal text-muted">(optional)</span></label>
          <input id="listing_url" data-wa-label="Listing" name="listing_url" type="url" inputmode="url" maxlength="500" placeholder="https://" value="<?= e(old($old, 'listing_url')) ?>" class="mt-1"<?= field_attrs($errors, 'listing_url') ?>>
          <?= field_error($errors, 'listing_url') ?>
        </div>
        <div>
          <label for="notes">Anything else we should know? <span class="font-normal text-muted">(optional)</span></label>
          <textarea id="notes" data-wa-label="Notes" name="notes" rows="4" maxlength="2000" class="mt-1"<?= field_attrs($errors, 'notes', true) ?>><?= e(old($old, 'notes')) ?></textarea>
          <p id="notes-hint" class="hint">Budget, preferred colour, when you need it, or questions.</p>
          <?= field_error($errors, 'notes') ?>
        </div>
      </fieldset>

      <p class="text-sm text-muted">We use these details only to prepare your quote and contact you. See our <a href="/privacy">privacy notice</a>.</p>
      <?php $waBase = whatsapp_url(); ?>
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <button type="submit" class="btn-primary w-full sm:w-auto" data-busy="Sending...">Send quote request</button>
        <?php if ($waBase !== ''): ?>
          <button type="button" class="btn-secondary w-full sm:w-auto" data-wa-send="<?= e($waBase) ?>" data-wa-intro="Hello Atlantic Auto And Shipping Services, I would like an import quote." hidden>
            <img src="<?= e(asset('img/brands/whatsapp.svg')) ?>" alt="" width="20" height="20" class="h-5 w-5"> Send on WhatsApp
          </button>
        <?php endif; ?>
      </div>
      <?php if ($waBase !== ''): ?>
        <p class="hidden text-xs text-muted" data-wa-note>Send on WhatsApp opens WhatsApp with your details filled in. Check them, then press send in WhatsApp.</p>
      <?php endif; ?>
    </form>
  </div>
</section>
