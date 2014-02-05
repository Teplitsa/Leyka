<?php
/**
 * Leyka Template: Toggles
 * Description: Toggled options for each payment method
 **/

$active_pm = leyka_get_pm_list(true);

leyka_pf_submission_errors();?>

<div id="leyka-payment-form" class="leyka-tpl-toggles">
<?php
	$counter = 0;
	foreach($active_pm as $i => $pm):
	leyka_setup_current_pm($pm);
	$counter++;
?>
<div class="leyka-payment-option toggle <?php if($counter == 1) echo 'toggled';?>">
<div class="toggle-trigger <?php echo count($active_pm) > 1 ? '' : 'toggle-inactive';?>">
    <?php echo leyka_pf_get_pm_label();?>
</div>
<div class="toggle-area">
<form class="leyka-pm-form" id="<?php echo leyka_pf_get_form_id();?>" action="<?php echo leyka_pf_get_form_action();?>" method="post">
<!--	<input type="hidden" name="leyka_debug" value="1" />-->
	
	<div class="leyka-pm-fields">
<?php
	echo leyka_pf_get_amount_field();
	echo leyka_pf_get_hidden_fields();	
?>
	<input name="leyka_payment_method" value="<?php echo esc_attr($pm->full_id);?>" type="hidden" />
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
	if(!empty($icons)){
		$list = array();
		foreach($icons as $i){
			$list[] = "<li>{$i}</li>";
		}

		echo "<ul class='leyka-pm-icons cf'>";
		echo implode('', $list);
		echo "</ul>";
	}
?>
	</div> <!-- .leyka-pm-fields -->	

<?php echo "<div class='leyka-pm-desc'>".apply_filters('leyka_the_content', leyka_pf_get_pm_description())."</div>"; ?>
	
</form>
</div>
</div>
<?php endforeach;?>

<?php leyka_pf_footer();?>

</div><!-- #leyka-payment-form -->