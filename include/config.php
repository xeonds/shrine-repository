<?php
class Config {
    private $json_path = 'config.json';
    private $param = array(
        'site_name' => "博客名称",
        'sub_title' => "博客副标题",
        'site_footer' => "页脚名称",
        'all_category' => array("示例分类"),
        'admin_id' => 'admin',
        'admin_password' => "admin",
        'time_begin' => date('Y'),
        'enable_animation' => true,
        'enable_msg_board' => true,
        'enable_comment' => true,
        'enable_unsort' => true,
        'enable_login_entrance' => true,
        'open_link_in_new_tag' => true,
        'enable_file_detail' => true,
        'reverse_comment' => true,
        'reverse_msg_board' => true,
        'enable_tags' => true
    );

    public function init() {
        if (is_file($this->json_path) && file_get_contents($this->json_path) != '') {
            return 'Init failed:already inited.';
        } else {
            file_put_contents($this->json_path, json_encode($this->param, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }

    public function get($value = 'all') {
        if (is_file($this->json_path) && file_get_contents($this->json_path) != '') {
            $argv = func_get_args();
            if (count($argv) == 0) {
                return json_decode(file_get_contents($this->json_path), true);
            } else if (count($argv) == 1) {
                return (json_decode(file_get_contents($this->json_path), true))[$argv[0]];
            } else if (count($argv) >= 2) {
                $res = [];
                foreach ($argv as $val) {
                    $res[] = (json_decode(file_get_contents($this->json_path), true))[$val];
                }

                return $res;
            }
        } else {
            return 'Failed to get setting:not inited.';
        }
    }

    public function set($param) {
        if (!is_array($param["all_category"])) {
            $param["all_category"] = explode(",", $param["all_category"]);
        }
        if (is_file($this->json_path) && file_get_contents($this->json_path) != '') {
            file_put_contents($this->json_path, json_encode($param, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        } else {
            return 'Failed to save setting:not inited.';
        }
    }
}
