<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa general info step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */

$org_address = leyka_options()->opt('org_address');
?>

<p>В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Кликните на пункт <b>«Государственная регистрация»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_gos_reg-click.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <?php if($org_address):?>
        
            <div class="caption">Скопируйте адрес регистрации и вставьте в форму</div>
            <div class="body value">
                <b><?php echo $org_address?></b>
            </div>
            
        <?php else:?>
        
            <div class="settings-block custom-block option-block text">
                <?php leyka_render_text_field('org_address', array('title' => 'Вставьте адрес регистрации вашей организации', 'comment' => 'Этих данных не оказалось в настройках. Заполните их, они еще пригодятся'))?>
            </div>
            
        <?php endif?>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_gos_reg-fill-address.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="body">
            Заполните поле фактического адреса, где находится ваша организация. Если фактический адрес совпадает с адресом регистрации, вставьте это адрес еще раз. После заполнения, нажмите кнопку <b>«Сохранить»</b>
        </div>
    </div>

</div>
