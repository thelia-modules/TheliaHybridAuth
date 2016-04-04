<?php
/**
 * Created by PhpStorm.
 * User: tompradat
 * Date: 04/04/2016
 * Time: 10:53
 */

namespace TheliaHybridAuth\Controller;

use Thelia\Controller\Admin\BaseAdminController;
use TheliaHybridAuth\TheliaHybridAuth;

class HybridAuthProcess extends BaseAdminController
{
    public function processAction()
    {
        TheliaHybridAuth::initHybridAuth();
        \Hybrid_Endpoint::process();
    }
}