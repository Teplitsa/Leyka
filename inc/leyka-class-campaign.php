<?php if( !defined('WPINC') ) die;

/**
 * Leyka Donation Campaign Functionality
 **/

class Leyka_Campaign_Management {

	private static $_instance = null;

	public static $post_type = 'leyka_campaign';
	
	private function __construct() {

		add_action('add_meta_boxes', array($this, 'set_metaboxes'));
		add_filter('manage_'.self::$post_type.'_posts_columns', array($this, 'manage_columns_names'));
		add_action('manage_'.self::$post_type.'_posts_custom_column', array($this, 'manage_columns_content'), 2, 2);
		add_action('save_post', array($this, 'save_data'), 2, 2);

        add_action('restrict_manage_posts', array($this, 'manage_filters'));
        add_action('pre_get_posts', array($this, 'do_filtering'));

		add_filter('post_row_actions', array($this, 'row_actions'), 10, 2);

	}
	
	public static function get_instance() {

		if( !self::$_instance ) {
			self::$_instance = new self;
        }

		return self::$_instance;
	}

    public function set_admin_messages($messages) {

        $current_post = get_post();

        $messages[Leyka_Campaign_Management::$post_type] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf(
                __('Campaign updated. <a href="%s">View it</a>', 'leyka'),
                esc_url(home_url('?p='.$current_post->ID))
            ),
            2 => __('Field updated.', 'leyka'),
            3 => __('Field deleted.', 'leyka'),
            4 => __('Campaign updated.', 'leyka'),
            /* translators: %s: date and time of the revision */
            5 => isset($_GET['revision']) ? sprintf(__('Campaign restored to revision from %s', 'leyka'), wp_post_revision_title((int)$_GET['revision'], false)) : false,
            6 => sprintf(
                __('Campaign published. <a href="%s">View it</a>', 'leyka'),
                esc_url(home_url('?p='.$current_post->ID))
            ),
            7 => __('Campaign saved.', 'leyka'),
            8 => sprintf(
                __('Campaign submitted. <a target="_blank" href="%s">Preview it</a>', 'leyka'),
                esc_url(add_query_arg('preview', 'true', home_url('?p='.$current_post->ID)))
            ),
            9 => sprintf(
                __('Campaign scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview it</a>', 'leyka'),
                // translators: Publish box date format, see http://php.net/date
                date_i18n(__( 'M j, Y @ G:i'), strtotime($current_post->post_date)),
                esc_url(home_url('?p='.$current_post->ID))
            ),
            10 => sprintf(
                __('Campaign draft updated. <a target="_blank" href="%s">Preview it</a>', 'leyka'),
                esc_url(add_query_arg('preview', 'true', home_url('?p='.$current_post->ID)))
            ),
        );

