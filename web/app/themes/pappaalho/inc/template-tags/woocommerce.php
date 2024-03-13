<?php

// Disable WooCommerce block styles (front-end) ->
function themesharbor_disable_woocommerce_block_styles() {
    wp_dequeue_style( 'wc-blocks-style' );
  }
  add_action( 'wp_enqueue_scripts', 'themesharbor_disable_woocommerce_block_styles' );
// <- Disable WooCommerce block styles (front-end)

// Disable WooCommerce block styles (back-end) ->
function themesharbor_disable_woocommerce_block_editor_styles() {
    wp_deregister_style( 'wc-block-editor' );
    wp_deregister_style( 'wc-blocks-style' );
  }
  add_action( 'enqueue_block_assets', 'themesharbor_disable_woocommerce_block_editor_styles', 1, 1 );
// <- Disable WooCommerce block styles (back-end)


// WooCommerce - Lisämääritykset tarvittaessa ->

// WooCommerce - Tehdään mistä tahansa sisällöstä automaattisesti ostettava tuote ->
class WCCPT_Product_Data_Store_CPT extends WC_Product_Data_Store_CPT {


  public function read( &$product ) {

      $product->set_defaults();

      if ( ! $product->get_id() || ! ( $post_object = get_post( $product->get_id() ) ) || ! in_array( $post_object->post_type, array( 'vaihtoautot' ) ) ) { // change birds with your post type
          throw new Exception( __( 'Invalid product.', 'woocommerce' ) );
      }

      $id = $product->get_id();

      $rekisterinumero = get_field('vaihtoauto__rekisterinumero', $id);
      $merkki = get_field('vaihtoauto__merkki', $id);
      $malli = get_field('vaihtoauto__malli', $id);

      $product->set_props( array(
          'name'              => 'Varausmaksu ajoneuvosta: '.$merkki.' '.$malli.' - '.$rekisterinumero,
          'slug'              => $post_object->post_name,
          'date_created'      => 0 < $post_object->post_date_gmt ? wc_string_to_timestamp( $post_object->post_date_gmt ) : null,
          'date_modified'     => 0 < $post_object->post_modified_gmt ? wc_string_to_timestamp( $post_object->post_modified_gmt ) : null,
          'status'            => $post_object->post_status,
          'description'       => $post_object->post_content,
          'short_description' => $post_object->post_excerpt,
          'parent_id'         => $post_object->post_parent,
          'menu_order'        => $post_object->menu_order,
          'reviews_allowed'   => 'open' === $post_object->comment_status,
      ) );

      $this->read_attributes( $product );
      $this->read_downloads( $product );
      $this->read_visibility( $product );
      $this->read_product_data( $product );
      $this->read_extra_data( $product );
      $product->set_object_read( true );
  }

  public function get_product_type( $product_id ) {
      $post_type = get_post_type( $product_id );
      if ( 'product_variation' === $post_type ) {
          return 'variation';
      } elseif ( in_array( $post_type, array( 'vaihtoautot' ) ) ) { // change birds with your post type
          $terms = get_the_terms( $product_id, 'product_type' );
          return ! empty( $terms ) ? sanitize_title( current( $terms )->name ) : 'simple';
      } else {
          return false;
      }
  }
}

add_filter( 'woocommerce_data_stores', 'woocommerce_data_stores' );

function woocommerce_data_stores ( $stores ) {      
  $stores['product'] = 'WCCPT_Product_Data_Store_CPT';
  return $stores;
}

add_filter('woocommerce_get_price','reigel_woocommerce_get_price',20,2);
function reigel_woocommerce_get_price($price,$post){
  if ($post->post->post_type === 'vaihtoautot') // change this to your post type
      //$price = get_post_meta($post->id, "_price", true); // assuming your price meta key is price
      $price = 1000;
      return $price;
}
// <- WooCommerce - Tehdään mistä tahansa sisällöstä automaattisesti ostettava tuote

// <- WooCommerce - Lisämääritykset tarvittaessa