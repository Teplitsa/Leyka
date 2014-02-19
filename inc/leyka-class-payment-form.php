<?php
/**
 * Payment form
 **/

//options in temp_config.php

/**
 * Form
 **/
class Leyka_Payment_Form {
	private $_pm_name;
	private $_pm = array();
	private $_form_action;
	private $_current_currency; //current currency in the view
	
	function __construct(Leyka_Payment_Method $payment_method, $current_currency = null) {
//		global $leyka_pm;
		
		/*if(is_array($payment_method)){
			$this->payment_method = $payment_method['id'];
			$this->pm_options = $payment_method;			
		}
		else {*/

        $this->_pm = $payment_method;
        $this->_pm_name = $payment_method->id;       
        //pm: get whole payment data by PM ID					
		// }

		$this->_current_currency = $current_currency;
		$this->_form_action = site_url('leyka-process-donation');
	}	

	/**
	 * Global Form params
	 **/
	function get_form_id() {
		return 'leyka-form-'.$this->_pm_name;
	}
	
	function get_form_action(){
		
		return $this->_form_action;
	}
	
	function get_amount_field() {

		if( !$this->is_field_supported('amount') )
			return '';
		
		// Options: amount field mode:
		$mode = leyka_options()->opt('donation_sum_field_type'); // fixed/flexible
		$supported_curr = leyka_get_active_currencies(); // $this->get_supported_currencies();
		$current_curr = $this->get_current_currency();
		
		if(empty($supported_curr[$current_curr]))
			return ''; // current currency isn't supported
		
		ob_start();

		if($mode == 'fixed') {

			$comment = __('Please, specify your donation amount', 'leyka');			
			$variants = explode(',', $supported_curr[$current_curr]['amount_settings']['fixed']);	

			if($variants) {?>

                <span class="<?php echo $current_curr;?> amount-variants-container">
                <?php foreach($variants as $amount) {?>
                    <label class="figure" title="<?php echo esc_attr($comment);?>">
                        <input type="radio" value="<?php echo (int)$amount;?>" name="leyka_donation_amount"><?php echo (int)$amount;?>
                    </label>
		        <?php }?>
                </span>

                <?php foreach($supported_curr as $currency => $data) {
                    if($currency == $current_curr)
                        continue;?>

                    <span class="<?php echo $currency;?> amount-variants-container" style="display:none;">
                        <?php foreach(explode(',', $data['amount_settings']['fixed']) as $amount) {?>
                            <label class="figure" title="<?php echo esc_attr($comment);?>">
                                <input type="radio" value="<?php echo (int)$amount;?>" name="leyka_donation_amount" /><?php echo (int)$amount;?>
                            </label>
                        <?php }?>
                    </span>
                <?php }?>

                <span class="currency"><?php echo $this->get_currency_field();?></span>                
				<div id="leyka_donation_amount-error" class="field-error"></div>
		    <?php }

        } else {?>

			<span class="figure">
                <input type="text" title="<?php echo __('Specify donation amount', 'leyka');?>" name="leyka_donation_amount" class="required" id="donate_amount_flex" value="<?php echo esc_attr($supported_curr[$current_curr]['amount_settings']['flexible']);?>">                
            </span>
			<span class="currency">
				<?php echo $this->get_currency_field();?>
			</span>
			<div id="leyka_donation_amount-error" class="field-error"></div>
		<?php }

		$out = ob_get_contents();
		ob_end_clean();

		return leyka_field_wrap($out, 'amount-selector amount '.$mode);			
	}
	
	function get_hidden_fields($campaign_id = null){
		global $post;

		if($campaign_id == null)
			$campaign_id = $post->ID;

        $template = leyka_get_current_template_data();
		$hiddens = array(
			'leyka_template_id' => $template['id'],
			'leyka_campaign_id' => (int)$campaign_id
		);

		$out = wp_nonce_field('leyka_payment_form', '_wpnonce', true, false);
		foreach($hiddens as $key => $value){
			$out .= "<input type='hidden' name='".esc_attr($key)."' value='".esc_attr($value)."' />";
		}

		return $out;
	}
	
	public function get_currency_field() {

		$supported_curr = $this->get_supported_currencies();
		$curr = $this->get_current_currency();

		if(count(array_keys($supported_curr)) > 1) {

			// Multicurrency:
			$out = "<select name='leyka_donation_currency' class='leyka_donation_currency'>";
			foreach ($supported_curr as $cid => $obj) {
				$out .= "<option data-currency-label='".$obj['label']."' value='".esc_attr($cid)."' "
                        .selected($cid, $curr, false).">".$obj['label']."</option>";
			}
			$out .= "</select>";
			
		} else {

			$obj = each($supported_curr);
			$out = '<span>'.$obj['value']['label'].'</span><input type="hidden" name="leyka_donation_currency" 
                    class="leyka_donation_currency" data-currency-label="'.$obj['value']['label'].'" value="'.$obj['key'].'" />';
		}
		
		// Add limits:
		$hiddens = array();
		foreach($supported_curr as $cid => $obj){
			$hiddens[] = "<input type='hidden' name='top_".esc_attr($cid)."' value='".esc_attr($obj['top'])."'>";
			$hiddens[] = "<input type='hidden' name='bottom_".esc_attr($cid)."' value='".esc_attr($obj['bottom'])."'>";
		}
				
		return $out.implode('', $hiddens);
	}
	
