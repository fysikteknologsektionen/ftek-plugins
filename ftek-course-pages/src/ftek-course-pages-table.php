<?php

/* Creates the table of all courses, which is then made searchable 
   through javascript */

function ftek_course_table_shortcode($atts, $content, $tag)
{
    extract( shortcode_atts( array(
	), $atts ) );
    
    $args = array(
        'post_type' => 'ftek_course_page',
        'nopaging'  => true,
        'orderby'  => 'title',
        'order'     => 'ASC',
    );
    
    global $course_table_page;
    $course_table_page = true;
    
    $query = new WP_Query( $args );
    if ( ! $query->have_posts() ) {
        return;
    }
    
    $script_url = plugins_url() . '/ftek-course-pages/js/';
	wp_enqueue_script( 'datatables', 
                        $script_url . 'jquery.dataTables.min.js', 
                        array('jquery'), 
                        '1.9.4', 
                        false);
    wp_enqueue_script( 'ftek_course_table', 
                        $script_url . 'table.js',
                        array( 'jquery', 'datatables' ),
                        false,
                        false);
        
    $output = "<table id='course-table' class='tablepress'>";
    
    
    $output .= "<thead><tr>";
    $headers = array(
        __('Course page on ftek.se', 'ftekcp'),
        __('Course code', 'ftekcp'),
        __('Class', 'ftekcp'),
        __('Period', 'ftekcp'),
        __('External links', 'ftekcp'),
    );
    foreach ($headers as $header) {
        $output .= "<th>$header</th>";
    }
    $output .= "</tr></thead>";
    
    
    $output .= "<tbody>";
    while ( $query->have_posts() ) {
        $query->the_post();
        $url = qtrans_convertURL(get_permalink());
        $cells = array(
            "<a href='$url'>".get_the_title()."</a>",
            course_code(),
            course_pretty_classes(),
            'LP '.course_pretty_study_periods(),
            course_pretty_links(),
        );
        $output .= "<tr>";
        foreach ($cells as $cell) {
            $output .= "<td>$cell</td>";
        }
        $output .=  "</tr>";
    }
    $output .= "</tbody>";
    
    $output .= "</table>";
    
    wp_reset_postdata();
    
    return $output;
}
add_shortcode('ftek_course_table', 'ftek_course_table_shortcode');
