<?php

if (!defined('ABSPATH')) {
    exit;
}

// Check setting
if (!acfe_get_setting('modules/force_sync')) {
    return;
}

if (!class_exists('acfe_pro_force_sync')):

class acfe_pro_force_sync
{
    
    /*
     * Construct
     */
    public function __construct()
    {
        add_action('current_screen', array($this, 'current_screen'));
    }
    
    /*
     * Current Screen
     */
    public function current_screen()
    {
        
        // Vars
        $screen = get_current_screen();
        $rule = acf_is_screen(array('dashboard', 'edit-acf-field-group'));
        
        // Filter
        $rule = apply_filters('acfe/modules/force_sync/rule', $rule, $screen);
        
        if (!$rule) {
            return;
        }
        
        // Sync
        $this->sync_field_groups();
    }
    
    /*
     * Sync Field Groups
     */
    public function sync_field_groups($silent = false)
    {
        
        // Avoid timestamp update during force sync
        acf_update_setting('json', false);
    
        // Vars
        $files = acf_get_local_json_files();
        $desync = $this->get_desync_field_groups();
        $field_groups = array();
        
        // Exclude
        $exclude = apply_filters('acfe/modules/force_sync/exclude', array());
        
        // Loop
        foreach ($desync as $key => $group) {
            
            // Check exclude
            if (in_array($key, $exclude)) {
                continue;
            }
            
            $local_field_group = json_decode(file_get_contents($files[$key]), true);
            $local_field_group['ID'] = $group['ID'];
            $result = acf_import_field_group($local_field_group);
    
            $field_groups[] = $result;
        }
        
        // Sync done
        if ($field_groups) {
            if (!$silent) {
                $this->remove_desync($field_groups);
                $this->add_admin_notice($field_groups);
            }
    
            do_action('acfe/modules/force_sync/sync', $field_groups);
        }
        
        // Re-enable json
        acf_update_setting('json', true);
        
        return $field_groups;
    }
    
    /*
     * Get Desync Field Groups
     */
    public function get_desync_field_groups()
    {
        
        // Vars
        $desync = array();
        $files = acf_get_local_json_files();
        
        if (!$files) {
            return $desync;
        }
    
        // Get all groups in a single cached query to check if sync is available.
        $field_groups = acf_get_field_groups();
        
        foreach ($field_groups as $field_group) {
        
            // Extract vars.
            $local = acf_maybe_get($field_group, 'local');
            $modified = acf_maybe_get($field_group, 'modified');
            $private = acf_maybe_get($field_group, 'private');
        
            // Ignore if is private.
            if ($private) {
                continue;
            
            // Ignore not local "json".
            } elseif ($local !== 'json') {
                continue;
            
            // Append to sync if not yet in database.
            } elseif (!$field_group['ID']) {
                $desync[$field_group['key']] = $field_group;
            
            // Append to sync if "json" modified time is newer than database.
            } elseif ($modified && $modified > get_post_modified_time('U', true, $field_group['ID'])) {
                $desync[$field_group['key']] = $field_group;
            }
        }
        
        return $desync;
    }
    
    /*
     * Remove Desync
     */
    public function remove_desync($field_groups = array())
    {
        if (acf_version_compare(acf_get_setting('version'), '<', '5.9')) {
            return;
        }
        
        foreach ($field_groups as $field_group) {
            unset(acf_get_instance('ACF_Admin_Field_Groups')->sync, $field_group['key']);
        }
        
        if (empty(acf_get_instance('ACF_Admin_Field_Groups')->sync)) {
            acf_get_instance('ACF_Admin_Field_Groups')->sync = array();
        }
        
        acf_get_instance('ACF_Admin_Field_Groups')->setup_sync();
        acf_get_instance('ACFE_Field_Groups')->sync = acf_get_instance('ACF_Admin_Field_Groups')->sync;
    }
    
    /*
     * Add Admin Notice
     */
    public function add_admin_notice($field_groups)
    {
        
        // Vars
        $count = count($field_groups);
        $field_groups_links = array();
        
        foreach ($field_groups as $field_group) {
            $field_groups_links[] = '<a href="' . admin_url('post.php?post=' . $field_group['ID'] . '&action=edit') . '">' . $field_group['title'] . '</a>';
        }
        
        $title = $count > 1 ? $count . ' Field Groups automatically synchronized: ' : $count . ' Field Group automatically synchronized: ';
        
        acf_add_admin_notice($title . implode(', ', $field_groups_links), 'success');
    }
}

acf_new_instance('acfe_pro_force_sync');

endif;

/*
 * acfe_force_sync
 */
function acfe_force_sync()
{
    return acf_get_instance('acfe_pro_force_sync')->sync_field_groups(true);
}
