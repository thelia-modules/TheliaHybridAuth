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

use Front\Controller\CustomerController;
use Front\Front;
use Thelia\Core\Event\Customer\CustomerCreateOrUpdateEvent;
use Thelia\Core\Event\Newsletter\NewsletterEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Security\Exception\WrongPasswordException;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Log\Tlog;
use Thelia\Model\Base\CustomerQuery;
use Thelia\Model\NewsletterQuery;
use Thelia\Tools\Password;
use Thelia\Tools\URL;
use TheliaHybridAuth\Form\ConfirmPassword;
use TheliaHybridAuth\Model\HybridAuth;
use TheliaHybridAuth\Model\HybridAuthQuery;
use TheliaHybridAuth\TheliaHybridAuth;

/**
 * Class HybridAuthCustomerController
 * @package TheliaHybridAuth\Controller
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class HybridAuthCustomerController extends CustomerController
{
    public function viewRegisterAction()
    {
        if ($this->getSecurityContext()->hasCustomerUser()) {
            // Redirect to home page
            return $this->generateRedirect(URL::getInstance()->getIndexPage());
        }

        try {

            TheliaHybridAuth::initHybridAuth();

            if (isset($_REQUEST['hauth_start']) || isset($_REQUEST['hauth_done'])) {
                \Hybrid_Endpoint::process();
            }

            $providerName = ucfirst($this->getRequest()->get('provider'));

            $config = TheliaHybridAuth::getConfigByProvider($providerName);

            $hybridauth = new \Hybrid_Auth($config);

            $provider = $hybridauth->authenticate(
                $providerName,
                array(URL::getInstance()->retrieveCurrent($this->getRequest()))
            );

            $user_profile = $provider->getUserProfile();

            // set a random password for the user
            $password = Password::generateRandom(8);

            $this->getRequest()->getSession()->set("hybridauth_provider", $providerName);
            $this->getRequest()->getSession()->set("hybridauth_token", $user_profile->identifier);

            $form = $this->createForm("register.hybrid.auth", "form", array(
                'title' => $this->getTitleFromGender($user_profile),
                'firstname' => $user_profile->firstName,
                'lastname' => $user_profile->lastName,
                'email' => ($user_profile->emailVerified) ? $user_profile->emailVerified : $user_profile->email,
                'email_confirm' => ($user_profile->emailVerified) ? $user_profile->emailVerified : $user_profile->email,
                'cellphone' => $user_profile->phone,
                'address' => $user_profile->address,
                'zipcode' => $user_profile->zip,
                'city' => $user_profile->city,
                'password' => $password,
                'password_confirm' => $password,
                'provider' => $providerName
            ));

            $this->getParserContext()->addForm($form);

            return $this->render("register-hybrid-auth");

        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $form = $this->createForm("register.hybrid.auth");

        $form->setErrorMessage($message);

        $this->getParserContext()->addForm($form)->setGeneralError($message);

        return $this->render("register-hybrid-auth");
    }

    protected function getTitleFromGender($userProfile)
    {
        switch ($userProfile->gender) {
            case 'male':
                return 1;
                break;

            case 'female':
                return 2;
                break;

            default:
                return null;
                break;
        }
    }

    public function associationAction($providerName)
    {
        if (! $this->getSecurityContext()->hasCustomerUser()) {
            return $this->generateRedirect(URL::getInstance()->getIndexPage());
        }

        try {

            TheliaHybridAuth::initHybridAuth();

            if (isset($_REQUEST['hauth_start']) || isset($_REQUEST['hauth_done'])) {
                \Hybrid_Endpoint::process();
            }

            $config = TheliaHybridAuth::getConfigByProvider($providerName);

            $hybridauth = new \Hybrid_Auth($config);

            $provider = $hybridauth->authenticate(
                $providerName,
                array(URL::getInstance()->retrieveCurrent($this->getRequest()))
            );

            $identifier = $provider->getUserProfile()->identifier;

            if (null !== $id = $this->getSession()->getCustomerUser()->getId()) {
                $hybridauthEntry = new HybridAuth();
                $hybridauthEntry->setCustomerId($id)->setToken($identifier)->setProvider($providerName);
                $hybridauthEntry->save();
            }

            return $this->generateRedirectFromRoute('customer.home');

        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        return $this->render('account', array('error' => $message ));
    }

    public function removeAssociationAction($providerName)
    {
        if (! $this->getSecurityContext()->hasCustomerUser()) {
            return $this->generateRedirect(URL::getInstance()->getIndexPage());
        }

        if (null !== $id = $this->getSession()->getCustomerUser()->getId()) {
            HybridAuthQuery::create()->filterByCustomerId($id)->filterByProvider($providerName)->findOne()->delete();
        }

        return $this->generateRedirectFromRoute('customer.home');
    }

    public function createAction()
    {
        if (! $this->getSecurityContext()->hasCustomerUser()) {
            $customerCreation = $this->createForm("register.hybrid.auth", "form");

            try {
                $form = $this->validateForm($customerCreation, "post");

                $customerCreateEvent = $this->createEventInstance($form->getData());

                $this->dispatch(TheliaEvents::CUSTOMER_CREATEACCOUNT, $customerCreateEvent);

                $newCustomer = $customerCreateEvent->getCustomer();

                // Newsletter
                if (true === $form->get('newsletter')->getData()) {
                    $newsletterEmail = $newCustomer->getEmail();
                    $nlEvent = new NewsletterEvent(
                        $newsletterEmail,
                        $this->getRequest()->getSession()->getLang()->getLocale()
                    );
                    $nlEvent->setFirstname($newCustomer->getFirstname());
                    $nlEvent->setLastname($newCustomer->getLastname());

                    // Security : Check if this new Email address already exist
                    if (null !== $newsletter = NewsletterQuery::create()->findOneByEmail($newsletterEmail)) {
                        $nlEvent->setId($newsletter->getId());
                        $this->dispatch(TheliaEvents::NEWSLETTER_UPDATE, $nlEvent);
                    } else {
                        $this->dispatch(TheliaEvents::NEWSLETTER_SUBSCRIBE, $nlEvent);
                    }
                }

                $this->processLogin($customerCreateEvent->getCustomer());

                $cart = $this->getSession()->getSessionCart($this->getDispatcher());
                if ($cart->getCartItems()->count() > 0) {
                    $response = $this->generateRedirectFromRoute('cart.view');
                } else {
                    $response = $this->generateSuccessRedirect($customerCreation);
                }

                //set data for hybrid_auth table
                $hybridAuth = new HybridAuth();
                $hybridAuth->setCustomerId($customerCreateEvent->getCustomer()->getId())
                    ->setProvider($this->getRequest()->getSession()->get("hybridauth_provider"))
                    ->setToken($this->getRequest()->getSession()->get("hybridauth_token"))
                    ->save();

                return $response;

            } catch (FormValidationException $e) {
                $message = $this->getTranslator()->trans(
                    "Please check your input: %s",
                    [
                        '%s' => $e->getMessage()
                    ],
                    Front::MESSAGE_DOMAIN
                );
            } catch (\Exception $e) {
                $message = $this->getTranslator()->trans(
                    "Sorry, an error occured: %s",
                    [
                        '%s' => $e->getMessage()
                    ],
                    Front::MESSAGE_DOMAIN
                );
            }

            Tlog::getInstance()->error(
                sprintf(
                    "Error during customer creation process : %s. Exception was %s",
                    $message,
                    $e->getMessage()
                )
            );

            $customerCreation->setErrorMessage($message);

            $this->getParserContext()
                ->addForm($customerCreation)
                ->setGeneralError($message)
            ;

                return $this->render("register-hybrid-auth");
        }
    }

    public function loginAction()
    {
        if (! $this->getSecurityContext()->hasCustomerUser()) {

            TheliaHybridAuth::initHybridAuth();

            if (isset($_REQUEST['hauth_start']) || isset($_REQUEST['hauth_done'])) {
                \Hybrid_Endpoint::process();
            }

            $providerName = ucfirst($this->getRequest()->get('provider'));

            $config = TheliaHybridAuth::getConfigByProvider($providerName);

            $hybridauth = new \Hybrid_Auth($config);

            $provider = $hybridauth->authenticate(
                $providerName,
                array(URL::getInstance()->retrieveCurrent($this->getRequest()))
            );

            $identifier = $provider->getUserProfile()->identifier;

            $mail = ($provider->getUserProfile()->emailVerified) ?
                $provider->getUserProfile()->emailVerified : $provider->getUserProfile()->email;

            $hybridauth = HybridAuthQuery::create()
                ->filterByProvider($providerName)->filterByToken($identifier)->findOne();

            // if user registered with hybridauth then log him
            if ($hybridauth !== null) {
                $customer = CustomerQuery::create()->findOneById($hybridauth->getCustomerId());
                $this->processLogin($customer);
                return $this->generateRedirect(URL::getInstance()->getIndexPage());

            // if user registered without hybridauth, try to associate account with hybridauth
            } elseif ($mail !== null && CustomerQuery::create()->filterByEmail($mail)->findOne() !== null) {

                $this->getRequest()->getSession()->set('user_email', $mail);
                $this->getRequest()->getSession()->set('user_token', $identifier);
                $this->getRequest()->getSession()->set('provider', $providerName);
                $this->setCurrentRouter('router.front');

                return $this->generateRedirectFromRoute(
                    'customer.login.view',
                    array(
                        'confirm_password' => '1',
                        'provider' => $providerName
                    )
                );

            // else user has no account, redirect to register with hybridauth
            } else {
                $this->getRequest()->getSession()->set('user_profile', $provider->getUserProfile());
                $this->setCurrentRouter('router.front');

                return $this->generateRedirectFromRoute(
                    'customer.login.view',
                    array(
                        'redirect_hybridauth_register' => '1',
                        'provider' => $providerName
                    )
                );
            }
        }
    }

    public function linkAction()
    {
        if (! $this->getSecurityContext()->hasCustomerUser()) {

            $confirmPasswordForm = new ConfirmPassword($this->getRequest());

            try {

                $form = $this->validateForm($confirmPasswordForm, "post");

                $mail = $this->getRequest()->getSession()->get('user_email');
                $token = $this->getRequest()->getSession()->get('user_token');
                $provider = $this->getRequest()->getSession()->get('provider');

                $customer = CustomerQuery::create()->filterByEmail($mail)->findOne();

                if ($customer !== null && $customer->checkPassword($form->get('password')->getData())) {
                    $this->processLogin($customer);

                    $ha = new HybridAuth();
                    $ha
                        ->setToken($token)
                        ->setProvider($provider)
                        ->setCustomerId($customer->getId())
                        ->save()
                    ;

                } else {
                    throw new WrongPasswordException();
                }

                return $this->generateSuccessRedirect($confirmPasswordForm);

            } catch (WrongPasswordException $e) {
                $message = $this->getTranslator()->trans(
                    "Wrong password. Please try again",
                    [],
                    TheliaHybridAuth::DOMAIN_NAME
                );
            } catch (\Exception $e) {
                $message = $this->getTranslator()->trans(
                    "Oops, something went wrong : %e  Your session may have passed away, try to log in again",
                    [
                        '%e' => $e->getMessage()
                    ],
                    TheliaHybridAuth::DOMAIN_NAME
                );
            }

            Tlog::getInstance()->error(
                sprintf(
                    "Error during customer confirm password process : %s. Exception was %s",
                    $message,
                    $e->getMessage()
                )
            );

            $confirmPasswordForm->setErrorMessage($message);

            $this->getParserContext()->addForm($confirmPasswordForm);

            if ($confirmPasswordForm->hasErrorUrl()) {
                return $this->generateErrorRedirect($confirmPasswordForm);
            }
        }
    }

    private function createEventInstance($data)
    {
        $customerCreateEvent = new CustomerCreateOrUpdateEvent(
            isset($data["title"])?$data["title"]:null,
            isset($data["firstname"])?$data["firstname"]:null,
            isset($data["lastname"])?$data["lastname"]:null,
            isset($data["address1"])?$data["address1"]:null,
            isset($data["address2"])?$data["address2"]:null,
            isset($data["address3"])?$data["address3"]:null,
            isset($data["phone"])?$data["phone"]:null,
            isset($data["cellphone"])?$data["cellphone"]:null,
            isset($data["zipcode"])?$data["zipcode"]:null,
            isset($data["city"])?$data["city"]:null,
            isset($data["country"])?$data["country"]:null,
            isset($data["email"])?$data["email"]:null,
            isset($data["password"]) ? $data["password"]:null,
            $this->getRequest()->getSession()->getLang()->getId(),
            isset($data["reseller"])?$data["reseller"]:null,
            isset($data["sponsor"])?$data["sponsor"]:null,
            isset($data["discount"])?$data["discount"]:null,
            isset($data["company"])?$data["company"]:null,
            null,
            isset($data["state"])?$data["state"]:null
        );

        return $customerCreateEvent;
    }
}