# DESIGN.md: Atlantic Auto and Shipping Services

Read this before touching any UI in this project. It states decisions, not options.

## Product

Atlantic Auto and Shipping Services is a Ghanaian company that imports vehicles from the USA into Ghana and handles sourcing, purchase, pickup, shipping, clearance at Tema and delivery (PG-019, PG-026). The website has two jobs: let prospective buyers **Request an Import Quote** and let existing customers **Request a Vehicle Update** (PG-003). Behind it sits a small staff admin where the team keeps a tracking page for each vehicle current, with vessel and ETA data pulled from the carrier (PG-021, PG-023, PG-024). Customers are occasional, mostly first-time importers on phones, often on mobile data (PG-010, PG-011, PG-012).

## Direction: Harbour Trust

*The mockup, sharpened around the two actions.*

Navy hero and footer bands, teal action buttons, white photo cards on a pale blue-grey page, as in the supplied mockup and logo (PG-013). It fits because the client already likes this look, it reads as a dependable logistics firm, and it is the fastest to build.

It is deliberately **not** a scroll-story microsite (direction C, rejected as too heavy on mobile data), not a bare utility page (direction B, rejected as too plain), and not a general freight site: there is no pickup/destination planner from the mockup (PG-004).

## Foundation

| Area | Decision |
|---|---|
| Theme | Light page, navy hero band and navy footer band |
| Navigation | Top bar on public pages; sticky bottom action bar on mobile; simple left list in the staff admin |
| Content width | Centered, 1200px max |
| Density | Comfortable public site; 44px rows in the admin |
| Corner radius | `radius.md` cards and inputs, `radius.sm` chips, `radius.lg` large panels |
| Typography | Outfit for headings, Inter for body and UI |
| Primary interaction | Two calls to action leading to short single-page forms; stage timeline on tracking pages |
| Mobile strategy | Mobile-first with full parity on the public site; admin desktop-first, usable on a phone for stage changes |
| Animation | Subtle fades only, off under reduced motion |
| Icons | Lucide |

## Rules

1. Every public page offers **Request an Import Quote** (primary, teal filled) and **Request a Vehicle Update** (secondary, outlined). Never more than one filled primary button per view. (PG-003)
2. Scope is USA to Ghana. Never present the company as shipping between arbitrary places, and never add a pickup/destination planner. (PG-004, PG-026)
3. The landing page has exactly the 12 sections in `screens.md`, in that order. (PG-027)
4. Never publish prices, duty rates, transit times, customer counts or reviews the client has not supplied. Cost and clearance sections explain components only. Testimonials stay hidden until real content exists. (PG-015, PG-027, AS-08)
5. Phone numbers are shown and validated in +233 format; WhatsApp links use `wa.me` with the client's number. No placeholder contact details ship. (PG-015, PG-026)
6. Forms are one column on mobile, labels above fields, inline errors, and a thank-you screen with a reference number and a prefilled "Continue on WhatsApp" link. (PG-006, PG-008, PG-017, PG-020)
7. Select boxes use `appearance:none`, about 42px right padding and one teal 16px chevron at `right 14px center` (studio rule). (PG-017)
8. Tracking pages show the six stages Sourcing, Purchased, Picked up, Shipped, Clearing, Delivered as a vertical timeline, current stage highlighted, "last updated" always visible. (PG-021, PG-024)
9. A tracking page opens only from a private link, or tracking reference plus last 4 phone digits. VIN alone never opens one. (PG-022)
10. Copy says stages are updated by the Atlantic team and the carrier; never imply live GPS. (R-01)
11. The admin inbox is one inbox with tabs All, Import Quotes, Vehicle Updates, each with a new-item count. Notification email subjects start with `[Import Quote]` or `[Vehicle Update]`. (PG-025)
12. All landing page and hero images are supplied by the client. Do not generate or use stock images. Until a file arrives, its slot is a plain navy or pale panel. No car brand logos as decoration; third-party marks (WhatsApp, carriers) use official files only. (PG-014)
13. Pages stay lean: responsive compressed images (AVIF/WebP with JPEG fallback), no heavy client frameworks on public pages, forms submit without JavaScript. (PG-012)
14. No em dashes in any copy, UI string or code comment (studio rule).

## Do and don't

- **Do** keep the hero to the supplied headline, one supporting line and two buttons. **Don't** add a carousel or a third button.
- **Do** describe what makes up the cost and what the team handles at Tema. **Don't** add a duty calculator or sample figures.
- **Do** keep tracking text plain ("Shipped: on the vessel, ETA Tema 12 Dec"). **Don't** use a map or moving-vehicle animation that suggests live GPS.

## Tokens

All colours, radii, fonts and spacing come from `tokens.json` in this folder. Do not restate or invent values; if a token is missing, reopen the gate.

## Change process

Do not edit these files by hand. Reopen the affected decision with `python scripts/gate.py reopen --id PG-xxx --reason "<why>"`, get it answered on the dashboard, update the affected docs and re-seal.
