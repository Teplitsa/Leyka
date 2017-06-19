<?php if( !defined('WPINC') ) die;
/**
 * Payment form
 **/

class Leyka_Payment_Form {

	protected $_pm_name;
	protected $_pm = null;
	protected static $_form_action;
	protected $_current_currency; // Current currency in the view

	public function __construct(Leyka_Payment_Method $payment_method = null, $current_currency = null) {

        if( !leyka()->form_is_screening ) {
            leyka()->form_is_screening = true;
        }

        $this->_pm = $payment_method ? $payment_method : false;
        $this->_pm_name = $payment_method ? $payment_method->id : false;

		$this->_current_currency = $current_currency;

	}

	public function __get($name) {
		switch($name) {
			case 'id': return $this->_pm ? $this->_pm->id : false;
			case 'full_id': return $this->_pm ? $this->_pm->full_id : false;
			case 'label': return $this->_pm ? $this->_pm->label : false;
            default: return false;
		}
	}

    public function get_form_id() {
		return 'leyka-form-'.($this->_pm_name ? $this->_pm_name : '');
	}

    public static function get_form_action() {

        if( !self::$_form_action ) {
            self::$_form_action = get_option('permalink_structure') ?
                home_url('leyka-process-donation') : home_url('?page=leyka-process-donation');
        }

		return self::$_form_action;

	}

	public function get_recurring_field() {

        if( !$this->is_field_supported('recurring') ) {
            return '';
        }

        ob_start();?>

        <label class="checkbox leyka-recurring-field">
            <input type="checkbox" class="leyka-recurring" name="leyka_recurring" value="1">
            <span class="leyka-checkbox-label"><?php _e('Monthly donations', 'leyka');?></span>
        </label>

        <?php $out = ob_get_contents();
        ob_end_clean();

        return apply_filters('leyka_recurring_field_html', $out);

    }

    public function get_amount_field() {

		if( !$this->is_field_supported('amount') ) {
			return '';
        }

		$mode = leyka_options()->opt('donation_sum_field_type'); // fixed/flexible/mixed
		$supported_curr = leyka_get_currencies_data();
		$current_curr = $this->get_current_currency();

		if(empty($supported_curr[$current_curr])) {
			return ''; // Current currency isn't supported
        }

		ob_start();?>

		<label for="leyka_donation_amount" class="leyka-screen-reader-text"><?php _e('Donation amount', 'leyka');?></label>

        <?php foreach($supported_curr as $currency => $data) {?>

            <span class="<?php echo $currency; ?> amount-variants-container" <?php echo $currency == $current_curr ? '' : 'style="display:none;"';?>>

            <?php if($mode == 'fixed' || $mode == 'mixed') {

                $variants = explode(',', $data['amount_settings']['fixed']);?>

            <?php foreach($variants as $amount) {?>
                <label class="figure" title="<?php _e('Please, specify your donation amount', 'leyka');?>">
                    <input type="radio" value="<?php echo (int)$amount;?>"
                           name="leyka_donation_amount" <?php echo $currency == $current_curr ? '' : 'disabled="disabled"';?>>
                    <?php echo (int)$amount;?>
                </label>
                <?php }

                if ($mode == 'mixed' && $variants) {
                    _e('or', 'leyka');
                }

            }

            if($mode != 'fixed') {?>

                <input type="text" title="<?php echo __('Specify the amount of your donation', 'leyka');?>" name="leyka_donation_amount" class="donate_amount_flex" value="<?php echo esc_attr($supported_curr[$current_curr]['amount_settings']['flexible']);?>" <?php echo $currency == $current_curr ? '' : 'disabled="disabled"';?> placeholder="<?php echo apply_filters('leyka_form_free_amount_placeholder', $supported_curr[$current_curr]['amount_settings']['flexible']);?>">
            <?php }?>

            </span>
        <?php }?>

        <span class="currency"><?php echo $this->get_currency_field();?></span>
        <div class="leyka_donation_amount-error field-error"></div>

		<?php $out = ob_get_contents();
		ob_end_clean();

		return leyka_field_wrap($out, 'amount-selector amount '.$mode);

	}

