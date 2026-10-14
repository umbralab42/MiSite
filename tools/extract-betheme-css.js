#!/usr/bin/env node
/**
 * Витягує з CSS betheme лише ті правила, які справді застосовуються до розмітки
 * сайту, і зберігає їх у власні файли теми mits:
 *   css/mits-base.css      — з be.css, animations, fontawesome, responsive.css (до mits-*.css)
 *   css/mits-settings.css  — з налаштувань betheme (mfn-dynamic, mfn-custom), після mits-*.css
 *
 * Використання (статична копія має бути доступна по HTTP):
 *   python3 -m http.server 8081 -d reference/static-site &
 *   node tools/extract-betheme-css.js http://localhost:8081 wp-content/themes/mits/css
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const cleanDom = require('./clean-dom');

const BASE = process.argv[2] || 'http://localhost:8081';
const OUT = process.argv[3] || path.join(__dirname, '../wp-content/themes/mits/css');
const ROOT = path.join(__dirname, '../reference/static-site');
const GROUPS = {
  'mits-base.css': ['mfn-be-css', 'mfn-animations-css', 'mfn-font-awesome-css', 'mfn-responsive-css'],
  'mits-settings.css': ['mfn-dynamic-inline-css', 'mfn-custom-inline-css'],
};
const SHEETS = [].concat(...Object.values(GROUPS));
// Класи body, які betheme ставив на всіх сторінках. Правила з ними переписуються
// без класу, тож новій темі ці класи не потрібні.
const CONST_BODY = ['layout-full-width', 'responsive-overflow-x-mobile', 'mobile-side-slide', 'content-brightness-dark', 'style-simple'];

function pages() {
  const out = [];
  (function walk(dir) {
    for (const f of fs.readdirSync(dir)) {
      const p = path.join(dir, f);
      if (fs.statSync(p).isDirectory()) { if (!/^wp-|^form$/.test(f)) walk(p); }
      else if (f === 'index.html' || f === '404.html') {
        const html = fs.readFileSync(p, 'utf8');
        if (html.includes('http-equiv="refresh"')) continue;
        out.push('/' + path.relative(ROOT, p).replace(/index\.html$/, '').split('/').map(encodeURIComponent).join('/'));
      }
    }
  })(ROOT);
  return out;
}

// Виконується в браузері: для кожного правила повертає селектори, що знаходять елементи.
function collect(sheetIds) {
  const DYN = /::?(hover|focus|focus-visible|focus-within|active|visited|link|target|before|after|placeholder|selection|marker|first-letter|first-line|autofill|-webkit-[a-z-]+|-moz-[a-z-]+|-ms-[a-z-]+)(\([^)]*\))?/g;
  const found = {};
  for (const sheet of document.styleSheets) {
    const id = sheet.ownerNode && sheet.ownerNode.id;
    if (!sheetIds.includes(id)) continue;
    (function walk(rules, p) {
      for (let i = 0; i < rules.length; i++) {
        const r = rules[i], key = id + ':' + p + i;
        if (r.cssRules && !(r instanceof CSSStyleRule)) { walk(r.cssRules, p + i + '.'); continue; }
        if (!(r instanceof CSSStyleRule)) continue;
        for (const sel of r.selectorText.split(/,(?![^(]*\))/)) {
          let s = sel.trim().replace(DYN, '').trim();
          if (!s || /[>+~]$/.test(s)) s += '*';
          try { if (document.querySelector(s)) (found[key] = found[key] || []).push(sel.trim()); } catch (e) { /* невалідний для Chromium */ }
        }
      }
    })(sheet.cssRules, '');
  }
  return found;
}

