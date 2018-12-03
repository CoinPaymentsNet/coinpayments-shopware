<?php
/**
 * Created by PhpStorm.
 * User: Peter Vayda
 * Date: 30.11.18
 * Time: 11:51
 */

namespace CoinPayments;

class Exception
{
    public static function formatError($error)
    {
        if ($error != 'ok') {
            $reason = '';

            if (isset($error['error']))
                $reason = $error['error'];
            return $reason;
        }
        else {
            return $error;
        }
    }

    public static function throwException($http_status, $error)
    {
        $reason = is_array($error) && isset($error['reason']) ? $error['reason'] : '';

        switch ($http_status) {
            case 400:
                switch ($reason) {
                    default: throw new Api\BadRequest(self::formatError($error));
                }
            case 404:
                switch ($reason) {
                    case 'PageNotFound': throw new Api\PageNotFound(self::formatError($error));
                    default: throw new Api\NotFound(self::formatError($error));
                }
            case 500:
                throw new Api\InternalServerError(self::formatError($error));
            case 504:
                throw new Api\InternalServerError(self::formatError($error));
            default: throw new Api\APIError(self::formatError($error));
        }
    }
}

