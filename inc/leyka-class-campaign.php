<?php
/**
 * Leyka Donation Campaign Functionality
 **/

class Leyka_Campaign_Management {
		
	private static $_instance = null;

	public $post_type = 'leyka_campaign';
	
	private function __construct() {

		add_action('leyka_campaign_metaboxes', array($this, 'set_metaboxes'));	
		add_filter('manage_'.$this->post_type.'_posts_columns', array($this, 'manage_columns_names'));
		add_action('manage_'.$this->post_type.'_posts_custom_column', array($this, 'manage_columns_content'), 2, 2);
		add_action('save_post', array($this, 'save_data'), 2, 2);
	}
	
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if(self::$_instance === null)
			self::$_instance = new self;

		return self::$_instance;
	}

	/** Metaboxes */
	function set_metaboxes(){

		$post_type = $this->post_type;	

		add_meta_box($post_type.'_data', __('Campaign settings', 'leyka'), array($this, 'data_meta_box'), $post_type, 'normal', 'high');
	}
	
	function data_meta_box($post) {
		
		$campaign = new Leyka_Campaign($post);
		
		$cur_template = $campaign->template;
		if(empty($cur_template))
			$cur_template = 'default';
		
		$templates = leyka()->get_templates();
        
        $payment_title = $campaign->payment_title;?>

        <!-- Campaign target commented out for next release -->
<!--		<fieldset id="target-amount"  class="metabox-field campaign-field">-->
<!--			<label for="campaign_target">--><?php //_e('Target amount (in main currency chosen in Settings)', 'leyka');?><!--</label><br>-->
<!--			<input type="text" name="campaign_target" id="campaign_target" value="--><?php //echo (int)$campaign->get_meta('campaign_target');?><!--">			-->
<!--		</fieldset>-->
        <!-- Campaign target comment end -->

        <fieldset id="payment-title"  class="metabox-field campaign-field">
            <label for="payment_title">
                <?php _e('Campaign title meant for payment system', 'leyka');?>
                <br />
                <small><?php echo __('If empty, main campaign title will be used', 'leyka');?></small>
            </label>
            
            <input type="text" class="widefat" name="payment_title" id="payment_title" value="<?php echo $payment_title ? $payment_title : $campaign->title;?>">
        </fieldset>
		
		<fieldset id="campaign-template"  class="metabox-field campaign-field">
			<label for="campaign_template"><?php _e('Template', 'leyka');?></label>
			<select name="campaign_template">
				<option value="default" <?php selected($cur_template, 'default');?>>
                    <?php _e('Default template', 'leyka');?>
                </option>
			<?php
				if($templates) {
                    foreach($templates as $template) {?>
					<option value="<?php echo esc_attr($template['id']);?>" <?php selected($cur_template, $template['id']);?>>
                        <?php echo esc_attr($template['name']);?>
                    </option>
			        <?php }
                }?>
			</select>
		</fieldset>

        <fieldset id="campaign-finished"  class="metabox-field campaign-field">
            <label for="is_finished">
                <input type="checkbox" id="is_finished" name="is_finished" value="1" <?php echo $campaign->is_finished ? 'checked' : '';?> /> <?php _e('Campaign is finished, all donations recieving stopped.', 'leyka');?>
            </label>
        </fieldset>
	<?php 
	}
	
	function save_data($post_id, WP_Post $post) {

		$campaign = new Leyka_Campaign($post);
		$campaign->save();
	}

	/** Table Columns */
	function manage_columns_names($columns){
		
		$unsort = $columns;
		$columns = array();
		
		if(isset($unsort['cb'])){
			$columns['cb'] = $unsort['cb'];
			unset($unsort['cb']);
		}

		$columns['ID'] = 'ID';

		if(isset($unsort['title'])) {
			$columns['title'] = $unsort['title'];
			unset($unsort['title']);
		}

        /* Campaign target commented out for next release */
//		$columns['target'] = __('Target', 'leyka');

		$columns['payment_title'] = __('Title meant for payment system', 'leyka');
		//$columns['total'] = __('Total', 'leyka');
	
		if(isset($unsort['date'])){
			$columns['date'] = $unsort['date'];
			unset($unsort['date']);
		}

		if(!empty($unsort))
			$columns = array_merge($columns, $unsort);
			
				
		return $columns;
	}
	
	function manage_columns_content($column_name, $post_id){
		
		$campaign = new Leyka_Campaign(get_post($post_id));
		if($column_name == 'ID')
			echo (int)$campaign->id;
        elseif($column_name == 'payment_title')
            echo $campaign->payment_title;
        /** Campaign target commented out for next release */
//		elseif($column_name == 'target') {
//			
//			$target = intval($campaign->get_meta('purpose_target'));			
//			echo $target;
//		}
	}
}


class Leyka_Campaign {
	
	private $_id;
	private $_post_object;

	function __construct($campaign) {
		
		if(is_object($campaign)) {
			$this->_id = $campaign->ID;
            $this->_post_object = $campaign;
		} elseif((int)$campaign > 0) {
			$this->_id = (int)$campaign;
            $this->_post_object = get_post($this->_id);
		}
        
        if( !$this->_post_object ) {
            $this->_id = 0;
            // throw new Leyka_Exception() 
        }
	}

    public function __get($field) {

        switch($field) {
            case 'id':
            case 'ID': return $this->_id;
            case 'title':
            case 'name': return $this->_post_object->post_title;
            case 'payment_title':
                $p_title = get_post_meta($this->_id, 'payment_title', true);
                return $p_title ? $p_title : $this->_post_object->post_title;
            case 'template': return get_post_meta($this->_id, 'campaign_template', true);
            case 'description': return $this->_post_object->post_content;
            case 'status': return $this->_post_object->post_status;
            case 'permalink':
            case 'url': return get_permalink($this->_id);
			case 'is_finished':
				return (int)get_post_meta($this->_id, 'is_finished', true) > 0;
//            case '': return ''; break;
            default:
                return null;
        }
    }
	
	/** CRUD */
	function save() {

		$meta = $this->get_default_meta();

		if( !empty($_REQUEST['campaign_template']) )
			$meta['campaign_template'] = trim($_REQUEST['campaign_template']);

        if( !empty($_REQUEST['payment_title']) )
			$meta['payment_title'] = esc_attr(trim($_REQUEST['payment_title']));

        $meta['is_finished'] = empty($_REQUEST['is_finished']) ? 0 : 1;

        /** Campaing target is commented out for the next release */
//		if(isset($_REQUEST['campaign_target']) && !empty($_REQUEST['campaign_target']))
//			$meta['campaign_target'] = intval($_REQUEST['campaign_target']);

		foreach($meta as $key => $value) {
			update_post_meta($this->_id, $key, $value);
		}
	}

	function get_default_meta() {
		return array(
//			'campaign_target' => 0, /** Campaing target is commented out for the next release */
            'payment_title' => '',
			'campaign_template' => 'default'
		);
	}

//	function get_meta($key) {
//		return get_post_meta($this->_id, $key, true);
//	}
	
	
	
}//class end



