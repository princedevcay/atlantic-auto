<?= App\Core\View::fetch('partials/page-head', ['eyebrow' => 'Terms', 'heading' => 'Terms of Use']) ?>
<section class="section">
  <div class="container-x prose-plain max-w-3xl">
    <!-- Draft for the client to confirm before launch. Not legal advice. -->
    <div class="mb-8 rounded-md border-2 border-warning bg-surface p-4 text-sm text-warning" role="note">Draft terms of use. Atlantic Auto will confirm this text before the site goes live.</div>
    <h2 class="text-2xl font-bold">About this website</h2>
    <p>This website is run by Atlantic Auto And Shipping Services, a company based in Ghana that helps customers import vehicles from the USA. By using the site you agree to these terms.</p>
    <h2 class="mt-8 text-2xl font-bold">Information on the site</h2>
    <p>The information here is a general guide to our services. It is not a quote or an offer. Prices, duty, taxes and timings depend on each vehicle and are confirmed only in a written quote from our team.</p>
    <h2 class="mt-8 text-2xl font-bold">Quote and update requests</h2>
    <p>Sending a request does not create a contract. We will contact you to confirm details. Any import service is agreed separately in writing, including payment terms.</p>
    <?php if (customer_tracking()): ?>
    <h2 class="mt-8 text-2xl font-bold">Tracking pages</h2>
    <p>Your tracking link and reference are private. Do not share them with people you do not want to see your vehicle's progress. Stages are updated by our team and, while at sea, by information from the shipping line. They are not live GPS and may change.</p>
    <?php endif; ?>
    <h2 class="mt-8 text-2xl font-bold">Website chat</h2>
    <p>The chat on this site gives general answers to common questions. It is not advice about your specific vehicle. For anything about your own import, contact our team.</p>
    <h2 class="mt-8 text-2xl font-bold">Acceptable use</h2>
    <p>Do not misuse the site, try to access other customers' information, send spam through our forms, or interfere with how the site works.</p>
    <h2 class="mt-8 text-2xl font-bold">Trademarks</h2>
    <p>Vehicle brand names and logos shown on this site belong to their owners and are used only to describe the kinds of vehicles we import. We are not an authorised dealer of these brands.</p>
    <h2 class="mt-8 text-2xl font-bold">Privacy</h2>
    <p>How we use your information is explained in our <a href="/privacy">privacy notice</a>.</p>
    <h2 class="mt-8 text-2xl font-bold">Contact</h2>
    <p>Questions about these terms? Use the details on our <a href="/contact">contact page</a>.</p>
  </div>
</section>
