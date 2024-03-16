<?php if( !defined('WPINC') ) die;
/** Custom field group for the Yandex Kassa payments cards. */
/** @var $this Leyka_Text_Block A block for which the template is used. */?>
 
<p>
    <?php
        _e("Check the «Test mode» box in the settings to make test payments, or skip this option to accept payments in combat mode.", "leyka");
    ?>
</p>
<div id="" class="settings-block option-block type-checkbox">
    <div id="leyka_mixplat_prod_mode-wrapper" class="leyka-checkbox-field-wrapper ">
        <label>
            <span class="field-component field">
                <input type="checkbox" id="leyka_mixplat_prod_mode-field">&nbsp;<?php _e("The working mode of integration", "leyka"); ?>                 
                <span class="field-q">
                    <img src="/wp-content/plugins/leyka/img/icon-q.svg" alt="">
                    <span class="field-q-tooltip"><?php _e("Check the box if the integration with the payment operator is in operation mode. Sometimes this mode is also called &quot;sandbox&quot; or &quot;sandbox&quot;.", "leyka");
                    
                    ?></span>
                </span>
            </span>
        </label>
    </div>
</div>