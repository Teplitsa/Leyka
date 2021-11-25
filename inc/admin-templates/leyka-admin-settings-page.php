<?php if( !defined('WPINC') ) die;
/** Admin Settings page template */

/** @var $this Leyka_Admin_Setup */

$current_stage = $this->get_current_settings_tab();
$is_separate_sections_forms = $this->is_separate_forms_stage($current_stage);?>

<div class="wrap leyka-admin leyka-settings-page">
    <!-- data-leyka-admin-page-type="extensions-list-page" -->
    <!-- <div class="leyka-admin wrap single-settings extension-settings" data-leyka-admin-page-type="extension-settings-page" data-leyka-extension-id="<?php // echo $extension->id;?>">  -->

    <h1 class="with-country">

        <?php $current_country = leyka_get_countries_full_info(leyka_options()->opt_safe('receiver_country'));?>

        <a href="<?php echo admin_url('admin.php?page=leyka_settings&stage=beneficiary#receiver_country');?>" title="<?php echo $current_country ? sprintf(__('Receiver country: %s', 'leyka'), $current_country['title']) : '';?>">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL.'img/countries/'.leyka_options()->opt_safe('receiver_country').'.svg';?>" alt="" class="country-flag-icon">
        </a>

        <?php _e('Leyka settings', 'leyka');?>

    </h1>

    <h2 class="nav-tab-wrapper"><?php echo $this->settings_tabs_menu();?></h2>

    <div id="tab-container">

        <?php $admin_page_args = ['stage' => $current_stage, 'gateway' => empty($_GET['gateway']) ? '' : $_GET['gateway']];

        $admin_page = 'admin.php?page=leyka_settings';
        foreach($admin_page_args as $arg_name => $value) {
            if($value) {
                $admin_page = add_query_arg($arg_name, $value, $admin_page);
            }
        }

        if( !$is_separate_sections_forms ) {?>

        <form method="post" action="<?php echo admin_url($admin_page);?>" id="leyka-settings-form">

            <?php wp_nonce_field("leyka_settings_{$current_stage}", '_leyka_nonce');

        }

        if(file_exists(LEYKA_PLUGIN_DIR."inc/settings-pages/leyka-settings-{$current_stage}.php")) {
            require_once(LEYKA_PLUGIN_DIR."inc/settings-pages/leyka-settings-{$current_stage}.php");
        } else {

            do_action("leyka_settings_pre_{$current_stage}_fields");

            foreach(leyka_opt_alloc()->get_tab_options($current_stage) as $option) { // Render each option/section

                if($is_separate_sections_forms) {?>

                    <form method="post" action="<?php echo admin_url($admin_page);?>" id="leyka-settings-form">

                    <?php if(isset($option['section']['name'])) {?>
                        <input type="hidden" name="leyka_options_section" value="<?php echo $option['section']['name'];?>">
                    <?php }?>

                    <?php wp_nonce_field("leyka_settings_{$current_stage}", '_leyka_nonce');
                    do_action("leyka_settings_pre_{$current_stage}_fields");

                }

                if(is_array($option) && !empty($option['section'])) {

                    $option['section']['is_separate_sections_forms'] = $is_separate_sections_forms;
                    $option['section']['current_stage'] = $current_stage;

                    do_action('leyka_render_section', $option['section']);

                } else { // is this case even possible?

                    $option_info = leyka_options()->get_info_of($option);
                    do_action("leyka_render_{$option_info['type']}", $option, $option_info);

                }

                if($is_separate_sections_forms) {?>
                    </form>
                <?php }

            }

            do_action("leyka_settings_post_{$current_stage}_fields");?>

            <?php if( !$is_separate_sections_forms ) {?>
                <p class="submit">
                    <input type="submit" name="<?php echo "leyka_settings_{$current_stage}";?>_submit" value="<?php _e('Save settings', 'leyka');?>" class="button-primary">
                </p>
            <?php }

        }

        if( !$is_separate_sections_forms ) {?>
        </form>
    <?php }?>

    </div>

    <?php // include(LEYKA_PLUGIN_DIR.'inc/settings-fields-templates/leyka-helpchat.php');?>

</div>