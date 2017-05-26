<?php if( !defined('WPINC') ) die;
/**
 * Leyka Revo Template code extras.
 **/

//if( !function_exists('leyka_donation_history_list') ) {
//    function leyka_donation_history_list($campaign_id) {
//
//        $currency = "<span class='curr-mark'>&#8381;</span>";
//
//        // dummy history items
//        $history = array(
//            array(1000, 'Василий Иванов', '12.05.2017'),
//            array(1500, 'Мария Петрова', '11.05.2017'),
//            array(300, 'Семен Луковичный', '08.05.2017'),
//            array(350, 'Даниил Черный', '08.05.2017'),
//            array(300, 'Ольга Богуславская', '08.05.2017'),
//            array(1000, 'Мария Разумовская-Розенберг', '05.05.2017'),
//            array(10000, 'Анонимное пожертвование', '02.05.2017')
//        );
//
//        for($i=0; $i<2; $i++) {
//            $history = array_merge($history, $history);
//        }
//
//        ob_start();
//
//        foreach($history as $h) {?>
<!--            <div class="history__row">-->
<!--                <div class="history__cell h-amount">--><?php //echo number_format($h[0], 2, '.', ' ').' '.$currency;?><!--</div>-->
<!--                <div class="history__cell h-name">--><?php //echo $h[1];?><!--</div>-->
<!--                <div class="history__cell h-date">--><?php //echo $h[2];?><!--</div>-->
<!--            </div>-->
<!--        --><?php //}
//
//        $out = ob_get_contents();
//        ob_end_clean();
//
//        return $out;
//
//    }
//}