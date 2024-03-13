<?php
/**
 * Configuration overrides for WP_ENV === 'staging'
 */

use Roots\WPConfig\Config;

/**
 * You should try to keep staging as close to production as possible. However,
 * should you need to, you can always override production configuration values
 * with `Config::define`.
 *
 * Example: `Config::define('WP_DEBUG', true);`
 * Example: `Config::define('DISALLOW_FILE_MODS', false);`
 */

 // Enable plugin and theme updates and installation from the admin
Config::define('DISALLOW_FILE_MODS', false);

/**
 * Custom Settings
 */
Config::define('AUTOMATIC_UPDATER_DISABLED', true);
Config::define('DISABLE_WP_CRON', false);
Config::define('DISALLOW_FILE_EDIT', true);
Config::define('WP_POST_REVISIONS', 25);
Config::define('WP_MEMORY_LIMIT', '128M');
Config::define('WP_CACHE', true ); // Added by WP Rocket
set_time_limit(180);

/**
 * Debug
 */
// ini_set('display_errors', 0);
// Config::define('WP_DEBUG_DISPLAY', false);
// Config::define('SCRIPT_DEBUG', false);
