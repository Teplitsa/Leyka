<?php if( !defined('WPINC') ) die;
/**
 * Leyka Revo Template code extras.
 **/

function leyka_template_revo_get_supporters_list($campaign_id, $max_names = 5) {

    $donations = leyka_get_campaign_donations($campaign_id);
    $first_donors_names = array();
    foreach($donations as $donation) { /** @var $donation Leyka_Donation */

        if(
            $donation->donor_name &&
            !in_array($donation->donor_name, array(__('Anonymous', 'leyka'), 'Anonymous')) &&
            !in_array($donation->donor_name, $first_donors_names)
        ) {
            $first_donors_names[] = mb_ucfirst($donation->donor_name);
        }

        if(count($first_donors_names) >= (int)$max_names) { // 5 is a max number of donors names in a list
            break;
        }

    }

    if(count($first_donors_names)) { // There is at least one donor ?>
        <strong><?php _e('Supporters:', 'leyka');?></strong>
    <?php }

    if(count($donations) <= count($first_donors_names)) { // Only names in the list
        echo implode(', ', array_slice($first_donors_names, 0, -1)).' '.__('and', 'leyka').' '.end($first_donors_names);
    } else { // names list and the number of the rest of donors

        echo implode(', ', array_slice($first_donors_names, 0, -1)).' '.__('and', 'leyka');
        $campaign = get_post($campaign_id);

        $campaign_donations_permalink = trim(get_permalink($campaign_id), '/');
        if(strpos($campaign_donations_permalink, '?')) {
            $campaign_donations_permalink = home_url('?post_type='.Leyka_Donation_Management::$post_type.'&leyka_campaign_filter='.$campaign->post_name);
        } else {
            $campaign_donations_permalink = $campaign_donations_permalink.'/donations/';
        }?>

        <a href="<?php echo $campaign_donations_permalink;?>" class="leyka-js-history-more">
            <?php echo sprintf(__('%d more', 'leyka'), count($donations) - count($first_donors_names));?>
        </a>

    <?php }

}