<?php
/*
   Plugin Name: Chalmers Lunch Widget
   Description: A widget showing today's lunch at Chalmers University
   Author: Anton Älgmyr
   Text Domain: chlw
   Domain Path: /languages
 */

/*
  Complete rewrite of Pontus' code to handle the changes made by Chalmers.
  There is no RSS anymore. For better or worse JSON is the new format we have
  to deal with. It's nice and detailed, but it's unsure how to fetch menus for
  a specific that (i.e. not today) which breaks the nice "tomorrow's lunch"
  functionality for now.

  Probably a good idea to reach out to the devs that did this change and ask
  what's possible.
*/


add_action( 'init', 'init_chlw' );
function init_chlw() {
  // Load translations
  load_plugin_textdomain('chlw', false, basename( dirname( __FILE__ ) ) . '/languages' );
}


function chlw_format_dish($dish, $lang) {
  $inEnglish = $lang != "sv";

  $css = 'style="height:1.2em;width:auto;margin:1pt;margin-left:4pt;vertical-align:middle"';

  $dishStr = $dish->displayNames[$inEnglish]->displayName;
  $dishStr = $dishStr . "<span style='white-space:nowrap;'>";
  foreach ($dish->allergens as $allergen) {
    $imageUrl = str_replace("http://", "https://", $allergen->imageURLDark);
    $dishStr = $dishStr . " <img src=\"" . $imageUrl . "\" " . $css . "/>";
  }
  $dishStr = $dishStr . "</span>";

  return $dishStr;
}

function chlw_get_menu($restName, $lang) {
  $restaurantIds = array(
      "Kårrestaurangen" => 5,
      "Express" => 7,
      "L's Kitchen" => 8,
      "L's Express?" => 9,
      "L's Resto" => 32,
      "Kokboken" => 35,
      "S.M.A.K" => 42,
      );

  $restId = $restaurantIds[$restName];

  // Fetch and decode JSON file

  /* Use week menu?
  $url = "http://carboncloudrestaurantapi.azurewebsites.net/api/menuscreen/getdataweek?restaurantid=$restId";
  $json = file_get_contents($url);
  $weekMenu = json_decode($json);

  $day = 5;

  // If there is no menu, return NULL
  if (count($weekMenu->menus) < $day) {
    return NULL;
  }

  // Pick the correct day
  $dayMenu = $weekMenu->menus[$day-1];
  */

  $url = "http://carboncloudrestaurantapi.azurewebsites.net/api/menuscreen/getdataday?restaurantid=$restId";
  $json = file_get_contents($url);
  $dayMenu = json_decode($json);

  // For every category, make a list of dishes
  $menu = array();
  foreach ($dayMenu->recipeCategories as $cat) {
    $catName = $lang == "sv" ? $cat->name : $cat->nameEnglish;

    $menu[$catName] = array_map(
        function($dish) use ($lang) {
          return chlw_format_dish($dish, $lang);
        },
        $cat->recipes);
  }

  return $menu;
}

/*
 *  Widget
 */
class ChalmersLunchWidget extends WP_Widget {

  function __construct() {
    // Instantiate the parent object
    parent::__construct(
        'chalmers_lunch_widget', 
        __('Chalmers Lunch Widget', 'chlw'),
        array( 
          'description' => __('Shows lunch menus for Chalmers University', 'chlw'),
          'classname' => 'chalmers_lunch_widget',
          )
        );
  }

  function widget( $args, $instance ) {
    // Settings
    $restaurants = array(
        "Kårrestaurangen",
        "Express"
        );
    $lang = qtrans_getLanguage();

    $title = __("Today's lunch", 'chlw');

    // Some WP fluff
    echo $args['before_widget'];
    $title = apply_filters( 'widget_title', $title);
    echo $args['before_title'] . $title . $args['after_title'];

    // Get all menus
    foreach ($restaurants as $restName) {
      // Get formatted dishes for all categories for the restaurant 
      $menu = chlw_get_menu($restName, $lang);

      ksort($menu, SORT_STRING);

      echo "<h3 class='lunch-place'>$restName</h3>";

      if ($menu == NULL) {
        echo __("No lunch today", 'chlw');
        continue;
      }

      echo "<ul class='meals'>";
      foreach ($menu as $cat => $dishes) {
        echo "<li class='meal'>";
        echo "<span class='meal-title'>$cat</span>";
        echo "<ul>";

        foreach ($dishes as $dish) {
          echo "<li class='dish'>$dish</li>";
        }
        echo "</ul>";
        echo "</li>";
      }
      echo "</ul>";
    }

    echo $args['after_widget'];
  }

  function update( $new_instance, $old_instance ) {
    // Save widget options
    // Todo: add settings for what restaurants to display
  }

  function form( $instance ) {
    // Output admin widget options form
  }
}

function register_chalmers_lunch_widget() {
  register_widget( 'ChalmersLunchWidget' );
}

add_action( 'widgets_init', 'register_chalmers_lunch_widget' );
