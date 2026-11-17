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

  // Prevent double submits while a form is sending.
  document.addEventListener('submit', function (ev) {
    var form = ev.target;
    if (!(form instanceof HTMLFormElement)) return;
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

  // Focus the error summary after a failed submit.
  var summary = document.querySelector('[data-error-summary]');
  if (summary) summary.focus();
})();
