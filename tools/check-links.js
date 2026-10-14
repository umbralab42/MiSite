#!/usr/bin/env node
/**
 * Перевірка внутрішніх посилань: обходить сайт від головних сторінок обох мов,
 * збирає посилання (a href), зображення (src, srcset), стилі, скрипти, iframe
 * і перевіряє, що кожна внутрішня адреса відповідає 200 (після переадресацій).
 *
 *   node tools/check-links.js http://localhost:8080
 */
const BASE = (process.argv[2] || 'http://localhost:8080').replace(/\/$/, '');
const START = ['/', '/en/', '/news/', '/teachers/', '/en/news/', '/en/teachers/'];
const origin = new URL(BASE).origin;

const seen = new Map();      // url -> status
const from = new Map();      // url -> сторінка, де знайдено
const queue = [...START.map(p => BASE + p)];
const pages = new Set();

function norm(href, base) {
  try {
    const u = new URL(href, base);
    if (u.origin !== origin) return null;
    u.hash = '';
    return u.href;
  } catch (e) { return null; }
}

async function check(url) {
  if (seen.has(url)) return seen.get(url);
  let res;
  try { res = await fetch(url, { redirect: 'follow' }); } catch (e) { seen.set(url, 'ERR'); return 'ERR'; }
  seen.set(url, res.status);
  const type = res.headers.get('content-type') || '';
  if (res.status === 200 && type.includes('text/html') && !url.includes('/wp-admin') && !url.includes('/wp-json') && !url.includes('/feed')) {
    const html = await res.text();
    const finalUrl = res.url;
    if (!pages.has(finalUrl)) {
      pages.add(finalUrl);
      const found = [];
      for (const m of html.matchAll(/<(a|link|area)\b[^>]*\bhref=["']([^"']+)["']/gi)) if (!/rel=["'](?:dns-prefetch|preconnect|alternate\b[^"']*type)/i.test(m[0])) found.push(m[2]);
      for (const m of html.matchAll(/<(img|script|iframe|source)\b[^>]*\bsrc=["']([^"']+)["']/gi)) found.push(m[2]);
      for (const m of html.matchAll(/\bsrcset=["']([^"']+)["']/gi)) m[1].split(',').forEach(s => found.push(s.trim().split(/\s+/)[0]));
      for (const h of found) {
        const decoded = h.replace(/&#0?38;|&amp;/g, '&');
        if (/^(mailto|tel|javascript|data):/i.test(decoded)) continue;
        if (/^https?:\/\/(dev\.)?mits-khpi\.kh\.ua/i.test(decoded)) { seen.set(decoded, 'OLD-DOMAIN'); from.set(decoded, finalUrl); continue; }
        const u = norm(decoded, finalUrl);
        if (!u || /\/wp-(admin|login)|xmlrpc|\/feed\/|\/wp-json\/|\?replytocom|\/comments\/feed/.test(u)) continue;
        if (!from.has(u)) from.set(u, finalUrl);
        if (!seen.has(u)) queue.push(u);
      }
    }
  }
  return res.status;
}

(async () => {
  while (queue.length) {
    const batch = queue.splice(0, 8);
    await Promise.all(batch.map(check));
  }
  const bad = [...seen].filter(([, s]) => s !== 200);
  console.log(`Сторінок обійдено: ${pages.size}; перевірено адрес: ${seen.size}; битих: ${bad.length}`);
  for (const [u, s] of bad) console.log(`  ${s}  ${decodeURI(u)}  (на сторінці ${decodeURI(from.get(u) || '-')})`);
  process.exit(bad.length ? 1 : 0);
})();