	function get_name_field($value = '') {
		
		if(!$this->is_field_supported('name'))
			return '';
		
		$ph = __('Your name', 'leyka');
		$comment = __('We will use this to personalize your donation experience', 'leyka');
		
		ob_start();
	?>		
		<label class="input req"><input type="text" class="required" name="leyka_donor_name" placeholder="<?php echo esc_attr($ph);?>" id="leyka_donor_name" value="<?php echo $value;?>"></label>
		<p class="field-comment"><?php echo $comment;?></p>
		<p id="leyka_donor_name-error" class="field-error"></p>
        
	<?php
		$out = ob_get_contents();
		ob_end_clean();
		return leyka_field_wrap($out, 'email');		
	}
	
	
	function get_email_field($value = '') {
		
		if(!$this->is_field_supported('email'))
			return '';
		
		$ph = __('Your email', 'leyka');
		$comment = __('We will send the donation success notice to this address', 'leyka');
		
		ob_start();
	?>	
		<label class="input req"><input type="text" class="required email" name="leyka_donor_email" placeholder="<?php echo esc_attr($ph);?>" id="leyka_donor_email" value="<?php echo $value;?>"></label>
		<p class="field-comment"><?php echo $comment;?></p>
        <p id="leyka_donor_email-error" class="field-error"></p>
		
	<?php
		$out = ob_get_contents();
		ob_end_clean();
		return leyka_field_wrap($out, 'name');		
	}
	
	
	function get_agree_field() {
		
		if( !leyka_options()->opt('argee_to_terms_needed') || !$this->is_field_supported('agree') )
			return '';

		// options: agree label for chackbox
		$agree_id = esc_attr(uniqid().'-text');

		ob_start();
	?>
		<div id="<?php echo $agree_id;?>" class="leyka-oferta-text">
			<div class="leyka-modal-close">X</div>
			<div class="leyka-oferta-text-frame">
				<div class="leyka-oferta-text-flow"><?php echo apply_filters('leyka_terms_of_service_text', leyka_options()->opt('terms_of_service_text'));?></div>
			</div>
		</div>
		<label class="checkbox">
			<input type="checkbox" name="leyka_agree" id="leyka_agree" class="leyka_agree required" value="1" />
			<a class="leyka-legal-confirmation-trigger" href="#<?php echo $agree_id;?>">
                <?php echo leyka_options()->opt('agree_to_terms_text');?>
            </a>
		</label>
        <p class="field-error" id="leyka_agree-error"></p>
		
	<?php
		$out = ob_get_contents();
		ob_end_clean();
		return leyka_field_wrap($out, 'agree');		
	}
	
	function get_submit_field() {
		
		if(!$this->is_field_supported('submit'))
			return '';
		
		$label = $this->get_submit_label();
		//disabled="disabled"
		ob_start();
	?>
		<input type="submit" id="leyka_donation_submit" name="leyka_donation_submit" value="<?php echo esc_attr($label);?>" />
	<?php
		$out = ob_get_contents();
		ob_end_clean();
		return leyka_field_wrap($out, 'submit');	
	}

	
	/** PM related methods **/
	function get_pm_id() {

		return $this->_pm_name;
	}
	
	function get_pm_label() {
        /** @todo Maybe, we need to throw an ex in case when PM label is empty for some reason */
        return $this->_pm->label ? $this->_pm->label : '';
	}
	
	function get_pm_description() {

        return $this->_pm->description ? $this->_pm->description : '';		
	}
	
	function get_supported_currencies() {

		$supported_curr = $this->_pm->currencies;
		$active_curr = leyka_get_active_currencies();
		$curr = array();

		foreach($active_curr as $cid => $obj) {
			if(in_array($cid, $supported_curr))
				$curr[$cid] = $obj;
		}

		return $curr;
	}

	function get_current_currency(){
		if(empty($this->_current_currency))
			$this->_current_currency = $this->_pm->default_currency;

		return $this->_current_currency;
	}

	function get_supported_global_fields() {
		return $this->_pm->has_global_fields ? array('amount', 'name', 'email', 'agree', 'submit') : array('');
	}

	function is_field_supported($field) {
		return in_array($field, $this->get_supported_global_fields());
	}