        return $messages;
    }

    public function row_actions($actions, $campaign) {

        $current_screen = get_current_screen();

        if( !$current_screen || !is_object($current_screen) || $current_screen->post_type != self::$post_type ) {
            return $actions;
        }

        unset($actions['inline hide-if-no-js']);
        return $actions;

    }

    public function manage_filters() {

        if(get_current_screen()->id == 'edit-'.self::$post_type && current_user_can('leyka_manage_donations')) {?>

            <label for="campaign-state-select"></label>
            <select id="campaign-state-select" name="campaign_state">
                <option value="all" <?php echo empty($_GET['campaign_state']) ? 'selected="selected"' : '';?>>
                    - <?php _e('Collection state', 'leyka');?> -
                </option>
                <option value="is_finished" <?php echo !empty($_GET['campaign_state']) && $_GET['campaign_state'] == 'is_finished' ? 'selected="selected"' : '';?>><?php _e('Closed', 'leyka');?></option>
                <option value="is_open" <?php echo !empty($_GET['campaign_state']) && $_GET['campaign_state'] == 'is_open' ? 'selected="selected"' : '';?>><?php _e('Opened', 'leyka');?></option>

            </select>

            <label for="target-state-select"></label>
            <select id="target-state-select" name="target_state">
                <option value="" <?php echo empty($_GET['target_state']) ? 'selected="selected"' : '';?>>
                    - <?php _e('Target', 'leyka');?> -
                </option>

                <?php foreach(leyka()->get_campaign_target_states() as $state => $label) {?>
                <option value="<?php echo $state;?>" <?php echo !empty($_GET['target_state']) && $_GET['target_state'] == $state ? 'selected="selected"' : '';?>>
                    <?php echo $label;?>
                </option>
                <?php }?>
            </select>
    <?php }
    }

    public function do_filtering(WP_Query $query) {

        if(is_admin() && $query->is_main_query() && get_current_screen()->id == 'edit-'.self::$post_type) {

            $meta_query = array('relation' => 'AND');

            if( !empty($_REQUEST['campaign_state']) && $_REQUEST['campaign_state'] !== 'all' ) {
                $meta_query[] = array('key' => 'is_finished', 'value' => $_REQUEST['campaign_state'] == 'is_finished' ? 1 : 0);
            }

            if( !empty($_REQUEST['target_state']) )
                $meta_query[] = array('key' => 'target_state', 'value' => $_REQUEST['target_state']);

            //...

            if(count($meta_query) > 1) {
                $query->set('meta_query', $meta_query);
            }
        }
    }

	/** Metaboxes: */
	public function set_metaboxes() {

        add_meta_box(
            self::$post_type.'_excerpt', __('Annotation', 'leyka'),
            array($this, 'annotation_meta_box'), self::$post_type, 'normal', 'high'
        );

        add_meta_box(self::$post_type.'_data', __('Campaign settings', 'leyka'), array($this, 'data_meta_box'), self::$post_type, 'normal', 'high');

        // Metaboxes are only for campaign editing page:
        $screen = get_current_screen();
        if($screen->post_type == self::$post_type && $screen->base == 'post' && !$screen->action) {

            add_meta_box(
                self::$post_type.'_embed', __('Campaign embedding', 'leyka'),
                array($this, 'embedding_meta_box'), self::$post_type, 'normal', 'high'
            );

		    add_meta_box(
                self::$post_type.'_donations', __('Donations history', 'leyka'),
                array($this, 'donations_meta_box'), self::$post_type, 'normal', 'high'
            );

            add_meta_box(
                self::$post_type.'_statistics', __('Campaign statistics', 'leyka'),
                array($this, 'statistics_meta_box'), self::$post_type, 'side', 'low'
            );
        }
	}

    public function data_meta_box($post) {

		$campaign = new Leyka_Campaign($post);

		$cur_template = $campaign->template ? $campaign->template : 'default';?>

        <fieldset id="payment-title" class="metabox-field campaign-field campaign-purpose">
            <label for="payment_title">
                <?php _e('Campaign title meant for payment system', 'leyka');?>
                <br>
                <small><?php echo __('If empty, main campaign title will be used', 'leyka');?></small>
            </label>

            <input type="text" class="widefat" name="payment_title" id="payment_title" value="<?php echo $campaign->payment_title ? $campaign->payment_title : $campaign->title;?>">
        </fieldset>

		<h4 class="metabox-field-title campaign-template"><?php _e('Template settings', 'leyka');?></h4>

		<fieldset id="campaign-template" class="metabox-field campaign-field campaign-template">
			<label for="campaign_template"><?php _e('Template for payment form', 'leyka');?></label>
			<select id="campaign_template" name="campaign_template">
				<option value="default" <?php selected($cur_template, 'default');?>>
                    <?php _e('Default template', 'leyka');?>
                </option>

            <?php $templates = leyka()->get_templates(); 
            if($templates) {
                foreach($templates as $template) {?>
                <option value="<?php echo esc_attr($template['id']);?>" <?php selected($cur_template, $template['id']);?>>
                    <?php _e(esc_attr($template['name']), 'leyka');?>
                </option>
            <?php }
            }?>

			</select>
		</fieldset>

		<fieldset id="ignore-global-template" class="metabox-field campaign-field campaign-ignorance">
			<label for="ignore_global_template">
			<input type="checkbox" name="ignore_global_template" id="ignore_global_template" value="1" <?php checked($campaign->ignore_global_template_settings, 1);?>>&nbsp;
			<?php _e('Ignore global template settings', 'leyka');?></label>
		</fieldset>

		<h4 class="metabox-field-title campaign-target"><?php _e('Campaign target', 'leyka');?></h4>

		<fieldset id="target-amount" class="metabox-field campaign-field campaign-target">
			<label for="campaign_target">
                <?php echo sprintf(__('Target (%s)', 'leyka'), leyka_options()->opt('currency_rur_label'));?>
            </label>
			<input type="text" name="campaign_target" id="campaign_target" value="<?php echo $campaign->target;?>" class="widefat">
		</fieldset>
		
		<fieldset id="collected-amount" class="metabox-field campaign-field campaign-target-collected">
			<label for="collected_target">
                <?php echo sprintf(__('Collected (%s)', 'leyka'), leyka_get_currency_label('rur'));?>
            </label>			
			<input type="text" id="collected_target" disabled="disabled" value="<?php echo $campaign->total_funded;?>" class="widefat">
            <?php if(get_current_screen()->action != 'add') {?>
            <div class="recalculate-total-funded">
                <a href="<?php echo add_query_arg(array('recalculate_total_funded' => 1,));?>" id="recalculate_total_funded" data-nonce="<?php echo wp_create_nonce('leyka_recalculate_total_funded_amount');?>" data-campaign-id="<?php echo $campaign->id;?>"><?php _e('Recalculate the collected amount', 'leyka');?></a>
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL.'/img/ajax-loader-h.gif';?>" id="recalculate_total_funded_loader" style="display: none;">
                <div class="message error-message" id="recalculate_message"></div>
            </div>
            <?php }?>
		</fieldset>

		<fieldset id="d-scale-demo" class="metabox-field campaign-field campaign-target-scale">
		<?php if($campaign->target > 0) {

			$percentage = round(100*$campaign->total_funded/$campaign->target);
            $percentage = $percentage > 100 ? 100 : $percentage;?>

			<div class="d-scale-scale">
				<div class="target">
					<div style="width:<?php echo $percentage;?>%" class="collected">&nbsp;</div>
				</div>
			</div>

			<?php if($campaign->target_state === 'is_reached') {?>

			<p><?php printf(__('Reached at: %s', 'leyka'), '<b>'.$campaign->date_target_reached.'</b>');?></p>

            <?php if(leyka_options()->opt('send_donor_emails_on_campaign_target_reaching')) {?>
            <p><?php echo __('Donors mailout:', 'leyka');?>
                <b><?php echo $campaign->target_reaching_mailout_sent ?
                    __('sent', 'leyka').' ('.($campaign->target_reaching_mailout_errors ? __('errors detected', 'leyka') : __('mailing succeeded', 'leyka')).')' :
                    __('not sent', 'leyka');?></b>
            </p>

			<?php }

            }?>

		<?php }?>
		</fieldset>

        <?php $curr_page = get_current_screen();
        if($curr_page->action !== 'add') {?>

        <fieldset id="campaign-finished" class="metabox-field campaign-field campaign-finished">
            <label for="is-finished">
                <input type="checkbox" id="is-finished" name="is_finished" value="1" <?php echo $campaign->is_finished ? 'checked' : '';?>> <?php _e('Donations collection stopped', 'leyka');?>
            </label>
        </fieldset>
	    <?php }

    }

    public function statistics_meta_box(WP_Post $campaign) { $campaign = new Leyka_Campaign($campaign);?>

        <div class="stats-block">
            <span class="stats-label"><?php _e('Views:', 'leyka');?></span>
            <span class="stats-data"><?php echo $campaign->views_count;?> <?php _e('times', 'leyka');?></span>
        </div>
        <div class="stats-block">
            <span class="stats-label"><?php _e('Donation attempts:', 'leyka');?></span>
            <span class="stats-data"><?php echo $campaign->submits_count;?> <?php _e('times', 'leyka');?></span>
        </div>

    <?php
    }

    public function annotation_meta_box(WP_Post $campaign) {?>

        <label for="excerpt"></label>
        <textarea id="excerpt" name="excerpt" cols="40" rows="1"><?php echo $campaign->post_excerpt;?></textarea>
        <p><?php _e('Annotation is an optional summary of campaign description that can be used in templates.', 'leyka');?></p>

    <?php }

    public function donations_meta_box(WP_Post $campaign) { $campaign = new Leyka_Campaign($campaign);?>

        <div>
            <a class="button" href="<?php echo admin_url('/post-new.php?post_type=leyka_donation&campaign_id='.$campaign->id);?>"><?php _e('Add correctional donation', 'leyka');?></a>
        </div>

        <table id="donations-data-table">
            <thead>
                <td><?php _e('ID', 'leyka');?></td>
                <td><?php _e('Amount', 'leyka');?></td>
                <td><?php _e('Donor', 'leyka');?></td>
                <td><?php _e('Method', 'leyka');?></td>
                <td><?php _e('Date', 'leyka');?></td>
                <td><?php _e('Status', 'leyka');?></td>
                <td><?php _e('Payment type', 'leyka');?></td>
                <td><?php _e('Actions', 'leyka');?></td>
            </thead>
            <tfoot>
                <td><?php _e('ID', 'leyka');?></td>
                <td><?php _e('Amount', 'leyka');?></td>
                <td><?php _e('Donor', 'leyka');?></td>
                <td><?php _e('Method', 'leyka');?></td>
                <td><?php _e('Date', 'leyka');?></td>
                <td><?php _e('Status', 'leyka');?></td>
                <td><?php _e('Payment type', 'leyka');?></td>
                <td><?php _e('Actions', 'leyka');?></td>
            </tfoot>

            <tbody>
            <?php foreach($campaign->get_donations(array('submitted', 'funded', 'refunded', 'failed')) as $donation) {

                $gateway_label = $donation->gateway_id ? $donation->gateway_label : __('Custom payment info', 'leyka');
                $pm_label = $donation->gateway_id ? $donation->pm_label : $donation->pm;
				$amount_css = $donation->sum < 0 ? 'amount-negative' : 'amount';?>

                <tr <?php echo $donation->type == 'correction' ? 'class="leyka-donation-row-correction"' : '';?>>
                    <td><?php echo $donation->id;?></td>
                    <td><?php echo '<span class="'.$amount_css.'">'.$donation->sum.'&nbsp;'.$donation->currency_label.'</span>';?></td>
                    <td><?php echo $donation->donor_name ? $donation->donor_name : __('Anonymous', 'leyka');?></td>
                    <td><?php echo $pm_label.' ('.mb_strtolower($gateway_label).')';?></td>
                    <td><?php echo $donation->date;?></td>
                    <td><?php echo '<i class="'.esc_attr($donation->status).'">'.mb_ucfirst($donation->status_label).'</i>';?></td>
                    <td><?php echo mb_ucfirst($donation->payment_type_label);?></td>
                    <td><a href="<?php echo admin_url("/post.php?post={$donation->id}&action=edit");?>"><?php echo __('Edit', 'leyka');?></a></td>
                </tr>

            <?php }?>
            </tbody>
        </table>
    <?php
    }

    public function embedding_meta_box(WP_Post $campaign) {?>

	<div class="embed-block">

        <h3><?php _e('On this website content', 'leyka');?></h3>
        <div id="embed-size-pane" class="setting-row">
            <label for="campaign-shortcode"><?php _e("To embed a campaign donation form in any of your website materials, insert the following code in page HTML:", 'leyka');?></label>
            <br>
            <input type="text" class="embed-code read-only campaign-shortcode" id="campaign-shortcode" value="<?php echo esc_attr(self::get_campaign_form_shortcode($campaign->ID));?>">
        </div>

        <h3><?php _e('On the other websites pages', 'leyka');?></h3>
		<div class="embed-code-wrap">

            <div id="embed-size-pane" class="setting-row">
                <h4><?php _e('Size settings', 'leyka');?></h4>
				<label><?php _e('Width', 'leyka');?>: <input type="text" name="embed_iframe_w" id="embed_iframe_w" value="300" size="4"></label>
				<label><?php _e('Height', 'leyka');?>: <input type="text" name="embed_iframe_w" id="embed_iframe_h" value="400" size="4"></label>

                <div id="embed-campaign_card" class="settings-field">
                    <label for="campaign-embed-code"><?php _e("To embed a campaign card in some other web page, insert the following code in page HTML:", 'leyka');?></label>
                    <textarea class="embed-code read-only campaign-embed-code" id="campaign-embed-code"><?php echo self::get_card_embed_code($campaign->ID, true, 300, 400); ?></textarea>
                </div>
			</div>

		</div>

		<div class="leyka-embed-preview">
			<h4><?php _e('Preview', 'leyka');?></h4>
			<?php echo self::get_card_embed_code($campaign->ID, false); ?>
		</div>

	</div>
    <?php }

	static function get_card_embed_code($campaign_id, $increase_counters = false, $w = 300, $h = 400){

		$link = get_permalink($campaign_id);
        $link .= (stristr($link, '?') !== false ? '&' : '?').'embed_object=campaign_card';
        $link .= '&increase_counters='.(int)!!$increase_counters;

		return '<iframe width="'.(int)$w.'" height="'.(int)$h.'" src="'.$link.'"></iframe>';
	}

    static function get_campaign_form_shortcode($campaign_id) {

        // Right now, Revo template only works through [leyka_inline_campaign] shortcode,
        // and all other templates need [leyka_campaign_form] shortcode:

        $campaign = new Leyka_Campaign($campaign_id);

        /** @todo When [leyka_inline_campaign] could display any form template, change this. */
        return $campaign->template == 'revo' ?
            '[leyka_inline_campaign id="'.$campaign_id.'"]' : '[leyka_campaign_form id="'.$campaign_id.'"]';
    }

	public function save_data($campaign_id, WP_Post $campaign) {

		$campaign = new Leyka_Campaign($campaign);

        $meta = array();

        if( !empty($_REQUEST['campaign_template']) && $campaign->template != $_REQUEST['campaign_template'] ) {
            $meta['campaign_template'] = trim($_REQUEST['campaign_template']);
        }

        if( !empty($_REQUEST['payment_title']) && $campaign->payment_title != $_REQUEST['payment_title'] ) {

            $meta['payment_title'] = trim($_REQUEST['payment_title']);
            $meta['payment_title'] = esc_attr(htmlentities($meta['payment_title'], ENT_QUOTES, 'UTF-8'));
        }

        $_REQUEST['is_finished'] = !empty($_REQUEST['is_finished']) ? 1 : 0;
        if($_REQUEST['is_finished'] != $campaign->is_finished) {
            $meta['is_finished'] = $_REQUEST['is_finished'];
        }

        $_REQUEST['ignore_global_template'] = !empty($_REQUEST['ignore_global_template']) ? 1 : 0;
        if($_REQUEST['ignore_global_template'] != $campaign->ignore_global_template_settings) {
            $meta['ignore_global_template'] = $_REQUEST['ignore_global_template'];
        }

        if(isset($_REQUEST['campaign_target']) && $_REQUEST['campaign_target'] != $campaign->target) {

            $_REQUEST['campaign_target'] = (float)$_REQUEST['campaign_target'];
            $_REQUEST['campaign_target'] = $_REQUEST['campaign_target'] >= 0.0 ? $_REQUEST['campaign_target'] : 0.0;

            update_post_meta($campaign->id, 'campaign_target', $_REQUEST['campaign_target']);

            $campaign->refresh_target_state();

        }

        foreach($meta as $key => $value) {
            update_post_meta($campaign->id, $key, $value);
        }
	}

	/** Campaigns list table columns: */
    public function manage_columns_names($columns){

		$unsort = $columns;
		$columns = array();

		if( !empty($unsort['cb']) ) {

			$columns['cb'] = $unsort['cb'];
			unset($unsort['cb']);

		}

		$columns['ID'] = 'ID';

		if(isset($unsort['title'])) {

			$columns['title'] = $unsort['title'];
			unset($unsort['title']);

		}

		$columns['coll_state'] = __('Collection state', 'leyka');
		$columns['target'] = __('Progress', 'leyka');
		$columns['payment_title'] = __('Payment purpose', 'leyka');
        $columns['shortcode'] = __('Campaign shortcode', 'leyka');

		if(isset($unsort['date'])) {

			$columns['date'] = $unsort['date'];
			unset($unsort['date']);

		}

		if($unsort) {
			$columns = array_merge($columns, $unsort);
        }

		return $columns;
	}

    public function manage_columns_content($column_name, $campaign_id){

		$campaign = new Leyka_Campaign($campaign_id);

		if($column_name === 'ID') {
			echo (int)$campaign->id;
		} elseif($column_name === 'payment_title') {
            echo $campaign->payment_title;
        } elseif($column_name === 'coll_state') {

			echo $campaign->is_finished == 1 ?
				'<span class="c-closed">'.__('Closed', 'leyka').'</span>' :
				'<span class="c-opened">'.__('Opened', 'leyka').'</span>';

		} elseif($column_name == 'target') {

			if($campaign->target_state == 'no_target') {
				leyka_fake_scale_ultra($campaign);			
			} else {
				leyka_scale_ultra($campaign);
			}

			if($campaign->target_state === 'is_reached' && $campaign->date_target_reached) {?>
		    <span class='c-reached'><?php printf(__('Reached at: %s', 'leyka'), '<time>'.$campaign->date_target_reached.'</time>'); ?></span>
		<?php }

		} elseif($column_name === 'shortcode') {?>
            <input type="text" class="embed-code read-only campaign-shortcode" value="<?php echo esc_attr(self::get_campaign_form_shortcode($campaign->ID));?>">
        <?php }
	}

} // class


