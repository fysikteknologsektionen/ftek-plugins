<?php
/*
Plugin Name: MailChimp Sync Groups
Description: Sync class groups with wordpress metadata
Version: 4.2.1
Author: johwin
Text Domain: mailchimp-groups
Domain Path: /languages
*/

// Prevent direct file access
defined( 'ABSPATH' ) or exit;


// Send group from Wordpress to Mailchimp
add_filter( 'mailchimp_sync_subscriber_data', function( $subscriber, $user ) {
    $api_key = get_option('mc4wp')['api_key']; // Get API key from Mailchimp for Wordpress plugin
    $dc = substr($api_key, -4); // Extract server from last 4 characters
    $api_url = 'https://' .$dc. '.api.mailchimp.com/3.0/lists/83df300634/interest-categories/620b9026f5/interests?count=50';
    
    // Extract Mailchimp groups from API
    $request = wp_remote_get( $api_url , array( 'timeout' => 10, 'headers' => array( 'Authorization' => 'apikey ' . $api_key)));
    $data = json_decode( wp_remote_retrieve_body( $request ) );
    $groups = $data->interests;
    
    // If name matches "class"-metadata, then set to true
    $class = get_user_meta( $user->ID, "class", true);
    foreach ($groups as $group) {
        $subscriber->interests[$group->id] = $group->name === $class ;
    }
    return $subscriber;
}, 10, 2 );


// Send group from MailChimp to Wordpress (via webhook)
add_action( 'mailchimp_sync_webhook_profile', function( $data, $user ) {
    update_user_meta( $user->ID, "class", $data['merges']['INTERESTS'] );
}, 10, 2 );