    public static function get_common_hidden_fields($campaign = null, array $rewrite = array()) {

        if($campaign) {
            $campaign = leyka_get_validated_campaign($campaign);
        } else {

            if( !is_singular(Leyka_Campaign_Management::$post_type) ) {
                return false;
            }

            $campaign = new Leyka_Campaign(get_post());

        }

        $template = leyka_get_current_template_data();
        $hiddens = apply_filters('leyka_hidden_common_donation_form_fields', array(
            'leyka_template_id' => $template['id'],
            'leyka_campaign_id' => $campaign->id,
            'leyka_ga_campaign_title' => esc_attr($campaign->payment_title),
            'leyka_amount_field_type' => leyka_options()->opt('donation_sum_field_type'),
        ));
        $hiddens = $rewrite + $hiddens;

        $out = wp_nonce_field('leyka_payment_form', '_wpnonce', true, false);
        foreach($hiddens as $key => $value) {
            $out .= "<input type='hidden' name='$key' value='".esc_attr($value)."' class='$key'>\n";
        }

        return $out;

    }

    public function get_hidden_fields($campaign = null) {

		if($campaign) {
			$campaign = leyka_get_validated_campaign($campaign);
		} else {

			if( !is_singular(Leyka_Campaign_Management::$post_type) ) {
				return false;
			}

			$campaign = new Leyka_Campaign(get_post());
		}

		$out = '';
		foreach(apply_filters('leyka_hidden_donation_form_fields', array(), $this, $campaign) as $field_name => $value) {
			$out .= "<input type='hidden' name='".esc_attr($field_name)."' value='".esc_attr($value)."'>";
		}

		return $out;
	}

	public function get_currency_field() {

		$supported_curr = $this->get_supported_currencies();
		$curr = $this->get_current_currency();

		if(count(array_keys($supported_curr)) > 1) {

			// Multi-currency:
			$out = '<label for="leyka_donation_currency" class="leyka-screen-reader-text">'.__('Currency', 'leyka').'</label>';
			$out .= '<select name="leyka_donation_currency" class="leyka_donation_currency">';
			foreach($supported_curr as $cid => $obj) {
				$out .= "<option data-currency-label='".$obj['label']."' value='".esc_attr($cid)."' "
                        .selected($cid, $curr, false).">".$obj['label']
                        ."</option>";
			}
			$out .= '</select>';

		} else {

			$obj = each($supported_curr);
			$out = '<span>'.$obj['value']['label'].'</span>
			        <input type="hidden" name="leyka_donation_currency" class="leyka_donation_currency" data-currency-label="'.$obj['value']['label'].'" value="'.$obj['key'].'" >';
		}

		return $out.$this->get_hidden_amount_fields();

	}

	public function get_hidden_amount_fields() {

        $hiddens = array();
        foreach($this->get_supported_currencies() as $currency_id => $data) {
            $hiddens[] = '<input type="hidden" name="top_'.esc_attr($currency_id).'" value="'.esc_attr($data['top']).'">
			              <input type="hidden" name="bottom_'.esc_attr($currency_id).'" value="'.esc_attr($data['bottom']).'">';
        }

        return implode("\n", $hiddens);

    }

	public function get_name_field($value = '') {

		if( !$this->is_field_supported('name') ) {
            return '';
        }

		ob_start();?>

		<label for="leyka_donor_name" class="leyka-screen-reader-text"><?php _e('Your name', 'leyka');?></label>
		<label class="input req">
            <input type="text" class="required non-email" name="leyka_donor_name" placeholder="<?php echo apply_filters('leyka_form_donor_name_placeholder', __('Your name', 'leyka'));?>" value="<?php echo apply_filters('leyka_form_donor_name_value', $value);?>">
        </label>
		<p class="field-comment">
            <?php echo apply_filters('leyka_form_donor_name_comment', __('We will use this to personalize your donation experience', 'leyka'));?>
        </p>
		<p class="leyka_donor_name-error field-error"></p>

	<?php $out = ob_get_contents();
		ob_end_clean();
		return leyka_field_wrap($out, 'name');		
	}

