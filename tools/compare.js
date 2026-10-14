#!/usr/bin/env node
/**
 * Порівнює кожну сторінку статичної копії з тією самою адресою на WordPress:
 *  - знімки екрана (Playwright) і частка пікселів, що відрізняються;
 *  - структура DOM (теги, класи, текст), щоб бачити, що саме відрізняється.
 *
 *   node tools/compare.js --static=http://localhost:8081 --wp=http://localhost:8080 --out=/tmp/cmp [--width=1440] [--only=наука]
 */
const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const cleanDom = require('./clean-dom');
const { FREEZE_JS, prepare, compare } = require('./shots');

const arg = (k, d) => { const a = process.argv.find(x => x.startsWith('--' + k + '=')); return a ? a.split('=').slice(1).join('=') : d; };
const STATIC = arg('static', 'http://localhost:8081');
const WP = arg('wp', 'http://localhost:8080');
const OUT = arg('out', '/tmp/cmp');
const WIDTH = +arg('width', 1440);
const ONLY = arg('only', '');
const NOSHOT = process.argv.includes('--no-shots');
fs.mkdirSync(OUT, { recursive: true });

// Структура DOM у вигляді рядків: відступ + тег.класи + текст.
function outline() {
  const SKIP = new Set(['SCRIPT', 'STYLE', 'NOSCRIPT', 'TEMPLATE', 'LINK', 'META']);
  const lines = [];
  (function walk(el, depth) {
    for (const n of el.childNodes) {
      if (n.nodeType === 3) {
        const t = n.textContent.replace(/\s+/g, ' ').trim();
        if (t) lines.push('  '.repeat(depth) + '"' + t + '"');
        continue;
      }
      if (n.nodeType !== 1 || SKIP.has(n.tagName)) continue;
      // Службові елементи форм: «крутилка» CF7 і пастка для ботів зі статичного form.js.
      if (n.classList.contains('wpcf7-spinner') || n.getAttribute('name') === 'mits_website') continue;
      if (n.tagName === 'svg') { lines.push('  '.repeat(depth) + 'svg'); continue; }
      const cls = [...n.classList].filter(c => !/^(menu-item-\d+|page-item-\d+|wp-image-\d+|is-active|current[-_].*)$/.test(c)).sort().join('.');
      let extra = '';
      if (n.tagName === "UL" && /^menu-main_menu/.test(n.id)) { lines.push("  ".repeat(depth) + "ul#menu.mits-nav__menu"); walk(n, depth + 1); continue; }
      // Відносні адреси й адреси локальних серверів порівнюються як шляхи; зовнішні — повністю.
      const local = (v) => decodeURIComponent((v || '').replace(/^https?:\/\/localhost(:\d+)?/, ''));
      if (n.tagName === 'A') extra = ' href=' + local(n.getAttribute('href')).toLowerCase();
      if (n.tagName === 'IMG') extra = ' src=' + local(n.getAttribute('src'));
      lines.push('  '.repeat(depth) + n.tagName.toLowerCase() + (n.id && !/^(menu-item|wpcf7-f)/.test(n.id) ? '#' + n.id : '') + (cls ? '.' + cls : '') + extra);
      walk(n, depth + 1);
    }
  })(document.body, 0);
  return lines;
}

(async () => {
  const browser = await chromium.launch({ executablePath: fs.existsSync('/opt/pw-browsers/chromium') ? '/opt/pw-browsers/chromium' : undefined });
  const ctx = await browser.newContext({ viewport: { width: WIDTH, height: 900 } });
  await ctx.addInitScript(FREEZE_JS);
  // Пари [адреса статичної копії, адреса WordPress]; для 404 — довільна неіснуюча адреса.
  const pages = require('./pages')().map(p => [p, p]).concat([['/404.html', '/neisnuiucha-storinka/']])
    .filter(([p]) => !ONLY || decodeURIComponent(p).includes(ONLY));
  const summary = [];
  for (const [u, wpPath] of pages) {
    const name = (decodeURIComponent(u).replace(/\//g, '_') || '_').replace(/^_|_$/g, '') || 'home';
    const res = {};
    for (const [side, base] of [['static', STATIC], ['wp', WP]]) {
      const page = await ctx.newPage();
      const resp = await page.goto(base + (side === 'wp' ? wpPath : u), { waitUntil: 'networkidle' }).catch(e => null);
      res[side + 'Status'] = resp ? resp.status() : 0;
      // В еталоні прибираються лише приховані елементи betheme; класи лишаються (його CSS від них залежить).
      if (side === 'static') await page.evaluate(cleanDom, '.');
      await prepare(page);
      fs.writeFileSync(`${OUT}/${name}.${side}.txt`, (await page.evaluate(outline)).join('\n'));
      if (!NOSHOT) await page.screenshot({ path: `${OUT}/${name}.${side}.png`, fullPage: true });
      await page.close();
    }
    let line = `${String(res.wpStatus).padStart(3)} `;
    if (!NOSHOT) {
      const r = compare(`${OUT}/${name}.static.png`, `${OUT}/${name}.wp.png`, `${OUT}/${name}.diff.png`);
      line += `${(r.ratio * 100).toFixed(3).padStart(8)}% ${r.sizeA.join('x')} ${r.sizeB.join('x')} `;
    }
    const a = fs.readFileSync(`${OUT}/${name}.static.txt`, 'utf8'), b = fs.readFileSync(`${OUT}/${name}.wp.txt`, 'utf8');
    line += (a === b ? 'dom=same ' : 'dom=DIFF ') + decodeURIComponent(u);
    console.log(line);
    summary.push(line);
  }
  fs.writeFileSync(`${OUT}/summary.txt`, summary.join('\n') + '\n');
  await browser.close();
})();
