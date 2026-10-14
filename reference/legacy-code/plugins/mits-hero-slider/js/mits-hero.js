/**
 * Hero slider — autoplay + dot navigation.
 * Companion to css/mits-hero.css
 */
(function () {
  'use strict';

  var hero = document.querySelector('.mits-hero');
  if (!hero) return;

  var slides  = Array.from(hero.querySelectorAll('.mits-hero__slide'));
  var dots    = Array.from(hero.querySelectorAll('.mits-hero__dot'));
  if (slides.length < 2) return;

  var current = 0, timer;

  function goTo(n) {
    slides[current].classList.remove('is-active');
    if (dots[current]) dots[current].classList.remove('is-active');
    current = (n + slides.length) % slides.length;
    slides[current].classList.add('is-active');
    if (dots[current]) dots[current].classList.add('is-active');
  }

  function start() { timer = setInterval(function () { goTo(current + 1); }, 5000); }
  function stop()  { clearInterval(timer); }

  dots.forEach(function (dot, i) {
    dot.addEventListener('click', function () { stop(); goTo(i); start(); });
  });

  hero.addEventListener('mouseenter', stop);
  hero.addEventListener('mouseleave', start);

  start();
}());
