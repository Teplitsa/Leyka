<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Radios
 * Description: Radio options for each payment method
 * Deprecated: true
 **/

/** @var $campaign Leyka_Campaign */

$active_pm_list = apply_filters('leyka_form_pm_order', leyka_get_pm_list(true));

$active_currencies = [];
foreach($active_pm_list as $pm) {
    $active_currencies = $active_currencies + $pm->currencies;
}

$pm_forms = [];
foreach($active_pm_list as $pm) {
    $pm_forms[$pm->full_id] = new Leyka_Payment_Form($pm, $pm->default_currency);
}

leyka_pf_submission_errors();

$curr_pm = leyka_get_pm_by_id(reset($active_pm_list)->full_id, true);

leyka_setup_current_pm($curr_pm, $curr_pm->default_currency);
$campaign = leyka_get_validated_campaign($campaign);?>

<div id="leyka-payment-form" class="leyka-tpl-radio" data-template="radio" data-leyka-ver="<?php echo esc_attr(Leyka_Payment_Form::get_plugin_ver_for_atts());?>">

<div class="leyka-payment-option">

    <form class="leyka-pm-form" action="<?php echo esc_attr(leyka_pf_get_form_action());?>" method="post" id="leyka-form-common">

        <div class="form-part freeze-fields">
            <?php foreach($active_pm_list as $pm) {?>
            <div class="pm-amount-field <?php echo esc_attr( $pm->full_id );?>" <?php echo wp_kses_post( $curr_pm->full_id == $pm->full_id ? '' : 'style="display:none;"' );?>>
                <?php echo wp_kses_post( $pm_forms[$pm->full_id]->get_amount_field().$pm_forms[$pm->full_id]->get_recurring_field() );?>
            </div>
            <?php }?>
            <span class="currency-var rur" style="display: none;"></span>
        </div>

        <div class="leyka-pm-list">

            <div class="leyka-hidden-fields">
                <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo leyka_pf_get_common_hidden_fields($campaign);

                foreach($active_pm_list as $pm) {?>
                <div class="pm-hidden-field <?php echo esc_attr( $pm->full_id );?>" <?php echo wp_kses_post( $curr_pm->full_id == $pm->full_id ? '' : 'style="display:none;"' );?>>
                    <?php 
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo leyka_pf_get_pm_hidden_fields($campaign, $pm_forms[$pm->full_id]);
                    ?>
                </div>
                <?php }?>
            </div>

            <!-- pm selector -->
            <div class="pm-selector form-part">
                <ul class="leyka-pm-selector">
                <?php foreach($active_pm_list as $pm) {?>
                    <li class="leyka-pm-variant <?php echo esc_attr( $curr_pm->full_id == $pm->full_id ? 'active' : '' );?>">
                        <label class="radio">
                            <input type="radio"
                                   name="leyka_payment_method"
                                   value="<?php echo esc_attr($pm->full_id);?>"
                                   data-pm_id="<?php echo esc_attr($pm->id);?>" <?php checked($curr_pm->id, $pm->id);?>
                                   data-curr-supported="<?php echo esc_html(implode(',', $pm->currencies));?>">
                            <?php echo esc_html( $pm->label );?>
                        </label>
                    </li>
                <?php }?>
                </ul>
            </div>
        </div>

        <div id="leyka-pm-data" class="changeable-fields form-part">
            
        <?php foreach($active_pm_list as $pm) {?>

            <div class="leyka-pm-fields <?php echo esc_attr($pm->full_id);?>" <?php echo wp_kses_post( $curr_pm->full_id == $pm->full_id ? '' : 'style="display:none;"' );?>>

                <div class="leyka-user-data">
                    <!-- field for GA -->
                    <input type="hidden" name="leyka_ga_payment_method" value="<?php echo esc_attr($pm->label);?>">
                    <?php echo wp_kses( $pm_forms[$pm->full_id]->get_name_field()
                        .$pm_forms[$pm->full_id]->get_email_field()
                        .$pm_forms[$pm->full_id]->get_comment_field()
                        .$pm_forms[$pm->full_id]->get_pm_fields(), 'content');?>
                </div>

                <?php echo wp_kses( $pm_forms[$pm->full_id]->get_agree_field().$pm_forms[$pm->full_id]->get_submit_field(), 'content' );

                $icons = $pm_forms[$pm->full_id]->get_pm_icons();
                if($icons) {?>
                    <ul class="leyka-pm-icons cf"><li><?php echo wp_kses_post(implode('</li><li>', $icons));?></li></ul>
                <?php }?>
            </div>

            <div class="leyka-pm-desc <?php echo esc_attr($pm->full_id);?>" <?php echo wp_kses_post( $curr_pm->full_id == $pm->full_id ? '' : 'style="display:none;"' );?>>
                <?php echo wp_kses_post(apply_filters('leyka_the_content', $pm_forms[$pm->full_id]->get_pm_description()));?>
            </div>

        <?php }?>

        </div>

    </form>
</div>

<?php if(leyka_options()->opt_template('show_campaign_sharing')) {
    leyka_share_campaign_block(empty($campaign) ? false : $campaign->id);
}

leyka_pf_footer();?>

</div>