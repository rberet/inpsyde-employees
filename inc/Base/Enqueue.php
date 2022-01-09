<?php

/**
 * @package InpsydeEmployees
 */

namespace Inc\Base;

use \Inc\Base\BaseController;

/**
 *
 */
class Enqueue extends BaseController
{
    public function register()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue'));
    }

    public function enqueue()
    {
        // enqueue all our scripts
        wp_enqueue_script('media-upload');
        wp_enqueue_script('wp-api');
        wp_enqueue_media();
        wp_enqueue_style('mypluginstyle', $this->plugin_url . 'assets/style.min.css');
        wp_enqueue_script('meta-box-image', $this->plugin_url . 'assets/media.js');
        wp_enqueue_script('my-script', get_stylesheet_directory_uri(). '/js/my-script.js');
        wp_localize_script('my-script', 'myScript', array( 'pluginsUrl' => plugins_url(), ));
    }
}
