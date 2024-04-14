<?php if( !defined('WPINC') ) die;
/**
 * Leyka widgets
 **/

add_action('widgets_init', 'leyka_custom_widgets', 15);
function leyka_custom_widgets(){

	register_widget('Leyka_Campaign_Card_Widget');
	register_widget('Leyka_Campaigns_List_Widget');
	register_widget('Leyka_Donations_List_Widget');

}

class Leyka_Campaign_Card_Widget extends WP_Widget {

	public function __construct() {

		parent::__construct('leyka_campaign_card',  __('Leyka: Campaign Card', 'leyka'), [
            'classname'   => 'leyka_campaign_card',
            'description' => __('Campaign informer with configurable elements', 'leyka')
        ]);

	}

	public function widget($args, $instance) {

		extract($args, EXTR_SKIP);

		$title = apply_filters('widget_title', $instance['title']);

		if( !empty($instance['campaign_id']) && $instance['campaign_id'] === '-' ) { // Last campaign

			$query = get_posts(['post_type' => Leyka_Campaign_Management::$post_type, 'posts_per_page' => 1,]);
			if( !$query ) {
				return;
			}

			$campaign_id = reset($query)->ID;

		} else if((int)$instance['campaign_id'] === 0) { // Take campaign from context
			$campaign_id = null;
		} else { // Campaign is set explicitly
			$campaign_id = (int)$instance['campaign_id'];
		}

		$args = [
			'show_title'    => !!$instance['show_title'],
			'show_thumb'    => !!$instance['show_thumb'],
			'show_excerpt'  => !!$instance['show_excerpt'],
			'show_scale'    => !!$instance['show_scale'],
			'show_button'   => !!$instance['show_button'],
		];

		$css_id = 'leyka_campaign_card_widget-'.uniqid();

		$campaign = new Leyka_Campaign($campaign_id);

		if($campaign->campaign_template === 'star') {
		    $html = leyka_shortcode_campaign_card(array_merge(['campaign_id' => $campaign_id], $args));
		}
		else {
		    $html = leyka_get_campaign_card($campaign_id, $args);
		}

		if( !$html ) {
			return;
		}
		$campaign = new Leyka_Campaign($campaign_id);
		if( !leyka_form_is_displayed(false) ) { // Don't increase campaign views counter if we're on a page with this campaign's donation form
			$campaign->increase_views_counter();
		}

		/** @var $before_widget */
		echo wp_kses_post( $before_widget );
		if($title) {
			/**
			 * @var $before_title
			 * @var $after_title
			 */
			echo wp_kses_post( $before_title.$title.$after_title );
		}

		echo '<div id="'. esc_attr($css_id).'">'. wp_kses_post( $html ) . "</div>";

		/** @var $after_widget */
		echo wp_kses_post( $after_widget );

	}

	public function update($new_instance, $old_instance) {

		$instance = $old_instance;

		$instance['title'] = sanitize_text_field($new_instance['title']);
		$instance['campaign_id'] = sanitize_text_field($new_instance['campaign_id']);

		$instance['show_title'] = !empty($new_instance['show_title']);
		$instance['show_thumb'] = !empty($new_instance['show_thumb']);
		$instance['show_excerpt'] = !empty($new_instance['show_excerpt']);
		$instance['show_scale'] = !empty($new_instance['show_scale']);
		$instance['show_button'] = !empty($new_instance['show_button']);

		return $instance;

	}

	public function form($instance) {

		$defaults = [
			'title' => '',
			'campaign_id' => '',
			'show_title' => 1,
			'show_thumb' => 1,
			'show_excerpt' => 1,
			'show_scale' => 1,
			'show_button' => 1,
		];

		$instance = wp_parse_args((array)$instance, $defaults);?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title', 'leyka');?>:
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($instance['title']);?>">
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('campaign_id'));?>">
                <?php esc_html_e('Campaign ID', 'leyka');?>:
            </label>

