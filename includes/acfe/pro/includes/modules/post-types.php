<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('acfe_pro_dynamic_post_types')):

class acfe_pro_dynamic_post_types
{
    public $post_type;
    
    /*
     * Construct
     */
    public function __construct()
    {
        $this->post_type = 'acfe-dpt';
        
        add_filter('acfe/post_type/register', array($this, 'register'), 15, 2);
        add_filter('acfe/post_type/save_args', array($this, 'save_args'), 15, 3);
        add_action('acfe/post_type/save', array($this, 'save'), 15, 3);
        add_action('acfe/post_type/import_fields', array($this, 'import_fields'), 15, 3);
        
        $this->add_local_field_group();
    }
    
    /*
     * Register
     */
    public function register($args, $name)
    {
    
        // Check Active
        if (!acf_maybe_get($args, 'active', true)) {
            return false;
        }
        
        return $args;
    }
    
    /*
     * Save Args
     */
    public function save_args($args, $name, $post_id)
    {
    
        // Active
        $active = get_field('acfe_dpt_active', $post_id);
        $active = $active === null ? true : $active;
    
        $args['active'] = $active;
        
        return $args;
    }
    
    /*
     * Save
     */
    public function save($name, $args, $post_id)
    {
    
        // Update post
        wp_update_post(array(
            'ID'            => $post_id,
            'post_status'   => $args['active'] ? 'publish' : 'acf-disabled',
        ));
    }
    
    /*
     * Import Fields
     */
    public function import_fields($name, $args, $post_id)
    {
        update_field('acfe_dpt_active', $args['active'], $post_id);
    }
    
    /*
     * Add Local Field Group
     */
    public function add_local_field_group()
    {
        acf_add_local_field_group(array(
            'key' => 'group_acfe_dynamic_post_type_side',
            'title' => 'Post Type: Side',
            'acfe_display_title' => 'Active',
            'fields' => array(
                array(
                    'key' => 'field_acfe_dpt_active',
                    'label' => '',
                    'name' => 'acfe_dpt_active',
                    'type' => 'true_false',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'message' => '',
                    'default_value' => 1,
                    'ui' => 1,
                    'ui_on_text' => '',
                    'ui_off_text' => '',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => $this->post_type,
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'side',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
        ));
    }
}

acf_new_instance('acfe_pro_dynamic_post_types');

endif;
