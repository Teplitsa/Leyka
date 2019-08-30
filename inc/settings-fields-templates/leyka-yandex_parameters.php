<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */

$admin_email = get_option('admin_email');
$shop_password = leyka_options()->opt('yandex_shop_password');
$yandex_check_url = site_url('/leyka/service/yandex/check_order/');
$yandex_aviso_url = site_url('/leyka/service/yandex/payment_aviso/');?>

<p><?php esc_html_e("Let's start the technical connection for Yandex.Kassa and Leyka.", 'leyka');?></p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption"><?php _e('Find and select <strong>"Wordpress (Leyka)"</strong> in the dropdown', 'leyka');?></div>
        <?php leyka_show_wizard_captioned_screenshot("yandex/yandex_parameters-click.png")?>
        <div class="body">
            <p><?php esc_html_e('Several fields will be filled automatically. You may check if all the addresses are correct:', 'leyka');?></p>
            <div class="expandable-area collapsed org-data">
                <div class="fields">

                    <div class="field">
                        <label>CheckURL</label>
                        <p class="field-text leyka-wizard-copy2clipboard"><?php echo $yandex_check_url;?></p>
                    </div>

                    <div class="field">
                        <label>AvisoURL</label>
                        <p class="field-text leyka-wizard-copy2clipboard"><?php echo $yandex_aviso_url;?></p>
                    </div>

                </div>

                <a class="inline expand" href="#"><?php esc_html_e('A list of the site addresses that should be entered in Yandex.Kassa', 'leyka');?></a>
                <a class="inline collapse" href="#"><?php esc_html_e('Close the list', 'leyka');?></a>
            </div>
        </div>
    </div>

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption"><?php _e('Enter in the <strong>"Address for the register emails"</strong>', 'leyka');?></div>
        <div class="body value leyka-wizard-copy2clipboard"><b><?php echo $admin_email;?></b></div>
    </div>

    <div class="enum-separated-block">

        <div class="block-separator"><div></div></div>

        <div class="caption" <?php if( !$shop_password ) {?>style="display: none;"<?php }?>>
            <?php _e('Copy the password and paste it in the <strong>"shopPassword"</strong> field', 'leyka');?>
        </div>

        <div class="body value <?php if( !$shop_password ) {?>no-password<?php }?> leyka-wizard-copy2clipboard">
            <b <?php if( !$shop_password ) {?>style="display: none;"<?php }?>><?php echo $shop_password;?></b>
            <?php if( !$shop_password ) {?>
            <input type="button" class="button button-secondary" id="yandex-generate-shop-password" value="<?php esc_attr_e('Generate the "shopPassword"', 'leyka');?>">
            <?php }?>
            <input type="hidden" name="leyka_yandex_shop_password" value="<?php echo $shop_password;?>">
        </div>

    </div>

</div>