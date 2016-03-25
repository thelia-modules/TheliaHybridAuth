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

namespace TheliaHybridAuth\Controller;

use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
use Thelia\Log\Tlog;
use Thelia\Tools\URL;
use TheliaHybridAuth\Model\ProviderConfig;
use TheliaHybridAuth\Model\ProviderConfigQuery;
use TheliaHybridAuth\TheliaHybridAuth;

/**
 * Class Configuration
 * @package TheliaHybridAuth\Controller
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class Configuration extends BaseAdminController
{
    public function viewAction($providerName)
    {
        if (null !== $response = $this->checkAuth(array(), 'TheliaHybridAuth', AccessManager::VIEW)) {
            return $response;
        }
        //check provider existence
        if (null === $providerConfig = ProviderConfigQuery::create()->filterByProvider($providerName)->findOne()) {
            throw new \Exception(Translator::getInstance()->trans(
                "Provider not found",
                array(),
                TheliaHybridAuth::DOMAIN_NAME
            ));
        }

        $providerId = $providerConfig->getKey();
        $providerSecret = $providerConfig->getSecret();

        $form = $this->createForm('update.provider', 'form', array(
            'id' => $providerId,
            'secret' => $providerSecret,
        ));

        $this->getParserContext()->addForm($form);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->render("include/form-update");
        } else {
            return $this->generateRedirectFromRoute(
                'admin.module.configure',
                array(),
                array('module_code' => 'TheliaHybridAuth')
            );
        }
    }

    public function testProviderAction($providerName)
    {
        if (null !== $response = $this->checkAuth(array(), 'TheliaHybridAuth', AccessManager::VIEW)) {
            return $response;
        }

        try {
            require_once(__DIR__ . '/../HybridAuth/Hybrid/Auth.php');
            require_once(__DIR__.'/../HybridAuth/Hybrid/Endpoint.php');

            if (isset($_REQUEST['hauth_start']) || isset($_REQUEST['hauth_done'])) {
                \Hybrid_Endpoint::process();
            }

            $config = TheliaHybridAuth::getConfigByProvider($providerName);

            $hybridauth = new \Hybrid_Auth($config);

            $hybridauth->authenticate(
                $providerName,
                array(URL::getInstance()->retrieveCurrent($this->getRequest()))
            );

        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return $this->generateRedirectFromRoute(
            'admin.module.configure',
            array(
                "error" => (isset($message)) ? 1 : 0,
                "provider" => $providerName
            ),
            array('module_code' => 'TheliaHybridAuth')
        );

    }

    public function addProviderAction()
    {
        if (null !== $response = $this->checkAuth(array(), 'TheliaHybridAuth', AccessManager::VIEW)) {
            return $response;
        }

        $formProvider = $this->createForm('create.provider');

        try {

            $form = $this->validateForm($formProvider, 'POST');

            $providerName = $form->get('name')->getData();
            $providerId = $form->get('id')->getData();
            $providerSecret = $form->get('secret')->getData();

            if (null !== ProviderConfigQuery::create()->filterByProvider($providerName)->findOne()) {
                throw new \Exception(Translator::getInstance()->trans(
                    'This provider already exists, use the "edit" button to update it',
                    array(),
                    TheliaHybridAuth::DOMAIN_NAME
                ));
            }

            (new ProviderConfig())
                ->setProvider($providerName)
                ->setEnabled(false)
                ->setKey($providerId)
                ->setSecret($providerSecret)
                ->save()
            ;

            return $this->generateSuccessRedirect($formProvider);

        } catch (\Exception $e) {
            $message = Translator::getInstance()->trans(
                'Oops an error occured : %e',
                [
                    '%e' => $e->getMessage()
                ],
                TheliaHybridAuth::DOMAIN_NAME
            );
        }

        $formProvider->setErrorMessage($message);

        $this->getParserContext()
            ->addForm($formProvider)
            ->setGeneralError($message)
        ;

        if ($formProvider->hasErrorUrl()) {
            return $this->generateErrorRedirect($formProvider);
        } else {
            return $this->generateRedirectFromRoute(
                'admin.module.configure',
                array(),
                array('module_code' => 'TheliaHybridAuth')
            );
        }
    }

    public function updateProviderAction($providerName)
    {
        if (null !== $response = $this->checkAuth(array(), 'TheliaHybridAuth', AccessManager::UPDATE)) {
            return $response;
        }

        $formProvider = $this->createForm('update.provider');

        try {

            $form = $this->validateForm($formProvider, 'POST');

            $providerConfig = ProviderConfigQuery::create()->filterByProvider($providerName)->findOne();

            $providerConfig
                ->setKey($form->get('id')->getData())
                ->setSecret($form->get('secret')->getData())
                ->save()
            ;

            return $this->viewAction($providerName);

        } catch (\Exception $e) {
            $message = Translator::getInstance()->trans(
                'Oops an error occured : %e',
                [
                    '%e' => $e->getMessage()
                ],
                TheliaHybridAuth::DOMAIN_NAME
            );
        }

        $formProvider->setErrorMessage($message);

        $this->getParserContext()
            ->addForm($formProvider)
            ->setGeneralError($message)
        ;

        if ($formProvider->hasErrorUrl()) {
            return $this->generateErrorRedirect($formProvider);
        } else {
            return $this->generateRedirectFromRoute(
                'admin.module.configure',
                array(),
                array('module_code' => 'TheliaHybridAuth')
            );
        }

    }

    public function deleteProviderAction($providerName)
    {
        if (null !== $response = $this->checkAuth(array(), 'TheliaHybridAuth', AccessManager::DELETE)) {
            return $response;
        }

        $formProvider = $this->createForm('base.provider');

        try {

            $this->validateForm($formProvider, 'POST');

            if (null === $providerConfig = ProviderConfigQuery::create()->filterByProvider($providerName)->findOne()) {

                throw new \Exception(Translator::getInstance()->trans(
                    'The provider %name doesn\'t exist',
                    [
                        '%name' => $providerName
                    ],
                    TheliaHybridAuth::DOMAIN_NAME
                ));

            } else {

                $providerConfig->delete();
            }

            return $this->generateSuccessRedirect($formProvider);

        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("%provider delete", array('%provider' => $providerName)),
                $e->getMessage(),
                $formProvider
            );
        }

        if ($formProvider->hasErrorUrl()) {
            return $this->generateErrorRedirect($formProvider);
        } else {
            return $this->generateRedirectFromRoute(
                'admin.module.configure',
                array(),
                array('module_code' => 'TheliaHybridAuth')
            );
        }
    }

    public function toggleActivationAction($providerName)
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, array(), AccessManager::UPDATE)) {
            return $response;
        }
        $message = null;
        try {

            $providerConfig = ProviderConfigQuery::create()->filterByProvider($providerName)->findOne();

            if (!$providerConfig->getId() || !$providerConfig->getSecret()) {
                throw new \Exception(Translator::getInstance()->trans(
                    'You need to provide an id and secret for this provider',
                    array(),
                    TheliaHybridAuth::DOMAIN_NAME
                ));
            }

            $providerConfig
                ->setEnabled(($providerConfig->getEnabled()) ? false : true)
                ->save()
            ;

        } catch (\Exception $e) {
            $message = $e->getMessage();

            Tlog::getInstance()->addError("Failed to activate/desactivate provider:", $e);
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($message) {
                $response = $this->jsonResponse(json_encode(array(
                    "error" => $message
                )), 500);
            } else {
                $response = $this->nullResponse();
            }
        } else {
            $response = $this->generateRedirectFromRoute(
                'admin.module.configure',
                array(),
                array('module_code' => 'TheliaHybridAuth')
            );
            return $response;
        }

        return $response;
    }
}