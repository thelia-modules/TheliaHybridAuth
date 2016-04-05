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
use Thelia\Tools\URL;
use TheliaHybridAuth\Model\ProviderConfig;
use TheliaHybridAuth\Model\ProviderConfigQuery;

class TheliaHybridAuth extends BaseModule
{
    /** @var string */
    const DOMAIN_NAME = 'theliahybridauth';

    public function preActivation(ConnectionInterface $con = null)
    {
        if (! $this->getConfigValue('is_initialized', false)) {
            $database = new Database($con);

            $database->insertSql(null, array(__DIR__ . '/Config/thelia.sql'));
        }

        return true;
    }

    public function postActivation(ConnectionInterface $con = null)
    {
        if (! $this->getConfigValue('is_initialized')) {
            (new ProviderConfig())->setProvider('Facebook')->setEnabled(false)->save();
            (new ProviderConfig())->setProvider('Google')->setEnabled(false)->save();
            (new ProviderConfig())->setProvider('Twitter')->setEnabled(false)->save();
            (new ProviderConfig())->setProvider('Yahoo')->setEnabled(false)->save();
            (new ProviderConfig())->setProvider('OpenID')->setEnabled(false)->save();
            (new ProviderConfig())->setProvider('LinkedIn')->setEnabled(false)->save();
            (new ProviderConfig())->setProvider('Foursquare')->setEnabled(false)->save();

            $this->setConfigValue('is_initialized', true);
        }
    }

    public static function getConfigByProvider($providerName)
    {
        $providerConfig = ProviderConfigQuery::create()->filterByProvider($providerName)->findOne();

        return array(
            "base_url" => URL::getInstance()->getBaseUrl().'/hybridauth/process',
            "providers" => array(
                $providerName => array(
                    "enabled" => true,
                    "keys" => array(
                        "id" => $providerConfig->getProviderKey(),
                        "key" => $providerConfig->getProviderKey(),
                        "secret" => $providerConfig->getSecret()
                    ),
                    "scope" => $providerConfig->getScope()
                )
            ),
        );
    }

    public static function initHybridAuth()
    {
        if (!class_exists('Hybrid_Auth')) {
            require_once(__DIR__ . '/HybridAuth/Hybrid/Auth.php');
        }
        if (!class_exists('Hybrid_Endpoint')) {
            require_once(__DIR__.'/HybridAuth/Hybrid/Endpoint.php');
        }
    }
}
