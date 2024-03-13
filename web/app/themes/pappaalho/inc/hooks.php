<?php
/**
 * All hooks that are run in the theme are listed here
 *
 */

namespace Tiiliskivi;

 /**
 * General hooks
 */
/*
require get_theme_file_path( 'inc/hooks/general.php' );
add_action( 'widgets_init', __NAMESPACE__ . '\widgets_init' );
*/

/**
 * Scripts and styles associated hooks
 */

// SASS tyylittelyt teemassa. Mikäli käytät vain Bricksbuilderin tyylitteylä, voit kommentoida tämän pois. ->
require get_theme_file_path( 'inc/hooks/scripts-styles.php' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_theme_scripts' );
// <- SASS tyylittelyt teemassa. Mikäli käytät vain Bricksbuilderin tyylitteylä, voit kommentoida tämän pois.

// NB! If you use ajax functionality in Gravity Forms, remove this line
// to prevent Uncaught ReferenceError: jQuery is not defined
//add_action( 'wp_default_scripts', __NAMESPACE__ . '\move_jquery_into_footer' );

/**
 * Form related hooks
 */
/*
require get_theme_file_path( 'inc/hooks/forms.php' );
add_action( 'gform_enqueue_scripts', __NAMESPACE__ . '\dequeue_gf_stylesheets', 999 );
*/