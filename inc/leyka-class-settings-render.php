<?php if( !defined('WPINC') ) die;
/**
 * Leyka Settings Render class.
 **/

abstract class Leyka_Settings_Render extends Leyka_Singleton {

    protected static $_instance = null;

    protected $_id;

    protected function __construct() {

        $this->_loadCssJs();
        $this->_setAttributes();

    }

    /** @var Leyka_Settings_Controller */
    protected $_controller;

    abstract protected function _setAttributes();

    /**
     * @param Leyka_Settings_Controller $controller
     * @return Leyka_Settings_Render
     */
    public function setController(Leyka_Settings_Controller $controller) {

        $this->_controller = $controller;

        return $this;

    }

    abstract public function renderPage();

    abstract public function renderNavigationArea();
    abstract public function renderMainArea();

    abstract public function renderStep();

    abstract public function renderCommonErrorsArea();

    abstract public function renderTextBlock(Leyka_Text_Block $block);
    abstract public function renderOptionBlock(Leyka_Option_Block $block);
    abstract public function renderContainerBlock(Leyka_Container_Block $block);

    abstract public function renderHiddenFields();
    abstract public function renderSubmitArea();

    protected function _loadCssJs() {

        wp_enqueue_script(
            'leyka-settings-render',
            LEYKA_PLUGIN_BASE_URL.'assets/js/admin.js',
            array('jquery',),
            LEYKA_VERSION,
            true
        );
//        add_action('wp_enqueue_scripts', array($this, 'localize_scripts')); // wp_footer

        wp_enqueue_style(
            'leyka-settings',
            LEYKA_PLUGIN_BASE_URL.'assets/css/admin.css',
            array(),
            LEYKA_VERSION
        );

        do_action('leyka_settings_enqueue_scripts');

    }

    public function __get($name) {
        switch($name) {
            case 'id': return $this->_id;
            case 'full_id': return $this->_id.'-'.$this->_controller->id;
            default:
                return null;
        }
    }

}

class Leyka_Wizard_Render extends Leyka_Settings_Render {

    protected static $_instance = null;

    protected function _setAttributes() {
        $this->_id = 'wizard';
    }

    public function renderPage() {?>

        <div class="leyka-wizard">
            <div class="nav-area">
                <?php $this->renderNavigationArea();?>
            </div>
            <div class="main-area">
                <?php $this->renderMainArea();?>
            </div>
        </div>

    <?php }

    public function renderCommonErrorsArea() {
        foreach($this->_controller->getCommonErrors() as $error) { /** @var WP_Error $error */ ?>
            <span><?php echo $error->get_error_message();?></span>
        <?php }
    }

