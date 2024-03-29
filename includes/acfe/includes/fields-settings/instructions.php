<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('acfe_instructions')):

class acfe_instructions
{
    public function __construct()
    {
        add_action('acfe/pre_render_field_group', array($this, 'pre_render_field_group'), 10, 3);
        add_filter('acf/field_wrapper_attributes', array($this, 'field_wrapper_attributes'), 10, 2);
    }
    
    public function pre_render_field_group($field_group, $fields, $post_id)
    {
        acf_disable_filter('acfe/instruction_tooltip');
        acf_disable_filter('acfe/instruction_above_field');
        
        if (acf_maybe_get($field_group, 'instruction_placement') === 'tooltip') {
            acf_enable_filter('acfe/instruction_tooltip');
        } elseif (acf_maybe_get($field_group, 'instruction_placement') === 'above_field') {
            acf_enable_filter('acfe/instruction_above_field');
        }
    }
    
    public function field_wrapper_attributes($wrapper, $field)
    {
        if (!acf_maybe_get($field, 'label')) {
            $wrapper['class'] .= ' acfe-no-label';
        }
        
        if (acf_maybe_get($field, 'instructions')) {
            if (acf_is_filter_enabled('acfe/instruction_tooltip')) {
                $wrapper['data-instruction-tooltip'] = acf_esc_html($field['instructions']);
            } elseif (acf_is_filter_enabled('acfe/instruction_above_field')) {
                $wrapper['data-instruction-above-field'] = acf_esc_html($field['instructions']);
            }
        }
        
        return $wrapper;
    }
}

new acfe_instructions();

endif;
