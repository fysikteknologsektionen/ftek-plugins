<?php
/*
Plugin Name: Ftek User Fields
Description: Extra encrypted user fields for Chalmers Union Card and personal identification number
Author: Johan Winther (johwin)
Text Domain: chcw
Domain Path: /languages
*/


/*
* Load encryption library
*/

add_action( 'init', 'init_ftek_uf' );
function init_ftek_uf() {
    // Load translations
    load_plugin_textdomain('ftek_uf', false, basename( dirname( __FILE__ ) ) . '/languages' );
    load_plugin_textdomain('chcw', false, basename( dirname( __FILE__ ) ) . '../chalmers-card-widget/languages' );
    if ( !class_exists( 'Defuse\Crypto\Crypto' ) ) {
        require_once( 'vendor/autoload.php'); // Make sure to run composer install in current folder to download dependencies
    }
    
    if ( current_user_can( edit_users ) ) {
			// filters to display the user's groups
			add_filter( 'manage_users_columns', 'ftek_uf_manage_users_columns' );
			// args: unknown, string $column_name, int $user_id
			add_filter( 'manage_users_custom_column', 'ftek_uf_manage_users_custom_column', 8, 3 );
	}
}

/*
* Profile field for personal identification number
*/

add_action( 'show_user_profile', 'user_meta_show_form_field_personal_id_number' );
add_action( 'edit_user_profile', 'user_meta_show_form_field_personal_id_number' ); // Show personal ID for administrators

function user_meta_show_form_field_personal_id_number( $user ) {
    $is_active = false;
require_once( ABSPATH . 'wp-includes/pluggable.php' );
if ( $group = Groups_Group::read_by_name( 'Sektionsaktiva' ) ) {
    $is_active = Groups_User_Group::read( get_current_user_id() , $group->group_id );
}
if ($is_active) {
    $key = Defuse\Crypto\Key::loadFromAsciiSafeString( PERSON_ENCRYPT_KEY );
    $personalNumber = "";
    $personalNumberEncrypted = get_user_meta($user->ID, 'personnummer' , true);
    if ($personalNumberEncrypted != "") {
        $personalNumber = Defuse\Crypto\Crypto::decrypt($personalNumberEncrypted, $key);
    }
    ?>

    <h3><?= __('Personal data','ftek_uf') ?></h3>

    <table class="form-table">
        <tr>
            <th>
                <label for="personnummer"><?= __('Personal ID number' , 'ftek_uf') ?></label>
            </th>
            <td>
                <input type="text"
                class="regular-text ltr"
                id="personnummer"
                name="personnummer"
                value="<?= esc_attr($personalNumber); ?>"
                title="<?= __("Your personal ID number is 10 digits long.", 'chcw') ?>"
                placeholder="YYMMDD-XXXX" 
                pattern="[0-9]{2}((0[0-9])|(10|11|12))(([0-2][0-9])|(3[0-1]))-[0-9]{4}"
                required>
                <p class="description">
                    <?= __("By submitting your personal ID number you accept that Fysikteknologsektionen will save it in an encrypted format. It will be used to give your relevant access to our premises.",'ftek_uf') ?>
                </p>
            </td>
        </tr>
    </table>
<?php }
}

add_action( 'personal_options_update', 'user_meta_update_form_field_personal_id_number' );
add_action( 'edit_user_profile_update', 'user_meta_update_form_field_personal_id_number' );

/**
* The save action.
*
* @param $user_id int the ID of the current user.
*
* @return bool Meta ID if the key didn't exist, true on successful update, false on failure.
*/
function user_meta_update_form_field_personal_id_number( $user_id ) {
    $is_active = false;
require_once( ABSPATH . 'wp-includes/pluggable.php' );
if ( $group = Groups_Group::read_by_name( 'Sektionsaktiva' ) ) {
    $is_active = Groups_User_Group::read( get_current_user_id() , $group->group_id );
}
if ($is_active) {
    // check that the current user have the capability to edit the $user_id
    if (!current_user_can('edit_user', $user_id) || ($_POST['personnummer'] != "" && !preg_match('/[0-9]{2}((0[0-9])|(10|11|12))(([0-2][0-9])|(3[0-1]))-[0-9]{4}/', $_POST['personnummer']))) {
        return false;
    }
    // create/update user meta for the $user_id but encrypt it first
    $key = Defuse\Crypto\Key::loadFromAsciiSafeString( PERSON_ENCRYPT_KEY );
    if ($_POST['personnummer'] == "") {
      $personalNumberEncrypted = "";
    } else {
      preg_match('/[0-9]{2}((0[0-9])|(10|11|12))(([0-2][0-9])|(3[0-1]))-[0-9]{4}/', $_POST['personnummer'], $matches);
      $personalNumberEncrypted = Defuse\Crypto\Crypto::encrypt($matches[0], $key);
    }
    return update_user_meta(
        $user_id,
        'personnummer',
        $personalNumberEncrypted
    );
}
}