			<?php $current_value = $instance['campaign_id'];?>
            <select id="<?php echo esc_attr( $this->get_field_id('campaign_id') );?>" name="<?php echo esc_attr( $this->get_field_name( 'campaign_id') );?>" class="widefat">
                <option value="-" <?php echo wp_kses_post( $current_value == '-' ? 'selected="selected"' : '' );?>>
					<?php esc_html_e('The most recent campaign', 'leyka');?>
                </option>
                <option value="0" <?php echo wp_kses_post( $current_value == '0' ? 'selected="selected"' : '' );?>>
					<?php esc_html_e('Campaign based on a context', 'leyka');?>
                </option>

				<?php foreach(get_posts(['post_type' => Leyka_Campaign_Management::$post_type, 'nopaging' => true]) as $campaign) {?>
                    <option value="<?php echo esc_attr( $campaign->ID );?>" <?php echo wp_kses_post( $current_value == $campaign->ID ? 'selected="selected"' : '' );?>>
						<?php echo esc_html( $campaign->post_title );?>
                    </option>
				<?php }?>

            </select>
            <br>
            <small class="help"><?php esc_html_e('Copy-paste ID of the campaign to output, state "0" to detect it from context or leave the field blank to display the most recent campaign', 'leyka');?></small>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('show_title'));?>">
                <input id="<?php echo esc_attr( $this->get_field_id('show_title') );?>" name="<?php echo esc_attr( $this->get_field_name('show_title') );?>" value="1" type="checkbox" <?php checked( !!$instance['show_title'], 1 );?>>
				<?php esc_html_e('Show title', 'leyka');?>
            </label>
            <br>
            <label for="<?php echo esc_attr($this->get_field_id('show_thumb')); ?>">
                <input id="<?php echo esc_attr( $this->get_field_id( 'show_thumb' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name('show_thumb') );?>" value="1" type="checkbox" <?php checked( !!$instance['show_thumb'], 1 );?>>
				<?php esc_html_e('Show thumbnail', 'leyka');?>
            </label>
            <br>
            <label for="<?php echo esc_attr($this->get_field_id('show_excerpt'));?>">
                <input id="<?php echo esc_attr( $this->get_field_id( 'show_excerpt' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_excerpt' ) ); ?>" value="1" type="checkbox" <?php checked( !!$instance['show_excerpt'], 1 );?>>
				<?php esc_html_e('Show excerpt', 'leyka');?>
            </label>
            <br>
            <label for="<?php echo esc_attr($this->get_field_id('show_scale'));?>">
                <input id="<?php echo esc_attr( $this->get_field_id( 'show_scale' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_scale' ) ); ?>" value="1" type="checkbox" <?php checked( !!$instance['show_scale'], 1 );?>>
				<?php esc_html_e('Show scale', 'leyka');?>
            </label>
            <br>
            <label for="<?php echo esc_attr($this->get_field_id('show_button')); ?>">
                <input id="<?php echo esc_attr( $this->get_field_id( 'show_button' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_button' ) ); ?>" value="1" type="checkbox" <?php checked( !!$instance['show_button'], 1 );?>>
				<?php esc_html_e('Show «support» button', 'leyka');?>
            </label>
        </p>

	<?php }

}

class Leyka_Campaigns_List_Widget extends WP_Widget {

	public function __construct() {

		parent::__construct('leyka_campaigns_list',  esc_html__('Leyka: Campaigns List', 'leyka'), [
            'classname'   => 'leyka_campaigns_list',
            'description' => esc_html__('List of recent campaigns with configurable attributes', 'leyka'),
        ]);

	}

