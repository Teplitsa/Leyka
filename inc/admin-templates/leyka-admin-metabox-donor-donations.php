<?php if( !defined('WPINC') ) die;
/** Admin Donor's info page template */

/** @var $this Leyka_Admin_Setup */

try {
    $donor = new Leyka_Donor(absint($_GET['donor']));
} catch(Exception $e) {
    wp_die(wp_kses_post($e->getMessage()));
}?>

<div class="donations-info">
    <dl>
        <dt><?php esc_html_e('Amount donated:', 'leyka');?></dt>
        <dd><?php echo wp_kses_post( leyka_format_amount($donor->amount_donated).' '.leyka_get_currency_label() );?></dd>

        <dt><?php esc_html_e('Donations number:', 'leyka');?></dt>
        <dd><?php echo esc_html(number_format_i18n($donor->get_donations_count()));?></dd>
    </dl>
</div>

<table id="donations-data-table" class="leyka-data-table donor-info-table" data-donor-id="<?php echo esc_attr( $donor->id );?>">
    <thead>
        <tr>
            <td><?php esc_html_e('ID', 'leyka');?></td>
            <td><?php esc_html_e('Type', 'leyka');?></td>
            <td><?php esc_html_e('Date', 'leyka');?></td>
            <td><?php esc_html_e('Campaign', 'leyka');?></td>
            <td><?php esc_html_e('Amount', 'leyka');?></td>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td><?php esc_html_e('ID', 'leyka');?></td>
            <td><?php esc_html_e('Type', 'leyka');?></td>
            <td><?php esc_html_e('Date', 'leyka');?></td>
            <td><?php esc_html_e('Campaign', 'leyka');?></td>
            <td><?php esc_html_e('Amount', 'leyka');?></td>
        </tr>
    </tfoot>

    <tbody><?php // All table data will be received via AJAX ?></tbody>

</table>