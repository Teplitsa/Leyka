<?php if( !defined('WPINC') ) die;

/** Custom field group for the Campaign completed Step of the Init wizard. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */
?>

<div id="<?php echo $this->id;?>" class="settings-block custom-block <?php echo $this->field_type;?>">
<?php if(leyka_are_bank_essentials_set()) { // Bank essentials are filled

    $permalinks_on = !!get_option('permalink-structure');
    $init_campaign = get_post(get_transient('leyka_init_campaign_id'));?>

    <ul class="leyka-campaign-completed" data-campaign-id="<?php echo $init_campaign->ID;?>">
        <li>
            <div class="item-text">Кампания настроена по адресу:</div>
            <div class="item-info">
            <?php $sample_permalink_html = get_sample_permalink_html($init_campaign->ID);

            // As of 4.4, the Get Shortlink button is hidden by default.
            if ( has_filter( 'pre_get_shortlink' ) || has_filter( 'get_shortlink' ) ) {

                $shortlink = wp_get_shortlink($init_campaign->ID, 'post');

                if ( !empty( $shortlink ) && $shortlink !== $permalink && $permalink !== home_url('?page_id=' . $post->ID) ) {
                    $sample_permalink_html .= '<input id="shortlink" type="hidden" value="' . esc_attr( $shortlink ) . '" /><button type="button" class="button button-small" onclick="prompt(&#39;URL:&#39;, jQuery(\'#shortlink\').val());">' . __( 'Get Shortlink' ) . '</button>';
                }

            }

            if('pending' != $init_campaign->post_status) {

                $has_sample_permalink = $sample_permalink_html && 'auto-draft' != $init_campaign->post_status;?>

                <div id="edit-slug-box" class="hide-if-no-js">
                    <?php
                    if ( $has_sample_permalink )
                        echo $sample_permalink_html;
                    ?>
                </div>

            <?php }?>
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