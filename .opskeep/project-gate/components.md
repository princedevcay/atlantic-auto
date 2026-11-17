# Components: Atlantic Auto and Shipping Services

All values come from `tokens.json`. Character-carrying components are marked with a star.

## Navigation
- **Top bar**: logo left, links, primary button right. `color.surface` background, `color.border` bottom line. Collapses to a menu button under 900px.
- **Mobile action bar** (star): fixed bottom bar under 768px with Request an Import Quote (filled) and Request a Vehicle Update (outlined), 48px tall buttons, safe-area padding. Hidden on form pages.
- **Footer**: navy (`color.text` as background, white text), logo, links, phone, WhatsApp, office, email.
- **Admin side list**: Inbox, Shipments, Sign out. Collapses to a top menu on phones.

## Actions
- **Button**: primary (`color.primary` fill, `color.primary_contrast` text), secondary (outlined `color.primary`), ghost (text). Height 48px public, 40px admin. `radius.md`. States: hover, focus ring, pressed, disabled, loading.
- **WhatsApp button**: official WhatsApp glyph from `public/assets/img/brands/`, label "Continue on WhatsApp".

## Data display
- **Hero band** (star): navy band, client image, headline in Outfit, two buttons. Plain navy if no image yet.
- **Service card**: icon, title, one line, `color.surface`, `radius.md`, soft shadow.
- **Step timeline** (star): numbered steps; horizontal on desktop for How It Works, vertical everywhere for tracking. Current stage in `color.primary`, done stages with a check, future stages muted (`color.muted`).
- **Carrier event list**: time, event, location, source label ("from carrier" or "from Atlantic team").
- **FAQ accordion**: native `details`/`summary`, chevron, keyboard operable.
- **Admin table**: 44px rows (`density.row_height`), sticky header, stage chip, status chip.
- **Chips**: stage and status, `radius.sm`, using `color.success`, `color.warning`, `color.danger` only for real states.

## Forms
- **Text input**, **phone input** (+233 prefix), **textarea**, **checkbox group**, **select** (appearance none, teal chevron, 42px right padding). Label above, hint below, error below in `color.danger` with an icon.
- **Track lookup**: reference and last 4 digits side by side on desktop, stacked on mobile.
- **Photo upload** (admin): JPEG/PNG/WebP, size limit shown.

## Feedback
- **Inline error**, **form error summary** at top on submit failure, **success panel** with reference number, **toast** in admin for saved changes, **last updated** stamp on tracking pages, **empty states** for inbox tabs and shipments.

## Overlays
- **Admin detail drawer** for inbox items. **Mobile menu** sheet. No modals on public pages.
