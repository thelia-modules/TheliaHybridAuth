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

use Thelia\Form\BaseForm;

/**
 * Class BaseProvider
 * @package TheliaHybridAuth\Form
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class BaseProvider extends BaseForm
{
    public function getName()
    {
        return 'provider';
    }

    public function buildForm()
    {

    }
}