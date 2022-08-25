<?php if ( !defined('WPINC') ) die;

require_once LEYKA_PLUGIN_DIR.'extensions/dashamail/lib/DashamailApi.php';

/**
 * Leyka Extension: Dashamail extension
 * Version: 1.0
 * Author: DashaMail
 * Author URI: https://dashamail.ru
 * Debug only: 0
 **/

class Leyka_Dashamail_Extension extends Leyka_Extension {
    
    protected static $_instance;
    
    protected function _set_attributes() {
        
    $this->_id = 'dashamail';
    $this->_title = 'DashaMail';
    $this->_description = 'Расширение предоставляет возможность интеграции с сервисом DashaMail.';
    $this->_full_description = 'Расширение позволяет осуществлять автоматическую подписку доноров в адресные базы сервиса DashaMail';
    $this->_settings_description = '';
    $this->_connection_description = 
        '<h4>Краткая инструкция:</h4>
        <div>
            <ol>
                <li>Зарегистрируйте аккаунт в сервисе DashaMail</li>
                <li>Создайте адресную базу, в которую будут записываться данные доноров</li>
                <li>Скопируйте API-ключ из личного кабинета DashaMail в поле настроек расширения</li>
                <li>Выберите желаемые опции подтверждения подписки и перезаписи информации о доноре</li>
            </ol>
        </div>';
    $this->_user_docs_link = '';
    $this->_has_wizard = false;
    $this->_has_color_options = false;
    $this->_icon = LEYKA_PLUGIN_BASE_URL.'extensions/dashamail/img/dasha_icon.png';
    }


