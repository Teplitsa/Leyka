<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlet: Recurring donations stats
 * Description: A portlet to display simple statistics for the recurring donations.
 *
 * Title: Recurrings
 * Thumbnail: /img/dashboard/icon-money-recurring.svg
 *
 * @var $params
 **/

/** @var $params */
$data = Leyka_Recurring_Stats_Portlet_Controller::get_instance()->get_template_data($params);?>

<div class="portlet-row">
    <div class="row-label"><?php _e('Recurring donations amount', 'leyka');?></div>
    <div class="row-data">

        <?php if( !isset($data['recurring_donations_amount']) ) {?>
            <div class="no-data"><?php _e('No data available', 'leyka');?></div>
        <?php } else {?>

            <div class="main-number"><?php echo number_format($data['recurring_donations_amount'], 0, ".", " ").'&nbsp;'.leyka_get_currency_label();?></div>
            <div class="percent <?php echo $data['recurring_donations_amount_delta_percent'] < 0 ? 'negative' : ($data['recurring_donations_amount_delta_percent'] > 0 ? 'positive' : '');?>"><?php echo str_replace(['+', '-'], '', $data['recurring_donations_amount_delta_percent']);?></div>

        <?php }?>

    </div>
</div>

<div class="portlet-row donations-number-percent-chart">
	<div class="chart-wrapper">
    	<div class="chart">
    		<canvas id="leyka-recurring-chart" width="120" height="120"></canvas>
            <div class="chart-center">
          		<div class="circle-label"><?php echo $data['recurring_donations_number_percent'];?><span class="percent">%</span></div>
            </div>
    	</div>
    	<div class="legend">
    		<div class="legend-item">
    			<span class="icon recurring"></span>
    			<span class="label"><?php _e('Recurrings', 'leyka');?></span>
    		</div>
    		<div class="legend-item">
    			<span class="icon other"></span>
    			<span class="label"><?php _e('Other payments', 'leyka');?></span>
    		</div>
    	</div>
	</div>
</div>

<script>
	var recurringChartData = {
	    datasets: [{
	        data: [parseFloat('<?php echo $data['recurring_donations_number_percent'];?>'), 100 - parseFloat('<?php echo $data['recurring_donations_number_percent'];?>')],
            backgroundColor: [
                'rgba(0, 103, 153, 1)',
                'rgba(37, 160, 209, 1)'
            ],
            borderWidth: 0,
	    }],
	};

	jQuery(function(){
		var ctx = document.getElementById('leyka-recurring-chart').getContext('2d');
		var myChart = new Chart(ctx, {
			type: 'doughnut',
		    data: recurringChartData,
		    options: {
	            rotation: -2,
	            cutoutPercentage: 60,
	            legend: {
	            	display: false,
	            },
		    }
		});	
	});
</script>
