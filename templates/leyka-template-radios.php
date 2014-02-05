<?php
/**
 * Leyka Template: Radios
 * Description: Radio options for each payment method
 **/

$active_pm = leyka_get_pm_list(true);

leyka_pf_submission_errors();

// current state
$curr_pm = leyka_get_pm_by_id(reset($active_pm)->full_id, true);

leyka_setup_current_pm($curr_pm, $curr_pm->default_currency);?>

<div id="leyka-payment-form" class="leyka-tpl-radio">

<div class="leyka-payment-option">
<form class="leyka-pm-form" action="<?php echo leyka_pf_get_form_action();?>" method="post" id="leyka-form-common">
	
<!--	<div id="amount-selector" class="form-part freeze-fields">-->
	<div id="amount-selector" class="form-part freeze-fields">
		<?php echo leyka_pf_get_amount_field();?>		
	</div>

	<div id="leyka-currency-data">

		<?php echo leyka_pf_get_hidden_fields();?>		

		<!-- pm selector -->
		<div id="pm-selector" class="form-part">
			<ul class="leyka-pm-selector">
            <?php foreach($active_pm as $pm) {?>
                <li <?php if($curr_pm->id == $pm->id) echo 'class="active"';?>>
                    <label class="radio">
                        <input type="radio" name="leyka_payment_method" value="<?php echo esc_attr($pm->full_id);?>" <?php checked($curr_pm->id, $pm->id); ?> data-pm_id="<?php echo esc_attr($pm->id);?>" />
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
			<?php
				echo "<div class='leyka-pm-desc'>".apply_filters('leyka_the_content', leyka_pf_get_pm_description())."</div>";
			?>
		</div>

	</div> <!-- #currecy data -->

</form>
</div><!-- .leyka-payment-option -->

<?php leyka_pf_footer();?>

</div><!-- #leyka-payment-form -->