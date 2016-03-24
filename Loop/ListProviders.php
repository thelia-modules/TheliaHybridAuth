<?php
/*************************************************************************************/
/*      This file is part of the TheliaHybridAuth package.                           */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace TheliaHybridAuth\Loop;

use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use TheliaHybridAuth\Model\HybridAuthQuery;
use TheliaHybridAuth\TheliaHybridAuth;
use Thelia\Type;

/**
 * Class ListProviders
 * @package TheliaHybridAuth\Loop
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class ListProviders extends BaseLoop implements ArraySearchLoopInterface
{
    public function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createBooleanOrBothTypeArgument('enabled', true),
            Argument::createIntTypeArgument('customer_id'),
            Argument::createAnyTypeArgument('exclude')
        );
    }

    public function buildArray()
    {
        $array = array();
        $providers = explode(',', TheliaHybridAuth::getConfigValue('provider_list'));
        $providersExcluded = explode(',', $this->getExclude());

        if (null !== $customerId = $this->getCustomerId()) {
            $customerProviders = array();
            $results = HybridAuthQuery::create()->filterByCustomerId($customerId)->find();

            foreach ($results as $result) {
                $customerProviders[] = $result->getProvider();
            }
        }

        foreach ($providers as $providerName) {
            $enabled = (TheliaHybridAuth::getConfigValue($providerName.'_enabled') == null) ? false : true;
            $provider = array();
            $provider['name'] = $providerName;
            $provider['enabled'] = $enabled;

            if (!in_array($providerName, $providersExcluded)) {
                if (isset($customerProviders) && in_array($providerName, $customerProviders)) {
                    if ($this->getEnabled() === Type\BooleanOrBothType::ANY) {
                        $array[] = $provider;
                    } elseif ($enabled === $this->getEnabled()) {
                        $array[] = $provider;
                    }
                } elseif (!isset($customerProviders)) {
                    if ($this->getEnabled() === Type\BooleanOrBothType::ANY) {
                        $array[] = $provider;
                    } elseif ($enabled === $this->getEnabled()) {
                        $array[] = $provider;
                    }
                }
            }
        }

        return $array;
    }

    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $provider) {
            $loopResultRow = new LoopResultRow($provider);

            $loopResultRow->set('NAME', $provider['name'])
                ->set('ENABLED', $provider['enabled']);

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}