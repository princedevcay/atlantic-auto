<h1 class="text-2xl font-bold">Audit log</h1>
<p class="mt-1 text-sm text-muted">The last 300 security-relevant actions. Kept for 12 months.</p>
<div class="card mt-5 overflow-x-auto">
  <table class="admin-table w-full">
    <thead><tr><th scope="col">When</th><th scope="col">Who</th><th scope="col">Action</th><th scope="col">Target</th><th scope="col">Details</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="whitespace-nowrap"><?= e(fmt_date((string) $r['created_at'], 'j M Y, g:i:sa')) ?></td>
          <td><?= e($r['staff_name'] ?? '-') ?></td>
          <td class="font-mono text-xs"><?= e($r['action']) ?></td>
          <td class="text-xs"><?= $r['target_type'] ? e($r['target_type'] . ' ' . $r['target_id']) : '-' ?></td>
          <td class="text-xs text-muted"><?= e($r['details'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
