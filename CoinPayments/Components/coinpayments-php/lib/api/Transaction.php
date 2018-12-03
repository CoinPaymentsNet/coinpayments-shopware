<?php
/**
 * Created by PhpStorm.
 * User: Peter Vayda
 * Date: 30.11.18
 * Time: 11:51
 */

namespace CoinPayments\Api;

class Transaction extends Base
{
    private static $_cmdCreate = 'create_transaction';
    private static $_cmdInfo = 'get_tx_info';

    public static function createTransaction($params)
    {
        return self::request(self::$_cmdCreate, $params);
    }

    public static function getTransaction($params, $full = false)
    {
        if ($full) {
            $params['full'] = 1;
        }
        return self::request(self::$_cmdInfo, $params);
    }

    public function generateData()
    {

    }
}