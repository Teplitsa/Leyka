<?php
/*
    Plugin name: Leyka Move
    Version: 0.1
*/

add_action( 'admin_menu', 'leyka_move' );
function leyka_move(){
    $page_title = 'Leyka Move';
    $menu_title = 'Leyka Move';
    $capability = 'manage_options';
    $menu_slug  = 'leyka-move';
    $function   = 'leykaMoveApp';
    $icon_url   = 'dashicons-randomize';
    $position   = 100;

    add_menu_page(
        $page_title,
        $menu_title,
        $capability,
        $menu_slug,
        $function,
        $icon_url,
        $position
    );
}

function leykaMoveApp()
{
    global $wpdb;

    $campaignsAll = $wpdb
        ->get_results(
            "SELECT
                p.id,
                p.post_title AS title,
                m.meta_value AS state,
                COALESCE(mt.meta_value, '-') AS target,
                mf.meta_value AS funded
            FROM $wpdb->posts p
            LEFT JOIN $wpdb->postmeta m ON p.id = m.post_id AND m.meta_key = 'target_state'
            LEFT JOIN $wpdb->postmeta mt ON p.id = mt.post_id AND mt.meta_key = 'campaign_target'
            LEFT JOIN $wpdb->postmeta mf ON p.id = mf.post_id AND mf.meta_key = 'total_funded'
            WHERE p.post_type = 'leyka_campaign'
            ORDER BY p.post_title ASC;
        ");

    $campaignId = $_POST['campaign'] ?? '0';
    $newdata = $_POST['newdata'];
    $commit = $_POST['commit'] ?? '';
    $selectedMovedTitle = '';
    $cmpname = $_POST['cmpname'] ?? '';

    if ($commit) {

        if (current_user_can('administrator')) {

            $commit = json_decode(stripslashes($commit), true);

            $target = $commit['target'];
            $from = $commit['from'];

            $newLog = [
                'from' => (string)$from,
                'date' => (string)time(),
            ];

            $fnLogUpdate = fn(int $sid, string $log): string =>
            "UPDATE $wpdb->postmeta
                            SET `meta_value` = '$log'
                            WHERE `post_id` = $sid AND
                                  `meta_key`= 'leyka_move_log';
                ";
            $fnLogInsert = fn(int $sid, string $log): string =>
            "INSERT
                    INTO $wpdb->postmeta(`post_id`, `meta_key`, `meta_value`)
                    VALUES($sid, 'leyka_move_log', '$log');
                ";
            $fnLog = function (int $sid) use($wpdb, $fnLogInsert, $fnLogUpdate, $newLog): string
            {
                $logQuery = "SELECT `meta_value`
                        FROM $wpdb->postmeta
                        WHERE `post_id` = $sid
                        AND `meta_key` = 'leyka_move_log';
                ";

                $log = $wpdb
                    ->get_results($logQuery);

                $logFn = $fnLogInsert;
                if(count($log)) {
                    $log = json_decode($log[0]->meta_value, true);
                    $logFn = $fnLogUpdate;
                }
                $log[] = $newLog;
                $log = json_encode($log);

                return $logFn($sid, $log);

            };

            $fnCheckChildren = fn(int $sid, int $from): string =>
            "SELECT
                cp.meta_value AS mail,
                ml.post_id,
                ch.meta_value AS CMP
            FROM $wpdb->postmeta cp
            LEFT JOIN $wpdb->postmeta ml ON ml.meta_key = 'leyka_donor_email' AND ml.meta_value = cp.meta_value
            LEFT JOIN $wpdb->postmeta ch ON ch.meta_key = 'leyka_campaign_id' AND ch.meta_value = $from AND ch.post_id = ml.post_id
            WHERE
                ch.meta_value IS NOT NULL AND
                ml.post_id >= cp.post_id AND
                cp.post_id = $sid AND
                cp.meta_key = 'leyka_donor_email';
            ";

            $fnChangeOnly = fn(int $sid, int $target, int $from): string =>
            "UPDATE $wpdb->postmeta
                        SET `meta_value` = $target
                        WHERE `post_id` = $sid AND
                              `meta_key`= 'leyka_campaign_id' AND
                              `meta_value` = $from;
                ";

            $fnMetaDataCopy = function(int $sid, bool $logOnly = false) use($wpdb): array
            {
                $metaQuery = "SELECT * FROM $wpdb->postmeta WHERE `post_id` = $sid";

                if ($logOnly) {
                    $metaQuery .= " AND `meta_key` = 'leyka_move_log'";
                }

                $metaQuery .= ';';

                $metaDataCopy = $wpdb
                    ->get_results($metaQuery);

                $metaDataCopyRes = [];
                foreach($metaDataCopy as $data) {
                    $metaDataCopyRes[$data->meta_key] = $data->meta_value;
                }
                echo '<pre>';
                //print_r($metaDataCopyRes);
                echo '</pre>';

                return $metaDataCopyRes;
            };

            $fnMetaDataCopyChangeMeta = function(array $data, int $target) use($wpdb): array
            {
                $data['leyka_campaign_id'] = (string)$target;

                return $data;
            };

            $fnPostDataCopy = fn(int $sid): string =>
            "SELECT * FROM $wpdb->posts WHERE `id` = $sid;";

            $nowString = '\'' . (string)time() . '\'';
            $fnCloseOldSubscription = function (int $sid) use($nowString, $wpdb): bool
            {
                $updateRebillingQuery =
                    "UPDATE $wpdb->postmeta
                        SET `meta_value` = ''
                        WHERE `post_id` = $sid AND
                              `meta_key`= '_rebilling_is_active';";

                $updateValueQuery = "UPDATE $wpdb->postmeta
                        SET `meta_value` = '0'
                        WHERE `post_id` = $sid AND
                              `meta_key`= 'leyka_donation_amount';";

                $updateRecurrentsQuery = "UPDATE $wpdb->postmeta
                        SET `meta_value` = '1'
                        WHERE `post_id` = $sid AND
                              `meta_key`= 'leyka_recurrents_cancelled';";

                $updateRecurrentsDateQuery = "UPDATE $wpdb->postmeta
                        SET `meta_value` = $nowString
                        WHERE `post_id` = $sid AND
                              `meta_key`= 'leyka_recurrents_cancel_date';";

                $updateRebillingQueryResult = $wpdb->query($updateRebillingQuery);
                $updateValueQueryResult = $wpdb->query($updateValueQuery);
                $updateRecurrentsQueryResult = $wpdb->query($updateRecurrentsQuery);
                $updateRecurrentsDateQueryResult = $wpdb->query($updateRecurrentsDateQuery);

                $updateTotalQuery = "UPDATE $wpdb->postmeta
                        SET `meta_value` = '0'
                        WHERE `post_id` = $sid AND
                              `meta_key`= 'leyka_donation_amount_total';";

                $updateCurrQuery = "UPDATE $wpdb->postmeta
                        SET `meta_value` = '0'
                        WHERE `post_id` = $sid AND
                              `meta_key`= 'leyka_main_curr_amount';";

                $wpdb->query($updateTotalQuery);
                $wpdb->query($updateCurrQuery);

                ##var_dump($updateRebillingQueryResult, '$updateRebillingQueryResult');
                ##var_dump($updateRecurrentsQueryResult, '$updateRecurrentsQueryResult');
                ##var_dump($updateRecurrentsDateQueryResult, '$updateRecurrentsDateQueryResult');
                ##var_dump($updateValueQueryResult, '$updateValueQueryResult');

                return !!$updateValueQueryResult && !!$updateRebillingQueryResult && !!$updateRecurrentsQueryResult && !!$updateRecurrentsDateQueryResult;
            };


            $metaCopyLogExistsFn = fn(array $data): bool => isset($data['leyka_move_log']);

            $fnPostDataInsertCopy = function(array $data) use($wpdb): string
            {
                $keys = [];
                $values = [];
                foreach ($data as $key => $value) {

                    if (strpos($key, 'date') || strpos($key, 'modified')) {
                        $value = date('Y-m-d H:i:s');
                    }
                    $keys[] = '`' . $key . '`';
                    $values[] = '\'' . $value . '\'';
                }
                $keys = implode(',', $keys);
                $values = implode(',', $values);
                return "INSERT INTO `$wpdb->posts`($keys) VALUES($values);";
            };

            $fnMetaDataInsertCopy = function(array $data, int $postId) use($wpdb): string
            {
                $result = [];
                foreach ($data as $key => $value) {
                    $pid = '\'' . $postId . '\'';
                    $key = '\'' . $key . '\'';
                    $value = '\'' . $value . '\'';
                    $result[] = '(' . $pid . ',' . $key . ',' . $value . ')';
                }
                $result = implode(',', $result);

                return "INSERT INTO `$wpdb->postmeta`(`post_id`, `meta_key`, `meta_value`) VALUES$result;";
            };

            $moveResult = [
                'toName' => $commit['name'],
                'from' => $from,
                'subscriptions' => [],
            ];

            foreach ($commit['subscriptions'] as $subscription) {

                $wpdb->query('START TRANSACTION');

                $sid = (int)$subscription['sid'];

                /*
                 * $checkChildren
                 * returns count === 1 -> check log -> TRUE ? change : copy
                 * returns count > 1 -> copy
                */
                $checkChildren = $wpdb
                    ->get_results($fnCheckChildren($sid, $from));

                /*echo"<pre>";print_r($checkChildren);echo"</pre>";*/

                if (count($checkChildren) === 1) {

                    $metaDataCopy = $fnMetaDataCopy($sid, true);
                    $metaCopyLogExists = $metaCopyLogExistsFn($metaDataCopy);

                    if ($metaCopyLogExists) {

                        /*echo 'CHANGE metaCopyLogExists';
                        echo '<br>';
                        echo 'means this is a copy of the original init with no futher donations yet';
                        echo '<br><br>';*/

                        /*DO ONLY CHANGE*/

                        /*change campaign*/
                        $mainQuery = $fnChangeOnly($sid, $target, $from);
                        /*record log*/
                        $logQuery = $fnLog($sid);

                        $logQueryResult = $wpdb->query($logQuery);
                        $mainQueryResult = $wpdb->query($mainQuery);

                        if($logQueryResult && $mainQueryResult) {

                            $wpdb->query('COMMIT');
                            $moveResult['subscriptions'][$sid] = true;
                            continue;
                        }

                        $wpdb->query('ROLLBACK');
                        $moveResult['subscriptions'][$sid] = false;
                        continue;
                    }

                    /*echo 'COPY !metaCopyLogExists';
                    echo '<br>';
                    echo 'means this is an original init';
                    echo '<br><br>';*/


                } else {

                    /*echo 'COPY checkChildren > 1';
                    echo '<br>';
                    echo 'means this init has further donations and should be copied';
                    echo '<br><br>';*/

                }

                /*DO COPY AND DO CLOSE*/

                /*1 copy post*/
                $postDataCopy = $wpdb
                    ->get_results($fnPostDataCopy($sid));
                $postDataCopyRes = [];
                foreach($postDataCopy[0] as $key => $value) {
                    $postDataCopyRes[$key] = $value;
                }
                unset($postDataCopyRes['ID']);
                /*echo '<pre>';
                print_r($postDataCopyRes);
                echo '</pre>';*/

                $insertPostCopyQuery = $fnPostDataInsertCopy($postDataCopyRes);

                /*echo $insertPostCopyQuery;
                echo '<br><br>';*/

                /*2 get new post id*/
                /*$copyId = $wpdb->insert_id;*/

                /*3 copy meta
                AND CHANGE CAMPAIGN
                */
                $metaDataCopy = $fnMetaDataCopy($sid);
                $metaDataCopy = $fnMetaDataCopyChangeMeta($metaDataCopy, $target);
                /*$insertMetaCopyQuery = $fnMetaDataInsertCopy($metaDataCopy, $copyId);*/

                /*echo $insertMetaCopyQuery;
                echo '<br><br>';*/

                /*4 add log to closed campaign*/

                $logQuery = $fnLog($sid);

                /*echo $logQuery;
                echo '<br><br>';*/

                /*5 add log to new campaign*/
                //$logQuery = $fnLog($sid);


                /*6 close old subscription*/
                //AND ZERO THE VALUE
                //del$closeOldSubscriptionQuery = $fnCloseOldSubscription($sid);

                /*echo $closeOldSubscriptionQuery;
                echo '<br><br>';*/

                $insertPostQueryResult = $wpdb->query($insertPostCopyQuery);
                $copyId = $wpdb->insert_id;
                $insertMetaCopyQuery = $fnMetaDataInsertCopy($metaDataCopy, $copyId);
                $insertMetaCopyQueryResult = $wpdb->query($insertMetaCopyQuery);
                $insertLogQueryResult = $wpdb->query($logQuery);
                $logCopySubscriptionQuery = $fnLog($copyId);
                $logCopySubscriptionQueryResult = $wpdb->query($logCopySubscriptionQuery);
                $closeOldSubscriptionQueryResult = $fnCloseOldSubscription($sid);

                if ($insertPostQueryResult && $insertMetaCopyQueryResult && $insertLogQueryResult && $logCopySubscriptionQueryResult && $closeOldSubscriptionQueryResult) {
                    $wpdb->query('COMMIT');
                    $moveResult['subscriptions'][$sid] = true;
                    continue;
                }

                ###var_dump($insertPostQueryResult);
                //echo '<pre>';
                ###var_dump($copyId);
                //echo '<br>';
                //echo $insertMetaCopyQuery;
                ###var_dump($insertMetaCopyQueryResult);
                //echo '</pre>';
                //echo '<pre>';
                ###var_dump($insertLogQueryResult);
                //echo '</pre>';
                ###var_dump($logCopySubscriptionQueryResult);
                //echo '<pre>';
                ###var_dump($closeOldSubscriptionQueryResult);
                //echo '</pre>';

                $wpdb->query('ROLLBACK');
                $moveResult['subscriptions'][$sid] = false;
            }
        }
    }

    $campaigns = [
        'no-target' => [],
        'is-reached' => [],
        'in-progress' => [],
    ];

    echo "<br><br>";
    echo "<form action='' method='post' class='get-subscriptions-form'>";
    echo "<label>Move From: <select name='campaign' class='get-campaign-select'>";
    echo "<option value='0'>Choose Old Campaign</option>";
    $selectedMovedResult = isset($moveResult['from']) ? (string)$moveResult['from'] : '0';
    foreach($campaignsAll as $campaign) {
        $id = $campaign->id;
        $title = $campaign->title;
        $state = $campaign->state;
        $state = str_replace('_', '-', $state);
        $target = $campaign->target;
        $funded = $campaign->funded;
        $selected = $campaignId === $id ? ' selected' : '';
        $selectedMoved = $selectedMovedResult && $selectedMovedResult === $id ? ' selected' : '';
        if ($selectedMoved && isset($moveResult['toName'])) {
            $selectedMovedTitle = $moveResult['toName'];
        }
        $campaigns[$state][] = "<option value='$id'$selectedMoved$selected>$title Target:$target/Funded:$funded</option>";

    }
    echo "<optgroup><option>No-Target</option></optgroup>";
    echo implode('', $campaigns['no-target']);
    echo '<optgroup><option>In-Progress</option></optgroup>';
    echo implode('', $campaigns['in-progress']);
    echo '<optgroup><option>Reached</option></optgroup>';
    echo implode('', $campaigns['is-reached']);
    echo '</select></label>';
    echo '<input type="hidden" name="cmpname"/>';
    echo "</form>";

    $campaignId = (int)$campaignId;
    if (isset($moveResult['from'])) {
        $campaignId = (int)($moveResult['from']);
    }

    if ($campaignId) {
        $allQuery = "SELECT
                s.post_id as sid, COUNT(s.post_id) as sidCount,
                s.meta_value as cid,
                COALESCE(ra.meta_value, '0') as rebilling,
                COALESCE(la.meta_value, '') as movelog,
                da.meta_value as amount,
                COALESCE(cd.meta_value, '0') as end,
                COALESCE(gw.meta_value, null) as gwresponse,
                COALESCE(fn.meta_value, null) as stfunded,
                COALESCE(ml.meta_value, null) as mldonor,
                p.post_author as uid,
                p.post_date as start,
                COALESCE(u.display_name, 'no-name') as donor
            FROM $wpdb->postmeta s
            LEFT JOIN $wpdb->postmeta ra ON ra.post_id = s.post_id AND ra.meta_key = '_rebilling_is_active'
            LEFT JOIN $wpdb->postmeta la ON la.post_id = s.post_id AND la.meta_key = 'leyka_move_log'
            LEFT JOIN $wpdb->postmeta da ON da.post_id = s.post_id AND da.meta_key = 'leyka_donation_amount'
            LEFT JOIN $wpdb->postmeta cd ON cd.post_id = s.post_id AND cd.meta_key = 'leyka_recurrents_cancel_date'
            LEFT JOIN $wpdb->postmeta gw ON gw.post_id = s.post_id AND gw.meta_key = 'leyka_gateway_response'
            LEFT JOIN $wpdb->postmeta ml ON ml.post_id = s.post_id AND ml.meta_key = 'leyka_donor_email'
            LEFT JOIN $wpdb->postmeta fn ON fn.post_id = s.post_id AND fn.meta_key = '_status_log' AND fn.meta_value LIKE '%funded%'
            LEFT JOIN $wpdb->posts p ON p.id = s.post_id
            LEFT JOIN $wpdb->users u ON u.id = p.post_author
            WHERE
                s.meta_key = 'leyka_campaign_id' AND
                s.meta_value = $campaignId
            GROUP BY sid ORDER BY start DESC;
        ";
        $all = $wpdb
            ->get_results($allQuery);
        //echo (count($all));
        //echo"<pre>";print_r($all);echo"</pre>";

        $subscriptionsCurrent = [];
        $subscriptionsEnded = [];
        $nonSubscriptions = [];
        $subscriptionsNoGw = [];
        $subscriptionsNoEmail = [];

        foreach($all as $item) {
            $log = '';
            if ($item->movelog) {
                $item->movelog = json_decode($item->movelog, true);
                foreach($item->movelog as $key => $log) {
                    $item->movelog[$key]['date'] = date('d-m-Y', $item->movelog[$key]['date']);
                }
                $log = ' data-log=\'' . json_encode($item->movelog) . '\'';
            }
            $item->start = strtotime($item->start);
            $start = (int)$item->start ? date('d-m-Y', $item->start) : '-';
            $end = (int)$item->end ? date('d-m-Y', $item->end) : '';
            $item->donor = trim(strip_tags($item->donor));

            if ((int)$item->uid) {
                $item->donor = "<a class='user-uid' href='/wp-admin/?page=leyka_donor_info&donor=$item->uid' target='_blank'>$item->donor</a>";
            }
            if ((int)$item->sid) {
                $item->amount = "<a class='user-uid' href='/wp-admin/admin.php?page=leyka_donation_info&donation=$item->sid' target='_blank'>$item->amount</a>";
            }

            if ($item->rebilling) {

                if (!$item->mldonor) {
                    $subscriptionsNoEmail[] = "<div class='subscription-div' $log data-rb='$item->rebilling' data-sid='$item->sid' data-uid='$item->uid'>Value: <b>$item->amount</b>, Start: <b>$start</b>, End: <b>$end</b>, Donor: <b>$item->donor</b></div>";
                    continue;
                }
                if (!$item->gwresponse || !$item->stfunded) {
                    $subscriptionsNoGw[] = "<div class='subscription-div' $log data-rb='$item->rebilling' data-sid='$item->sid' data-uid='$item->uid'>Value: <b>$item->amount</b>, Start: <b>$start</b>, End: <b>$end</b>, Donor: <b>$item->donor</b></div>";
                    continue;
                }

                if ((int)$item->end) {
                    $subscriptionsEnded[] = "<div class='subscription-div' $log data-rb='$item->rebilling' data-sid='$item->sid' data-uid='$item->uid'>Value: <b>$item->amount</b>, Start: <b>$start</b>, End: <b>$end</b>, Donor: <b>$item->donor</b></div>";
                    continue;
                }

                $moved = isset($moveResult['subscriptions']) && isset($moveResult['subscriptions'][$item->sid]) ? $moveResult['subscriptions'][$item->sid] : null;
                $movedErrorClass = '';
                if ($moved === false) {
                    $movedErrorClass = ' moved-error';
                }
                $subscriptionsCurrent[] = "<label><div $log class='subscription-div$movedErrorClass'>Value: <b>$item->amount</b>, Start: <b>$start</b>, End: <b>$end</b>, Donor: <b>$item->donor</b><input data-rb='$item->rebilling' data-sid='$item->sid' data-uid='$item->uid' type='checkbox' /></div></label>";
                continue;
            }
            if ((int)$item->end) {
                $subscriptionsEnded[] = "<div class='subscription-div' $log data-rb='$item->rebilling' data-sid='$item->sid' data-uid='$item->uid'>Value: <b>$item->amount</b>, Start: <b>$start</b>, End: <b>$end</b>, Donor: <b>$item->donor</b></div>";
                continue;
            }
            $nonSubscriptions[] = "<div class='subscription-div' $log data-rb='$item->rebilling' data-sid='$item->sid' data-uid='$item->uid'>Value: <b>$item->amount</b>, Start: <b>$start</b>, End: <b>$end</b>, Donor: <b>$item->donor</b></div>";
        }

        echo '<div class="main-container">';
        if (isset($moveResult['subscriptions'])) {
            $allCount = count($moveResult['subscriptions']);
            $movedCount = count(array_filter($moveResult['subscriptions'], function(bool $el): bool {
                return !!$el;
            }));
            echo "<h2 class='moved-ok'>Moved to \"$selectedMovedTitle\": $movedCount</h2>";
            if (($allCount - $movedCount) > 0) {
                $errorsCount = $allCount - $movedCount;
                echo "<h2 class='moved-error'>Errors: $errorsCount</h2>";
            }
        }

        $count = count($subscriptionsCurrent) + count($subscriptionsEnded);

        echo "<h2><a class='user-uid' href='/wp-admin/post.php?post=$campaignId&action=edit' target='_blank'>$cmpname ($count subscriptions)</a></h2>";

        $count = count($subscriptionsCurrent);
        if ($count) {
            echo '<div class="move-header">';
            echo '<h4>Select subscriptions and the new target campaign</h4>';
            echo '<label><h3>Select all active subscriptions <input type="checkbox" class="subscriptions-all" disabled/></h3></label>';
            echo '<div class="new-campaign-div"><form action="" method="post" class="new-campaign-form"><input type="hidden" name="newdata"/><label>Move <span class="move-counter" style="text-align:center;display:inline-block;width:20px">0</span> To: </label></form></div>';
            echo '<label><h3>Or select individual subscriptions</h3></label>';
            echo '</div>';
            echo "<h3 class='hide-button'>Active subscriptions ($count)</h3>";
            echo '<div class="hide-content">';
            echo "<div data-id='$campaignId' class='subscriptions-current'>";
            echo implode('', $subscriptionsCurrent);
            echo '</div>';
            echo '</div>';
            echo '<br>';
        }
        $count = count($subscriptionsEnded);
        if ($count) {
            echo "<h3 class='hide-button'>Closed subscriptions ($count)</h3>";
            echo '<div class="hide-content display-none">';
            echo implode('', $subscriptionsEnded);
            echo '</div>';
            echo '<br><br>';
        }
        $count = count($subscriptionsNoGw);
        if ($count) {
            echo "<h3 class='hide-button'>Never started subscriptions ($count)</h3>";
            echo '<div class="hide-content display-none">';
            echo implode('', $subscriptionsNoGw);
            echo '</div>';
            echo '<br><br>';
        }
        $count = count($subscriptionsNoEmail);
        if ($count) {
            echo "<h3 class='hide-button'>Donor doesn't have an email ($count)</h3>";
            echo '<div class="hide-content display-none">';
            echo implode('', $subscriptionsNoEmail);
            echo '</div>';
            echo '<br><br>';
        }
        $count = count($nonSubscriptions);
        if ($count) {
            echo '<hr>';
            echo '<br><br>';
            echo "<h3 class='hide-button'>One-Time Donation $count</h3>";
            echo '<div class="hide-content display-none">';
            echo implode('', $nonSubscriptions);
            echo '</div>';
        }
        echo '</div>';
    }

    if (isset($newdata)) {
        $newdataOriginal = stripslashes($newdata);
        $newdata = json_decode(stripslashes($newdata), true);
        $name = trim(strip_tags($newdata['name']));
        $count = count($newdata['subscriptions']);
        $s = $count > 1 ? 's' : '';
        echo '<br><br>';
        echo "<form action='' method='post' class='move-subscriptions-form'>";
        echo "<input type='submit' value='MOVE $count subscription$s to CAMPAIGN \"$name\"?' />";
        echo "<input type='hidden' name='commit' value='$newdataOriginal' />";
        echo "</form>";
    }

    echo "<script>

        const form = document.querySelector('.get-subscriptions-form');
        const select = document.querySelector('.get-campaign-select');
        const cmpname = form.querySelector('input[name=\"cmpname\"]');
        
        const mainContainer = document.querySelector('.main-container');
        const moveCounter = document.querySelector('.move-counter');
        const subscriptionsAll = document.querySelector('.subscriptions-all');
        const subscriptionsCurrent = document.querySelector('.subscriptions-current');
        const newCampaignSelect = document.querySelector('.get-campaign-select').cloneNode(true);
        const newCampaignSelectContainer = document.querySelector('.new-campaign-div form');
        
        const hideButtons = [...document.querySelectorAll('.hide-button')];
        hideButtons.forEach((el) => {
          el.addEventListener('click', (ev) => {
            const hideContent = ev.currentTarget.nextElementSibling;
            console.log(ev.currentTarget);
            console.log(hideContent);
            hideContent.classList.toggle('display-none');
          });
        })
        
        const state = {
          subscriptions: [],
          'true': function(data) {
            this.subscriptions.push(data);
            return this;
          },
          'false': function(data) {
            this.subscriptions =
                this.subscriptions.filter((el) => {
                    return el.sid !== data.sid;
                });
            return this;
          },
          'all-true': function() {
            const allChecks = [...document.querySelectorAll('.subscription-div input')];
            allChecks.map(el => el.click());
            return this;
          },
          'all-false': function() {
            this.subscriptions = [];
            const allChecks = [...document.querySelectorAll('.subscription-div input')];
            allChecks.map(el => el.checked = false);
            return this;
          },
          newSelect: function() {
            moveCounter.textContent = String(this.subscriptions.length);
            if (this.subscriptions.length) {
              newCampaignSelect.disabled = false;
              return;
            }
            newCampaignSelect.childNodes[0].selected = true;
            newCampaignSelect.disabled = true;
          }
        };
        
        select.addEventListener('change', (ev) => {
          if (mainContainer) {
            mainContainer.style.display = 'none';
          }
          if (newCampaignSelectContainer) {
            newCampaignSelectContainer.remove();
          }
          if (subscriptionsCurrent) {
            subscriptionsCurrent.remove();
          }
          const text = ev.currentTarget.querySelector('option[value=\"' + ev.currentTarget.value + '\"').textContent.split('Target')[0].trim();
          cmpname.value = text;
          form.submit();
        });
        
        const checkboxClickHandler = (ev) => {
          if (ev.target.type === 'checkbox') {
            const el = ev.target;
            const sid = Number(el.dataset.sid);
            const uid = Number(el.dataset.uid);
            const action = String(el.checked);
            state[action]({sid, uid}).newSelect();
          }
        }
        
        const subscriptionsAllClickHandler = (ev) => {
          const el = ev.target;
          const checked = String(el.checked);
          const action = 'all-' + checked;
          state[action]().newSelect();
        }
        
        if(subscriptionsAll) {
          subscriptionsAll.addEventListener('click', subscriptionsAllClickHandler);
        }
        
        if(subscriptionsCurrent) {
          subscriptionsCurrent.addEventListener('click', checkboxClickHandler);
        }
        
        if (newCampaignSelectContainer) {
          newCampaignSelect.childNodes[0].textContent = 'Choose New Target Campaign';
          newCampaignSelect.childNodes[0].selected = true;
          const selected = select.value;
          newCampaignSelect.querySelector('option[value=\"' + selected + '\"]').disabled = true;
          newCampaignSelect.name = 'newcampaign';
          newCampaignSelect.disabled = true;
          newCampaignSelectContainer.append(newCampaignSelect);
          
          newCampaignSelect.addEventListener('change', (ev) => {
            const target = Number(ev.target.value);
            if (Number(select.value) === target) {
              return;
            }
            if (!state.subscriptions.length) {
              return;
            }
            if (!Number(ev.target.value)) {
              return;
            }
              const from = Number(select.value);
              const hidden = newCampaignSelectContainer.elements.newdata;
              const query = 'option[value=\"' + target + '\"]';
              const name = ev.target.querySelector(query).textContent.split('Target')?.[0] || '';
              const {subscriptions} = state;
              hidden.value = JSON.stringify({from, target, name, subscriptions});
              if (mainContainer) {
                mainContainer.style.display = 'none';
              }
              if (newCampaignSelect) {
                  newCampaignSelect.remove();
              }
              if (subscriptionsCurrent) {
                subscriptionsCurrent.remove();
              }
              newCampaignSelectContainer.submit();
          });
        }
        
        if (document.querySelector('.notice-warning')) {
          document.querySelector('.notice-warning').remove();
        }
        
        const logs = [...document.querySelectorAll('div[data-log]')];
        if (logs.length) {
          logs.forEach((el) => {
            const log = JSON.parse(el.dataset.log);
            const attr = log.map((el) => {
              const compaignId = el.from;
              const selector = 'option[value=\"' + compaignId + '\"]';
              const compaign = select.querySelector(selector).textContent.split('Target:')[0];
              return el.date + ' ' + compaign;
            });
            el.dataset.log = 'Moved from: ' + attr.join(', ');
          });
        }
        
    </script>";

    echo "<style>
        .subscription-div {
            margin: 10px 0;
            padding: 10px;
        }
        .subscription-div:hover {
            background-color: lightgray;
        }
        .subscription-div input {
            margin: 0 0 0 10px;
        }
        
        .subscription-div[data-log] {
            background-color: #fffbf6; 
        }
        
        .subscription-div[data-log]:after {
            content: attr(data-log);
            margin-left: 10px;
            font-size: 10px;
        }
        .subscription-div.moved-error, .moved-error {
            background-color: #ff0000;
            padding: 10px 10px;
            color: #fff;
        }
        .moved-ok {
            background-color: #fff8df;
            padding: 10px 10px;
        }
        .hide-button {
            cursor: pointer;
            border: 1px solid lightgray;
            border-radius: 6px;
            padding: 12px;
        }
        
        .display-none {
            display: none;
        }
        
        .main-container {
            padding: 12px;
            margin: 12px;
        }
        
        .move-header {
            position: sticky;
            top: 22px;
            background-color: #f2f2f2;
            padding: 12px 12px 4px 12px;
        }
        .get-subscriptions-form {
            position: fixed;
            top: 28px;
            z-index: 100;
            padding: 12px;
            background-color: #f2f2f2;
        }
        h3 {
            margin: 10px;
        }
        
        input[type='submit'] {
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>";
}

