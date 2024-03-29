<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('acfe_field_flexible_content_actions')):

class acfe_field_flexible_content_actions
{
    public function __construct()
    {
    
        // Hooks
        add_filter('acfe/flexible/defaults_field', array($this, 'defaults_field'), 6);
        add_filter('acfe/flexible/render_field_settings', array($this, 'render_field_settings'), 6);
        
        add_filter('acfe/flexible/validate_field', array($this, 'validate_actions'));
        add_filter('acfe/flexible/wrapper_attributes', array($this, 'wrapper_attributes'), 10, 2);
        add_filter('acfe/flexible/load_fields', array($this, 'load_fields'), 10, 2);
        add_filter('acfe/flexible/prepare_layout', array($this, 'prepare_layout'), 10, 5);
        add_filter('acfe/flexible/layouts/icons', array($this, 'layout_icons'), 11, 3);
        add_filter("acfe/flexible/secondary_actions", array($this, 'secondary_actions'), 10, 2);
    
        add_filter('acf/fields/flexible_content/layout_title', array($this, 'layout_title'), 5, 4);
        add_filter('acf/load_value/type=flexible_content', array($this, 'load_value'), 10, 3);
    }
    
    public function defaults_field($field)
    {
        $field['acfe_flexible_add_actions'] = array();
        
        return $field;
    }
    
    public function render_field_settings($field)
    {
        
        /*
         * old settings:
         *
         * acfe_flexible_title_edition
         * acfe_flexible_toggle
         * acfe_flexible_copy_paste
         * acfe_flexible_lock
         * acfe_flexible_close_button
         *
         * acfe_flexible_clone
         */
        
        $choices = array(
            'title'     => 'Inline Title Edit',
            'toggle'    => 'Toggle Layout',
            'copy'      => 'Copy/paste Layout',
            'lock'      => 'Lock Layouts',
            'close'     => 'Close Button',
        );
    
        if (acf_version_compare(acf_get_setting('version'), '<', '5.9')) {
            $choices['clone'] = 'Clone';
        }
    
        acf_render_field_setting($field, array(
            'label'         => __('Additional Actions'),
            'name'          => 'acfe_flexible_add_actions',
            'key'           => 'acfe_flexible_add_actions',
            'instructions'  => __('Additional actions'),
            'type'              => 'checkbox',
            'default_value'     => '',
            'layout'            => 'horizontal',
            'choices'           => $choices,
            'conditional_logic' => array(
                array(
                    array(
                        'field'     => 'acfe_flexible_advanced',
                        'operator'  => '==',
                        'value'     => '1',
                    ),
                )
            )
        ));
    }
    
    public function validate_actions($field)
    {
        $actions = acf_get_array($field['acfe_flexible_add_actions']);
        
        // acfe_flexible_title_edition
        if (acf_maybe_get($field, 'acfe_flexible_title_edition')) {
            if (!in_array('title', $actions)) {
                $actions[] = 'title';
            }
            acfe_unset($field, 'acfe_flexible_title_edition');
        }
        
        // acfe_flexible_toggle
        if (acf_maybe_get($field, 'acfe_flexible_toggle')) {
            if (!in_array('toggle', $actions)) {
                $actions[] = 'toggle';
            }
            acfe_unset($field, 'acfe_flexible_toggle');
        }
        
        // acfe_flexible_copy_paste
        if (acf_maybe_get($field, 'acfe_flexible_copy_paste')) {
            if (!in_array('copy', $actions)) {
                $actions[] = 'copy';
            }
            acfe_unset($field, 'acfe_flexible_copy_paste');
        }
        
        // acfe_flexible_lock
        if (acf_maybe_get($field, 'acfe_flexible_lock')) {
            if (!in_array('lock', $actions)) {
                $actions[] = 'lock';
            }
            acfe_unset($field, 'acfe_flexible_lock');
        }
        
        // acfe_flexible_close_button
        if (acf_maybe_get($field, 'acfe_flexible_close_button')) {
            if (!in_array('close', $actions)) {
                $actions[] = 'close';
            }
            acfe_unset($field, 'acfe_flexible_close_button');
        }
        
        // acfe_flexible_clone
        if (acf_maybe_get($field, 'acfe_flexible_clone')) {
            if (!in_array('clone', $actions)) {
                $actions[] = 'clone';
            }
            acfe_unset($field, 'acfe_flexible_clone');
        }
    
        $field['acfe_flexible_add_actions'] = $actions;
        
        return $field;
    }
    
