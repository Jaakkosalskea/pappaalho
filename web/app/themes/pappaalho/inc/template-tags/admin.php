<?php

//namespace Tiiliskivi;

// This file handles the admin area and functions - You can use this file to make changes to the dashboard.

/************* DASHBOARD WIDGETS *****************/
// Disable default dashboard widgets
function remove_menus_and_widgets() {

// Remove Meta boxes ->
	Remove_meta_box('dashboard_right_now', 'dashboard', 'core');    // Right Now Widget
	remove_meta_box('dashboard_recent_comments', 'dashboard', 'core'); // Comments Widget
	remove_meta_box('dashboard_incoming_links', 'dashboard', 'core');  // Incoming Links Widget
	remove_meta_box('dashboard_plugins', 'dashboard', 'core');         // Plugins Widget

	Remove_meta_box('dashboard_quick_press', 'dashboard', 'core');  // Quick Press Widget
	remove_meta_box('dashboard_recent_drafts', 'dashboard', 'core');   // Recent Drafts Widget
	remove_meta_box('dashboard_primary', 'dashboard', 'core');         //
	remove_meta_box('dashboard_secondary', 'dashboard', 'core');       //

	// Removing plugin dashboard boxes
	remove_meta_box('yoast_db_widget', 'dashboard', 'normal');         // Yoast's SEO Plugin Widget
// <- Remove Meta boxes

// Remove menus ->
	// remove_menu_page( 'index.php' );					//Dashboard
	// remove_menu_page( 'jetpack' );					//Jetpack* 
	// remove_menu_page( 'edit.php' );					//Posts
	// remove_menu_page( 'upload.php' );				//Media
	// remove_menu_page( 'edit.php?post_type=page' );	//Pages
	remove_menu_page( 'edit-comments.php' );			//Comments
	//remove_menu_page( 'themes.php' );					//Appearance
	// remove_menu_page( 'plugins.php' );				//Plugins
	// remove_menu_page( 'users.php' );					//Users
	// remove_menu_page( 'tools.php' );					//Tools
	// remove_menu_page( 'options-general.php' );		//Settings
	remove_menu_page( 'widgets.php' );					//Settings
	remove_submenu_page('themes.php', 'widgets.php');					// Widgets
// <- Remove menus
}
add_action( 'admin_menu', 'remove_menus_and_widgets' );
