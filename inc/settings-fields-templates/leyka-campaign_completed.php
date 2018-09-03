<?php if( !defined('WPINC') ) die;

/** Custom field group for the Campaign completed Step of the Init wizard. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */
?>

<div id="<?php echo $this->id;?>" class="settings-block custom-block <?php echo $this->field_type;?>">
<?php if(leyka_are_bank_essentials_set()) { // Bank essentials are filled

    $permalinks_on = !!get_option('permalink_structure');
    $init_campaign = get_post(get_transient('leyka_init_campaign_id'));
    $campaign_permalink_parts = get_sample_permalink($init_campaign->ID); // [0] - current URL template, [1] - current slug
    $campaign_base_url = rtrim(str_replace('%pagename%', '', $campaign_permalink_parts[0]), '/');
    $campaign_permalink_full = str_replace('%pagename%', $campaign_permalink_parts[1], $campaign_permalink_parts[0]);?>

    <ul class="leyka-campaign-completed" data-campaign-id="<?php echo $init_campaign->ID;?>">
        <li>
            <div class="item-text">Кампания настроена по адресу:</div>
            <div class="item-info">
                <div class="campaign-permalink">

                <?php if($permalinks_on) {?>

                    <span class="base-url"><?php echo $campaign_base_url;?></span>/<span class="slug"><?php echo $campaign_permalink_parts[1];?></span>

                <?php } else {?>

                    <span class="base-url"><?php echo $campaign_permalink_full;?></span>
                    <a href="<?php echo admin_url('options-permalink.php');?>" class="permalink-action" target="_blank">Включить постоянные ссылки</a>

                <?php }?>

                    <div class="edit-permalink-loading">
                         <div class="loader-wrap">
                            <span class="leyka-loader xs"></span>
                         </div>
                    </div>

                </div>

            </div>
        </li>
        <li>
            <div class="item-text">Вы можете вставить на любые страницы вашего сайта шорт-код</div>
            <div class="item-info">
                <?php echo Leyka_Campaign_Management::get_campaign_form_shortcode($init_campaign->ID);?>
            </div>
        </li>
        <li>
            <div class="item-text">Подключена <strong>оплата с помощью банковских квитанций</strong></a></div>
        </li>
    </ul>

<?php } else { // Bank essentials are filled ?>

<?php }?>

</div>