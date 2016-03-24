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

namespace TheliaHybridAuth\Hook;

use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Template\TemplateDefinition;
use Thelia\Core\Translation\Translator;
use TheliaHybridAuth\TheliaHybridAuth;

/**
 * Class FrontHook
 * @package TheliaHybridAuth\Hook
 * @author Tom Pradat <tpradat@openstudio.fr>
 */
class FrontHook extends BaseHook
{
    /**
     * @var Request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function onRegisterTop(HookRenderEvent $event)
    {
        // we don't display register buttons when the user in registering with a provider
        if (strcmp($this->request->get('_route'), 'hybridauth.register.get') !== 0
            && strcmp($this->request->get('_route'), 'hybridauth.register.post') !== 0) {
            $event->add($this->render('hybrid-auth-register-buttons.html'));
        }
    }

    public function onLoginFormTop(HookRenderEvent $event)
    {
        $event->add($this->render('hybrid-auth-login-buttons.html'));
    }

    public function onAccountAdditional(HookRenderBlockEvent $event)
    {
        $event->add(array(
            "type" => TemplateDefinition::FRONT_OFFICE,
            "id" => 'socials',
            "code" => 'account.additional',
            "title" => Translator::getInstance()->trans(
                'social networks associated',
                array(),
                TheliaHybridAuth::DOMAIN_NAME
            ),
            "content" => $this->render('providers-list-account.html')
        ));
    }

    public function onAccountAfterJavascriptInclude(HookRenderEvent $event)
    {
        $event->add($this->render('providers-list-account-js.html'));
    }

    public function onMainStylesheet(HookRenderEvent $event)
    {
        $event->add($this->addCSS('assets/css/style.css'));
    }

    public function onLoginMainBottom(HookRenderEvent $event)
    {
        $event->add($this->render('hybrid-auth-login-dialog.html'));
    }

    public function onLoginJavascriptInitialization(HookRenderEvent $event)
    {
        $event->add($this->render('hybrid-auth-login-dialog-js.html'));
    }
}