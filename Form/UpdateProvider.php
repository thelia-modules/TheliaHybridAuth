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

namespace TheliaHybridAuth\Form;

/**
 * Class UpdateProvider
 * @package TheliaHybridAuth\Form
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class UpdateProvider extends CreateProvider
{
    public function getName()
    {
        return 'update_provider';
    }

    public function buildForm()
    {
        parent::buildForm();

        $this->formBuilder
            ->add('name', 'hidden');
    }
}