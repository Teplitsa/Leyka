<?php if( !defined('WPINC') ) die; 
/**  Options config  **/
function leyka_engb_options($prefix) {

    return array(

        array('section' => array(
            'name'                  => $prefix.'-content',
            'title'                 => __('Content', 'leyka_engb'),
            'is_default_collapsed'  => false,
            'options'               => array(

                $prefix.'_title' => array(
                    'type'          => 'textarea', // char limit 
                    'title'         => __('Heading', 'leyka_engb'),
                    'required'      => true,
                    'description'   => __('Heading text (up to 100 characters) or shortcode', 'leyka_engb'), 
                    //'width' => 0.5,
                ),
                $prefix.'_text' => array(
                    'type'          => 'textarea',
                    'title'         => __('Main text', 'leyka_engb'),
                    'required'      => true,
                    'rows'          => 6,
                ),
                $prefix.'_selection' => array(
                    'type'          => 'text',
                    'title'         => __('Selected phrase', 'leyka_engb'),
                    'description'   => __('Phrase to be emphasized at the end of text', 'leyka_engb'),
                    'required'      => false,
                ),
                $prefix.'_button_label' => array(
                    'type'          => 'text',
                    'title'         => __('Button label', 'leyka_engb'),
                    'required'      => true,
                ),
                $prefix.'_button_link' => array(
                    'type'          => 'text',
                    'title'         => __('Button link', 'leyka_engb'),
                    'required'      => true,
                ),
            )
        )),

        array('section' => array(
            'name' => $prefix.'-placement',
            'title' => __('Placement', 'leyka_engb'),
            'is_default_collapsed' => true,
            'options' => array(

                $prefix . '_screen_position' => array(
                    'type' => 'radio',
                    'title' => __('Position on desktop screen', 'leyka_engb'),
                    'required' => true,
                    'default' => 'bottom',
                    'list_entries' => array(
                        'bottom' => array(
                            'title' => __('On bottom', 'leyka_engb'),
                            'comment' => '',
                        ),
                        'top' => array(
                            'title' => __('On top', 'leyka_engb'),
                            'comment' => '',
                        ),
                    )
                ),
                $prefix . '_show_on_pages' => array(
                    'type' => 'radio',
                    'title' => __('What pages to appear', 'leyka_engb'),
                    'required' => true,
                    'default' => 'all',
                    'list_entries' => array(
                        'all' => array(
                            'title' => __('All pages', 'leyka_engb'),
                        ),
                        'singles' => array(
                            'title' => __('Single pages', 'leyka_engb'),
                        ),
                        'onlyhome' => array(
                            'title' => __('Only homepage', 'leyka_engb'),
                        ),
                    )
                ),
                $prefix . '_show_on_home' => array(
                    'type' => 'radio',
                    'title' => __('Display on homepage', 'leyka_engb'),
                    'required' => true,
                    'default' => 'show',
                    'list_entries' => array(
                        'show' => array(
                            'title' => __('Yes', 'leyka_engb'),
                        ),
                        'hide' => array(
                            'title' => __('No', 'leyka_engb'),
                        ),
                    )
                ),
                $prefix.'_exclude_rules' => array(
                    'type'          => 'textarea',
                    'title'         => __('Hide banner on particular pages or content types', 'leyka_engb'),
                    'required'      => false,
                    'comment'       => __('Rules in this field hide banner on particular pages', 'leyka_engb'),
                    'description'   => __('Enter post IDs in format id:123 or post type in format pt:my_post, term IDs in format tid:123 or taxonomy - tax:my_tax. One rule per row.', 'leyka_engb')
                ),
                $prefix . '_hide_on_donation' => array(
                    'type' => 'radio',
                    'title' => __('Hide after donation', 'leyka_engb'),
                    'required' => true,
                    'default' => 'none',
                    'list_entries' => array(
                        'none' => array(
                            'title' => __('Don\'t hide', 'leyka_engb'),
                        ),
                        'day' => array(
                            'title' => __('Hide for 1 day', 'leyka_engb'),
                        ),
                        'week' => array(
                            'title' => __('Hide for 1 week', 'leyka_engb'),
                        ),
                        'forever' => array(
                            'title' => __('Hide forever', 'leyka_engb'),
                        ),
                    )
                ),
                $prefix . '_hide_from_roles' => array(
                    'type' => 'custom_engb_multiselect', 
                    'title' => __('Hide for user with roles', 'leyka_engb'),
                    'required' => false,
                    'list_entries' => leyka_engb_get_hide_on_roles_options(),
                    'update_callback' => 'leyka_engb_save_hide_on_roles_options',
                ),

            )
        )),
        
        array('section' => array(
            'name' => $prefix.'-interactions',
            'title' => __('Interactions', 'leyka_engb'),
            'is_default_collapsed' => true,
            'options' => array(

                $prefix . '_delay_type' => array(
                    'type' => 'radio',
                    'title' => __('Delay type', 'leyka_engb'),
                    'required' => true,
                    'default' => 'time',
                    'list_entries' => array(
                        'time' => array(
                            'title' => __('By time', 'leyka_engb'),
                        ),
                        'scroll' => array(
                            'title' => __('By scroll', 'leyka_engb'),
                        ),
                    )
                ),
                $prefix.'_time_amount' => array(
                    'type'          => 'text',
                    'title'         => __('Delay - sec.', 'leyka_engb'),
                    'required'      => false,
                    'default'       => '30',
                ),
                $prefix.'_scroll_amount' => array(
                    'type'          => 'number',
                    'title'         => __('Scrolled - percentage of page height', 'leyka_engb'),
                    'required'      => false,
                    'default'       => 50,
                    'min'           => 0,
                    'max'           => 100,
                ),
                $prefix . '_remember_close' => array(
                    'type' => 'radio',
                    'title' => __('Remember close', 'leyka_engb'),
                    'required' => true,
                    'default' => 'none',
                    'list_entries' => array(
                        'none' => array(
                            'title' => __('Don\'t remember', 'leyka_engb'),
                        ),
                        'session' => array(
                            'title' => __('Remember during session', 'leyka_engb'),
                        ),
                        'day' => array(
                            'title' => __('Remember for 1 day', 'leyka_engb'),
                        ),
                        'week' => array(
                            'title' => __('Remember for 1 week', 'leyka_engb'),
                        ),
                        'forever' => array(
                            'title' => __('Remember forever', 'leyka_engb'),
                        ),
                    )
                ),
            ))
        )
    );
}


/** Helpers for custom multiselect field **/
function leyka_engb_get_hide_on_roles_options() {

    $defaults = array(
        'logged_in' => __('Logged in user', 'leyka_engb'), 
    );

    $roles = wp_roles();

    if(empty($roles->roles)) {
        return $defaults;
    }

    $roles_options = array();
    
    foreach ($roles->roles as $key => $data) {
        $roles_options[$key] = $data['name'];
    }
    
    return array_merge($defaults, $roles_options);
}


function leyka_engb_save_hide_on_roles_options() {

    $roles = array();

    if( isset($_POST['leyka_engagement_banner_hide_from_roles']) ) {
        $roles = $_POST['leyka_engagement_banner_hide_from_roles'];
    }
    

    $valid = array();

    if(!empty($roles)) {
        foreach ($roles as $role) {
            if(wp_roles()->is_role( $role ) || $role == 'logged_in' ) {
                $valid[] = $role;
            }
        }
    }

    update_option('leyka_engagement_banner_hide_from_roles', $valid);
}
