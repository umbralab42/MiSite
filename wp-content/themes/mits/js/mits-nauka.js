/* Сторінка «Наука»: акордеони та вкладки навчальних років. */
(function () {
  'use strict';
  document.querySelectorAll('[data-nauka-accordion]').forEach(function (acc) {
    var headers = acc.querySelectorAll('.mits-adv-item__header');
    headers.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var open = btn.getAttribute('aria-expanded') === 'true';
        headers.forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
        if (!open) btn.setAttribute('aria-expanded', 'true');
      });
    });
  });
  document.querySelectorAll('[data-nauka-tabs]').forEach(function (list) {
    var tabs = Array.prototype.slice.call(list.querySelectorAll('[role="tab"]'));
    function select(tab, focus) {
      tabs.forEach(function (t) {
        var on = t === tab;
        t.classList.toggle('is-active', on);
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.tabIndex = on ? 0 : -1;
        var panel = document.getElementById(t.getAttribute('aria-controls'));
        if (panel) panel.hidden = !on;
      });
      if (focus) tab.focus();
    }
    tabs.forEach(function (tab, i) {
      tab.addEventListener('click', function () { select(tab, false); });
      tab.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowRight') select(tabs[(i + 1) % tabs.length], true);
        else if (e.key === 'ArrowLeft') select(tabs[(i - 1 + tabs.length) % tabs.length], true);
      });
    });
  });
})();
