<?php if( !defined('WPINC') ) die;
/**
 * Extension name: Support Packages
 * Version: 0
 * Author: Teplitsa of social technologies
 * Author URI: https://te-st.ru
 * Debug only: 0
 **/

class Leyka_Support_Packages_Extension extends Leyka_Extension {

    public static $max_packages_number = 5;
    public static $FEATURES = array(
        'leyka_limited_content' => array(
            'class' => 'Leyka_Support_Packages_Limit_Content_Feature',
            'is_shortcode' => true,
            'shortcode_atts' => array('support_plan' => '')
        ),
    );
    
    protected static $_instance;
    protected $_packages = null;

    protected function _set_attributes() {

        $this->_id = 'support_packages'; // Must be a unique string, like "support-packages"
        $this->_title = __('Support packages', 'leyka'); // A human-readable title, like "Support packages"

        // A human-readable short description (for backoffice extensions list page):
        $this->_description = 'Это небольшое описание расширения, символов на 100-130. Оказалось, придумать осмысленный текст сама по себе задачка не из лёгких.';

        // A human-readable full description (for backoffice extensions list page):
        $this->_full_description = 'Это более подробное описание расширения, символов на 150-300. Например, вот такое длинное, как эта строка, которую нужно придумывать.<br><br>Это наш первый модуль - Пакеты поддержки. Бумажные или полиэтиленовые, отдельный вопрос - его ещё не прорабатывали на проектировании. Надо поднять на ближайшем созвоне.';

        // A human-readable description (for backoffice extension settings page):
        $this->_settings_description = 'Если пользователь вдруг решает поменять сколько он(а) месячно жертвует, например увеличивает размер месячной поддержки с 999 рублей до 1050 рублей (попадая, таким образом из Базовых доноров в Серебряные), то переключение между Пакетами происходит автоматически.';

        // A human-readable description of how to enable the main feature (for backoffice extension settings page):
        $this->_connection_description = '<p><strong>Подключение функции «Ограничение доступа к контенту»</strong></p>
<p>Доступ можно ограничить ко всему посту или к частям текста с помощью шорткода</p>
<code>[leyka_limited_content support_plan="Программное название вознаграждения"]</code>
<br>Ваш текст<br>
<code>[/leyka_limited_content]</code>';

        $this->_user_docs_link = '//leyka.te-st.ru'; // Extension user manual page URL /** @todo Change it when possible. */
        $this->_has_wizard = false;
        $this->_has_color_options = true;
        
        $this->setup_shortcodes();

    }
    
