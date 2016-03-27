<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Radios
 * Description: Radio options for each payment method
 **/

$active_pm_list = apply_filters('leyka_form_pm_order', leyka_get_pm_list(true));

$active_currencies = array();
foreach($active_pm_list as $pm) {
    $active_currencies = $active_currencies + $pm->currencies;
}

$pm_forms = array();
foreach($active_pm_list as $pm) {
    $pm_forms[$pm->full_id] = new Leyka_Payment_Form($pm, $pm->default_currency);
}

leyka_pf_submission_errors();

$curr_pm = leyka_get_pm_by_id(reset($active_pm_list)->full_id, true);

leyka_setup_current_pm($curr_pm, $curr_pm->default_currency);
$campaign = leyka_get_validated_campaign($campaign);?>

<div id="leyka-payment-form" class="leyka-tpl-radio" data-template="radio">

<div class="leyka-payment-option">
<!-- <?php echo __("This donation form is created by Leyka WordPress plugin, created by Teplitsa of Social Technologies. If you are interested in some way, don't hesitate to write to us: support@te-st.ru", 'leyka');?> -->
    <form class="leyka-pm-form" action="<?php echo leyka_pf_get_form_action();?>" method="post" id="leyka-form-common">

        <div class="amount-selector" class="form-part freeze-fields">
            <?php foreach($active_pm_list as $pm) {?>
            <div class="pm-amount-field <?php echo $pm->full_id;?>" <?php echo $curr_pm->full_id == $pm->full_id ? '' : 'style="display:none;"';?>>
                <?php echo $pm_forms[$pm->full_id]->get_amount_field();?>
            </div>
            <?php }?>
            <span class="currency-var rur" style="display: none;"></span>
        </div>

        <div id="leyka-pm-list">

            <div class="leyka-hidden-fields">
                <?php foreach($active_pm_list as $pm) {?>
                <div class="pm-hidden-field <?php echo $pm->full_id;?>" <?php echo $curr_pm->full_id == $pm->full_id ? '' : 'style="display:none;"';?>>
                    <?php echo $pm_forms[$pm->full_id]->get_hidden_fields($campaign);?>
                </div>
                <?php }?>
            </div>

            <!-- pm selector -->
            <div id="pm-selector" class="form-part">
                <ul class="leyka-pm-selector">
                <?php foreach($active_pm_list as $pm) {?>
                    <li class="leyka-pm-variant <?php echo $curr_pm->full_id == $pm->full_id ? 'active' : '';?>">
                        <label class="radio">
                            <input type="radio"
                                   name="leyka_payment_method"
                                   value="<?php echo esc_attr($pm->full_id);?>"
                                   data-pm_id="<?php echo esc_attr($pm->id);?>" <?php checked($curr_pm->id, $pm->id);?>
                                   data-curr-supported="<?php echo implode(',', $pm->currencies);?>">
                            <?php echo $pm->label;?>
                        </label>
                    </li>
                <?php }?>
                </ul>
            </div>
        </div>

        <!-- changeable area -->
        <div id="leyka-pm-data" class="changeable-fields form-part">

        <?php foreach($active_pm_list as $pm) {?>

            <div class="leyka-pm-fields <?php echo esc_attr($pm->full_id);?>" <?php echo $curr_pm->full_id == $pm->full_id ? '' : 'style="display:none;"';?>>

                <div class="leyka-user-data">
                    <!-- field for GA -->
                    <input type="hidden" name="leyka_ga_payment_method" value="<?php echo esc_attr($pm->label);?>">
                    <?php echo $pm_forms[$pm->full_id]->get_name_field()
                        .$pm_forms[$pm->full_id]->get_email_field()
                        .$pm_forms[$pm->full_id]->get_pm_fields();?>
                </div>

                <?php echo $pm_forms[$pm->full_id]->get_agree_field().$pm_forms[$pm->full_id]->get_submit_field();

                $icons = $pm_forms[$pm->full_id]->get_pm_icons();
                if($icons) {?>
                    <ul class="leyka-pm-icons cf"><li><?php echo implode('</li><li>', $icons);?></li></ul>
                <?php }?>
            </div>

            <div class="leyka-pm-desc <?php echo esc_attr($pm->full_id);?>" <?php echo $curr_pm->full_id == $pm->full_id ? '' : 'style="display:none;"';?>>
                <?php echo apply_filters('leyka_the_content', $pm_forms[$pm->full_id]->get_pm_description());?>
            </div>

        <?php }?>

        </div>

    </form>
</div><!-- .leyka-payment-option -->

<?php if(leyka_options()->opt('show_campaign_sharing')) {
    leyka_share_campaign_block(empty($campaign) ? false : $campaign->id);
}

leyka_pf_footer();?>

</div><!-- #leyka-payment-form -->