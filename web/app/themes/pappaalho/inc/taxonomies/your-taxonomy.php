<?php
/**
 */

namespace Tiiliskivi;

/**
 * Registers the Your Taxonomy taxonomy.
 *
 * @param Array $post_types Optional. Post types in
 * which the taxonomy should be registered.
 */
class Your_Taxonomy extends Taxonomy {


  public function register( array $post_types = [] ) {
    // Taxonomy labels.
    $labels = [
      'name'                  => _x( 'Your Taxonomies', 'Taxonomy plural name', 'tiiliskivi' ),
      'singular_name'         => _x( 'Your Taxonomy', 'Taxonomy singular name', 'tiiliskivi' ),
      'search_items'          => __( 'Search Your Taxonomies', 'tiiliskivi' ),
      'popular_items'         => __( 'Popular Your Taxonomies', 'tiiliskivi' ),
      'all_items'             => __( 'All Your Taxonomies', 'tiiliskivi' ),
      'parent_item'           => __( 'Parent Your Taxonomy', 'tiiliskivi' ),
      'parent_item_colon'     => __( 'Parent Your Taxonomy', 'tiiliskivi' ),
      'edit_item'             => __( 'Edit Your Taxonomy', 'tiiliskivi' ),
      'update_item'           => __( 'Update Your Taxonomy', 'tiiliskivi' ),
      'add_new_item'          => __( 'Add New Your Taxonomy', 'tiiliskivi' ),
      'new_item_name'         => __( 'New Your Taxonomy', 'tiiliskivi' ),
      'add_or_remove_items'   => __( 'Add or remove Your Taxonomies', 'tiiliskivi' ),
      'choose_from_most_used' => __( 'Choose from most used Taxonomies', 'tiiliskivi' ),
      'menu_name'             => __( 'Your Taxonomy', 'tiiliskivi' ),
    ];

    $args = [
      'labels'            => $labels,
      'public'            => false,
      'show_in_nav_menus' => true,
      'show_admin_column' => true,
      'hierarchical'      => true,
      'show_tagcloud'     => false,
      'show_ui'           => true,
      'query_var'         => false,
      'rewrite'           => [
        'slug' => 'your-taxonomy',
      ],
    ];

    $this->register_wp_taxonomy( $this->slug, $post_types, $args );
  }

}
