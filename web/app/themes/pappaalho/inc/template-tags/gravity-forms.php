<?php

// Gravity Forms - Validation viesti ->
add_filter("gform_validation_message", "change_message", 10, 2);
function change_message($message, $form){
return '<div class="validation_error">Täytä alla olevat punaisella merkityt kentät.</div>';
}
// <- Gravity Forms - Validation viesti

// Gravity Forms - Ohjaa sivu lomakkeen kohdalle lähetyksen jälkeen ->
add_filter( 'gform_confirmation_anchor', '__return_true' );
// <- Gravity Forms - Ohjaa sivu lomakkeen kohdalle lähetyksen jälkeen

// Gravity Forms - Suojaa lomakkeen kautta lähetetyt tiedostot ->
add_filter( 'gform_require_login_pre_download', 'protect_gf_uploads', 10, 3 );
function protect_gf_uploads($require_login, $form_id, $field_id) {
    return true;
}
// <- Gravity Forms - Suojaa lomakkeen kautta lähetetyt tiedostot


// Asetukset jotka ovat normaalisti poissa käytöstä ->

/*
// Gravity Forms - content typen vaihtto text/plain -muotoon -> 
add_filter( 'gform_notification', 'my_gform_liidirajapinta', 10, 3 );
function my_gform_liidirajapinta( $notification, $form, $entry ) {

if ( $notification['name'] == 'Websales liidirajapinta' ) {    
$notification['message_format'] = 'text';
    }
return $notification;
}
// <- Gravity Forms - content typen vaihtto text/plain -muotoon
*/

/*
// Gravity Forms - Custom Ajax latauskuvake ->
add_filter("gform_ajax_spinner_url_1", "spinner_url", 10, 2);
function spinner_url($image_src, $form){
    return  get_template_directory_uri() . '/assets/images/rolling-preload.svg' ;
}
// <- Gravity Forms - Custom Ajax latauskuvake
*/

/*
// Gravity Forms - tietojen muokkaus ennen lähetystä ->

add_filter( 'gform_pre_render_1', 'vaihtoautotlomake' );
add_filter( 'gform_pre_validation_1', 'vaihtoautotlomake' );
add_filter( 'gform_pre_submission_filter_1', 'vaihtoautotlomake' );
add_filter( 'gform_admin_pre_render_1', 'vaihtoautotlomake' );
function vaihtoautotlomake( $form ) {
 
    $postid = get_the_ID();

    $rekkari = get_field('vaihtoauto__rekisterinumero', $postid);
    $merkki = get_field('vaihtoauto__merkki', $postid);
    $malli = get_field('vaihtoauto__malli', $postid);
    $mallitarkenne = get_field('vaihtoauto__mallitarkenne', $postid);
    $vuosimalli = get_field('vaihtoauto__vuosimalli', $postid);
    $hinta = get_field('vaihtoauto__myyntihinta', $postid);
    $kilometrit = get_field('vaihtoauto__mittarilukema', $postid);

    $kilometrit = number_format($kilometrit, 0, ',', ' ');

    $fields = $form['fields'];
    foreach( $form['fields'] as &$field ) {

        // Rekisterinumero
        if ( $field->id == 12 ) {
            $field->defaultValue = $merkki.' '.$malli.' ('.$rekkari.')';
        }

        if ( $field->id == 10 ) {

            $_POST['input_48'] = "Postinumero tulee tähän";

        }

    }

    $toimipiste = rgpost( 'input_10' );

    // Toimipisteen postinumero
    if ($toimipiste == 'Turku') {
    $_POST['input_48'] = "20300";
    }
    else if ($toimipiste == 'Salo') {
    $_POST['input_48'] = "24100";
    }
    else {
        $_POST['input_48'] = "20300";
    }

    // Toimipisteen nimi
    if ($toimipiste == 'Turku') {
    $_POST['input_45'] = "Keskusautohalli Turku";
    }
    else if ($toimipiste == 'Salo') {
    $_POST['input_45'] = "Keskusautohalli Salo";
    }
    else {
        $_POST['input_45'] = "Keskusautohalli Turku";
    }

    // Toimipisteen osoite
    if ($toimipiste == 'Turku') {
    $_POST['input_46'] = "Rieskalähteentie 75";
    }
    else if ($toimipiste == 'Salo') {
    $_POST['input_46'] = "Örninkatu 13";
    }
    else {
        $_POST['input_46'] = "Rieskalähteentie 75";
    }

    // Mallitarkenne
    $_POST['input_49'] = $mallitarkenne;

    // Vuosimalli
    $_POST['input_50'] = $vuosimalli;

    // Hinta
    $_POST['input_51'] = $hinta;

    return $form;
}

// <- Gravity Forms - tietojen muokkaus ennen lähetystä
*/

// <- Asetukset jotka ovat normaalisti poissa käytöstä