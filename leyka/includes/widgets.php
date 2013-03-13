<?php
/**
 * @package Leyka
 * @subpackage Widgets
 * @copyright Copyright (C) 2012-2013 by Teplitsa of Social Technologies (te-st.ru).
 * @author Lev Zvyagintsev aka Ahaenor
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License v2 or later
 * @since 1.0
 */

if( !defined('ABSPATH') ) exit; // Exit if accessed directly

/** Donations counter widget. */
class leyka_counter_widget extends WP_Widget {
    function __construct()
    {
        parent::WP_Widget(
            false,
            __('Donations counter', 'leyka'),
            array('description' => __('Display the donations counter, with progressbar etc.', 'leyka'))
        );
    }

    /**
     * Front-end widget looks.
     * @see WP_Widget::widget
     */
    function widget($args, $instance)
    {
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);
        $tax = $instance['taxonomy'];

        global $post, $edd_options;

        /**
         * Global vars from WP's widget API
         * 
         * @var $before_widget
         * @var $before_title
         * @var $after_title
         * @var $after_widget
         */
        echo $before_widget;
        echo $title ? $before_title.$title.$after_title : '';
        echo '<pre>'.print_r('Here comes the widget', TRUE).'</pre>';

        do_action( 'edd_before_taxonomy_widget' );
        $terms = get_terms( $tax );

        if ( is_wp_error( $terms ) ) {
            return;
        } else {
            echo "<ul class=\"edd-taxonomy-widget\">\n";
            foreach ( $terms as $term ) {
                echo '<li><a href="' . get_term_link( $term ) . '" title="' . esc_attr( $term->name ) . '" rel="bookmark">' . $term->name . '</a></li>'."\n";
            }
            echo "</ul>\n";
        }

        do_action( 'edd_after_taxonomy_widget' );
        echo $after_widget;
    }

    /**
     * Back-end widget options form.
     *
     * @see WP_Widget::form()
     * @param array $instance Previously saved values from database.
     * @return void
     */
    public function form( $instance ) {
        $title = empty($instance['title']) ? __('New title', 'leyka') : $instance['title'];
        ?>
    <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:');?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title');?>" type="text" value="<?php echo esc_attr($title);?>" />
    </p>
    <?php
    }

    /**
     * Processing widget options saving.
     * 
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = strip_tags( $new_instance['title'] );

        return $instance;
    }
}

function leyka_widgets_init(){
    register_widget('leyka_counter_widget');
}
add_action('widgets_init', 'leyka_widgets_init');