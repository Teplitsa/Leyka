<?php if( !defined('WPINC') ) die;

/**
 * Leyka widgets
 **/

add_action('widgets_init', 'leyka_custom_widgets', 15);
function leyka_custom_widgets(){

	register_widget('Leyka_Donations_List_Widget');
	register_widget('Leyka_Campaign_Card_Widget');
	register_widget('Leyka_Campaigns_List_Widget');
}


/** Campaign card widget **/
class Leyka_Campaign_Card_Widget extends WP_Widget {

	/** Widget setup */
	public function __construct() {

		$widget_ops = array(
			'classname'   => 'leyka_campaign_card',
			'description' => __('Campaign informer with configurable elements', 'leyka')
		);
		$this->WP_Widget('leyka_campaign_card',  __('Leyka: Campaign Card', 'leyka'), $widget_ops);	
	}

	/** Display widget */
    public function widget($args, $instance) {
		global $post;
		
		extract($args, EXTR_SKIP);

		$title = apply_filters('widget_title', $instance['title']);
				
		if($instance['campaign_id'] == '') {

            $query = new WP_Query(array(
				'post_type' => Leyka_Campaign_Management::$post_type,
				'posts_per_page' => 1,
			));
			if( !$query->have_posts() )
				return;

			$campaign_id = $query->posts[0]->ID;
		}
		elseif(intval($instance['campaign_id']) === 0)			
			$campaign_id = null;
		else 
			$campaign_id = (int)$instance['campaign_id'];
		
		$args = array(
			'show_title'    => !empty($instance['show_title']),
			'show_thumb'    => !empty($instance['show_thumb']),
			'show_excerpt'  => !empty($instance['show_excerpt']),
			'show_scale'    => !empty($instance['show_scale']),
			'show_button'   => !empty($instance['show_button']),			
		);		
		
		$css_id = 'leyka_campaign_card_widget-'.uniqid();
		$html = leyka_get_campaign_card($campaign_id, $args);
		if(empty($html))
			return;

		echo $before_widget;		
        if($title)
		    echo $before_title.$title.$after_title;

		echo '<div id="'.esc_attr($css_id).'">'.$html."</div>";

		echo $after_widget;
	}

	/** Update widget */
    public function update($new_instance, $old_instance) {

		$instance = $old_instance;

		$instance['title']        = sanitize_text_field($new_instance['title']);					
		$instance['campaign_id']  = sanitize_text_field($new_instance['campaign_id']);

		$instance['show_title']   = !empty($new_instance['show_title']);
		$instance['show_thumb']   = !empty($new_instance['show_thumb']);
		$instance['show_excerpt'] = !empty($new_instance['show_excerpt']);
		$instance['show_scale']   = !empty($new_instance['show_scale']);
		$instance['show_button']  = !empty($new_instance['show_button']);

		return $instance;
	}

	/** Widget setting */
    public function form($instance) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title'        => '',		
			'campaign_id'  => '',
			'show_title'   => 1,
			'show_thumb'   => 1,
			'show_excerpt' => 1,
			'show_scale'   => 1,
			'show_button'  => 1,
		);

		$instance = wp_parse_args((array)$instance, $defaults);?>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title', 'leyka');?>:</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($instance['title']);?>">
		</p>
			
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('campaign_id'));?>"><?php _e('Campaign ID', 'leyka');?>:</label>
			<input id="<?php echo $this->get_field_id('campaign_id');?>" name="<?php echo $this->get_field_name( 'campaign_id' ); ?>" value="<?php echo esc_attr($instance['campaign_id']);?>" type="text" /><br />
			<small class="help"><?php _e('Copy-paste ID of the campaign to output, state "0" to detect it from context or leave the field blank to display the most recent campaign', 'leyka');?></small>
		</p>
			
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('show_title'));?>">
			    <input id="<?php echo $this->get_field_id('show_title');?>" name="<?php echo $this->get_field_name('show_title');?>" value="1" type="checkbox" <?php checked( !!$instance['show_title'], 1 );?> />
			    <?php _e('Show title', 'leyka');?>
            </label>
		    <br />
			<label for="<?php echo esc_attr($this->get_field_id('show_thumb')); ?>">
			    <input id="<?php echo $this->get_field_id( 'show_thumb' ); ?>" name="<?php echo $this->get_field_name('show_thumb');?>" value="1" type="checkbox" <?php checked( !!$instance['show_thumb'], 1 );?> />
			    <?php _e('Show thumbnail', 'leyka');?>
            </label>
		    <br />
			<label for="<?php echo esc_attr($this->get_field_id('show_excerpt'));?>">
			    <input id="<?php echo $this->get_field_id( 'show_excerpt' ); ?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" value="1" type="checkbox" <?php checked( !!$instance['show_excerpt'], 1 );?>>
			    <?php _e('Show excerpt', 'leyka');?>
            </label>
		    <br />
			<label for="<?php echo esc_attr($this->get_field_id('show_scale'));?>">
			    <input id="<?php echo $this->get_field_id( 'show_scale' ); ?>" name="<?php echo $this->get_field_name( 'show_scale' ); ?>" value="1" type="checkbox" <?php checked( !!$instance['show_scale'], 1 );?> />
			    <?php _e('Show scale', 'leyka');?>
            </label>
		    <br />
			<label for="<?php echo esc_attr($this->get_field_id('show_button')); ?>">
			    <input id="<?php echo $this->get_field_id( 'show_button' ); ?>" name="<?php echo $this->get_field_name( 'show_button' ); ?>" value="1" type="checkbox" <?php checked( !!$instance['show_button'], 1 );?> />
			    <?php _e('Show «support» button', 'leyka');?>
            </label>
		</p>

	<?php }
} //class end



