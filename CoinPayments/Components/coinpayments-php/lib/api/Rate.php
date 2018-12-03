<?php
/**
 * Created by PhpStorm.
 * User: Peter Vayda
 * Date: 30.11.18
 * Time: 15:59
 */

namespace CoinPayments\Api;

class Rate extends Base
{
    private static $_ratesCmd = 'rates';

    public static function getRates($accepted = false)
    {
        return self::request(self::$_ratesCmd, ['accepted' => $accepted]);
    }

    public static function mapForAdmin($rates)
    {
        $data = [];
//        $data[] = ['name' => '--- Select currency ---', 'id' => ''];
        if ($rates['error'] == 'ok') {
            foreach ($rates['result'] as $key =>$rate) {
                $data[] = ['name' => $rate['name'], 'id' => $key];
            }
        } else {
            $data[] = ['name' => $rates['error'], 'id' => 'none'];
        }
        return $data;
    }

    public static function getByName($name)
    {
        $rates = self::getRates(false);

        foreach ($rates['result'] as $key => $rate) {
            if ($rate['name'] == $name) {
                return $key;
            }
        }
        \CoinPayments\Exception::throwException(500, 'Invalid token');
    }
    public static function getConverted($currency, $amount)
    {
        $rates = self::getRates()['result'];

        $rate = $currency != 'BTC' ?
            ($amount * $rates['USD']['rate_btc']) / $rates[$currency]['rate_btc'] :
            $amount * $rates['USD']['rate_btc'];
        return round($rate, 8);
    }
}