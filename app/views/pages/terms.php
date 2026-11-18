<?php
// Terms of Use. Change "Last updated" whenever the content changes.
$updated = '18 November 2026';
?>
<?= App\Core\View::fetch('partials/page-head', ['eyebrow' => 'Legal', 'heading' => 'Terms of Use', 'lead' => 'The terms that apply when you use the Atlantic Auto And Shipping Services website.']) ?>
<section class="section">
  <div class="container-x prose-plain max-w-3xl">
    <p class="text-sm font-semibold text-ink">Last updated: <?= e($updated) ?></p>

    <nav aria-label="On this page" class="mt-6 rounded-md border border-line bg-surface p-5 text-sm">
      <p class="font-semibold text-ink">On this page</p>
      <ol class="mt-2 grid list-decimal gap-1 pl-5 sm:grid-cols-2">
        <?php foreach (['about' => 'About these terms', 'info' => 'Information on this website', 'cars' => 'Cars for sale', 'quotes' => 'Quotes and import services', 'payments' => 'Payments and fraud warning', 'updates' => 'Vehicle updates', 'chat' => 'Website chat', 'use' => 'Acceptable use', 'ip' => 'Our content and trademarks', 'links' => 'Other websites', 'liability' => 'Our responsibility to you', 'law' => 'Governing law', 'changes' => 'Changes to these terms', 'contact' => 'Contact us'] as $id => $label): ?>
          <li><a href="#<?= e($id) ?>"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ol>
    </nav>

    <h2 id="about" class="mt-10 text-2xl font-bold">1. About these terms</h2>
    <p>This website is run by Atlantic Auto And Shipping Services ("we", "us"), a Ghanaian company that sells vehicles and helps customers import vehicles from the United States. These terms apply whenever you use this website. By using it, you agree to them. If you do not agree, please do not use the website.</p>

    <h2 id="info" class="mt-10 text-2xl font-bold">2. Information on this website</h2>
    <p>The information on this website is a general guide to our vehicles and services. We work to keep it accurate and up to date, but it is not a quote, an offer or professional advice about your situation. Shipping times, duties, taxes and fees depend on each vehicle and on rules set by others, and can change.</p>

    <h2 id="cars" class="mt-10 text-2xl font-bold">3. Cars for sale</h2>
    <ul class="mt-3 list-disc space-y-1 pl-6 text-muted">
      <li>Listings, prices and availability can change at any time. A car is only yours once we confirm the sale in writing and the agreed payment is received.</li>
      <li>Unless a listing says otherwise, prices are for the vehicle only. Shipping, duty, clearance and delivery may be extra; ask us what a price includes before you decide.</li>
      <li>Photos show the vehicle or a vehicle of the same type. Read the description and ask us any questions before you buy.</li>
      <li>Listings marked "Sample" are examples that show how our listings work. They are not vehicles for sale, and their prices are not offers.</li>
      <li>Mileage and other details come from the vehicle's records and the seller. We check what we reasonably can, and we will tell you about anything we know that affects the vehicle.</li>
    </ul>

    <h2 id="quotes" class="mt-10 text-2xl font-bold">4. Quotes and import services</h2>
    <p>Sending a quote or update request does not create a contract. We will contact you to confirm details and send you a quote. Any purchase or import service is agreed separately with you in writing, including what is included, the price and the payment terms. Duties and charges set by Ghana's customs and port authorities are decided by them and can change before your vehicle arrives.</p>

    <h2 id="payments" class="mt-10 text-2xl font-bold">5. Payments and fraud warning</h2>
    <p>This website does not take payments. We will only ask you to pay through the payment details our team confirms to you in writing for your purchase or import. If anyone asks you to pay into a different account, or claims to be us from an unfamiliar number or email, do not pay: contact us first using the details on our <a href="/contact">contact page</a>.</p>

    <h2 id="updates" class="mt-10 text-2xl font-bold">6. Vehicle updates</h2>
    <p>Our team tracks each vehicle and updates you through Request a Vehicle Update, WhatsApp or phone. Dates and arrival times we share are our best information at the time; shipping lines, ports and authorities can cause delays outside our control.</p>
    <?php if (customer_tracking()): ?>
    <p>Your tracking link and reference are private. Do not share them with anyone you do not want to see your vehicle's progress. Tracking shows stages updated by our team and the shipping line; it is not live GPS.</p>
    <?php endif; ?>

    <h2 id="chat" class="mt-10 text-2xl font-bold">7. Website chat</h2>
    <p>The chat on this website gives general answers to common questions. It is not advice about your specific vehicle or purchase. For anything about your own vehicle, contact our team.</p>

    <h2 id="use" class="mt-10 text-2xl font-bold">8. Acceptable use</h2>
    <p>You must not misuse this website: for example, by trying to access information about other customers, sending false information or spam through our forms, introducing harmful code, or interfering with how the website works. We may block access for anyone who misuses it.</p>

    <h2 id="ip" class="mt-10 text-2xl font-bold">9. Our content and trademarks</h2>
    <p>The text, design, photos and logo on this website belong to Atlantic Auto And Shipping Services or are used with permission. You may view and share pages for personal use, but you may not copy or reuse our content for commercial purposes without our written permission.</p>
    <p>Vehicle brand names and logos shown on this website belong to their owners and are used only to describe the vehicles we sell and import. We are not an authorised dealer of, or connected to, these manufacturers.</p>

    <h2 id="links" class="mt-10 text-2xl font-bold">10. Other websites</h2>
    <p>This website links to other services, such as WhatsApp. We are not responsible for their content or how they handle your information; their own terms apply.</p>

    <h2 id="liability" class="mt-10 text-2xl font-bold">11. Our responsibility to you</h2>
    <p>We provide this website as it is and work to keep it available and secure, but we cannot promise it will always be available or free of errors. To the extent the law allows, we are not responsible for losses that come from relying on general information on this website rather than on the written agreement for your purchase or import. Nothing in these terms limits any responsibility we cannot limit under Ghanaian law, or your rights as a consumer.</p>

    <h2 id="law" class="mt-10 text-2xl font-bold">12. Governing law</h2>
    <p>These terms are governed by the laws of the Republic of Ghana, and the courts of Ghana deal with any dispute about them.</p>

    <h2 id="changes" class="mt-10 text-2xl font-bold">13. Changes to these terms</h2>
    <p>We may update these terms from time to time. The date at the top shows when they were last updated. The version on this page when you use the website is the one that applies.</p>

    <h2 id="contact" class="mt-10 text-2xl font-bold">14. Contact us</h2>
    <p>Questions about these terms? Contact Atlantic Auto And Shipping Services using the details on our <a href="/contact">contact page</a>. How we handle your information is explained in our <a href="/privacy">Privacy Policy</a>.</p>
  </div>
</section>