class Leyka_Campaign {

	private $_id;
	private $_post_object;
    private $_campaign_meta;

	public function __construct($campaign) {

		if(is_object($campaign)) {

            if(is_a($campaign, 'WP_Post')) {

                $this->_id = $campaign->ID;
                $this->_post_object = $campaign;

            } else if(is_a($campaign, 'Leyka_Campaign')) {
                return $campaign;
            }

		} elseif((int)$campaign > 0) {

			$this->_id = (int)$campaign;
            $this->_post_object = get_post($this->_id);

		}

        if( !$this->_post_object || $this->_post_object->post_type != Leyka_Campaign_Management::$post_type ) {
            $this->_id = 0; /** @todo throw new Leyka_Exception() */
        }

        if( !$this->_campaign_meta ) {

            $meta = get_post_meta($this->_id, '', true);

            if(empty($meta['target_state'])) {

                $this->target_state = $this->_get_calculated_target_state();
                $meta['target_state'] = array($this->target_state);

            }

            if(empty($meta['_leyka_target_reaching_mailout_sent'])) {

                $this->target_reaching_mailout_sent = false;
                $meta['_leyka_target_reaching_mailout_sent'] = array(false);

            }

            if(empty($meta['_leyka_target_reaching_mailout_errors'])) {

                $this->target_reaching_mailout_errors = false;
                $meta['_leyka_target_reaching_mailout_errors'] = array(false);

            }

            if( !isset($meta['is_finished']) ) {

                update_post_meta($this->_id, 'is_finished', 0);
                $meta['is_finished'] = array(0);

            }

            if( !isset($meta['total_funded']) ) { // If campaign total collected amount is not saved, save it

                $sum = 0.0;
                foreach($this->get_donations(array('funded')) as $donation) {

                    $donation_amount = $donation->main_curr_amount ? $donation->main_curr_amount : $donation->amount;
                    if(is_array($donation_amount) && !empty($donation_amount[0]) && (float)$donation_amount[0] >= 0.0) {
                        $donation_amount = $donation_amount[0];
                    }

                    $sum += $donation_amount;
                }

                update_post_meta($this->_id, 'total_funded', $sum);

                $meta['total_funded'][0] = $sum;
            }

            $this->_campaign_meta = array(
                'payment_title' => empty($meta['payment_title']) ?
                    (empty($this->_post_object) ? '' : $this->_post_object->post_title) : $meta['payment_title'][0],
                'campaign_template' => empty($meta['campaign_template']) ? '' :  $meta['campaign_template'][0],
                'campaign_target' => empty($meta['campaign_target']) ? 0 : $meta['campaign_target'][0],
                'ignore_global_template' => empty($meta['ignore_global_template']) ?
                    '' : $meta['ignore_global_template'][0] > 0,
                'is_finished' => $meta['is_finished'] ? $meta['is_finished'][0] > 0 : 0,
                'target_state' => $meta['target_state'][0],
                'target_reaching_mailout_sent' => $meta['_leyka_target_reaching_mailout_sent'][0],
                'target_reaching_mailout_errors' => $meta['_leyka_target_reaching_mailout_errors'][0],
                'date_target_reached' => empty($meta['date_target_reached']) ? 0 : $meta['date_target_reached'][0],
                'count_views' => empty($meta['count_views']) ? 0 : $meta['count_views'][0],
                'count_submits' => empty($meta['count_submits']) ? 0 : $meta['count_submits'][0],
                'total_funded' => empty($meta['total_funded']) ? 0.0 : $meta['total_funded'][0],
//                '' => '',
            );
        }
	}

