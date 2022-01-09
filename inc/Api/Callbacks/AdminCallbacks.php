<?php

/**
 * @package InpsydeEmployees
 */

namespace Inc\Api\Callbacks;

use \Inc\Base\BaseController;

class AdminCallbacks extends BaseController
{
    public function adminWorker()
    {
        return require_once("$this->plugin_path/templates/employer.php");
    }
}