    public function wrapper_attributes($wrapper, $field)
    {
        $actions = $field['acfe_flexible_add_actions'];
        
        // Title
        if (in_array('title', $actions)) {
            $wrapper['data-acfe-flexible-title-edition'] = 1;
        }
        
        // Toggle
        if (in_array('toggle', $actions)) {
            $wrapper['data-acfe-flexible-toggle'] = 1;
        }
        
        // Copy
        if (in_array('copy', $actions)) {
            $wrapper['data-acfe-flexible-copy-paste'] = 1;
        }
        
        // Lock
        $lock = in_array('lock', $actions);
        $lock = apply_filters("acfe/flexible/lock", $lock, $field);
        $lock = apply_filters("acfe/flexible/lock/name={$field['_name']}", $lock, $field);
        $lock = apply_filters("acfe/flexible/lock/key={$field['key']}", $lock, $field);
        
        if ($lock) {
            $wrapper['data-acfe-flexible-lock'] = 1;
        }
        
        // Clone
        if (in_array('close', $actions)) {
            $wrapper['data-acfe-flexible-close-button'] = 1;
        }
        
        return $wrapper;
    }
    
    public function load_fields($fields, $field)
    {
        
        // Actions
        $actions = $field['acfe_flexible_add_actions'];
        
        foreach ($field['layouts'] as $i => $layout) {
            
            // Title
            if (in_array('title', $actions)) {
    
                // Vars
                $key = "field_{$layout['key']}_title";
                $name = 'acfe_flexible_layout_title';
                $label = $layout['label'];
    
                // Add local
                acf_add_local_field(array(
                    'label'                 => false,
                    'key'                   => $key,
                    'name'                  => $name,
                    'type'                  => 'text',
                    'required'              => false,
                    'maxlength'             => false,
                    'default_value'         => $label,
                    'placeholder'           => $label,
                    'parent_layout'         => $layout['key'],
                    'parent'                => $field['key']
                ));
    
                // Add sub field
                array_unshift($fields, acf_get_field($key));
            }
            
            // Toggle
            if (in_array('toggle', $actions)) {
    
                // Vars
                $key = "field_{$layout['key']}_toggle";
                $name = 'acfe_flexible_toggle';
    
                // Add local
                acf_add_local_field(array(
                    'label'                 => false,
                    'key'                   => $key,
                    'name'                  => $name,
                    'type'                  => 'acfe_hidden',
                    'required'              => false,
                    'default_value'         => false,
                    'parent_layout'         => $layout['key'],
                    'parent'                => $field['key']
                ));
    
                // Add sub field
                array_unshift($fields, acf_get_field($key));
            }
        }
        
        return $fields;
    }
    
    public function prepare_layout($layout, $field, $i, $value, $prefix)
    {
        if (empty($layout['sub_fields'])) {
            return $layout;
        }
    
        // Actions
        $actions = $field['acfe_flexible_add_actions'];
    
        // Title
        if (in_array('title', $actions)) {
            $sub_field = acfe_extract_sub_field($layout, 'acfe_flexible_layout_title', $value);
            
            if ($sub_field) {
    
                // update prefix to allow for nested values
                $sub_field['prefix'] = $prefix;
                $sub_field['class'] = 'acfe-flexible-control-title';
                $sub_field['data-acfe-flexible-control-title-input'] = 1;
    
                $sub_field = acf_validate_field($sub_field);
                $sub_field = acf_prepare_field($sub_field);
    
                $input_attrs = array();
                foreach (array('type', 'id', 'class', 'name', 'value', 'placeholder', 'maxlength', 'pattern', 'readonly', 'disabled', 'required', 'data-acfe-flexible-control-title-input') as $k) {
                    if (isset($sub_field[$k])) {
                        $input_attrs[$k] = $sub_field[$k];
                    }
                }
    
                // render input
                echo acf_get_text_input(acf_filter_attrs($input_attrs));
            }
        }
        
        // Toggle
        if (in_array('toggle', $actions)) {
            $sub_field = acfe_extract_sub_field($layout, 'acfe_flexible_toggle', $value);
    
            if ($sub_field) {
    
                // update prefix to allow for nested values
                $sub_field['prefix'] = $prefix;
                $sub_field['class'] = 'acfe-flexible-layout-toggle';
    
                $sub_field = acf_validate_field($sub_field);
                $sub_field = acf_prepare_field($sub_field);
    
                $input_attrs = array();
                foreach (array('type', 'id', 'class', 'name', 'value') as $k) {
                    if (isset($sub_field[$k])) {
                        $input_attrs[$k] = $sub_field[$k];
                    }
                }
    
                // render input
                echo acf_get_hidden_input(acf_filter_attrs($input_attrs));
            }
        }
        
        return $layout;
    }
    
