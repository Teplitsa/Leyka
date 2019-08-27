<?php
//Exceptions
class TstNotCLIRunException extends Exception {
}

class TstCLIHostNotSetException extends Exception {
}


function leyka_get_wordpress_base_path() {

	$dir = dirname(__FILE__);
	do {
		if(file_exists($dir.'/wp-load.php')) {
			return $dir;
		}
	} while($dir = realpath("$dir/.."));

	return null;

}

define('BASE_PATH', leyka_get_wp_core_path()."/");
define('WP_USE_THEMES', false);
define('WP_CURRENT_THEME', 'teplitsa');


if(php_sapi_name() !== 'cli') {
	throw new TstNotCLIRunException("Should be run from command line!");
}

$options = getopt("", array('host:'));
$tst_host = empty($options['host']) ? 'local.host' : $options['host'];

if(empty($tst_host)) {
	throw new TstCLIHostNotSetException("Host must be defined!");
}
else {
	
    $_SERVER = array(
        "HTTP_HOST" => $tst_host,
        "SERVER_NAME" => $tst_host,
        "REQUEST_URI" => "/",
        "REQUEST_METHOD" => "GET",
        "SERVER_PROTOCOL" => "https",
    );

    //global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;

    if(is_file(BASE_PATH.'core/wp-blog-header.php')) {
        require_once(BASE_PATH.'core/wp-blog-header.php'); // Use actual root path to wp-blog-header.php
    }
    else {
        require_once(BASE_PATH.'wp-blog-header.php');
    }
    header("HTTP/1.0 200 OK");

    fwrite(STDOUT, "HOST: " . $tst_host . chr(10));
    /*
     * ATTENTION!!!!! WP CHANGES CURRENT SYSTEM DATE-TIME TO UTC INSIDE THE SCRIPT!!!!!!!!
     */
    fwrite(STDOUT, "DATETIME: " . date( 'Y-m-d H:i:s' ) . chr(10));
    fwrite(STDOUT, "gmt_offset=" . get_option('gmt_offset') . chr(10));
    fwrite(STDOUT, "script_timezone=" . date('T') . chr(10));
    fwrite(STDOUT, "timezone_string=" . get_option('timezone_string') . chr(10));

}