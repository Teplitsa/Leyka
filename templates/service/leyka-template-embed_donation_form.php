<?php if( !defined('WPINC') ) die;
/**
 * Leyka Template: Embed Donation Forms.
 * Description: A template for an embed donation forms. On a main website, normally, it is not in use.
 **/


$cpost = get_post();

?>
<!DOCTYPE html>
<html class="embed" <?php language_attributes(); ?>>
<head>
<style>
	* {
		margin: 0;
		padding: 0;
		-moz-box-sizing: border-box;
		-webkit-box-sizing: border-box;
		box-sizing: border-box;
	}
	
	#embedded-card {
		width: 296px;		
		font: 14px/21px "HelveticaNeue", "Helvetica Neue", Helvetica, Arial, sans-serif;
		color: #444;		
	}
	
	a, a:visited {
		color: #1db318;
		text-decoration: none;
	}
	
	a:hover, a:focus, a:active {
		color: #189414;
	}
	
	.leyka-campaign-card  {
		border: 1px solid #dfdfdf;
	}
	
	.lk-thumbnail {
		width: 100%;		
	}
	
		.lk-thumbnail a { 
			display: block;
		}
		
		.lk-thumbnail img {
			width: 100%;
			height: auto;
		}
		
	.lk-info {
		padding: 15px;
	}
	
	.lk-title {
		font-weight: bold;
		font-size: 1.35em;
		line-height: 1.2;
		color: #111;
		margin-bottom: 0.5em;
		max-height: 50px;		
	}
	
		.lk-title a, .lk-title a:visited {
			color: #111;
		}
		
		.lk-title a:hover, .lk-title a:focus, .lk-title a:active {
			color: #189414;
		}
	
	
	.lk-title + p {
		max-height: 110px;
		overflow: hidden;
		position: relative;
	}
	
	.lk-title + p:before {
		content: '';		
		display: block;
		width: 100%;
		height: 40px;
		position: absolute;
		left: 0;
		bottom: 0;
		background: -moz-linear-gradient(top,  rgba(255,255,255,0) 0%, rgba(255,255,255,1) 100%); 
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(255,255,255,0)), color-stop(100%,rgba(255,255,255,1)));
		background: -webkit-linear-gradient(top,  rgba(255,255,255,0) 0%,rgba(255,255,255,1) 100%); 
		background: -o-linear-gradient(top,  rgba(255,255,255,0) 0%,rgba(255,255,255,1) 100%); 
		background: -ms-linear-gradient(top,  rgba(255,255,255,0) 0%,rgba(255,255,255,1) 100%); 
		background: linear-gradient(to bottom,  rgba(255,255,255,0) 0%,rgba(255,255,255,1) 100%); 
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#00ffffff', endColorstr='#ffffff',GradientType=0 ); 
	}
	
	.leyka-scale {
		border-top: 1px solid #dfdfdf;
		padding: 15px;
	}
	
	.leyka-scale-scale {
		height: 8px;
		width: 100%;
	}
	
		.leyka-scale-scale .target {
			background: #f1f1f1;
			height: 100%;
			width: 100%;
		}
		
		.leyka-scale-scale .collected {
			background: #1db318;
			height: 100%;			
		}	
	
	.leyka-scale-label {
		font-size: 0.85em;
		padding-top: 4px;
		color: #888;
	}
	
		.leyka-scale-label b {
			color: #111;
		}
	
	.leyka-scale-button {
		text-align: center;
		margin-top: 15px;
	}
	
	.leyka-scale-button a,
	.leyka-scale-button a:visited {
		display: inline-block;
		text-transform: uppercase;
		color: #fff;
		background: #1db318;
		padding: 0.5em 1.5em;
		-webkit-transition: all 0.3s ease;
		-moz-transition: all 0.3s ease;
		-ms-transition: all 0.3s ease;
		-o-transition: all 0.3s ease;
		transition: all 0.3s ease;
	}
	
	.leyka-scale-button a:hover,
	.leyka-scale-button a:focus,
	.leyka-scale-button a:active {
		background: #189414;
	}
	
</style>
</head>
<body>
<div id="embedded-donation-form">
<?php
	echo leyka_get_payment_form($cpost->ID);
?>
</div>
</body>

</html>