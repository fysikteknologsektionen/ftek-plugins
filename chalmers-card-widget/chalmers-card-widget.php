<?php
/*
   Plugin Name: Chalmers Card Widget
   Description: A widget showing today's lunch at Chalmers University
   Author: Johan Winther (johwin)
   Text Domain: chcw
   Domain Path: /languages
 */

/*
  For fetching card account balance. Uses the api at https://chcw.se/api/card-balance/v1/
*/

add_action( 'init', 'init_chcw' );
function init_chcw() {
  // Load translations
  load_plugin_textdomain('chcw', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

function chcw_get_balance($cardNumber) {
    // Fetch and decode JSON file
    $cardObject = json_decode(file_get_contents("/api/v1/$cardNumber"));
    if (property_exists($error, $cardObject)) {
        return $cardObject;
    } else {
        return false;
    }

}

// Register and load the widget
function chalmers_card_load_widget() {
	register_widget( 'ChalmersCardWidget' );
}
add_action( 'widgets_init', 'chalmers_card_load_widget' );

// Creating the widget
class ChalmersCardWidget extends WP_Widget {

	function __construct() {
		parent::__construct(

			// Base ID of your widget
			'chalmers_card_widget',

			// Widget name will appear in UI
			__('Chalmers Card Widget', 'chcw'),

			// Widget description
			array( 'description' => __( 'Shows card balance for Chalmers Student Union Card.', 'chcw' ), )
		);
	}

	// Creating widget front-end

	public function widget( $args, $instance ) {
        $cardNumber = get_user_meta(get_current_user_id(), 'chalmers-card', true);
        if ($cardNumber != "") {
            $cardObject = chcw_get_balance($cardNumber);
            if ($cardObject) {
                $title = apply_filters( 'widget_title', $instance['title'] );

                // before and after widget arguments are defined by themes
                echo $args['before_widget'];
                if ( ! empty( $title ) )
                echo $args['before_title'] . $title . $args['after_title'];

                // This is where you run the code and display the output

                echo '<span id="name">' . $cardObject->cardHolder . '</span><br>';
                echo '<span id="balance">' . number_format_i18n($cardObject->cardBalance->value, 2). ' ' . __('SEK', 'chcw') . '</span>';


                echo $args['after_widget'];
            }
        }
	}

	// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Card Balance', 'chcw' );
		}
		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
} // Class chalmers_card_widget ends here


/*
* Profile field for Student Union card
*/
add_action( 'show_user_profile', 'user_meta_show_form_field_chalmers_card' );
add_action( 'edit_user_profile', 'user_meta_show_form_field_chalmers_card' );

function user_meta_show_form_field_chalmers_card( $user ) { ?>

	<h3>Chalmers</h3>

	<table class="form-table">
		<tr>
			<th>
				<label for="chalmers_card">Kårkort</label>
			</th>
			<td>
				<input type="number"
				class="regular-text ltr"
				id="chalmers-card"
				name="chalmers-card"
				value="<?= esc_attr(get_user_meta($user->ID, 'chalmers-card', true)); ?>"
				title="You can find your 16 digit number on your Student Union Card."
				pattern="\d{16}"
				required>
				<p class="description">
					Skriv in hela numret för ditt kårkort. Detta måste uppdateras när du får ett nytt.
				</p>
			</td>
		</tr>
	</table>
<?php }

add_action( 'personal_options_update', 'user_meta_update_form_field_chalmers_card' );
add_action( 'edit_user_profile_update', 'user_meta_update_form_field_chalmers_card' );

/**
* The save action.
*
* @param $user_id int the ID of the current user.
*
* @return bool Meta ID if the key didn't exist, true on successful update, false on failure.
*/
function user_meta_update_form_field_chalmers_card( $user_id ) {

	// check that the current user have the capability to edit the $user_id
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}

	// create/update user meta for the $user_id
	return update_user_meta(
		$user_id,
		'chalmers-card',
		$_POST['chalmers-card']
	);
}
