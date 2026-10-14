# Розгортання сайту МІТС на хостингу ukraine.com.ua

Інструкція для власника, який має SSH-доступ до хостингу. Усе виконується з консолі SSH
у каталозі акаунта. Приклади команд припускають, що сайт лежить у
`~/mits-khpi.kh.ua/www` (на ukraine.com.ua каталог сайту зазвичай `~/<домен>/www`;
перевірте у панелі: «Хостинг → Сайти»).

> Старий сайт було зламано. **Нічого не переносимо зі старих файлів і старої бази**:
> ні `wp-content`, ні дамп бази, ні `wp-config.php`. Вміст переноситься лише імпортом
> із репозиторію (`content/content.json` + файли зі статичної копії).

## 0. Що знадобиться

- PHP 8.3 (панель: «Хостинг → Налаштування PHP» — версія 8.3, модулі `mysqli`, `gd` або `imagick`, `intl`, `mbstring`, `dom`).
- Нова база MySQL/MariaDB з окремим користувачем і довгим випадковим паролем
  (панель: «Хостинг → Бази даних»).
- WP-CLI. Перевірте `wp --info`. Якщо команди немає:
  ```bash
  mkdir -p ~/bin && curl -o ~/bin/wp https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x ~/bin/wp && echo 'export PATH="$HOME/bin:$PATH"' >> ~/.bashrc && source ~/.bashrc
  ```
