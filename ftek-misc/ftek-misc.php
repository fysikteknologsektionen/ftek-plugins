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

/* Only allow sign-up of accounts with cid and student.chalmers.se mail addresses. */
require_once(ABSPATH . WPINC . '/pluggable.php' );
$file = parse_url($_SERVER['REQUEST_URI']);
$path = explode('/',@$file['path']);
parse_str(@$file['query']);
if( ((end($path) == 'wp-login.php' AND @$_GET['action'] == 'register') OR (end($path) == 'wp-signup.php'))  ) {
  wp_redirect('www.ftek.se/registrering');
  exit;
}

/* Stop Adding Functions Below this Line */
?>