    protected function _set_options_defaults() {

        $this->_options = apply_filters('leyka_'.$this->_id.'_extension_options', array(
            array('section' => array(
                'name' => $this->_id.'-main-options',
                'title' => __('Main options', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array(
                    $this->_id.'_title' => array(
                        'type' => 'text',
                        'title' => '1. Заголовок обращения', // __('', 'leyka'),
                        'required' => true,
                        'placeholder' => 'Подпишитесь, чтобы прочитать целиком', // __('E.g., ', 'leyka'),
                        'width' => 0.5,
                    ),
                    $this->_id.'_main_text' => array(
                        'type' => 'textarea',
                        'title' => '2. Текстовое обращение', // __('', 'leyka'),
                        'required' => false,
                    ),
                    $this->_id.'_subscription_text' => array(
                        'type' => 'textarea',
                        'title' => '3. Текст о подписке', // __('', 'leyka'),
                        'placeholder' => 'Подписка продлевается автоматически. Вы можете отписаться в любой момент в личном кабинете',
                        'required' => false,
                    ),
                    $this->_id.'_activation_button_label' => array(
                        'type' => 'text',
                        'title' => '4. Надпись на кнопке активации', // __('', 'leyka'),
                        'required' => true,
                        'placeholder' => 'Подписаться', // __('E.g., ', 'leyka'),
                        'default' => 'Подписаться', // __('E.g., ', 'leyka'),
                        'width' => 0.5,
                    ),
                    $this->_id.'_account_link_label' => array(
                        'type' => 'text',
                        'title' => '5. Надпись для ссылки перехода в ЛК', // __('', 'leyka'),
                        'required' => true,
                        'placeholder' => 'У меня уже есть подписка', // __('E.g., ', 'leyka'),
                        'default' => 'У меня уже есть подписка', // __('', 'leyka'),
                        'width' => 0.5,
                    ),
                    $this->_id.'_closed_content_icon' => array(
                        'type' => 'file',
//                        'upload_format' => 'pics',
//                        'show_preview' => false,
                        'title' => '',
//                        'upload_title' => 'Выберите картинку',
                        'upload_label' => __('Load closed content icon', 'leyka'),
                        'description' => __('A *.png or *.svg file. The size is no more than 2 Mb', 'leyka'),
//                        'comment' => 'Тестовый коммент к полю загрузки картинки.',
//                        'required' => false,
                        'default' => '', /** @todo Add the default icon URL */
//                        'field_classes' => '', /** @todo Add the default icon URL */
                    ),
                    $this->_id.'_campaign' => array(
                        'type' => 'campaign_select',
                        'title' => 'Кампания для рекуррентных подписок', // __('', 'leyka'),
//                        'placeholder' => __('', 'leyka'),
                        'required' => true,
                    ),
                )
            )),
            array('section' => array(
                'name' => $this->_id.'-packages',
                'title' => __('Packages options', 'leyka'),
                'is_default_collapsed' => false,
                'options' => array(
                    'custom_support_packages_settings' => array(
                        'type' => 'custom_support_packages_settings', // Special option type
                    ),
                )
            )),
            array('section' => array(
                'name' => $this->_id.'-for-devs',
                'title' => __('For developers', 'leyka'),
                'is_default_collapsed' => true,
                'options' => array(
                    $this->_id.'_css' => array(
                        'type' => 'textarea',
                        'is_code_editor' => 'css',
                        'title' => __('Styles settings', 'leyka'),
                        'default' => '/* .some-selector-1 { color: black; } */ '.__('/* The main font color */', 'leyka')
                            .'/* .some-selector-2 { color: orange; } */ '.__('/* The secondary font color */', 'leyka'),
                    ),
                )
            )),
        ));

    }

    protected function _is_package_active($package, $recurring_subscriptions) {
        $total_subscriptions_amount = 0;
        foreach($recurring_subscriptions as $init_donation) {
            if($init_donation->cancel_recurring_requested) {
                continue;
            }
            
            $total_subscriptions_amount += $init_donation->amount;
        }
        
        return $total_subscriptions_amount >= $package->min_amount;
    }
    
    public function is_package_active($package, $user) {
        $active_package = $this->get_user_active_package($user);
        return $active_package->id === $package->id;
    }

    public function is_package_activated($package, $user) {
        $donor = new Leyka_Donor($user);
        $recurring_subscriptions = $donor->get_init_recurring_donations(true);
        return $this->_is_package_active($package, $recurring_subscriptions);
    }
    
    public function has_packages() {
        return count($this->get_packages()) > 0;
    }
    
    public function get_packages() {
        if($this->_packages === null) {
            $this->_packages = array(
                new Leyka_Support_Packages_Package(null, array('id' => 'basic', 'min_amount' => 100)),
                new Leyka_Support_Packages_Package(null, array('id' => 'advanced', 'min_amount' => 300)),
                new Leyka_Support_Packages_Package(null, array('id' => 'pro', 'min_amount' => 500)),
                new Leyka_Support_Packages_Package(null, array('id' => 'unlim', 'min_amount' => 5000)),
            );
        }
        
        return $this->_packages;
    }
    
    public function reset_packages() {
        $this->_packages = null;
    }
    
    public function get_user_activated_packages($user) {
        $donor = new Leyka_Donor($user);
        $recurring_subscriptions = $donor->get_init_recurring_donations(true);
        
        $active_packages = array();
        foreach($this->get_packages() as $package) {
            if($this->_is_package_active($package, $recurring_subscriptions)) {
                $active_packages[] = $package;
            }
        }
        
        return $active_packages;
    }

    public function get_package($package_id) {
        foreach($this->get_packages() as $package) {
            if($package->id === $package_id) {
                return $package;
            }
        }
        
        return null;
    }
    
    public function get_user_active_package($user) {
        $donor = new Leyka_Donor($user);
        $recurring_subscriptions = $donor->get_init_recurring_donations(true);
        
        $max_active_package = null;
        foreach($this->get_packages() as $package) {
            if($this->_is_package_active($package, $recurring_subscriptions)) {
                $max_active_package = $package;
            }
        }
        
        return $max_active_package;
    }
    
    public function get_activate_feature_form($feature, $user) {
        $leyka_ext_sp_template_tags = new Leyka_Support_Packages_Template_Tags();
        ob_start();
        $leyka_ext_sp_template_tags->show_activate_feature_form($feature, $user, $this);
        return ob_get_clean();
    }
    
    public function is_feature_open($feature, $user) {
        if($feature->support_plan) {
            $package = $this->get_package($feature->support_plan);
            return $package && $this->is_package_activated($package, $user);
        }
        
        return false;
    }
    
    public function setup_shortcodes() {
        foreach(Leyka_Support_Packages_Extension::$FEATURES as $feature_name => $feature_config) {
            if(!empty($feature_config['is_shortcode']) && $feature_config['is_shortcode']) {
                add_shortcode($feature_name, array($this, 'handle_shortcode'));
            }
        }
    }
    
    public function handle_shortcode($atts, $content = null, $tag=null) {
        $user = wp_get_current_user();
        
        foreach(Leyka_Support_Packages_Extension::$FEATURES as $feature_name => $feature_config) {
            if($feature_name === $tag) {
                
                if(!empty($feature_config['shortcode_atts'])) {
                    $feature_config['shortcode_atts'] = shortcode_atts( $feature_config['shortcode_atts'], $atts );
                }
                
                $feature = new $feature_config['class']($feature_name, $feature_config);
                if($this->is_feature_open($feature, $user)) {
                    return $feature->do_if_open(array('content' => $content));
                }
                else {
                    return $feature->do_if_closed(array('content' => $content)) . $this->get_activate_feature_form($feature, $user);
                }
            }
        }
    }
    
    public function get_persistent_campaign() {
        $recurring_subscriptions = get_posts(array(
            'post_type' => Leyka_Campaign_Management::$post_type,
            'post_status' => 'publish',
            'meta_query' => array(
                array('key' => 'is_finished', 'value' => 1, 'compare' => '!=', 'type' => 'NUMERIC',),
                array('key' => 'campaign_type', 'value' => 'persistent'),                
            ),
            'nopaging' => true,
        ));
        
        return !empty($recurring_subscriptions) ? $recurring_subscriptions[0] : null;
    }
}

class Leyka_Support_Packages_Feature {
    protected $_config = array();
    
