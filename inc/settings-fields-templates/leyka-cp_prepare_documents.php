<?php if( !defined('WPINC') ) die;

/** Custom field group for the CP payments cards. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */
?>

<div class="<?php echo $this->field_type;?> custom-block-cp-prepare-documents">

    <h4>Скачайте документы</h4>
    
    <ul>
        <li><a href="https://cloudpayments.ru/Docs/Oferta_scan.pdf" target="_blank">Оферту CloudPayments для ознакомления</a></li>
        <li><a href="https://cloudpayments.ru/Docs/%D0%9F%D1%80%D0%B8%D0%BB%D0%BE%D0%B6%D0%B5%D0%BD%D0%B8%D0%B5%201.docx" target="_blank">Скачайте и заполните Приложение 1</a></li>
    </ul>

    <p class="org-data fill-explain">Вам необходимо будет внести данные вашей организации.</p>
    
    <div class="expandable-area collapsed org-data">
        <div class="fields">
            
            <div class="field">
                <label>Полное наименование организации</label>
                <span class="field-text"><?php echo leyka_options()->opt('org_full_name')?></span>
                <div class="field-errors"><span>Не заполнено</span></div>
            </div>
    
            <div class="field">
                <label>Сокращенное наименование организации</label>
                <span class="field-text"><?php echo leyka_options()->opt('org_short_name')?></span>
                <div class="field-errors"><span>Не заполнено</span></div>
            </div>
    
            <div class="field">
                <label>Ф.И.О. директора</label>
                <span class="field-text"><?php echo leyka_options()->opt('org_face_fio_ip')?></span>
                <div class="field-errors"><span>Не заполнено</span></div>
            </div>
    
            <div class="field">
                <label>Юридический адрес организации</label>
                <span class="text"><?php echo leyka_options()->opt('org_address')?></span>
                <div class="field-errors"><span>Не заполнено</span></div>
            </div>
    
            <div class="inline-container">
                <div class="field">
                    <label>ОГРН</label>
                    <span class="field-text"><?php echo leyka_options()->opt('org_ogrn')?></span>
                    <div class="field-errors"><span>Не заполнено</span></div>
                </div>
                
                <div class="field">
                    <label>КПП</label>
                    <span class="field-text"><?php echo leyka_options()->opt('org_kpp')?></span>
                    <div class="field-errors"><span>Не заполнено</span></div>
                </div>
                
                <div class="field">
                    <label>ИНН</label>
                    <span class="field-text"><?php echo leyka_options()->opt('org_inn')?></span>
                    <div class="field-errors"><span>Не заполнено</span></div>
                </div>
            </div>
            
            <div class="field">
                <label>Наименование банка</label>
                <span class="text"><?php echo leyka_options()->opt('org_bank_name')?></span>
                <div class="field-errors"><span>Не заполнено</span></div>
            </div>
            
            <div class="inline-container">
                <div class="field">
                    <label>Расчётный счёт</label>
                    <span class="field-text"><?php echo leyka_options()->opt('org_bank_account')?></span>
                    <div class="field-errors"><span>Не заполнено</span></div>
                </div>
                
                <div class="field">
                    <label>Корреспондентский счёт</label>
                    <span class="field-text"><?php echo leyka_options()->opt('org_bank_corr_account')?></span>
                    <div class="field-errors"><span>Не заполнено</span></div>
                </div>
            </div>
            
            <div class="inline-container">
                <div class="field">
                    <label>БИК банка</label>
                    <span class="field-text"><?php echo leyka_options()->opt('org_bank_bic')?></span>
                    <div class="field-errors"><span>Не заполнено</span></div>
                </div>
                
                <div class="field">
                    <label>КПП банка</label>
                    <span class="field-text"><?php echo leyka_options()->opt('org_bank_kpp')?></span>
                    <div class="field-errors"><span>Не заполнено</span></div>
                </div>
            </div>
            
        </div>
        
        <a class="inline expand" href="#">Показать данные</a>
        <a class="inline collapse" href="#">Свернуть</a>
    </div>

</div>