    protected function _get_calculated_target_state() {

        $target = get_post_meta($this->_id, 'campaign_target', true);
        return empty($target) ?
            'no_target' :
            (Leyka_Campaign::get_campaign_collected_amount($this->_id) >= $target ? 'is_reached' : 'in_progress');
    }

    public function __get($field) {

        switch($field) {
            case 'id':
            case 'ID': return $this->_id;
            case 'title':
            case 'name': return $this->_post_object ? $this->_post_object->post_title : '';
            case 'payment_title': return $this->_campaign_meta['payment_title'];
            case 'template':
            case 'campaign_template':
                return $this->_campaign_meta['campaign_template'] === 'default' ?
                    leyka_options()->opt('donation_form_template') : $this->_campaign_meta['campaign_template'];
            case 'campaign_target':
            case 'target': return $this->_campaign_meta['campaign_target'];
            case 'description': return $this->_post_object ? $this->_post_object->post_content : '';
            case 'excerpt':
            case 'post_excerpt':
            case 'post_name': return $this->_post_object ? $this->_post_object->post_name : '';
            case 'short_description': return $this->_post_object ? $this->_post_object->post_excerpt : '';
            case 'status': return $this->_post_object ? $this->_post_object->post_status : '';
            case 'permalink':
            case 'url': return get_permalink($this->_id);
			case 'is_finished':
			case 'is_closed':
				return $this->_campaign_meta['is_finished'];
            case 'ignore_global_template':
            case 'ignore_global_template_settings':
				return $this->_campaign_meta['ignore_global_template'];
            case 'target_state':
                return $this->_campaign_meta['target_state'];
            case 'date_reached':
            case 'target_reached_date':
            case 'date_target_reached':
                $date = $this->_campaign_meta['date_target_reached'];
                return $date ? date(get_option('date_format'), $date) : 0;
            case 'target_reaching_mailout_sent': return !!$this->_campaign_meta['target_reaching_mailout_sent'];
            case 'target_reaching_mailout_errors': return !!$this->_campaign_meta['target_reaching_mailout_errors'];
            case 'views':
            case 'count_views':
            case 'views_count': return $this->_campaign_meta['count_views'];
            case 'submits':
            case 'count_submits':
            case 'submits_count': return $this->_campaign_meta['count_submits'];
            case 'total_funded':
            case 'total_collected':
            case 'total_donations_funded': return $this->_campaign_meta['total_funded'];
            case '': return '';
//            case '': return '';
            default:
                return apply_filters('leyka_get_unknown_campaign_field', null, $field, $this);
        }
    }

