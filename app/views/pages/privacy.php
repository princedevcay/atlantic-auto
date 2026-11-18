<?php
// Privacy Policy. Keep it true to how the site and the business actually handle data.
// Change "Last updated" whenever the content changes.
$updated = '18 November 2026';
?>
<?= App\Core\View::fetch('partials/page-head', ['eyebrow' => 'Legal', 'heading' => 'Privacy Policy', 'lead' => 'How Atlantic Auto And Shipping Services collects, uses and protects your personal information.']) ?>
<section class="section">
  <div class="container-x prose-plain max-w-3xl">
    <p class="text-sm font-semibold text-ink">Last updated: <?= e($updated) ?></p>

    <nav aria-label="On this page" class="mt-6 rounded-md border border-line bg-surface p-5 text-sm">
      <p class="font-semibold text-ink">On this page</p>
      <ol class="mt-2 grid list-decimal gap-1 pl-5 sm:grid-cols-2">
        <?php foreach (['who' => 'Who we are', 'collect' => 'Information we collect', 'use' => 'How we use it', 'basis' => 'Why we are allowed to use it', 'share' => 'Who we share it with', 'transfers' => 'Information outside Ghana', 'keep' => 'How long we keep it', 'security' => 'How we protect it', 'cookies' => 'Cookies', 'rights' => 'Your rights', 'children' => 'Children', 'changes' => 'Changes to this policy', 'contact' => 'Contact us'] as $id => $label): ?>
          <li><a href="#<?= e($id) ?>"><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ol>
    </nav>

    <h2 id="who" class="mt-10 text-2xl font-bold">1. Who we are</h2>
    <p>Atlantic Auto And Shipping Services ("we", "us") is a Ghanaian company that sells vehicles and helps customers import vehicles from the United States: sourcing, purchase, pickup, shipping, clearance at Tema and delivery in Ghana. We are responsible for the personal information described in this policy, which we handle in line with Ghana's Data Protection Act, 2012 (Act 843).</p>

    <h2 id="collect" class="mt-10 text-2xl font-bold">2. Information we collect</h2>
    <p><strong>When you request an import quote:</strong> your name, phone or WhatsApp number, email address if you give it, the vehicle you want (year, make, model), where the vehicle is, any listing link, the services you need and anything you write in the notes.</p>
    <p><strong>When you request a vehicle update:</strong> your name, phone or WhatsApp number, the vehicle's VIN or the reference we gave you, and your question.</p>
    <p><strong>When you enquire about a car for sale:</strong> your name, phone or WhatsApp number, the car you are interested in and your message.</p>
    <p><strong>When you buy a car or use our import services:</strong> the details we need to complete the purchase, shipping and clearance, such as your identification, your contact details, the vehicle's VIN and title documents, and records of what was agreed and paid. We collect these from you directly when we arrange your purchase or import.</p>
    <p><strong>When you contact us</strong> by WhatsApp, phone or email: the messages you send us.</p>
    <p><strong>Technical information:</strong> to keep the website secure, our systems record limited technical details, such as when a form was sent and a coded (hashed) version of your connection's IP address, used only to block spam and abuse. We do not use advertising or analytics trackers.</p>
    <p>We do not collect payment card details on this website.</p>

    <h2 id="use" class="mt-10 text-2xl font-bold">3. How we use it</h2>
    <ul class="mt-3 list-disc space-y-1 pl-6 text-muted">
      <li>to prepare and explain your quote and answer your questions;</li>
      <li>to sell you a vehicle, and to source, buy, transport, ship, clear and deliver your vehicle;</li>
      <li>to keep you updated about your vehicle at each stage;</li>
      <li>to keep the records a business must keep for accounting, tax and customs;</li>
      <li>to protect our website, our customers and our staff from fraud, spam and misuse.</li>
    </ul>
    <p>We do not sell your personal information, and we do not use it for advertising. We will only send you marketing messages if you have asked us to, and you can ask us to stop at any time.</p>

    <h2 id="basis" class="mt-10 text-2xl font-bold">4. Why we are allowed to use it</h2>
    <p>We use your information because you asked us for a quote, an update or a vehicle, and we need it to do what you asked (to take steps toward, or to carry out, our agreement with you); because the law requires us to keep certain records; because we have a legitimate interest in running and protecting our business and website; or, where we ask for it, because you have given your consent, which you can withdraw at any time.</p>

    <h2 id="share" class="mt-10 text-2xl font-bold">5. Who we share it with</h2>
    <p>We only share the information each party needs:</p>
    <ul class="mt-3 list-disc space-y-1 pl-6 text-muted">
      <li><strong>Our staff</strong>, who sign in to our system with a password and a code from an authenticator app.</li>
      <li><strong>Service providers</strong> who host our website, store our data and deliver our email, under contract and only to provide their service to us.</li>
      <li><strong>Partners needed to complete your purchase or import</strong>, such as US sellers and auctions, transport companies, shipping lines, clearing agents and Ghana's customs and port authorities, when you ask us to handle your vehicle.</li>
      <li><strong>Authorities</strong>, when the law requires it.</li>
    </ul>
    <p>If you choose to message us on WhatsApp, WhatsApp's own terms and privacy policy also apply to those messages.</p>

    <h2 id="transfers" class="mt-10 text-2xl font-bold">6. Information outside Ghana</h2>
    <p>Our website and email are hosted by a provider whose servers may be located outside Ghana, and importing a vehicle from the USA means sharing the necessary details with partners in the United States. When information leaves Ghana, we only share what is needed and use providers that protect it.</p>

    <h2 id="keep" class="mt-10 text-2xl font-bold">7. How long we keep it</h2>
    <ul class="mt-3 list-disc space-y-1 pl-6 text-muted">
      <li><strong>Quote, update and car enquiries</strong> that do not lead to a purchase or import are deleted 12 months after our last contact with you.</li>
      <li><strong>Records of vehicles you buy or import through us</strong> are kept for as long as Ghanaian law requires us to keep business, tax and customs records, and then deleted.</li>
      <?php if (customer_tracking()): ?><li><strong>Tracking links</strong> are switched off 90 days after your vehicle is delivered.</li><?php endif; ?>
      <li><strong>Security logs</strong> are kept for 12 months.</li>
      <li><strong>Backups</strong> are kept for 14 days and then deleted.</li>
    </ul>

    <h2 id="security" class="mt-10 text-2xl font-bold">8. How we protect it</h2>
    <p>Our website only works over an encrypted connection (HTTPS). Access to customer information is limited to our staff, who sign in with a password and a code from an authenticator app, and staff actions are logged. Our systems are kept up to date and checked for security problems. No system is perfectly secure, but if we learn of a breach that puts your information at risk, we will act quickly and tell you and the relevant authorities where the law requires it.</p>

    <h2 id="cookies" class="mt-10 text-2xl font-bold">9. Cookies</h2>
    <p>We use only the cookies the website needs to work: one that keeps a form secure while you fill it in, and, for our staff, one that keeps them signed in. These expire when you close your browser or after a short time, and they are not used to track you. We do not use advertising, analytics or social media cookies.</p>

    <h2 id="rights" class="mt-10 text-2xl font-bold">10. Your rights</h2>
    <p>Under Ghana's Data Protection Act, 2012 (Act 843), you can ask us:</p>
    <ul class="mt-3 list-disc space-y-1 pl-6 text-muted">
      <li>whether we hold information about you, and for a copy of it;</li>
      <li>to correct information that is wrong or incomplete;</li>
      <li>to delete information we no longer need or have no right to keep;</li>
      <li>to stop using your information for a particular purpose, including any marketing.</li>
    </ul>
    <p>To make a request, contact us using the details on our <a href="/contact">contact page</a>. We may ask you to confirm your identity before we act, and we will reply as soon as we can. If you are not satisfied with our answer, you can complain to the Data Protection Commission of Ghana.</p>

    <h2 id="children" class="mt-10 text-2xl font-bold">11. Children</h2>
    <p>Our services are for adults. We do not knowingly collect information from anyone under 18.</p>

    <h2 id="changes" class="mt-10 text-2xl font-bold">12. Changes to this policy</h2>
    <p>We may update this policy when our services or the law change. The date at the top shows when it was last updated. If we make a significant change, we will tell you through the website or, where appropriate, directly.</p>

    <h2 id="contact" class="mt-10 text-2xl font-bold">13. Contact us</h2>
    <p>For any question about this policy or your information, contact Atlantic Auto And Shipping Services using the details on our <a href="/contact">contact page</a>.</p>
  </div>
</section>
