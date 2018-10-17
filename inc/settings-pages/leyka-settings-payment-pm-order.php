<?php if( !defined('WPINC') ) die; // If this file is called directly, abort

function leyka_pm_sortable_option_html_new($is_hidden = false, $pm_full_id = '#FID#', $pm_label = '#L#') {?>

    <li class="pm-order" data-pm-id="<?php echo $pm_full_id;?>" <?php echo !!$is_hidden ? 'style="display:none"' : '';?>>

        <div class="gateway-logo">

        </div>

        <div class="pm-info">

        </div>

<!--        <span class="pm-label" id="pm-label---><?php //echo $pm_full_id;?><!--">--><?php //echo $pm_label;?><!--</span>-->
<!---->
<!--        <span class="pm-label-fields" style="display:none;">-->
<!--            <input type="text" id="pm_labels[--><?php //echo $pm_full_id;?><!--]" value="--><?php //echo $pm_label;?><!--" placeholder="--><?php //_e('Enter some title for this payment method', 'leyka');?><!--">-->
<!--            <input type="hidden" class="pm-label-field" name="leyka_--><?php //echo $pm_full_id;?><!--_label" value="--><?php //echo $pm_label;?><!--">-->
<!--            <span class="new-pm-label-ok"><span class="dashicons dashicons-yes"></span></span>-->
<!--            <span class="new-pm-label-cancel"><span class="dashicons dashicons-no"></span></span>-->
<!--        </span>-->
<!---->
<!--        <span class="pm-change-label" data-pm-id="--><?php //echo $pm_full_id;?><!--">-->
<!--            <span class="dashicons dashicons-edit"></span>-->
<!--        </span>-->

    </li>

<?php }?>

<div class="side-area">

    <div class="pm-order-header">
        <h3>В каком порядке отобразятся способы платежа на сайте</h3>
        <div class="pm-order-description">Перетаскивайте мышью блоки, соответствующие способам платежа</div>
    </div>

    <ul id="pm-order-settings">

    <?php leyka_pm_sortable_option_html_new(true); // To clone the PM item structure when adding new items

    $pm_available = leyka_options()->opt('pm_available');
    $pm_order = explode('pm_order[]=', leyka_options()->opt('pm_order'));
    array_shift($pm_order);

    foreach($pm_order as $i => &$pm_full_id) {

        $pm_full_id = str_replace('&amp;', '', $pm_full_id);
        $pm = leyka_get_pm_by_id($pm_full_id, true);

        if($pm && in_array($pm_full_id, $pm_available) ) {
            leyka_pm_sortable_option_html_new(false, $pm_full_id, $pm->label);
        } else {
            unset($pm_order[$i]);
        }

    }?>

    </ul>

</div>