    public function layout_icons($icons, $layout, $field)
    {
        $actions = $field['acfe_flexible_add_actions'];
        
        // Toggle
        if (in_array('toggle', $actions)) {
            $icons = array_merge(array(
                'toggle' => '<a class="acf-icon small light acf-js-tooltip acfe-flexible-icon dashicons dashicons-hidden" href="#" title="Toggle layout" data-acfe-flexible-control-toggle="' . $layout['name'] . '"></a>'
            ), $icons);
        }
    
        // Copy
        if (in_array('copy', $actions)) {
            $icons = array_merge(array(
                'copy' => '<a class="acf-icon small light acf-js-tooltip acfe-flexible-icon dashicons dashicons-category" href="#" title="Copy layout" data-acfe-flexible-control-copy="' . $layout['name'] . '"></a>'
            ), $icons);
        }
        
        // Clone
        if (in_array('clone', $actions) && acf_version_compare(acf_get_setting('version'), '<', '5.9')) {
            $icons = array_merge($icons, array(
                'clone' => '<a class="acf-icon small light acf-js-tooltip acfe-flexible-icon dashicons dashicons-admin-page" href="#" title="Clone layout" data-acfe-flexible-control-clone="' . $layout['name'] . '"></a>'
            ));
        }
        
        return $icons;
    }
    
    public function secondary_actions($actions, $field)
    {
        if (!in_array('copy', $field['acfe_flexible_add_actions'])) {
            return $actions;
        }
        
        $actions['copy'] = '<a href="#" data-acfe-flexible-control-action="copy">' . __('Copy layouts', 'acfe') . '</a>';
        $actions['paste'] = '<a href="#" data-acfe-flexible-control-action="paste">' . __('Paste layouts', 'acfe') . '</a>';
        
        return $actions;
    }
    
    public function layout_title($title, $field, $layout, $i)
    {
        if (!in_array('title', $field['acfe_flexible_add_actions'])) {
            return $title;
        }
        
        // Get Layout Title
        $value = get_sub_field('acfe_flexible_layout_title');
        
        if (!empty($value)) {
            $title = wp_unslash($value);
        }
        
        return '<span class="acfe-layout-title acf-js-tooltip" title="' . __('Layout', 'acfe') . ': ' . esc_attr(strip_tags($layout['label'])) . '"><span class="acfe-layout-title-text">' . $title . '</span></span>';
    }
    
    public function load_value($value, $post_id, $field)
    {
        
        // Bail early if admin
        if (is_admin() && !wp_doing_ajax()) {
            return $value;
        }
        
        // Bail early if preview
        if (acf_maybe_get_POST('action') === 'acfe/flexible/layout_preview') {
            return $value;
        }
    
        if (empty($field['layouts'])) {
            return $value;
        }
        
        if (!in_array('toggle', $field['acfe_flexible_add_actions'])) {
            return $value;
        }
        
        $models = array();
        
        foreach ($field['layouts'] as $layout_key => $layout) {
            $models[$layout['name']] = array(
                'key'       => $layout['key'],
                'name'      => $layout['name'],
                'toggle'    => "field_{$layout['key']}_toggle"
            );
        }
        
        $value = acf_get_array($value);
        
        foreach ($value as $k => $layout) {
            if (!isset($models[$layout['acf_fc_layout']])) {
                continue;
            }
            
            if (!acf_maybe_get($layout, $models[$layout['acf_fc_layout']]['toggle'])) {
                continue;
            }
            
            unset($value[$k]);
        }
        
        return $value;
    }
}

acf_new_instance('acfe_field_flexible_content_actions');

endif;
