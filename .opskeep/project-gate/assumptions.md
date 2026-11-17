# Atlantic Auto and Shipping Services: Assumptions

Assumptions the builder saw but did not individually confirm. If one is wrong, reopen it:
`gate.py reopen --id PG-xxx --reason "..."`

- **AS-03** The team handles requests by email and WhatsApp; no CRM integration in v1.
- **AS-04** English only.
- **AS-05** No online payment in v1; purchase payments are handled offline.
- **AS-06** Phone, WhatsApp, office address and email will be supplied by the client before launch.
- **AS-07** Constraint has no measured evidence yet; it is based on the owner's description.
- **AS-08** No prices, duty rates or transit times are published until the client supplies them; cost and clearance sections explain components only.
- **AS-09** The shared inbox address will be supplied with the other contact details before launch.
- **AS-10** Client-supplied images arrive before launch, with the right to use them; the hero needs at least one wide image (about 2400px) and a portrait crop for phones.

## Decisions confirmed by gate approval (not individually reviewed)

- **PG-001 Constraint**: Prospects cannot start a USA import quote online, and existing customers have no clear way to ask where their vehicle is, so both land as ad hoc calls and WhatsApp messages that are slow to answer and easy to lose.
- **PG-002 The bet**: Customers will use simple online forms if they are quicker than calling; several weeks of build; watch quote requests per week and update requests that arrive with a VIN or reference.
- **PG-006 Import quote form**: Short form: name, phone (WhatsApp), email optional, vehicle year/make/model, where it is in the USA (auction, dealer or private, with a listing link if any), destination, and notes. No payment on the site.
- **PG-008 Where submissions go**: Each form sends a notification to the team's email, shows a reference number on the thank-you screen, and offers a 'Continue on WhatsApp' link with the details prefilled.
- **PG-010 Audience**: First-time and repeat importers, mostly non-experts, visiting a few times around a purchase.
- **PG-011 Mobile-first**: Designed for phones first, with full parity on desktop. Sticky bottom bar on mobile with both actions.
- **PG-012 Slow-network tolerance**: Lean pages: compressed responsive images, no heavy scripts, forms work with JavaScript off, clear retry on failure.
- **PG-016 WCAG 2.2 AA baseline**: Keyboard operable, visible focus, 4.5:1 text contrast, labelled form fields with inline errors, 44px touch targets.
- **PG-017 Forms style**: Large labelled inputs, custom select chevron in teal (studio rule), one column on mobile, progress-free single page.
- **PG-018 Site map (v2)**: Public: Home, Services, How It Works, Request an Import Quote, Request a Vehicle Update, Track Your Vehicle, About Us, Contact, Privacy. Staff: sign-in, shipments list, shipment detail.
- **PG-022 How customers open their tracking page**: Two ways: a private link the team sends on WhatsApp, or the Track Your Vehicle page with tracking reference plus last 4 digits of the phone on file. No customer accounts. VIN alone never opens a page.
- **PG-023 Team admin area**: Staff sign in to a simple admin: list of shipments with stage filters, create a shipment from a quote, change stage, add notes and photos, enter carrier booking or container number, see incoming quote and update requests. Desktop-first, works on a phone for quick stage changes.
- **PG-024 Carrier integration**: Pull vessel name, ETA and container milestones automatically from the carrier (or a container-tracking service covering it) using the booking, bill of lading or container number. Staff can always override, and the page shows when it last updated. Which carriers and which API are chosen at build after a lookup, not guessed.
- **PG-027 Landing page sections**: 1 Hero (supplied copy, both buttons). 2 Track Your Vehicle quick box. 3 Services: the six steps as cards. 4 How it works: USA lot to your door in Ghana timeline. 5 Why Atlantic: Ghanaian team, one contact throughout, updates at every stage, paperwork handled (no invented figures). 6 What we import: cars, SUVs, pickups and trucks from auctions, dealers and private sellers. 7 What your cost is made of: purchase, US towing, ocean freight, Tema duty and clearance, delivery (explained, no rates). 8 Clearing at Tema: what we handle and documents you need. 9 Recent deliveries and testimonials (shown only once real ones are supplied). 10 FAQ. 11 About Us (supplied copy). 12 Closing call to action with phone, WhatsApp, office and email.
