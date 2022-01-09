<?php

/**
 * @package InpsydeEmployees
 */

namespace Inc\Base;

class Activate
{
    public static function activate()
    {
        flush_rewrite_rules();
        $default = array(
            'worker_manager' => 1
        );
        if (!get_option('overview_plugin')) {
            update_option('overview_plugin', $default);
        }
    }
}
