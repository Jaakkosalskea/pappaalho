<?php
namespace Bricks;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php
// Theme Information
$theme_information = [];
$active_theme      = wp_get_theme();

$theme_information['theme_name'] = [
	'label' => esc_html__( 'Theme name', 'bricks' ),
	'data'  => $active_theme->Name,
];

$theme_information['theme_version'] = [
	'label' => esc_html__( 'Theme version', 'bricks' ),
	'data'  => $active_theme->Version,
];

$theme_information['theme_author'] = [
	'label' => esc_html__( 'Theme author', 'bricks' ),
	'data'  => $active_theme->get( 'Author' ),
];

$theme_information['theme_author_uri'] = [
	'label' => esc_html__( 'Theme author URI', 'bricks' ),
	'data'  => '<a href="' . esc_url( $active_theme->get( 'AuthorURI' ) ) . '" target="_blank">' . $active_theme->get( 'AuthorURI' ) . '</a>',
];

$theme_information['theme_is_child_theme'] = [
	'label' => esc_html__( 'Theme is child theme', 'bricks' ),
	'data'  => is_child_theme() ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-minus"></span>',
];

if ( is_child_theme() ) {
	$parent_theme = wp_get_theme( $active_theme->Template );

	$theme_information['parent_theme_name'] = [
		'label' => esc_html__( 'Parent theme name', 'bricks' ),
		'data'  => $parent_theme->Name,
	];

	$theme_information['parent_theme_version'] = [
		'label' => esc_html__( 'Parent theme version', 'bricks' ),
		'data'  => $parent_theme->Version,
	];

	$theme_information['parent_theme_uri'] = [
		'label' => esc_html__( 'Parent theme URI', 'bricks' ),
		'data'  => $parent_theme->get( 'ThemeURI' ),
	];

	$theme_information['parent_theme_author_uri'] = [
		'label' => esc_html__( 'Parent theme author URI', 'bricks' ),
		'data'  => $parent_theme->{'Author URI'},
	];
}
?>

<?php
// WordPress Environment
$wp_environment = [];

$wp_environment['home_url'] = [
	'label' => esc_html__( 'Home URL', 'bricks' ),
	'data'  => home_url(),
];

$wp_environment['site_url'] = [
	'label' => esc_html__( 'Site URL', 'bricks' ),
	'data'  => site_url(),
];

$wp_environment['site_url'] = [
	'label' => esc_html__( 'REST API Prefix', 'bricks' ),
	'data'  => rest_get_url_prefix() === 'wp-json' ? rest_get_url_prefix() : '<span class="text-warning">' . rest_get_url_prefix() . '</span>',
];

$wp_environment['wp_version'] = [
	'label' => esc_html__( 'WP version', 'bricks' ),
	'data'  => get_bloginfo( 'version' ),
];

$wp_environment['wp_debug'] = [
	'label' => esc_html__( 'WP debug', 'bricks' ),
	'data'  => defined( 'WP_DEBUG' ) ? WP_DEBUG ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-minus"></span>' : '<span class="dashicons dashicons-no-alt"></span>',
];

$wp_environment['wp_language'] = [
	'label' => esc_html__( 'WP language', 'bricks' ),
	'data'  => ( defined( 'WPLANG' ) && WPLANG ? WPLANG : 'en_US' ),
];

$wp_environment['wp_multisite'] = [
	'label' => esc_html__( 'WP multisite', 'bricks' ),
	'data'  => is_multisite() ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-minus"></span>',
];

function bricks_let_to_num( $v ) {
	$l   = substr( $v, -1 );
	$ret = substr( $v, 0, -1 );

	switch ( strtoupper( $l ) ) {
		case 'P':
		case 'T':
		case 'G':
		case 'M':
		case 'K':
			$ret *= 1024;
			break;
		default:
			break;
	}

	return $ret;
}

$memory_limit = bricks_let_to_num( WP_MEMORY_LIMIT ) / 1024;

$wp_environment['wp_memory_limit'] = [
	'label' => esc_html__( 'WP memory limit', 'bricks' ),
	'data'  => '<span class="' . ( $memory_limit >= 64 ? 'text-success' : 'text-warning' ) . '">' . $memory_limit . 'MB' . ( $memory_limit >= 64 ? '' : ' - ' . esc_html__( 'Recommended wp_memory_limit: 64M (or more)', 'bricks' ) ) . '</span><a href="https://academy.bricksbuilder.io/article/requirements/#wp-memory-limit" target="_blank" rel="noopener"><i class="dashicons dashicons-editor-help"></i></a>',
];
?>

<?php
// Server Environment
$server_environment = [];

$server_environment['server_info'] = [
	'label' => esc_html__( 'Server info', 'bricks' ),
	'data'  => $_SERVER['SERVER_SOFTWARE'],
];

global $wpdb;

if ( $wpdb->use_mysqli ) {
	$mysql_version = @mysqli_get_server_info( $wpdb->dbh );
} else {
	$mysql_version = @mysql_get_server_info();
}

$server_environment['server_mysql_version'] = [
	'label' => esc_html__( 'MySQL version', 'bricks' ),
	'data'  => $mysql_version,
];

if ( function_exists( 'phpversion' ) ) {
	$php_version                              = phpversion();
	$server_environment['server_php_version'] = [
		'label' => esc_html__( 'PHP version', 'bricks' ),
		'data'  => '<span class="' . ( $php_version >= 5.4 ? 'text-success' : 'text-danger' ) . '">' . $php_version . ( $php_version >= 5.4 ? '' : ' - ' . esc_html__( 'Min. PHP version to run Bricks is PHP 5.4', 'bricks' ) ) . '</span>',
	];
}

