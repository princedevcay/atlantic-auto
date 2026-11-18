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
- **PG-018 Site map (v2)**: Public: Home, Cars for Sale, car detail pages, Services, How It Works, Request an Import Quote, Request a Vehicle Update, About Us, Contact, Privacy. Staff: sign-in, shipments list, shipment detail.
- **PG-024 Carrier integration**: Pull vessel name, ETA and container milestones automatically from the carrier (or a container-tracking service covering it) using the booking, bill of lading or container number, shown to staff in the admin. Staff can always override. Which carriers and which API are chosen at build after a lookup, not guessed.
- **PG-031 Cars for Sale pages**: New 'Cars for Sale' menu item. /cars lists published cars as photo cards (photo, year, make, model, price, status badge, key specs) with filters for availability and body type. /cars/{reference} is the detail page: photo gallery, price, status, specs (year, make, model, body type, mileage, transmission, fuel, engine, colour, where the car is now: in the USA, in transit or in Ghana), description, and buttons for 'I'm interested' and WhatsApp. The home page gets a 'Featured cars for sale' section directly after the service cards: up to six available cars that staff marked as featured (newest available cars if none are featured), with a 'See all cars' link; hidden when no car is published.
- **PG-032 Prices and availability**: Each car has a price amount and currency (GHS by default, USD allowed per car) or 'Price on request'. Status is Available, Reserved or Sold, shown as a badge. Sold and Reserved cars stay listed with their badge and no enquiry button until staff hide them. Prices are entered by staff; the site never calculates or estimates prices.
- **PG-033 Car management in the admin**: New 'Cars' section in the staff admin: list with status and visibility filters; add and edit a car (all fields above); upload up to 12 photos and choose the main one; change status in one click; mark a car as Featured for the home page; Publish or Hide (hidden cars disappear from the site immediately but keep their data); delete is Admin only with typed confirmation. Every change is in the audit log.
- **PG-034 Car enquiries**: 'I'm interested' on an available car opens a short form (name, phone or WhatsApp, optional message) with the same spam protection as the other forms. It lands in the inbox under Car Enquiries, linked to the car, emails the shared inbox as [Car Enquiry], and the thank-you screen offers 'Continue on WhatsApp' with the car's reference.
