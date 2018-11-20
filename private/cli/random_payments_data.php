<?php

global $LEYKA_TEST_DATA;

$LEYKA_TEST_DATA = array();

$LEYKA_TEST_DATA['config'] = array(
    'pets' => array(
        'payments_count' => 10000,
        'pm_count' => 5,
    ),
    'children' => array(
        'payments_count' => random_int(10, 20),
        'pm_count' => random_int(1, 2),
    ),
    'forest' => array(
        'payments_count' => random_int(10, 20),
        'pm_count' => random_int(3, 5),
    ),
);

$LEYKA_TEST_DATA['ngos'] = array(
    'pets' => array(
        # NGO data
        'leyka_org_full_name' => 'Фонд помощи бездомным животным "Общий Барсик"',
        'leyka_org_face_fio_ip' => 'Котов Аристарх Евграфович',
        'leyka_org_face_fio_rp' => 'Собакин Евлампий Мстиславович',
        'leyka_org_face_position' => 'Директор',
        'leyka_org_address' => '127001, Россия, Москва, ул. Ленина, д.1, оф.5',

        # reg and bank account
        'leyka_org_state_reg_number' => '1134567890123',
        'leyka_org_kpp' => '223456789',
        'leyka_org_inn' => '333456789012',
        'leyka_org_bank_account' => '44445678901234567890',
        'leyka_org_bank_name' => 'МЯО Звербанк',
        'leyka_org_bank_bic' => '555556789',
        'leyka_org_bank_corr_account' => '66666678901234567890',

        // View settings:
        'leyka_donation_form_template' => 'revo',
        'leyka_donation_sum_field_type' => 'mixed',
        'leyka_scale_widget_place' => '-',
        'leyka_donations_history_under_forms' => 0,
        'leyka_show_campaign_sharing' => 0,

         // Misc settings:
        'leyka_agree_to_terms_needed' => 1,
        'leyka_terms_agreed_by_default' => 1,
        'leyka_agree_to_terms_text_text_part' => __('I accept', 'leyka'),
        'leyka_agree_to_terms_text_link_part' => __('Terms of Service', 'leyka'),
    ),
    'children' => array(
        # NGO data
        'leyka_org_full_name' => 'Роботизированный детский дом "Кибер-папа"',
        'leyka_org_face_fio_ip' => 'Бот Электрон Андроидович',
        'leyka_org_face_fio_rp' => 'Ардуинов Скрэтч Альбертович',
        'leyka_org_face_position' => 'Директор',
        'leyka_org_address' => '127001, Россия, Москва, ул. Будущего, д.23, оф.13',

        # reg and bank account
        'leyka_org_state_reg_number' => '1111111111111',
        'leyka_org_kpp' => '222222222',
        'leyka_org_inn' => '333333333333',
        'leyka_org_bank_account' => '44445678901234567890',
        'leyka_org_bank_name' => 'ПАО НаноТехноПсевдоБанк',
        'leyka_org_bank_bic' => '555556789',
        'leyka_org_bank_corr_account' => '66666678901234567890',

        // View settings:
        'leyka_donation_form_template' => 'neo',
        'leyka_donation_sum_field_type' => 'mixed',
        'leyka_scale_widget_place' => '-',
        'leyka_donations_history_under_forms' => 1,
        'leyka_show_campaign_sharing' => 1,

         // Misc settings:
        'leyka_agree_to_terms_needed' => 1,
        'leyka_terms_agreed_by_default' => 1,
        'leyka_agree_to_terms_text_text_part' => __('I accept', 'leyka'),
        'leyka_agree_to_terms_text_link_part' => __('Terms of Service', 'leyka'),
    ),
    'forest' => array(
        # NGO data
        'leyka_org_full_name' => 'Дружина пожарных "Бобры добры"',
        'leyka_org_face_fio_ip' => 'Бобров Степан Игнатович',
        'leyka_org_face_fio_rp' => 'Боброва Августина Ефремовна',
        'leyka_org_face_position' => 'Директор',
        'leyka_org_address' => '127001, Россия, Москва, пер. Запрудный, д.1',

        # reg and bank account
        'leyka_org_state_reg_number' => '1111111111111',
        'leyka_org_kpp' => '222222222',
        'leyka_org_inn' => '333333333333',
        'leyka_org_bank_account' => '44445678901234567890',
        'leyka_org_bank_name' => 'ПАО ЭкоЗдравСпасБанк',
        'leyka_org_bank_bic' => '555556789',
        'leyka_org_bank_corr_account' => '66666678901234567890',

        // View settings:
        'leyka_donation_form_template' => 'radios',
        'leyka_donation_sum_field_type' => 'mixed',
        'leyka_scale_widget_place' => '-',
        'leyka_donations_history_under_forms' => 1,
        'leyka_show_campaign_sharing' => 1,

         // Misc settings:
        'leyka_agree_to_terms_needed' => 1,
        'leyka_terms_agreed_by_default' => 1,
        'leyka_agree_to_terms_text_text_part' => __('I accept', 'leyka'),
        'leyka_agree_to_terms_text_link_part' => __('Terms of Service', 'leyka'),
    ),
);

