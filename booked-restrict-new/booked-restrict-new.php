<?php

/*
Plugin Name: Booked Add-On: Remove new user capabilities
Plugin URI: https://getbooked.io
Description: Removes the ability to edit profile or add users through Booked
Author: Johan Winther (johwin)
Text Domain: booked-restrict-new
*/

// Is Booked installed and active?
if( in_array('booked/booked.php',apply_filters('active_plugins',get_option('active_plugins')))) {

	if(!class_exists('BookedRN_Plugin')) {
		class BookedRN_Plugin {

			public function __construct() {

				add_action('init', array(&$this, 'booked_rn_init') );

			}

			public function booked_rn_init(){
			        global $booked_plugin;
				if (is_user_logged_in()):
					add_filter('booked_profile_tabs',array(&$this, 'booked_rn_tabs'),1);
					remove_action('admin_notices', array(&$booked_plugin,'booked_pending_notice'));
					remove_filter('manage_users_columns',array(&$booked_plugin,'booked_add_user_columns'),15);
					remove_filter('manage_users_custom_column',array(&$booked_plugin,'booked_add_custom_user_columns'),15);
					//add_action('wp_ajax_booked_new_appointment_form', array(&$this,'booked_rn_appointment_form'));
				endif;

			}

			public function booked_rn_tabs($custom_tabs){
				unset($custom_tabs['edit']);
				return $custom_tabs;

			}
			public function booked_rn_columns($defaults) {
			       unset($defaults['booked_appointments']);
			       return $defaults;
			}

		}
	}

	add_action('plugins_loaded','init_bookedrn');
}

function init_bookedrn(){
	if(class_exists('BookedRN_Plugin')) {

		$bookedrn_plugin = new BookedRN_Plugin();

	}
}
