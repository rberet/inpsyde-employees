<?php

/**
 * @package InpsydeEmployees
 */

namespace Inc\Api\Callbacks;

use \Inc\Base\BaseController;

class ManagerCallbacks extends BaseController
{
    public function checkboxSanitize($input)
    {
        $output = array();
        foreach ($this->managers as $key => $value) {
            $output[$key] = $output[$key] = isset($input[$key]) ? true : false;
        }
        return $output;
    }

    public function adminSectionManager()
    {
        echo __('Manage the Sections and Features of this Plugin by activating the checkboxes from the following list.', 'text_domain');
    }

    public function checkboxField($args)
    {
        $name = $args['label_for'];
        $classes = $args['class'];
        $option_name = $args['option_name'];
        $checkbox = get_option($option_name);
        $checked = isset($checkbox[$name]) ? ($checkbox[$name] ? true : false) : true;
        echo '<div class="' . $classes . '"><input type="checkbox" id="' . $name . '" name="' . $option_name . '[' . $name . ']" value="1" class="" ' . ($checked ? 'checked' : '') . '><label for="' . $name . '"><div></div></label></div>';
    }
}
