<? if( !defined('WPINC') ) die;

/**
 * Leyka widgets
 **/

add_action('widgets_init', 'leyka_custom_widgets', 15);
function leyka_custom_widgets(){
	
	register_widget('Leyka_Donations_List_Widget');
	register_widget('Leyka_Campaign_Card_Widget');	
}


/** Campaign card widget **/
class Leyka_Campaign_Card_Widget extends WP_Widget {
	
	/** Widget setup */
	function __construct() {
        		
		$widget_ops = array(
			'classname'   => 'leyka_campaign_card',
			'description' => __('Campaign informer with configurable elements', 'leyka')
		);
		$this->WP_Widget('leyka_campaign_card',  __('Leyka: Campaign Card', 'leyka'), $widget_ops);	
	}

	
	/** Display widget */
	function widget($args, $instance) {
		global $post;
		
		extract($args, EXTR_SKIP);

		$title = apply_filters('widget_title', $instance['title']);
		
		if($instance['campaign_id'] == ''){
			$query = new WP_Query(array(
				'post_type' => 'leyka_campaign',
				'posts_per_page' => 1
			));
			if(!$query->have_posts())
				return;
			
			$campaign_id = $query->posts[0]->ID;
		}
		else {
			$campaign_id = intval($instance['campaign_id']);	
		}
		
		$args = array(
			'show_title'    => intval($instance['show_title']),
			'show_thumb'    => intval($instance['show_thumb']),
			'show_excerpt'  => intval($instance['show_excerpt']),
			'show_scale'    => intval($instance['show_scale']),
			'show_button'   => intval($instance['show_button']),
			'button_target' => ($campaign_id == 0) ? 'form' : 'page' //where button should point
		);
				
		$html = leyka_get_campaign_card($campaign_id, $args);
		if(empty($html))
			return;
				
		echo $before_widget;		
        if($title){
		    echo $before_title.$title.$after_title;
        }
		
		echo $html;
		
		echo $after_widget;
	}
	
	
	/** Update widget */
	function update($new_instance, $old_instance) {

		$instance = $old_instance;
		
		$instance['title']        = sanitize_text_field($new_instance['title']);					
		$instance['campaign_id']  = sanitize_text_field($new_instance['campaign_id']);
		
		$instance['show_title']   = intval($new_instance['show_title']);
		$instance['show_thumb']   = intval($new_instance['show_thumb']);
		$instance['show_excerpt'] = intval($new_instance['show_excerpt']);
		$instance['show_scale']   = intval($new_instance['show_scale']);
		$instance['show_button']  = intval($new_instance['show_button']);
		
		return $instance;
	}
	
	
	/** Widget setting */
	function form($instance) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title'        => '',		
			'campaign_id'  => '',
			'show_title'    => 1,
			'show_thumb'    => 1,
			'show_excerpt'  => 1,
			'show_scale'    => 1,
			'show_button'   => 1,
		);

		$instance = wp_parse_args((array)$instance, $defaults);
		
		$title        = esc_attr($instance['title']);								
		$campaign_id  = esc_attr($instance['campaign_id']);
		
		$show_title   = intval($instance['show_title']);
		$show_thumb   = intval($instance['show_thumb']);
		$show_excerpt = intval($instance['show_excerpt']);
		$show_scale   = intval($instance['show_scale']);
		$show_button  = intval($instance['show_button']);
	?>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title', 'leyka');?>:</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo $title; ?>">
		</p>
			
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('campaign_id')); ?>"><?php _e('Campaign ID', 'leyka');?>:</label>
			<input id="<?php echo $this->get_field_id( 'campaign_id' ); ?>" name="<?php echo $this->get_field_name( 'campaign_id' ); ?>" value="<?php echo $campaign_id; ?>" type="text"><br>
			<small class="help"><?php _e('Copy-paste ID of the campaign to filter donations in the list, state "0" to detect it from context or leave the field blank to display the most recent campaign', 'leyka');?></small>
		</p>
			
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('show_title')); ?>">
			<input id="<?php echo $this->get_field_id( 'show_title' ); ?>" name="<?php echo $this->get_field_name( 'show_title' ); ?>" value="1" type="checkbox" <?php checked($show_title, 1);?>>
			<?php _e('Show title', 'leyka');?></label>
		<br>
			<label for="<?php echo esc_attr($this->get_field_id('show_thumb')); ?>">
			<input id="<?php echo $this->get_field_id( 'show_thumb' ); ?>" name="<?php echo $this->get_field_name( 'show_thumb' ); ?>" value="1" type="checkbox" <?php checked($show_thumb, 1);?>>
			<?php _e('Show thumbnail', 'leyka');?></label>
		<br>
			<label for="<?php echo esc_attr($this->get_field_id('show_excerpt')); ?>">
			<input id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" value="1" type="checkbox" <?php checked($show_excerpt, 1);?>>
			<?php _e('Show excerpt', 'leyka');?></label>
		<br>
			<label for="<?php echo esc_attr($this->get_field_id('show_scale')); ?>">
			<input id="<?php echo $this->get_field_id( 'show_scale' ); ?>" name="<?php echo $this->get_field_name( 'show_scale' ); ?>" value="1" type="checkbox" <?php checked($show_scale, 1);?>>
			<?php _e('Show scale', 'leyka');?></label>
		<br>
			<label for="<?php echo esc_attr($this->get_field_id('show_button')); ?>">
			<input id="<?php echo $this->get_field_id( 'show_button' ); ?>" name="<?php echo $this->get_field_name( 'show_button' ); ?>" value="1" type="checkbox" <?php checked($show_button, 1);?>>
			<?php _e('Show "support" button', 'leyka');?></label>
		</p>
		
	<?php
	}
	
	
} //class end

 
/** Donors list widget **/
class Leyka_Donations_List_Widget extends WP_Widget {
	
