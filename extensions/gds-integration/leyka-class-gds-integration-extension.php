<?php if( !defined('WPINC') ) die;
/**
 * Leyka Extension: Google Data Studio extension
 * Version: 1.0
 * Author: Teplitsa of social technologies
 * Author URI: https://te-st.ru
 **/

class Leyka_Gds_Integration_Extension extends Leyka_Extension {

    protected static $_instance;

    protected function _set_attributes() {

        $this->_id = 'gds_integration';
        $this->_title = __('Google Data Studio');

        // A human-readable short description (for backoffice extensions list page):
        $this->_description = __('Integration of your donations data with Google Data Studio web data visualization service (via MySQL data connector).', 'leyka');

        // A human-readable full description (for backoffice extensions list page):
        $this->_full_description = ''; // 150-300 chars

        // A human-readable description (for backoffice extension settings page):
        $this->_settings_description = '';

        // A human-readable description of how to enable the main feature (for backoffice extension settings page):
        $this->_connection_description = '<p>Подробнее о подключении сайта с Лейкой к Google Data Studio <a href="" target="_blank">читайте здесь</a></p>';

//        $this->_user_docs_link = '//your-site.org/extension-manual'; // Extension user manual page URL
        $this->_has_wizard = false;
        $this->_has_color_options = false;

    }

    protected function _set_options_defaults() {

        $this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', array(
            $this->_id.'_donations_date_period' => array(
                'type' => 'select',
                'title' => __('Donations dates period', 'leyka'),
                'description' => __('Choose a donations dates period from which your donations will be prepared to export to Google Data Studio. WARNING: the donations data to export will be refreshed only at the closest call of your special data preparing procedure.', 'leyka'),
                'default' => '2_years',
                'list_entries' => array(
                    '2_months' => __('Last two months', 'leyka'),
                    '6_months' => __('Last six months', 'leyka'),
                    '1_year' => __('Last one year', 'leyka'),
                    '2_years' => __('Last two years', 'leyka'),
                    'all' => __('For all time', 'leyka'),
                ),
            ),
        ));

    }

    /** Will be called only if the Extension is active. */
    protected function _initialize_active() {

        // Add the data preparing procedure to Leyka (to make it's browser calling possible):
        add_filter('leyka_procedure_address', function($procedure_absolute_address, $procedure_id, $params){

            if($procedure_id !== 'gds-data-preparation') {
                return $procedure_absolute_address;
            }

            return LEYKA_PLUGIN_DIR.'/extensions/gds-integration/leyka-gds-data-preparation.php';

        }, 10, 3);

    }

    public function activate() { // Create the special DB table for the GDS-prpared Donations data, if needed

        global $wpdb;

        if( !$wpdb->get_row("SHOW TABLES LIKE '{$wpdb->prefix}leyka_gds_integration_donations_data'") ) {

            $wpdb->query("CREATE TABLE `{$wpdb->prefix}leyka_gds_integration_donations_data` (
  `ID` bigint(20) UNSIGNED NOT NULL,
  `donation_date` datetime NOT NULL,
  `payment_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway_title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pm_title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_label` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `amount` float NOT NULL,
  `amount_total` float NOT NULL,
  `status` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `campaign_title` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `donor_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `donor_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `donor_has_account` BOOLEAN NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

        }

    }

    public function deactivate() { // Remove the special DB table

        global $wpdb;
        $wpdb->query("DROP TABLE `{$wpdb->prefix}leyka_gds_integration_donations_data`");

    }

}

function leyka_add_extension_gds_integration() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Gds_Integration_Extension::get_instance());
}

add_action('leyka_init_actions', 'leyka_add_extension_gds_integration');