    public function renderMainArea() {

        $current_step = $this->_controller->getCurrentStep();?>

        <div class="step-title">
            <h2 id="step-title-<?php echo $current_step->full_id?>"><?php echo $current_step->title;?></h2>
        </div>

        <div class="step-common-errors <?php echo $this->_controller->hasCommonErrors() ? 'has-errors' : '';?>">
            <?php $this->renderCommonErrorsArea();?>
        </div>

        <form id="leyka-settings-form-<?php echo $current_step->full_id;?>" class="leyka-settings-form leyka-wizard-step" method="post" action="<?php echo admin_url('admin.php?page=leyka_settings_new&screen='.$this->full_id);?>">
            <div class="step-content">
            <?php foreach($current_step->getBlocks() as $block) { /** @var $block Leyka_Settings_Block */

            /** @todo If-else here sucks. Make it a Factory Method */

                if(is_a($block, 'Leyka_Container_Block')) {?>

                    <div id="<?php echo $block->id;?>" class="settings-block container-block">

                    <?php $entry_width = $block->entry_width ? (100.0*$block->entry_width).'%' : false;

                    $sub_blocks_list = $block->getContent();

                    foreach($sub_blocks_list as $sub_block_index => $sub_block) { /** @var Leyka_Settings_Block $sub_block */?>

                        <div class="container-entry" <?php echo $entry_width ? 'style="flex-basis: '.($sub_block_index == count($sub_blocks_list) - 1 ? 'auto' : $entry_width).';"' : '';?>>

                            <?php if(is_a($sub_block, 'Leyka_Option_Block')) {

                                $option_info = leyka_options()->get_info_of($sub_block->getContent());?>

                                <div id="<?php echo $sub_block->id;?>" class="settings-block option-block <?php echo $sub_block->show_title ? '' : 'option-title-hidden';?> <?php echo $sub_block->show_description ? '' : 'option-description-hidden';?> <?php echo $this->_controller->hasComponentErrors($sub_block->id) ? 'has-errors' : '';?>">
                                    <?php do_action("leyka_render_{$option_info['type']}", $sub_block->getContent(), $option_info);?>
                                    <div class="field-errors">
                                    <?php foreach($this->_controller->getComponentErrors($sub_block->id) as $error) { /** @var $error WP_Error */ ?>
                                        <span><?php echo $error->get_error_message();?></span>
                                    <?php }?>
                                    </div>
                                </div>

                            <?php }?>

                        </div>

                    <?php }?>

                    </div>


                <?php } else if(is_a($block, 'Leyka_Text_Block')) {?>

                <div id="<?php echo $block->id;?>" class="settings-block text-block"><p><?php echo $block->getContent();?></p></div>

                <?php } else if(is_a($block, 'Leyka_Custom_Option_Block')) {

//                    echo '<p>'.$block->option_id.' custom option here</p>';

                } else if(is_a($block, 'Leyka_Option_Block')) {

                    $option_info = leyka_options()->get_info_of($block->getContent());?>

                <div id="<?php echo $block->id;?>" class="settings-block option-block <?php echo $block->show_title ? '' : 'option-title-hidden';?> <?php echo $block->show_description ? '' : 'option-description-hidden';?> <?php echo $this->_controller->hasComponentErrors($block->id) ? 'has-errors' : '';?>">
                    <?php do_action("leyka_render_{$option_info['type']}", $block->getContent(), $option_info);?>
                    <div class="field-errors">
                    <?php foreach($this->_controller->getComponentErrors($block->id) as $error) { /** @var $error WP_Error */?>
                        <span><?php echo $error->get_error_message();?></span>
                    <?php }?>
                    </div>
                </div>

                <?php }

            }?>
            </div>

            <?php $this->renderHiddenFields();?>

            <div class="step-submit">
            <?php $this->renderSubmitArea();?>
            </div>
        </form>
        
        <div style="display: none;">
        
        <h1>Приветствуем вас!</h1>
        <p>Вы установили плагин «Лейка», осталось его настроить. Мы проведём вас по всем шагам, поможем подсказками, а если нужна будет наша помощь, вы можете обратиться к нам через форму в правой части экрана</p>
        
        <h2>Получатель пожертвований</h2>
        <p>Вы установили плагин «Лейка», осталось его настроить. Мы проведём вас по всем шагам, поможем подсказками, а если нужна будет наша помощь, вы можете обратиться к нам через форму в правой части экрана</p>
        
        <h3>Такого заголовка пока нет</h3>
        <p>Вы установили плагин «Лейка», осталось его <a href="https://te-st.ru">настроить</a>. Мы проведём вас по всем шагам, поможем подсказками, а если нужна будет наша помощь, вы можете обратиться к нам через форму в правой части экрана</p>
        
        <div class="fields">
            
            <div class="field">
                <label>Выберите вашу страну:<img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-q.svg" class="field-q" /></label>
                <input type="text" name="some" placeholder="Введите текст"/>
                <p class="explain">Архитектура Лейки позволяет вам собирать деньги и в других странах. Узнайте, как подключить вашу страну <a href="https://te-st.ru">здесь</a>.</p>
            </div>

            <div class="field required">
                <label>Выберите вашу страну:</label>
                <select name="some">
                    <option>Россия</option>
                    <option>Китай</option>
                </select>
                <p class="explain">Архитектура Лейки позволяет вам собирать деньги и в других странах. Узнайте, как подключить вашу страну <a href="https://te-st.ru">здесь</a>.</p>
            </div>

            <div class="field field-options">
                <label><input type="radio" name="some1"/>НКО – юридическое лицо</label>
                <label><input type="radio" name="some1"/>Физическое лицо</label>
                <p class="explain">Архитектура Лейки позволяет вам собирать деньги и в других странах. Узнайте, как подключить вашу страну <a href="https://te-st.ru">здесь</a>.</p>
            </div>

            <div class="field field-options">
                <label><input type="checkbox" name="some2"/>НКО – юридическое лицо</label>
                <label><input type="checkbox" name="some2"/>Физическое лицо</label>
            </div>

            <div class="field">
                <label>Выберите вашу страну:</label>
                <textarea name="some" placeholder="Введите текст"></textarea>
                <p class="explain">Архитектура Лейки позволяет вам собирать деньги и в других странах. Узнайте, как подключить вашу страну <a href="https://te-st.ru">здесь</a>.</p>
            </div>

            <div class="field field-error">
                <label>Выберите вашу страну:</label>
                <input type="text" name="some" placeholder="Введите текст"/>
                <p class="message message-error">В поле должны стоять числовые значения</p>
                <p class="explain">Архитектура Лейки позволяет вам собирать деньги и в других странах. Узнайте, как подключить вашу страну <a href="https://te-st.ru">здесь</a>.</p>
            </div>

            <div class="field field-success">
                <label>Выберите вашу страну:</label>
                <input type="text" name="some" placeholder="Введите текст"/>
            </div>

            <div class="field field-warning">
                <label>Выберите вашу страну:</label>
                <input type="text" name="some" placeholder="Введите текст"/>
            </div>
            
        </div>
        
        <div class="step-submit">
            <input type="submit" class="button button-primary" />
            <div class="sec-action">
                <a class="link-sec" href="https://te-st.ru">Вернуться на предыдущий шаг</a>
            </div>
            <div class="sec-action">
                <input type="submit" class="link-sec" value="Вернуться на предыдущий шаг"/>
            </div>
        </div>
        
        </div>
        
    <?php }

