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
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use TheliaHybridAuth\TheliaHybridAuth;

/**
 * Class ListProviders
 * @package TheliaHybridAuth\Loop
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class ListProviders extends BaseLoop implements ArraySearchLoopInterface
{
    public function getArgDefinitions()
    {
        return new ArgumentCollection();
    }

    public function buildArray()
    {
        $array = array();
        $providers = explode(',', TheliaHybridAuth::getConfigValue('provider_list'));

        foreach ($providers as $provider_name) {
            $enabled = (TheliaHybridAuth::getConfigValue($provider_name.'_enabled') == null) ? false : true;
            $provider = array();
            $provider['name'] = $provider_name;
            $provider['enabled'] = $enabled;
            $array[] = $provider;
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