	public function widget($args, $instance) {

		extract($args, EXTR_SKIP);

		$title = apply_filters('widget_title', $instance['title']);

		$q_args = [
			'post_type' => Leyka_Campaign_Management::$post_type,
			'posts_per_page' => empty($instance['limit']) ? 3 : (int)$instance['limit'],
			'post_status' => 'publish',
		];

		if( !empty($instance['include']) ) {
			$q_args['post__in'] = array_map('intval', explode(',', $instance['include']));
		}

		if( !empty($instance['exclude']) ) {
			$q_args['post__not_in'] = array_map('intval', explode(',', $instance['exclude']));
		}

		if( !empty($instance['exclude_finished']) ) {
			$q_args['meta_query'] = [
				[
					'key'     => 'is_finished',
					'value'   => 1,
					'compare' => '!=',
					'type' => 'NUMERIC',
				],
			];
		}

		$campaigns = get_posts(apply_filters('leyka_campaigns_list_widget_query_args', $q_args, $instance));
		if( !$campaigns ) {
			return;
		}

		$args = [
			'show_title' => !empty($instance['show_title']),
			'show_thumb' => !empty($instance['show_thumb']),
			'show_excerpt' => !empty($instance['show_excerpt']),
			'show_scale' => !empty($instance['show_scale']),
			'show_button' => !empty($instance['show_button']),
			'exclude_finished' => !empty($instance['exclude_finished']),
		];

		/** @var $before_widget */
		/** @var $before_title */
		/** @var $after_widget */
		/** @var $after_title */
		echo wp_kses_post( $before_widget );
		if($title) {
			echo wp_kses_post( $before_title.$title.$after_title );
		}

		$css_id = 'leyka_campaign_list_widget-'.uniqid();
		echo "<div id='".esc_attr($css_id)."' class='leyka-campaigns-list'>";

		add_filter('leyka_campaign_card_thumbnail_size', [$this, '_campaign_thumb_size']);
		add_filter('leyka_campaign_card_class', [$this, '_campaign_css']);

		foreach($campaigns as $campaign) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo leyka_get_campaign_card($campaign->ID, $args);
		}
		remove_filter('leyka_campaign_card_thumbnail_size', [$this, '_campaign_thumb_size']);
		remove_filter('leyka_campaign_card_class', [$this, '_campaign_css']);

