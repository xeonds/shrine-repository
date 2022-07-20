<?php

/**
 * Response - quickly struct a json-format response
 * @method __construct($status, $data = null, $errCode = null, $isSuccess = true)
 * @param $status status code of response, like 200, 404, etc.
 * @param $data response data if isSuccess is true
 * @param $errCode error code if isSuccess is false
 * @param $isSuccess default is true
 */
class Response
{
    private $status, $isSuccess, $data;
    /**
     * Construct function. returns parsed json data.
     * @param $status status code of response, like 200, 404, etc.
     * @param $data response data, also as errCode
     * @param $isSuccess default is true
     */
    public function __construct(int $status, $data = null, bool $isSuccess = true)
    {
        $this->status = $status;
        $this->data = $data;
        $this->isSuccess = $isSuccess;
    }

    public function get()
    {
        return json_encode(array(
            "status" => $this->status,
            $this->isSuccess ? "data" : "errCode" => $this->data
        ));
    }
}
