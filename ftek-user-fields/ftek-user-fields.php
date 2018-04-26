<?php
/*
Plugin Name: Ftek User Fields
Description: Extra user fields for class
Author: Johan Winther (johwin)
Text Domain: ftek_uf
Domain Path: /languages
*/

add_action( 'init', 'init_ftek_uf' );
function init_ftek_uf() {
    // Load translations (none at the moment)
    load_plugin_textdomain('ftek_uf', false, basename( dirname( __FILE__ ) ) . '/languages' );

    if ( current_user_can('edit_users') ) {
        // filters to display the user's groups
        add_filter( 'manage_users_columns', 'ftek_uf_manage_users_columns' );
        // args: unknown, string $column_name, int $user_id
        add_filter( 'manage_users_custom_column', 'ftek_uf_manage_users_custom_column', 8, 3 );
    }
}

/**
* Adds a new column to the users table to show the class
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

/*
* Class field on edit profile page
*/
add_action( 'show_user_profile', 'user_meta_show_form_field_class' );
add_action( 'edit_user_profile', 'user_meta_show_form_field_class' ); // Show class for administrators
function user_meta_show_form_field_class( $user ) {
    
    $class = get_user_meta($user->ID, 'class' , true);
    if (!empty($class)) {
        $program = strrev(substr(strrev($class),2));
        $year = substr($class,2);
    } else {
        $program = "";
        $year = "";
    }
    ?>
    <script>
         jQuery(document).ready(function ($) {
             $("tr.user-description-wrap").before('\
                <tr class="user-class">\
                    <th><label for="year">Årskurs</label></th>\
                    <td>\
                        <select class="program" name="program">\
                            <option <?= echo($program ? "" : "selected") ?> disabled hidden value="">Program</option>\
                            <option <?= echo($program === "f" ? "selected" : "") ?> value="f">F</option>\
                            <option <?= echo($program === "tm" ? "selected" : "") ?> value="tm">TM</option>\
                        </select>\
                        <input name="year" type="number" pattern="[0-9]{2}" placeholder="YY" value="<?= echo $year ?>">\
                        <p class="description">Här kan du välja klass för att skräddarsy hemsidan och den mejl du får. Ytterligare mejlinställningar går att hitta via länken längst ner i något av våra utskick.</p>\
                    </td>\
                </tr>\
             ');
         });
    </script>
<?php 
}


add_action( 'personal_options_update', 'user_meta_update_form_field_class' );
add_action( 'edit_user_profile_update', 'user_meta_update_form_field_class' );
/**
* The save action.
*
* @param $user_id int the ID of the current user.
*
* @return bool Meta ID if the key didn't exist, true on successful update, false on failure.
*/
function user_meta_update_form_field_class( $user_id ) {
    $class = $_POST['program'] . substr($_POST['year'],-2); //
    
    // check that the current user have the capability to edit the $user_id
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    if (!preg_match('/(f|tm)[0-9]{2}/', $class)) {
        $class = "";
    }
    return update_user_meta($user_id, 'class', $class);
}
