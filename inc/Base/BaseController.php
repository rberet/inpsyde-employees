<?php

/**
 * @package InpsydeEmployees
 */

namespace Inc\Base;

class BaseController
{
    public $plugin_path;
    public $plugin_url;
    public $plugin;

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path(dirname(__FILE__, 2));
        $this->plugin_url = plugin_dir_url(dirname(__FILE__, 2));
        $this->plugin = plugin_basename(dirname(__FILE__, 3)) . '/inpsyde-plugin.php';
    }

    public function activated(string $key)
    {
        $option = get_option('overview_plugin');
        return isset($option[$key]) ? $option[$key] : false;
    }
}
