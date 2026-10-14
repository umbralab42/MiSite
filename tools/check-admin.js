#!/usr/bin/env node
/**
 * Перевірка адмінки в браузері (Playwright):
 *  1) зміна тексту блоку («МІТС: блоки → Головна → Чому ми?») і його поява на сайті;
 *  2) зміна поля сторінки «Наука» (метабокс у редакторі блоків);
 *  3) новий викладач українською + переклад англійською (кнопка «+» Polylang);
 *  4) нова новина українською + переклад англійською (редактор блоків).
 * Змінені тексти повертаються; ID тестових записів пишуться в /tmp/mits-admin-test-ids.json
 * (видалити: wp post delete <ID…> --force).
 *
 *   node tools/check-admin.js http://localhost:8080 admin admin-local
 */
const { chromium } = require('playwright');
const fs = require('fs');

const [BASE = 'http://localhost:8080', USER = 'admin', PASS = 'admin-local'] = process.argv.slice(2);
const STAMP = 'Тест ' + Date.now().toString(36);
const results = [];
const ok = (name, cond, extra = '') => { results.push([cond ? 'OK  ' : 'FAIL', name, extra]); console.log((cond ? 'OK   ' : 'FAIL ') + name + (extra ? ' — ' + extra : '')); };

(async () => {
  const browser = await chromium.launch({ executablePath: fs.existsSync('/opt/pw-browsers/chromium') ? '/opt/pw-browsers/chromium' : undefined });
  const ctx = await browser.newContext({ viewport: { width: 1400, height: 1000 } });
  const page = await ctx.newPage();
  const phpErrors = [];
  page.on('response', async (r) => {
    if (r.url().startsWith(BASE) && (r.headers()['content-type'] || '').includes('text/html')) {
      const t = await r.text().catch(() => '');
      if (/<b>(Fatal error|Warning|Notice|Deprecated)<\/b>|Uncaught /.test(t)) phpErrors.push(r.url());
    }
  });
  const front = async (path) => { const p = await ctx.newPage(); await p.goto(BASE + path); const h = await p.content(); await p.close(); return h; };

  // Вхід.
  await page.goto(BASE + '/wp-login.php');
  await page.fill('#user_login', USER);
  await page.fill('#user_pass', PASS);
  await Promise.all([page.waitForNavigation(), page.click('#wp-submit')]);
  ok('вхід в адмінку', page.url().includes('/wp-admin'));

  // 1. Блок «Чому ми?» (обидві мови).
  await page.goto(BASE + '/wp-admin/admin.php?page=mits-blocks&tab=front');
  const ukTitle = 'input[name="mits_blocks[uk][mits_why][title]"]';
  const enTitle = 'input[name="mits_blocks[en][mits_why][title]"]';
  const oldUk = await page.inputValue(ukTitle), oldEn = await page.inputValue(enTitle);
  await page.fill(ukTitle, oldUk + ' ' + STAMP);
  await page.fill(enTitle, oldEn + ' ' + STAMP);
  await Promise.all([page.waitForNavigation(), page.click('#submit')]);
  ok('збереження «МІТС: блоки»', page.url().includes('saved=1'));
  ok('новий заголовок «Чому ми?» на головній UA', (await front('/')).includes(STAMP));
  ok('новий заголовок «Why choose us?» на головній EN', (await front('/en/home-en/')).includes(STAMP));
  await page.fill(ukTitle, oldUk); await page.fill(enTitle, oldEn);
  await Promise.all([page.waitForNavigation(), page.click('#submit')]);

  // 2. Поле сторінки «Наука» (редактор блоків + метабокс).
  const naukaId = await (async () => { await page.goto(BASE + '/wp-admin/edit.php?post_type=page&lang=uk&s=%D0%9D%D0%B0%D1%83%D0%BA%D0%B0'); const href = await page.getAttribute('a.row-title', 'href'); return href && href.match(/post=(\d+)/)[1]; })();
  await page.goto(BASE + `/wp-admin/post.php?post=${naukaId}&action=edit`);
  await page.waitForSelector('input[name="mits_meta[_nauka_ipst_title]"]', { timeout: 60000 });
  const closeWelcome = page.locator('.components-modal__frame button[aria-label="Close"], .components-modal__frame button[aria-label="Закрити"]');
  if (await closeWelcome.count()) await closeWelcome.first().click();
  const oldIpst = await page.inputValue('input[name="mits_meta[_nauka_ipst_title]"]');
  await page.fill('input[name="mits_meta[_nauka_ipst_title]"]', oldIpst + ' ' + STAMP);
  await page.click('.editor-header__settings button.editor-post-publish-button, .edit-post-header__settings button.editor-post-publish-button');
  await page.waitForTimeout(4000);
  ok('поле «IPST» сторінки «Наука» збережено й видно на сайті', (await front('/%D0%BD%D0%B0%D1%83%D0%BA%D0%B0/')).includes(STAMP));
  await page.fill('input[name="mits_meta[_nauka_ipst_title]"]', oldIpst);
  await page.click('.editor-header__settings button.editor-post-publish-button, .edit-post-header__settings button.editor-post-publish-button');
  await page.waitForTimeout(4000);

  // 3. Викладач (класичний редактор).
  await page.goto(BASE + '/wp-admin/post-new.php?post_type=teacher&new_lang=uk');
  await page.fill('#title', 'Тестовий Викладач ' + STAMP);
  await page.fill('input[name="mits_meta[_teacher_position]"]', 'доцент');
  await page.fill('input[name="mits_meta[_teacher_grade]"]', 'кандидат технічних наук');
  await page.click('.mits-repeater__add');
  await page.fill('.mits-repeater__rows input[type=text]', 'програмування');
  await page.fill('#menu_order', '99');
  await Promise.all([page.waitForNavigation(), page.click('#publish')]);
  const teacherId = new URL(page.url()).searchParams.get('post');
  ok('новий викладач UA опублікований', !!teacherId);
  const ukTeachers = await front('/teachers/');
  ok('викладач UA у списку /teachers/', ukTeachers.includes('Тестовий Викладач ' + STAMP));
  const addEn = await page.getAttribute('a.pll_icon_add[href*="new_lang=en"], #post-translations a[href*="new_lang=en"]', 'href').catch(() => null);
  await page.goto(addEn ? (addEn.startsWith('http') ? addEn : BASE + '/wp-admin/' + addEn) : BASE + `/wp-admin/post-new.php?post_type=teacher&from_post=${teacherId}&new_lang=en`);
  await page.fill('#title', 'Test Teacher ' + STAMP);
  await page.fill('input[name="mits_meta[_teacher_position]"]', 'Associate Professor');
  await Promise.all([page.waitForNavigation(), page.click('#publish')]);
  const teacherEnId = new URL(page.url()).searchParams.get('post');
  const enTeachers = await front('/en/teachers/');
  ok('переклад викладача EN у списку /en/teachers/', enTeachers.includes('Test Teacher ' + STAMP));
  ok('англійського викладача немає в українському списку', !(await front('/teachers/')).includes('Test Teacher ' + STAMP));

  // 4. Новина (редактор блоків).
  await page.goto(BASE + '/wp-admin/post-new.php?post_type=news&new_lang=uk');
  await page.waitForSelector('.editor-post-title__input, h1.wp-block-post-title, [aria-label="Add title"], [aria-label="Додати заголовок"]', { timeout: 60000 });
  if (await closeWelcome.count()) await closeWelcome.first().click().catch(() => {});
  const frame = page.frameLocator('iframe[name="editor-canvas"]');
  const hasFrame = await page.locator('iframe[name="editor-canvas"]').count();
  const root = hasFrame ? frame : page;
  await root.locator('.editor-post-title__input, h1.wp-block-post-title').first().click();
  await page.keyboard.type('Тестова новина ' + STAMP);
  await page.keyboard.press('Enter');
  await page.keyboard.type('Текст тестової новини.');
  await page.click('.editor-header__settings .editor-post-publish-panel__toggle, .edit-post-header__settings .editor-post-publish-panel__toggle');
  await page.click('.editor-post-publish-panel__header-publish-button button');
  await page.waitForSelector('.post-publish-panel__postpublish', { timeout: 30000 });
  const newsId = await page.evaluate(() => window.wp && wp.data.select('core/editor').getCurrentPostId());
  ok('нова новина UA опублікована', !!newsId);
  ok('новина UA на головній', (await front('/')).includes('Тестова новина ' + STAMP));
  await page.goto(BASE + `/wp-admin/post-new.php?post_type=news&from_post=${newsId}&new_lang=en`);
  await page.waitForSelector('iframe[name="editor-canvas"], .editor-post-title__input', { timeout: 60000 });
  await page.waitForTimeout(1500);
  const root2 = (await page.locator('iframe[name="editor-canvas"]').count()) ? page.frameLocator('iframe[name="editor-canvas"]') : page;
  const title2 = root2.locator('.editor-post-title__input, h1.wp-block-post-title').first();
  await title2.click();
  await page.keyboard.press('Control+A');
  await page.keyboard.type('Test news ' + STAMP);
  await page.click('.editor-header__settings .editor-post-publish-panel__toggle, .edit-post-header__settings .editor-post-publish-panel__toggle');
  await page.click('.editor-post-publish-panel__header-publish-button button');
  await page.waitForSelector('.post-publish-panel__postpublish', { timeout: 30000 });
  const newsEnId = await page.evaluate(() => window.wp && wp.data.select('core/editor').getCurrentPostId());
  ok('переклад новини EN на головній EN', (await front('/en/home-en/')).includes('Test news ' + STAMP));
  const enNews = await front('/en/news/');
  ok('новина EN у списку /en/news/', enNews.includes('Test news ' + STAMP));
  ok('англійської новини немає в українському списку', !(await front('/news/')).includes('Test news ' + STAMP));

  ok('без PHP-помилок у відповідях', phpErrors.length === 0, phpErrors.slice(0, 3).join(', '));

  // Тестові записи видаляє той, хто запускав (ID — у /tmp/mits-admin-test-ids.json):
  //   wp post delete <id> --force
  fs.writeFileSync('/tmp/mits-admin-test-ids.json', JSON.stringify({ teacherId, teacherEnId, newsId, newsEnId }));
  await browser.close();
  console.log(results.filter(r => r[0] !== 'OK  ').length ? '\nЄ помилки' : '\nУсе гаразд');
  process.exit(results.filter(r => r[0] !== 'OK  ').length ? 1 : 0);
})();
