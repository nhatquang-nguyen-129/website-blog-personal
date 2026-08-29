(function () {
  function initSignup(root) {
    var trigger = root.querySelector('[data-newsletter-trigger]');
    var modal = root.querySelector('[data-newsletter-modal]');
    var closers = root.querySelectorAll('[data-newsletter-close]');
    var form = root.querySelector('[data-newsletter-form]');
    var emailField = root.querySelector('[data-newsletter-email]');
    var consentField = root.querySelector('[data-newsletter-consent]');
    var honeypot = root.querySelector('[data-newsletter-honeypot]');
    var submitBtn = root.querySelector('[data-newsletter-submit]');
    var message = root.querySelector('[data-newsletter-message]');

    if (!trigger || !modal || !form || !emailField || !consentField || !submitBtn) {
      return;
    }

    // The header instance's modal starts out nested inside .site-header,
    // which sets backdrop-filter for its frosted-glass sticky effect —
    // that alone makes it the containing block for any position:fixed
    // descendant (same rule as transform/filter), so the modal would
    // center itself against the thin header bar instead of the actual
    // viewport. Moving it to be a direct child of <body> escapes that.
    // The element references above stay valid; only its parent changes.
    document.body.appendChild(modal);

    function openModal() {
      modal.removeAttribute('hidden');
      trigger.setAttribute('aria-expanded', 'true');
      emailField.focus();
      document.addEventListener('keydown', onKeydown);
    }

    function closeModal() {
      modal.setAttribute('hidden', '');
      trigger.setAttribute('aria-expanded', 'false');
      document.removeEventListener('keydown', onKeydown);
      trigger.focus();
    }

    function onKeydown(e) {
      if (e.key === 'Escape') {
        closeModal();
      }
    }

    trigger.addEventListener('click', openModal);
    closers.forEach(function (closer) {
      closer.addEventListener('click', closeModal);
    });

    // The Submit button starts disabled in the markup itself (works even if
    // this script fails to load — visitor just can't submit at all, rather
    // than submitting without having agreed to anything).
    consentField.addEventListener('change', function () {
      submitBtn.disabled = !consentField.checked;
    });

    function showMessage(text, isError) {
      if (!message) {
        return;
      }
      message.textContent = text;
      message.classList.toggle('is-error', !!isError);
      message.removeAttribute('hidden');
    }

    form.addEventListener('submit', function (e) {
      e.preventDefault();

      if (!window.mlnsSettings || !window.mlnsSettings.restUrl) {
        return;
      }

      submitBtn.disabled = true;

      window
        .fetch(window.mlnsSettings.restUrl, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            email: emailField.value,
            consent: consentField.checked,
            hp: honeypot ? honeypot.value : '',
          }),
        })
        .then(function (response) {
          return response.json().then(function (data) {
            return { ok: response.ok, data: data };
          });
        })
        .then(function (result) {
          if (!result.ok) {
            showMessage(result.data.message || 'Something went wrong.', true);
            submitBtn.disabled = !consentField.checked;
            return;
          }

          form.reset();
          submitBtn.disabled = true;
          showMessage(
            result.data.status === 'already_subscribed'
              ? 'You’re already subscribed.'
              : 'You’re subscribed!',
            false
          );
        })
        .catch(function () {
          showMessage('Something went wrong — try again in a moment.', true);
          submitBtn.disabled = !consentField.checked;
        });
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var groups = document.querySelectorAll('[data-newsletter-signup]');
    groups.forEach(initSignup);
  });
})();
