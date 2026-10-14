#!/usr/bin/env node
/**
 * Перевірка інтерактиву на сайті: випадна панель, акордеони, слайдер, вкладки «Науки»,
 * перевірка обов'язкових полів форми CF7 (лист не надсилається), мобільне меню, помилки JS.
 *
 *   node tools/check-interactive.js http://localhost:8080
 */
const { chromium } = require('playwright');
const fs = require('fs');
const BASE = (process.argv[2] || 'http://localhost:8080').replace(/\/$/, '');
(async () => {
  const b = await chromium.launch({ executablePath: fs.existsSync('/opt/pw-browsers/chromium') ? '/opt/pw-browsers/chromium' : undefined });
  const out = [];
  const t = (n, v) => out.push((v ? 'OK   ' : 'FAIL ') + n);
  const errs = [];
  // Desktop
  let p = await b.newPage({ viewport: { width: 1440, height: 900 } });
  p.on('pageerror', e => errs.push(e.message));
  await p.goto(BASE + '/', { waitUntil: 'networkidle' });
  await p.click('#mitsDropdownArrow');
  t('стрілка відкриває панель', await p.$eval('#mitsDropdown', e => e.classList.contains('is-open')));
  await p.click('body', { position: { x: 700, y: 800 } });
  t('клік поза панеллю закриває', !(await p.$eval('#mitsDropdown', e => e.classList.contains('is-open'))));
  const hdr = p.locator('.mits-advantages .mits-adv-item__header').first();
  await hdr.click();
  t('акордеон «Переваги» відкривається', (await hdr.getAttribute('aria-expanded')) === 'true');
  await p.locator('.mits-hero__dot').nth(3).click();
  t('крапка слайдера перемикає слайд', await p.$eval('.mits-hero__slide:nth-child(4)', e => e.classList.contains('is-active')));
  await p.goto(BASE + '/%D0%BD%D0%B0%D1%83%D0%BA%D0%B0/', { waitUntil: 'networkidle' });
  await p.locator('.mits-nauka__tab').nth(1).click();
  t('вкладка року «Науки» перемикається', !(await p.$eval('#nauka-year-2024', e => e.hidden)) && await p.$eval('#nauka-year-2025', e => e.hidden));
  const nh = p.locator('[data-nauka-accordion] .mits-adv-item__header').first();
  await nh.click();
  t('акордеон «Науки» відкривається', (await nh.getAttribute('aria-expanded')) === 'true');
  await p.goto(BASE + '/%D0%B2%D1%81%D1%82%D1%83%D0%BF/', { waitUntil: 'networkidle' });
  const fh = p.locator('.mits-vstup-faq .mits-adv-item__header').first();
  await fh.click();
  t('FAQ відкривається', (await fh.getAttribute('aria-expanded')) === 'true');
  // Форма: перевірка обов'язкових полів CF7 (без надсилання листа)
  await p.click('.mits-footer-form input.wpcf7-submit');
  await p.waitForTimeout(2500);
  t('CF7: незаповнена форма показує помилки', (await p.locator('.mits-footer-form .wpcf7-not-valid-tip').count()) > 0);
  await p.close();
  // Mobile
  p = await b.newPage({ viewport: { width: 390, height: 800 }, hasTouch: true });
  p.on('pageerror', e => errs.push(e.message));
  await p.goto(BASE + '/', { waitUntil: 'networkidle' });
  await p.click('#mitsHamburger');
  t('мобільне меню відкривається', await p.$eval('.mits-nav:not(.mits-footer-nav) .mits-nav__menu', e => e.classList.contains('is-open')));
  t('без помилок JS', errs.length === 0);
  console.log(out.join('\n')); if (errs.length) console.log(errs);
  process.exitCode = out.some(l => l.startsWith('FAIL')) ? 1 : 0;
  await b.close();
})();
