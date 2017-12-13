<?php
/*
Plugin Name: Ftek Export Users
Description: Adds export button to users page for current group
Author: Johan Winther (johwin)
Text Domain: ftek_eu
Domain Path: /languages
*/

<?php

/********* Export to csv ***********/
add_action('admin_footer', 'ftek_export_users');

function ftek_export_users() {
    $screen = get_current_screen();
    if ( $screen->id != "users" )   // Only add to users.php page
        return;
    ?>
    <script type="text/javascript">
        jQuery(document).ready( function($)
        {
            $('.tablenav.top .clear, .tablenav.bottom .clear').before('<form action="#" method="POST"><input type="hidden" id="ftek_export_csv" name="ftek_export_csv" value="1" /><input class="button button-primary user_export_button" style="margin-top:3px;" type="submit" value="<?php esc_attr_e('Export as CSV', 'ftek');?>" /></form>');
        });
    </script>
    <?php
}

add_action('admin_init', 'export_csv'); //you can use admin_init as well

function export_csv() {
    if (!empty($_POST['ftek_export_csv'])) {

        if (current_user_can('manage_options')) {
            header("Content-type: application/force-download");
            header('Content-Disposition: inline; filename="users'.date('YmdHis').'.csv"');

            // WP_User_Query arguments
            $args = array (
                'order'          => 'ASC',
                'orderby'        => 'display_name',
                'fields'         => 'all',
            );
            
            // The User Query
            $blogusers = get_users( $args );
            // Array of WP_User objects.
            foreach ( $blogusers as $user ) {
                $meta = get_user_meta($user->ID);
                $role = $user->roles;
                $email = $user->user_email;

                $first_name = ( isset($meta['first_name'][0]) && $meta['first_name'][0] != '' ) ? $meta['first_name'][0] : '' ;
                $last_name  = ( isset($meta['last_name'][0]) && $meta['last_name'][0] != '' ) ? $meta['last_name'][0] : '' ;

                echo '"' . $first_name . '","' . $last_name . '","' . $email . '","' . ucfirst($role[0]) . '"' . "\r\n";
            }

            exit();
        }
    }
}
