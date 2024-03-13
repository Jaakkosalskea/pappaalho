<?php
// Custom shortcodes ->

/*
// Päävalikko - [top-menu] ->
function topmenu_func( $atts ){
ob_start();
wp_nav_menu(
                                array(
                                    'menu' => 'päävalikko',
                                    'menu_class' => 'menu nav',
                                    'fallback_cb' => '',
                                    'menu_id' => 'top-menu',
                                    'depth' => '4'
                                )
                            );
return ob_get_clean();
}
add_shortcode( 'top-menu', 'topmenu_func' );
// <- Päävalikko - [top-menu]
*/

/*
// Footervalikko - [footer-menu] ->
function footermenu_func( $atts ){
ob_start();
wp_nav_menu(
                                array(
                                    'menu' => 'Footervalikko',
                                    'menu_class' => 'footer-menu footer-nav',
                                    'fallback_cb' => '',
                                    'menu_id' => 'footer-menu',
                                    'depth' => '2'
                                )
                            );
return ob_get_clean();
}
add_shortcode( 'footer-menu', 'footermenu_func' );
// <- Footervalikko - [footer-menu]
*/

/*
// Päivämäärä - [paivamaara] ->
function paivamaara_func( $atts ){
	date_default_timezone_set('Europe/Helsinki');
$date = date('j.n.Y H:i:s');
return $date;
}
add_shortcode( 'paivamaara', 'paivamaara_func' );
// <- Päivämäärä - [paivamaara]
*/
// <- Custom shortcodes