- SSL-сертифікат для домену (панель: «SSL-сертифікати» → Let's Encrypt).
- Копія цього репозиторію на сервері, **поза** веб-каталогом:
  ```bash
  cd ~ && git clone https://github.com/umbralab42/MiSite.git mits-src
  ```
  (або завантажте архів гілки й розпакуйте в `~/mits-src`).

## 1. Резервна копія і чистий каталог

```bash
cd ~/mits-khpi.kh.ua
tar czf ~/old-site-$(date +%F).tar.gz www   # архів старих файлів — лише для довідки, на сайт не повертати
mv www www-old-$(date +%F) && mkdir www && cd www
```

Дамп старої бази зробіть у панелі й зберігайте окремо від сайту. Стару базу після запуску
нового сайту видаліть (або хоча б змініть пароль її користувача).

## 2. Ядро WordPress і wp-config.php

```bash
cd ~/mits-khpi.kh.ua/www
wp core download --locale=uk
cp ~/mits-src/wp-config-sample.php wp-config.php
```

Відредагуйте `wp-config.php` (`nano wp-config.php`):

- `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST` — з панелі (хост зазвичай `localhost`);
- `$table_prefix` — свій нестандартний, наприклад `k7mz_` (не `wp_` і не `wp_mits2`);
- ключі й солі: після збереження виконайте `wp config shuffle-salts`;
- `WP_HOME` / `WP_SITEURL` — `https://mits-khpi.kh.ua`.

`DISALLOW_FILE_MODS` поки лишається закоментованим (увімкнемо в кроці 8).

## 3. Встановлення

```bash
wp core install --url=https://mits-khpi.kh.ua --title="МІТС" \
  --admin_user="<особистий логін, не admin>" --admin_email="<ваш e-mail>" --skip-email
# пароль буде показано один раз — збережіть у менеджері паролів
wp language core install uk --activate
```

## 4. Тема, плагіни, налаштування

```bash
cp -r ~/mits-src/wp-content/themes/mits     wp-content/themes/
cp -r ~/mits-src/wp-content/plugins/mits-core wp-content/plugins/

wp plugin install polylang contact-form-7 limit-login-attempts-reloaded two-factor --activate
wp language plugin install --all uk
wp theme activate mits
wp plugin activate mits-core
wp rewrite structure '/%postname%/'

# зайве з типового встановлення
wp plugin delete akismet hello
wp theme list --field=name | grep -v '^mits$' | xargs -r wp theme delete
```

Flamingo **не** встановлюйте: заявки з форми не мають зберігатися в базі.

## 5. Імпорт вмісту

```bash
wp mits import ~/mits-src/content/content.json \
  --static=$HOME/mits-src/reference/static-site \
  --recipient="<скринька, куди мають приходити заявки>"
wp rewrite flush
```

Імпорт:
- створює мови Polylang (українська без префікса, англійська з `/en/`);
- копіює файли з `reference/static-site/wp-content/uploads/` у `wp-content/uploads/`;
- створює сторінки, новини, викладачів, слайди, блоки, форми CF7, меню обома мовами
  з тими самими ID, що були у старій базі;
- англійські тексти, яких не було в базі, бере зі статичної копії `en/`;
- записує `.htaccess` із забороною виконання PHP у `wp-content/uploads/`.

Повторний запуск можливий лише з `--force` (усі імпортовані записи буде видалено й створено знову;
зміни, зроблені після імпорту в адмінці, пропадуть).

Адресу одержувача заявок можна змінити будь-коли: «Контакт → Контактні форми → Консультація
(і Consultation) → вкладка «Лист» → «Кому»». У коді й у репозиторії її немає.

## 6. .htaccess, заголовки безпеки, права

```bash
cp ~/mits-src/deploy/htaccess .htaccess
cp ~/mits-src/deploy/uploads.htaccess wp-content/uploads/.htaccess   # якщо імпорт його ще не створив

find . -type d -exec chmod 755 {} \;
find . -type f -exec chmod 644 {} \;
chmod 600 wp-config.php
```

`.htaccess` кореня: блокує `xmlrpc.php`, `?author=N`, виконання PHP у `uploads` і
`wp-includes`, додає заголовки `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`,
`Permissions-Policy` і базовий `Content-Security-Policy` (дозволено Google Fonts, вбудовані
YouTube і Google Maps). Якщо після змін у дизайні щось не завантажується — дивіться консоль
браузера: блокування CSP видно там.

Перевірка заголовків:
```bash
curl -sI https://mits-khpi.kh.ua/ | grep -iE 'x-content|x-frame|referrer|content-security'
curl -s -o /dev/null -w '%{http_code}\n' https://mits-khpi.kh.ua/xmlrpc.php        # 403
curl -s -o /dev/null -w '%{http_code}\n' "https://mits-khpi.kh.ua/?author=1"       # 403 або 404
curl -s https://mits-khpi.kh.ua/wp-json/wp/v2/users                                # rest_no_route
```

## 7. Cron

У `wp-config.php` стоїть `DISABLE_WP_CRON`, тому завдання WordPress запускає cron хостингу
(панель: «Хостинг → Планувальник Cron»):

| Коли | Команда |
|---|---|
| кожні 10 хв | `cd ~/mits-khpi.kh.ua/www && wp cron event run --due-now --quiet` |
| щодня о 04:20 | `cd ~/mits-khpi.kh.ua/www && wp core update --minor --quiet && wp plugin update --all --quiet && wp language core update --quiet && wp language plugin update --all --quiet` |

Друге завдання потрібне, бо після `DISALLOW_FILE_MODS` WordPress сам себе не оновлює.
Мажорні версії ядра (`wp core update` без `--minor`) встановлюйте вручну після перевірки
на локальній копії (`docker compose`, див. README).

## 8. Після налаштування

1. Увійдіть в адмінку, на сторінці профілю налаштуйте двофакторний вхід (Two Factor → TOTP,
   збережіть резервні коди). Доки цього не зроблено, адміністратору доступний лише профіль
   (`MITS_REQUIRE_2FA` у `wp-config.php`).
2. «Налаштування → Limit Login Attempts»: локальний режим, 4 спроби, блокування 20 хв,
   після 4 блокувань — 24 год.
3. Перевірте форму «Консультація» обома мовами (лист має прийти на адресу з кроку 5).
4. Увімкніть у `wp-config.php` рядок `define( 'DISALLOW_FILE_MODS', true );`.
5. Видаліть з сервера копію репозиторію або хоча б статичну копію з неї:
   `rm -rf ~/mits-src` (вона не потрібна для роботи сайту).
6. Видаліть `www-old-*` після того, як переконаєтеся, що нічого звідти не потрібно
   (не відкривайте старі файли через веб).

## 9. Оновлення коду теми/плагіна

```bash
cd ~/mits-src && git pull
rsync -a --delete ~/mits-src/wp-content/themes/mits/ ~/mits-khpi.kh.ua/www/wp-content/themes/mits/
rsync -a --delete ~/mits-src/wp-content/plugins/mits-core/ ~/mits-khpi.kh.ua/www/wp-content/plugins/mits-core/
```

Імпорт повторно не запускайте — він для першого наповнення.

## 10. Перевірка після розгортання

- Головна, `/en/`, усі пункти меню обома мовами, перемикач мов веде на відповідну сторінку.
- Сторінки, що на старому сайті давали 404: `/контакти/`, `/освіта/загальні-положення/`,
  `/освіта/бакалавр/`, `/освіта/магістратура/`, `/освіта/аспірантура/`, `/вступ/загальний-опис/`.
- `https://mits-khpi.kh.ua/wp-content/uploads/test.php` (створіть порожній файл і видаліть
  після перевірки) — має віддавати 403.
- «Інструменти → Здоров'я сайту»: без критичних зауважень.
