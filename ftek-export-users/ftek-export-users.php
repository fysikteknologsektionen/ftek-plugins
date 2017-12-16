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
    function getAllText() {
        var text = "";
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
        return text;
    }
    function download() {
      try {
          var text = getAllText();
          var element = document.createElement('a');
          element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
          element.setAttribute('download', "users-" + (new Date()).toISOString().slice(0,10) + ".csv");
          element.style.display = 'none';
          document.body.appendChild(element);
          element.click();
          document.body.removeChild(element);
      } catch (e) {
          alert("Kunde inte exportera. Vänligen kontakta Spidera!");
      }
    }
    function copyAll() {
        copyText(getAllText());
    }
    function copyEmails() {
      copyText($("tbody#the-list").find("td.email").map( 
        function(i, el) {
          return $(el).text();
        }).get().join(" \n"));
    }
    function copyText(text) {
      var textArea = document.createElement("textarea");
      // Place in top-left corner of screen regardless of scroll position.
      textArea.style.position = 'fixed';
      textArea.style.top = 0;
      textArea.style.left = 0;
      // Ensure it has a small width and height. Setting to 1px / 1em
      // doesn't work as this gives a negative w/h on some browsers.
      textArea.style.width = '2em';
      textArea.style.height = '2em';
      // We don't need padding, reducing the size if it does flash render.
      textArea.style.padding = 0;
      // Clean up any borders.
      textArea.style.border = 'none';
      textArea.style.outline = 'none';
      textArea.style.boxShadow = 'none';
      // Avoid flash of white box if rendered for any reason.
      textArea.style.background = 'transparent';
      textArea.value = text;

      document.body.appendChild(textArea);
      textArea.select();
      try {
        var successful = document.execCommand('copy');
        var msg = successful ? 'successful' : 'unsuccessful';
        console.log('Copying text command was ' + msg);
      } catch (err) {
        console.log('Oops, unable to copy');
      }
      document.body.removeChild(textArea);
    }
        jQuery(document).ready( function($)
        {   
            var html = '<div class="tablenav-pages" style="float:left"><span class="button user_copy_button" onclick="copyAll()" style="margin-right: 0.5em;">Copy CSV</span><span class="button email_copy_button" onclick="copyEmails()" style="margin-right: 0.5em;">Copy Email List</span><button class="button button-primary user_export_button" onclick="download()" style="margin-right: 0.5em;">Export CSV</button></div>';
            $('div.tablenav-pages').before(html);
        });
    </script>
    <?php
}

?>
