// Список сторінок статичної копії (без сторінок-переадресацій), як шляхи URL.
const fs = require('fs'), path = require('path');
const ROOT = path.join(__dirname, '../reference/static-site');
module.exports = function pages() {
  const out = [];
  (function walk(dir) {
    for (const f of fs.readdirSync(dir).sort()) {
      const p = path.join(dir, f);
      if (fs.statSync(p).isDirectory()) { if (!/^wp-|^form$/.test(f)) walk(p); }
      else if (f === 'index.html') {
        if (fs.readFileSync(p, 'utf8').includes('http-equiv="refresh"')) continue;
        out.push('/' + path.relative(ROOT, p).replace(/index\.html$/, '').split('/').map(encodeURIComponent).join('/'));
      }
    }
  })(ROOT);
  return out;
};
