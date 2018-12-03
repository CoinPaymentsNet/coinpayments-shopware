<?php
/**
 * Created by PhpStorm.
 * User: Peter Vayda
 * Date: 30.11.18
 * Time: 11:54
 */

namespace CoinPayments\Api;

class Base
{
    protected static $_publicKey = '';
    protected static $_privateKey = '';
    protected static $_mercahntId = '';
    protected static $_ipnSecret = '';
    protected static $_debug = false;
    protected static $_debugIpn = false;

    protected static $_apiUrl = 'https://www.coinpayments.net/api.php';

    public static function config($config)
    {
        if (isset($config['coinpaymentsPublicKey'])) {
            self::$_publicKey = $config['coinpaymentsPublicKey'];
        }
        if (isset($config['coinpaymentsSecretKey'])) {
            self::$_privateKey = $config['coinpaymentsSecretKey'];
        }
        if (isset($config['coinpaymentsMerchantId'])) {
            self::$_mercahntId = $config['coinpaymentsMerchantId'];
        }
        if (isset($config['coinpaymentsIpnSecret'])) {
            self::$_ipnSecret = $config['coinpaymentsIpnSecret'];
        }
        if (isset($config['coinpaymentsDebug'])) {
            self::$_debug = (bool)$config['coinpaymentsDebug'];
        }
        if (isset($config['coinpaymentsIpnDebug'])) {
            self::$_debug = (bool)$config['coinpaymentsIpnDebug'];
        }
    }

    public static function request($cmd, $params, $headers = array())
    {
        $defaultParams = array(
            'version' => 1,
            'cmd' => $cmd,
            'key' => self::$_publicKey
        );

        $defaultHeaders = array('Content-Type: application/x-www-form-urlencoded');

        $params = array_merge($defaultParams, $params);
        $headers = array_merge($defaultHeaders, $headers);
        $headers[] = 'HMAC: ' . self::generateHmac($params);
        $curl = curl_init();

        $options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => self::$_apiUrl,
            CURLOPT_POST => 1
        );

        curl_setopt_array($curl, $options);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);
        $decodedResponse   = json_decode($response, TRUE);
        $httpStatus        = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($decodedResponse['error'] == 'ok')
            return $decodedResponse;
        else
            \CoinPayments\Exception::throwException($httpStatus, $decodedResponse);
    }

    private static function generateHmac($params)
    {
        return hash_hmac('sha512', http_build_query($params), self::$_privateKey);

    }
}