// Small progressive enhancements. Every page works without this file.
(function () {
  'use strict';

  // Close the mobile menu on Escape and return focus to its button.
  document.addEventListener('keydown', function (ev) {
    if (ev.key !== 'Escape') return;
    document.querySelectorAll('details[data-menu][open]').forEach(function (d) {
      d.removeAttribute('open');
      var s = d.querySelector('summary');
      if (s) s.focus();
    });
  });

  // Mobile menu drawer: close by overlay or close button, and stop the page scrolling behind it.
  document.querySelectorAll('details[data-menu]').forEach(function (d) {
    d.querySelectorAll('[data-menu-close]').forEach(function (el) {
      el.addEventListener('click', function () { d.removeAttribute('open'); d.querySelector('summary').focus(); });
    });
    d.addEventListener('toggle', function () {
      document.documentElement.classList.toggle('overflow-hidden', d.open);
      document.documentElement.classList.toggle('menu-open', d.open);
      if (d.open) { var first = d.querySelector('nav a'); if (first) first.focus(); }
    });
  });

  // Prevent double submits while a form is sending.
  document.addEventListener('submit', function (ev) {
    var form = ev.target;
    if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-chat-form')) return;
    if (form.dataset.sending === '1') { ev.preventDefault(); return; }
    form.dataset.sending = '1';
    form.querySelectorAll('button[type="submit"][data-busy]').forEach(function (b) {
      b.setAttribute('aria-disabled', 'true');
      b.textContent = b.dataset.busy;
    });
  });

  // Typed confirmation for destructive admin actions.
  document.querySelectorAll('[data-confirm-word]').forEach(function (form) {
    var input = form.querySelector('input[name="confirm"]');
    var btn = form.querySelector('button[type="submit"]');
    if (!input || !btn) return;
    var word = form.getAttribute('data-confirm-word');
    var sync = function () { btn.disabled = input.value.trim().toUpperCase() !== word; };
    input.addEventListener('input', sync);
    sync();
  });

  // Copy buttons.
  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var target = document.getElementById(btn.getAttribute('data-copy'));
      if (!target || !navigator.clipboard) return;
      navigator.clipboard.writeText(target.value || target.textContent).then(function () {
        var old = btn.textContent;
        btn.textContent = 'Copied';
        setTimeout(function () { btn.textContent = old; }, 2000);
      });
    });
  });

  // Website chat, phase 1: predefined answers only. Nothing typed is sent anywhere.
  var chat = document.querySelector('[data-chat]');
  if (chat) {
    var panel = chat.querySelector('[data-chat-panel]');
    var openBtn = chat.querySelector('[data-chat-open]');
    var log = chat.querySelector('[data-chat-log]');
    var input = chat.querySelector('[data-chat-input]');
    var fallback = chat.querySelector('[data-chat-fallback]');

    var whenClosed = openBtn.querySelector('[data-chat-when-closed]');
    var whenOpen = openBtn.querySelector('[data-chat-when-open]');
    var isOpen = false;
    var setOpen = function (open) {
      isOpen = open;
      panel.hidden = !open;
      openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      openBtn.setAttribute('aria-label', open ? 'Close chat' : 'Chat with us');
      // The same button becomes a round X while the chat is open.
      whenClosed.classList.toggle('hidden', open);
      whenClosed.classList.toggle('flex', !open);
      whenOpen.classList.toggle('hidden', !open);
      whenOpen.classList.toggle('flex', open);
      openBtn.classList.toggle('px-5', !open);
      openBtn.classList.toggle('w-14', open);
      openBtn.classList.toggle('h-14', open);
      openBtn.classList.toggle('px-0', open);
      if (open) { input.focus(); } else { openBtn.focus(); }
    };
    var bubble = function (text, mine, link, label) {
      var row = document.createElement('div');
      row.className = 'flex ' + (mine ? 'justify-end' : 'gap-2');
      var b = document.createElement('div');
      b.className = mine
        ? 'max-w-[85%] rounded-lg rounded-tr-sm bg-primary px-4 py-3 text-[15px] leading-relaxed text-white'
        : 'max-w-[85%] rounded-lg rounded-tl-sm bg-surface px-4 py-3 text-[15px] leading-relaxed shadow-card';
      b.textContent = text;
      if (link && label) {
        var a = document.createElement('a');
        a.href = link;
        a.textContent = label;
        a.className = 'mt-2 block font-semibold text-primary';
        if (/^https?:/.test(link)) { a.rel = 'noopener'; a.target = '_blank'; }
        b.appendChild(a);
      }
      row.appendChild(b);
      log.appendChild(row);
      log.scrollTop = log.scrollHeight;
    };
    var reply = function (text, link, label) {
      var typing = document.createElement('div');
      typing.className = 'text-xs text-muted';
      typing.textContent = 'Atlantic Auto is typing...';
      log.appendChild(typing);
      log.scrollTop = log.scrollHeight;
      setTimeout(function () { typing.remove(); bubble(text, false, link, label); }, 600);
    };

    openBtn.addEventListener('click', function (ev) { ev.preventDefault(); setOpen(!isOpen); });
    chat.querySelector('[data-chat-close]').addEventListener('click', function () { setOpen(false); });
    panel.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') { setOpen(false); } });

    chat.querySelectorAll('[data-chat-q]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        bubble(btn.getAttribute('data-chat-q'), true);
        reply(btn.getAttribute('data-chat-a'), btn.getAttribute('data-chat-link'), btn.getAttribute('data-chat-label'));
      });
    });

    chat.querySelector('[data-chat-form]').addEventListener('submit', function (ev) {
      ev.preventDefault();
      ev.stopPropagation();
      var text = input.value.trim();
      if (!text) { return; }
      bubble(text, true);
      input.value = '';
      var f = fallback.content.querySelector('span');
      reply(f.getAttribute('data-text'), f.getAttribute('data-link'), f.getAttribute('data-label'));
    });
  }

  // Focus the error summary after a failed submit.
  var summary = document.querySelector('[data-error-summary]');
  if (summary) summary.focus();
})();
