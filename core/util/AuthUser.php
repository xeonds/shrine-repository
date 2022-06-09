<?php
include_once 'core/logic/UserDB.php';

class AuthUser
{
    private $uid, $apiKey, $password;
    public $udb;

    public function __construct($a = null, $u = '', $p = '')
    {
        $this->udb = new UserDB;
        if ($a != null)
        {
            $this->apiKey = $a;
        }
        else
        {
            $this->uid = $u;
            $this->password = $p;
        }
    }

    public function auth()
    {
        if ($this->apiKey != null)
        {
            return $this->authByApikey();
        }
        return $this->authByPassword();
    }

    private function authByApikey()
    {
        $result = false;

        if ($this->udb->getUser(0, $this->apiKey) != false)
        {
            $result = true;
        }

        return $result;
    }

    private function authByPassword()
    {
        $result = false;

        if ($this->udb->getUser($this->uid, '') != false)
        {
            $result = true;
        }

        return $result;
    }
}
