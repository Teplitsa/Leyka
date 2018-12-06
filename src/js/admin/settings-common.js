/** Common settings functions */

// Expandable options sections:
jQuery(document).ready(function($){
    $('.leyka-options-section .header h3').click(function(e){
        e.preventDefault();
        var $section = $(this).closest('.leyka-options-section');
        $section.toggleClass('collapsed');
    });
});

// delete fields comments
jQuery(document).ready(function($){
    $('.leyka-admin .leyka-options-section .field-component.help').contents().filter(function(){
        return (this.nodeType == 3);
    }).remove();
});

// rules of the dependence of the set of fields on the legal type
jQuery(document).ready(function($){
    if($('#change_receiver_legal_type').length == 0) {
        return;
    }
    
    console.log('ules of the dependence of the set of fields on the legal type ON');
    console.log($('input[type=radio][name=leyka_receiver_legal_type]:checked').val());
    
    leyka_toggle_sections_dependent_on_legal_type($('input[type=radio][name=leyka_receiver_legal_type]:checked').val());
    $('input[type=radio][name=leyka_receiver_legal_type]').change(function(){
        leyka_toggle_sections_dependent_on_legal_type($('input[type=radio][name=leyka_receiver_legal_type]:checked').val());
    });
    
    function leyka_toggle_sections_dependent_on_legal_type($val) {
        if($val == 'legal') {
            $('#person_terms_of_service').hide();
            $('#beneficiary_person_name').hide();
            $('#person_bank_essentials').hide();
            
            $('#terms_of_service').show();
            $('#beneficiary_org_name').show();
            $('#org_bank_essentials').show();
        }
        else {
            $('#person_terms_of_service').show();
            $('#beneficiary_person_name').show();
            $('#person_bank_essentials').show();
            
            $('#terms_of_service').hide();
            $('#beneficiary_org_name').hide();
            $('#org_bank_essentials').hide();
        }
    }
});

// upload l10n
jQuery(document).ready(function($){
    $('#upload-l10n-button').click(function(){
        
        var $btn = $(this);
        var $loading = $('<span class="leyka-loader xs"></span>');
        
        $btn.parent().append($loading);
        $btn.prop('disabled', true);
        $btn.closest('.content').find('.field-errors').removeClass('has-errors').find('span').empty();
        $btn.closest('.content').find('.field-success').hide();
        
        var actionData = {
            action: 'leyka_upload_l10n'
        };

        console.log(leyka.ajaxurl);
        
        $.post(leyka.ajaxurl, actionData, null, 'json')
            .done(function(json) {
                
                console.log(json);

                if(json.status === 'ok') {
                    $btn.closest('.content').find('.field-success').show();
                    setTimeout(function(){
                            location.reload();
                        }, 500);
                } else if(json.status === 'error' && json.message) {
                    $btn.closest('.content').find('.field-errors').addClass('has-errors').find('span').html(json.message);
                } else {
                    $btn.closest('.content').find('.field-errors').addClass('has-errors').find('span').html('Ошибка!');
                }

            }).fail(function(){
                $btn.closest('.content').find('.field-errors').addClass('has-errors').find('span').html('Ошибка!');
            }).always(function(){
                $loading.remove();
                $btn.prop('disabled', false);
            });
    
    });

});