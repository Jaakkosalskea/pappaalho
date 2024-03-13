<?php
/**
 * Enqueue and localize theme scripts and styles
 *
 */

namespace Tiiliskivi;

function enqueue_theme_scripts() {
  if ( ! bricks_is_builder_main() ) {

    // jQuery käyttöön ->
    /*
    wp_enqueue_script( 'jquery' );
    wp_scripts()->add_data('jquery', 'group', 1);
    wp_scripts()->add_data('jquery-core', 'group', 1);
    wp_scripts()->add_data('jquery-migrate', 'group', 1);

    wp_enqueue_script('child-global-js', get_stylesheet_directory_uri() . '/assets/'.get_asset_file('front-end.js'), array(), filemtime(get_stylesheet_directory() . '/assets/'.get_asset_file('front-end.js')), true);
    */
    // <- jQuery käyttöön
    
    wp_enqueue_style('child-global-css', get_stylesheet_directory_uri() . '/assets/'.get_asset_file('global.css'), array(), filemtime(get_stylesheet_directory() . '/assets/'.get_asset_file('global.css')));	
  
  }
}

/**
 * Returns the built asset filename and path depending on
 * current environment.
 *
 * @param string $filename File name with the extension
 * @return string file and path of the asset file
 */
function get_asset_file( $filename ) {
  $env = 'development' === wp_get_environment_type() ? 'dev' : 'prod'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

  $filetype = pathinfo( $filename )['extension'];

  return "${filetype}/${env}/${filename}";
} // end get_asset_file
