<?php
class Config {
    private $json_path = 'config.json';
    private $default = array(
        'ui' => array(
            'title' => '',
            'nav' => '',
            'sub_title' => '',
            'footer' => '',
            'page' => array(
                'comment_board' => true,
                'tags' => true,
                'archive' => true
            ),
            'login_entrance' => true,
            'open_link_in_new_tab' => true
        ),
        'meta' => array(
            'comment_meta' => true,
            'file_detail' => true,
            'size_control' => array(
                'full' => '',
                'wide' => '',
                'medium' => '',
                'small' => ''
            )
        )
    );
    private $config;

    public function __construct() {
        if (is_file($this->json_path)) {
            $this->config = json_decode(file_get_contents($this->json_path), true);
        } else {
            file_put_contents($this->json_path, json_encode($this->default), LOCK_EX);
        }
    }

    public function get() {
        return $this->config;
    }

    public function save($param) {
        file_put_contents($this->json_path, json_encode($param), LOCK_EX);
    }
}