$server_environment['server_php_post_max_size'] = [
	'label' => esc_html__( 'PHP post max size', 'bricks' ),
	'data'  => ini_get( 'post_max_size' ),
];

$max_execution_time = ini_get( 'max_execution_time' );

$server_environment['server_php_time_limit'] = [
	'label' => esc_html__( 'PHP execution time limit', 'bricks' ),
	'data'  => '<span class="' . ( $max_execution_time >= 180 ? 'text-success' : 'text-warning' ) . '">' . $max_execution_time . ( $max_execution_time >= 180 ? '' : ' - ' . esc_html__( 'Recommended max_execution_time: 180 (or more)', 'bricks' ) ) . '</span><a href="https://academy.bricksbuilder.io/article/requirements/#max-execution-time" target="_blank" rel="noopener"><i class="dashicons dashicons-editor-help"></i></a>',
];

$server_environment['server_php_max_input_vars'] = [
	'label' => esc_html__( 'PHP max input vars', 'bricks' ),
	'data'  => ini_get( 'max_input_vars' ),
];

$server_environment['server_php_safe_mode'] = [
	'label' => esc_html__( 'PHP safe mode', 'bricks' ),
	'data'  => ini_get( 'safe_mode' ) ? '<span class="dashicons dashicons-yes"></span>' : '<span class="dashicons dashicons-minus"></span>',
];

$server_environment['server_php_memory_limit'] = [
	'label' => esc_html__( 'PHP memory limit', 'bricks' ),
	'data'  => ini_get( 'memory_limit' ),
];

$server_environment['server_php_upload_max_filesize'] = [
	'label' => esc_html__( 'PHP max upload file size', 'bricks' ),
	'data'  => '<span class="' . ( (int) ini_get( 'upload_max_filesize' ) >= 16 ? 'text-success' : 'text-danger' ) . '">' . ini_get( 'upload_max_filesize' ) . ( (int) ini_get( 'upload_max_filesize' ) >= 16 ? '' : ' - ' . esc_html__( 'Recommended upload_max_filesize: 16M (or more)', 'bricks' ) ) . '</span><a href="https://academy.bricksbuilder.io/article/requirements/#max-file-upload-size" target="_blank" rel="noopener"><i class="dashicons dashicons-editor-help"></i></a>',
];
?>

<?php
// Active Plugins
if ( ! function_exists( 'get_plugins' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$get_plugins        = get_plugins();
$get_active_plugins = get_option( 'active_plugins', [] );
$active_plugins     = [];

foreach ( $get_plugins as $plugin_path => $plugin ) {
	if ( ! in_array( $plugin_path, $get_active_plugins ) ) {
		continue;
	}

	$active_plugins[] = [
		'name'    => $plugin['Name'],
		'version' => $plugin['Version'],
		'author'  => $plugin['Author'],
		'uri'     => $plugin['PluginURI'],
	];
}
?>

<div class="wrap bricks-admin-wrapper system-information">
	<h1 class="admin-notices-placeholder"></h1>

	<h1 class="title"><?php echo esc_html__( 'System Information', 'bricks' ); ?></h1>

  <table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Theme Information', 'bricks' ); ?></th>
			</tr>
		</thead>
		<tbody>
	  <?php foreach ( $theme_information as $theme_data ) { ?>
		<tr>
		  <td class="label"><?php echo esc_html( $theme_data['label'] ); ?>:</td>
		  <td><?php echo $theme_data['data']; ?></td>
		</tr>
	  <?php } ?>
	</tbody>
  </table>

  <table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th colspan="3"><?php esc_html_e( 'WordPress Environment', 'bricks' ); ?></th>
			</tr>
		</thead>
		<tbody>
	  <?php foreach ( $wp_environment as $wp_data ) { ?>
		<tr>
		  <td class="label"><?php echo esc_html( $wp_data['label'] ); ?>:</td>
		  <td><?php echo $wp_data['data']; ?></td>
		</tr>
	  <?php } ?>
	</tbody>
  </table>

  <table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Server Environment', 'bricks' ); ?></th>
			</tr>
		</thead>
		<tbody>
	  <?php foreach ( $server_environment as $server_data ) { ?>
		<tr>
		  <td class="label"><?php echo esc_html( $server_data['label'] ); ?>:</td>
		  <td><?php echo $server_data['data']; ?></td>
		</tr>
	  <?php } ?>
	</tbody>
  </table>

  <table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th colspan="3"><?php esc_html_e( 'Active Plugins', 'bricks' ); ?></th>
			</tr>
		</thead>
		<tbody>
	  <?php foreach ( $active_plugins as $plugin_data ) { ?>
		<tr>
		  <td class="label"><?php echo esc_html( $plugin_data['name'] . ' (' . $plugin_data['version'] . ')' ); ?></td>
		  <td><?php esc_html_e( 'by', 'bricks' ); ?> <a href="<?php echo esc_url( $plugin_data['uri'] ); ?>" target="_blank"><?php echo $plugin_data['author']; ?></a></td>
		</tr>
	  <?php } ?>
	</tbody>
	</table>

	<!-- <textarea id="bricks-system-information-output" cols="30" rows="10" readonly></textarea> -->
</div>
