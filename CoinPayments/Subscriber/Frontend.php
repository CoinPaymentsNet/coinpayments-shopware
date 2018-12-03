<?php

namespace CoinPayments\Subscriber;

use Enlight\Event\SubscriberInterface;

class Frontend implements SubscriberInterface
{
    protected $_pluginDir;

    protected $_templateManager;

    public function __construct($pluginDir, \Enlight_Template_Manager $templateManager)
    {
        $this->_pluginDir = $pluginDir;
        $this->_templateManager = $templateManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onFrontendPostDispatch',
            'Enlight_Controller_Action_PostDispatchSecure' => 'onPostDispatch',
        );
    }

    public function onPostDispatch()
    {
        $this->_templateManager->addTemplateDir(
            $this->_pluginDir . '/Resources/views/',
            'payment',
            \Enlight_Template_Manager::POSITION_APPEND
        );
    }

    public function onFrontendPostDispatch(\Enlight_Event_EventArgs $args)
    {
        /** @var $controller \Enlight_Controller_Action */
        $controller = $args->getSubject();
        $request = $controller->Request()->getParams();

        if ($request['coinpaymentsCurrency']) {
            /** @var \Enlight_Components_Session_Namespace $session */
            $session = $controller->get('session');
            $session->offsetSet('coinpaymentsCurrency', $request['coinpaymentsCurrency']);
        }
        $view = $controller->View();
    }

    public function storeCustomerCurrency(\Enlight_Event_EventArgs $args)
    {

    }

    public function onPaymentFallback(\Enlight_Event_EventArgs $args)
    {
        print_R($args);die();
    }
}
