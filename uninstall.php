<?php if(!defined('WP_UNINSTALL_PLUGIN')) exit; // if uninstall.php is not called by WordPress, die
/**
 * Fired when the plugin is uninstalled.
 *
 * Plugin Name: Leyka
 * Plugin URI:  https://leyka.te-st.ru/
 * Author:      Lev Zvyagintsev
 * Author URI:  ahaenor@gmail.com
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

if(empty($_POST['cleanup'])) {?>
    <h2>
        <?php _e('Should Leyka delete all its settings, campaigns and donations data from the website database?', 'leyka');?>
    </h2>
    <form action="#" method="post">
        <div class="leyka-remove-data">
            <input type="submit" name="cleanup[y]" value="<?php _e('YES, delete all Leyka database presence', 'leyka');?>">
        </div>
        <div class="leyka-leave-data">
            <input type="submit" name="cleanup[n]" value="<?php _e("NO, leave Leyka database entries", 'leyka');?>">
        </div>
        <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('leyka_delete_plugin');?>">
    </form>
<?php } else if($_POST['cleanup'] && !empty($_POST['cleanup']['y'])) {
    echo '<pre>' . print_r('DO DB CLEANUP!', 1) . '</pre>';?>

<?php } else if($_POST['cleanup'] && !empty($_POST['cleanup']['n'])) {
    echo '<pre>' . print_r('JUST DELETE PLUGIN FILES!', 1) . '</pre>'?>

<?php }
//$option_name = 'wporg_option';
//
//delete_option($option_name);
//
//// for site options in Multisite
//delete_site_option($option_name);
//
//// drop a custom database table
//global $wpdb;
//$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mytable"); // If this file is called directly, abort

// Uninstall functionality here...