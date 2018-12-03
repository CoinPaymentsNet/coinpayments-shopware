<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 30.11.18
 * Time: 16:22
 */
require_once __DIR__ . '/../../Components/coinpayments-php/init.php';

class Shopware_Controllers_Backend_Coinpayments extends Shopware_Controllers_Backend_ExtJs implements \Shopware\Components\CSRFWhitelistAware
{
    protected $model;

    public function preDispatch()
    {
        \CoinPayments\Api\Base::config($this->container->get('shopware.plugin.cached_config_reader')->getByPluginName('CoinPayments'));
        parent::preDispatch();
    }

    public function getRatesAction()
    {
        $mapped = \CoinPayments\Api\Rate::mapForAdmin(\CoinPayments\Api\Rate::getRates());

        $this->view->assign([
            'data' => $mapped,
            'total' => count($mapped),
        ]);
    }

    public function getWhitelistedCSRFActions()
    {
        return ['getRates'];
    }
}