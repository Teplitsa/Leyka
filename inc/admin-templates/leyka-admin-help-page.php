<?php if( !defined('WPINC') ) die;
/** Admin Dashboard page template */

/** @var $this Leyka_Admin_Setup */?>

<div class="wrap leyka-admin leyka-info-sidebar-page leyka-help-page">

    <h1><?php _e('Helper', 'leyka');?></h1>

    <div class="leyka-page-content">

        <div class="main-col">

            <?php $metaboxes_area_id = 'dashboard_page_leyka_help';?>
            <input type="hidden" class="leyka-support-metabox-area" value="<?php echo $metaboxes_area_id;?>">

            <?php do_meta_boxes($metaboxes_area_id, 'normal', NULL);?>

            <div class="leyka-content-row wizards-row">

                <?php $this->show_admin_portlet('wizard-inner-ad', [
                    'title' => __('Base setup', 'leyka'),
                    'subtitle' => __('Setup Wizard', 'leyka'),
                    'text' => __("You installed the Leyka plugin, all that's left is to set it up. We will guide you through all the steps and help with tips.", 'leyka'),
                    'wizard_link' => admin_url('admin.php?page=leyka_settings_new&screen=wizard-init'),
                ]);

                $this->show_admin_portlet('wizard-inner-ad', [
                    'title' => __('YooKassa', 'leyka'),
                    'subtitle' => __('Setup Wizard', 'leyka'),
                    'text' => leyka_get_gateway_by_id('yandex')->description,
                    'wizard_link' => admin_url('admin.php?page=leyka_settings_new&screen=wizard-yandex'),
                ]);

                $this->show_admin_portlet('wizard-inner-ad', [
                    'title' => __('CloudPayments', 'leyka'),
                    'subtitle' => __('Setup Wizard', 'leyka'),
                    'text' => leyka_get_gateway_by_id('cp')->description,
                    'wizard_link' => admin_url('admin.php?page=leyka_settings_new&screen=wizard-cp'),
                ]);?>

            </div>

            <?php do_meta_boxes($metaboxes_area_id, 'lower', null);?>

        </div>

        <div class="sidebar-col">
            <?php $this->_show_admin_template('dashboard-sidebar');?>
        </div>

    </div>

</div>