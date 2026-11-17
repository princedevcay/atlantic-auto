# Screens: Atlantic Auto and Shipping Services

Site map from PG-018. Public pages share the top bar (logo, Services, How It Works, Track, About, Contact, "Request an Import Quote" button) and the navy footer with contact details. On mobile the top bar collapses to logo plus menu, and a sticky bottom bar holds both actions.

## Public

### Home (landing page, PG-029)
Purpose: turn a first-time visitor into a quote request, and route existing customers to tracking or an update request. Follows the owner's 17 Nov mockup:

1. **Hero**: navy band with client photo on the right, headline "Import Your Vehicle from the USA", supporting line, Request an Import Quote and Request a Vehicle Update.
2. **Track bar**: one-row card overlapping the hero: title, tracking reference, last 4 phone digits, Track Vehicle.
3. **Services**: six photo cards, Sourcing, Purchase, Pickup, Shipping, Clearance, Delivery.
4. **Six stages**: icon stepper joined by a line.
5. **Why Atlantic**: four points with round icons.
6. **What we import**: five cards with real brand logo, photo, type and example model (Toyota Camry, Honda CR-V, Ford Ranger, Mercedes-Benz Sprinter, Isuzu NPR), then a strip: US auctions, Dealers, Private sellers, Vehicles you already own.
7. **What your cost is made of**: five parts with icons, no figures.
8. **Clearing at Tema**: photo, "We take care of" and "What you provide" checklists.
9. **Recent deliveries and testimonials**: hidden until the client supplies real content.
10 to 12. **One row**: FAQ accordion, About Us card (photo, text, both buttons), Contact card.

States: missing photos show pale panels with an icon; section 9 absent until content exists.

### Services
The six steps in more depth, each with what Atlantic does and what the customer does. Ends with the quote button.

### How It Works
Full step timeline (PG-019, PG-021 stages), what the customer hears at each stage, and a short note that updates come from the team and the carrier.

### Request an Import Quote (PG-006)
Single-page form: name, phone/WhatsApp (+233), email (optional), services needed (checkboxes for the six steps), vehicle year/make/model, where it is in the USA (auction, dealer, private) with listing link, notes. States: empty, field errors, submitting, submit failed with retry (form content kept), success with reference number and "Continue on WhatsApp".

### Request a Vehicle Update (PG-020)
Form: name, phone/WhatsApp, VIN or tracking reference, question. Success screen shows reference, a link to the tracking page if one matches, and "Continue on WhatsApp".

### Track Your Vehicle (PG-022)
Lookup: tracking reference plus last 4 phone digits. Errors are generic ("We could not find a vehicle with those details") so they never confirm whether a reference exists.

### Tracking page (PG-021, PG-024)
Opened from a private link or the lookup. Header: vehicle (year, make, model), reference, last updated. Vertical six-stage timeline with dates and team notes; Shipped stage shows vessel, ETA Tema and container events from the carrier with their time. Photos if added. Footer: Request a Vehicle Update and WhatsApp. States: carrier feed unavailable shows last known data with its time.

### About Us, Contact, Privacy
About: supplied copy. Contact: phone, WhatsApp, office address and map link, email, hours (client to supply). Privacy: what the forms collect and why.

## Staff admin (PG-023, PG-025)

- **Sign in**: staff only.
- **Inbox**: one list with tabs All, Import Quotes, Vehicle Updates and new counts. Row: type, name, phone, vehicle or reference, received time, status (New, In progress, Done). Detail drawer with full submission and "Reply on WhatsApp".
- **Shipments list**: table filtered by stage, search by reference, VIN or name. "New shipment" or "Create from quote".
- **Shipment detail**: customer and vehicle info, stage control (one tap to advance), notes, photo upload, carrier booking/BL/container number, carrier feed status, copy private link.

Entry and exit: every public page reaches both forms in one tap; every form ends on a success screen that links to WhatsApp and Home.
