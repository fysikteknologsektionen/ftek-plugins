<?php

/*
Plugin Name: WP Support Plus: sMail Notification
Description: Sends an e-mail to predefined adress when a new ticket is created through WP Support Plus.
Author: Eric Carlsson
*/

add_action( 'wpsp_after_create_ticket', 'wpsp_mail' );
function wpsp_mail($ticket_id) {
  global $wpdb;
  $sql = 'SELECT subject, guest_name, guest_email, create_time FROM {$wpdb->prefix}wpsp_ticket WHERE id=' . $ticket_id;
  $result = $wpdb->get_row($sql);
  if ($result != null) {
    $mail_to = 'spidera@ftek.se';
    $mail_subject = '[Ticket #' . $ticket_id . '] WP Support Plus notification';
    $mail_message = '<html><font size=4>A new ticket has been created</font><br /><b>Author:</b> ' . $result->guest_name
                    . '<br \><b>Author email:</b> ' . $result->guest_email
                    . '<br \><b>Subject:</b> ' . $result->subject
                    . '<br \><b>Date:</b> ' . $result->create_time
                    . '<br \><a href="https://ftek.se/support/?page=tickets&section=ticket-list&action=open-ticket&id='
                    . $ticket_id . '"><b>View ticket by clicking here</b></a>' . '</html>';
    $mail_headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail($mail_to, $mail_subject, $mail_message, $mail_headers);
  }
}
?>
