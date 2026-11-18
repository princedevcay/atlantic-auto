// CI dependency gate (CTL-SC-001): fail on any HIGH or CRITICAL npm advisory, except time-limited,
// owner-approved exceptions in .github/audit-exceptions.json. An exception stops counting after its
// expiry date, or as soon as the package has a release newer than max_version (a possible fix).
import { execFileSync } from 'node:child_process';
import { readFileSync } from 'node:fs';

const run = (cmd, args) => {
  try { return execFileSync(cmd, args, { encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'] }); }
  catch (e) { return e.stdout || ''; } // npm audit exits 1 when it finds anything
};
const cmp = (a, b) => {
  const pa = a.split('.').map(Number), pb = b.split('.').map(Number);
  for (let i = 0; i < 3; i++) { if ((pa[i] || 0) !== (pb[i] || 0)) return (pa[i] || 0) - (pb[i] || 0); }
  return 0;
};

const report = JSON.parse(run('npm', ['audit', '--json']) || '{}');
const { exceptions = [] } = JSON.parse(readFileSync(new URL('../.github/audit-exceptions.json', import.meta.url), 'utf8'));
const today = new Date().toISOString().slice(0, 10);

// Collect HIGH/CRITICAL advisories by GHSA id.
const found = new Map();
for (const [pkg, v] of Object.entries(report.vulnerabilities || {})) {
  for (const via of v.via || []) {
    if (typeof via !== 'object' || !['high', 'critical'].includes(via.severity)) continue;
    const id = (via.url || '').split('/').pop();
    found.set(id, { id, pkg: via.name || pkg, severity: via.severity, title: via.title });
  }
}

let failed = false;
for (const adv of found.values()) {
  const ex = exceptions.find((e) => e.id === adv.id);
  if (!ex) { console.log(`::error::${adv.severity.toUpperCase()} ${adv.id} in ${adv.pkg}: ${adv.title}`); failed = true; continue; }
  if (today > ex.expires) { console.log(`::error::Exception for ${adv.id} expired on ${ex.expires}. Fix it or get a new owner approval.`); failed = true; continue; }
  const latest = run('npm', ['view', ex.package, 'version']).trim();
  if (latest && cmp(latest, ex.max_version) > 0) {
    console.log(`::error::${ex.package} ${latest} is newer than ${ex.max_version}. Check whether it fixes ${adv.id}, upgrade, and remove the exception.`);
    failed = true; continue;
  }
  console.log(`::warning::Allowed until ${ex.expires}: ${adv.id} in ${adv.pkg} (${ex.reason.split('.')[0]}).`);
}
for (const ex of exceptions) {
  if (!found.has(ex.id)) console.log(`::notice::Exception ${ex.id} is no longer needed; remove it from .github/audit-exceptions.json.`);
}
console.log(failed ? 'npm audit gate: FAILED' : `npm audit gate: passed (${found.size} high/critical advisory, ${exceptions.length} exception)`);
process.exit(failed ? 1 : 0);