    public function renderHiddenFields() {
    }

    public function renderSubmitArea() {

        $submits = $this->_controller->getSubmitData();?>

        <?php if($submits['next_url'] === true) {?>

        <input type="submit" class="step-next button button-primary" name="leyka_settings_submit_<?php echo $this->_controller->id;?>" value="<?php echo $submits['next_label'];?>">

        <?php } else if(is_string($submits['next_url'])) {?>

        <a href="<?php echo esc_url($submits['next_url']);?>" class="wizard-custom-link"><?php echo $submits['next_label'];?></a>

        <?php }

        if( !empty($submits['additional_label']) && !empty($submits['additional_url']) ) {?>
            <a href="<?php echo esc_url($submits['additional_url']);?>">
                <?php echo esc_html($submits['additional_label']);?>
            </a>
        <?php }?>

        <br>

        <?php if( !empty($submits['prev']) ) {?>
        <div class="sec-action">
            <input type="submit" class="step-prev link-sec" name="leyka_settings_prev_<?php echo $this->_controller->id;?>" value="<?php echo $submits['prev'];?>">
        </div>
        <?php }?>

    <?php }

    public function renderNavigationArea() {

        $navigation_data = $this->_controller->getNavigationData();

//        echo '<pre>'.print_r($navigation_data, 1).'</pre>';?>

        <div class="nav-chain">
            <div class="nav-line">

            <?php foreach($navigation_data as $section_index => $section) {?>

                <div class="nav-section <?php if( !empty($section['is_current']) ) {?>active<?php } elseif($section['is_completed']) {?>done<?php }?>">

                    <div class="nav-section-title">
                        <div class="nav-section-marker">
                        <?php if( !empty($section['is_completed']) ) {?>
                            <img src="<?php echo LEYKA_PLUGIN_BASE_URL?>img/icon-ok.svg">
                        <?php } else {
                            echo $section_index + 1;
                        }?>
                        </div>
                        <?php echo esc_html($section['title']);?>
                    </div>

                    <?php if(empty($section['is_completed']) && !empty($section['steps'])) {?>
                        <div class="nav-steps">

                            <?php foreach($section['steps'] as $step) {?>

                                <div class="nav-step <?php if( !empty($step['is_current']) ) {?>active<?php } else if( !empty($step['is_completed']) ) {?>done<?php }?>">
                                    <?php echo esc_html($step['title']);

                                    if( false && !empty($step['is_current']) ) {?>
                                    <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/icon-i.svg" class="step-i">
                                    <?php }?>
                                </div>

                            <?php }?>

                        </div>
                    <?php }?>

                </div>

            <?php }?>

            </div>

        </div>

        <div class="leyka-logo">
            <img src="<?php echo LEYKA_PLUGIN_BASE_URL;?>img/nav-logo.svg" />
        </div>

    <?php }

    public function renderStep() {
        // TODO: Implement renderStep() method.
    }

    public function renderContainerBlock(Leyka_Container_Block $block) {
        // TODO: Implement renderContainerBlock() method.
    }

    public function renderTextBlock(Leyka_Text_Block $block) {
        // TODO: Implement renderTextBlock() method.
    }

    public function renderOptionBlock(Leyka_Option_Block $block) {
        // TODO: Implement renderOptionBlock() method.
    }

}