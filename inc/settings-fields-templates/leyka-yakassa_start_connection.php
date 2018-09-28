<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa start connection step. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */

$org_inn = leyka_options()->opt('org_inn');

?>

<p>В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Перейдите по адресу</div>
        <div class="body value">
            <a target="_blank" href="https://kassa.yandex.ru/joinups">https://kassa.yandex.ru/joinups</a>
        </div>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        
        <?php if($org_inn):?>
        
            <div class="caption">Скопируйте ИНН вашей организации</div>
            <div class="body value">
                <b><?php echo $org_inn?></b>
            </div>
            
        <?php else:?>
        
            <div class="settings-block custom-block option-block text">
                <?php leyka_render_text_field('org_inn', array('title' => 'Вставьте ИНН вашей организации', 'comment' => 'Этих данных не оказалось в настройках. Заполните их, они еще пригодятся'))?>
            </div>
            
        <?php endif?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Вставьте в форму ИНН и нажмите кнопку <b>«Продолжить»</b></div>
        
        <div class="captioned-screen">
            <div class="screen-wrapper">
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/yakassa/yakassa_start_connection-inn-input.png" class="leyka-instructions-screen" />
                <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-zoom-screen.svg" class="zoom-screen" />
            </div>
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/yakassa/yakassa_start_connection-inn-input.png" class="leyka-instructions-screen-full" />
        </div>
    </div>

</div>