    public function get_email_field($value = '') {

		if( !$this->is_field_supported('email') ) {
			return '';
        }

		ob_start();?>

		<label for="leyka_donor_email" class="leyka-screen-reader-text"><?php _e('Your email', 'leyka');?></label>
		<label class="input req">
            <input type="text" class="required email leyka_donor_email" name="leyka_donor_email" placeholder="<?php echo apply_filters('leyka_form_donor_email_placeholder', __('Your email', 'leyka'));?>" value="<?php echo $value;?>">
        </label>
		<p class="field-comment"><?php _e('We will send the donation success notice to this address', 'leyka');?></p>
        <p class="leyka_donor_email-error field-error"></p>

	<?php $out = ob_get_contents();
		ob_end_clean();
		return leyka_field_wrap($out, 'email');

	}

	public function get_agree_field() {

		if( !leyka_options()->opt('agree_to_terms_needed') || !$this->is_field_supported('agree') ) {
            return '';
        }

		$agree_id = esc_attr(uniqid().'-text');

		ob_start();?>

		<div id="<?php echo $agree_id;?>" class="leyka-oferta-text">
			<div class="leyka-modal-close">X</div>
			<div class="leyka-oferta-text-frame">
				<div class="leyka-oferta-text-flow">
                    <?php echo apply_filters('leyka_terms_of_service_text', do_shortcode(leyka_options()->opt('terms_of_service_text')));?>
                </div>
			</div>
		</div>

		<label class="checkbox">
			<input type="checkbox" name="leyka_agree" class="leyka_agree required" value="1" id="leyka_agree" <?php echo leyka_options()->opt('terms_agreed_by_default') ? 'checked="checked"' : '';?>>
            <span class="leyka-checkbox-label">
                <?php echo apply_filters('agree_to_terms_text_text_part', leyka_options()->opt('agree_to_terms_text_text_part')).' ';?>
                <a class="leyka-legal-confirmation-trigger" href="#" data-oferta-content="#<?php echo $agree_id;?>">
				    <?php echo apply_filters('agree_to_terms_text_link_part', leyka_options()->opt('agree_to_terms_text_link_part'));?>
			    </a>
            </span>
		</label>
        <p class="leyka_agree-error field-error"></p>

	<?php $out = ob_get_contents();
		ob_end_clean();
		return leyka_field_wrap($out, 'agree');

	}

    public function get_submit_field() {

		if( !$this->is_field_supported('submit') ) {
			return '';
        }

		ob_start();?>
		<input type="submit" id="leyka_donation_submit" name="leyka_donation_submit" value="<?php echo esc_attr($this->get_submit_label());?>">

    <?php $out = ob_get_contents();
		ob_end_clean();
		return leyka_field_wrap($out, 'submit');

	}

	/** PM related methods **/
    public function get_pm_id() {
		return $this->_pm_name;
	}

    public function get_pm_label() {
        return $this->_pm->label ? $this->_pm->label : '';
	}

    public function get_pm_description() {
        return $this->_pm->description ? apply_filters('leyka_pm_description', $this->_pm->description, $this->_pm_name) : '';
	}

    public function get_supported_currencies() {

		$supported_curr = $this->_pm ? $this->_pm->currencies : array(leyka_options()->opt('main_currency'));
		$active_curr = leyka_get_currencies_data();
		$curr = array();

		foreach($active_curr as $cid => $obj) {
			if(in_array($cid, $supported_curr)) {
				$curr[$cid] = $obj;
            }
		}

		return $curr;

	}

    public function get_current_currency() {

		if( !$this->_current_currency ) {
			$this->_current_currency = $this->_pm ? $this->_pm->default_currency : leyka_options()->opt('main_currency');
        }

		return $this->_current_currency;

	}

    public function get_supported_global_fields() {

        $global_fields = $this->_pm && $this->_pm->has_global_fields ?
            array('amount', 'name', 'email', 'agree', 'submit') : array('');

        if($global_fields && $this->_pm->has_recurring_support()) {
            $global_fields[] = 'recurring';
        }

		return $global_fields;

	}