$LEYKA_TEST_DATA['campaigns'] = array(
    'pets' => array(
        array('name' => 'build-house-for-pets', 'title' => 'Строим жилье для питомцев', 'target' => 2700000.0, 'thumbnail' => 'dog001.jpg', 'content' => <<<EOT
Ритмоединица, по определению, регрессийно представляет собой септаккорд. Показательный пример – midi-контроллер иллюстрирует звукорядный канал. Аллюзийно-полистилистическая композиция образует контрапункт контрастных фактур, в таких условиях можно спокойно выпускать пластинки раз в три года.

Ощущение мономерности ритмического движения возникает, как правило, в условиях темповой стабильности, тем не менее явление культурологического порядка монотонно вызывает дорийский флэнжер. Серпантинная волна, следовательно, использует контрапункт контрастных фактур. Еще Аристотель в своей «Политике» говорил, что музыка, воздействуя на человека, доставляет «своего рода очищение, то есть облегчение, связанное с наслаждением», однако эффект «вау-вау» полифигурно выстраивает септаккорд.

Показательный пример – кластерное вибрато выстраивает сонорный фузз. Пуантилизм, зародившийся в музыкальных микроформах начала ХХ столетия, нашел далекую историческую параллель в лице средневекового гокета, однако форшлаг просветляет гармонический интервал, это и есть одномоментная вертикаль в сверхмногоголосной полифонической ткани. Септаккорд имеет изоритмический хорус. Нота, так или иначе, просветляет однокомпонентный рефрен. Аллегро иллюстрирует самодостаточный гармонический интервал.
EOT
        ),
        array('name' => 'buy-food-for-kittens', 'title' => 'Покупаем еду для котят', 'target' => 1500000.0, 'thumbnail' => 'cat001.jpg', 'content' => <<<EOT
Беспошлинный ввоз вещей и предметов в пределах личной потребности, куда входят Пик-Дистрикт, Сноудония и другие многочисленные национальные резерваты природы и парки, оформляет бассейн нижнего Инда.

Утконос прочно вызывает тюлень, именно здесь с 8.00 до 11.00 идет оживленная торговля с лодок, нагруженных всевозможными тропическими фруктами, овощами, орхидеями, банками с пивом. Пустыня уязвима. Горная река изящно входит круговорот машин вокруг статуи Эроса, при этом имейте в виду, что чаевые следует оговаривать заранее, так как в разных заведениях они могут сильно различаться.

Акцентируется не красота садовой дорожки, а растительный покров входит урбанистический белый саксаул, здесь есть много ценных пород деревьев, таких как железное, красное, коричневое (лим), черное (гу), сандаловое деревья, бамбуки и другие виды. Население, при том, что королевские полномочия находятся в руках исполнительной власти - кабинета министров, последовательно просветляет протяженный портер. Административно-территориальное деление оформляет традиционный растительный покров, а чтобы сторож не спал и был добрым, ему приносят еду и питье, цветы и ароматные палочки.
EOT
        ),
        array('name' => 'heal-kid-after-kitten-attack', 'title' => 'Требуется лечение душевной травмы', 'target' => 6500.0, 'thumbnail' => 'child001.jpeg', 'content' => <<<EOT
Береговая линия притягивает бахрейнский динар, здесь сохранились остатки построек древнего римского поселения Аквинка - "Аквинкум". Море традиционно. На коротко подстриженной траве можно сидеть и лежать, но поваренная соль дегустирует шведский антарктический пояс.

Очаг многовекового орошаемого земледелия поднимает протяженный кит. Южное полушарие входит крестьянский санитарный и ветеринарный контроль. Кандым просветляет гидроузел. Герцеговина, в первом приближении, входит протяженный очаг многовекового орошаемого земледелия.

Памятник Нельсону теоретически возможен. Озеро Ньяса вразнобой дегустирует культурный эфемероид. Здесь работали Карл Маркс и Владимир Ленин, но Амазонская низменность отталкивает протяженный культурный ландшафт.
EOT
        ),
        array('name' => 'treat-pets', 'title' => 'Лечим больных животных', 'target' => 0, 'content' => ""),
        array('name' => 'treat-pets-done', 'title' => 'Лечим Барсика - успешно', 'target' => 800.0, 'content' => ""),
    ),
    'children' => array(
        array('name' => 'need-energy', 'title' => 'Собираем на оплату электроэнергии', 'target' => 0, 'content' => ""),
        array('name' => 'upgrade-cluster', 'title' => 'На новый процессор для управления персоналом', 'target' => 300000.0, 'content' => ""),
        array('name' => 'build-robo-nanny-done', 'title' => 'Строим робота-няню для грудничков - успешно', 'target' => 800.0, 'content' => ""),
    ),
    'forest' => array(
        array('name' => 'maintain-workers', 'title' => 'Содержание бобров-прогрызсчиков', 'target' => 0, 'content' => "Они прогрызают просеки по 20 часов в сутки. Мы должны обеспечивать им комфортный отдых и быстрое восстановление."),
        array('name' => 'workers-vacation', 'title' => 'Поощрительная поездка бобров-героев в Анапу', 'target' => 150000.0, 'content' => ""),
        array('name' => 'build-comfortable-cages-done', 'title' => 'Стоим комфортное жилье для бобров-трудяг - успешно', 'target' => 1200.0, 'content' => ""),
    ),
);

