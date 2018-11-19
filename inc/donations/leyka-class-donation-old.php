<?php if( !defined('WPINC') ) die;

require_once(LEYKA_PLUGIN_DIR.'inc/donations/leyka-class-donation-post.php');

/**
 * Old donation class - a clone of Leyka_Donation_Post, added for backward-compatibility.
 *
 * @deprecated Use Leyka_Donations_Factory::get_instance()->getDonation($donation) instead.
 */
class Leyka_Donation extends Leyka_Donation_Post {
}