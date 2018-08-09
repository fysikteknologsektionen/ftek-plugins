<?php
/*
Plugin Name: Sync Taxonomy Terms
Description: Sync terms between taxonomies ('category' & 'event category')
Version: 1.0.0
Author: Johan Winther (johwin)
*/

// Prevent direct file access
defined( 'ABSPATH' ) or exit;


/* Term is added */
function sync_taxonomy_create_term( $term_id, $tt_id, $taxonomy ) {
    
    // unhook this function so it doesn't loop infinitely
    remove_action('created_term', 'sync_taxonomy_create_term');

    $taxonomies = array('category', 'event-category');
    
    // Only sync between category and event category
    if ( !in_array( $taxonomy, $taxonomies ) ) {
        return;
    }
    
    $is_category = $taxonomy === 'category';
    $term = get_term( $term_id, $taxonomy );

    // Check if term with slug doesn't exist in the other taxonomy
    if ( !term_exists( $term->slug, $taxonomies[$is_category] )) {

        // Insert new term
        wp_insert_term(
            $term->name,   // the term name
            $taxonomies[$is_category], // the taxonomy
            array(
                'description' => $term->description,
                'slug'        => $term->slug,
            )
        );
    }
}
add_action( 'created_term' , 'sync_taxonomy_create_term' , 10, 3 );


/* Term is removed */
function sync_taxonomy_delete_term( $term_id, $tt_id, $taxonomy, $deleted_term ) {
    
    // unhook this function so it doesn't loop infinitely
    remove_action('deleted_term', 'sync_taxonomy_delete_term');


    $taxonomies = array('category', 'event-category');
    
    // Only sync between category and event category
    if ( !in_array( $taxonomy, $taxonomies ) ) {
        return;
    }
    
    $is_category = $taxonomy === 'category';

    // Check if term with slug exists in the other taxonomy
    if ( term_exists( $deleted_term->slug, $taxonomies[$is_category] )) {

        $term = get_term_by('slug', $deleted_term->slug, $taxonomies[$is_category] );

        // Delete term
        wp_delete_term( $term->term_id, $taxonomies[$is_category] );
    }
}
add_action( 'deleted_term' , 'sync_taxonomy_delete_term' , 10, 4 );

/* Term is updated */
function sync_taxonomy_edit_term( $term_id, $tt_id, $taxonomy ) {

    // unhook this function so it doesn't loop infinitely
    remove_action('edited_term', 'sync_taxonomy_edit_term');

    $taxonomies = array('category', 'event-category');
    
    // Only sync between category and event category
    if ( !in_array( $taxonomy, $taxonomies ) ) {
        return;
    }

    $is_category = $taxonomy === 'category';
    $updated_term = get_term( $term_id, $taxonomy );

    // Check if term with slug exists in the other taxonomy
    if ( term_exists( $updated_term->slug, $taxonomies[$is_category] )) {

        $term = get_term_by('slug', $updated_term->slug, $taxonomies[$is_category] );

        // Update term
        wp_update_term( $term->term_id, $taxonomies[$is_category], array(
            'name' => $updated_term->name,
        ));

    } else { // If it doesn't exist, create a new term
        sync_taxonomy_create_term( $term_id, null, $taxonomy );
    }
}
add_action( 'edited_term' , 'sync_taxonomy_edit_term' , 10, 3 );