$LEYKA_TEST_DATA['donors'] = array(
    array('name' => 'Мартынов Семен Семенович', 'email' => 'test.martinov@ngo2.ru'),
    array('name' => 'Коровин Остап Рудольфович', 'email' => 'test.korovin@ngo2.ru'),
    array('name' => 'Быков Иван Иванович', 'email' => 'test.bikov@ngo2.ru'),
    array('name' => 'Лось Вениамин Робертович', 'email' => 'test.los@ngo2.ru'),
    array('name' => 'Зайцев Иван Митрофанович', 'email' => 'test.zaitesev@ngo2.ru'),
    array('name' => 'Лисицин Модест Петрович', 'email' => 'test.lisitsin@ngo2.ru'),
    array('name' => 'Скворцова Евлампия Прокофьевна', 'email' => 'test.skvortsova@ngo2.ru'),
    array('name' => 'Кац Изольда Альбертовна', 'email' => 'test.kats@ngo2.ru'),
    array('name' => 'Лебедева Наина Иосифовна', 'email' => 'test.lebedeva@ngo2.ru'),
    array('name' => 'Соколова Октябрина Космосовна', 'email' => 'test.sokolova@ngo2.ru'),
    array('name' => 'Медведева Тамара Л', 'email' => 'test.medvedeva@ngo2.ru'),
);

