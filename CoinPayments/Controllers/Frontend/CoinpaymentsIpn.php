<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 26.11.18
 * Time: 21:01
 */

use Shopware\Models\Order\Status;

class Shopware_Controllers_Frontend_CoinpaymentsIpn extends Enlight_Controller_Action implements \Shopware\Components\CSRFWhitelistAware
{
    protected $_config;

    public function preDispatch()
    {
        $this->_config = $this->container->get('shopware.plugin.cached_config_reader')->getByPluginName('CoinPayments');
    }

    public function handleAction()
    {
        $data = $this->Request()->getParams();
        $hmac = $this->Request()->getHeader('HMAC');

        if (!$hmac && empty($data)) {
            die('Wrong data');
        }

        if (!$this->checkHmac($data, $hmac)) {
            die('Wrong HMAC');
        }

        $sql = 'SELECT id FROM s_order WHERE temporaryID=?';
        $orderId = Shopware()->Db()->fetchOne($sql, [$data['txn_id']]);
        $orderData = Shopware()->Modules()->Order()->getOrderById($orderId);
        $order = Shopware()->Modules()->Order();
        if (empty($orderData)) {
            die('No Order Found');
        }

        $this->updatePayments($data, $order, $orderData);
        die('SUCCESS');
    }

    protected function updatePayments($data, sOrder $order, $orderData)
    {
        $orderStatus = $data['status'] == 100 ?
            Status::ORDER_STATE_COMPLETED :
            Status::ORDER_STATE_IN_PROCESS;
        $paymentStatus = $data['status'] == 100 ?
            Status::PAYMENT_STATE_COMPLETELY_PAID :
            Status::PAYMENT_STATE_OPEN;

        $order->setPaymentStatus($orderData['orderID'], $paymentStatus, true, $data['status_text']);
        $order->setOrderStatus($orderData['orderID'], $orderStatus, true, $data['status_text']);
    }

    private function checkHmac($data, $hmac)
    {
        if (!$hmac) {
            return false;
        }

        $serverHmac = hash_hmac(
            "sha512",
            http_build_query($data),
            trim($this->_config['coinpaymentsIpnSecret'])
        );
        if ($hmac != $serverHmac) {
            return false;
        }
        return true;
    }

    public function getWhitelistedCSRFActions()
    {
        return ['handle'];
    }
}