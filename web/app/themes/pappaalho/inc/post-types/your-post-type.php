<?php
/**
 * @package tiiliskivi
 */

namespace Tiiliskivi;

/**
 * Registers the Your Post Type post type.
 */
class Your_Post_Type extends Post_Type {

  public function register() {

    // Modify all the i18ized strings here.
    $generated_labels = [
      'menu_name'          => __( 'Your Post Type', 'tiiliskivi' ),
      'name'               => _x( 'Your Post Types', 'post type general name', 'tiiliskivi' ),
      'singular_name'      => _x( 'Your Post Type', 'post type singular name', 'tiiliskivi' ),
      'name_admin_bar'     => _x( 'Your Post Type', 'add new on admin bar', 'tiiliskivi' ),
      'add_new'            => _x( 'Add New', 'thing', 'tiiliskivi' ),
      'add_new_item'       => __( 'Add New Your Post Type', 'tiiliskivi' ),
      'new_item'           => __( 'New Your Post Type', 'tiiliskivi' ),
      'edit_item'          => __( 'Edit Your Post Type', 'tiiliskivi' ),
      'view_item'          => __( 'View Your Post Type', 'tiiliskivi' ),
      'all_items'          => __( 'All Your Post Types', 'tiiliskivi' ),
      'search_items'       => __( 'Search Your Post Types', 'tiiliskivi' ),
      'parent_item_colon'  => __( 'Parent Your Post Types:', 'tiiliskivi' ),
      'not_found'          => __( 'No your post types found.', 'tiiliskivi' ),
      'not_found_in_trash' => __( 'No your post types found in Trash.', 'tiiliskivi' ),
    ];

    // Definition of the post type arguments. For full list see:
    // http://codex.wordpress.org/Function_Reference/register_post_type
    $args = [
      'labels'              => $generated_labels,
      'description'         => '',
      'menu_icon'           => null,
      'public'              => true,
      'publicly_queryable'  => false, // Käytä arvoa true, mikäli haluat omat urlit sisältösivuille.
      'has_archive'         => false,
      'exclude_from_search' => false,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'show_in_rest'        => false,
      'rewrite'             => [
        'with_front'  => false,
        'slug'        => 'your-post-type',
      ],
      'supports'            => [ 'title', 'editor', 'thumbnail', 'revisions' ],
      'taxonomies'          => [],
    ];

    $this->register_wp_post_type( $this->slug, $args );
  }
}