    public function is_field_supported($field) {
		return in_array($field, array_merge(
            $this->get_supported_global_fields(),
            $this->_pm ? $this->_pm->custom_fields : array()
        ));
	}

    public function get_pm_fields() {

		$res = $this->_pm ? $this->_pm->custom_fields : array(); // Array of custom fields' HTMLs

		if($res) {
            foreach($res as $key => $field) {
			    $res[$key] = leyka_field_wrap($field, $key);
            }
		}

		return implode('', $res);

	}

    public function get_submit_label(){
		return $this->_pm && $this->_pm->submit_label ? $this->_pm->submit_label : leyka_options()->opt('donation_submit_text');
	}

    public function get_pm_icons() {

		$res = $list = array(); // Array of icons urls
		if($this->_pm->icons) {
            $res = $this->_pm->icons;
        }

        foreach($res as $src) {
            $src = esc_url($src);
            $list[] = "<img src='{$src}' />";
        }

		return $list;

	}

	/**
	 * Template elements: tooltips error marks etc
	 **/

    public function question_mark($content, $css = '', $title = '') {
		return "<div class='question-icon {$css}'
         data-placement=right'
         data-title='{$title}'
         data-content='{$content}'
         data-html='true'
         data-trigger='hover'></div>";
	}
}

/* Helpers  */
function leyka_field_wrap($out, $css = '') {
		
	$css = esc_attr($css);
	return "<div class='leyka-field {$css}'>{$out}</div>";

}

function leyka_get_req_mark(){		
	return "<span class='req'>*</span>";
}

/* Template tags */
global $leyka_current_pm; /** @todo Make it a singletone instead of global var */

function leyka_setup_current_pm(Leyka_Payment_Method $payment_method, $currency = null) {
	/** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;
	$leyka_current_pm = new Leyka_Payment_Form($payment_method, $currency);

}

function leyka_pf_get_form_id($campaign_id = false) {

    $campaign_id = !$campaign_id ? get_the_ID() : (int)$campaign_id;
    $campaign_suffix = '';
    
    if($campaign_id && (int)$campaign_id > 0) {
        $campaign_suffix = (int)$campaign_id;
    }
    
    return 'leyka-pf-'.$campaign_suffix;

}

function leyka_pf_get_form_auto_open_class($campaign_id) {
    
    $auto_open_form_param = 'leyka-auto-open-form';
    $auto_open_form_class = 'leyka-pf--active';
    
    if(isset($_GET[$auto_open_form_param]) && $_GET[$auto_open_form_param]) {
        if( $_GET[$auto_open_form_param] == leyka_pf_get_form_id($campaign_id) ) {
            return $auto_open_form_class;
        }
    }
    
}

function leyka_pf_get_form_auto_open_final_screen_class($final_screen_name) {
    
    $open_class = "leyka-pf__final--open";
    $open_half_class = "leyka-pf__final--open-half";
    $open_param = 'leyka-final-screen';
    
    $ret = '';
    if(isset($_GET[$open_param]) && $_GET[$open_param]) {
        if( preg_match( "/^".preg_quote($final_screen_name)."/", $_GET[$open_param])) {
            $ret .= " " . $open_class;
            if( preg_match( "/-half$/", $_GET[$open_param])) {
                $ret .= " " . $open_half_class;
            }
        }
    }
    
    return $ret;
}

function leyka_pf_get_form_action() {
	return Leyka_Payment_Form::get_form_action();
}

function leyka_pf_is_field_supported($field) {
    /** @var Leyka_Payment_Form $leyka_current_pm */
    global $leyka_current_pm;
    return $leyka_current_pm->is_field_supported($field);

}

function leyka_pf_get_common_hidden_fields(Leyka_Campaign $campaign = null) {
    return Leyka_Payment_Form::get_common_hidden_fields(leyka_get_validated_campaign($campaign));
}

