<?php if( !defined('WPINC') ) die; // If this file is called directly, abort ?>

<div class="side-area">

    <div class="pm-order-header">
        <h3>В каком порядке отобразятся способы платежа на сайте</h3>
        <div class="pm-order-description">Перетаскивайте мышью блоки, соответствующие способам платежа</div>
    </div>

    <div class="pm-update-status">
        <div class="result ok-message">Изменения сохранены</div>
        <div class="result error-message"></div>
        <div class="result leyka-loader xs"></div>
    </div>

    <ul id="pm-order-settings" data-pm-order="<?php echo leyka_options()->opt('pm_order');?>" data-nonce="<?php echo wp_create_nonce('leyka-update-pm-order');?>">

    <?php leyka_pm_sortable_option_html_new(true); // To clone the PM item structure when adding new items

    $pm_available = leyka_options()->opt('pm_available');
    $pm_order = explode('pm_order[]=', leyka_options()->opt('pm_order'));
    array_shift($pm_order);

    foreach($pm_order as $i => &$pm_full_id) {

        $pm_full_id = str_replace(array('&amp;', '&'), '', $pm_full_id);
        $pm = leyka_get_pm_by_id($pm_full_id, true);

        if($pm && in_array($pm_full_id, $pm_available) ) {
            leyka_pm_sortable_option_html_new(false, $pm_full_id, $pm->label);
        } else {
            unset($pm_order[$i]);
        }

    }?>

    </ul>

</div>