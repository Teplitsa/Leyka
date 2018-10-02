<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex.Kassa step. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */

$campaigns = get_posts(array(
    'post_type' => Leyka_Campaign_Management::$post_type,
    'post_status' => array('publish', 'pending', 'draft'),
    'posts_per_page'   => 1,
    'fields' => 'ids',
));

$campaign_id = count($campaigns) ? $campaigns[0] : null;
$campaign = $campaign_id ? new Leyka_Campaign($campaign_id) : null;
$campaign_title = $campaign ? apply_filters('single_post_title', $campaign->title) : null;

$test_payment = !empty($_COOKIE['leyka_donation_id']) ? new Leyka_Donation($_COOKIE['leyka_donation_id']) : null;
$is_came_back_from_yandex = preg_match(
    '/^https:\/\/money.yandex.ru\/payments\/external\/confirmation?orderId=.*/',
    wp_get_referer()
);
$is_payment_completed = $is_came_back_from_yandex && $test_payment && $test_payment->get_funded_date();?>

<div class="payment-tryout-wrapper">
    <input type="button" class="button button-secondary" <?php echo $is_payment_completed ? 'disabled' : '';?> id="yakassa-make-live-payment" value="Реальное пожертвование">
    <span class="leyka-loader xs yakassa-make-live-payment-loader" style="display: none;"></span>
</div>

<?php if( !$is_came_back_from_yandex ) {?>
<div class="payment-tryout-comment live-payment"><span class="attention-needed">Внимание!</span> Необходимо будет ввести данные действующей карты и деньги будут с нее списаны.</div>
<?php } elseif($is_payment_completed) {?>
    <div class="payment-result">
        <div class="result ok">Поздравляем! Ваш платёж успешно прошел</div>
    </div>
<?php } else {?>
    <div class="payment-result">
        <div class="result fail">Произошла ошибка</div>
    </div>
<?php }?>

<!--
action: leyka_ajax_get_gateway_redirect_data
_wpnonce: e15af61957
_wp_http_referer: /campaign/%d0%bd%d0%b0-%d1%83%d1%81%d1%82%d0%b0%d0%b2%d0%bd%d1%83%d1%8e-%d0%b4%d0%b5%d1%8f%d1%82%d0%b5%d0%bb%d1%8c%d0%bd%d0%be%d1%81%d1%82%d1%8c/
leyka_template_id: revo
leyka_amount_field_type: custom
leyka_campaign_id: 11
leyka_ga_campaign_title: На уставную деятельность
leyka_honeypot: 
top_rur: 30000
bottom_rur: 100
leyka_donation_amount: 100
leyka_donation_currency: rur
leyka_recurring: 0
amount-range: 100
leyka_payment_method: yandex-yandex_card
leyka_donor_name: denis
leyka_donor_email: test@ngo2.ru
leyka_agree: 1
leyka_agree_pd: 1
-->

<input type="hidden" name="payment_completed" value="<?php echo (int)$is_payment_completed;?>">

<script>
    
    var leykaYakassaPaymentData = {
        action: 'leyka_ajax_get_gateway_redirect_data',
        leyka_template_id: 'revo',
        leyka_amount_field_type: 'custom',
        leyka_donation_amount: 1.0,
        leyka_donation_currency: 'rur',
        leyka_recurring: 0,
        leyka_payment_method: 'yandex-yandex_card',
        leyka_agree: 1,
        leyka_agree_pd: 1,
        _wpnonce: '<?php echo wp_create_nonce('leyka_payment_form');?>',
        _wp_http_referer: '<?php echo wp_get_referer();?>',
        leyka_campaign_id: <?php echo $campaign_id ? $campaign_id : 0;?>,
        leyka_ga_campaign_title: '<?php echo $campaign_title ? $campaign_title : '';?>',
        leyka_donor_name: '<?php echo leyka_options()->opt('org_face_fio_ip');?>',
        leyka_donor_email: '<?php echo get_option('admin_email');?>',
        //leyka_success_page_url: '<?php //echo '';?>//'
    };

</script>