function leyka_pf_get_pm_hidden_fields($campaign = null, Leyka_Payment_Form $pf = null) {

    if( !$pf ) {
        /** @var Leyka_Payment_Form $leyka_current_pm */
        global $leyka_current_pm;
        $pf = $leyka_current_pm;

    }

    return $pf->get_hidden_fields(leyka_get_validated_campaign($campaign));
}

function leyka_pf_get_hidden_fields($campaign = null, $include_common_fields = true) {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;

    $campaign = leyka_get_validated_campaign($campaign);

	return ($include_common_fields ? Leyka_Payment_Form::get_common_hidden_fields($campaign) : '')
            .$leyka_current_pm->get_hidden_fields($campaign);
}

function leyka_pf_get_recurring_field() {
    /** @var Leyka_Payment_Form $leyka_current_pm */
    global $leyka_current_pm;

    return $leyka_current_pm->get_recurring_field();

}

function leyka_pf_get_amount_field() {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;
	
	return $leyka_current_pm->get_amount_field();

}

function leyka_pf_get_amount_value() {
    return empty($_POST['leyka_donation_amount']) ? '' : $_POST['leyka_donation_amount']; 
}

function leyka_pf_get_currency_value() {
    return empty($_POST['leyka_donation_currency']) ? '' : $_POST['leyka_donation_currency'];
}

function leyka_pf_get_donor_name_value() {
    return empty($_POST['leyka_donor_name']) ? '' : stripslashes($_POST['leyka_donor_name']);
}

function leyka_pf_get_donor_email_value() {
    return empty($_POST['leyka_donor_email']) ? '' : $_POST['leyka_donor_email'];
}

function leyka_pf_get_campaign_id_value() {
    return empty($_POST['leyka_campaign_id']) ? 0 : $_POST['leyka_campaign_id'];
}

function leyka_pf_get_payment_method_value() {
    $pm = empty($_POST['leyka_payment_method']) ? '' : explode('-', $_POST['leyka_payment_method']);

    return $pm ? array('gateway_id' => $pm[0], 'payment_method_id' => $pm[1]) : array();
}

function leyka_pf_get_name_field($value = '') {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;

	return $leyka_current_pm->get_name_field($value);
}

function leyka_pf_get_email_field($value = '') {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;

	return $leyka_current_pm->get_email_field($value);
}

//function leyka_pf_get_recurring_field() {
//    /** @var Leyka_Payment_Form $leyka_current_pm */
//	global $leyka_current_pm;
//
//	return $leyka_current_pm->get_recurring_field();
//}

function leyka_pf_get_agree_field() {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;

	return $leyka_current_pm->get_agree_field();
}

function leyka_pf_get_submit_field() {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;

	return $leyka_current_pm->get_submit_field();
}

function leyka_pf_get_pm_label() {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;

	return $leyka_current_pm->get_pm_label();
}

function leyka_pf_get_pm_description() {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;

	return $leyka_current_pm->get_pm_description();
}

function leyka_pf_get_pm_fields() {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;

	return $leyka_current_pm->get_pm_fields();
}

function leyka_pf_get_pm_icons() {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;

	return $leyka_current_pm->get_pm_icons();
}

function leyka_pf_footer() { do_action('leyka_before_footer');?>

<div class="leyka-form-footer">
	<div id="leyka-copy">
		<p><?php printf(__('Proudly powered by %s', 'leyka'), '<a href="//leyka.te-st.ru" target="_blank">'._x('Leyka', 'Plugin name in preposotional case', 'leyka').'</a>');?></p>
	</div>
</div>
<?php do_action('leyka_after_footer');
}

