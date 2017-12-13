<?php
/*
Plugin Name: Ftek Export Users
Description: Adds export button to users page for current group
Author: Johan Winther (johwin)
Text Domain: ftek_eu
Domain Path: /languages
*/

/********* Export to csv ***********/
add_action('admin_footer', 'ftek_export_users');

function ftek_export_users() {
    $screen = get_current_screen();
    if ( $screen->id != "users" )   // Only add to users.php page
        return;
    ?>
    <script type="text/javascript">
    function download() {
        var text = "";
      try {
      jQuery("tbody#the-list").find("tr").each(function(i,row) {
          var row = jQuery(row);
          var td = row.find("td").each(function(j,col) {
              var col = jQuery(col);
              if (col.hasClass("username")) {
                  text += col.find("strong > a").text() + "\t";
              } else if (!col.hasClass("groups_user_groups")) {
                  text += col.text() + "\t";
              }
          });
          text += "\n";

      });
      var element = document.createElement('a');
      element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
      element.setAttribute('download', "users-" + (new Date()).toISOString().slice(0,10) + ".csv");
      element.style.display = 'none';
      document.body.appendChild(element);
      element.click();
      document.body.removeChild(element);
      } catch (e) {
          alert("Kunde inte exportera. Kolla så att du är på rätt sida.");
      }
    }
        jQuery(document).ready( function($)
        {
            $('form#the-list').append('<button class="button button-primary user_export_button" style="margin-left:0.5em;" value="Export CSV" onclick="download()" 
            />');
        });
    </script>
    <?php
}

?>
