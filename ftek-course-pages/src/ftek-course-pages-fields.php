<?php

// Initialize the metabox class
add_action( 'init', 'be_initialize_cmb_meta_boxes', 9999 );
function be_initialize_cmb_meta_boxes() {
	if ( !class_exists( 'cmb_Meta_Box' ) ) {
		require_once( plugin_dir_path(__FILE__).'../lib/metabox/init.php' );
	}
}

// Create metabox

define('FTEK_COURSE_PREFIX', '_ftek_course_');

function ftek_courses_metaboxes( $meta_boxes ) {
	$prefix = FTEK_COURSE_PREFIX; // Prefix for all fields
	$meta_boxes[] = array(
		'id' => 'ftek_courses_metabox',
		'title' => __('Course info', 'ftekcp'),
		'pages' => array('ftek_course_page'), // post type
		'context' => 'normal',
		'priority' => 'default',
		'show_names' => true, // Show field names on the left
		'fields' => array(
			array(
				'name' => __('Course code', 'ftekcp'),
				'id' => $prefix . 'code',
				'type' => 'text_small',
			),
			array(
				'name' => __('Course credit', 'ftekcp'),
				'id' => $prefix . 'credit',
				'type' => 'text_small',
			),
			array(
				'name' => __('Course website url', 'ftekcp'),
				'id' => $prefix . 'website',
				'type' => 'text_url',
                'placeholder' => 'http://www.math.chalmers.se/Math/Grundutb/CTH/tma970/1314/'
			),
			array(
				'name' => __('PingPong page url', 'ftekcp'),
				'id' => $prefix . 'pingpong',
				'type' => 'text_url'
			),
			array(
				'name' => __('Last course evaluation', 'ftekcp'),
				'id' => $prefix . 'evaluation',
				'type' => 'text_url'
			),
			array(
				'name' => __('Intended learning outcomes', 'ftekcp'),
				'id' => $prefix . 'outcomes',
				'type' => 'text_url'
			),
			array(
				'name' => __('Course representatives', 'ftekcp'),
				'id' => $prefix . 'representatives',
                'desc' => __('Enter cids', 'ftekcp'),
				'type' => 'text_repeat'
			),
			array(
				'name' => __('Study periods', 'ftekcp'),
				'id' => $prefix . 'study_periods',
				'type' => 'multicheck',
                'options' => array(
                    '1' => 1,
                    '2' => 2,
                    '3' => 3,
                    '4' => 4
                )
			),
            array(
				'name' => __('Programme year', 'ftekcp'),
				'id' => $prefix . 'programme_year',
				'type' => 'radio_inline',
                'options' => array(
                    array('name' => '1', 'value' => '1'),
                    array('name' => '2', 'value' => '2'),
                    array('name' => '3', 'value' => '3'),
                    array('name' => __('Master', 'ftekcp'), 'value' => 'master'),
                )
			),
			array(
				'name' => __('Programmes', 'ftekcp'),
				'id' => $prefix . 'programmes',
				'type' => 'multicheck',
                'options' => array( 
                    'F' => 'F',
                    'TM' => 'TM'
                )
			),
			array(
				'name' => __('Course attendants', 'ftekcp'),
                'desc' => __('Approximately how many students attend the course? Used for sorting purposes.', 'ftekcp'),
				'id' => $prefix . 'attendants',
				'type' => 'text',
                'std' => 20
			),
		),
	);

	return $meta_boxes;
}
add_filter( 'cmb_meta_boxes', 'ftek_courses_metaboxes' );
