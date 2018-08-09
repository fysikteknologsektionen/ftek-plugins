<?php


add_action( 'widgets_init', 'register_ftek_course_widget' );
function register_ftek_course_widget() {
	register_widget( 'FtekCourseWidget' );
}

class FtekCourseWidget extends WP_Widget {

	function FtekCourseWidget() {
		// Instantiate the parent object
		parent::__construct(
            'ftek_course_widget', 
            __('Ftek Course Widget', 'ftekcp'),
            array( 
                'description' => __('Shows current courses for F and TM at Chalmers University', 'ftekcp'),
                'classname' => 'ftek_course_widget',
            )
        );
	}

	function widget( $args, $instance ) {
        // This widget is useless if you're not Swedish
        // as it only shows first three years
        if (qtrans_getLanguage() != 'sv') {
            return '';
        }
        // We have to do this
        echo $args['before_widget'];
        $title = __('Courses', 'ftekcp');
        $title = apply_filters( 'widget_title', $title);
        echo $args['before_title'] . '<a href="/kurser" title="'.__('More courses', 'ftekcp').'">' .$title. '</a>' . $args['after_title'];
        
        // Our stuff begins
        $prefix = FTEK_COURSE_PREFIX;
        $current_study_period = current_study_period();
        
        for ($year=1; $year <= 3; $year++) { 
            
            $query = course_query($year);
            
            echo '<h3>';
            echo __('Year', 'ftekcp') . ' ' . $year; 
            echo ' (';
            echo __('Schedule', 'ftekcp') . ' ';
            echo join(", ", timeEdit_links($year));
            echo ')';
            echo '</h3>';
            
            if ( $query->have_posts() ) {
                echo '<ul>';
            	while ( $query->have_posts() ) {
            		$query->the_post();
                    
                    // For some reason I can't filter this
                    // in the query... Don't know why.
                    if ( ! in_array(
                        $current_study_period, 
                        course_study_periods()
                    )) continue;
                        
            		echo '<li><a href="'
                         . get_permalink() 
                         . '">'
                         . get_the_title() 
                         . '</a></li>';
            	}
                echo '</ul>';
            } else {
            	// no posts found
            }
        }

        $course_page = get_page_by_path("kurser");
        if ($course_page) {
            $url = get_permalink( $course_page->ID );
            echo "<a class='more-link more-courses-link' href='$url'>";
            echo __('More courses', 'ftekcp') .  '&#8594;';
            echo "</a>";
        }
        echo $args['after_widget'];
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
	}

	function form( $instance ) {
		// Output admin widget options form
	}
}



function course_query($programme_year)
{
    $args = array
    (
        'post_type' => 'ftek_course_page',
        'nopaging' => true,
        'meta_key' => FTEK_COURSE_PREFIX . 'attendants',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'meta_query' => array(
            array(
                'key' => FTEK_COURSE_PREFIX . 'programme_year',
                'value' => "$programme_year",
            ),
            array(
                'key' => FTEK_COURSE_PREFIX . 'attendants',
                'value' => 10,
                'type' => 'numeric',
                'compare' => '>='
            )
        )
    );
    return new WP_Query($args);
}



function timeEdit_links($programme_year)
{
    $programmes = array('F', 'TM'); 
    
    return array_map(function($programme) use ($programme_year) {
        $timeEdit_url = timeEdit_url($programme, $programme_year);
        return '<a href="' . $timeEdit_url . '">'
                 . $programme // . $programme_year
             . '</a>';
    }, $programmes);
}
