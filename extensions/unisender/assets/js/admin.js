/** Unisender extension - admin JS */

jQuery(document).ready(function($){

    const $donor_fields = $('#leyka_unisender_donor_fields-field');

    if ($donor_fields) {

        update_donor_fields_description();

        $donor_fields.on('change', () => {update_donor_fields_description()});

        function update_donor_fields_description() {

            const donor_fields = $donor_fields.val();
            const $donor_fields_description = $('#leyka_unisender_donor_fields-wrapper .help');
            let description = '';

            donor_fields.forEach((field, idx) => {

                if(field === 'phone') return;
                description += (idx === 0 ? ' ' : ', ')+'"'+field.replaceAll('-', '_')+'"';

            })

            if(description === '') {
                $donor_fields_description.hide();
            } else {

                $donor_fields_description.show();

                // TODO: В классе юнисендера не срабатывает хук "leyka_js_localized_strings".
                //  Разобраться в чем дело и настроить нормальную локализацию JS строк
                //description = leyka.donor_fields_description_hint + '<span class="leyka-unisender-donor-fields-vars">' + description + '</span>';
                description = 'Подсказка: чтобы получить данные полей в Unisender, список должен иметь поля с следующими переменными -<span class="leyka-unisender-donor-fields-vars">'+description+'</span>';

                $donor_fields_description.html(description);

            }

        }

    }

});

