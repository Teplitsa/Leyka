// Campaign add/edit page:
jQuery(document).ready(function($){

    // Campaign type change:
    $(':input[name="campaign_type"]').on('change.leyka', function(e){

        e.preventDefault();

        let $this = $(this);

        if( !$this.prop('checked') ) {
            return;
        }

        let $persistent_campaign_fields = $('.persistent-campaign-field'),
            $temp_campaign_fields = $('.temporary-campaign-fields'),
            $form_template_field = $(':input[name="campaign_template"]');

        if($this.val() === 'persistent') {

            $persistent_campaign_fields.show();
            $temp_campaign_fields.hide();

            $form_template_field
                .data('prev-value', $form_template_field.val())
                .val('star')
                .prop('disabled', 'disabled');

        } else {

            $persistent_campaign_fields.hide();
            $temp_campaign_fields.show();

            if($form_template_field.data('prev-value')) {
                $form_template_field.val($form_template_field.data('prev-value'));
            }
            $form_template_field.removeProp('disabled');

        }

    }).change();

    // Donation types field change:
    let $donations_types_fields = $(':input[name="donations_type"]'),
        $default_donation_type_field = $('#donation-type-default');
    $donations_types_fields.on('change.leyka', function(e){

        e.preventDefault();

        let donations_types_selected = [];
        $donations_types_fields.filter(':checked').each(function(){
            donations_types_selected.push($(this).val());
        });

        if(donations_types_selected.length > 1) {
            $default_donation_type_field.show();
        } else {
            $default_donation_type_field.hide();
        }

    }).change();

});