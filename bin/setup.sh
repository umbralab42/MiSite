#!/usr/bin/env bash
# Встановлення локальної копії сайту МІТС у контейнері WP-CLI.
#
#   docker compose up -d
#   docker compose run --rm cli bash /project/bin/setup.sh
#
# Що робить: встановлює WordPress (якщо ще не встановлено), українську мову адмінки,
# безкоштовні плагіни з wordpress.org, вмикає тему mits і плагін mits-core, імпортує вміст.
# Пароль адміністратора генерується й виводиться один раз (або задайте ADMIN_PASSWORD).
set -euo pipefail

cd /var/www/html
URL="${WP_HOME:-http://localhost:${WP_PORT:-8080}}"
ADMIN_USER="${ADMIN_USER:-mits-admin}"
ADMIN_EMAIL="${ADMIN_EMAIL:-admin@example.test}"
PLUGINS="polylang contact-form-7 limit-login-attempts-reloaded two-factor"

if ! wp core is-installed 2>/dev/null; then
  ADMIN_PASSWORD="${ADMIN_PASSWORD:-$(head -c 18 /dev/urandom | base64 | tr -d '/+=')}"
  wp core install --url="$URL" --title="МІТС" --admin_user="$ADMIN_USER" \
    --admin_password="$ADMIN_PASSWORD" --admin_email="$ADMIN_EMAIL" --skip-email
  echo "Адміністратор: $ADMIN_USER / $ADMIN_PASSWORD (збережіть пароль; увімкніть двофакторний вхід у профілі)"
fi

wp language core install uk --activate || echo "Мовний пакет uk недоступний (немає доступу до wordpress.org) — адмінка буде англійською."

for p in $PLUGINS; do
  if wp plugin is-installed "$p"; then
    wp plugin activate "$p" || true
  else
    wp plugin install "$p" --activate || echo "Не вдалося встановити $p (немає доступу до wordpress.org?)."
  fi
done
wp language plugin install --all uk >/dev/null 2>&1 || true

wp theme activate mits
wp plugin activate mits-core
wp rewrite structure '/%postname%/'

# Зайве з типового встановлення.
wp plugin delete akismet hello >/dev/null 2>&1 || true
for t in $(wp theme list --field=name); do [ "$t" != "mits" ] && wp theme delete "$t" >/dev/null 2>&1 || true; done

if [ "${SKIP_IMPORT:-0}" != "1" ]; then
  wp mits import /project/content/content.json --static=/project/reference/static-site ${IMPORT_ARGS:-}
fi
wp rewrite flush
echo "Готово: $URL"
