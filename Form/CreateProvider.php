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

use Symfony\Component\Validator\Constraints;
use Thelia\Core\Translation\Translator;
use TheliaHybridAuth\TheliaHybridAuth;

/**
 * Class CreateProvider
 * @package TheliaHybridAuth\Form
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class CreateProvider extends BaseProvider
{
    public function buildForm()
    {
        $this->formBuilder
            ->add('name', 'text', array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Name', array(), TheliaHybridAuth::DOMAIN_NAME),
                'label_attr' => array(
                    'for' => 'name'
                ),
                "constraints" => array(
                    new Constraints\NotBlank()
                ),
            ))
            ->add('id', 'text', array(
                'required' => true,
                'label' => 'Id',
                'label_attr' => array(
                    'for' => 'id'
                ),
                "constraints" => array(
                    new Constraints\NotBlank()
                ),
            ))
            ->add('secret', 'text', array(
                'required' => true,
                'label' => Translator::getInstance()->trans('Secret', array(), TheliaHybridAuth::DOMAIN_NAME),
                'label_attr' => array(
                    'for' => 'secret'
                ),
                "constraints" => array(
                    new Constraints\NotBlank()
                ),
            ))
            ->add('scope', 'text', array(
                'label' => Translator::getInstance()->trans('Scope', array(), TheliaHybridAuth::DOMAIN_NAME),
                'label_attr' => array(
                    'for' => 'scope'
                )
            ))
        ;
    }

    public function getName()
    {
        return 'create_provider';
    }
}