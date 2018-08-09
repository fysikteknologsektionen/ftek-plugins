<?php

add_action( 'init', 'init_ftek_course_pages' );
function init_ftek_course_pages() {
    // Load translations
    load_plugin_textdomain('ftekcp', false, 'ftek-course-pages/languages' );
    
    // Add new post type
    $labels = array(
	    'name' => __( 'Course pages' , 'ftekcp'),
	    'singular_name' => __( 'Course page', 'ftekcp'),
        'menu_name' => __('Course pages', 'ftekcp'),
        'all_items' => __('All Course Pages', 'ftekcp'),
        'add_new' => __('Add New', 'ftekcp'),
        'add_new_item' => __('Add New Course Page', 'ftekcp'),
        'edit_item' => __('Edit Course Page', 'ftekcp'),
        'new_item' => __('New Course Page', 'ftekcp'),
        'view_item' => __('View Course Page', 'ftekcp'),
        'search_items' => __('Search Course Pages', 'ftekcp'),
        'not_found' => __('No course pages found', 'ftekcp'),
        'not_found_in_trash' => __('No course pages found in Trash', 'ftekcp')
    );
    
    
    $args = array(
        'label' => __( 'Course pages' , 'ftekcp'),
		'labels' => $labels,
		'public' => true,
        'show_ui' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'rewrite' => array(
            'slug' => 'kurs', // Translating this mucks up qtranslate
        ),
        'capability_type' => 'course_page',
        'supports' => array('title', 'editor', 'custom_fields')
	);
    
	register_post_type( 'ftek_course_page', $args);
}

/* Flush rewrite rules – see http://codex.wordpress.org/Function_Reference/register_post_type */

function ftek_course_pages_rewrite_flush() {
    init_ftek_course_pages();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ftek_course_pages_rewrite_flush' );

/* This is so admin can always edit the course pages */

function add_ftek_course_page_admin_capabilities() {
    $role = get_role( 'administrator' );

    $role->add_cap( 'edit_course_page' ); 
    $role->add_cap( 'edit_course_pages' ); 
    $role->add_cap( 'edit_others_course_pages' ); 
    $role->add_cap( 'publish_course_pages' ); 
    $role->add_cap( 'read_course_page' ); 
    $role->add_cap( 'read_private_course_pages' ); 
    $role->add_cap( 'delete_course_page' ); 
}
add_action( 'admin_init', 'add_ftek_course_page_admin_capabilities');


/*
 * Modify qtranslate hook
 *
 * When qtranslate excludes untranslated posts, it looks at the content of the post. This doesn't work well when the content field is empty, as it may be for 
a course page. Therefore, I change it to check the title too, since
the title should never be empty.
 * We also need to make an exception for the Courses page, since otherwise it won't hide untranslated posts.
 */

function modify_qtrans_hook() {
    remove_filter('posts_where_request', 'qtrans_excludeUntranslatedPosts');
    add_filter('posts_where_request', 'modified_qtrans_excludeUntranslatedPosts');
}
add_action('plugins_loaded', 'modify_qtrans_hook');

function modified_qtrans_excludeUntranslatedPosts($where) {
	global $q_config, $wpdb, $course_table_page;
	// if( $q_config['hide_untranslated'] and (!is_singular() or $course_table_page) ) {
	if( $q_config['hide_untranslated'] and (!is_singular() and $course_table_page) ) {
		$where .= " AND ($wpdb->posts.post_title LIKE '%<!--:".qtrans_getLanguage()."-->%' OR $wpdb->posts.post_content LIKE '%<!--:".qtrans_getLanguage()."-->%')";
    }
	return $where;
}

// Make slug equal to course code
add_action( 'save_post', 'ftek_course_code_slug_save' );

function ftek_course_code_slug_save( $post_id ) {
    $course_code = course_code($post_id);
    // verify post is not a revision and course code is present
    if ( ! wp_is_post_revision( $post_id ) && $course_code) {

        // unhook this function to prevent infinite looping
        remove_action( 'save_post', 'ftek_course_code_slug_save' );

        // update the post slug
        wp_update_post( array(
            'ID' => $post_id,
            'post_name' => $course_code
        ));

        // re-hook this function
        add_action( 'save_post', 'ftek_course_code_slug_save' );

    }
}