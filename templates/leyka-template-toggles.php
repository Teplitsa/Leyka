<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Toggles
 * Description: Toggled options for each payment method
 * Deprecated: true
 **/

/** @var $campaign Leyka_Campaign */

$active_pm = apply_filters('leyka_form_pm_order', leyka_get_pm_list(true));

leyka_pf_submission_errors();?>

<div id="leyka-payment-form" class="leyka-tpl-toggles" data-template="toggles" data-leyka-ver="<?php echo esc_attr(Leyka_Payment_Form::get_plugin_ver_for_atts());?>">

<?php $counter = 0;

	foreach($active_pm as $i => $pm) {

	leyka_setup_current_pm($pm);
	$counter++;?>

<div class="leyka-payment-option toggle <?php if($counter == 1) echo 'toggled';?> <?php echo esc_attr($pm->full_id);?>">
    <div class="leyka-toggle-trigger <?php echo count($active_pm) > 1 ? '' : 'toggle-inactive';?>">
        <?php echo wp_kses_post(leyka_pf_get_pm_label());?>
    </div>
    <div class="leyka-toggle-area">
        <form class="leyka-pm-form" action="<?php echo esc_attr(leyka_pf_get_form_action());?>" method="post">

            <div class="leyka-pm-fields">

            <?php
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo leyka_pf_get_amount_field().leyka_pf_get_recurring_field() .(leyka_pf_get_hidden_fields(empty($campaign) ? false : $campaign->id));?>

            <input name="leyka_payment_method" value="<?php echo esc_attr($pm->full_id);?>" type="hidden">
            <input name="leyka_ga_payment_method" value="<?php echo esc_attr($pm->label);?>" type="hidden">
            <div class="leyka-user-data">
            <?php echo leyka_pf_get_name_field() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                .leyka_pf_get_email_field() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                .leyka_pf_get_comment_field() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                .leyka_pf_get_pm_fields(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
            </div>

        <?php 
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo leyka_pf_get_agree_field().leyka_pf_get_submit_field();

            $icons = leyka_pf_get_pm_icons();
            if($icons) {

                $list = [];
                foreach($icons as $i) {
                    $list[] = "<li>{$i}</li>";
                }

                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo '<ul class="leyka-pm-icons cf">'.implode('', $list).'</ul>'; 

            }?>
            </div> <!-- .leyka-pm-fields -->

        <?php echo "<div class='leyka-pm-desc'>".wp_kses_post(apply_filters('leyka_the_content', leyka_pf_get_pm_description()))."</div>"; ?>

        </form>
    </div>
</div>
<?php }?>

<?php if(leyka_options()->opt_template('show_campaign_sharing')) {
    leyka_share_campaign_block(empty($campaign) ? false : $campaign->id);
}

leyka_pf_footer();?>

</div>