    public function __construct($feature_name, $config=array()) {
        $this->_config = $config;
    }
    
    public function __get($field) {
    }
    
    public function __set($field, $value) {
    }
    
    public function do_if_open($params) {
    }
    
    public function do_if_closed($params) {
    }
}

class Leyka_Support_Packages_Shrotcode_Feature extends Leyka_Support_Packages_Feature {
    public function __construct($feature_name, $config=array()) {
        parent::__construct($feature_name, $config);
    }
}

class Leyka_Support_Packages_Limit_Content_Feature extends Leyka_Support_Packages_Shrotcode_Feature {
    public function __construct($feature_name, $config=array()) {
        parent::__construct($feature_name, $config);
    }
    
    public function __get($field) {
        switch($field) {
            case 'support_plan':
                return !empty($this->_config['shortcode_atts']['support_plan']) ? $this->_config['shortcode_atts']['support_plan'] : '';
                
            case 'activate_title':
                return esc_html__('Subscribe to read the whole', 'leyka');
                
            case 'activate_subtitle':
                return esc_html__('To break stereotypes, to unite guys who are passionate about technology, to inspire in search of their calling - these are the goals set by the participants of the European Programming Week.', 'leyka');
                
            default:
                return '';
        }
    }
    
    public function do_if_open($params) {
        return !empty($params['content']) ? do_shortcode($params['content']) : "";
    }
    
    public function do_if_closed($params) {
        return "";
    }
}

class Leyka_Support_Packages_Package {
    protected  $_package_data;
    
    public function __construct($package_id=null, $package_config=null) {
        if(is_array($package_config)) {
            $this->_package_data = $package_config;
        }
        elseif($package_id) {
            $this->_package_data = array('id' => $package_id);
        }
    }
    
    public function __get($field) {
        switch($field) {
            case 'id':
            case 'ID':
                return $this->_package_data['id'];
            case 'icon_url':
                return LEYKA_PLUGIN_BASE_URL . 'extensions/' . Leyka_Support_Packages_Extension::get_instance()->id_dash . '/img/sup-pack-crown-queen-20x20.svg';
            case 'icon_path':
                return LEYKA_PLUGIN_DIR . 'extensions/' . Leyka_Support_Packages_Extension::get_instance()->id_dash . '/img/sup-pack-crown-queen-20x20.svg';
            case 'title':
                return 'Начальный уровень';
            case 'price':
            case 'min_amount':
                return intval($this->_package_data['min_amount']);
            case 'price_currency':
                return '₽';
            default:
                return apply_filters('leyka_ext_get_unknown_support_package_field', null, $field, $this);
        }
    }
    
    public function __set($field, $value) {
    }
}

class Leyka_Support_Packages_Template_Tags {
    public function __construct() {
    }