    protected function _set_options_defaults() {

        $this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', [
            [
                'section' => [
                    'name' => $this->_id.'-main-options',
                    'title' => 'Основные опции',
                    'options' => [
                        $this->_id.'_api_key' => [
                            'type' => 'text',
                            'title' => 'Укажите Ваш API-ключ DashaMail',
                            'description' => 'После ввода API-ключа сохраните настройки расширения',
                            'required' => true,
                            'is_password' => true,
                            'placeholder' => sprintf('abcdefghij0123456789'),
                            'comment' => 'API-ключ находится в Вашем личном кабинете DashaMail, в разделе "Интеграции".'
                        ], 
                        $this->_id.'_lists' => [
                            'type' => 'select',
                            'title' => 'Выберите адресную базу DashaMail, в которую будет записана информация о доноре',
                            'required' => true,
                            'comment' => 'Рекомендуется создать новую адресную базу, не содержащую дополнительных полей',
                            'description' => 'Список Ваших адресных баз будет отображен только после сохранения API-ключа',
                            'default' => ['name'],
                            'list_entries' => $this->_get_donor_lists(),
                        ],
                        $this->_id.'_donor_fields' => [
                            'type' => 'static_text',
                            'title' => 'Поля, передаваемые в адресную базу DashaMail:',
                            'description' => 'Существует ограничение на количество дополнительных полей - в выбранную адресную базу может быть добавлено не более 9. Поля "Имя" и "Email" являются основными и по умолчанию будут записаны в адресную базу',
                            'is_html' => true,
                            'value' => $this->_get_all_donor_fields(),
                        ],
                        $this->_id.'_donor_confirmation' => [
                            'type' => 'checkbox',
                            'default' => true,
                            'title' => 'Подтвердить подписку донора дополнительным письмом?',
                            'comment' => 'Если опция выбрана, то донор получит письмо с запросом подтверждения его подписки в DashaMail',
                            'short_format' => true
                        ],
                        $this->_id.'_donor_overwrite' => [
                            'type' => 'radio',
                            'title' => 'Перезаписывать информацию о доноре, если он уже присутствует в адресной базе?',
                            'comment' => 'В случае, когда донор уже был добавлена в адресную базу DashaMail',
                            'default' => '0',
                            'list_entries' => [
                                '0' => 'Не перезаписывать данные донора новыми значениями в адресной базе',
                                '1' => 'Перезаписывать данные донора новыми значениями в адресной базе',
                            ]
                        ]
                    ]
                ]
            ],
            [
                'section' => [
                    'name' => $this->_id.'-logs',
                    'title' => 'Лог ошибок интеграции',
                    'is_default_collapsed' => true,
                    'options' => [
                        $this->_id.'_error_log' => [
                            'type' => 'static_text',
                            'title' => '',
                            'is_html' => true,
                            'value' => $this->_render_dashamail_error_log()
                        ]
                    ]
                ]
            ]
        ]);
    }

    protected function _get_donor_fields() {

        $fields_library = leyka_options()->opt('additional_donation_form_fields_library');
        foreach($fields_library as $name => $data) {
            $additional_fields[$name] = $data['title'];
        }

        return $additional_fields;
    }

    protected function _get_all_donor_fields() {

        $additional_fields = '<span>1. Email</span><br><span>2. Имя</span><br>';
        $num = 3;
        $arr_donor_fields = $this->_get_donor_fields();
        foreach($arr_donor_fields as $name => $data) {
            $additional_fields = $additional_fields.'<span>'.$num.'. '.$data.'</span><br>';
            $num++;
        }

        return $additional_fields;
    }


    protected function _get_donor_lists() { // get all user's lists via DashaMail API-method

        $apiClass = new \Dashamail\ApiWrapper\DashamailApi(leyka_options()->get_option_value($this->_id.'_api_key'));
        $json_lists = $apiClass->getLists([]);
        $full_lists = json_decode($json_lists, true)['response']['data'];
        $error_code = json_decode($json_lists, true)['response']['msg']['err_code'];
        $error_text = json_decode($json_lists, true)['response']['msg']['text'];

        $error_result = [$error_code => $error_text];
        $error_result = [
                'error_date' => date('d.m.Y H:i'),
                'error_code' => $error_code,
                'error_text' => $error_text,
        ];

        $this->_error_handle($error_result);

        $lists = [];

        foreach ($full_lists as $lists_item) {
            $lists[$lists_item['id']] = $lists_item['name'];
        }

        if ($lists == []) {
            return ['res' => 'Пусто'];
        } else {
            
            return $lists;
        }
    }

    protected function _error_handle($error_data) {

        $result_array = $error_data;
        $error_log = !get_option('leyka_dashamail_error_log') ? [] : get_option('leyka_dashamail_error_log');
        $error_log[] = json_encode($result_array);

        if(sizeof($error_log) > 10) {
            array_shift($error_log);
        }

        update_option('leyka_dashamail_error_log', $error_log, 'no');

    }

    public function _render_dashamail_error_log() {

        if( !get_option('leyka_dashamail_error_log') || !is_array(get_option('leyka_dashamail_error_log')) ) {
            return '';
        }

        $error_log = get_option('leyka_dashamail_error_log');
        $error_log_text = '';

        foreach($error_log as $index => $error_data_str) {

            $error_data = json_decode($error_data_str, true);

            $error_log_text .= $error_data['error_code'] != 0 ? '<li><b>'.$error_data['error_date'].': </b> error code <b>'.$error_data['error_code'].'</b> - '.$error_data['error_text'].'</li>' : '';
        }

        return $error_log_text;
    }

    protected function _initialize_active() {
        add_action('admin_enqueue_scripts', [$this, '_load_admin_assets']);
        add_action('leyka_donation_funded_status_changed', [$this, '_add_donor_to_dashamail_list'], 11, 3);
    }

    public function _add_donor_to_dashamail_list($donation_id, $old_status, $new_status) {

        $apiClass = new \Dashamail\ApiWrapper\DashamailApi(leyka_options()->get_option_value($this->_id.'_api_key'));
        $donation = Leyka_Donations::get_instance()->get($donation_id);
        $overwrite = leyka_options()->opt($this->_id.'_donor_overwrite');

        if ($donation) {
            add_action('leyka_donation_funded_status_changed', [$this, '_add_donor_to_dashamail_list'], 11, 3);
        }

        if ($donation->payment_type === 'rebill' && $donation->init_recurring_donation_id !== $donation->id) {
            return false;
        }

        if ($old_status == 'funded' || $new_status == 'funded' || $old_status !== 'funded' || $new_status !== 'funded') {

            $donor_list_id = leyka_options()->get_option_value($this->_id.'_lists');
            $donor_fields = ['email' => $donation->donor_email];
            $arr_donor_fields = $this->_get_donor_fields();

            foreach($arr_donor_fields as $field_name) {
                if ($field_name === 'name') {
                    $donor_fields[$field_name] = $donation->donor_name;
                } 
                $donation_additional_fields = $donation->additional_fields;
            }
        }

            $curr_donater = json_decode($apiClass->getMember(['list_id'=>$donor_list_id, 'email'=>$donor_fields['email']]));
            if ($curr_donater->response->msg->err_code == 0) {
                $curr_donater_presence = true;
            } else {
                $curr_donater_presence = false;
            }

            $curr_donater_merges = [];
            $j = 1;
            foreach ($curr_donater->response->data[0] as $key=>$value) {
                if ($key == strval('merge_'.$j)) {
                    $curr_donater_merges['merge_'.$j] = $value;
                    $j++;
                }
            };

            $curr_list = json_decode($apiClass->getLists(['list_id'=>$donor_list_id, 'merge_json']));
            $curr_list_merges = [];
            $curr_list_merges_titles = [];
            $i = 1;
            foreach ($curr_list->response->data[0] as $key=>$value) {
                if ($key == strval('merge_'.$i) && $value !== '') {
                    $curr_list_merges['merge_'.$i] = unserialize($value)['title'];
                    $curr_list_merges_titles[unserialize($value)['title']] = ['merge_'.$i];
                    $i++;
                }
            };

            $summary_current_merges = [];
            foreach($curr_list_merges as $key=>$value) {
                foreach($curr_donater_merges as $k=>$v) {
                    if ($key == $k) {
                        $summary_current_merges[$key] = ['title'=>$value, 'value'=>$v];
                    }
                }
            }


            

            $donation_additional_fields = array_merge(['Имя' => $donation->donor_name], $donation_additional_fields);
            $params = ['list_id' => $donor_list_id,
                'email' => $donor_fields['email'],
                'merge_1' => $donation->donor_name,
            ];

            
            if ($donation_additional_fields !== []) {
                $additional_params = [];
                $i = 1;
                foreach ($donation_additional_fields as $field=>$value) { 
                    if ($i <= 10) {
                        if ($overwrite == 1) {
                            $params = array_merge($params, ['update'=>'']);
                        }

                        foreach ($arr_donor_fields as $lname=>$rname) {
                            if ($lname == $field) {
                                $field = $rname;
                            }
                        }

                        if (!in_array($field, $curr_list_merges) && !in_array($value, $curr_list_merges_titles) && $i<=10) {
                            
                            $newMerge = $apiClass->addMerge(['list_id'=>$donor_list_id, 'type'=>'text', 'title'=>$field, 'var'=>str_replace(' ', '_', mb_strtoupper($field, 'UTF-8'))]);
                            
                            $error_code = json_decode($newMerge, true)['response']['msg']['err_code'];
                            $error_text = json_decode($newMerge, true)['response']['msg']['text'];

                            $error_result = [$error_code => $error_text];
                            $error_result = [
                                    'error_date' => date('d.m.Y H:i'),
                                    'error_code' => $error_code,
                                    'error_text' => $error_text,
                            ];

                            $this->_error_handle($error_result);
                        }

                        $curr_list2 = json_decode($apiClass->getLists(['list_id'=>$donor_list_id, 'merge_json']));
                        
                        $m = 1;
                        foreach ($curr_list2->response->data[0] as $k=>$v) {
                            if ($k == strval('merge_'.$m) && $v !== '') {
                                $curr_list_merges['merge_'.$m] = unserialize($v)['title'];
                                $curr_list_merges_titles[unserialize($v)['title']] = ['merge_'.$m];
                                $m++;
                            }
                        };

                        foreach($curr_list_merges as $merge=>$val) {
                            if ($val == $field) {
                                
                                $additional_params[$merge] = $value;

                            }
                        }
                    }
                    $i++;
                }

                $params = array_merge($params, $additional_params);
            }

            $confirm = leyka_options()->opt($this->_id.'_donor_confirmation');

            if ($confirm == 1 && $curr_donater_presence == false) {

                $url = 'https://forms.dmsubscribe.ru/';
                $params = array_merge($params, ['no_conf' => '', 'notify' => '']);

                $content = http_build_query($params);
                $contextOptions = [
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-type: application/x-www-form-urlencoded;charset=utf-8',
                        'content' => $content,
                    ],
                    'ssl' => [
                        'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
                    ]
                ];
                $context = stream_context_create($contextOptions);
                $result = @file_get_contents($url, false, $context);

            } else {
                $result = $apiClass->addMember($params);

                $error_code = json_decode($result, true)['response']['msg']['err_code'];
                $error_text = json_decode($result, true)['response']['msg']['text'];

                $error_result = [$error_code => $error_text];
                $error_result = [
                        'error_date' => date('d.m.Y H:i'),
                        'error_code' => $error_code,
                        'error_text' => $error_text,
                ];

                $this->_error_handle($error_result);
            }
    }

    public function _load_admin_assets() {

        if($this->is_admin_settings_page($this->_id)) {

            wp_enqueue_style(
                $this->_id.'-admin',
                self::get_base_url().'/assets/css/admin.css',
                [],
                defined('WP_DEBUG_DISPLAY') && WP_DEBUG_DISPLAY ? uniqid() : null
            );

            wp_enqueue_script(
                $this->_id.'-admin',
                self::get_base_url().'/assets/js/admin.js',
                ['jquery']
            );

        }

    }
}



function leyka_add_extension_dashamail() {

leyka()->add_extension(Leyka_Dashamail_Extension::get_instance());

}

add_action('leyka_init_actions', 'leyka_add_extension_dashamail');