<?php if( !defined('WPINC') ) die;
/** Admin Donors list page template */

/** @var $this Leyka_Admin_Setup */?>

<div class="wrap" data-leyka-admin-page-type="donations-list-page">
    <h1 class="wp-heading-inline"><?php _e('Donations', 'leyka');?></h1>

    <div id="poststuff">
        <div>

            <form class="donations-list-filters" action="#" method="get">

                <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']);?>">
                <input type="hidden" name="status" value="<?php echo empty($_GET['status']) ? '' : esc_attr($_GET['status']);?>">

                <div class="col-1">
                    <div class="filters-row"><div class="filter-warning" id="leyka-filter-warning"></div></div>
                </div>

<!--                <div class="col-2">-->
<!--                    <input type="submit" class="button" value="--><?php //_e('Filter the data', 'leyka');?><!--">-->
<!--                     <a href="--><?php //echo admin_url('/admin.php?page=leyka_donations');?><!--" class="reset-filters">--><?php //_e('Reset the filter', 'leyka');?><!--</a>-->
<!--                </div>-->
<!---->
<!--            <div class="donations-list-export"><button>--><?php //_e('Export the list in CSV', 'leyka');?><!--</button></div>-->

                <div id="post-body-content" class="<?php if($this->_donations_list_table->record_count() === 0) {?>empty-donations-list<?php }?>">
                    <div>
                        <?php $this->_donations_list_table->views();
                        $this->_donations_list_table->prepare_items();
                        $this->_donations_list_table->display();?>
                    </div>
                </div>

            </form>

        </div>

    </div>
</div>