<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa general info step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */

$org_bank_bic = leyka_options()->opt('org_bank_bic');
$org_bank_account = leyka_options()->opt('org_bank_account');

?>

<p>В этом разделе заполняются общие данные об организации, которые собирает Яндекс Касса для принятия решения о сотрудничестве с вами.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Кликните на пункт <b>«Банковский счет»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_bank_account-click.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <?php if($org_bank_bic):?>
        
            <div class="caption">Скопируйте БИК банка вашей организации и вставьте в форму</div>
            <div class="body value">
                <b><?php echo $org_bank_bic?></b>
            </div>
            
        <?php else:?>
        
            <div class="settings-block custom-block option-block text">
                <?php leyka_render_text_field('org_bank_bic', array('title' => 'Вставьте БИК банка вашей организации', 'comment' => 'Этих данных не оказалось в настройках. Заполните их, они еще пригодятся'))?>
            </div>
            
        <?php endif?>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_bank_account-fill-bank-bic.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <?php if($org_bank_account):?>
        
            <div class="caption">Скопируйте номер расчетного счета и вставьте в форму:</div>
            <div class="body value">
                <b><?php echo $org_bank_account?></b>
            </div>
            
        <?php else:?>
        
            <div class="settings-block custom-block option-block text">
                <?php leyka_render_text_field('org_bank_account', array('title' => 'Вставьте номер расчетного вашей организации', 'comment' => 'Этих данных не оказалось в настройках. Заполните их, они еще пригодятся'))?>
            </div>
            
        <?php endif?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="body">
            После заполнения, нажмите кнопку <b>«Сохранить»</b>
        </div>
    </div>

</div>