		echo "</div>" . wp_kses_post( $after_widget );

	}

	public function _campaign_thumb_size($size){
		return 'thumbnail';
	}

	public function _campaign_css($css) {
		return 'leyka-campaign-list-item';
	}

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

		$instance['exclude_finished'] = !empty($new_instance['exclude_finished']);

		return $instance;

	}

	public function form($instance) {

		$defaults = [
			'title' => '',
			'limit' => 3,
			'show_title' => true,
			'show_thumb' => true,
			'show_excerpt' => true,
			'show_scale' => true,
			'show_button' => true,
			'include' => '',
			'exclude' => '',
			'exclude_finished' => false,
		];

		$instance = wp_parse_args((array)$instance, $defaults);

		$limit = (int)$instance['limit'];

		$show_title = (int)$instance['show_title'];
		$show_thumb = (int)$instance['show_thumb'];
		$show_excerpt = !empty($instance['show_excerpt']);
		$show_scale = !empty($instance['show_scale']);
		$show_button = !empty($instance['show_button']);

		$exclude_finished = !empty($instance['exclude_finished']);?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title', 'leyka');?>:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($instance['title']);?>" />
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit'));?>"><?php esc_html_e('Number', 'leyka');?>:</label>
            <select class="widefat" name="<?php echo esc_attr( $this->get_field_name('limit') );?>" id="<?php echo esc_attr( $this->get_field_id('limit') );?>">
				<?php for($i=1; $i<=10; $i++) { ?>
                    <option <?php selected($limit, $i);?> value="<?php echo esc_attr( $i );?>"><?php echo esc_html( $i );?></option>
				<?php }?>
            </select>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('show_title'));?>">
                <input id="<?php echo esc_attr( $this->get_field_id('show_title') );?>" name="<?php echo esc_attr( $this->get_field_name('show_title') ); ?>" value="1" type="checkbox" <?php checked($show_title, 1);?>>
				<?php esc_html_e('Show title', 'leyka');?></label>
            <br>
            <label for="<?php echo esc_attr($this->get_field_id('show_thumb'));?>">
                <input id="<?php echo esc_attr( $this->get_field_id('show_thumb') );?>" name="<?php echo esc_attr( $this->get_field_name('show_thumb') ); ?>" value="1" type="checkbox" <?php checked($show_thumb, 1);?>>
				<?php esc_html_e('Show thumbnail', 'leyka');?></label>
            <br>
            <label for="<?php echo esc_attr($this->get_field_id('show_excerpt')); ?>">
                <input id="<?php echo esc_attr( $this->get_field_id('show_excerpt') );?>" name="<?php echo esc_attr( $this->get_field_name('show_excerpt') ); ?>" value="1" type="checkbox" <?php checked($show_excerpt, 1);?>>
				<?php esc_html_e('Show excerpt', 'leyka');?></label>
            <br>
            <label for="<?php echo esc_attr($this->get_field_id('show_scale')); ?>">
                <input id="<?php echo esc_attr( $this->get_field_id('show_scale') );?>" name="<?php echo esc_attr( $this->get_field_name('show_scale') ); ?>" value="1" type="checkbox" <?php checked($show_scale, 1);?>>
				<?php esc_html_e('Show scale', 'leyka');?></label>
            <br>
            <label for="<?php echo esc_attr($this->get_field_id('show_button')); ?>">
                <input id="<?php echo esc_attr( $this->get_field_id('show_button') );?>" name="<?php echo esc_attr( $this->get_field_name('show_button') ); ?>" value="1" type="checkbox" <?php checked($show_button, 1);?>>
				<?php esc_html_e('Show «support» button', 'leyka');?></label>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('include'));?>"><?php esc_html_e('Include campaigns', 'leyka');?>:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('include'));?>" name="<?php echo esc_attr($this->get_field_name('include'));?>" type="text" value="<?php echo esc_attr($instance['include']);?>">
            <small class="help"><?php esc_html_e('Comma-separated list of IDs', 'leyka');?></small>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('exclude'));?>"><?php esc_html_e('Exclude campaigns', 'leyka');?>:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('exclude'));?>" name="<?php echo esc_attr($this->get_field_name('exclude'));?>" type="text" value="<?php echo esc_attr($instance['exclude']);?>">
            <small class="help"><?php esc_html_e('Comma-separated list of IDs', 'leyka');?></small>
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('exclude_finished'));?>">
                <input id="<?php echo esc_attr( $this->get_field_id('exclude_finished') );?>" name="<?php echo esc_attr( $this->get_field_name('exclude_finished') );?>" value="1" type="checkbox" <?php checked($exclude_finished, 1);?>>
				<?php esc_html_e('Exclude finished campaigns', 'leyka');?>
            </label>
        </p>

	<?php }

}

class Leyka_Donations_List_Widget extends WP_Widget {

	public function __construct() {

		parent::__construct('leyka_donations_list',  esc_html__('Leyka: Donations List', 'leyka'), [
            'classname'   => 'leyka_donations_list',
            'description' => esc_html__('Recent donations list, optionally filtered by campaign', 'leyka')
        ]);

	}

	public function widget($args, $instance) {

		extract($args, EXTR_SKIP);

		$title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);

		$campaign_id = empty($instance['campaign_id']) ? 'all' : (int)$instance['campaign_id'];

		$html = leyka_get_donors_list($campaign_id, [
            'num' => empty($instance['limit']) ? 5 : (int)$instance['limit'],
            'show_purpose' => !empty($instance['show_purpose']),
            'show_name' => !empty($instance['show_name']),
            'show_date' => !empty($instance['show_date']),
        ]);
		if( !$html ) {
			return;
		}

