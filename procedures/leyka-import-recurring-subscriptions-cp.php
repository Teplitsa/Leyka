<?php require_once 'procedures-common.php';

if( !defined('WPINC') ) die;


$csv_file_handle = fopen(LEYKA_PLUGIN_DIR.'/Подписки.csv', 'r');

if( !$csv_file_handle ) {
    die('Error: CSV file not found');
}

/** Utility function - strictly for this procedure */
function leyka_find_most_fit_recurring_subscription(array $subscription_donations, $cp_recurring_id) {

    foreach($subscription_donations as $donation) { // First, look for all relevant parameters

        if($donation->cp_recurring_id === $cp_recurring_id && $donation->status === 'funded' && $donation->recurring_is_active) {
            return $donation;
        }

    }

    foreach($subscription_donations as $donation) { // Second, look just for recurring ID

        if($donation->cp_recurring_id === $cp_recurring_id) {
            return $donation;
        }

    }

    foreach($subscription_donations as $donation) { // Third, just take a first Subsctiption w/o any recurring ID

        if( !$donation->cp_recurring_id ) {
            return $donation;
        }

    }

    return false; // In the end, nothing found. Well, we did our best

}

$campaigns_titles2ids = [];

while( ($subscription_data = fgetcsv($csv_file_handle, null, ";")) !== FALSE ) {

    if(mb_stripos($subscription_data[0], '_') === false) { // The first CSV line is of columns names, so skip it
        continue;
    }
    if( !in_array($subscription_data[2], ['Работает','Просрочена',]) ) { // "Просроченные" subscriptions are still active
        continue;
    }

//    $subscription_data[0] - CP subscription ID
//    $subscription_data[1] - Date/time of subscription creation (and for it's rebills)
//    $subscription_data[2] - Subscription status (in russian, i.e. "Работает/Просрочена")
//    $subscription_data[3] - Subscription amount
//    $subscription_data[4] - Subscription currency (CP symbols, i.e. "₽")
//    $subscription_data[5] - Subscription period (in russian, i.e. "Раз в месяц")
//    $subscription_data[6] - Subscription campaign payment title
//    $subscription_data[7] - Subscription donor ID (in fact, it's donor's email copy)
//    $subscription_data[8] - Subscription donor email
//    $subscription_data[9] - Subscription payments number (not including the init payment)
//    $subscription_data[10] - The last subscription payment date/time
//    $subscription_data[11] - The next subscription payment date/time

//    echo '<pre>Looking for subscription: '.print_r($subscription_data[0].', '.$subscription_data[8], 1).'</pre>';

    $campaign_id = false;

    if( !array_key_exists($subscription_data[6], $campaigns_titles2ids) ) { // Find Subscription Campaign by title/payment_title

        $campaign = leyka_get_campaign_by_title($subscription_data[6]);
        $campaigns_titles2ids[$subscription_data[6]] = $campaign ? $campaign->ID : false;

    }
    $campaign_id = $campaigns_titles2ids[$subscription_data[6]];

    if( !$campaign_id ) { // Subscription Campaign not found - skip the Subscription

//        echo '<pre>Campaign not found by title: '.print_r($subscription_data[6], 1).'</pre>';
        continue;

    }

    $params = [
        // Don't filter by some parameters here - better to find any relatively fit Subscription for the Donor (and fix it),
        // then to search more thoroughly from the beginning and find nothing:
//        'status' => 'funded',
//        'recurring_active' => true,
//        'meta' => ['cp_recurring_id' => $subscription_data[0]],
        'recurring_only_init' => true,
        'donor_email' => $subscription_data[8],
        'gateway_pm' => 'cp-card',
        'date_from' => date('d.m.Y 00:00:00', strtotime($subscription_data[1])),
        'date_to' => date('d.m.Y 23:59:59', strtotime($subscription_data[1])),
        'amount_filter' => '='.((float)$subscription_data[3]),
        'campaign_id' => $campaign_id,
    ];

    $subscription_donations = Leyka_Donations::get_instance()->get($params);

    if($subscription_donations) {

        $subscription_donation = leyka_find_most_fit_recurring_subscription($subscription_donations, $subscription_data[0]);

        if($subscription_donation) {

//            echo '<pre>Subscription Donation found: '.print_r($subscription_donation->id.', '.$subscription_donation->donor_email.', '.$subscription_donation->status.', '.(int)$subscription_donation->recurring_is_active, 1).'</pre>';

            $subscription_donation->status = 'funded';
            $subscription_donation->recurring_is_active = true;
            $subscription_donation->cp_recurring_id = $subscription_data[0];

            echo '<pre>Subscription Donation after updates: '.print_r($subscription_donation->id.', '.$subscription_donation->donor_email.', '.$subscription_donation->status.', '.(int)$subscription_donation->recurring_is_active, 1).'</pre>';

        }

    } else {

//        echo '<pre>Subscription Donation not found: '.print_r($subscription_data[0], 1).'</pre>';

        // Insert the new Subscription Donation:
        $donor_name = explode('@', $subscription_data[8]);
        $donor_name = reset($donor_name);

        $params = [
            'status' => 'funded',
            'donor_name' => $donor_name,
            'donor_email' => $subscription_data[8],
            'amount' => round($subscription_data[3], 2),
            'currency_id' => leyka_get_currency_id_by_symbol($subscription_data[4]),
            'campaign_id' => $campaign_id,
            'payment_type' => 'rebill',
            'recurring_is_active' => true,
            'init_recurring_donation' => 0, // It's an Init recurring Donation
            'gateway_id' => 'cp',
            'pm_id' => 'card',
            'payment_title' => $subscription_data[6],
            'date_created' => date('Y-m-d H:i:s', strtotime($subscription_data[1])),
        ];

//        echo '<pre>Inserting by params: '.print_r($params, 1).'</pre>';

        $new_subscription = Leyka_Donations::get_instance()->add($params, true);

//        echo '<pre>Insert result: '.print_r($new_subscription, 1).'</pre>';

        if($new_subscription && !is_wp_error($new_subscription)) {
            $new_subscription->cp_recurring_id = $subscription_data[0];
//            echo '<pre>Inserted Subscription recurring ID: '.print_r($new_subscription->cp_recurring_id, 1).'</pre>';
        }

    }


}

fclose($csv_file_handle);