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

use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Loop\Argument\Argument;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use TheliaHybridAuth\Model\HybridAuthQuery;
use TheliaHybridAuth\Model\ProviderConfigQuery;
use Thelia\Type;

/**
 * Class ListProviders
 * @package TheliaHybridAuth\Loop
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class ListProviders extends BaseLoop implements PropelSearchLoopInterface
{
    public function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createBooleanOrBothTypeArgument('enabled', true),
            Argument::createIntTypeArgument('customer_id'),
            Argument::createAnyTypeArgument('exclude')
        );
    }

    public function buildModelCriteria()
    {

        $query = ProviderConfigQuery::create();

        if ($this->getEnabled() !== Type\BooleanOrBothType::ANY) {
            $query->filterByEnabled($this->getEnabled());
        }

        if (null !== $this->getCustomerId()) {
            $query->filterByHybridAuth(HybridAuthQuery::create()->filterByCustomerId($this->getCustomerId())->find());
        }

        if (null !== $providersExcluded = explode(',', $this->getExclude())) {
            foreach ($providersExcluded as $provider) {
                $query->filterByProvider($provider, Criteria::NOT_EQUAL);
            }
        }

        return $query;
    }

    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $provider) {
            $loopResultRow = new LoopResultRow($provider);

            $loopResultRow->set('NAME', $provider->getProvider())
                ->set('ENABLED', $provider->getEnabled());

            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}