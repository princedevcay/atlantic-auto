<?php /** @var array<string, string> $errors @var array<string, string> $labels */ ?>
<?php if (!empty($errors)): ?>
<div class="mb-6 rounded-md border-2 border-danger bg-surface p-5" role="alert" tabindex="-1" data-error-summary>
  <h2 class="flex items-center gap-2 text-lg font-semibold text-danger"><?= icon('circle-alert') ?>Please check <?= count($errors) === 1 ? 'one thing' : count($errors) . ' things' ?></h2>
  <ul class="mt-2 list-disc space-y-1 pl-6">
    <?php foreach ($errors as $field => $msg): ?>
      <li><a href="#<?= e($field === 'services' ? 'svc-sourcing' : $field) ?>" class="text-danger"><?= e($msg) ?></a></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>
<?php if (!empty($notice)): ?>
<div class="mb-6 rounded-md border-2 border-warning bg-surface p-5" role="status">
  <p class="font-medium text-warning"><?= e($notice) ?></p>
</div>
<?php endif; ?>
