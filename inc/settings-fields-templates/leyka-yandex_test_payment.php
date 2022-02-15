<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex.Kassa step. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */

$campaigns = get_posts([
    'post_type' => Leyka_Campaign_Management::$post_type,
    'post_status' => ['publish', 'pending', 'draft',],
    'posts_per_page'   => 1,
    'fields' => 'ids',
]);

$campaign_id = count($campaigns) ? $campaigns[0] : null;
$campaign = $campaign_id ? new Leyka_Campaign($campaign_id) : null;
$campaign_title = $campaign ? apply_filters('single_post_title', $campaign->title) : null;

$test_payment = empty($_COOKIE['leyka_donation_id']) ? null : Leyka_Donations::get_instance()->get($_COOKIE['leyka_donation_id']);
$is_came_back_from_yandex = preg_match('/^https:\/\/yoomoney.ru*/', wp_get_raw_referer());
$is_payment_completed = $is_came_back_from_yandex && $test_payment && $test_payment->get_funded_date();?>

<div class="payment-tryout-wrapper">
    <input type="button" class="button button-secondary" <?php echo $is_payment_completed ? 'disabled' : '';?> id="yandex-make-live-payment" value="<?php esc_attr_e('"Almost real" donation', 'leyka');?>">
    <span class="leyka-loader xs yandex-make-live-payment-loader" style="display: none;"></span>
</div>

<?php if( !$is_came_back_from_yandex ) {?>
<div class="payment-tryout-comment live-payment">
    <span class="attention-needed"><?php _e('Warning!', 'leyka');?></span> <?php _e('You will have to enter the real and working bank card, and the real money will be taken from it.', 'leyka');?>
</div>
<?php } else if($is_payment_completed) {?>
    <div class="payment-result">
        <div class="result ok"><?php _e('Congratulations! Your donation is successful', 'leyka');?></div>
    </div>
<?php } else {?>
    <div class="payment-result">
        <div class="result fail"><?php _e('An error occured', 'leyka');?></div>
    </div>
<?php }?>

<input type="hidden" name="payment_completed" value="<?php echo (int)$is_payment_completed;?>">

<script>

    let leykaYandexPaymentData = {
        action: 'leyka_ajax_get_gateway_redirect_data',
        leyka_template_id: 'star',
        leyka_amount_field_type: 'custom',
        leyka_donation_amount: 1.0,
        leyka_donation_currency: 'rub',
        leyka_recurring: 0,
        leyka_payment_method: 'yandex-yandex_card',
        leyka_agree: 1,
        leyka_agree_pd: 1,
        _wpnonce: '<?php echo wp_create_nonce('leyka_payment_form');?>',
        _wp_http_referer: '<?php echo wp_get_referer();?>',
        leyka_campaign_id: <?php echo $campaign_id ? : 0;?>,
        leyka_ga_campaign_title: '<?php echo $campaign_title ? $campaign_title : '';?>',
        leyka_donor_name: '<?php echo leyka_options()->opt('org_face_fio_ip');?>',
        leyka_donor_email: '<?php echo get_option('admin_email');?>',
    };

</script>