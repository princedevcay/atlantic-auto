// Copies self-hosted fonts, Lucide icons and the official WhatsApp glyph into public/assets.
// Runs in CI before the Tailwind build; nothing here is loaded from a CDN at runtime.
import { mkdirSync, copyFileSync, readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const nm = (...p) => join(root, 'node_modules', ...p);
const out = (...p) => join(root, 'public', 'assets', ...p);

const copy = (from, to) => { mkdirSync(dirname(to), { recursive: true }); copyFileSync(from, to); };

// Fonts (variable, Latin subset)
copy(nm('@fontsource-variable/inter/files/inter-latin-wght-normal.woff2'), out('fonts', 'inter-latin-wght-normal.woff2'));
copy(nm('@fontsource-variable/outfit/files/outfit-latin-wght-normal.woff2'), out('fonts', 'outfit-latin-wght-normal.woff2'));

// Lucide icons used by the views (app/Core/Icon.php inlines them)
const icons = [
  'search', 'shopping-cart', 'truck', 'ship', 'file-check', 'house', 'check', 'chevron-down', 'chevron-right',
  'arrow-right', 'phone', 'mail', 'map-pin', 'clock', 'menu', 'x', 'shield-check', 'users', 'refresh-cw',
  'circle-check', 'circle-alert', 'inbox', 'package', 'log-out', 'upload', 'link', 'copy', 'trash-2',
  'user-plus', 'lock', 'car', 'anchor', 'image', 'info', 'file-text', 'smartphone', 'headset', 'bell',
  'coins', 'clipboard-list', 'car-front', 'bus',
];
for (const name of icons) {
  const svg = readFileSync(nm('lucide-static', 'icons', `${name}.svg`), 'utf8')
    .replace(/<!--[\s\S]*?-->/g, '')
    .replace(/\s(width|height)="24"/g, '')
    .replace(/class="[^"]*"/, '')
    .trim();
  mkdirSync(out('icons'), { recursive: true });
  writeFileSync(out('icons', `${name}.svg`), svg + '\n');
}

// Official WhatsApp glyph from simple-icons (see brand/LOGOS.md)
copy(nm('simple-icons', 'icons', 'whatsapp.svg'), out('img', 'brands', 'whatsapp.svg'));

// Car brands shown on "What we import" (owner's request, PG-028). Official one-colour glyphs in each brand's colour.
const si = await import('simple-icons');
for (const [slug, key] of [['toyota', 'siToyota'], ['honda', 'siHonda'], ['ford', 'siFord']]) {
  const svg = readFileSync(nm('simple-icons', 'icons', `${slug}.svg`), 'utf8').replace('<svg ', `<svg fill="#${si[key].hex}" `);
  writeFileSync(out('img', 'brands', `${slug}.svg`), svg);
}
// Mercedes-Benz and Isuzu are not in simple-icons; official files from Wikimedia Commons live in brand/logos/.
copy(join(root, 'brand', 'logos', 'mercedes-benz.svg'), out('img', 'brands', 'mercedes-benz.svg'));
copy(join(root, 'brand', 'logos', 'isuzu.svg'), out('img', 'brands', 'isuzu.svg'));

console.log(`assets copied: 2 fonts, ${icons.length} icons, 6 brand logos`);
