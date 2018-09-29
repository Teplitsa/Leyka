<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */

$admin_email = get_option('admin_email');
$shop_password = leyka_options()->opt('yandex_shop_password');
$yandex_check_url = site_url('/leyka/service/yandex/check_order/');
$yandex_aviso_url = site_url('/leyka/service/yandex/payment_aviso/');

?>

<p>Переходим к техническому подключению Яндекс Кассы к Лейке.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">В выпадающем списке найдите и выберите пункт <b>«Wordpress (Лейка)»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_parameters-click.png")?>
        <div class="body">
            <p>
                Часть полей проставится автоматически. Вы можете проверить точность адресов
            </p>
            <div class="expandable-area collapsed org-data">
                <div class="fields">
                    
                    <div class="field">
                        <label>CheckURL</label>
                        <p class="field-text"><?php echo $yandex_check_url?></p>
                    </div>
                    
                    <div class="field">
                        <label>AvisoURL</label>
                        <p class="field-text"><?php echo $yandex_aviso_url?></p>
                    </div>
                    
                </div>
                
                <a class="inline expand" href="#">Список адресов, которые должны вставиться в Яндекс Кассе</a>
                <a class="inline collapse" href="#">Свернуть</a>
            </div>
        </div>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Вставьте в поле <b>«Email для отправки реестров»</b></div>
        <div class="body value">
            <b><?php echo $admin_email?></b>
        </div>
    </div>

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption" <?php if(!$shop_password):?>style="display: none;"<?php endif?>>Скопируйте пароль и вставьте в поле «shopPassword»</div>
        <div class="body value">
            <b <?php if(!$shop_password):?>style="display: none;"<?php endif?>><?php echo $shop_password?></b>
            <?php if(!$shop_password):?>
            <input type="button" class="button button-secondary" id="yakassa-generate-shop-password" value="Сгенерируйте пароль «shopPassword»">
            <?php endif?>
            <input type="hidden" name="leyka_yandex_shop_password" value="<?php echo $shop_password?>" />
        </div>
    </div>
    
</div>