function leyka_share_campaign_block($campaign_id = null) {

	if( !$campaign_id ) {
		$campaign_id = get_the_ID();
    }?>

	<div id="share-campaign-area" class="toggle">
		<div class="leyka-toggle-trigger"><?php _e('Share (get embed code)', 'leyka');?></div>
		<div class="leyka-toggle-area">
			
			<div class="leyka-embed-block">
                <div id="embed-size-pane" class="leyka-setting-row">
                    <div class="col-1"><label><?php _e('Width', 'leyka');?>: <input type="text" name="embed_iframe_w" id="embed_iframe_w" value="300" size="4"></label>
                    <label><?php _e('Height', 'leyka');?>: <input type="text" name="embed_iframe_w" id="embed_iframe_h" value="510" size="4"></label>
                    </div>
                    <div class="col-2">
                    <textarea class="embed-code read-only campaign-embed-code"><?php echo Leyka_Campaign_Management::get_card_embed_code($campaign_id, true);?></textarea></div>
                </div>

                <div class="leyka-embed-preview">
                    <h4><?php _e('Preview', 'leyka');?></h4>
                    <?php echo Leyka_Campaign_Management::get_card_embed_code($campaign_id, false); ?>
                </div>
			</div><!-- .embed-block -->
			
		</div>
	</div>
<?php
}

/* previous submission errors */
function leyka_pf_submission_errors() {?>

    <div id="leyka-submit-errors" class="leyka-submit-errors" <?php echo leyka()->has_session_errors() ? '' : 'style="display:none"';?>>
    <?php if(leyka()->has_session_errors()) {?>
        <span><?php _e('Errors', 'leyka');?>: </span>
        <ul>
            <?php foreach(leyka()->get_session_errors() as $wp_error) { /** @var $wp_error WP_Error */?>
                <li><?php echo $wp_error->get_error_message();?></li>
            <?php }?>
        </ul>
        <?php leyka()->clear_session_errors();?>
    <?php }?>
    </div>
<?php }

add_action('leyka_single_campaign_sharing', 'leyka_share_campaign_block');

/**
 * Donation forms template
 **/

add_filter('the_content', 'leyka_print_donation_elements');
function leyka_print_donation_elements($content) {

	$current_campaign_post = get_post();

	$autoprint = leyka_options()->opt('donation_form_mode');
	if( !is_singular(Leyka_Campaign_Management::$post_type) || !$autoprint ) {
        return $content;
    }

	$campaign = new Leyka_Campaign($current_campaign_post);
	if($campaign->ignore_global_template_settings) {
		return $content;
    }

	$post_content = $content;
	$content = '';

	// Scale on top of form:	
	if(leyka_options()->opt('scale_widget_place') == 'top' || leyka_options()->opt('scale_widget_place') == 'both') {
        $content .= do_shortcode("[leyka_scale show_button='1']");
    }

	$content .= $post_content;

	// Scale below form:
	if($campaign->target && (leyka_options()->opt('scale_widget_place') == 'bottom' || leyka_options()->opt('scale_widget_place') == 'both')) {
        $content .= do_shortcode("[leyka_scale show_button='0']");
    }

    $content .= get_leyka_payment_form_template_html($current_campaign_post); // Payment form

    $campaign->increase_views_counter(); // Increase campaign views counter

	// Donations list:
    if(leyka_options()->opt('leyka_donations_history_under_forms')) {

		$list = leyka_get_donors_list($current_campaign_post->ID);
		if($list) {

			$label = apply_filters('leyka_donations_list_title', __('Our sincere thanks', 'leyka'), $current_campaign_post->ID);
			$content .= '<h3 class="leyka-donations-list-title">'.$label.'</h3>'.$list;
		}
    }

	return $content;
}

function leyka_get_current_template_data($campaign = null, $template = null, $is_service = false) {

	if( !$campaign ) {
		$campaign = get_post();
    } elseif(is_int($campaign)) {
		$campaign = get_post($campaign);
    }

    // Get campaign-specific template, if needed:
	if( !$template ) {

        if( !is_a($campaign, 'Leyka_Campaign') ) {
		    $campaign = new Leyka_Campaign($campaign);
        }

		$template = $campaign->template; 
	}
    
    if( !$template || $template == 'default' ) {
        $template = leyka_options()->opt('donation_form_template');
    }

    $template = leyka()->get_template($template, !!$is_service);
   
    return $template ? $template : false;

}

