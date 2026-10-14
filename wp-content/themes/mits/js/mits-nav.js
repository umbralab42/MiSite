/* Навігація: випадна панель під стрілкою, підменю на сенсорних екранах, «гамбургер». */
(function () {
  'use strict';

  var arrow = document.getElementById('mitsDropdownArrow');
  var dropdown = document.getElementById('mitsDropdown');

  if (arrow && dropdown) {
    var dragStartY = null;
    var isDragging = false;
    var THRESHOLD = 30;

    var openDD = function () { dropdown.classList.add('is-open'); dropdown.setAttribute('aria-hidden', 'false'); arrow.setAttribute('aria-expanded', 'true'); };
    var closeDD = function () { dropdown.classList.remove('is-open'); dropdown.setAttribute('aria-hidden', 'true'); arrow.setAttribute('aria-expanded', 'false'); };
    var toggle = function () { if (dropdown.classList.contains('is-open')) closeDD(); else openDD(); };
    var move = function (y) {
      if (dragStartY === null) return;
      var d = y - dragStartY;
      if (Math.abs(d) > THRESHOLD) isDragging = true;
      if (d > THRESHOLD && !dropdown.classList.contains('is-open')) openDD();
      if (d < -THRESHOLD && dropdown.classList.contains('is-open')) closeDD();
    };
    var end = function () {
      if (!isDragging && dragStartY !== null) toggle();
      dragStartY = null;
      isDragging = false;
    };

    arrow.addEventListener('mousedown', function (e) { dragStartY = e.clientY; isDragging = false; e.preventDefault(); });
    document.addEventListener('mousemove', function (e) { move(e.clientY); });
    document.addEventListener('mouseup', end);
    arrow.addEventListener('touchstart', function (e) { dragStartY = e.touches[0].clientY; isDragging = false; }, { passive: true });
    arrow.addEventListener('touchmove', function (e) { move(e.touches[0].clientY); }, { passive: true });
    arrow.addEventListener('touchend', end);
    arrow.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
    });
    document.addEventListener('click', function (e) {
      if (dropdown.classList.contains('is-open') && !dropdown.contains(e.target) && !arrow.contains(e.target)) closeDD();
    });
  }

  // Підменю шапки: на сенсорних екранах перший дотик відкриває підменю.
  document.querySelectorAll('.mits-nav:not(.mits-footer-nav) .mits-nav__menu > li').forEach(function (li) {
    if (!li.querySelector('.sub-menu')) return;
    li.addEventListener('click', function (e) {
      if (e.target.closest('.sub-menu')) return;
      if (window.matchMedia('(hover: none)').matches) {
        e.preventDefault();
        var isOpen = li.classList.toggle('is-open');
        li.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      }
    });
  });
  document.addEventListener('click', function (e) {
    if (!e.target.closest('.mits-nav__menu')) {
      document.querySelectorAll('.mits-nav:not(.mits-footer-nav) .mits-nav__menu > li.is-open').forEach(function (li) { li.classList.remove('is-open'); });
    }
  });

  // «Гамбургер» у шапці.
  bindHamburger(document.getElementById('mitsHamburger'), document.querySelector('.mits-nav:not(.mits-footer-nav) .mits-nav__menu'));

  // «Гамбургер» і підменю в підвалі.
  var footerMenu = document.getElementById('mits-footer-menu');
  bindHamburger(document.getElementById('mitsFooterHamburger'), footerMenu);
  if (footerMenu) {
    footerMenu.querySelectorAll('li.menu-item-has-children > a').forEach(function (link) {
      link.addEventListener('click', function (e) {
        var li = link.parentElement;
        var isOpen = li.classList.toggle('is-open');
        li.parentElement.querySelectorAll('li.is-open').forEach(function (other) { if (other !== li) other.classList.remove('is-open'); });
        if (isOpen) e.preventDefault();
      });
    });
  }

  function bindHamburger(button, menu) {
    if (!button || !menu) return;
    button.addEventListener('click', function () {
      var isOpen = menu.classList.toggle('is-open');
      button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      button.classList.toggle('is-open', isOpen);
    });
    document.addEventListener('click', function (e) {
      if (!button.contains(e.target) && !menu.contains(e.target)) {
        menu.classList.remove('is-open');
        button.setAttribute('aria-expanded', 'false');
        button.classList.remove('is-open');
        menu.querySelectorAll('li.is-open').forEach(function (li) { li.classList.remove('is-open'); });
      }
    });
  }
})();
