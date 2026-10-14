/* Повторювані блоки й вибір зображень для полів MITS Core. */
(function () {
  'use strict';

  function renumber(repeater) {
    var ph = repeater.getAttribute('data-placeholder');
    var rows = repeater.querySelector(':scope > .mits-repeater__rows').children;
    Array.prototype.forEach.call(rows, function (row, i) {
      row.querySelectorAll('[name]').forEach(function (el) {
        // Номер рядка цього рівня — останній індекс перед підполем цього рівня.
        el.name = el.name.replace(new RegExp('^(' + escape(repeater._base) + ')\\[[^\\]]+\\]'), '$1[' + i + ']');
      });
    });
    function escape(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }
  }

  function baseName(repeater) {
    var marker = repeater.querySelector(':scope > input[type=hidden][name$="[__present]"]');
    return marker ? marker.name.replace(/\[__present\]$/, '') : '';
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.mits-repeater__add, .mits-repeater__remove, .mits-repeater__up, .mits-repeater__down');
    if (!btn) return;
    e.preventDefault();
    var repeater = btn.closest('.mits-repeater');
    if (btn.classList.contains('mits-repeater__add')) {
      repeater = btn.parentNode;
    }
    repeater._base = baseName(repeater);
    var rows = repeater.querySelector(':scope > .mits-repeater__rows');
    if (btn.classList.contains('mits-repeater__add')) {
      var tpl = repeater.querySelector(':scope > template.mits-repeater__template');
      var ph = repeater.getAttribute('data-placeholder');
      var html = tpl.innerHTML;
      // Шаблон міг бути створений, коли батьківський рядок мав інший номер: беремо актуальну основу імені.
      var m = html.match(new RegExp('name="([^"]*?)\\[' + ph + '\\]'));
      if (m && m[1] !== repeater._base) html = html.split(m[1] + '[').join(repeater._base + '[');
      html = html.split(ph).join(String(rows.children.length));
      var wrap = document.createElement('div');
      wrap.innerHTML = html;
      rows.appendChild(wrap.firstElementChild);
    } else {
      var row = btn.closest('.mits-repeater__row');
      if (btn.classList.contains('mits-repeater__remove')) {
        if (!window.confirm('Видалити цей рядок?')) return;
        row.remove();
      } else if (btn.classList.contains('mits-repeater__up') && row.previousElementSibling) {
        row.parentNode.insertBefore(row, row.previousElementSibling);
      } else if (btn.classList.contains('mits-repeater__down') && row.nextElementSibling) {
        row.parentNode.insertBefore(row.nextElementSibling, row);
      }
    }
    renumber(repeater);
  });

  // Вибір зображення з медіатеки.
  document.addEventListener('click', function (e) {
    var pick = e.target.closest('.mits-image-pick');
    var clear = e.target.closest('.mits-image-clear');
    if (!pick && !clear) return;
    e.preventDefault();
    var box = (pick || clear).closest('.mits-image-field');
    var input = box.querySelector('.mits-image-value');
    var img = box.querySelector('.mits-image-preview');
    if (clear) {
      input.value = '';
      img.hidden = true;
      img.removeAttribute('src');
      return;
    }
    var frame = window.wp.media({ title: 'Виберіть зображення', library: { type: 'image' }, multiple: false });
    frame.on('select', function () {
      var a = frame.state().get('selection').first().toJSON();
      var url = a.url.replace(window.location.origin, '');
      input.value = box.getAttribute('data-store') === 'id' ? a.id : url;
      img.src = (a.sizes && a.sizes.thumbnail ? a.sizes.thumbnail.url : a.url);
      img.hidden = false;
    });
    frame.open();
  });
})();
