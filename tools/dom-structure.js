// Для кожної сторінки: батьківський ланцюжок ключових секцій (щоб відтворити вкладеність).
const { chromium } = require('playwright');
const fs = require('fs');
const pages = require('./pages')();
const BASE = process.argv[2] || 'http://localhost:8081';
(async () => {
  const b = await chromium.launch({ executablePath: fs.existsSync('/opt/pw-browsers/chromium') ? '/opt/pw-browsers/chromium' : undefined });
  const p = await b.newPage();
  for (const u of pages) {
    await p.goto(BASE + u, { waitUntil: 'domcontentloaded' });
    const r = await p.evaluate(() => {
      const chain = (el) => { const out = []; while (el && el !== document.body) { out.unshift(el.id ? '#' + el.id : el.tagName.toLowerCase() + (el.className && typeof el.className === 'string' ? '.' + el.className.trim().split(/\s+/)[0] : '')); el = el.parentElement; } return out.join('>'); };
      const pick = ['.mits-footer-form', '.mits-footer-nav', 'section.mits-contacts', '.mits-subjects', '.mits-algo', '.mits-news', '#comments'];
      return pick.map(s => { const e = document.querySelector(s); return e ? s + ' :: ' + chain(e.parentElement) : null; }).filter(Boolean);
    });
    console.log(decodeURIComponent(u)); r.forEach(x => console.log('   ', x));
  }
  await b.close();
})();