/** Campaign card widget **/
class Leyka_Campaigns_List_Widget extends WP_Widget {
	
	/** Widget setup */
    public function __construct() {
        		
		$widget_ops = array(
			'classname'   => 'leyka_campaigns_list',
			'description' => __('List of recent campaigns with configurable attributes', 'leyka')
		);
		$this->WP_Widget('leyka_campaigns_list',  __('Leyka: Campaigns List', 'leyka'), $widget_ops);	
	}

	/** Display widget */
	public function widget($args, $instance) {

		global $post;

		extract($args, EXTR_SKIP);

		$title = apply_filters('widget_title', $instance['title']);

		$q_args = array(
			'post_type' => Leyka_Campaign_Management::$post_type,
			'posts_per_page' => empty($instance['limit']) ? 3 : (int)$instance['limit'],
			'post_status' => 'publish',
		);

		if( !empty($instance['include']) )
			$q_args['post__in'] = array_map('intval', explode(',', $instance['include']));

		if( !empty($instance['exclude']) )
			$q_args['post__not_in'] = array_map('intval', explode(',', $instance['exclude']));

		$query = new WP_Query(apply_filters('leyka_campaigns_list_widget_query_args', $q_args, $instance));
		if( !$query->have_posts() )
			return;

		$args = array(
			'show_title'    => !empty($instance['show_title']),
			'show_thumb'    => !empty($instance['show_thumb']),
			'show_excerpt'  => !empty($instance['show_excerpt']),
			'show_scale'    => !empty($instance['show_scale']),
			'show_button'   => !empty($instance['show_button']),			
		);

		echo $before_widget;		
        if($title)
		    echo $before_title.$title.$after_title;
		
		$css_id = 'leyka_campaign_list_widget-'.uniqid();
		echo "<div id='".esc_attr($css_id)."' class='leyka-campaigns-list'>";
		
		add_filter('leyka_campaign_card_thumbnail_size', array($this, '_campaign_thumb_size'));
		add_filter('leyka_campaign_card_class', array($this, '_campaign_css'));
		
		foreach($query->posts as $qp) {
			echo leyka_get_campaign_card($qp->ID, $args);		
		}
		remove_filter('leyka_campaign_card_thumbnail_size', array($this, '_campaign_thumb_size'));
		remove_filter('leyka_campaign_card_class', array($this, '_campaign_css'));

		echo "</div>";				
		echo $after_widget;
	}
	
	public function _campaign_thumb_size($size){
		
		return 'thumbnail';
	}
	
	public function _campaign_css($css) {
		
		return 'leyka-campaign-list-item';
	}
	
