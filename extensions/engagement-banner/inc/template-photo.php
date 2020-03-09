<?php if( !defined('WPINC') ) die;

/** @var $photo array */ ?>
<div class='engb-photo-block'>
    <div class='engb-photo-image'><?php echo $photo['image'];?></div>
        <div class='engb-photo-content'>
        <div class='engb-photo-name'><?php echo esc_html($photo['name']); ?></div>
        <div class='engb-photo-role'><?php echo esc_html(esc_html($photo['role'])); ?></div>
    </div>
</div>