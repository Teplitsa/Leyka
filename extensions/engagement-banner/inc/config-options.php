<?php if( !defined('WPINC') ) die; 
/**  Options config  **/
function leyka_engb_options($prefix) {

    return [

        ['section' => [
            'name' => $prefix.'-content',
            'title' => __('Content', 'leyka'),
            'is_default_collapsed' => false,
            'options' => [
                $prefix.'_title' => [
                    'type' => 'textarea', // char limit
                    'title' => __('Heading', 'leyka'),
                    'required' => true,
                    'description' => __('Heading text (up to 100 characters) or shortcode', 'leyka'),
                    //'width' => 0.5,
                ],
                $prefix.'_text' => [
                    'type' => 'textarea',
                    'title' => __('Main text', 'leyka'),
                    'required' => true,
                    'rows' => 6,
                ],
                $prefix.'_selection' => [
                    'type' => 'text',
                    'title' => __('Selected phrase', 'leyka'),
                    'description' => __('Phrase to be emphasized at the end of text', 'leyka'),
                    'required' => false,
                ],
                $prefix.'_button_label' => [
                    'type' => 'text',
                    'title' => __('Button label', 'leyka'),
                    'required' => true,
                ],
                $prefix.'_button_link' => [
                    'type' => 'text',
                    'title' => __('Button link', 'leyka'),
                    'required' => true,
                ],
            ],
        ],],

        ['section' => [
            'name' => $prefix.'-placement',
            'title' => __('Placement', 'leyka'),
            'is_default_collapsed' => true,
            'options' => [
                $prefix . '_screen_position' => [
                    'type' => 'radio',
                    'title' => __('Position on desktop screen', 'leyka'),
                    'required' => true,
                    'default' => 'bottom',
                    'list_entries' => [
                        'bottom' => ['title' => __('On bottom', 'leyka'), 'comment' => '',],
                        'top' => ['title' => __('On top', 'leyka'), 'comment' => '',],
                    ],
                ],
                $prefix . '_show_on_pages' => [
                    'type' => 'radio',
                    'title' => __('What pages to appear', 'leyka'),
                    'required' => true,
                    'default' => 'all',
                    'list_entries' => [
                        'all' => ['title' => __('All pages', 'leyka'),],
                        'singles' => ['title' => __('Single pages', 'leyka'),],
                        'onlyhome' => ['title' => __('Only homepage', 'leyka'),],
                    ],
                ],
                $prefix . '_show_on_home' => [
                    'type' => 'radio',
                    'title' => __('Display on homepage', 'leyka'),
                    'required' => true,
                    'default' => 'show',
                    'list_entries' => [
                        'show' => ['title' => __('Yes', 'leyka'),],
                        'hide' => ['title' => __('No', 'leyka'),],
                    ],
                ],
                $prefix.'_exclude_rules' => [
                    'type'          => 'textarea',
                    'title'         => __('Hide banner on particular pages or content types', 'leyka'),
                    'required'      => false,
                    'comment'       => __('Rules in this field hide banner on particular pages', 'leyka'),
                    'description'   => __('Enter post IDs in format id:123 or post type in format pt:my_post, term IDs in format tid:123 or taxonomy - tax:my_tax. One rule per row.', 'leyka'),
                ],
                $prefix . '_hide_on_donation' => [
                    'type' => 'radio',
                    'title' => __('Hide after donation', 'leyka'),
                    'required' => true,
                    'default' => 'none',
                    'list_entries' => [
                        'none' => ['title' => __("Don't hide", 'leyka'),],
                        'day' => ['title' => __('Hide for 1 day', 'leyka'),],
                        'week' => ['title' => __('Hide for 1 week', 'leyka'),],
                        'forever' => ['title' => __('Hide forever', 'leyka'),],
                    ],
                ],
                $prefix . '_hide_from_roles' => [
                    'type' => 'custom_engb_multiselect', 
                    'title' => __('Hide for user with roles', 'leyka'),
                    'required' => false,
                    'list_entries' => leyka_engb_get_hide_on_roles_options(),
                    'update_callback' => 'leyka_engb_save_hide_on_roles_options',
                ],

            ],
        ],],
        
        ['section' => [
            'name' => $prefix.'-interactions',
            'title' => __('Interactions', 'leyka'),
            'is_default_collapsed' => true,
            'options' => [
                $prefix . '_delay_type' => [
                    'type' => 'radio',
                    'title' => __('Delay type', 'leyka'),
                    'required' => true,
                    'default' => 'time',
                    'list_entries' => [
                        'time' => ['title' => __('By time', 'leyka'),],
                        'scroll' => ['title' => __('By scroll', 'leyka'),],
                    ],
                ],
                $prefix.'_time_amount' => [
                    'type' => 'number',
                    'title' => __('Delay - sec.', 'leyka'),
                    'required' => false,
                    'default' => 30,
                    'min' => 1,
                    'max' => 60,
                    'step' => 1,
                ],
                $prefix.'_scroll_amount' => [
                    'type' => 'number',
                    'title' => __('Scrolled - percentage of page height', 'leyka'),
                    'required' => false,
                    'default' => 50,
                    'min' => 0,
                    'max' => 100,
                ],
                $prefix . '_remember_close' => [
                    'type' => 'radio',
                    'title' => __('Remember close', 'leyka'),
                    'required' => true,
                    'default' => 'none',
                    'list_entries' => [
                        'none' => ['title' => __("Don't remember", 'leyka'),],
                        'session' => ['title' => __('Remember during session', 'leyka'),],
                        'day' => ['title' => __('Remember for 1 day', 'leyka'),],
                        'week' => ['title' => __('Remember for 1 week', 'leyka'),],
                        'forever' => ['title' => __('Remember forever', 'leyka'),],
                    ],
                ],
            ],],
        ],
    ];
}


/** Helpers for custom multiselect field **/
function leyka_engb_get_hide_on_roles_options() {

    $defaults = ['logged_in' => __('Logged in user', 'leyka'),];

    $roles = wp_roles();

    if(empty($roles->roles)) {
        return $defaults;
    }

    $roles_options = [];
    
    foreach ($roles->roles as $key => $data) {
        $roles_options[$key] = $data['name'];
    }
    
    return array_merge($defaults, $roles_options);
}


function leyka_engb_save_hide_on_roles_options() {

    $roles = [];

    if( isset($_POST['leyka_engagement_banner_hide_from_roles']) ) {
        $roles = $_POST['leyka_engagement_banner_hide_from_roles'];
    }
    

    $valid = [];

    if(!empty($roles)) {
        foreach ($roles as $role) {
            if(wp_roles()->is_role( $role ) || $role == 'logged_in' ) {
                $valid[] = $role;
            }
        }
    }

    update_option('leyka_engagement_banner_hide_from_roles', $valid);
}
