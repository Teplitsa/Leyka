<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

$donor_user = get_user_by('id', $_GET['donor']);
if( !$donor_user) {

    wp_redirect(admin_url('admin.php?page=leyka'));
    exit;

}?>

Metabox content here - donor's tags