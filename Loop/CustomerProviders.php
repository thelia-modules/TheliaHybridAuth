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

use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use TheliaHybridAuth\Model\HybridAuthQuery;

/**
 * Class CustomerProviders
 * @package TheliaHybridAuth\Loop
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class CustomerProviders extends BaseLoop implements PropelSearchLoopInterface
{
    public function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('customer_id')
        );
    }

    public function buildModelCriteria()
    {
        $customerId = $this->getCustomerId();

        $query = HybridAuthQuery::create()->filterByCustomerId($customerId);

        return $query;
    }

    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $provider) {
            $loopResultRow = (new LoopResultRow($provider))
                ->set('NAME', $provider->getProvider());

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}