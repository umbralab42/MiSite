// Порівнює кожну сторінку статичної копії «як є» з тією ж сторінкою, де розмітку
// betheme прибрано, а CSS betheme замінено на css/mits-base.css + css/mits-settings.css.
const { chromium } = require('playwright');
const fs = require('fs'), path = require('path');
const cleanDom = require('./clean-dom');
const { FREEZE_JS, prepare, compare } = require('./shots');
const BASE = process.argv[2] || 'http://localhost:8081';
const OUT = process.argv[3] || '/tmp/css-swap';
const WIDTH = +(process.argv[4] || 1440);
const ONLY = process.argv[5];
const THEME = path.join(__dirname, '../wp-content/themes/mits/css');
fs.mkdirSync(OUT, { recursive: true });
const pages = require('./pages')();

(async () => {
  const browser = await chromium.launch({ executablePath: fs.existsSync('/opt/pw-browsers/chromium') ? '/opt/pw-browsers/chromium' : undefined });
  const ctx = await browser.newContext({ viewport: { width: WIDTH, height: 900 } });
  await ctx.addInitScript(FREEZE_JS);
  const base = fs.readFileSync(path.join(THEME, 'mits-base.css'), 'utf8');
  const settings = fs.readFileSync(path.join(THEME, 'mits-settings.css'), 'utf8');
  for (const u of pages.filter(p => !ONLY || decodeURIComponent(p).includes(ONLY))) {
    const name = decodeURIComponent(u).replace(/\//g, '_') || 'home';
    const page = await ctx.newPage();
    await page.goto(BASE + u, { waitUntil: 'networkidle' });
    await prepare(page);
    await page.screenshot({ path: `${OUT}/${name}-a.png`, fullPage: true });
    await page.evaluate(cleanDom);
    await page.evaluate(([base, settings]) => {
      ['mfn-be-css', 'mfn-animations-css', 'mfn-font-awesome-css', 'mfn-responsive-css'].forEach((id, i) => {
        const n = document.getElementById(id); if (!n) return;
        if (i === 0) { const s = document.createElement('style'); s.textContent = base.replace(/\.\.\/fonts\//g, 'http://localhost:8082/fonts/'); n.replaceWith(s); } else n.remove();
      });
      const d = document.getElementById('mfn-dynamic-inline-css'); if (d) d.textContent = settings;
      const c = document.getElementById('mfn-custom-inline-css'); if (c) c.remove();
    }, [base, settings]);
    await prepare(page);
    await page.screenshot({ path: `${OUT}/${name}-b.png`, fullPage: true });
    const r = compare(`${OUT}/${name}-a.png`, `${OUT}/${name}-b.png`, `${OUT}/${name}-diff.png`);
    console.log((r.ratio * 100).toFixed(3).padStart(8) + '%', r.sizeA.join('x'), r.sizeB.join('x'), name);
    await page.close();
  }
  await browser.close();
})();
