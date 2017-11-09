<?php

/* 
 * This code generates a settings page in the admin UI.
 * See https://codex.wordpress.org/Settings_API for details. 
 */

define('FTEKCP_DATES_SETTINGS', 'ftek_course_dates');
define('FTEKCP_SCHEDULE_SETTINGS', 'ftek_course_schedule');
define('FTEKCP_SETTINGS', 'ftekcp_settings');

// Add the admin settings page
add_action('admin_menu', 'ftek_course_admin_add_page');
function ftek_course_admin_add_page() {
    add_options_page(__('Ftek Course Pages Settings', 'ftekcp'), 
                     __('Ftek Course Pages', 'ftekcp'), 
                     'edit_others_course_pages', // capability
                     FTEKCP_SETTINGS, 
                     'ftek_course_settings_page');
}

// HTML for settings page
function ftek_course_settings_page()
{
    ?>
    <div>
        <h2><?= __('Ftek Course Pages Settings', 'ftekcp')?></h2>
        <form action="options.php" method="post">
        <?php settings_fields(FTEKCP_SETTINGS); ?>
        <?php do_settings_sections(FTEKCP_SETTINGS); ?>
 
        <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
        </form></div>
        
    <?php
}

// Register settings, wordpress generates them
add_action('admin_init', 'ftek_course_admin_init');
function ftek_course_admin_init()
{
    // Study period date settings
    add_settings_section(FTEKCP_DATES_SETTINGS, 
                         __('Date settings', 'ftekcp'),
                         function(){
                             echo __('Enter the <strong>end date</strong> for every study period.', 'ftekcp');
                         },
                         FTEKCP_SETTINGS);
    for ($i=1; $i <= 4; $i++) { 
        add_settings_field(
            "ftek_course_study_period_$i",
            sprintf(__('End date, study period %s', 'ftekcp'), $i),
            function() use ($i) {
                ftek_course_field_study_period($i);
            },
            FTEKCP_SETTINGS,
            FTEKCP_DATES_SETTINGS
        );
    }
    register_setting(FTEKCP_SETTINGS,
                     FTEKCP_DATES_SETTINGS);
                     
    // Schedule link settings
    add_settings_section(FTEKCP_SCHEDULE_SETTINGS,
                         __('Schedule settings', 'ftekcp'),
                         function(){
                             echo __('Enter the URL to the schedule for each class. The linked schedule should begin with the current week and end a year later.', 'ftekcp');
                         },
                         FTEKCP_SETTINGS);
    $classes = array('F1', 'F2', 'F3', 'TM1', 'TM2', 'TM3');
    foreach ($classes as $class) {
        add_settings_field(
            "ftek_course_schedule_URL_$class",
            sprintf(__('Schedule URL %s', 'ftekcp'), $class),
            function() use ($class) {
                ftek_course_schedule_link($class);
            },
            FTEKCP_SETTINGS,
            FTEKCP_SCHEDULE_SETTINGS
        );
    }
    register_setting(FTEKCP_SETTINGS,
                     FTEKCP_SCHEDULE_SETTINGS);
    
}

// Print out each date field
function ftek_course_field_study_period($study_period) {
    $options = get_option(FTEKCP_DATES_SETTINGS);
    $month = $options['month' . $study_period];
    $day   = $options['day'   . $study_period];
    $name = FTEKCP_DATES_SETTINGS;
    echo __("Month", 'ftekcp');
    echo " <select id='ftek_course_study_period_{$study_period}_month' name='{$name}[month$study_period]'>";
    for ($i = 1; $i <= 12; $i++) {
        if ($i == $month) {
            $selected = " selected='selected'";
        }
        else {
            $selected = "";
        }
	    echo "<option value='$i'$selected>$i";
    }
    echo "</select> ";
    echo __("Day", 'ftekcp');
    echo " <select id='ftek_course_study_period_{$study_period}_day' name='{$name}[day$study_period]'>";
    for ($i = 1; $i <= 31; $i++) {
        if ($i == $day) {
            $selected = " selected='selected'";
        }
        else {
            $selected = "";
        }
	    echo "<option value='$i'$selected>$i";
    }
    echo "</select>";
}


function ftek_course_schedule_link($class)
{
    $url = get_option(FTEKCP_SCHEDULE_SETTINGS)[$class];
    $name = FTEKCP_SCHEDULE_SETTINGS . "[$class]";
    echo "<input id='ftek_course_schedule_URL_$class' type='url' name='$name' value='$url' size='80'>";
}