    public function __set($field, $value) {

        switch($field) {
            case 'target_state':
                if( in_array($value, array_keys(leyka()->get_campaign_target_states())) ) {

                    $this->_campaign_meta['target_state'] = $value;
                    update_post_meta($this->_id, 'target_state', $value);

                }
                break;
            case 'target_reaching_mailout_sent':
                $this->_campaign_meta['target_reaching_mailout_sent'] = !!$value;
                update_post_meta($this->_id, '_leyka_target_reaching_mailout_sent', !!$value);
                break;
            case 'target_reaching_mailout_errors':
                $this->_campaign_meta['target_reaching_mailout_errors'] = !!$value;
                update_post_meta($this->_id, '_leyka_target_reaching_mailout_errors', !!$value);
                break;
            default:
        }
    }

    /**
     * Get all donations of the campaign with given statuses.
     * NOTE: This method is to be called after init (1), or else it will return an empty array.
     *
     * @param $status array Of leyka donation statuses.
     * @return array Of Leyka_Donation objects.
     */
    public function get_donations(array $status = null) {

        if( !did_action('leyka_cpt_registered') ) { // Leyka PT statuses isn't there yet
            return array();
        }

        $donations = get_posts(array(
            'post_type' => Leyka_Donation_Management::$post_type,
            'post_status' => $status ? $status : array('submitted', 'funded', 'refunded', 'failed', 'trash',),
            'nopaging' => true,
            'meta_key' => 'leyka_campaign_id',
            'meta_value' => $this->_id,
        ));

        $count = count($donations);
        for($i = 0; $i < $count; $i++) {
            $donations[$i] = new Leyka_Donation($donations[$i]->ID);
        }

        return $donations;

    }

