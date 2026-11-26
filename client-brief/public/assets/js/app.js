/**
 * ArchonDigital Client Brief — Client-Side UX Helpers (Vanilla JavaScript)
 * Note: Server-side validation in PHP remains authoritative.
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('clientBriefForm');
    const submitBtn = document.getElementById('submitBriefBtn');

    /* 1. Highlight selected radio tile */
    const optionRadios = document.querySelectorAll('.ad-option-tile input[type="radio"]');
    optionRadios.forEach(function (radio) {
      radio.addEventListener('change', function () {
        const groupName = radio.getAttribute('name');
        document
          .querySelectorAll('input[type="radio"][name="' + groupName + '"]')
          .forEach(function (peer) {
            const tile = peer.closest('.ad-option-tile');
            if (tile) {
              tile.classList.toggle('is-selected', peer.checked);
            }
          });
      });
    });

    /* 2. Character counter helper for textareas */
    const textareas = document.querySelectorAll('textarea[data-char-count]');
    textareas.forEach(function (textarea) {
      const max = parseInt(textarea.getAttribute('data-char-count') || '1500', 10);
      const counter = document.createElement('div');
      counter.className = 'ad-char-counter tabular-nums';

      function updateCounter() {
        counter.textContent = textarea.value.length + ' / ' + max + ' karakter';
      }

      textarea.insertAdjacentElement('afterend', counter);
      textarea.addEventListener('input', updateCounter);
      updateCounter();
    });

    /* 3. Client-side Indonesian WhatsApp format helper + double-submit prevention */
    if (form) {
      const waInput = document.getElementById('whatsapp');

      function validateIndonesianWa(raw) {
        const cleaned = raw.replace(/[\s\-\(\)\.]+/g, '').trim();
        const digits = cleaned.startsWith('+') ? cleaned.slice(1) : cleaned;
        return /^(\+?62|0)8[1-9][0-9]{6,11}$/.test(cleaned) || /^628[1-9][0-9]{6,11}$/.test(digits);
      }

      if (waInput) {
        waInput.addEventListener('input', function () {
          waInput.setCustomValidity('');
        });
      }

      let isSubmitting = false;

      form.addEventListener('submit', function (event) {
        if (isSubmitting) {
          event.preventDefault();
          return;
        }

        if (waInput) {
          const val = waInput.value.trim();
          if (val !== '' && !validateIndonesianWa(val)) {
            waInput.setCustomValidity('Masukkan nomor WhatsApp Indonesia yang valid (contoh: 085159117451).');
          } else {
            waInput.setCustomValidity('');
          }
        }

        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
          form.classList.add('was-validated');
          const firstInvalid = form.querySelector(':invalid');
          if (firstInvalid && typeof firstInvalid.focus === 'function') {
            firstInvalid.focus();
          }
          return;
        }

        isSubmitting = true;
        if (submitBtn) {
          submitBtn.disabled = true;
          submitBtn.textContent = 'Mengirim Brief...';
        }
      });
    }
  });
})();
