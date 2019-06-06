<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlet: Donations dynamics
 * Description: A portlet to display donations dynamics.
 *
 * Title: Donations dynamics
 * Thumbnail: /img/dashboard/icon-chart.svg
 **/

$data = Leyka_Donations_Dynamics_Portlet_Controller::get_instance()->get_template_data($params);?>


<div class="dynamics-bar-chart">
	<canvas id="leyka-dynamics-chart" width="450" height="150"></canvas>
</div>

<script>
	var leykaDonationsChartData = {
		labels: <?php echo json_encode($data['labels']);?>,
	    datasets: [{
	        data: <?php echo json_encode($data['data']);?>,
			borderWidth: 1,
			backgroundColor: 'rgba(0, 133, 186, 0.2)',
			borderColor: 'rgba(0, 133, 186, 0.2)',
	    }],
	};

	jQuery(function(){
		var ctx = document.getElementById('leyka-dynamics-chart').getContext('2d');
		var myChart = new Chart(ctx, {
			type: 'bar',
		    data: leykaDonationsChartData,
		    options: {
		    	responsive: true,
	            legend: {
	            	display: false,
	            },
	            tooltips: {
		            mode: 'x',
	            	backgroundColor: 'rgba(0, 103, 153, 1)',
	            	bodyFontColor: 'rgba(255, 255, 255, 1)',
	            },
	            scales: {
	                yAxes: [{
	                    ticks: {
	                        beginAtZero: true,
	                        stepSize: 1,
	                        maxTicksLimit: 5,
	                    }
	                }]
	            },	            
		    }
		});	
	});
</script>
