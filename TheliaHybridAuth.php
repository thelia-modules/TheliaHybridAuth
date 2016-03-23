<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace TheliaHybridAuth;

use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Install\Database;
use Thelia\Module\BaseModule;

class TheliaHybridAuth extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'theliahybridauth';

    public function preActivation(ConnectionInterface $con = null)
    {
        if (! $this->getConfigValue('is_initialized', false)) {
            $database = new Database($con);

            $database->insertSql(null, array(__DIR__ . '/Config/thelia.sql'));

            $this->setConfigValue('is_initialized', true);
        }

        return true;
    }

    public function postActivation(ConnectionInterface $con = null)
    {
        $this->setConfigValue(
            'provider_list',
            'Facebook,Google,Twitter,Yahoo,OpenID,LinkedIn,Foursquare'
        );
    }

    public static function getConfigByProvider($providerName)
    {
        return array(
            "base_url" => "",
            "providers" => array(
                $providerName => array(
                    "enabled" => true,
                    "keys" => array(
                        "id" => TheliaHybridAuth::getConfigValue($providerName.'_id'),
                        "key" => TheliaHybridAuth::getConfigValue($providerName.'_id'),
                        "secret" => TheliaHybridAuth::getConfigValue($providerName.'_secret')
                    )
                )
            ),
        );
    }
}
