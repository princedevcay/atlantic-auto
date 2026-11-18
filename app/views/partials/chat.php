<?php
use App\Models\Chat;

$online = Chat::isOnline();
$wa = whatsapp_url('Hello Atlantic, I have a question about importing a vehicle.');
?>
<div class="fixed right-4 z-50 flex flex-col items-end gap-3 md:bottom-6 md:right-6 <?= !empty($hideBar) ? 'bottom-4' : 'bottom-20' ?>" data-chat>
  <section id="chat-panel" hidden class="flex h-[min(40rem,calc(100dvh-10rem))] w-[26rem] max-w-[calc(100vw-2rem)] flex-col overflow-hidden rounded-lg border border-line bg-surface shadow-[0_12px_40px_rgb(12_42_69/0.25)]" role="dialog" aria-labelledby="chat-title" data-chat-panel>
    <header class="on-navy flex items-center gap-3 bg-navy px-4 py-3 text-white">
      <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white">
        <img src="<?= e(asset('img/favicon.png')) ?>" alt="" width="64" height="64" class="h-7 w-7">
        <span class="absolute -bottom-0.5 -right-0.5 h-3.5 w-3.5 rounded-full border-2 border-navy <?= $online ? 'bg-[#22c55e]' : 'bg-[#94a3b8]' ?>" aria-hidden="true"></span>
      </span>
      <div class="min-w-0 flex-1">
        <h2 id="chat-title" class="text-lg font-semibold text-white">Atlantic Auto</h2>
        <p class="flex items-center gap-1.5 text-sm text-white/80">
          <span class="h-2 w-2 rounded-full <?= $online ? 'bg-[#22c55e]' : 'bg-[#94a3b8]' ?>" aria-hidden="true"></span>
          <?= $online ? 'Online now' : 'Offline. We reply as soon as we are back.' ?>
        </p>
      </div>
      <button type="button" class="rounded-md p-1.5 text-white/80 hover:bg-white/10 hover:text-white" aria-label="Close chat" data-chat-close><?= icon('x') ?></button>
    </header>

    <div class="flex-1 space-y-3 overflow-y-auto bg-bg px-4 py-4" aria-live="polite" data-chat-log>
      <div class="flex gap-2">
        <div class="max-w-[85%] rounded-lg rounded-tl-sm bg-surface px-4 py-3 text-[15px] leading-relaxed shadow-card">
          Hello! Welcome to Atlantic Auto And Shipping Services. How can we help with your vehicle import today?
        </div>
      </div>
    </div>

    <div class="border-t border-line bg-surface">
      <p class="px-4 pt-3 text-sm font-semibold text-muted" id="chat-q-label">Popular questions</p>
      <ul class="flex gap-2 overflow-x-auto px-4 pb-3 pt-2" aria-labelledby="chat-q-label">
        <?php foreach (Chat::questions() as $item): ?>
          <li class="shrink-0">
            <button type="button" class="whitespace-nowrap rounded-full border border-primary/40 bg-primary-soft px-4 py-2 text-sm font-medium text-primary hover:border-primary"
              data-chat-q="<?= e($item['q']) ?>" data-chat-a="<?= e($item['a']) ?>" data-chat-link="<?= e($item['link']) ?>" data-chat-label="<?= e($item['label']) ?>"><?= e($item['q']) ?></button>
          </li>
        <?php endforeach; ?>
      </ul>
      <form class="flex items-center gap-2 border-t border-line px-3 py-2" data-chat-form>
        <label for="chat-input" class="sr-only">Type your message</label>
        <input id="chat-input" type="text" maxlength="300" autocomplete="off" placeholder="Type your message..." class="min-h-11 flex-1 rounded-full border-line px-4 py-2 text-[15px]" data-chat-input>
        <button type="submit" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary text-white hover:bg-primary-dark" aria-label="Send"><?= icon('send', 'icon h-4 w-4') ?></button>
      </form>
      <p class="pb-3 text-center text-xs text-muted">Powered by <a href="https://princecaleb.dev" class="font-semibold text-primary no-underline hover:underline" rel="noopener" target="_blank">princecaleb.dev</a></p>
    </div>
    <template data-chat-fallback>
      <span data-text="Thanks for your message. Live chat with our team is coming soon. For now, please <?= $wa !== '' ? 'message us on WhatsApp' : 'send us a request' ?> and we will reply."
            data-link="<?= e($wa !== '' ? $wa : '/contact') ?>" data-label="<?= $wa !== '' ? 'Chat on WhatsApp' : 'Contact us' ?>"></span>
    </template>
  </section>

  <a href="/contact" class="btn-primary rounded-full px-5 shadow-[0_8px_24px_rgb(11_116_128/0.35)]" aria-controls="chat-panel" aria-expanded="false" aria-label="Chat with us" data-chat-open>
    <span class="flex items-center gap-2" data-chat-when-closed><?= icon('message-circle', 'icon h-5 w-5') ?><span>Chat with us</span></span>
    <span class="hidden items-center" data-chat-when-open><?= icon('x', 'icon h-6 w-6') ?></span>
  </a>
</div>
