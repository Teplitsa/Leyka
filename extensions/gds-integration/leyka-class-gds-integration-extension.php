<?php if( !defined('WPINC') ) die;
/**
 * Leyka Extension: Google Data Studio extension
 * Version: 1.1
 * Author: Teplitsa of social technologies
 * Author URI: https://te-st.ru
 **/

class Leyka_Gds_Integration_Extension extends Leyka_Extension {

    protected static $_instance;

    protected static $_max_gds_allowed_data_lines = 100000;

    protected function _set_attributes() {

        $this->_id = 'gds_integration';
        $this->_title = __('Google Data Studio');

        // A human-readable short description (for backoffice extensions list page):
        $this->_description = __('Integration of your donations data with Google Data Studio web data visualization service (via MySQL data connector).', 'leyka');

        // A human-readable full description (for backoffice extensions list page):
        $this->_full_description = ''; // 150-300 chars

        global $wpdb;

        // A human-readable description (for backoffice extension settings page):
        $this->_settings_description = __('<p>This extension will help you convert the Leyka donations data to the export format. After this data conversion you may use them to create dashboards, charts and data tables in Google Data Studio.</p>', 'leyka')
        .'<h3>'.__('Set up the Cron job in your hosting Dashboard', 'leyka').'</h3>'
        .'<ul>'
            .'<li>'
                .__('Copy your procedure absolute address:', 'leyka')
                .'<br><code>'.str_replace(basename(__FILE__), 'leyka-gds-data-preparation.php', realpath(__FILE__)).'</code>'
            .'</li>'
            .'<li>'.sprintf(__('Set the Cron job to call the procedure nightly (<a href="%s" target="_blank" class="leyka-outer-link">user manual for setting up Cron jobs</a>, chapter 2)', 'leyka'), 'https://leyka.te-st.ru/docs/gds/').'</li>'
        .'</ul>'
        .'<h3>'.__('When the Cron job is done at least one time, there will be a new data table in your website database', 'leyka').'</h3>'
        .'<ul>'
            .'<li>'.__('The GDS data table name:', 'leyka').'<br><code>'.$wpdb->prefix.'leyka_gds_integration_donations_data</code></li>'
            .'<li>'.sprintf(__('Connect the table and GDS using a MySQL data connector (<a href="%s" target="_blank" class="leyka-outer-link">user manual for creating a GDS data connection</a>, chapter 4)', 'leyka'), 'https://leyka.te-st.ru/docs/gds/').'</li>'
            .'<li>'.sprintf(__('Set up the data visualization in GDS (<a href="%s" target="_blank" class="leyka-outer-link">dashboards & charts setup examples</a>, chapter 5)', 'leyka'), 'https://leyka.te-st.ru/docs/gds/').'</li>'
        .'</ul>';

        // A human-readable description of how to enable the main feature (for backoffice extension settings page):
        $this->_connection_description = '';

        $this->_user_docs_link = 'https://leyka.te-st.ru/docs/gds/';
        $this->_has_wizard = false;
        $this->_has_color_options = false;

    }

