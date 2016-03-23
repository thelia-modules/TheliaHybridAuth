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
use Thelia\Form\CustomerCreateForm;
use Symfony\Component\Validator\Constraints;
use Thelia\Model\ConfigQuery;
use TheliaHybridAuth\TheliaHybridAuth;

/**
 * Class Register
 * @package TheliaHybridAuth\Form
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class Register extends CustomerCreateForm
{
    public function buildForm()
    {
        parent::buildForm();

        // override 'password' and 'password_confirm' to change type to hidden
        $this->formBuilder
            ->add("password", "hidden", array(
                "constraints" => array(
                    new Constraints\NotBlank(),
                    new Constraints\Length(array("min" => ConfigQuery::read("password.length", 4))),
                ),
                "label" => Translator::getInstance()->trans("Password", array(), TheliaHybridAuth::DOMAIN_NAME),
                "label_attr" => array(
                    "for" => "password",
                ),
            ))
            ->add("password_confirm", "hidden", array(
                "constraints" => array(
                    new Constraints\NotBlank(),
                    new Constraints\Length(array("min" => ConfigQuery::read("password.length", 4))),
                    new Constraints\Callback(array("methods" => array(
                        array($this, "verifyPasswordField"),
                    ))),
                ),
                "label" => Translator::getInstance()->trans(
                    "Password confirmation",
                    array(),
                    TheliaHybridAuth::DOMAIN_NAME
                ),
                "label_attr" => array(
                    "for" => "password_confirmation",
                ),
            ))
            ->add("provider", "hidden", array())
        ;
    }
}