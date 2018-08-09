<?php

function current_study_period()
{
    // Returns current study period (1, 2, 3 or 4). Calculated from
    // period end dates set in the admin settings panel.
    $options = get_option('ftek_course_dates');
    for ($i = 1; $i <= 4; $i++) {
        $end_times[$i] = mktime(0,0,0,$options["month$i"],$options["day$i"]);
    }
    // Sort in current time
    $end_times['now'] = time();
    asort($end_times);
    // Find dates position in sorted array to figure out
    // Current study period
    $periods = array_keys($end_times);
    $position = array_search('now', $periods);
    $current = $periods[($position + 1) % count($periods)];
    return $current;
}

function timeEdit_url($programme, $programme_year)
{
    return get_option(FTEKCP_SCHEDULE_SETTINGS)[$programme . $programme_year];
}


/*
 * Tags
 */

function get_ftek_course_field($ID, $field)
{
    if ( ! $ID ) {
        $ID = get_the_ID();
    }
    $post_meta_value = get_post_meta($ID, FTEK_COURSE_PREFIX . $field);
    if ( count($post_meta_value) < 1) {
        return array(null);
    } else {
        return $post_meta_value;
    }
}

function course_code($ID = NULL)
{
    return strtoupper( get_ftek_course_field($ID, 'code')[0] );
}

function course_website($ID = NULL)
{
    return get_ftek_course_field($ID, 'website')[0];
}

function course_credit($ID = NULL)
{
    $credit = get_ftek_course_field($ID, 'credit')[0];
    if (!$credit or trim($credit) == '') {
        return;
    }
    $no_comma_credit = str_replace(',', '.', $credit);
    return floatval($no_comma_credit) . ' ' . __('credits', 'ftekcp');
}

function course_pingpong($ID = NULL)
{
    return get_ftek_course_field($ID, 'pingpong')[0];
}

function course_outcomes($ID = NULL)
{
    return get_ftek_course_field($ID, 'outcomes')[0];
}

function course_evaluation($ID = NULL)
{
    return get_ftek_course_field($ID, 'evaluation')[0];
}

function course_study_periods($ID = NULL)
{
    return get_ftek_course_field($ID, 'study_periods');
}

function course_programmes($ID = NULL) {
    return get_ftek_course_field($ID, 'programmes');
}

function course_programme_year($ID = NULL)
{
    return get_ftek_course_field($ID, 'programme_year')[0];
}

function course_representatives($ID = NULL)
{
    return get_ftek_course_field($ID, 'representatives')[0];
}

/* 
 * Printing
 */

function course_pretty_classes($ID = NULL)
{  
    $programmes = array_filter(course_programmes($ID));
    $year = course_programme_year($ID);
    if ($year == 'master') {
        return __('Master course', 'ftekcp');
    }
    elseif ($programmes and $year) {
        $classes = array_map(
            function($programme) use ($year) {
                return "$programme$year";
            }, 
            $programmes);
        return join(" ", $classes);
    }
}

function course_pretty_study_periods($ID = NULL)
{
    $SPs = course_study_periods($ID);
    $SP_count = count($SPs);
    $result = '';
    if ($SP_count == 1) {
        $result .= $SPs[0];
    }
    elseif ($SP_count > 1) {
        $result .= $SPs[0] . '–' . end($SPs);
    }
    return $result;
}

function course_pretty_links($ID = NULL)
{
    $website_url = course_website($ID);
    $pingpong_url = course_pingpong($ID);
    $evaluation_url = course_evaluation($ID);
    $outcomes_url = course_outcomes($ID);
    $statistics_url = 'http://tenta.bowald.se/#/search/statistics/' . course_code($ID) . '/chart';
    
    if ( ! ($website_url || $pingpong_url || $evaluation_url || $outcomes_url) ) {
        return;
    }
    $result = '<ul>';
    $links = array(
        'course-website' => array( __('Course website', 'ftekcp'), $website_url ),
        'course-pingpong' => array( 'PingPong', $pingpong_url ),
        'course-evaluation' => array( __('Course evaluation', 'ftekcp'), $evaluation_url ),
        'course-outcomes' => array( __('Intended course outcomes', 'ftekcp'), $outcomes_url ),
        'course-statistics' => array( __('Exam statistics', 'ftekcp'), $statistics_url ),
        );
    foreach ($links as $id => $data) {
        if ($data[1]) {
            $result .= "<li><a href='$data[1]' target='_blank' id='".sanitize_title($id)."'>$data[0]</a></li>";
        }
    }
    return $result . '</ul>';
}

function course_pretty_representatives($ID = NULL)
{
    $reps = array_filter(course_representatives($ID));
    if ( ! $reps ) {
        return;
    }
    $list = join("\n", array_map('course_print_rep', $reps));
    return "<ul>$list</ul>";
}

function course_print_rep($cid)
{
    $user = get_user_by('login', $cid);
    if ($user) {
        $first = $user->user_firstname;
        $last  = $user->user_lastname;
        $name = "$first $last";
        $email = $user->user_email;
    } 
    else {
        set_include_path(get_include_path().PATH_SEPARATOR.'/usr/local/spidera/php/');
        include_once('ldap_functions.php');
        if ( class_exists('LDAPUser') ) {
            $ldap_user = new LDAPUser($cid);
            $name = $ldap_user->full_name;
            $email = $ldap_user->email;
        }
        else {
            return '';
        }
    }
    return "<li>"
            . "<a href='mailto:$email'>"
                . $name
            . "</a>"
        . "</li>";
}

// This is not internationalised, since the files are read directly from the file system.
function course_vbl($ID = NULL) 
{
    if (!function_exists('ftek_documents_listing')) {
        echo 'You have to enable the plugin ftek_documents.';
    }
    $course_code = strtolower( course_code($ID) );
    if (!$course_code) {
        return '';
    }
    // Path relative to uploads folder!
    $path = "vbl-documents/$course_code";
    $sorting_options = array('doc_order' => SORT_DESC);
    $listing = ftek_documents_listing($path, $sorting_options);
    if ( empty(trim($listing)) ) {
        $text = __("There doesn't seem to be any study material for this course.", 'ftekcp');
        return "<p>$text</p>";
    }
    else {
        return $listing;
    }
}
