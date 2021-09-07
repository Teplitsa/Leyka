<?php if( !defined('WPINC') ) die;

/** The plugin updates code wrappers. The code works on plugin activation if needed, dependent on the plugin new ver. */

function leyka_create_separate_donations_db_tables() {

    global $wpdb;

    $leyka_last_ver = get_option('leyka_last_ver');
    if($leyka_last_ver && version_compare($leyka_last_ver, '3.18') >= 0) {
        return;
    }

    $charset_collate = $wpdb->get_charset_collate();

    $have_innodb = $wpdb->get_var(
        "SELECT COUNT(ENGINE)
        FROM INFORMATION_SCHEMA.ENGINES
        WHERE ENGINE LIKE 'innodb' AND SUPPORT != 'NO'", ARRAY_A
    );
    $use_innodb = $have_innodb ? 'ENGINE=InnoDB' : '';

    require_once(ABSPATH.'wp-admin/includes/upgrade.php');

    // About SQL format:
    // 1. Each field MUST be in separate line.
    // 2. There must be two spaces between PRIMARY KEY and its name.
    //    E.g.: PRIMARY KEY[space][space](id)
    // Otherwise dbDelta won't work.

    $table_name = $wpdb->prefix.'leyka_donations';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

        $sql = "CREATE TABLE $table_name (
  ID bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  campaign_id bigint(20) UNSIGNED NOT NULL,
  status varchar(20) NOT NULL,
  payment_type varchar(20) NOT NULL,
  date_created datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  gateway_id varchar(30) NOT NULL,
  pm_id varchar(30) NOT NULL,
  currency_id varchar(10) NOT NULL,
  amount float NOT NULL,
  amount_total float NOT NULL,
  amount_in_main_currency float NOT NULL,
  amount_total_in_main_currency float NOT NULL,
  donor_name varchar(100) NOT NULL,
  donor_email varchar(100) NOT NULL,
  PRIMARY KEY  (ID),
  KEY campaign_id_index (campaign_id)
) $charset_collate $use_innodb;";
        dbDelta($sql);

    }

    $table_name = $wpdb->prefix.'leyka_donations_meta';
    if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

        $sql = "CREATE TABLE $table_name (
  meta_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  donation_id bigint(20) UNSIGNED NOT NULL,
  meta_key varchar(255) DEFAULT NULL,
  meta_value longtext,
  PRIMARY KEY  (meta_id),
  KEY donation_id_fk (donation_id),
  KEY meta_key_index (meta_key)
) $charset_collate $use_innodb;";
        dbDelta($sql);

        // Add foreign keys:
        $sql = "ALTER TABLE $table_name
  ADD FOREIGN KEY (donation_id) REFERENCES {$wpdb->prefix}leyka_donations (ID) ON DELETE CASCADE;";
        $wpdb->query($sql);

    }

}