	function get_pm_fields() {
//		global $leyka_pm;
		
		$res = $this->_pm->custom_fields; // Array of custom fields' HTMLs

		if($res) {
            foreach($res as $key => $f)
			    $res[$key] = leyka_field_wrap($f, $key);
		}

		return implode('', $res);
	}

	function get_submit_label(){

		return $this->_pm->submit_label; // ? $this->_pm->submit_label : leyka_options()->opt('donate_submit_text');
	}
	
	function get_pm_icons() {
//		global $leyka_pm;

		$res = $list = array(); // Array of icons urls
		if($this->_pm->icons)		
			$res = $this->_pm->icons;

        foreach($res as $src){
            $src = esc_url($src);
            $list[] = "<img src='{$src}' />";
        }

		return $list;			
	}

	/**
	 * Template elements: tooltps error marks etc
	 **/
	
	function question_mark($content, $css = '', $title = '') {
		
		return "<div class='question-icon {$css}'
         data-placement=right'
         data-title='{$title}'
         data-content='{$content}'
         data-html='true'
         data-trigger='hover'></div>";
	}
} // class end 

/* Helpers  */
function leyka_field_wrap($out, $css = '') {
		
	$css = esc_attr($css);
	return "<div class='leyka-field {$css}'>{$out}</div>";
}

function leyka_get_req_mark(){		
	return "<span class='req'>*</span>";
}

/* Template tags */
global $leyka_current_pm;

function leyka_setup_current_pm(Leyka_Payment_Method $payment_method, $currency = null) {
	global $leyka_current_pm;
	
	$leyka_current_pm = new Leyka_Payment_Form($payment_method, $currency);
}

function leyka_pf_get_form_id() {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;
	
	return $leyka_current_pm->get_form_id();
}

function leyka_pf_get_form_action() {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;
	
	return $leyka_current_pm->get_form_action();
}

function leyka_pf_get_hidden_fields($campaign_id = null) {
    /** @var Leyka_Payment_Form $leyka_current_pm */
	global $leyka_current_pm;
	
	return $leyka_current_pm->get_hidden_fields($campaign_id);
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
    return empty($_POST['leyka_donor_name']) ? '' : $_POST['leyka_donor_name'];
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

function leyka_pf_footer() {
?>
<div class="leyka-form-footer">
	<!-- credentials -->
	<div id="leyka-copy"><p>
	<?php printf(__('Proudly powered by %s', 'leyka'), '<a href="http://leyka.te-st.ru">'._x('Leyka', 'Plugin name in preposotional case', 'leyka').'</a>'); ?>							
	</p></div>
</div>
<?php
}

/* previous submission errors */
function leyka_pf_submission_errors() {
	
    if(leyka()->has_session_errors()) {?>

    <div class="leyka-submit-errors">
		<span><?php _e('Errors', 'leyka');?>: </span>
        <ul>
            <?php foreach(leyka()->get_session_errors() as $wp_error) { /** @var $wp_error WP_Error */?>
                <li><?php echo $wp_error->get_error_message();?></li>
            <?php }?>
        </ul>
    </div>
<?php leyka()->clear_session_errors();
    } 
}

/* Template */
add_filter('the_content', 'leyka_print_donation_form');
function leyka_print_donation_form($content) {

//    echo '<pre>'.print_r('Here', TRUE).'</pre>';
	$autoprint = leyka_options()->opt('leyka_donation_form_mode'); 
	if( !is_singular('leyka_campaign') || !$autoprint )
		return $content;
		
	return $content.get_leyka_payment_form_template_html();
}

function leyka_get_current_template_data() {
    global $post;

    // Get campaign-specific template, if needed:
    $campaign = new Leyka_Campaign($post);
    $template = $campaign->template; 
    if( !$template || $template == 'default' )
        $template = leyka_options()->opt('donation_form_template');

    $template = leyka()->get_template($template);
   
    return $template ? $template : false;
}

function get_leyka_payment_form_template_html(){
	global $post;

    $out = '';
    ob_start();

    $campaign = new Leyka_Campaign($post);
    if($campaign->is_finished) {?>

        <div id="leyka-campaign-finished"><?php echo __('This charity campaign has been finished. All fundraising stopped.', 'leyka');?></div>

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

            $template = leyka_get_current_template_data();

            if($template && isset($template['file'])){
                include($template['file']);
            }
        }

    }

    $out = ob_get_contents();
    ob_end_clean();

	return $out;
}

/**
 * Template tag for indirect filtering
 **/
function leyka_get_donation_form($echo = true) {
	//may be  it should accept campaign ID as param
	if( !is_singular('leyka_campaign') )
		return;
		
	if($echo) {
		echo get_leyka_payment_form_template_html();
	}
	else {
		return get_leyka_payment_form_template_html();
	}
}


