<?php

function show_wizard_captioned_screenshot($img_path, $img_path_full=false) {
    
    if(!$img_path_full) {
        $img_path_full = $img_path;
    }
    
?>
    <div class="captioned-screen">
        <div class="screen-wrapper">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/<?php echo $img_path?>" class="leyka-instructions-screen" />
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-zoom-screen.svg" class="zoom-screen" />
        </div>
        <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/<?php echo $img_path_full?>" class="leyka-instructions-screen-full" />
    </div>
<?php
}