/* Форма «Консультація» на статичному сайті: надсилає дані на /form/send.php
   і показує відповідь у тому самому місці й тими самими стилями, що й раніше. */
(function () {
  'use strict';
  var ENDPOINT = '/form/send.php';
  var TEXT = {
    uk: {
      sent: 'Дякуємо за Ваше повідомлення. Воно було надіслане.',
      failed: 'Сталася помилка під час спроби відправити Ваше повідомлення. Будь ласка, спробуйте ще раз пізніше.',
      invalid: 'Одне або декілька полів містять помилкові дані. Будь ласка, перевірте їх і спробуйте ще раз.',
      required: 'Будь ласка, заповніть це поле.',
      email: 'Будь ласка, введіть адресу електронної пошти.',
      tel: 'Будь ласка, введіть номер телефону.'
    },
    en: {
      sent: 'Thank you for your message. It has been sent.',
      failed: 'There was an error trying to send your message. Please try again later.',
      invalid: 'One or more fields have an error. Please check and try again.',
      required: 'Please fill in this field.',
      email: 'Please enter an email address.',
      tel: 'Please enter a telephone number.'
    }
  };

  function setStatus(form, status, message) {
    form.setAttribute('data-status', status);
    ['init', 'submitting', 'sent', 'failed', 'invalid'].forEach(function (c) { form.classList.remove(c); });
    form.classList.add(status);
    var out = form.querySelector('.wpcf7-response-output');
    if (out) {
      out.textContent = message || '';
      if (message) out.removeAttribute('aria-hidden'); else out.setAttribute('aria-hidden', 'true');
    }
    var live = form.parentNode.querySelector('.screen-reader-response [role="status"]');
    if (live) live.textContent = message || '';
  }

  function clearTips(form) {
    form.querySelectorAll('.wpcf7-not-valid-tip').forEach(function (n) { n.remove(); });
    form.querySelectorAll('.wpcf7-not-valid').forEach(function (n) {
      n.classList.remove('wpcf7-not-valid');
      n.setAttribute('aria-invalid', 'false');
    });
  }

  function markInvalid(form, name, message) {
    var input = form.querySelector('[name="' + name + '"]');
    if (!input) return;
    input.classList.add('wpcf7-not-valid');
    input.setAttribute('aria-invalid', 'true');
    var tip = document.createElement('span');
    tip.className = 'wpcf7-not-valid-tip';
    tip.setAttribute('aria-hidden', 'true');
    tip.textContent = message;
    (input.closest('.wpcf7-form-control-wrap') || input.parentNode).appendChild(tip);
  }

  function validate(form, t) {
    var bad = [];
    form.querySelectorAll('.wpcf7-validates-as-required').forEach(function (input) {
      if (!input.value.trim()) bad.push([input.name, t.required]);
    });
    var email = form.querySelector('[name="your-email"]');
    if (email && email.value.trim() && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) bad.push(['your-email', t.email]);
    var tel = form.querySelector('[name="your-phone"]');
    if (tel && tel.value.trim() && !/^[0-9+()\-\s.]{5,40}$/.test(tel.value.trim())) bad.push(['your-phone', t.tel]);
    return bad;
  }

  function init(form) {
    var wrap = form.closest('.wpcf7');
    var lang = wrap && wrap.getAttribute('lang') === 'en' ? 'en' : 'uk';
    var t = TEXT[lang];
    if (wrap) { wrap.classList.remove('no-js'); wrap.classList.add('js'); }

    var trap = document.createElement('input');
    trap.type = 'text'; trap.name = 'mits_website'; trap.tabIndex = -1; trap.autocomplete = 'off';
    trap.setAttribute('aria-hidden', 'true');
    trap.style.cssText = 'position:absolute;left:-9999px;width:1px;height:1px;opacity:0';
    form.appendChild(trap);
    var started = Math.floor(Date.now() / 1000);

    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (form.getAttribute('data-status') === 'submitting') return;
      clearTips(form);
      var bad = validate(form, t);
      if (bad.length) {
        bad.forEach(function (b) { markInvalid(form, b[0], b[1]); });
        setStatus(form, 'invalid', t.invalid);
        return;
      }
      var data = new FormData(form);
      data.set('mits_ts', String(started));
      data.set('mits_lang', lang);
      setStatus(form, 'submitting', '');
      fetch(ENDPOINT, { method: 'POST', body: data, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res.status === 'sent') {
            form.reset();
            started = Math.floor(Date.now() / 1000);
            setStatus(form, 'sent', t.sent);
          } else if (res.status === 'invalid') {
            (res.fields || []).forEach(function (n) { markInvalid(form, n, n === 'your-email' ? t.email : n === 'your-phone' ? t.tel : t.required); });
            setStatus(form, 'invalid', t.invalid);
          } else {
            setStatus(form, 'failed', t.failed);
          }
        })
        .catch(function () { setStatus(form, 'failed', t.failed); });
    });
  }

  function start() { document.querySelectorAll('form.wpcf7-form').forEach(init); }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start); else start();
})();
