<?php

// Otetaan käyttöön artikkelikuva
add_theme_support( 'post-thumbnails' );

// Määritä kaikki tarvittavat thumbnailkoot ->
// Muista määrittää tähän 2 X se koko joka näytetään sivulla (Retina)
add_image_size( 'fullhd-bg', 3840, 2160, true ); // true = Hard Crop Mode
// <- Määritä kaikki tarvittavat thumbnailkoot

// Onko kuva valittavissa hallinnassa ->
add_filter( 'image_size_names_choose', 'salskeathumbs_custom_image_sizes' );

function salskeathumbs_custom_image_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'fullhd-bg' => __( 'Full HD kuva' ),
    ) );
}
// <- Onko kuva valittavissa hallinnassa

// Poistetaan oletusthumbnailit ->
function wcr_remove_intermediate_image_sizes($sizes, $metadata) {
    $disabled_sizes = array(
        'thumbnail', // 150x150 image
        'medium', // max 300x300 image
        'large'   // max 1024x1024 image
    );

    // Poistetaan käytöstä käyttämättömät kuvat
    foreach ($disabled_sizes as $size) {
        if (!isset($sizes[$size])) {
            continue;
        }
    
        unset($sizes[$size]);
    }

    return $sizes;
}

add_filter('intermediate_image_sizes_advanced', 'wcr_remove_intermediate_image_sizes', 10, 2);
// <- Poistetaan oletusthumbnailit