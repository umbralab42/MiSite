// Спільні функції для знімків екрана: стабілізація сторінки та порівняння PNG.
const fs = require('fs');
const { PNG } = require('pngjs');
const pixelmatchMod = require('pixelmatch');
const pixelmatch = pixelmatchMod.default || pixelmatchMod;

const FREEZE_JS = () => {
  // Зупиняємо автопрокрутку слайдера й таймери, щоб знімки були відтворювані.
  window.setInterval = function () { return 0; };
  window.__mitsFrozen = true;
};
const FREEZE_CSS = '*,*::before,*::after{transition:none!important;animation:none!important;caret-color:transparent!important}';

async function prepare(page) {
  await page.addStyleTag({ content: FREEZE_CSS });
  // Підвантажуємо ліниві зображення.
  await page.evaluate(async () => {
    document.querySelectorAll('img[loading=lazy]').forEach(i => { i.loading = 'eager'; });
    await Promise.all([...document.images].map(i => (i.complete ? Promise.resolve() : new Promise(r => { i.onload = i.onerror = r; setTimeout(r, 8000); })).then(() => i.decode ? i.decode().catch(() => 0) : 0)));
    if (document.fonts) await document.fonts.ready;
  });
  // Прокрутка донизу й назад, щоб браузер точно відмалював усі зображення.
  await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 800) { window.scrollTo(0, y); await new Promise(r => setTimeout(r, 30)); } window.scrollTo(0, 0); });
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