    public static function get_campaign_collected_amount($campaign_id) {

        $campaign_id = (int)$campaign_id;
        if($campaign_id <= 0) {
            return false;
        }

        $campaign = new Leyka_Campaign($campaign_id);

        return $campaign->total_funded > 0.0 ? $campaign->total_funded : 0.0;

    }

    /** @deprecated Use $campaign->total_funded instead. */
    public function get_collected_amount() {
        return $this->total_funded > 0.0 ? $this->total_funded : 0.0;
    }

    public function refresh_target_state() {

        if( !$this->target ) {
            return false;
        }

        $new_target_state = $this->_get_calculated_target_state();
        $meta = array();

        if($new_target_state !== $this->target_state) {

            $meta['target_state'] = $new_target_state;

            if($new_target_state === 'is_reached') {
                $meta['date_target_reached'] = time();
            }

        } elseif($new_target_state === 'is_reached' && !$this->date_target_reached) {
            $meta['date_target_reached'] = time();
        } elseif($new_target_state !== 'is_reached' && $this->date_target_reached) {
            $meta['date_target_reached'] = 0;
        }

        if( !isset($meta['target_state']) ) {
            $meta['target_state'] = $new_target_state;
        }

        foreach($meta as $key => $value) {
            update_post_meta($this->_id, $key, $value);
        }

        return $meta['target_state'];

    }
	
