<?php

class User
{
    private $userid, $username, $password, $apikey;

    public function __construct(int $uid, $uname = '', $pwd = '', $apikey = '')
    {
        $this->userid  = $uid;
        $this->username  = $uname;
        $this->password  = $pwd;
        $this->apikey  = $apikey;
    }

    public function get()
    {
        return array(
            'userid' => $this->userid,
            'username' => $this->username,
            'password' => $this->password,
            'apikey'  => $this->apikey
        );
    }
}
