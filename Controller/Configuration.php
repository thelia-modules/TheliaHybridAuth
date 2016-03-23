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
        if (!in_array($providerName, explode(',', TheliaHybridAuth::getConfigValue('provider_list')))) {
            throw new \Exception(Translator::getInstance()->trans(
                "Provider not found",
                array(),
                TheliaHybridAuth::DOMAIN_NAME
            ));
        }

        $providerId = TheliaHybridAuth::getConfigValue($providerName.'_id');
        $providerSecret = TheliaHybridAuth::getConfigValue($providerName.'_secret');

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
            $providerList = explode(',', TheliaHybridAuth::getConfigValue('provider_list'));

            if (in_array($providerName, $providerList)) {
                throw new \Exception(Translator::getInstance()->trans(
                    'This provider already exists, use the "edit" button to update it',
                    array(),
                    TheliaHybridAuth::DOMAIN_NAME
                ));
            }

            TheliaHybridAuth::setConfigValue(
                'provider_list',
                TheliaHybridAuth::getConfigValue('provider_list').','.$providerName
            );

            TheliaHybridAuth::setConfigValue($providerName.'_id', $providerId);
            TheliaHybridAuth::setConfigValue($providerName.'_secret', $providerSecret);

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

            TheliaHybridAuth::setConfigValue($providerName.'_id', $form->get('id')->getData());
            TheliaHybridAuth::setConfigValue($providerName.'_secret', $form->get('secret')->getData());

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

            $providers = explode(',', TheliaHybridAuth::getConfigValue('provider_list'));

            if (!in_array($providerName, $providers)) {

                throw new \Exception(Translator::getInstance()->trans(
                    'The provider %name doesn\'t exist',
                    [
                        '%name' => $providerName
                    ],
                    TheliaHybridAuth::DOMAIN_NAME
                ));

            } else {
                $newProviders = array();

                foreach ($providers as $provider) {
                    if ($provider != $providerName) {
                        $newProviders[] = $provider;
                    }
                }

                TheliaHybridAuth::setConfigValue('provider_list', implode(',', $newProviders));
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

            if (!TheliaHybridAuth::getConfigValue($providerName.'_id')
                || !TheliaHybridAuth::getConfigValue($providerName.'_secret')) {
                throw new \Exception(Translator::getInstance()->trans(
                    'You need to provide an id and secret for this provider',
                    array(),
                    TheliaHybridAuth::DOMAIN_NAME
                ));
            }

            TheliaHybridAuth::setConfigValue(
                $providerName.'_enabled',
                (TheliaHybridAuth::getConfigValue($providerName.'_enabled') == false) ? true : false
            );

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