	static function get_target_state_label($state = false) {

        $labels = leyka()->get_campaign_target_states();

        if( !$state ) {
            return $labels;
        } else {
            return !empty($labels[$state]) ? $labels[$state] : false;
        }
	}

    public function increase_views_counter() {

        $this->_campaign_meta['count_views']++;
        update_post_meta($this->_id, 'count_views', $this->_campaign_meta['count_views']);
    }

    public function increase_submits_counter() {

        $this->_campaign_meta['count_submits']++;
        update_post_meta($this->_id, 'count_submits', $this->_campaign_meta['count_submits']);

    }

    public function update_total_funded_amount($donation = false, $action = '', $old_sum = false) {

        if( !$donation ) { // Recalculate total collected amount for a campaign and recache it

            $sum = 0.0;
            foreach($this->get_donations(array('funded')) as $donation) {
                $sum += $donation->sum_total;
            }

            $this->_campaign_meta['total_funded'] = $sum;
            update_post_meta($this->_id, 'total_funded', $this->_campaign_meta['total_funded']);

        } else { // Add/subtract a sum of a donation from it's campaign metadata

            $donation = leyka_get_validated_donation($donation);
            if( !$donation ) {
                return false;
            }

            if($action == 'remove') { // Subtract given donation's sum from campaign's total_funded
                $sum = -$donation->sum_total;
            } else { // Add given donation's sum to campaign's total_funded

                if($action == 'update_sum' && (int)$old_sum) { // If donation sum was changed, subtract it from total_funded first
                    $this->_campaign_meta['total_funded'] -= (int)$old_sum;
                }

                $sum = ($donation->status != 'funded' || $donation->campaign_id != $this->_id) && $donation->sum_total > 0 ?
                    -$donation->sum_total : $donation->sum_total;
                $sum = $donation->status == 'trash' ? -$sum : $sum;
            }

            $this->_campaign_meta['total_funded'] += $sum;

            update_post_meta($this->_id, 'total_funded', $this->_campaign_meta['total_funded']);
        }

        $this->refresh_target_state();

        return $this;

    }

    public function delete($force = False) {
        wp_delete_post( $this->_id, $force );
    }
    
}