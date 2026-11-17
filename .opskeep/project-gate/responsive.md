# Responsive: Atlantic Auto and Shipping Services

Mobile-first with full parity on public pages (PG-011). The admin is desktop-first and usable on a phone for quick stage changes (PG-023).

## Breakpoints
- Base: up to 599px, single column.
- 600px: two-column service cards.
- 768px: mobile action bar hidden; top bar shows the primary button.
- 900px: full top bar links; How It Works timeline goes horizontal.
- 1200px: content max width, three-column cards.

## Navigation
- Under 900px: logo plus menu button; menu opens a full-height sheet.
- Under 768px: sticky bottom bar with both actions on every public page except the two form pages and the tracking page (which has its own footer actions).
- Admin under 768px: side list becomes a top menu; tables become stacked rows showing name, stage and received time.

## Screens on small screens
- Hero: portrait crop of the client image, headline at 32 to 36px, buttons full width and stacked.
- Track box, forms: one column, full-width fields and buttons.
- Timelines: vertical.
- Cost and clearance sections: stacked lists, no tables.

## Touch
Minimum 44 by 44px targets, 8px between adjacent targets, phone and WhatsApp numbers are tap-to-call / tap-to-chat links.

## Slow networks (PG-012)
- Images: `srcset` with AVIF/WebP and JPEG fallback, lazy below the fold, hero preloaded at the right size, every image has fixed dimensions to avoid layout shift.
- Fonts: two families, subset, `font-display: swap`.
- Public pages work without JavaScript; forms are plain POST.
- Submit failures keep entered data and show a retry.
- Target: home page under 500KB on first load at mobile width, excluding the hero image beyond its mobile size.
