<?php if( !defined('WPINC') ) die;

/** Custom field group for the Campaign decoration Step of the Init wizard. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */
?>

<?php

$campaign_id = get_transient( 'leyka_init_campaign_id' );
$campaign = new Leyka_Campaign($campaign_id);
$templates = leyka()->get_templates(); 
$cur_template = "default";

?>

<div id="<?php echo $this->id;?>" class="settings-block custom-block <?php echo $this->field_type;?>">
    
    <div class="campaign-decor-wrap">
        
        <div class="decor-form">
            
            <div id="campaign_photo" class="settings-block option-block upload-photo-field">
                
                <div id="leyka_campaign_photo-wrapper">
                    <label for="leyka_campaign_photo-field">
                        <span class="field-component title">
                            <?php esc_html_e('Campaign thumbnail', 'leyka' )?> <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" class="field-q" />
                        </span>
                        <span class="field-component field">
                            <input type="file" value="" />
                            <input type="button" class="button upload-photo" value="<?php esc_html_e('Upload photo', 'leyka' )?>" />
                        </span>
                    </label>
                </div>
                <div class="field-errors"></div>
                
            </div>

            <div id="campaign_template" class="settings-block option-block upload-photo-field">
                
                <div id="leyka_campaign_template-wrapper">
                    <label for="leyka_campaign_template-field">
                        <span class="field-component title">
                            <?php esc_html_e('Choose form template', 'leyka' )?> <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" class="field-q" />
                        </span>
                        <span class="field-component field">
                            
                            <select id="leyka_campaign_template-field" name="campaign_template">
                                <option value="default" <?php selected($cur_template, 'default');?>>
                                    <?php _e('Default template', 'leyka');?>
                                </option>
                
                                <?php 
                                if($templates) {
                                    foreach($templates as $template) {?>
                                    <option value="<?php echo esc_attr($template['id']);?>" <?php selected($cur_template, $template['id']);?>>
                                        <?php esc_html_e(esc_attr($template['name']), 'leyka');?>
                                    </option>
                                <?php }
                                }?>
                
                            </select>
                            
                        </span>
                    </label>
                </div>
                <div class="field-errors"></div>
                
            </div>

        </div>
        
        <div class="decor-preview">
            
            <div class="title">Как будет выглядеть на сайте</div>
            
            <div class="preview-frame">
                <iframe width="300" height="500" src="<?php echo site_url("/?leyka_campaign=".$campaign->post_name."&embed_object=campaign_card&increase_counters=1")?>"></iframe>
            </div>
            
        </div>
        
    </div>
            
    
</div>