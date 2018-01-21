<?php

/*
Plugin Name: WP Support Plus: Mail Notification
Description: Sends an e-mail to the site admin when a new ticket is created in WP Support Plus.
Author: Eric Carlsson
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action( 'wpsp_after_create_ticket', 'wpsp_mail' );
function wpsp_mail($ticket_id) {
  global $wpdb;
  $sql = $wpdb->prepare("SELECT subject, guest_name, guest_email, create_time FROM {$wpdb->prefix}wpsp_ticket WHERE id=%d", $ticket_id);
  $result = $wpdb->get_row($sql);
  if (!is_null($result)) {
    $mail_to = get_bloginfo('admin_email');
    $mail_subject = '[Ticket #' . $ticket_id . '] WP Support Plus notification';
		$support_url = get_site_url(null, '/support/?page=tickets&section=ticket-list&action=open-ticket&id=' . $ticket_id);
    $mail_message = '<html><font size=4>A new ticket has been created in WP Support Plus</font><br /><b>Author:</b> ' . $result->guest_name
                    . '<br \><b>Author email:</b> ' . $result->guest_email
                    . '<br \><b>Subject:</b> ' . $result->subject
                    . '<br \><b>Date:</b> ' . $result->create_time
                    . '<br \><a href="' . $support_url . '"><b>View ticket by clicking here</b></a><br \><br \></html>';
    $mail_headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($mail_to, $mail_subject, $mail_message, $mail_headers);
  }
}
?>
