<?php

/**
 * @package InpsydeEmployees
 */

namespace Inc\Api\Callbacks;

use Inc\Base\BaseController;

class EmployerCallbacks extends BaseController
{
    public function shortcodePage()
    {
        return require_once("$this->plugin_path/templates/employer.php");
    }
}
