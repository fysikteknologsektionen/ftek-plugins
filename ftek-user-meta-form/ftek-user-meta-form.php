<?php
/*
Plugin Name: Ftek Usermeta Form
Description: Shortcode for ajax forms that update user metadata
Author: Johan Winther (johwin)
Text Domain: ftek_umaf
Domain Path: /languages
*/

function ftek_meta_form_shortcode($atts, $content, $tag)
{
    //extract( shortcode_atts( array(), $atts ) );

    wp_enqueue_script( 'ftek-user-meta-form', plugin_dir_url(__FILE__) . '/ftek-user-meta-form.js', array('jquery'), null, false);
    wp_localize_script('ftek-user-meta-form', 'ftek_user_meta_obj', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));

    $output = '<form id="meta-form" method="POST">';
    $output .= '<p><label for="nickname">Smeknamn: <br />';
    $output .= '<input type="text" id="nickname" name="nickname">';
    $output .= '</label></p>';
    $output .= '<p><label for="phone_number">Mobilnummer: <br />';
    $output .= '<input type="text" id="phone-number" name="phone_number">';
    $output .= '</label></p>';
    $output .= '<p><label for="personal_number">Personnummer: <br />';
    $output .= '<input type="text" id="personal-number" name="personal_number" placeholder="YYMMDD-XXXX">';
    $output .= '<span id="personal-number-message" style="color:red"></span>';
    $output .= '</label></p>';
    $output .= '<p><input type="submit" value="Spara" /><span id="form-message"></span></p>';
    $output .= '</form>';

    return $output;
}
add_shortcode('ftek_meta_form', 'ftek_meta_form_shortcode');


function ftek_update_meta() {
    $personalNumber = $_POST['personal_number'];
    if ( !class_exists( 'Defuse\Crypto\Crypto' ) ) {
        require_once(plugin_dir_url(__FILE__) . '/vendor/autoload.php'); // Make sure to run composer install in current folder to download dependencies
    }
    // create/update user meta for the $user_id but encrypt it first
    $key = Defuse\Crypto\Key::loadFromAsciiSafeString( PERSON_ENCRYPT_KEY );
    preg_match('/[0-9]{2}((0[0-9])|(10|11|12))(([0-2][0-9])|(3[0-1]))-[0-9]{4}/', $personalNumber, $matches);
    $personalNumberEncrypted = Defuse\Crypto\Crypto::encrypt($matches[0], $key);
    update_user_meta(get_current_user_id(), 'personnummer', $personalNumberEncrypted);
    echo "Updated";
    wp_die();
}

add_action( 'wp_ajax_ftek_update_meta', 'ftek_update_meta' );