    protected function _show_card_data_3rows($package, $params=array()) {
        $is_active = !empty($params['is_active']) && boolval($params['is_active']);
        
        if(empty($params['classes'])) {
            $params['classes'] = array();
        }
        
        if($is_active) {
            $params['classes'][] = 'active';
        }
        
        $extra_classes_str = !empty($params['classes']) ? implode(" ", $params['classes']) : '';        
        ?>
        
        <div class="leyka-ext-sp-card leyka-ext-sp-manage-card <?php echo $extra_classes_str;?>">
            <div class="leyka-ext-sp-card-row1">
                <div class="leyka-ext-sp-icon">
                	<?php if(preg_match("/\.svg$/", $package->icon_url)) {?>
                		<?php if(is_file($package->icon_path)) readfile($package->icon_path);?>
                	<?php } else {?>
                		<img src="<?php echo $package->icon_url;?>" />
            		<?php }?>
            	</div>
                <div class="leyka-ext-sp-title"><?php echo $package->title;?></div>
            </div>
            <div class="leyka-ext-sp-card-row2">
                <div class="leyka-ext-sp-price"><?php echo $package->price;?></div>
                <div class="leyka-ext-sp-currency"><?php echo $package->price_currency;?></div>
            </div>
            <div class="leyka-ext-sp-card-row3">
                <div class="leyka-ext-sp-period"><?php esc_html_e('Per month', 'leyka')?></div>
                <div class="leyka-ext-sp-status">
                	<?php if($is_active) {?>
                	<span><?php esc_html_e('Current status', 'leyka')?></span>
                	<?php } else {?>
            		<a href="#"><?php esc_html_e('Choose', 'leyka')?></a>
                	<?php }?>
                </div>
            </div>
        </div>
        <?php 
    }
    
    public function show_manage_card($package, $params=array()) {
        if(empty($params['classes'])) {
            $params['classes'] = array();
        }
        $params['classes'][] = 'leyka-ext-sp-manage-card';
        $this->_show_card_data_3rows($package, $params);
    }

    public function show_banner_card($package, $params=array()) {
        if(empty($params['classes'])) {
            $params['classes'] = array();
        }
        $params['classes'][] = 'leyka-ext-sp-banner-card';
        $this->_show_card_data_3rows($package, $params);
    }
    
    public function show_activate_feature_form($feature, $user, $leyka_ext_sp) {
        $max_width = count($leyka_ext_sp->get_packages()) * 186 - 16;
        $campaign_post = $leyka_ext_sp->get_persistent_campaign();
        $campaign_post_permalink = $campaign_post ? get_post_permalink($campaign_post) : '';
        ?>
        <div class="leyka-ext-sp-activate-feature-overlay">
        	<div class="leyka-ext-sp-activate-feature-overlay-gradient">
        	</div>
        	<div class="leyka-ext-sp-activate-feature-overlay-bg-wrapper">
            	<div class="leyka-ext-sp-activate-feature-overlay-bg">
                    <div class="leyka-ext-sp-activate-feature" style="max-width: <?php echo $max_width;?>px;">
                    	<h3><?php echo $feature->activate_title;?></h3>
                    	<div class="leyka-ext-sp-feature-subtitle"><?php echo $feature->activate_subtitle;?></div>
            			<div class="leyka-ext-support-packages">
            			<?php foreach($leyka_ext_sp->get_packages() as $package) {?>
            				<?php $this->show_manage_card($package, array('is_active' => $leyka_ext_sp->is_package_active($package, $user)))?>
            			<?php }?>
            			</div>
            			
            			<div class="leyka-ext-sp-terms-action">
                			<div class="leyka-ext-sp-subsription-terms">
                				<?php esc_html_e('Subscription renews automatically. You can unsubscribe at any time in', 'leyka');?> <a href="<?php echo site_url('/donor-account/');?>"><?php esc_html_e('your account', 'leyka');?></a>
                			</div>
                			<a href="<?php echo $campaign_post_permalink;?>" class="leyka-ext-sp-subscribe-action"><?php esc_html_e('Subscribe', 'leyka');?></a>
            			</div>
                	</div>
                    <div class="leyka-ext-sp-already-subsribed">
                    	<a href="<?php echo site_url('/donor-account/');?>" class="leyka-ext-sp-already-subscribed-link">
                    		<span class="leyka-ext-sp-already-subscribed-icon"><?php readfile(LEYKA_PLUGIN_DIR . 'extensions/' . Leyka_Support_Packages_Extension::get_instance()->id_dash . '/img/person.svg');?></span>
                    		<span class="leyka-ext-sp-already-subscribed-caption"><?php esc_html_e('I am already subscribed', 'leyka');?></span>
                		</a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

function leyka_add_extension_support_packages() { // Use named function to leave a possibility to remove/replace it on the hook
    leyka()->add_extension(Leyka_Support_Packages_Extension::get_instance());
}
add_action('leyka_init_actions', 'leyka_add_extension_support_packages');