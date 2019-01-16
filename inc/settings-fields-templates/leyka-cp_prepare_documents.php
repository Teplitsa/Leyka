<?php if( !defined('WPINC') ) die;

/** Custom field group for the CP payments cards. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */?>

<div class="<?php echo $this->field_type;?> custom-block-cp-prepare-documents">

    <h4><?php esc_html_e('Download the documents:', 'leyka');?></h4>
    
    <ul>
        <li><a href="https://cloudpayments.ru/wiki/podkluchenie/poryadok_podkluchenia/oferta" target="_blank"><?php esc_html_e('CloudPayments terms of usе - for your information', 'leyka');?></a></li>
        <li><a href="https://static.cloudpayments.ru/docs/%D0%9F%D1%80%D0%B8%D0%BB%D0%BE%D0%B6%D0%B5%D0%BD%D0%B8%D0%B5%201.docx" target="_blank"><?php esc_html_e('Download and fill out the Annex #1', 'leyka');?></a></li>
    </ul>

    <p class="org-data fill-explain"><?php esc_html_e('You will have to use your organization data.', 'leyka');?></p>

    <div class="expandable-area collapsed org-data">
        <div class="fields">
            
            <div class="field">
                <label><?php esc_html_e('Organization name', 'leyka');?></label>
                <p class="field-text"><?php echo leyka_options()->opt('org_full_name');?></p>
                <div class="field-errors"><span><?php esc_html_e('Not filled', 'leyka');?></span></div>
            </div>
    
            <div class="field">
                <label><?php esc_html_e('Organization short name', 'leyka');?></label>
                <p class="field-text"><?php echo leyka_options()->opt('org_short_name');?></p>
                <div class="field-errors"><span><?php esc_html_e('Not filled', 'leyka');?></span></div>
            </div>
    
            <div class="field">
                <label><?php esc_html_e("Director's full name", 'leyka');?></label>
                <p class="field-text"><?php echo leyka_options()->opt('org_face_fio_ip');?></p>
                <div class="field-errors"><span><?php esc_html_e('Not filled', 'leyka');?></span></div>
            </div>
    
            <div class="field">
                <label><?php esc_html_e('The organization official address', 'leyka');?></label>
                <p class="text"><?php echo leyka_options()->opt('org_address');?></p>
                <div class="field-errors"><span><?php esc_html_e('Not filled', 'leyka');?></span></div>
            </div>
    
            <div class="inline-container">
                <div class="field">
                    <label><?php esc_html_e('The organization state registration number', 'leyka');?></label>
                    <p class="field-text"><?php echo leyka_options()->opt('org_state_reg_number');?></p>
                    <div class="field-errors"><span><?php esc_html_e('Not filled', 'leyka');?></span></div>
                </div>
                
                <div class="field">
                    <label><?php esc_html_e('The organization statement of the account number', 'leyka');?></label>
                    <p class="field-text"><?php echo leyka_options()->opt('org_kpp');?></p>
                    <div class="field-errors"><span><?php esc_html_e('Not filled', 'leyka');?></span></div>
                </div>
                
                <div class="field">
                    <label><?php esc_html_e('The organization taxpayer individual number', 'leyka');?></label>
                    <p class="field-text"><?php echo leyka_options()->opt('org_inn');?></p>
                    <div class="field-errors"><span><?php esc_html_e('Not filled', 'leyka');?></span></div>
                </div>
            </div>
            
            <div class="field">
                <label><?php esc_html_e('The organization bank name', 'leyka');?></label>
                <p class="text"><?php echo leyka_options()->opt('org_bank_name');?></p>
                <div class="field-errors"><span><?php esc_html_e('Not filled', 'leyka');?></span></div>
            </div>

            <div class="inline-container">
                <div class="field">
                    <label><?php esc_html_e('The organization bank account number', 'leyka');?></label>
                    <p class="field-text"><?php echo leyka_options()->opt('org_bank_account');?></p>
                    <div class="field-errors"><span><?php esc_html_e('Not filled', 'leyka');?></span></div>
                </div>

                <div class="field">
                    <label><?php esc_html_e('The organization bank correspondent account number', 'leyka');?></label>
                    <p class="field-text"><?php echo leyka_options()->opt('org_bank_corr_account');?></p>
                    <div class="field-errors"><span><?php esc_html_e('Not filled', 'leyka');?></span></div>
                </div>
            </div>

            <div class="inline-container">
                <div class="field">
                    <label><?php esc_html_e('The organization bank BIC number', 'leyka');?></label>
                    <p class="field-text"><?php echo leyka_options()->opt('org_bank_bic');?></p>
                    <div class="field-errors"><span><?php esc_html_e('Not filled', 'leyka');?></span></div>
                </div>
            </div>

        </div>
        
        <a class="inline expand" href="#"><?php esc_html_e('Show the data', 'leyka');?></a>
        <a class="inline collapse" href="#"><?php esc_html_e('Hide the data', 'leyka');?></a>
    </div>

</div>