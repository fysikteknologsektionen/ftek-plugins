<?php
/*
    Plugin Name: Custom site plugin
    Description: Site specific code changes. For adding miscellaneous custom code snippets.
*/

/* Start Adding Functions Below this Line */

/* Stops emails being sent to admin when a user resets their password. */
if ( !function_exists( 'wp_password_change_notification' ) ) {
    function wp_password_change_notification() {}
}

/* Auto update plugins */
add_filter( 'auto_update_plugin', '__return_true' );

/* Stop Adding Functions Below this Line */
?>
