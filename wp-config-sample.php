<?php
/**
 * Зразок wp-config.php для сайту кафедри МІТС (mits-khpi.kh.ua).
 *
 * Скопіюйте у корінь WordPress як wp-config.php, заповніть значення і НЕ додавайте
 * справжній файл у git (він у .gitignore). Покроково — docs/DEPLOY.md.
 */

// ** База даних (дані з панелі хостингу ukraine.com.ua → Бази даних MySQL) ** //
define( 'DB_NAME', 'назва_бази' );
define( 'DB_USER', 'користувач_бази' );
define( 'DB_PASSWORD', 'пароль_бази' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

/*
 * Ключі й солі: згенеруйте нові (https://api.wordpress.org/secret-key/1.1/salt/ або
 * `wp config shuffle-salts`) і вставте замість рядків нижче. Ніколи не беріть їх зі старого сайту.
 */
define( 'AUTH_KEY',         'вставте сюди унікальний рядок' );
define( 'SECURE_AUTH_KEY',  'вставте сюди унікальний рядок' );
define( 'LOGGED_IN_KEY',    'вставте сюди унікальний рядок' );
define( 'NONCE_KEY',        'вставте сюди унікальний рядок' );
define( 'AUTH_SALT',        'вставте сюди унікальний рядок' );
define( 'SECURE_AUTH_SALT', 'вставте сюди унікальний рядок' );
define( 'LOGGED_IN_SALT',   'вставте сюди унікальний рядок' );
define( 'NONCE_SALT',       'вставте сюди унікальний рядок' );

/*
 * Префікс таблиць: нестандартний (не wp_ і не старий wp_mits2). Наприклад, кілька
 * випадкових літер і цифр із підкресленням у кінці: 'k7mz_'. Задається один раз до встановлення.
 */
$table_prefix = 'змініть_';

// ** Адреса сайту ** //
define( 'WP_HOME', 'https://mits-khpi.kh.ua' );
define( 'WP_SITEURL', 'https://mits-khpi.kh.ua' );
define( 'FORCE_SSL_ADMIN', true );

// ** Безпека ** //
// Редагування файлів тем і плагінів з адмінки вимкнено завжди.
define( 'DISALLOW_FILE_EDIT', true );
/*
 * Після налаштування (плагіни встановлено, мови завантажено) увімкніть і цю заборону:
 * встановлення/оновлення плагінів і тем з адмінки стане неможливим, лише через SSH (wp-cli).
 * Автоматичні оновлення безпеки ядра при цьому теж вимикаються, тому оновлюйте через
 * cron-завдання з docs/DEPLOY.md (wp core update --minor && wp plugin update --all).
 */
// define( 'DISALLOW_FILE_MODS', true );

// Автоматичні оновлення безпеки ядра (мінорні версії) — увімкнені.
define( 'WP_AUTO_UPDATE_CORE', 'minor' );

// Двофакторний вхід обов'язковий для адміністраторів (плагін Two Factor): без нього
// адміністратор бачить лише сторінку профілю, де налаштовує другий фактор.
define( 'MITS_REQUIRE_2FA', true );

// Кошик і ревізії: помірно, щоб база не розросталася.
define( 'WP_POST_REVISIONS', 10 );
define( 'EMPTY_TRASH_DAYS', 30 );

// WP-Cron запускається системним cron хостингу (див. docs/DEPLOY.md), а не відвідувачами.
define( 'DISABLE_WP_CRON', true );

// Налагодження на робочому сайті вимкнене; помилки — лише в журнал поза веб-доступом.
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', '0' ); // phpcs:ignore

define( 'WP_ENVIRONMENT_TYPE', 'production' );

/* Далі нічого не змінюйте. */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
