<?php
/**
 * Configuration overrides for WP_ENV === 'development'
 */

use Roots\WPConfig\Config;
use function Env\env;

Config::define('SAVEQUERIES', true);
Config::define('WP_DEBUG', true);
Config::define('WP_DEBUG_DISPLAY', true);
Config::define('WP_DEBUG_LOG', env('WP_DEBUG_LOG') ?? true);
Config::define('WP_DISABLE_FATAL_ERROR_HANDLER', true);
Config::define('SCRIPT_DEBUG', true);
Config::define('DISALLOW_INDEXING', true);
Config::define('DB_HOST', '127.0.0.1');


ini_set('display_errors', '1');

// Enable plugin and theme updates and installation from the admin
Config::define('DISALLOW_FILE_MODS', false);

/**
 * Custom Settings
 */
Config::define('AUTOMATIC_UPDATER_DISABLED', false);
Config::define('DISABLE_WP_CRON', false);
Config::define('DISALLOW_FILE_EDIT', true);
Config::define('WP_POST_REVISIONS', 25);
Config::define('WP_MEMORY_LIMIT', '128M');
set_time_limit(180);
