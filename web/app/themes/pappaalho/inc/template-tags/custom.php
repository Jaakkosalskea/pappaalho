<?php
// Pakotettu SSL - HTTP Strict Transport Security (HSTS) ->
add_action( 'send_headers', 'tgm_io_strict_transport_security' );
function tgm_io_strict_transport_security() {
    header( 'Strict-Transport-Security: max-age=31536000; includeSubDomains; preload' );
}
// <- Pakotettu SSL - HTTP Strict Transport Security (HSTS)

// Hatom/hentry remover (Fixes errors in Google Webmaster Tools) ->
function themeslug_remove_hentry( $classes ) {
    if ( is_page() ) {
        $classes = array_diff( $classes, array( 'hentry' ) );
    }
    return $classes;
}
add_filter( 'post_class','themeslug_remove_hentry' );
// <- Hatom/hentry remover (Fixes errors in Google Webmaster Tools)

// Fiksaus ylimääräisen p-rivin poistoon headerin alta ->
remove_filter('widget_text_content', 'wpautop');
// <- Fiksaus ylimääräisen p-rivin poistoon headerin alta

// Poistetaan Feedit käytöstä ->
function wpb_disable_feed() {
wp_die( __('Feedit on pois käytöstä. <a href="'. get_bloginfo('url') .'">Siirry etusivulle tästä</a>') );
}
 
add_action('do_feed', 'wpb_disable_feed', 1);
add_action('do_feed_rdf', 'wpb_disable_feed', 1);
add_action('do_feed_rss', 'wpb_disable_feed', 1);
add_action('do_feed_rss2', 'wpb_disable_feed', 1);
add_action('do_feed_atom', 'wpb_disable_feed', 1);
add_action('do_feed_rss2_comments', 'wpb_disable_feed', 1);
add_action('do_feed_atom_comments', 'wpb_disable_feed', 1);
// <- Poistetaan Feedit käytöstä

// Poistetaan linkeistä noreferer -tieto ->
add_filter( 'wp_targeted_link_rel', 'my_targeted_link_rel_remove_noreferrer' );
function my_targeted_link_rel_remove_noreferrer( $rel_values ) {
   return preg_replace( '/noreferrer\s*/i', '', $rel_values );
}
// <- Poistetaan linkeistä noreferer -tieto

// Poistetaan shortlinkit jotta sisäisten linkkien määrä vähenee -> 
remove_action( 'wp_head', 'wp_shortlink_wp_head');
// <- Poistetaan shortlinkit jotta sisäisten linkkien määrä vähenee

// Remove Gutenberg and use Classic editor ->
add_filter('use_block_editor_for_post', '__return_false', 10);
// <- Remove Gutenberg and use Classic editor

// Remove Gutenberg Block Library CSS from loading on the frontend ->
function smartwp_remove_wp_block_library_css(){
    wp_dequeue_style( 'wp-block-library' );
    wp_dequeue_style( 'wp-block-library-theme' );
    wp_dequeue_style( 'wc-block-style' ); // Remove WooCommerce block CSS
} 
add_action( 'wp_enqueue_scripts', 'smartwp_remove_wp_block_library_css', 100 );
// <- Remove Gutenberg Block Library CSS from loading on the frontend

// Hyväksy SVG tiedostot Mediakirjastoon ->
add_filter( 'wp_check_filetype_and_ext', function($data, $file, $filename, $mimes) {

  global $wp_version;
  if ( $wp_version !== '4.7.1' ) {
     return $data;
  }

  $filetype = wp_check_filetype( $filename, $mimes );

  return [
      'ext'             => $filetype['ext'],
      'type'            => $filetype['type'],
      'proper_filename' => $data['proper_filename']
  ];

}, 10, 4 );

function cc_mime_types( $mimes ){
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter( 'upload_mimes', 'cc_mime_types' );

function fix_svg() {
  echo '<style type="text/css">
        .attachment-266x266, .thumbnail img {
             width: 100% !important;
             height: auto !important;
        }
        </style>';
}
add_action( 'admin_head', 'fix_svg' );
// <- Hyväksy SVG tiedostot Mediakirjastoon

// Yleisasetukset ->
if( function_exists('acf_add_options_page') ) {
	
	acf_add_options_page(array(
		'page_title' 	=> 'Yleiset asetukset sivustolle',
		'menu_title'	=> 'Yleisasetukset',
		'menu_slug' 	=> 'yleisasetukset',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	));
	
}
// <- Yleisasetukset

// Remove dashicons in frontend for unauthenticated users ->
add_action( 'wp_enqueue_scripts', 'bs_dequeue_dashicons' );
function bs_dequeue_dashicons() {
    if ( ! is_user_logged_in() ) {
        wp_deregister_style( 'dashicons' );
    }
}
// <- Remove dashicons in frontend for unauthenticated users

// Poistetaan automaattiohjaus ->
remove_filter('template_redirect', 'redirect_canonical'); 
// <- Poistetaan automaattiohjaus

/*
// Lataa custom css ->
function tiiliskivi_enqueue_scripts() {
wp_enqueue_style( 'custom-css', get_template_directory_uri() . '/assets/css/custom.css', [], time(), 'all' ); // Luo tiedosto polun osoittamaan kohteeseen ja käytä vain jos tarvitaan custom.css tiedostoa.
// <- Lataa custom css

// Lataa custom js ->
wp_enqueue_script( 'custom-js', get_template_directory_uri() . '/assets/js/custom.js', array('jquery'), time(), false ); // Luo tiedosto polun osoittamaan kohteeseen ja käytä vain jos tarvitaan custom.js tiedostoa.
// <- Lataa custom js

}
add_action( 'wp_enqueue_scripts', 'tiiliskivi_enqueue_scripts' );
*/
