<?php if( !defined('WPINC') ) die; 
/**  Options config  **/
function leyka_engb_options($prefix) {

    return array(

        array('section' => array(
            'name'                  => $prefix.'-content',
            'title'                 => __('Content', 'leyka'),
            'is_default_collapsed'  => false,
            'options'               => array(

                $prefix.'_title' => array(
                    'type'          => 'textarea', // char limit 
                    'title'         => __('Heading', 'leyka'),
                    'required'      => true,
                    'description'   => __('Heading text (up to 100 characters) or shortcode', 'leyka'),
                    //'width' => 0.5,
                ),
                $prefix.'_text' => array(
                    'type'          => 'textarea',
                    'title'         => __('Main text', 'leyka'),
                    'required'      => true,
                    'rows'          => 6,
                ),
                $prefix.'_selection' => array(
                    'type'          => 'text',
                    'title'         => __('Selected phrase', 'leyka'),
                    'description'   => __('Phrase to be emphasized at the end of text', 'leyka'),
                    'required'      => false,
                ),
                $prefix.'_button_label' => array(
                    'type'          => 'text',
                    'title'         => __('Button label', 'leyka'),
                    'required'      => true,
                ),
                $prefix.'_button_link' => array(
                    'type'          => 'text',
                    'title'         => __('Button link', 'leyka'),
                    'required'      => true,
                ),
            )
        )),

        array('section' => array(
            'name' => $prefix.'-placement',
            'title' => __('Placement', 'leyka'),
            'is_default_collapsed' => true,
            'options' => array(

                $prefix . '_screen_position' => array(
                    'type' => 'radio',
                    'title' => __('Position on desktop screen', 'leyka'),
                    'required' => true,
                    'default' => 'bottom',
                    'list_entries' => array(
                        'bottom' => array(
                            'title' => __('On bottom', 'leyka'),
                            'comment' => '',
                        ),
                        'top' => array(
                            'title' => __('On top', 'leyka'),
                            'comment' => '',
                        ),
                    )
                ),
                $prefix . '_show_on_pages' => array(
                    'type' => 'radio',
                    'title' => __('What pages to appear', 'leyka'),
                    'required' => true,
                    'default' => 'all',
                    'list_entries' => array(
                        'all' => array(
                            'title' => __('All pages', 'leyka'),
                        ),
                        'singles' => array(
                            'title' => __('Single pages', 'leyka'),
                        ),
                        'onlyhome' => array(
                            'title' => __('Only homepage', 'leyka'),
                        ),
                    )
                ),
                $prefix . '_show_on_home' => array(
                    'type' => 'radio',
                    'title' => __('Display on homepage', 'leyka'),
                    'required' => true,
                    'default' => 'show',
                    'list_entries' => array(
                        'show' => array(
                            'title' => __('Yes', 'leyka'),
                        ),
                        'hide' => array(
                            'title' => __('No', 'leyka'),
                        ),
                    )
                ),
                $prefix.'_exclude_rules' => array(
                    'type'          => 'textarea',
                    'title'         => __('Hide banner on particular pages or content types', 'leyka'),
                    'required'      => false,
                    'comment'       => __('Rules in this field hide banner on particular pages', 'leyka'),
                    'description'   => __('Enter post IDs in format id:123 or post type in format pt:my_post, term IDs in format tid:123 or taxonomy - tax:my_tax. One rule per row.', 'leyka')
                ),
                $prefix . '_hide_on_donation' => array(
                    'type' => 'radio',
                    'title' => __('Hide after donation', 'leyka'),
                    'required' => true,
                    'default' => 'none',
                    'list_entries' => array(
                        'none' => array(
                            'title' => __('Don\'t hide', 'leyka'),
                        ),
                        'day' => array(
                            'title' => __('Hide for 1 day', 'leyka'),
                        ),
                        'week' => array(
                            'title' => __('Hide for 1 week', 'leyka'),
                        ),
                        'forever' => array(
                            'title' => __('Hide forever', 'leyka'),
                        ),
                    )
                ),
                $prefix . '_hide_from_roles' => array(
                    'type' => 'custom_engb_multiselect', 
                    'title' => __('Hide for user with roles', 'leyka'),
                    'required' => false,
                    'list_entries' => leyka_engb_get_hide_on_roles_options(),
                    'update_callback' => 'leyka_engb_save_hide_on_roles_options',
                ),

            )
        )),
        
        array('section' => array(
            'name' => $prefix.'-interactions',
            'title' => __('Interactions', 'leyka'),
            'is_default_collapsed' => true,
            'options' => array(

                $prefix . '_delay_type' => array(
                    'type' => 'radio',
                    'title' => __('Delay type', 'leyka'),
                    'required' => true,
                    'default' => 'time',
                    'list_entries' => array(
                        'time' => array(
                            'title' => __('By time', 'leyka'),
                        ),
                        'scroll' => array(
                            'title' => __('By scroll', 'leyka'),
                        ),
                    )
                ),
                $prefix.'_time_amount' => array(
                    'type'          => 'text',
                    'title'         => __('Delay - sec.', 'leyka'),
                    'required'      => false,
                    'default'       => '30',
                ),
                $prefix.'_scroll_amount' => array(
                    'type'          => 'number',
                    'title'         => __('Scrolled - percentage of page height', 'leyka'),
                    'required'      => false,
                    'default'       => 50,
                    'min'           => 0,
                    'max'           => 100,
                ),
                $prefix . '_remember_close' => array(
                    'type' => 'radio',
                    'title' => __('Remember close', 'leyka'),
                    'required' => true,
                    'default' => 'none',
                    'list_entries' => array(
                        'none' => array(
                            'title' => __('Don\'t remember', 'leyka'),
                        ),
                        'session' => array(
                            'title' => __('Remember during session', 'leyka'),
                        ),
                        'day' => array(
                            'title' => __('Remember for 1 day', 'leyka'),
                        ),
                        'week' => array(
                            'title' => __('Remember for 1 week', 'leyka'),
                        ),
                        'forever' => array(
                            'title' => __('Remember forever', 'leyka'),
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
        'logged_in' => __('Logged in user', 'leyka'),
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
