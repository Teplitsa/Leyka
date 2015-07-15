<?php if( !defined('WPINC') ) die;
/** Different ajax handler functions */

function leyka_submit_donation() {

    if(empty($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'leyka_payment_form')) {
        die(json_encode(array(
            'status' => 0,
            'message' => __('Wrong nonce in submitted form data', 'leyka'),
        )));
    }

    leyka()->clear_session_errors(); // Clear all previous submits errors, if there are some

    $pm = explode('-', $_POST['leyka_payment_method']);
    if( !$pm || count($pm) < 2 ) {
        die(json_encode(array(
            'status' => 0,
            'message' => __('Wrong gateway or/and payment method in submitted form data', 'leyka'),
        )));
    }

    $donation_id = leyka()->log_submission();

    do_action('leyka_payment_form_submission-'.$pm[0], $pm[0], implode('-', array_slice($pm, 1)), $donation_id, $_POST);

    $payment_vars = array_merge(
        apply_filters('leyka_submission_form_data-'.$pm[0], $_POST, $pm[1], $donation_id),
        array('status' => 1, 'donation_id' => $donation_id,)
    );

//    echo '<pre>' . print_r($payment_vars, 1) . '</pre>';

    die(json_encode($payment_vars));
}
add_action('wp_ajax_leyka_ajax_donation_submit', 'leyka_submit_donation');
add_action('wp_ajax_nopriv_leyka_ajax_donation_submit', 'leyka_submit_donation');

function leyka_get_campaigns_list() {

    if(empty($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'leyka_get_campaigns_list_nonce')) {
        die(json_encode(array()));
    }

    $_REQUEST['term'] = empty($_REQUEST['term']) ? '' : trim($_REQUEST['term']);

    $campaigns = get_posts(array(
        'post_type' => Leyka_Campaign_Management::$post_type,
        'post_status' => 'publish',
        'meta_query' => array(array(
            'key' => 'payment_title', 'value' => $_REQUEST['term'], 'compare' => 'LIKE', 'type' => 'CHAR',
        )),
    ));

    if( !$campaigns)
        $campaigns = get_posts(array(
            'post_type' => Leyka_Campaign_Management::$post_type,
            'post_status' => 'publish',
            's' => empty($_REQUEST['term']) ? '' : trim($_REQUEST['term'])
        ));

    foreach($campaigns as $index => $campaign) {
        $campaigns[$index] = array(
            'value' => $campaign->ID,
            'label' => $campaign->post_title,
            'payment_title' => get_post_meta($campaign->ID, 'payment_title', true)
        );
    }

    die(json_encode($campaigns));
}
add_action('wp_ajax_leyka_get_campaigns_list', 'leyka_get_campaigns_list');
add_action('wp_ajax_nopriv_leyka_get_campaigns_list', 'leyka_get_campaigns_list');

/** AJAX for donation form templates **/

function leyka_payment_method_action() {

    check_ajax_referer('leyka_payment_form', '_leyka_ajax_nonce');

    if(empty($_POST['pm_id'])) {
        die('-1');
    }

    $curr_currency = trim($_POST['currency']);
    $curr_pm = leyka_get_pm_by_id(trim($_POST['pm_id']));

    if( !$curr_pm ) {
        die('-1');
    }

    leyka_setup_current_pm($curr_pm, $curr_currency);

    ob_start();?>

    <div class="leyka-pm-fields">

        <div class='leyka-user-data'>
            <!-- field for GA -->
            <input type="hidden" name="leyka_ga_payment_method" value="<?php echo esc_attr($curr_pm->label);?>" />
            <?php
            echo leyka_pf_get_name_field(empty($_POST['user_name']) ? '' : trim($_POST['user_name']));
            echo leyka_pf_get_email_field(empty($_POST['user_email']) ? '' : trim($_POST['user_email']));
            echo leyka_pf_get_pm_fields();
            ?>
        </div>

        <?php
        echo leyka_pf_get_agree_field();
        echo leyka_pf_get_submit_field();

        $icons = leyka_pf_get_pm_icons();
        if($icons) {
            $list = array();
            foreach($icons as $i) {
                $list[] = "<li>{$i}</li>";
            }

            echo '<ul class="leyka-pm-icons cf">'.implode('', $list).'</ul>';
        }?>
    </div>
    <?php echo "<div class='leyka-pm-desc'>".apply_filters('leyka_the_content', leyka_pf_get_pm_description())."</div>";
    $out = ob_get_contents();
    ob_end_clean();

    $payment_form = new Leyka_Payment_Form($curr_pm, $curr_currency);
    echo json_encode(array('pm' => $out, 'currency' => $payment_form->get_currency_field()));
    die();
}
add_action('wp_ajax_leyka_payment_method', 'leyka_payment_method_action');
add_action('wp_ajax_nopriv_leyka_payment_method', 'leyka_payment_method_action');

function leyka_currency_choice_action() {

    check_ajax_referer('leyka_payment_form', '_leyka_ajax_nonce');

    if(empty($_POST['currency']))
        die('-1');

    $curr_currency = trim($_POST['currency']);
    $pm_selected = trim($_POST['current_pm']);
    $currently_active_pmethods = leyka_get_pm_list(true, $curr_currency);

    $curr_pm_is_active = false;
    foreach($currently_active_pmethods as $pm) {
        if($pm->id == $pm_selected) {
            $curr_pm_is_active = true;
            $pm_selected = $pm;
            break;
        }
    }
    if( !$curr_pm_is_active )
        $pm_selected = reset($currently_active_pmethods);

    leyka_setup_current_pm($pm_selected, $curr_currency);

    echo leyka_pf_get_hidden_fields((int)$_POST['campaign']);?>

    <!-- pm selector -->
    <div id="pm-selector" class="form-part">
        <ul class="leyka-pm-selector">
            <?php foreach($currently_active_pmethods as $pm) {?>
                <li>
                    <label class="radio">
                        <input type="radio" name="leyka_payment_method" value="<?php echo esc_attr($pm->full_id);?>" <?php checked($pm_selected->id, $pm->id);?> data-pm_id="<?php echo esc_attr($pm->id);?>">
                        <?php echo $pm->label;?>
                    </label>
                </li>
            <?php }?>
        </ul>
    </div>

    <!-- changeable area -->
    <div id="leyka-pm-data" class="changeable-fields form-part">

        <div class="leyka-pm-fields">

            <div class='leyka-user-data'>
                <!-- field for GA -->
                <input type="hidden" name="leyka_ga_payment_method" value="<?php echo esc_attr($curr_pm->label);?>" />
                <?php
                echo leyka_pf_get_name_field();
                echo leyka_pf_get_email_field();
                echo leyka_pf_get_pm_fields();
                ?>
            </div>

            <?php
            echo leyka_pf_get_agree_field();
            echo leyka_pf_get_submit_field();

            $icons = leyka_pf_get_pm_icons();
            if($icons) {
                $list = array();
                foreach($icons as $i){
                    $list[] = "<li>{$i}</li>";
                }

                echo "<ul class='leyka-pm-icons cf'>";
                echo implode('', $list);
                echo "</ul>";
            }

            ?>
        </div>
        <?php echo "<div class='leyka-pm-desc'>".apply_filters('leyka_the_content', leyka_pf_get_pm_description())."</div>";?>

    </div>
    <?php

    die();
}
add_action('wp_ajax_leyka_currency_choice', 'leyka_currency_choice_action');
add_action('wp_ajax_nopriv_leyka_currency_choice', 'leyka_currency_choice_action');