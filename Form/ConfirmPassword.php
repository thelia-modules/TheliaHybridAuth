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

use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;
use TheliaHybridAuth\TheliaHybridAuth;

/**
 * Class ConfirmPassword
 * @package TheliaHybridAuth\Form
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class ConfirmPassword extends BaseForm
{
    public function buildForm()
    {
        $this->formBuilder
            ->add("password", "password", array(
                "constraints" => array(
                    new Constraints\NotBlank(array(
                        'groups' => array('existing_customer'),
                    )),
                ),
                "label" => Translator::getInstance()->trans(
                    "Please enter your password",
                    array(),
                    TheliaHybridAuth::DOMAIN_NAME
                ),
                "label_attr" => array(
                    "for" => "password",
                ),
                "required"    => false,
            ));
    }

    public function getName()
    {
        return 'hybridauth_confirm_password';
    }
}