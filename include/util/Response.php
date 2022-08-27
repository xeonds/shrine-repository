<?php

/**
 * Response - quickly struct a json-format response
 * @method __construct($code, $data = null, $errCode = null, $msg = true)
 * @param $code status code of response, like 200, 404, etc.
 * @param $data response data if msg is true
 * @param $errCode error code if msg is false
 * @param $msg default is true
 */
class Response {
    /**
     * Construct function. returns parsed json data.
     * @param $code status code of response, like 200, 404, etc.
     * @param $msg default is true
     * @param $data response data
     */
    public static function gen(int $code, string $msg, $data = null) {
        return json_encode(array(
            "code" => $code,
            "msg" => $msg,
            "data" => $data
        ));
    }
}
