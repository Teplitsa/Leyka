<?php if( !defined('WPINC') ) die;
/** Custom field group for the MIXPLAT payments cards. */
/** @var $this Leyka_Text_Block A block for which the template is used. */?>

<p>
    <label>
        <input type="checkbox" id="schack" checked />
        <?php
            _e("Receive donations via SMS", "leyka");
        ?>
    </label>
</p>

<?php
    _e("<p>The keyword defines your organization as the recipient of SMS messages sent by subscribers for donations. The user needs to start the text of the SMS message with your keyword, then write the donation amount and send this message to a short number to donate to your foundation.</p>","leyka");
?>

<div class="show-sms">
    <?php
        _e("<p>Keyword fees can be directed to the main campaign or associated with a specific address fee. To do this, create and select the desired address fee in the Mixplat personal account when creating a keyword.</p>","leyka");
    ?>
    <div class="<?php echo esc_attr( $this->field_type );?> custom-block-mixplat_save_query mixplat-steps">    
        
        <div class="step">
            <div class="block-separator"><div></div></div>
            <p><?php
                _e("In the Mixplat personal account, within the framework of a previously created project, go to the &quot;Short Number&quot; tab and create a &quot;Keyword&quot;", "leyka");
            ?></p>
            <div class="captioned-screen">
                <div class="screen-wrapper">
                    <img src="/wp-content/plugins/leyka/img/mixplat/mixplat_img10.png" class="leyka-instructions-screen" alt="">
                    <img src="/wp-content/plugins/leyka/img/icon-zoom-screen.svg" class="zoom-screen" alt="">
                </div>
                <img src="/wp-content/plugins/leyka/img/mixplat/mixplat_img10.png" class="leyka-instructions-screen-full" alt="" style="display: none; position: fixed; z-index: 9991; left: 50%; top: 100px;">
            </div>
        </div>
            
        <div class="step">
            <div class="block-separator"><div></div></div>
            <?php
                _e("<p>After your keyword passes the accessibility check, it will be included in the general list with the status &quot;Working&quot;.</p><p>You can create several keywords, specify different address fees for them and use them both in the Leyka and offline.</p>","leyka");
            ?>
        </div>

        <div class="step">
            <div class="block-separator"><div></div></div>
            <p><?php
                _e("Select the appropriate Leyka campaign for SMS payments from the list or leave the default campaign", "leyka");
            ?>:</p>
        </div>
    </div>
</div>