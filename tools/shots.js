// Спільні функції для знімків екрана: стабілізація сторінки та порівняння PNG.
const fs = require('fs');
const { PNG } = require('pngjs');
const pixelmatchMod = require('pixelmatch');
const pixelmatch = pixelmatchMod.default || pixelmatchMod;

const FREEZE_JS = () => {
  // Зупиняємо автопрокрутку слайдера, щоб знімки були відтворювані.
  window.setInterval = function () { return 0; };
};
const FREEZE_CSS = '*,*::before,*::after{transition:none!important;animation:none!important;caret-color:transparent!important}';

async function prepare(page) {
  await page.addStyleTag({ content: FREEZE_CSS });
  // Прокрутка донизу, щоб спрацювали ліниві зображення, потім очікування їх завантаження.
  await page.evaluate(async () => {
    for (let y = 0; y < document.body.scrollHeight; y += 600) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 40)); }
  });
  await page.evaluate(async () => {
    const imgs = [...document.images];
    await Promise.all(imgs.map(i => (i.complete ? Promise.resolve() : new Promise(r => { i.addEventListener('load', r, { once: true }); i.addEventListener('error', r, { once: true }); setTimeout(r, 10000); })).then(() => (i.decode ? i.decode().catch(() => 0) : 0))));
    if (document.fonts) await document.fonts.ready;
    window.scrollTo(0, 0);
  });
  await page.waitForTimeout(300);
}

function compare(a, b, diffPath) {
  const A = PNG.sync.read(fs.readFileSync(a)), B = PNG.sync.read(fs.readFileSync(b));
  const w = Math.max(A.width, B.width), h = Math.max(A.height, B.height);
  const pad = (img) => { if (img.width === w && img.height === h) return img; const o = new PNG({ width: w, height: h }); o.data.fill(0); PNG.bitblt(img, o, 0, 0, img.width, img.height, 0, 0); return o; };
  const A2 = pad(A), B2 = pad(B), D = new PNG({ width: w, height: h });
  const n = pixelmatch(A2.data, B2.data, D.data, w, h, { threshold: 0.1 });
  if (diffPath) fs.writeFileSync(diffPath, PNG.sync.write(D));
  return { diff: n, ratio: n / (w * h), sizeA: [A.width, A.height], sizeB: [B.width, B.height] };
}

module.exports = { FREEZE_JS, FREEZE_CSS, prepare, compare };