/*
* Profile field for Student Union card
*/
add_action( 'show_user_profile', 'user_meta_show_form_field_chalmers_card' );
function user_meta_show_form_field_chalmers_card( $user ) {
    $key = Defuse\Crypto\Key::loadFromAsciiSafeString( CHALMERS_ENCRYPT_KEY );
    $cardNumber = "";
    $cardNumberEncrypted = get_user_meta($user->ID, 'chalmers-card' , true);
    if ($cardNumberEncrypted != "") {
        $cardNumber = Defuse\Crypto\Crypto::decrypt($cardNumberEncrypted, $key);
    }
    ?>

    <h3>Chalmers</h3>

    <table class="form-table">
        <tr>
            <th>
                <label for="chalmers_card"><?= __('Student Union Card' , 'chcw') ?></label>
            </th>
            <td>
                <input type="number"
                class="regular-text ltr"
                id="chalmers-card"
                name="chalmers-card"
                value="<?= esc_attr($cardNumber); ?>"
                title="<?= __("You can find your 16 digit number on your Student Union Card.", 'chcw') ?>"
                pattern="\d{16}"
                required>
                <p class="description">
                    <?= __("Write the whole number on your Student Union Card. This needs to be updated when you get a new one.",'chcw') ?>
                </p>
            </td>
        </tr>
    </table>
<?php }
add_action( 'personal_options_update', 'user_meta_update_form_field_chalmers_card' );
/**
* The save action.
*
* @param $user_id int the ID of the current user.
*
* @return bool Meta ID if the key didn't exist, true on successful update, false on failure.
*/
function user_meta_update_form_field_chalmers_card( $user_id ) {
    // check that the current user have the capability to edit the $user_id
    if (!current_user_can('edit_user', $user_id) || ($_POST['chalmers-card'] != "" && !preg_match('/\d{16}/', $_POST['chalmers-card']))) {
        return false;
    }
    // create/update user meta for the $user_id but encrypt it first
    $key = Defuse\Crypto\Key::loadFromAsciiSafeString( CHALMERS_ENCRYPT_KEY );
    if ($_POST['chalmers-card'] == "") {
      $cardNumberEncrypted = "";
    } else {
      $cardNumberEncrypted = Defuse\Crypto\Crypto::encrypt($_POST['chalmers-card'], $key);
    }
    return update_user_meta(
        $user_id,
        'chalmers-card',
        $cardNumberEncrypted
    );
}

/**
 * Adds a new column to the users table to show the personal ID number
 * 
 * @param array $column_headers
 * @return array column headers
 */
function ftek_uf_manage_users_columns( $column_headers ) {
    $column_headers['personal-number'] = __( 'Personal ID number', 'ftek_uf' );
	$column_headers['booked-phone'] = __( 'Phone Number', 'ftek_uf' );
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
    if ($column_name == 'personal-number') {
        $key = Defuse\Crypto\Key::loadFromAsciiSafeString( PERSON_ENCRYPT_KEY );
        $personalNumber = "";
        $personalNumberEncrypted = get_user_meta($user_id, 'personnummer' , true);
        if ($personalNumberEncrypted != "") {
            $personalNumber = Defuse\Crypto\Crypto::decrypt($personalNumberEncrypted, $key);
        }
        $output = $personalNumber;
    } else if ($column_name == 'booked-phone') {
        $output = get_user_meta($user_id, 'booked_phone' , true);
    }
    return $output;
}
