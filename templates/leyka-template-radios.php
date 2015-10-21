<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Radios
 * Description: Radio options for each payment method
 **/

$active_pm = apply_filters('leyka_form_pm_order', leyka_get_pm_list(true));

leyka_pf_submission_errors();

$curr_pm = leyka_get_pm_by_id(reset($active_pm)->full_id, true);

leyka_setup_current_pm($curr_pm, $curr_pm->default_currency);?>

<div id="leyka-payment-form" class="leyka-tpl-radio">

<div class="leyka-payment-option">
<!-- <?php echo __("This donation form is created by Leyka WordPress plugin, created by Teplitsa of Social Technologies. If you are interested in some way, don't hesitate to write to us: support@te-st.ru", 'leyka');?> -->
    <form class="leyka-pm-form" action="<?php echo leyka_pf_get_form_action();?>" method="post" id="leyka-form-common">

        <div id="amount-selector" class="form-part freeze-fields">
            <?php echo leyka_pf_get_amount_field();?>
        </div>

        <div id="leyka-currency-data">

            <?php echo leyka_pf_get_hidden_fields(empty($campaign) ? false : $campaign->id);?>

            <!-- pm selector -->
            <div id="pm-selector" class="form-part">
                <ul class="leyka-pm-selector">
                <?php foreach($active_pm as $pm) {?>
                    <li <?php if($curr_pm->full_id == $pm->full_id) echo 'class="active"';?>>
                        <label class="radio">
                            <input type="radio"
                                   name="leyka_payment_method"
                                   value="<?php echo esc_attr($pm->full_id);?>"
                                   data-pm_id="<?php echo esc_attr($pm->id);?>" <?php checked($curr_pm->id, $pm->id);?>>
                            <?php echo $pm->label;?>
                        </label>
                    </li>
                <?php }?>
                </ul>
            </div>
        </div>

        <!-- changeable area -->
        <div id="leyka-pm-data" class="changeable-fields form-part">

            <div class="leyka-pm-fields <?php echo esc_attr($curr_pm->full_id);?>">

                <div class="leyka-user-data">
                    <!-- field for GA -->
                    <input type="hidden" name="leyka_ga_payment_method" value="<?php echo esc_attr($curr_pm->label);?>">
                    <?php echo leyka_pf_get_name_field()
                        .leyka_pf_get_email_field()
                        .leyka_pf_get_pm_fields();?>
                </div>

                <?php echo leyka_pf_get_recurring_field()
                    .leyka_pf_get_agree_field()
                    .leyka_pf_get_submit_field();

                $icons = leyka_pf_get_pm_icons();
                if($icons) {
                    echo '<ul class="leyka-pm-icons cf"><li>'.implode('</li><li>', $icons).'</li></ul>';
                }?>
            </div>
            <div class="leyka-pm-desc"><?php echo apply_filters('leyka_the_content', leyka_pf_get_pm_description());?></div>
        </div>

    </form>
</div><!-- .leyka-payment-option -->

<?php if(leyka_options()->opt('show_campaign_sharing')) {
    leyka_share_campaign_block(empty($campaign) ? false : $campaign->id);
}

leyka_pf_footer();?>

</div><!-- #leyka-payment-form -->