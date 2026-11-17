# Atlantic Auto and Shipping Services: Project brief

Website and tracking for a USA vehicle importer: import quotes, vehicle update requests, and team-managed tracking pages fed by the carrier.

## The problem

**Constraint:** Prospects cannot start a USA import quote online, and existing customers have no clear way to ask where their vehicle is, so both land as ad hoc calls and WhatsApp messages that are slow to answer and easy to lose.

**Evidence:** Owner's description of current enquiries (see AS-07).

**Forces on the people served**

- Push: Importing a car from the USA feels opaque: unclear steps, costs and where the car is right now.
- Pull: A quick import quote request and a simple way to ask for a vehicle update.
- Anxiety: Trusting an importer with an expensive vehicle and a large payment, and not hearing anything once it ships.
- Habit: Calling or WhatsApping the team, or an agent they know, and waiting for a reply.

**Already working or existing:** Logo (navy and teal); Homepage mockup; Homepage and About Us copy

## The bet

We believe customers will use simple online forms if they are quicker than calling, so we are betting several weeks that this produces more import quote requests captured and vehicle update requests that arrive complete and get answered faster. We will watch quote requests per week, and share of update requests with a VIN or reference.

**Outcomes**

- More import quote requests are captured
- Vehicle update requests arrive complete and are answered faster

## Paths considered and rejected

- **Two Doors: Utility first: pick your door, get it done.**: Plainer than the mockup the client already saw; trust has to come from words alone.
- **The Crossing: Tell the journey from US lot to home port.**: Heaviest on slow phone connections and needs the most imagery and build time for a site whose job is two short forms.

## Intent

- **Product:** Lead-capture website with customer tracking pages and a small team admin
- **Primary users:** Prospective importers, existing customers, and Atlantic staff
- **Usage:** Customers occasionally on phones; staff daily
- **Primary jobs:** Request a quote; request an update; check a vehicle's stage; staff keep stages current
- **Platform:** Responsive web, mobile-first public site, desktop-first admin

## Product character

- Trustworthy: 95%
- Friendly: 75%
- Clear: 90%
- Expressive: 40%

## Chosen direction: Harbour Trust

The mockup, sharpened around the two actions.

Keeps what you already liked in the mockup (navy hero, teal buttons, photo cards) and refocuses it on USA imports and the two requests. Lowest risk and fastest to build.

## Users & jobs

- **PG-010 Audience**: First-time and repeat importers, mostly non-experts, visiting a few times around a purchase.

## Project

- **PG-003 Two primary actions**: Every page leads to Request an Import Quote (primary) and Request a Vehicle Update (secondary).
- **PG-004 USA import scope**: The site is about importing vehicles from the USA, from point of sale through shipping. The mockup's general pickup/destination planner is dropped.
- **PG-019 Full-service scope**: Atlantic handles sourcing, purchase, pickup, shipping, clearance and delivery. Services page and How It Works present these six steps, and customers can ask for any or all of them in the quote form.
- **PG-026 Ghanaian company, Ghana market**: Atlantic is a Ghanaian company importing vehicles from the USA into Ghana. Copy speaks to Ghanaian buyers, arrival and clearance are at Tema port, phone numbers use +233 format, WhatsApp is the main contact channel, and photography shows Ghanaian people and Tema rather than generic foreign ports.

## Risks

- (med) Forms collect names, phones and VINs: personal data that needs spam protection and safe handling (security gate).
- (low) No real photos, testimonials or business details supplied yet; trust content may launch thin.
- (high) Carrier APIs vary: some carriers have none, and tracking services may charge. Costs and coverage must be looked up before build.
- (high) Tracking pages and the admin hold customer, vehicle and payment-stage data; access control is the main job of the security gate.
- (med) Tracking pages go stale if staff do not update stages; the admin must make updates take seconds.
- (low) Customers may expect live GPS; copy must say stages are updated by the team and the carrier.
- (med) Ghana duty and clearance rules change; the clearance section must avoid figures and link to the team for current costs.

