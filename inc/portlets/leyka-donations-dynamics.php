<?php if( !defined('WPINC') ) die;
/**
 * Leyka Portlet: Donations dynamics
 * Description: A portlet to display donations dynamics.
 *
 * Title: Donations dynamics
 * Thumbnail: /img/dashboard/icon-chart.svg
 *
 * @var $params
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
	            backgroundColor: '#FF0000',
	            borderColor: '#ff0000',
	            tooltips: {
		            mode: 'x',
	            	backgroundColor: 'rgba(0, 103, 153, 1)',
	            	bodyFontColor: 'rgba(255, 255, 255, 1)',
	            },
	            scales: {
                    xAxes: [{ 
                  		gridLines: {
							color: '#F1F1F1',
							//zeroLineColor: 'rgba(37, 160, 209, 0.8)',
              			},
                    	ticks: {
                    		fontColor: "#44444A",
                    		fontFamily: "'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'",
                    	},
                    }],		            
	                yAxes: [{
                        gridLines: {
							color: '#F1F1F1',
							//zeroLineColor: 'rgba(37, 160, 209, 0.8)',
							zeroLineColor: '#F1F1F1',
                        },
	                    ticks: {
	                    	fontColor: "#44444A",
	                        beginAtZero: true,
	                        stepSize: 1,
	                        maxTicksLimit: 5,
	                        fontFamily: "'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol'",
	                    }
	                }]
	            },	            
		    }
		});	
	});
</script>
