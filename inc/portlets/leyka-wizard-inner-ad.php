<?php if( !defined('WPINC') ) die;

/**
 * Leyka Portlet: plugin Wizard description & link
 * Description: A portlet to display a plugin setup Wizard "inner ad".
 *
 * @var $params
 *
 * Title: #FROM_PARAMS#
 * Subtitle: #FROM_PARAMS#
 * Thumbnail: /img/icon-wizard-stick-only-blue.svg
 **/?>

<div class="wizard-init">

    <p><?php echo $params['text'];?></p>

    <p>
        <a href="<?php echo $params['wizard_link'];?>" class="button button-primary">
            <?php _e('Start the Wizard', 'leyka');?>
        </a>
    </p>

</div>