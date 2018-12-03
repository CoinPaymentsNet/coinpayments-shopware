<?php
/**
 * Created by PhpStorm.
 * User: Peter Vayda
 * Date: 30.11.18
 * Time: 11:54
 */

namespace CoinPayments\Api;

class APIError extends \Exception {}
# HTTP Status 400
class BadRequest extends APIError {}
# HTTP Status 404
class NotFound extends APIError {}
class PageNotFound extends NotFound{}
# HTTP Status 500, 504
class InternalServerError extends APIError {}
