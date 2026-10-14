# MiSite

Новий сайт кафедри мультимедійних та інтернет технологій і систем (МІТС) НТУ «ХПІ» на WordPress.

- Завдання й вимоги: [CLAUDE.md](CLAUDE.md)
- Еталон дизайну (статична копія сайту): [`reference/static-site/`](reference/static-site/)
- Вміст для імпорту: [`content/content.json`](content/content.json)
- Розгортання на хостингу: [docs/DEPLOY.md](docs/DEPLOY.md)
- Для редакторів: [docs/EDITING.md](docs/EDITING.md)

## Що в репозиторії

| Шлях | Що це |
|---|---|
| `wp-content/themes/mits/` | Тема (класична PHP, без батьківської). Шаблони відтворюють розмітку статичної копії. |
| `wp-content/plugins/mits-core/` | Плагін: типи записів, поля, сторінка «МІТС: блоки», сторінка «Наука», шорткоди контактів, Polylang, захист, команда `wp mits import`. |
| `docker-compose.yml`, `bin/setup.sh` | Локальна копія сайту однією командою. |
| `deploy/` | `.htaccess` кореня (заголовки безпеки, заборони) і `.htaccess` для `uploads`. |
| `wp-config-sample.php` | Зразок конфігурації з поясненнями (справжній `wp-config.php` у git не потрапляє). |
| `tools/` | Перевірка: порівняння з еталоном (знімки + DOM), биті посилання, адмінка; витяг CSS з betheme. |

## Локальна копія

Потрібен Docker.

```bash
cp .env.example .env                 # за бажанням змінити паролі
docker compose up -d                 # MariaDB + WordPress на http://localhost:8080
docker compose run --rm cli bash /project/bin/setup.sh
```

`setup.sh` встановлює WordPress (пароль адміністратора виводить один раз), українську мову,
безкоштовні плагіни (Polylang, Contact Form 7, Limit Login Attempts Reloaded, Two Factor),
вмикає тему й плагін і запускає імпорт вмісту. Тема й плагін змонтовані з репозиторію:
зміни в коді видно одразу.

Повторний імпорт: `docker compose run --rm cli wp mits import /project/content/content.json --force`.
Усе з нуля: `docker compose down -v`.

Переглянути еталон:

```bash
npx serve reference/static-site        # або: python3 -m http.server 8081 -d reference/static-site
```

## Перевірка

```bash
cd tools && npm install                 # Playwright, pixelmatch, pngjs
python3 -m http.server 8081 -d ../reference/static-site &
node compare.js --static=http://localhost:8081 --wp=http://localhost:8080 --out=/tmp/cmp --width=1440
node compare.js --static=http://localhost:8081 --wp=http://localhost:8080 --out=/tmp/cmp390 --width=390
node check-links.js http://localhost:8080
node check-interactive.js http://localhost:8080
node check-admin.js http://localhost:8080 <логін> <пароль>
```

`compare.js` для кожної з 92 сторінок еталона відкриває ту саму адресу на WordPress, порівнює
структуру DOM (теги, класи, тексти, адреси) і знімки екрана (частка пікселів, що відрізняються;
файли `*.diff.png` показують, де саме).

## Стилі

`css/mits-*.css` перенесено зі старої теми без змін. Від betheme (`be.css`, `responsive.css`,
згенеровані налаштування) потрібні лише правила, що реально застосовуються до розмітки сайту:
їх зібрано в `css/mits-base.css` і `css/mits-settings.css` скриптом
`tools/extract-betheme-css.js` (обходить усі сторінки еталона й лишає тільки правила, селектори
яких знаходять елементи). Власні доповнення — `css/mits-theme.css`.
