<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 26.11.18
 * Time: 10:56
 */

require_once __DIR__ . '/../../Components/coinpayments-php/init.php';

use Shopware\Models\Order\Status;

class Shopware_Controllers_Frontend_Coinpayments extends Shopware_Controllers_Frontend_Payment implements \Shopware\Components\CSRFWhitelistAware
{
    protected $_plugin;

    protected $_config;

    public function preDispatch()
    {
        $this->_config = $this->container->get('shopware.plugin.cached_config_reader')->getByPluginName('CoinPayments');
        \CoinPayments\Api\Base::config($this->_config);
        /** @var \Shopware\Components\Plugin $plugin */
        $plugin = $this->get('kernel')->getPlugins()['CoinPayments'];
        $this->get('template')->addTemplateDir($plugin->getPath() . '/Resources/views/');
    }

    public function indexAction()
    {
        return $this->redirect(['action' => 'direct', 'forceSecure' => true]);
    }

    /**
     * @throws Exception
     */
    public function directAction()
    {
        $this->_config = $this->container->get('shopware.plugin.cached_config_reader')->getByPluginName('CoinPayments');
        \CoinPayments\Api\Base::config($this->_config);
        $apiTransaction = \CoinPayments\Api\Transaction::createTransaction($this->getUrlParameters());

        $this->saveOrder(
            $apiTransaction['result']['status_url'],
            $apiTransaction['result']['txn_id'],
            Status::ORDER_STATE_OPEN
        );
        Shopware()->Db()->update(
            's_order',
            ['internalcomment' => json_encode($apiTransaction['result'])],
            'ordernumber = ' . $this->getOrderNumber());
        $this->redirect([
            'action' => 'status',
            'controller' => 'coinpayments',
            'module' => 'frontend'
        ]);
    }

    public function statusAction()
    {
        $orderN = $this->getOrderNumber();
        $sql = 'SELECT internalcomment FROM s_order WHERE ordernumber=?';
        $apiTransaction = json_decode(Shopware()->Db()->fetchOne($sql, [$orderN]), true);
        $this->View()->assign($apiTransaction);
    }
    /**
     * @return array
     * @throws Exception
     */
    private function getUrlParameters()
    {
        $service = $this->container->get('coin_payments.config_service');
        $router = $this->Front()->Router();
        $user = $this->getUser();
        $billing = $user['billingaddress'];
        $info = $user['additional']['user'];
        $basket = $this->getBasket();

        $items = '';
        $quantity = 0;
        foreach ($basket['content'] as $item) {
            $quantity += $item['quantity'];
            $items .= str_replace("\"", "`", $item['articlename']) . ': ' . $item['quantity'] . '; ';
        }

        //TODO add customer ability to change currency, admin too.
        $params = [
            'item_name' => rtrim($items, '; '),
            'item_number' => $quantity,
            'amount' => \CoinPayments\Api\Rate::getConverted($this->get('session')->offsetGet('coinpaymentsCurrency'), $this->getAmount()),
            'currency1' => $this->get('session')->offsetGet('coinpaymentsCurrency'),
            'currency2' =>  \CoinPayments\Api\Rate::getByName($this->_config['coinpaymentsPayout']),
            'buyer_email' => $info['email'],
            'address' => ' ',
            'custom' => $service->createPaymentToken($this->getAmount(), $billing['customernumber']),
            'ipn_url' => Shopware()->Config()->get('host') . '/coinpaymentsipn/handle',
            'buyer_name' => $billing['firstname'] . ' ' . $billing['lastname'],
            'invoice' => $service->createPaymentToken($this->getAmount(), $billing['customernumber'])
        ];
        return $params;
    }

    public function getRatesAction()
    {
        $mapped = \CoinPayments\Api\Rate::mapForAdmin(\CoinPayments\Api\Rate::getRates());

        echo json_encode($mapped); die();
    }

    public function getWhitelistedCSRFActions()
    {
        return [
            'getRates'
        ];
    }
}