	/** Update widget */
    public function update($new_instance, $old_instance) {

		$instance = $old_instance;
		
		$instance['title'] = sanitize_text_field($new_instance['title']);
		$instance['limit'] = empty($new_instance['limit']) ? 3 : (int)$new_instance['limit'];
		
		$instance['show_title']   = !empty($new_instance['show_title']);
		$instance['show_thumb']   = !empty($new_instance['show_thumb']);
		$instance['show_excerpt'] = !empty($new_instance['show_excerpt']);
		$instance['show_scale']   = !empty($new_instance['show_scale']);
		$instance['show_button']  = !empty($new_instance['show_button']);
		
		$instance['include'] = sanitize_text_field($new_instance['include']);
		$instance['exclude'] = sanitize_text_field($new_instance['exclude']);

		return $instance;
	}
	
	/** Widget setting */
    public function form($instance) {

		/* Set up some default widget settings. */
		$defaults = array(
			'title'        => '',		
			'limit'        => 3,
			'show_title'   => 1,
			'show_thumb'   => 1,
			'show_excerpt' => 1,
			'show_scale'   => 1,
			'show_button'  => 1,
			'include'      => '',
			'exclude'      => '',
		);

		$instance = wp_parse_args((array)$instance, $defaults);

		$limit = (int)$instance['limit'];

		$show_title   = (int)$instance['show_title'];
		$show_thumb   = (int)$instance['show_thumb'];
		$show_excerpt = !empty($instance['show_excerpt']);
		$show_scale   = !empty($instance['show_scale']);
		$show_button  = !empty($instance['show_button']);?>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title', 'leyka');?>:</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($instance['title']);?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('limit'));?>"><?php _e('Number', 'leyka');?>:</label>
			<select class="widefat" name="<?php echo $this->get_field_name('limit');?>" id="<?php echo $this->get_field_id('limit');?>">
				<?php for($i=1; $i<=10; $i++) { ?>
					<option <?php selected($limit, $i);?> value="<?php echo $i;?>"><?php echo $i;?></option>
				<?php }?>
			</select>
		</p>	

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('show_title'));?>">
			<input id="<?php echo $this->get_field_id('show_title');?>" name="<?php echo $this->get_field_name( 'show_title' ); ?>" value="1" type="checkbox" <?php checked($show_title, 1);?>>
			<?php _e('Show title', 'leyka');?></label>
		<br>
			<label for="<?php echo esc_attr($this->get_field_id('show_thumb')); ?>">
			<input id="<?php echo $this->get_field_id('show_thumb');?>" name="<?php echo $this->get_field_name( 'show_thumb' ); ?>" value="1" type="checkbox" <?php checked($show_thumb, 1);?>>
			<?php _e('Show thumbnail', 'leyka');?></label>
		<br>
			<label for="<?php echo esc_attr($this->get_field_id('show_excerpt')); ?>">
			<input id="<?php echo $this->get_field_id('show_excerpt');?>" name="<?php echo $this->get_field_name( 'show_excerpt' ); ?>" value="1" type="checkbox" <?php checked($show_excerpt, 1);?>>
			<?php _e('Show excerpt', 'leyka');?></label>
		<br>
			<label for="<?php echo esc_attr($this->get_field_id('show_scale')); ?>">
			<input id="<?php echo $this->get_field_id('show_scale');?>" name="<?php echo $this->get_field_name( 'show_scale' ); ?>" value="1" type="checkbox" <?php checked($show_scale, 1);?>>
			<?php _e('Show scale', 'leyka');?></label>
		<br>
			<label for="<?php echo esc_attr($this->get_field_id('show_button')); ?>">
			<input id="<?php echo $this->get_field_id('show_button');?>" name="<?php echo $this->get_field_name( 'show_button' ); ?>" value="1" type="checkbox" <?php checked($show_button, 1);?>>
			<?php _e('Show «support» button', 'leyka');?></label>
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('include'));?>"><?php _e('Include campaigns', 'leyka');?>:</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('include'));?>" name="<?php echo esc_attr($this->get_field_name('include'));?>" type="text" value="<?php echo esc_attr($instance['include']);?>" />
			<small class="help"><?php _e('Comma-separated list of IDs', 'leyka');?></small>
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('exclude'));?>"><?php _e('Exclude campaigns', 'leyka');?>:</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('exclude'));?>" name="<?php echo esc_attr($this->get_field_name('exclude'));?>" type="text" value="<?php echo esc_attr($instance['exclude']);?>" />
			<small class="help"><?php _e('Comma-separated list of IDs', 'leyka');?></small>
		</p>
		
	<?php }
} //class end