function get_leyka_payment_form_template_html($campaign = null, $template = null) {

    ob_start();

	if( !$campaign ) {
        $campaign = new Leyka_Campaign(get_post());
	} elseif(is_int($campaign) || is_a($campaign, 'WP_Post')) {
        $campaign = new Leyka_Campaign($campaign);
	} elseif( !is_a($campaign, 'Leyka_Campaign') ) {
        return false;
    }

    if($campaign->is_finished) {?>

    <div id="leyka-campaign-finished"><?php _e('Donations collection finished. Thank you for your support!', 'leyka');?></div>

<?php } else {

        $pm_list = leyka_get_pm_list(true);
        $curr_pm = $pm_list ? leyka_get_pm_by_id(reset($pm_list)->full_id, true) : false;

        if( !$curr_pm ) {?>

        <div class="<?php echo apply_filters('leyka_no_pm_error_classes', 'leyka-nopm-error');?>">
            <?php echo is_user_logged_in() ?
                   str_replace('%s', admin_url('admin.php?page=leyka_settings&stage=payment#leyka_pm_available-wrapper'), __('There are no payment methods selected to donate! Please, <a href="%s">set them up</a>.', 'leyka')) :
                    __('Dear donor, we are very sorry, but we had not setted up the donations module yet :( Please try to donate later.', 'leyka');?>
        </div>

        <?php } else {

            $template = leyka_get_current_template_data($campaign, $template);

            if($template && isset($template['file'])) {

                $template_subdir = LEYKA_PLUGIN_DIR.'templates/leyka-'.$template['id'];
                if(file_exists($template_subdir)) {
                    foreach(glob($template_subdir.'/leyka-'.$template['id'].'-*.php') as $file) {
                        if(stristr($file, 'leyka-'.$template['id'].'-init.php') === false) {
                            require_once($file);
                        }
                    }
                }
                
                if($template['id'] == 'revo') { /** @todo TMP!!! Make it normal */
                    echo leyka_inline_campaign(array('id' => $campaign->id, 'template' => 'revo'));
                } else {
                    require $template['file'];
                }

            }
        }

    } // Campaign finished

    $out = ob_get_contents();
    ob_end_clean();

	return $out;
}

/**
 * Template tag for indirect filtering
 * 
 * should be probably marked as deprecated
 * use leyka_get_payment_form instead
 **/
function leyka_get_donation_form($echo = true) {

	if( !is_singular(Leyka_Campaign_Management::$post_type) ) {
        return '';
    }

	if($echo) {

        echo get_leyka_payment_form_template_html();
        return true;

    } else {
        return get_leyka_payment_form_template_html();
    }
}

function leyka_remember_donation_data(array $additional_data = array()) {

    $to_remember = array('donor_name' => leyka_pf_get_donor_name_value(), 'donor_email' => leyka_pf_get_donor_email_value());

    foreach(array_merge($to_remember, $additional_data) as $key => $value) {
        leyka_remembered_data($key, $value);
    }

}

/** Filters */
function leyka_terms_of_service_text($text) {
    return wpautop(str_replace(
        array(
            '#LEGAL_NAME#',
            '#LEGAL_FACE#',
            '#LEGAL_FACE_RP#',
            '#LEGAL_FACE_POSITION#',
            '#LEGAL_ADDRESS#',
            '#STATE_REG_NUMBER#',
            '#KPP#',
            '#INN#',
            '#BANK_ACCOUNT#',
            '#BANK_NAME#',
            '#BANK_BIC#',
            '#BANK_CORR_ACCOUNT#',
        ),
        array(
            leyka_options()->opt('org_full_name'),
            leyka_options()->opt('org_face_fio_ip'),
            leyka_options()->opt('org_face_fio_rp'),
            leyka_options()->opt('org_face_position'),
            leyka_options()->opt('org_address'),
            leyka_options()->opt('org_state_reg_number'),
            leyka_options()->opt('org_kpp'),
            leyka_options()->opt('org_inn'),
            leyka_options()->opt('org_bank_account'),
            leyka_options()->opt('org_bank_name'),
            leyka_options()->opt('org_bank_bic'),
            leyka_options()->opt('org_bank_corr_account'),
        ),
        $text
    ));
}
add_filter('leyka_terms_of_service_text', 'leyka_terms_of_service_text');