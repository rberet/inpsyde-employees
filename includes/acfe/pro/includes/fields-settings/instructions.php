<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('acfe_pro_instructions')):

class acfe_pro_instructions
{
    public function __construct()
    {
        
        // Actions
        add_action('acf/field_group/admin_head', array($this, 'admin_head'));
        add_filter('acf/field_wrapper_attributes', array($this, 'field_wrapper_attributes'), 10, 2);
    }
 
    /*
     * Admin Head
     */
    public function admin_head()
    {
        global $field_group;
        
        add_filter('acf/prepare_field/name=instructions', array($this, 'prepare_settings_instructions'));
        
        if (acf_maybe_get($field_group, 'acfe_form')) {
            add_action('acf/render_field_settings', array($this, 'render_field_instructions_settings'), 11);
        }
    }
    
    /*
     * Render Field Instructions Settings
     */
    public function render_field_instructions_settings($field)
    {
        
        // Hide Field
        acf_render_field_setting($field, array(
            'label'         => __('Instructions Placement', 'acfe'),
            'instructions'  => '',
            'name'          => 'instruction_placement',
            'type'          => 'select',
            'placeholder'   => 'Default',
            'allow_null'    => true,
            'choices'       => array(
                'label'         => 'Below labels',
                'field'         => 'Below fields',
                'above_field'   => 'Above fields',
                'tooltip'       => 'Tooltip',
            ),
            'wrapper' => array(
                'data-after' => 'instructions',
            )
        ), true);
    }
    
    public function field_wrapper_attributes($wrapper, $field)
    {
        if (acf_maybe_get($field, 'instructions')) {
            if (acf_maybe_get($field, 'instruction_placement')) {
                $wrapper['data-instruction-placement'] = acf_maybe_get($field, 'instruction_placement');
            }
    
            if (strpos($field['instructions'], '---') !== false) {
                $wrapper['data-instruction-more'] = true;
            }
        }
        
        return $wrapper;
    }
    
    public function prepare_settings_instructions($field)
    {
        if ($field['wrapper']['class'] !== ' acf-field-setting-instructions') {
            return $field;
        }
        
        $field['instructions'] .= '. You may use &dash;&dash;&dash; to add a collapsed text';
        
        return $field;
    }
}

// initialize
new acfe_pro_instructions();

endif;
