<?php
/*
Plugin Name: Chalmers Card Widget
Description: Shows account balance for Chalmers Student Union Cards.
Author: Johan Winther (johwin)
Text Domain: chcw
Domain Path: /languages
*/

/*
For fetching card account balance. Uses the api at https://ftek.se/api/card-balance/v1/
*/

add_action( 'init', 'init_chcw' );
function init_chcw() {

    if ( !class_exists( 'Defuse\Crypto\Crypto' ) ) {
        require_once(__DIR__ . '/../ftek-user-fields/vendor/autoload.php');
    }

    // Load translations
    load_plugin_textdomain('chcw', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

function chcw_get_balance($cardNumber) {
    // Fetch and decode JSON file
    $url = "https://ftek.se/api/card-balance/v1/" . $cardNumber;
    $response = wp_remote_get( $url, array( 'timeout' => 2) );
    if ( is_wp_error( $response ) ) {
        return false;
    }
    $result = $response['body'];

    $cardObject = json_decode($result);
    return $cardObject;
}

// Register and load the widget
function chalmers_card_load_widget() {
    register_widget( 'ChalmersCardWidget' );
}
add_action( 'widgets_init', 'chalmers_card_load_widget' );

// PHP encrypt and decrypt handler function
function card_action() {
    $key = Defuse\Crypto\Key::loadFromAsciiSafeString( CHALMERS_ENCRYPT_KEY );
    if (isset($_POST['card-number'])) {
        $cardNumber = $_POST['card-number'];
        $cardNumberEncrypted = Defuse\Crypto\Crypto::encrypt($cardNumber, $key );
        echo $cardNumberEncrypted;
    } else if (isset($_POST['card-number-encrypted'])) {
        $cardNumberEncrypted = $_POST['card-number-encrypted'];
        $cardNumber = Defuse\Crypto\Crypto::decrypt($cardNumberEncrypted, $key );
        echo $cardNumber;
    }
    wp_die();
}
add_action( 'wp_ajax_card_action', 'card_action' );
add_action( 'wp_ajax_nopriv_card_action', 'card_action' );

// Creating the widget
class ChalmersCardWidget extends WP_Widget {

    function __construct() {
        parent::__construct(

            // Base ID of your widget
            'chalmers_card_widget',

            // Widget name will appear in UI
            __('Chalmers Card Widget', 'chcw'),

            // Widget description
            array( 'description' => __( 'Shows account balance for Chalmers Student Union Cards.', 'chcw' ), )
        );
    }

    // Creating widget front-end
    public function widget( $args, $instance ) {

        wp_enqueue_script('card-script', plugin_dir_url(__FILE__) . '/chalmers-card-widget.js', array('jquery'));
        wp_localize_script( 'card-script', 'ajax_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'currency' => __('SEK', 'chcw'),
            'wrong_format' => __('Please input 16 digits.', 'chcw')
        ));

        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        echo $args['before_title'] . __( 'Card Balance', 'chcw' ) . $args['after_title'];

        echo '<div id="card-message" style="display:none">';
        echo '<p id="fetch-message">' . __('Fetching balance...', 'chcw') . '</p>';
        echo '<p id="error-message" style="display:none">' . __('Could not fetch balance.', 'chcw') . '</p>';
        echo '</div>';

        echo '<div id="card-info" style="display:none">';
        echo '<p id="card-holder"></p>';
        echo '<p><span id="card-balance"></span><a href="https://kortladdning3.chalmerskonferens.se" target="_blank">'
        . __('Charge card','chcw') . '</a></p>';
        echo '<p><button id="remove-card" style="margin-top:0.5em">' . __('Remove card', 'chcw') . '</button></p>';
        echo '</div>';

        echo '<div id="card-input">';
        echo '<label for="card-number">' . __('Card number','chcw') . '</label>';
        echo '<input type="text" id="card-number" style="margin: 0.5em 0em;padding: 0.3em 0.15em;" placeholder="#### #### #### ####">';
        echo '<button id="get-balance">' . __('Get balance', 'chcw') . '</button>';
        echo '</div>';

        echo $args['after_widget'];

    }

    // Widget Backend
    public function form( $instance ) {

    }

    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {

    }
} // Class chalmers_card_widget ends here