/**
* AJAX for templates
**/
function leyka_payment_method_action() {
	
	check_ajax_referer('leyka_payment_form', '_leyka_ajax_nonce');
	
	if(empty($_POST['pm_id']))
		die('-1');
		
//	if(empty($_POST['currency']))
//		die('-1');
	
	$curr_currency = trim($_POST['currency']);	
	$curr_pm = leyka_get_pm_by_id(trim($_POST['pm_id']));
    
    if( !$curr_pm )
        die('-1');
		
	leyka_setup_current_pm($curr_pm, $curr_currency);

    ob_start();?>

	<div class="leyka-pm-fields">

	<div class='leyka-user-data'>
	<?php
		echo leyka_pf_get_name_field(empty($_POST['user_name']) ? '' : trim($_POST['user_name']));
		echo leyka_pf_get_email_field(empty($_POST['user_email']) ? '' : trim($_POST['user_email']));
		echo leyka_pf_get_pm_fields();
	?>
	</div>

	<?php
		echo leyka_pf_get_agree_field();
		echo leyka_pf_get_submit_field();
		
		$icons = leyka_pf_get_pm_icons();	
		if($icons) {
			$list = array();
			foreach($icons as $i){
				$list[] = "<li>{$i}</li>";
			}

			echo "<ul class='leyka-pm-icons cf'>";
			echo implode('', $list);
			echo "</ul>";
		}
		
	?>
	</div>
	<?php
		echo "<div class='leyka-pm-desc'>".apply_filters('leyka_the_content', leyka_pf_get_pm_description())."</div>";
    $out = ob_get_contents();
    ob_end_clean();

    $payment_form = new Leyka_Payment_Form($curr_pm, $curr_currency);
    echo json_encode(array('pm' => $out, 'currency' => $payment_form->get_currency_field()));
	die();
}
add_action('wp_ajax_leyka_payment_method', 'leyka_payment_method_action');
add_action('wp_ajax_nopriv_leyka_payment_method', 'leyka_payment_method_action');

function leyka_currency_choice_action(){
	check_ajax_referer('leyka_payment_form', '_leyka_ajax_nonce');
	
	if(empty($_POST['currency']))
		die('-1');

	$curr_currency = trim($_POST['currency']);	
	$pm_selected = trim($_POST['current_pm']);
	$currently_active_pmethods = leyka_get_pm_list(true, $curr_currency);
//    echo '<pre>'.print_r($currently_active_pmethods, TRUE).'</pre>';
	
    $curr_pm_is_active = false;
    foreach($currently_active_pmethods as $pm) {
        if($pm->id == $pm_selected) {
            $curr_pm_is_active = true;
            $pm_selected = $pm;
            break;
        }
    }
	if( !$curr_pm_is_active )
		$pm_selected = reset($currently_active_pmethods);

	leyka_setup_current_pm($pm_selected, $curr_currency);

    echo leyka_pf_get_hidden_fields((int)$_POST['campaign']);?>
	
	<!-- pm selector -->
	<div id="pm-selector" class="form-part">
		<ul class="leyka-pm-selector">
	<?php foreach($currently_active_pmethods as $pm) {?>
		<li>
			<label class="radio">
			<input type="radio" name="leyka_payment_method" value="<?php echo esc_attr($pm->full_id);?>" <?php checked($pm_selected->id, $pm->id);?> data-pm_id="<?php echo esc_attr($pm->id);?>">
			<?php echo $pm->label;?>
			</label>
		</li>
	<?php }?>
		</ul>
	</div>

	<!-- changeable area -->
	<div id="leyka-pm-data" class="changeable-fields form-part">
			
		<div class="leyka-pm-fields">
			
		<div class='leyka-user-data'>
		<?php
			echo leyka_pf_get_name_field();
			echo leyka_pf_get_email_field();
			echo leyka_pf_get_pm_fields();
		?>
		</div>
		
		<?php
			echo leyka_pf_get_agree_field();
			echo leyka_pf_get_submit_field();
			
			$icons = leyka_pf_get_pm_icons();	
			if($icons) {
				$list = array();
				foreach($icons as $i){
					$list[] = "<li>{$i}</li>";
				}

				echo "<ul class='leyka-pm-icons cf'>";
				echo implode('', $list);
				echo "</ul>";
			}
			
		?>
		</div>
		<?php echo "<div class='leyka-pm-desc'>".apply_filters('leyka_the_content', leyka_pf_get_pm_description())."</div>";?>

	</div>
<?php

	die();
}
add_action('wp_ajax_leyka_currency_choice', 'leyka_currency_choice_action');
add_action('wp_ajax_nopriv_leyka_currency_choice', 'leyka_currency_choice_action');

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
//                        leyka_options()->opt(''),
        ),
        $text
    ));
}
add_filter('leyka_terms_of_service_text', 'leyka_terms_of_service_text');