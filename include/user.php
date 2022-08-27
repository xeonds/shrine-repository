<?php
include_once('include/util/AuthCode.php');

class User {
    private $userid, $username, $password, $apikey;

    public function __construct(int $uid, $uname = '', $pwd = '', $apikey = '') {
        $this->userid  = $uid;
        $this->username  = $uname;
        $this->password  = $pwd;
        $this->apikey  = $apikey;
    }

    public function get() {
        return array(
            'userid' => $this->userid,
            'username' => $this->username,
            'password' => $this->password,
            'apikey'  => $this->apikey
        );
    }
}

/**
 * UserDB: A util class for user management
 */

class UserDB {
    private $dbPath = 'user.json', $db;

    public function __construct() {
        if (!is_file($this->dbPath)) {
            file_put_contents($this->dbPath, '{}', LOCK_EX);
        }
        $this->db = json_decode(file_get_contents($this->dbPath), true);
    }

    public function createUser(string $uname, string $pwd): int {
        try {
            //promise unique username
            foreach ($this->db as $key => $user) {
                if ($uname == $user['username']) {
                    throw new Exception("User name already in use");
                    return false;
                }
            }
            //promise userid available
            for ($uid = 1; isset($this->db[$uid]); $uid++);
            $this->db[$uid] = (new User($uid, $uname, $pwd, AuthCode::gen_auth_code(2, 'md5')))->get();
            $this->saveDB();
            return $uid;
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteUser(int $uid): bool {
        try {
            if (isset($this->db['uid'])) {
                $this->db[$uid] = null;
                $this->saveDB();
            } else
                throw new Exception('Delete user failed: No such user');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getUser(mixed $uid = null, string $apikey = '') {
        if ($uid != null) {
            if (is_int($uid)) {
                return isset($this->db[$uid]) ? $this->db[$uid] : false;
            } else if (is_string($uid)) {
                foreach ($this->db as $user) {
                    if ($user['username'] == $uid) {
                        return $user;
                    }
                }
            }
        }
        foreach ($this->db as $user) {
            if ($user['apikey'] == $apikey) {
                return $user != null ? $user : false;
            }
        }
        return false;
    }

    public function modifyUser(int $uid, array $data): bool {
        try {
            if (isset($this->db[$uid])) {
                $user = new User($uid, $data['username'], $data['password']);
                $this->db[$uid] = $user->get();
                $this->saveDB();
            } else
                throw new Exception('Modify user failed: No such user.');
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function saveDB() {
        try {
            file_put_contents($this->dbPath, json_encode($this->db), LOCK_EX);
        } catch (Exception $e) {
            return false;
        }
    }
}