/** Donors list widget **/
class Leyka_Donations_List_Widget extends WP_Widget {
	
	/** Widget setup */
	public function __construct() {
        		
		$widget_ops = array(
			'classname'   => 'leyka_donations_list',
			'description' => __('Recent donations list, optionally filtered by campaign', 'leyka')
		);
		$this->WP_Widget('leyka_donations_list',  __('Leyka: Donations List', 'leyka'), $widget_ops);	
	}

	/** Display widget */
    public function widget($args, $instance) {

//		global $post;

		extract($args, EXTR_SKIP);

		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
		
		$campaign_id = ($instance['campaign_id'] == '') ? 'all' : (int)$instance['campaign_id'];

		$args = array(
			'num'          => empty($instance['limit']) ? 5 : (int)$instance['limit'],
			'show_purpose' => !empty($instance['show_purpose']),
			'show_name'    => !empty($instance['show_name']),
			'show_date'    => !empty($instance['show_date']),
		);

		$html = leyka_get_donors_list($campaign_id, $args);
		if(empty($html))
			return;

		echo $before_widget;
        if($title)
		    echo $before_title.$title.$after_title;

		echo $html;

		echo $after_widget;
	}
	
	/** Update widget */
    public function update($new_instance, $old_instance) {

		$instance = $old_instance;
		
		$instance['title']        = sanitize_text_field($new_instance['title']);			
		$instance['limit']        = empty($new_instance['limit']) ? 5 : (int)$new_instance['limit'];
		$instance['campaign_id']  = sanitize_text_field($new_instance['campaign_id']);		
		$instance['show_purpose'] = !empty($new_instance['show_purpose']);
		$instance['show_name']    = !empty($new_instance['show_name']);
		$instance['show_date']    = !empty($new_instance['show_date']);
		
		return $instance;
	}

	/** Widget setting */
    public function form($instance) {

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
		$limit        = (int)$instance['limit'];
		$campaign_id  = esc_attr($instance['campaign_id']);
		$show_purpose = !!$instance['show_purpose'];
		$show_name    = !!$instance['show_name'];
		$show_date    = !!$instance['show_date'];?>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title', 'leyka');?>:</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo $title; ?>" />
		</p>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('limit'));?>"><?php _e('Donations number', 'leyka');?>:</label>
			<select class="widefat" name="<?php echo $this->get_field_name('limit');?>" id="<?php echo $this->get_field_id('limit');?>">
				<?php for($i=5; $i<=25; $i+= 5) {?>
					<option <?php selected($limit, $i) ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
				<?php }?>
			</select>
		</p>	

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('campaign_id'));?>"><?php _e('Campaign ID', 'leyka');?>:</label>
			<input id="<?php echo $this->get_field_id('campaign_id'); ?>" name="<?php echo $this->get_field_name('campaign_id');?>" value="<?php echo $campaign_id;?>" type="text" /><br />
			<small class="help"><?php _e('Copy-paste ID of the campaign to filter donations in the list, state "0" to detect it from context or leave the field blank to display recent entries', 'leyka');?></small>
		</p>

		<h4><?php _e('Donation item settings', 'leyka');?></h4>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('show_purpose'));?>">
			    <input id="<?php echo $this->get_field_id('show_purpose'); ?>" name="<?php echo $this->get_field_name('show_purpose');?>" value="1" type="checkbox" <?php checked($show_purpose, 1);?> />
			    <?php _e('Show donation purpose', 'leyka');?>
            </label>
		    <br />
			<label for="<?php echo esc_attr($this->get_field_id('show_name'));?>">
			    <input id="<?php echo $this->get_field_id('show_name');?>" name="<?php echo $this->get_field_name('show_name');?>" value="1" type="checkbox" <?php checked($show_name, 1);?> />
			    <?php _e("Show donor's name", 'leyka');?>
            </label>
		    <br />
			<label for="<?php echo esc_attr($this->get_field_id('show_date'));?>">
			    <input id="<?php echo $this->get_field_id('show_date');?>" name="<?php echo $this->get_field_name('show_date');?>" value="1" type="checkbox" <?php checked($show_date, 1);?> />
			    <?php _e('Show donation date', 'leyka');?>
            </label>
		</p>

	<?php }
} //class end
