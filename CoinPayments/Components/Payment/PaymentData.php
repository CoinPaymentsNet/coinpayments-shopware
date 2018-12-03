<?php
/**
 * Created by PhpStorm.
 * User: Peter Vayda
 * Date: 26.11.18
 * Time: 16:20
 */

namespace CoinPayments\Components\Payment;

class PaymentData
{
    /**
     * @param array $response
     * @param string $token
     * @return bool
     */
    public function isValidToken($response, $token)
    {
        return hash_equals($token, $response->custom);
    }

    /**
     * @param float $amount
     * @param int $customerId
     * @return string
     */
    public function createPaymentToken($amount, $customerId)
    {
        return md5(implode('|', [$amount, $customerId]));
    }
}