    protected function _set_options_defaults() {

        $this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', [
            $this->_id.'_donations_date_period' => [
                'type' => 'select',
                'title' => __('Donations dates period', 'leyka'),
                'description' => __('Choose a donations dates period from which your donations will be prepared to export to Google Data Studio. WARNING: donations data to export will be refreshed only at the closest call of your special data preparing procedure.', 'leyka'),
                'field_classes' => ['leyka-option-field-width-half'],
                'default' => '2_years',
                'list_entries' => [
                    '2_months' => __('Last two months', 'leyka'),
                    '6_months' => __('Last six months', 'leyka'),
                    '1_year' => __('Last one year', 'leyka'),
                    '2_years' => __('Last two years', 'leyka'),
                    'all' => __('For all time', 'leyka'),
                ],
            ],
            $this->_id.'_data_info' => [
                'type' => 'custom_gds_integration_data_info', // Special option type
            ],
        ]);

    }

    protected function _initialize_always() {

        if(is_admin()) {
            add_action('leyka_render_custom_gds_integration_data_info', [$this, '_render_gds_integration_data_info'], 10, 2);
        }

    }

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

        if( !$this->_gds_data_table_exists() ) {
            $this->_gds_data_table_create();
        }

    }

    public function deactivate() { // Remove the special DB table
        $this->_gds_data_table_delete();
    }

    public function _render_gds_integration_data_info($option_id, $data) {

        if( !Leyka_Gds_Integration_Extension::get_instance()->_gds_data_table_exists() ) {
            return;
        }

        $data_lines_count = Leyka_Gds_Integration_Extension::get_instance()->get_donations_to_convert_count();?>

        <div class="leyka-gds-integration-info-wrapper">

            <?php if($data_lines_count > Leyka_Gds_Integration_Extension::get_instance()->get_max_gds_allowed_lines()) {?>

                <div class="leyka-gds-data-error">
                    <?php echo sprintf(
                        __("WARNING: we can't use this donations selection. The GDS limit of data lines is exceeded (<strong>%s / %s</strong>). Try to select a more narrow period.", 'leyka'),
                        leyka_amount_format($data_lines_count),
                        leyka_amount_format(Leyka_Gds_Integration_Extension::get_instance()->get_max_gds_allowed_lines())
                    );?>
                </div>

            <?php } else {?>

                <div class="leyka-gds-data-info">
                    <?php echo sprintf(
                        __('Total donations to convert: <strong>%s / %s</strong>', 'leyka'),
                        leyka_amount_format($data_lines_count),
                        leyka_amount_format(Leyka_Gds_Integration_Extension::get_instance()->get_max_gds_allowed_lines())
                    );?>
                </div>

            <?php }

            $timestamp = get_transient('leyka_gds_integration_last_data_preparing_date');
            $timestamp = $timestamp ? strtotime($timestamp) : false;
            $last_procedure_run_date = $timestamp ?
                date(get_option('date_format'), $timestamp).', '.date(get_option('time_format'), $timestamp) : __('no', 'leyka');?>

            <div class="leyka-gds-data-info">
                <?php echo sprintf(__('Last successful data preparation date: <strong>%s</strong>', 'leyka'), $last_procedure_run_date);?>
            </div>

        </div>

    <?php }

    public function get_max_gds_allowed_lines() {
        return self::$_max_gds_allowed_data_lines;
    }

    public function _gds_data_table_exists() {

        global $wpdb;
        return $wpdb->get_row("SHOW TABLES LIKE '{$wpdb->prefix}leyka_gds_integration_donations_data'");

    }

    public function _gds_data_table_create() {

        global $wpdb;

        $wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}leyka_gds_integration_donations_data`;");
        $wpdb->query("CREATE TABLE `{$wpdb->prefix}leyka_gds_integration_donations_data` (
          `ID` bigint(20) UNSIGNED NOT NULL,
          `donation_date` datetime NOT NULL,
          `payment_type` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
          `gateway_title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
          `pm_title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
          `currency_label` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
          `amount` float NOT NULL,
          `amount_total` float NOT NULL,
          `status` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
          `campaign_title` text COLLATE utf8mb4_unicode_ci NOT NULL,
          `donor_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
          `donor_email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
          `donor_has_account` BOOLEAN,
          `donor_has_recurring_subscriptions` BOOLEAN) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    }

    public function _gds_data_table_insert(Leyka_Donation_Base $donation) {

        global $wpdb;

        $donor_has_recurring_subscriptions = $donation->donor_id ? Leyka_Donations::get_instance()->get_count([
            'donor_id' => $donation->donor_id,
            'status' => 'funded',
            'recurring_only_init' => true,
            'recurring_active' => true,
        ]) : false;

        return $wpdb->insert(
            "{$wpdb->prefix}leyka_gds_integration_donations_data",
            [
                'ID' => $donation->id,
                'donation_date' => date('Y-m-d H:i:s', $donation->date_timestamp),
                'payment_type' => $donation->payment_type,
                'gateway_title' => $donation->gateway_label,
                'pm_title' => $donation->pm_label,
                'currency_label' => $donation->currency_label,
                'amount' => $donation->amount,
                'amount_total' => $donation->amount_total,
                'status' => $donation->status,
                'campaign_title' => $donation->campaign_title,
                'donor_name' => $donation->donor_name,
                'donor_email' => $donation->donor_email,
                'donor_has_account' => absint($donation->donor_id) ? 1 : null,
                'donor_has_recurring_subscriptions' => $donor_has_recurring_subscriptions > 0 ? 1 : null,
            ],
            ['%d', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%d', '%d',]
        );

    }

    public function _gds_data_table_delete() {

        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}leyka_gds_integration_donations_data`;");

    }

    public function _gds_data_table_clear() {

        global $wpdb;
        $wpdb->query("TRUNCATE `{$wpdb->prefix}leyka_gds_integration_donations_data`");

    }

    public function get_donations_to_convert() {

        return Leyka_Donations::get_instance()->get(apply_filters('leyka_gds_integration_donation_query_params', [
            'date_query' => [$this->_get_date_query()],
        ]));

    }

    public function get_donations_to_convert_count() {

        return Leyka_Donations::get_instance()->get_count(apply_filters('leyka_gds_integration_donation_query_params', [
            'date_query' => [$this->_get_date_query()],
        ]));

    }

    protected function _get_date_query() {

        switch(leyka_options()->opt($this->_id.'_donations_date_period')) {
            case '2_months':
                return ['after' => '-2 month', 'inclusive' => true,];
            case '6_months':
                return ['after' => '-6 month', 'inclusive' => true,];
            case '1_year':
                return ['after' => '-1 year', 'inclusive' => true,];
            case 'all':
                return [];
            case '2_years':
            default:
                return ['after' => '-2 year', 'inclusive' => true,];
        }

    }

}

function leyka_add_extension_gds_integration() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Gds_Integration_Extension::get_instance());
}

add_action('leyka_init_actions', 'leyka_add_extension_gds_integration');