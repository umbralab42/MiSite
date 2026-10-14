/* Акордеони (переваги, FAQ, курси): відкритий лише один пункт у межах свого блоку. */
(function () {
  'use strict';
  document.querySelectorAll('.mits-advantages__accordion:not([data-nauka-accordion])').forEach(function (acc) {
    var headers = acc.querySelectorAll('.mits-adv-item__header');
    headers.forEach(function (btn) {
      if (btn.hasAttribute('data-no-content')) return;
      btn.addEventListener('click', function () {
        var isOpen = btn.getAttribute('aria-expanded') === 'true';
        headers.forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
        if (!isOpen) btn.setAttribute('aria-expanded', 'true');
      });
    });
  });
})();
