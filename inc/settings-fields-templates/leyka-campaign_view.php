<?php if( !defined('WPINC') ) die;

/** Custom field group for the Campaign decoration Step of the Init wizard. */

/** @var $this Leyka_Custom_Setting_Block A block for which the template is used. */

$campaign_id = get_transient( 'leyka_init_campaign_id' );
$campaign = new Leyka_Campaign($campaign_id);

if( !$campaign->template || $campaign->template === 'default' ) {
    $campaign->template = 'star';
}

//$templates = leyka()->get_templates();

wp_enqueue_media();?>

<input type="hidden" value="<?php echo $campaign_id;?>" id="leyka-decor-campaign-id">

<div id="<?php echo $this->id;?>" class="settings-block custom-block <?php echo $this->field_type;?>">

    <div class="campaign-decor-wrap">

        <div class="decor-form">

            <div id="campaign_photo" class="settings-block option-block upload-photo-field">

                <div id="leyka_campaign_photo-wrapper">
                    <label for="leyka_campaign_photo-field">
                        <span class="field-component title">
                            <?php _e('The campaign thumbnail picture', 'leyka');?>
                            <span class="field-q">
                                <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-q.svg" alt="">
                                <span class="field-q-tooltip">
                                    <?php _e('Set the main picture for your campaign', 'leyka');?>
                                </span>
                            </span>
                        </span>
                        <span class="field-component field">
                            <input type="file" value="">
                            <input type="button" class="button upload-photo" id="campaign_photo-upload-button" value="<?php esc_attr_e('Select a picture', 'leyka');?>">
                        </span>
                    </label>
                    <?php wp_nonce_field('set-campaign-photo', 'set-campaign-photo-nonce');?>
                    <input type="hidden" id="leyka-campaign_thumbnail" name="campaign_thumbnail" value="<?php echo get_post_thumbnail_id($campaign_id);?>">
                </div>
                <div class="field-errors"></div>
                
            </div>

            <input name="campaign_template" type="hidden" style="display:none;" value="<?php echo $campaign->template;?>">
            
            <div id="campaign-decoration-loading">
                 <div class="loader-wrap">
                    <span class="leyka-loader md"></span>
                 </div>
            </div>

        </div>

        <div class="decor-preview">

            <div class="title"><?php esc_html_e('How it will look on the website', 'leyka');?></div>

            <div class="preview-frame <?php echo $campaign->template;?>" id="leyka-preview-frame">
            <?php $embed_code = Leyka_Campaign_Management::get_card_embed_code($campaign_id, false, 343, 700);
                echo str_replace('embed_object=campaign_card', 'embed_object=campaign_card_templated', $embed_code);?>
            </div>

        </div>

    </div>

</div>