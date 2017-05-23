<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Revo
 * Description: The most recent te-st.ru design work, the modern and lightweight step-by-step form template.
 **/

$active_pm = apply_filters('leyka_form_pm_order', leyka_get_pm_list(true));
$supported_curr = leyka_get_active_currencies();
$mode = leyka_options()->opt('donation_sum_field_type'); // fixed/flexible/mixed

global $leyka_current_pm; /** @todo Make it a Leyka_Payment_Form class singleton */

leyka_pf_submission_errors();

//add option if we need thumb
$thumb_url = get_the_post_thumbnail_url($campaign_id, 'post-thumbnail');

ob_start();

$currency = "<span class='curr-mark'>&#8381;</span>";
//$currency = "<span class='curr-mark'>РУБ.</span>";

?>
<div id="leyka-pf-<?php echo $campaign_id;?>" class="leyka-pf">
<?php include(LEYKA_PLUGIN_DIR.'assets/svg/svg.svg');?>
<div class="leyka-pf__overlay"></div>

<div class="leyka-pf__module">
<div class="leyka-pf__close leyka-js-close-form">x</div>
<div class="leyka-pf__card inpage-card">
    <?php  if($thumb_url) { //add other terms ?>
        <div class="inpage-card__thumbframe"><div class="inpage-card__thumb" style="background-image: url(<?php echo $thumb_url;?>);"></div></div>
    <?php  } ?>

    <div class="inpage-card__content">
        <div class="inpage-card_title"><?php echo get_the_title($campaign_id);?></div>

        <div class="inpage-card_scale">
            <!-- NB: add class .fin to progress when it's 100% in fav of border-radius -->
            <?php $collected = leyka_get_campaign_collections($campaign_id);
            $target = leyka_get_campaign_target($campaign_id);

            $ready = (isset($target['amount']) && $target['amount']) ? round(100.0*$collected['amount']/$target['amount'], 1) : 0;
            $ready = $ready >= 100.0 ? 100.0 : $ready;?>

            <div class="scale"><div class="progress <?php echo $ready >= 100.0 ? 'fin' : '';?>" style="width:<?php echo $ready;?>%;"></div></div>
            <div class="target">
                <?php echo $collected['amount'];?>
                <span class="curr-mark"><?php echo leyka_options()->opt("currency_{$collected['currency']}_label");?></span>
            </div>

            <div class="info"><?php _e('collected of ', 'leyka');?> <?php echo $target['amount'];?>
                <span class="curr-mark"><?php echo leyka_options()->opt("currency_{$target['currency']}_label");?></span>
            </div>
        </div>

        <div class="inpage-card__note supporters">
            <?php leyka_rev2_get_supporters_list($campaign_id);?>
        </div>

        <div class="inpage-card__action">
            <button type="button" class="leyka-js-open-form"><?php echo leyka_options()->opt('donation_submit_text');?></button>
        </div>
    </div>

    <div class="inpage-card__history history">
        <div class="history__close leyka-js-history-close">x</div>
        <div class="history__title">Мы благодарим</div>
        <div class="history__list">
            <div class="history__list-flow"><?php echo leyka_donation_history_list($campaign_id);?></div>
        </div>
        <div class="history__action">
            <!-- link to full history page -->
            <?php  $all = trailingslashit(get_permalink($campaign_id)).'donations/';?>
            <a href="<?php echo $all;?>">Показать весь список</a>
        </div>
    </div>
</div>

<?php leyka_pf_footer();?>