		/**
		 * @var $before_widget
		 * @var $before_title
		 * @var $after_title
		 * @var $after_widget
		 */
		echo wp_kses_post( $before_widget.($title ? $before_title.$title.$after_title : '').$html.$after_widget );

	}

	public function update($new_instance, $old_instance) {

		$instance = $old_instance;

		$instance['title'] = sanitize_text_field($new_instance['title']);
		$instance['limit'] = empty($new_instance['limit']) ? 5 : (int)$new_instance['limit'];
		$instance['campaign_id'] = sanitize_text_field($new_instance['campaign_id']);
		$instance['show_purpose'] = !empty($new_instance['show_purpose']);
		$instance['show_name'] = !empty($new_instance['show_name']);
		$instance['show_date'] = !empty($new_instance['show_date']);

		return $instance;

	}

	public function form($instance) {

		$defaults = [
			'title' => '',
			'limit' => 5,
			'campaign_id' => '',
			'show_purpose' => 1,
			'show_name' => 1,
			'show_date' => 1,
		];

		$instance = wp_parse_args((array)$instance, $defaults);?>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title', 'leyka');?>:</label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title'));?>" name="<?php echo esc_attr($this->get_field_name('title'));?>" type="text" value="<?php echo esc_attr($instance['title']);?>">
        </p>

        <p>
            <label for="<?php echo esc_attr($this->get_field_id('limit'));?>"><?php esc_html_e('Donations number', 'leyka');?>:</label>
            <select class="widefat" name="<?php echo esc_attr( $this->get_field_name('limit') );?>" id="<?php echo esc_attr( $this->get_field_id('limit') );?>">
				<?php for($i = 5; $i <= 25; $i += 5) {?>
                    <option <?php selected((int)$instance['limit'], $i) ?> value="<?php echo esc_attr($i); ?>"><?php echo esc_html( $i );?></option>
				<?php }?>
            </select>
        </p>

        <p>

            <label for="<?php echo esc_attr($this->get_field_id('campaign_id'));?>">
                <?php esc_html_e('Campaign ID', 'leyka');?>:
            </label>
            <input id="<?php echo esc_attr($this->get_field_id('campaign_id'));?>" name="<?php echo esc_attr($this->get_field_name('campaign_id'));?>" value="<?php echo esc_attr($instance['campaign_id']);?>" type="text"><br>

            <small class="help">
                <?php esc_html_e('Copy-paste ID of the campaign to filter donations in the list, state "0" to detect it from context or leave the field blank to display recent entries', 'leyka');?>
            </small>

        </p>

        <h4><?php esc_html_e('Donation item settings', 'leyka');?></h4>

        <p>

            <label for="<?php echo esc_attr($this->get_field_id('show_purpose'));?>">
                <input id="<?php echo esc_attr($this->get_field_id('show_purpose'));?>" name="<?php echo esc_attr($this->get_field_name('show_purpose'));?>" value="1" type="checkbox" <?php checked( !!$instance['show_purpose'], 1 );?>>
				<?php esc_html_e('Show donation purpose', 'leyka');?>
            </label>
            <br>
            <label for="<?php echo esc_attr($this->get_field_id('show_name'));?>">
                <input id="<?php echo esc_attr($this->get_field_id('show_name'));?>" name="<?php echo esc_attr($this->get_field_name('show_name'));?>" value="1" type="checkbox" <?php checked( !!$instance['show_name'], 1 );?>>
				<?php esc_html_e("Show donor's name", 'leyka');?>
            </label>
            <br>
            <label for="<?php echo esc_attr($this->get_field_id('show_date'));?>">
                <input id="<?php echo esc_attr($this->get_field_id('show_date'));?>" name="<?php echo esc_attr($this->get_field_name('show_date'));?>" value="1" type="checkbox" <?php checked( !!$instance['show_date'], 1 );?>>
				<?php esc_html_e('Show donation date', 'leyka');?>
            </label>

        </p>

	<?php }

}