$LEYKA_TEST_DATA['payment_methods'] = array(
    'yandex' => array('yandex_all', 'yandex_card', 'yandex_money', 'yandex_wm', 'yandex_sb', 'yandex_ab', 'yandex_pb'),
    'mixplat' => array('sms', 'mobile'),
    'quittance' => array('bank_order'),
    'text' => array('text_box'),
    'chronopay' => array('chronopay_card'),
    'cp' => array('card'),
    'paymaster' => array('paymaster_all'),
    'paypal' => array('paypal_all'),
    'rbk' => array('bankcard'),
    'robokassa' => array('BANKOCEAN2', 'YandexMerchantOcean', 'WMR', 'Qiwi30Ocean', 'Other'),
    'yandex_phyz' => array('yandex_phyz_money', 'yandex_phyz_card'),
);

$LEYKA_TEST_DATA['gateways_options'] = array(
    'chronopay' => array(
        'leyka_chronopay_use_payment_uniqueness_control' => '1',
        'leyka_chronopay_ip' => '185.30.16.166',
        'leyka_chronopay_shared_sec' => '123123123123123123123123',
        'leyka_chronopay-chronopay_card_label' => 'Банковская карта',
        'leyka_chronopay_card_rebill_product_id_eur' => '111111111111',
        'leyka_chronopay_card_rebill_product_id_usd' => '222222222222',
        'leyka_chronopay_card_rebill_product_id_rur' => '333333333333',
        'leyka_chronopay_card_product_id_eur' => '444444444444',
        'leyka_chronopay_card_product_id_usd' => '555555555555',
        'leyka_chronopay_card_product_id_rur' => '666666666666',
        'leyka_chronopay-chronopay_card_description' => 'Описание',
    ),
    'rbk' => array(
        'leyka_rbk_api_web_hook_key' => '333333333333333',
        'leyka_rbk_api_key' => '2222222222222',
        'leyka_rbk_shop_id' => '11111111111',
        'leyka_rbk_secret_key' => '',
        'leyka_rbk_hash_type' => 'md5',
        'leyka_rbk_use_hash' => '0',
        'leyka_rbk_eshop_account' => '',
        'leyka_rbk_eshop_id' => '',
        'leyka_rbk-rbk_all_label' => 'RBK Money (любой способ платежа)',
        'leyka_rbk-rbk_all_description' => 'Описание',
        'leyka_rbk-rbkmoney_label' => 'RBK Money',
        'leyka_rbk-rbkmoney_description' => 'Описание',
        'leyka_rbk-bankcard_label' => 'Банковская карта',
        'leyka_rbk-bankcard_description' => 'Описание',
    ),
    'quittance' => array(
        'leyka_quittance_redirect_page' => '7',
        'leyka_quittance-bank_order_label' => 'Банковская платёжная квитанция',
        'leyka_quittance-bank_order_description' => 'Описание',
    ),
    'paymaster' => array(
        'leyka_paymaster_hash_method' => 'md5',
        'leyka_paymaster_secret_word' => '123123',
        'leyka_paymaster_merchant_id' => 'merchant',
        'leyka_paymaster-paymaster_all_label' => 'Paymaster - умный платеж',
        'leyka_paymaster-paymaster_all_description' => 'Описание',
    ),
    'yandex' => array(
        'leyka_yandex-yandex_pb_label' => 'Промсвязьбанк',
        'leyka_yandex-yandex_pb_description' => 'Описание',
        'leyka_yandex-yandex_ab_label' => 'Альфа-клик',
        'leyka_yandex-yandex_ab_description' => 'Описание',
        'leyka_yandex-yandex_sb_label' => 'Сбербанк Онлайн',
        'leyka_yandex-yandex_sb_description' => 'Описание',
        'leyka_yandex-yandex_wm_label' => 'WebMoney',
        'leyka_yandex-yandex_wm_description' => 'Описание',
        'leyka_yandex-yandex_money_label' => 'Яндекс.деньги',
        'leyka_yandex-yandex_money_description' => 'Описание',
        'leyka_yandex-yandex_card_label' => 'Банковская карта',
        'leyka_yandex-yandex_card_description' => 'Описание',
        'leyka_yandex-yandex_card_private_key_password' => '123123',
        'leyka_yandex-yandex_card_private_key_path' => '/path/to/my/ssl.cert',
        'leyka_yandex-yandex_card_certificate_path' => '/path/to/my/recur.cert',
        'leyka_yandex-yandex_card_rebilling_available' => '0',
        'leyka_yandex-yandex_all_label' => 'Яндекс.Касса - умный платеж',
        'leyka_yandex-yandex_all_description' => 'Описание',
    ),
    'text' => array(
        'leyka_text-text_box_label' => 'Дополнительные способы',
        'leyka_text-text_box_description' => 'Описание',
    ),
    'mixplat' => array(
        'leyka_mixplat_test_mode' => '0',
        'leyka_mixplat_secret_key' => '22222222222222',
        'leyka_mixplat_service_id' => '1111111111111111',
        'leyka_mixplat-sms_label' => 'Платежи с помощью SMS',
        'leyka_mixplat-sms_details' => 'Чтобы сделать пожертвование, отправьте SMS',
        'leyka_mixplat-sms_description' => '',
        'leyka_mixplat-sms_default_campaign_id' => '9',
        'leyka_mixplat-mobile_label' => 'Мобильный платёж',
        'leyka_mixplat-mobile_details' => '',
        'leyka_mixplat-mobile_description' => 'Являет процесс.',
    ),
    'robokassa' => array(
        'leyka_robokassa_test_mode' => '0',
        'leyka_robokassa_shop_password2' => '456456456',
        'leyka_robokassa_shop_password1' => '123123123',
        'leyka_robokassa_shop_id' => '11111111111111',
        'leyka_robokassa-Other_label' => 'Робокасса (любой способ платежа)',
        'leyka_robokassa-Other_description' => 'Описание',
        'leyka_robokassa-Qiwi30Ocean_label' => 'Qiwi-кошелёк',
        'leyka_robokassa-Qiwi30Ocean_description' => 'Описание',
        'leyka_robokassa-WMR_label' => 'WebMoney',
        'leyka_robokassa-WMR_description' => 'Описание',
        'leyka_robokassa-YandexMerchantOcean_label' => 'Яндекс.Деньги',
        'leyka_robokassa-YandexMerchantOcean_description' => 'Описание',
        'leyka_robokassa-BANKOCEAN2_label' => 'Банковская карта',
        'leyka_robokassa-BANKOCEAN2_description' => 'Описание',
    ),
    'paypal' => array(
        'leyka_paypal_keep_payment_logs' => '0',
        'leyka_paypal_accept_verified_only' => '0',
        'leyka_paypal_enable_recurring' => '1',
        'leyka_paypal_test_mode' => '1',
        'leyka_paypal_client_id' => '44444444444444444',
        'leyka_paypal_api_signature' => '33333333333333',
        'leyka_paypal_api_password' => '222222222222222',
        'leyka_paypal_api_username' => '1111111111111111',
        'leyka_paypal-paypal_all_label' => 'PayPal',
        'leyka_paypal-paypal_all_description' => 'Описание',
    ),
    'cp' => array(
        'leyka_cp_test_mode' => '1',
        'leyka_cp_ip' => '111111111111111111111111',
        'leyka_cp_public_id' => 'pk_87cbbfddf3dc5eb1cba749a051789',
        'leyka_cp-card_label' => 'Банковская карта',
        'leyka_cp-card_description' => 'Описание',      
    ),
    'yandex_phyz' => array(
        'leyka_yandex_money_secret' => '2222222222222222222',
        'leyka_yandex_money_account' => '1111111111111111111111',
        'leyka_yandex_phyz-yandex_phyz_money_label' => 'Виртуальная валюта Яндекс.Деньги',
        'leyka_yandex_phyz-yandex_phyz_money_description' => 'Описание',
        'leyka_yandex_phyz-yandex_phyz_card_label' => 'Банковская карта',
        'leyka_yandex_phyz-yandex_phyz_card_description' => 'Описание',
    ),
);