// Виконується в браузері: збирає текст CSS лише з потрібних правил.
function build(args) {
  const [sheetIds, keep] = args;
  const usedFonts = new Set(), usedAnims = new Set(), urls = [];
  const out = {};
  function decl(style) {
    const t = style.cssText;
    const ff = style.getPropertyValue('font-family');
    if (ff) ff.split(',').forEach(f => usedFonts.add(f.trim().replace(/^["']|["']$/g, '').toLowerCase()));
    const an = style.getPropertyValue('animation-name') || style.getPropertyValue('animation');
    if (an) an.split(/[\s,]+/).forEach(a => usedAnims.add(a));
    return t;
  }
  for (const sheet of document.styleSheets) {
    const id = sheet.ownerNode && sheet.ownerNode.id;
    if (!sheetIds.includes(id)) continue;
    const later = [];
    const text = (function walk(rules, p) {
      let s = '';
      for (let i = 0; i < rules.length; i++) {
        const r = rules[i], key = id + ':' + p + i;
        if (r instanceof CSSStyleRule) {
          if (keep[key]) {
            // Загальні правила для тегів (скидання, типографіка) лишаються повністю,
            // щоб вміст, доданий редакторами пізніше (h5, table, blockquote…), виглядав так само.
            const generic = r.selectorText.split(/,(?![^(]*\))/).map(x => x.trim()).filter(x => !/[.#]/.test(x));
            s += [...new Set([...keep[key], ...generic])].join(', ') + ' { ' + decl(r.style) + ' }\n';
          }
        } else if (r instanceof CSSMediaRule || (window.CSSSupportsRule && r instanceof CSSSupportsRule)) {
          const inner = walk(r.cssRules, p + i + '.');
          if (inner) s += (r instanceof CSSMediaRule ? '@media ' + r.conditionText : '@supports ' + r.conditionText) + ' {\n' + inner + '}\n';
        } else if (r instanceof CSSFontFaceRule || r instanceof CSSKeyframesRule) {
          later.push({ r, pos: s.length });
        }
      }
      return s;
    })(sheet.cssRules, '');
    let pre = '';
    for (const { r } of later) {
      if (r instanceof CSSFontFaceRule) {
        const fam = r.style.getPropertyValue('font-family').replace(/^["']|["']$/g, '').toLowerCase();
        if (usedFonts.has(fam)) pre += r.cssText + '\n';
      } else if (usedAnims.has(r.name)) pre += r.cssText + '\n';
    }
    out[id] = { text: pre + text, href: sheet.href || location.href };
  }
  return out;
}

(async () => {
  const browser = await chromium.launch({ executablePath: fs.existsSync('/opt/pw-browsers/chromium') ? '/opt/pw-browsers/chromium' : undefined });
  const page = await browser.newPage();
  const keep = {};
  const list = pages();
  for (const u of list) {
    await page.goto(BASE + u, { waitUntil: 'load' });
    await page.evaluate(cleanDom, '^(' + CONST_BODY.join('|') + '|no-content-padding|full-width-site)$');
    const f = await page.evaluate(collect, SHEETS);
    for (const [k, v] of Object.entries(f)) keep[k] = [...new Set([...(keep[k] || []), ...v])];
  }
  // Будуємо на головній (усі таблиці стилів однакові на всіх сторінках).
  await page.goto(BASE + '/', { waitUntil: 'load' });
  const built = await page.evaluate(build, [SHEETS, keep]);
  await browser.close();

  const assetsDir = path.join(OUT, '..', 'fonts');
  for (const [file, ids] of Object.entries(GROUPS)) {
    let css = `/* ${file}: правила, перенесені з betheme (${ids.join(', ')}),\n * які застосовуються до розмітки сайту. Згенеровано tools/extract-betheme-css.js\n * з reference/static-site (${list.length} сторінок). Далі редагується вручну. */\n`;
    for (const id of ids) {
      if (!built[id]) continue;
      let t = built[id].text.replace(/url\("?([^")]+)"?\)/g, (m, u) => {
        const abs = new URL(u, built[id].href);
        if (abs.origin !== new URL(BASE).origin) return m;
        const rel = decodeURIComponent(abs.pathname);
        const src = path.join(ROOT, rel);
        if (/\.(woff2?|ttf|eot|svg)$/.test(rel) && fs.existsSync(src)) {
          fs.mkdirSync(assetsDir, { recursive: true });
          fs.copyFileSync(src, path.join(assetsDir, path.basename(rel)));
          return `url("../fonts/${path.basename(rel)}")`;
        }
        return `url("${rel}")`;
      });
      for (const c of CONST_BODY) {
        t = t.replace(new RegExp('body\\.' + c + '\\b', 'g'), 'body')
             .replace(new RegExp('\\.' + c + '(?=[ ,{])', 'g'), 'body');
      }
      t = t.replace(/(^|, )body (#|\.)/gm, '$1$2');
      css += `\n/* ---- ${id} ---- */\n` + t;
    }
    fs.writeFileSync(path.join(OUT, file), css);
    console.log(file, css.length);
  }
})();
