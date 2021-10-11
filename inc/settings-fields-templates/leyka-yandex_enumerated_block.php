<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa general info step. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */

if( !empty($this->field_data['option_id']) ) {
    $option_value = leyka_options()->opt($this->field_data['option_id']);
} else {
    $option_value = '';
}?>

<div class="enum-separated-block">
    <div class="block-separator"><div></div></div>

    <?php if(
        !empty($this->field_data['caption'])
        && (empty($this->field_data['option_id']) || ( !empty($this->field_data['show_text_if_set'] ) && $option_value))
    ) {?>
        <div class="caption"><?php echo $this->field_data['caption'];?></div>
    <?php }?>
    
    <?php if( !empty($this->field_data['value_url']) ) {?>
    
        <div class="body value">
            <a target="_blank" href="<?php echo $this->field_data['value_url']?>"><?php echo $this->field_data['value_url']?></a>
        </div>
    
    <?php } else if( !empty($this->field_data['value_text']) ) {?>
    
        <div class="body value <?php if($this->field_data['copy2clipboard']):?>leyka-wizard-copy2clipboard<?php endif;?>">
            <b><?php echo $this->field_data['value_text']?></b>
        </div>

    <?php } else if( !empty($this->field_data['option_id']) ) {?>

        <?php if( !empty($this->field_data['show_text_if_set']) && $option_value) {?>
        
            <div class="body value <?php if($this->field_data['copy2clipboard']):?>leyka-wizard-copy2clipboard<?php endif;?>">
                <b><?php echo $option_value?></b>
                <input type="hidden" name="leyka_<?php echo $this->field_data['option_id']?>" value="<?php echo $option_value?>">
            </div>
            
        <?php } else {
            
            $field_classes = [];
            if( !empty($this->field_data['copy2clipboard']) ) {
                $field_classes[] = 'leyka-wizard-copy2clipboard';
            }

            leyka_render_text_field($this->field_data['option_id'], [
                'title' => $this->field_data['option_title'],
                'comment' => !empty($this->field_data['option_comment']) ? $this->field_data['option_comment'] : null,
                'placeholder' => !empty($this->field_data['option_placeholder']) ?
                    $this->field_data['option_placeholder'] : null,
                'field_classes' => $field_classes,
                'value' => $option_value
            ]);

        }

    } else if( !empty($this->field_data['screenshot']) ) {
        leyka_show_wizard_captioned_screenshot(
            $this->field_data['screenshot'],
            !empty($this->field_data['screenshot_full']) ? $this->field_data['screenshot_full'] : null
        );
    }

    if( !empty($this->field_data['text']) ) {?>
        <div class="body"><?php echo $this->field_data['text'];?></div>
    <?php }?>
    
</div>