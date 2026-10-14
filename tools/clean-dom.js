// Прибирає зі сторінки статичної копії все, що належить betheme і не входить
// у новий сайт: прихований заголовок і підвал betheme, бічне меню, класи body/html.
// Результат — та розмітка, яку видає нова тема mits.
// keep — регулярний вираз (рядком) для класів body betheme, які треба лишити.
module.exports = function cleanDom(keep) {
  ['#Sliding-top', '#Header_wrapper', '#mfn-header-template', '#Footer', '#Side_slide', '#body_overlay',
   'script[type=speculationrules]', '#wp-emoji-settings'].forEach(function (s) {
    document.querySelectorAll(s).forEach(function (n) { n.remove(); });
  });
  // keep === '.' — лише прибрати елементи, класи не чіпати (порівняння еталона «як є»).
  if (keep === '.') return;
  // Класи, які тема mits додає сама на тих самих сторінках (mits_body_class()).
  var keepRe = new RegExp(keep || '^(no-content-padding|full-width-site)$');
  var drop = /^(color-custom|content-brightness-|input-brightness-|style-simple|button-animation-|layout-|full-width-site|if-|no-content-padding|header-|sticky-|ab-|menu-|menuo-|subheader-|responsive-|mobile-|be-|woocommerce-|wp-theme-betheme|theme-betheme|mfn-)/;
  document.body.className = document.body.className.split(/\s+/).filter(function (c) { return c && (keepRe.test(c) || !drop.test(c)); }).join(' ');
  document.documentElement.className = '';
};
