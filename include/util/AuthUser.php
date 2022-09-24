<?php
require_once 'include/user.php';

class AuthUser {
    private $uid, $apiKey, $password, $udb;
    public $info;

    public function __construct($a = null, string $u = '', string $p = '') {
        $this->udb = new UserDB;
        $this->apiKey = $a;
        $this->uid = $u;
        $this->password = $p;
    }

    public function auth() {
        if ($this->apiKey != null) {
            return $this->authByApikey();
        }
        return $this->authByPassword();
    }

    private function authByApikey() {
        $result = false;

        if (($this->info = $this->udb->getUser(apikey: $this->apiKey)) != false) {
            $result = true;
        }

        return $result;
    }

    private function authByPassword() {
        $result = false;

        if (($this->info = $this->udb->getUser(uid: $this->uid))['password'] == $this->password) {
            $result = true;
        }

        return $result;
    }
}
