<?php if( !defined('WPINC') ) die;

/** The plugin-specific DB actions code. Works on plugin activation */

$leyka_last_ver = get_option('leyka_last_ver');
global $wpdb;

if( !$leyka_last_ver || $leyka_last_ver < '4.0' ) { // Create separated donations tables

    $charset_collate = $wpdb->get_charset_collate();

    $have_innodb = $wpdb->get_results("SHOW VARIABLES LIKE 'have_innodb'", ARRAY_A);
    $use_innodb = ($have_innodb[0]['Value'] == 'YES') ? 'ENGINE=InnoDB' : '';

    require_once(ABSPATH.'wp-admin/includes/upgrade.php');

    // sql to create your table
    // NOTICE that:
    // 1. each field MUST be in separate line
    // 2. There must be two spaces between PRIMARY KEY and its name
    //    Like this: PRIMARY KEY[space][space](id)
    // otherwise dbDelta will not work
    $sql = "CREATE TABLE {$wpdb->prefix}leyka_donations (
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

    $sql = "CREATE TABLE {$wpdb->prefix}leyka_donations_meta (
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
    $sql = "ALTER TABLE {$wpdb->prefix}leyka_donations_meta
      ADD FOREIGN KEY (donation_id) REFERENCES {$wpdb->prefix}leyka_donations (ID) ON DELETE CASCADE;";
    $wpdb->query($sql);

}