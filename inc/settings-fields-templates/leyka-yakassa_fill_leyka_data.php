<?php if( !defined('WPINC') ) die;

/** Custom field group for the Yandex Kassa general info step. */

/** @var $this Leyka_Text_Block A block for which the template is used. */

$yandex_shop_id = leyka_options()->opt('yandex_shop_id');
$yandex_secret_key = leyka_options()->opt('yandex_secret_key');

?>

<p>Переходим к техническому подключению Яндекс Кассы к Лейке.</p>

<div class="<?php echo $this->field_type;?> custom-block-captioned-screens">

    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Перейдите по адресу</div>
        <div class="body value">
            <a target="_blank" href="https://kassa.yandex.ru/joinups">https://kassa.yandex.ru/joinups</a>
        </div>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Скопируйте параметр <b>«ShopID»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_fill_leyka_data-copy-shop-id.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="settings-block custom-block option-block text">
            <?php leyka_render_text_field('yandex_shop_id', array('title' => 'Вставьте параметр в поле', 'placeholder' => 'Ваш ShopID'))?>
        </div>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="caption">Скопируйте параметр <b>«Секретный ключ»</b></div>
        <?php show_wizard_captioned_screenshot("yakassa/yakassa_fill_leyka_data-copy-secret-key.png")?>
    </div>
    
    <div class="enum-separated-block">
        <div class="block-separator"><div></div></div>
        <div class="settings-block custom-block option-block text">
            <?php leyka_render_text_field('yandex_secret_key', array('title' => 'Вставьте параметр в поле', 'placeholder' => 'Секретный ключ'))?>
        </div>
    </div>
    
</div>