	/** Widget setup */
	function __construct() {
        		
		$widget_ops = array(
			'classname'   => 'leyka_donations_list',
			'description' => __('Recent donations list optionally filtered by campaign', 'leyka')
		);
		$this->WP_Widget('leyka_donations_list',  __('Leyka: Donations List', 'leyka'), $widget_ops);	
	}

	
	/** Display widget */
	function widget($args, $instance) {
		global $post;
		
		extract($args, EXTR_SKIP);

		$title = apply_filters('widget_title', $instance['title']);
		
		if($instance['campaign_id'] == ''){
			$campaign_id = 'all';
		}
		else {
			$campaign_id = intval($instance['campaign_id']);	
		}
		
		$args = array(
			'num'          => intval($instance['limit']),
			'show_purpose' => intval($instance['show_purpose']),
			'show_name'    => intval($instance['show_name']),
			'show_date'    => intval($instance['show_date']),
		);
		
		$html = leyka_get_donors_list($campaign_id, $args);
		if(empty($html))
			return;
				
		echo $before_widget;		
        if($title){
		    echo $before_title.$title.$after_title;
        }
		
		echo $html;
		
		echo $after_widget;
	}
	
	
	/** Update widget */
	function update($new_instance, $old_instance) {

		$instance = $old_instance;
		
		$instance['title']        = sanitize_text_field($new_instance['title']);			
		$instance['limit']        = intval($new_instance['limit']);
		$instance['campaign_id']  = sanitize_text_field($new_instance['campaign_id']);		
		$instance['show_purpose'] = intval($new_instance['show_purpose']);
		$instance['show_name']    = intval($new_instance['show_name']);
		$instance['show_date']    = intval($new_instance['show_date']);
		
		return $instance;
	}
	
	
	/** Widget setting */
	function form($instance) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title'        => '',				
			'limit'        => 5,
			'campaign_id'  => '',
			'show_purpose' => 1,
			'show_name'    => 1,
			'show_date'    => 1,
		);

		$instance = wp_parse_args((array)$instance, $defaults);
		
		$title        = esc_attr($instance['title']);				
		$limit        = intval($instance['limit']);				
		$campaign_id  = esc_attr($instance['campaign_id']);
		$show_purpose = intval($instance['show_purpose']);
		$show_name    = intval($instance['show_name']);
		$show_date    = intval($instance['show_date']);
	?>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title', 'leyka');?>:</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo $title; ?>">
		</p>
			
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php _e('Number', 'leyka');?>:</label>
			<select class="widefat" name="<?php echo $this->get_field_name('limit'); ?>" id="<?php echo $this->get_field_id('limit'); ?>">
				<?php for ($i = 5; $i <= 25; $i+= 5) { ?>
					<option <?php selected($limit, $i) ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php } ?>
			</select>
		</p>	
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('campaign_id')); ?>"><?php _e('Campaign ID', 'leyka');?>:</label>
			<input id="<?php echo $this->get_field_id( 'campaign_id' ); ?>" name="<?php echo $this->get_field_name( 'campaign_id' ); ?>" value="<?php echo $campaign_id; ?>" type="text"><br>
			<small class="help"><?php _e('Copy-paste ID of the campaign to filter donations in the list, state "0" to detect it from context or leave the field blank to display recent entries', 'leyka');?></small>
		</p>
		
		<h4><?php _e('Donation item settings', 'leyka');?></h4>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('show_purpose')); ?>">
			<input id="<?php echo $this->get_field_id( 'show_purpose' ); ?>" name="<?php echo $this->get_field_name( 'show_purpose' ); ?>" value="1" type="checkbox" <?php checked($show_purpose, 1);?>>
			<?php _e('Show donation purpose', 'leyka');?></label>
		<br>
			<label for="<?php echo esc_attr($this->get_field_id('show_name')); ?>">
			<input id="<?php echo $this->get_field_id( 'show_name' ); ?>" name="<?php echo $this->get_field_name( 'show_name' ); ?>" value="1" type="checkbox" <?php checked($show_name, 1);?>>
			<?php _e('Show donor\'s name', 'leyka');?></label>
		<br>
			<label for="<?php echo esc_attr($this->get_field_id('show_date')); ?>">
			<input id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" value="1" type="checkbox" <?php checked($show_date, 1);?>>
			<?php _e('Show donation date', 'leyka');?></label>
		</p>
		
	<?php
	}
	
	
} //class end
