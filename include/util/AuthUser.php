<?php
include_once 'core/logic/UserDB.php';

class AuthUser {
    private $uid, $apiKey, $password;
    public $udb;

    public function __construct($a = null, $u = '', $p = '') {
        $this->udb = new UserDB;
        if ($a != null) {
            $this->apiKey = $a;
        } else {
            $this->uid = $u;
            $this->password = $p;
        }
    }

    public function auth() {
        if ($this->apiKey != null) {
            return $this->authByApikey();
        }
        return $this->authByPassword();
    }

    private function authByApikey() {
        $result = false;

        if ($this->udb->getUser(0, $this->apiKey) != false) {
            $result = true;
        }

        return $result;
    }

    private function authByPassword() {
        $result = false;

        if ($this->udb->getUser($this->uid, '') != false) {
            $result = true;
        }

        return $result;
    }
}

class AuthLogin {
    var $password;
    var $ok;
    var $salt = 'as5d64f65';
    var $domain = 'mxts.jiujiuer.xyz';

    public function auth() {
        $this->ok = false;

        if (!$this->check_session())
            $this->check_cookie();

        return $this->ok;
    }

    public function login($password) {
        if ($this->check(md5($password . $this->salt))) {
            $this->ok = true;
            $_SESSION['password'] = md5($password . $this->salt);
            setcookie("password", md5($password . $this->salt), time() + 60 * 60 * 24 * 30, "/", $this->domain);

            return true;
        } else {
            return false;
        }
    }

    public function logout() {
        $this->ok = false;

        $_SESSION['password'] = "";
        setcookie("password", "", time() - 3600, "/", $this->domain);
    }

    private function check_session() {
        if (!empty($_SESSION['password']))
            return $this->check($_SESSION['password']);
        else
            return false;
    }

    private function check_cookie() {
        if (!empty($_COOKIE['password']))
            return $this->check($_COOKIE['password']);
        else
            return false;
    }

    private function check($password_md5) {
        $setting = new Setting;
        $admin_password = md5($setting->get("admin_password") . $this->salt);

        if ($admin_password == $password_md5) {
            $this->ok = true;
            return true;
        } else {
            return false;
        }
    }
}
