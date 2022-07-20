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
    private $dbPath = 'db/userDB.json', $db;

    public function __construct() {
        $this->db = json_decode(file_get_contents($this->dbPath), true);
    }

    public function createUser(string $uname, string $pwd): int {
        try {
            for ($uid = 1; isset($this->db[$uid]); $uid++);
            $this->db[$uid] = (new User($uid, $uname, $pwd, (new AuthCode(2, 'md5'))->getCode()))->get();
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

    public function getUser(int $uid = 0, string $apikey = '') {
        if ($uid > 0) {
            return $this->db[$uid] != null ? $this->db[$uid] : false;
        } else {
            foreach ($this->db as $user) {
                if ($user['apikey'] == $apikey) {
                    return $user != null ? $user : false;
                }
            }
            return false;
        }
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
