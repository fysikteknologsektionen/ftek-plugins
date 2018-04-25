<?php
/*
Plugin Name: Ftek User Fields
Description: Extra user fields for class
Author: Johan Winther (johwin)
Text Domain: ftek_uf
Domain Path: /languages
*/


/*
* Load encryption library
*/

add_action( 'init', 'init_ftek_uf' );
function init_ftek_uf() {
    // Load translations
    load_plugin_textdomain('ftek_uf', false, basename( dirname( __FILE__ ) ) . '/languages' );

    if ( current_user_can('edit_users') ) {
        // filters to display the user's groups
        add_filter( 'manage_users_columns', 'ftek_uf_manage_users_columns' );
        // args: unknown, string $column_name, int $user_id
        add_filter( 'manage_users_custom_column', 'ftek_uf_manage_users_custom_column', 8, 3 );
    }
}

/**
* Adds a new column to the users table to show the personal ID number
*
* @param array $column_headers
* @return array column headers
*/
function ftek_uf_manage_users_columns( $column_headers ) {
    $column_headers['class'] = "Årskurs";
    unset($column_headers['posts']); // Hide number of posts
    unset($column_headers['role']); // Hide roles
    return $column_headers;
}

/**
* Renders custom column content.
*
* @param string $output
* @param string $column_name
* @param int $user_id
* @return string custom column content
*/
function ftek_uf_manage_users_custom_column( $output, $column_name, $user_id ) {
    if ($column_name == 'class') {
        $class = get_user_meta($user_id, 'class' , true);
        if ($class != "") {
            $output = $class;
        } else {
            $output = "-";
        }
        return $output;
    }
}
