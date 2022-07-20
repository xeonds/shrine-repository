<?php
class UI {
    var $data;
    var $page;
    var $sub_window;
    var $script;
    var $theme;

    public function __construct() {
        $setting = new Setting;
        $theme   = new Theme;
        $this->theme = $theme->parser_v2($setting->get("theme"));
        foreach ($this->theme['html'] as $key => $val)
            foreach ($val as $k => $v)
                $this->theme['html'][$key][$k] = $this->lang_tags_replace($v);
    }
    public function get() {
        switch ($this->page) {
            case 'custom':
                foreach ($this->data as $key => $val) {
                    $data = $val;
                }
                break;

            default:
                $data = $this->theme['html']['index'][0];
                $main = '';
                foreach ($this->data as $key => $val) {
                    if ($key != 'head')
                        $main .= $val;
                }
                $data = str_replace(
                    array('{head}', '{main}', '{sub_window}', '{script}'),
                    array($this->data['head'][0], $main, $this->sub_window($this->sub_window), $this->script($this->script)),
                    $data
                );
                break;
        }
        return $data;
    }
    public function set($param, $page, $sub_window, $script) {
        $this->script = $script;
        $this->sub_window = $sub_window;
        $this->page = $page;
        $this->data = [];
        foreach ($param as $key => $val) {
            $this->data[$key] = $this->$key($val);
        }
    }
    public function head($title = '') {
        $setting = new Setting;
        $css = $script = '';

        if ($this->theme['css'] != array())
            foreach ($this->theme['css'] as $key => $val)
                $css .= str_replace('{path}', $val, $this->theme['html']['head'][1]) . PHP_EOL;
        if ($this->theme['js'] != array())
            foreach ($this->theme['js'] as $key => $val)
                $script .= str_replace('{path}', $val, $this->theme['html']['head'][2]) . PHP_EOL;
        $kword = array('{title}', '{1}', '{2}');
        $param = array(
            $title == '' ? $setting->get("site_name") : $title . " | " . $setting->get("site_name"),
            $css,
            $script
        );

        return str_replace($kword, $param, $this->theme['html']['head']);
    }
    public function header($param) {
        $setting = new Setting;
        $admin = new AuthLogin;
        $page = $this->page;
        $enable_login_entrance = $setting->get("enable_login_entrance");
        switch ($page) {
            case '':
            case 'file':
            case 'search':
                $data = $this->theme['html']['header'][1];
                break;

            case 'article':
            case 'manage':
                $data = str_replace(array('{url}', '{text}'), array('index.php', '返回主页'), $this->theme['html']['header'][2]);
                break;

            case 'file_detail':
                $data = str_replace(array('{url}', '{text}'), array('index.php?page=file&path=', '返回主页'), $this->theme['html']['header'][2]);
                break;

            default:
                break;
        }
        switch ($page) {
            case '':
            case 'file':
            case 'msg_board':
            case 'tags':
                if ($admin->auth())
                    $data .= str_replace(array('{url}', '{text}'), array('index.php?page=manage&manage=analytics', '管理'), $this->theme['html']['header'][2]);
                else
                    if ($enable_login_entrance)
                    $data .= str_replace(array('{func}', '{text}'), array('toggle(\'login\')', '登录'), $this->theme['html']['header'][3]);
                break;

            case 'manage':
                $data .= str_replace(array('{url}', '{text}'), array('index.php?option=logout', '退出登录'), $this->theme['html']['header'][2]);
                switch ($param["manage"]) {
                    case 'analytics':
                    case 'article':
                    case 'custom':
                    case 'extra':
                        $data .= '';
                }
                break;

            case 'search':
                $data .= str_replace(array('{url}', '{text}'), array('index.php', '返回主页'), $this->theme['html']['header'][2]);
                break;

            case 'article':
                if ($admin->auth()) {
                    $data .= str_replace(array('{func}', '{text}'), array('toggle(\'editor\')', '编辑文章'), $this->theme['html']['header'][3]);
                }
                break;
        }
        $kword = array('{title_png}', '{sub_title}', '{4}');
        $argument = array(
            $this->theme['png']['title'],
            $setting->get('sub_title'),
            $data
        );

        return str_replace($kword, $argument, $this->theme['html']['header'][0]);
    }
    public function navigator($page) {
        $setting = new Setting();
        $all_category = $setting->get('all_category');
        $enable_unsort = $setting->get('enable_unsort');
        $enable_msg_board = $setting->get('enable_msg_board');
        $enable_tags = $setting->get('enable_tags');
        switch ($page) {
            case 'manage':
                $data = str_replace(array('{url}', '{text}'), array('index.php?page=manage&manage=analytics', '数据中心'), $this->theme['html']['navigator'][1]) .
                    str_replace(array('{url}', '{text}'), array('index.php?page=manage&manage=article', '文章管理'), $this->theme['html']['navigator'][1]) .
                    str_replace(array('{url}', '{text}'), array('index.php?page=manage&manage=custom', '自定义'), $this->theme['html']['navigator'][1]) .
                    str_replace(array('{url}', '{text}'), array('index.php?page=manage&manage=extra', '高级设置'), $this->theme['html']['navigator'][1]);
                break;

            case '':
            default:
                $data =  str_replace(array('{url}', '{text}'), array('index.php', '所有'), $this->theme['html']['navigator'][1]);
                $data .= $enable_unsort == true ? str_replace(array('{url}', '{text}'), array('index.php?page=file', '储物间'), $this->theme['html']['navigator'][1]) . PHP_EOL : '';
                $data .= $enable_msg_board == true ? str_replace(array('{url}', '{text}'), array('index.php?page=msg_board', '留言板'), $this->theme['html']['navigator'][1]) . PHP_EOL : '';
                $data .= $enable_tags == true ? str_replace(array('{url}', '{text}'), array('index.php?page=tags', '标签'), $this->theme['html']['navigator'][1]) . PHP_EOL : '';
                if (is_array($all_category)) {
                    foreach ($all_category as $val) {
                        $data .= str_replace(array('{url}', '{text}'), array("index.php?page=&class=$val", $val), $this->theme['html']['navigator'][1]) . PHP_EOL;
                    }
                }
                break;
        }
        return str_replace('{1}', $data, $this->theme['html']['navigator'][0]);
    }
    public function article_list($category) {
        $data = '';
        $count = 0;
        $item = File::article_arrange(File::get_file("article", "*.md", 0, 0));

        foreach ($item as $val) {
            $article = Text::article_parser($val["path"]);
            $title = $article["head"]["title"];
            $article_category = $article["head"]["class"];
            $desc = $article["head"]["desc"];
            $des_img = $article["head"]["desimg"];
            if ($category == "") {
                if (trim($des_img) != '') {
                    $data .= str_replace(array('{article_path}', '{article_title}', '{article_des_img}', '{article_desc}'), array(urlencode($val['path']), $title, $des_img, $desc), $this->theme['html']['article_list'][2]);
                } else {
                    $data .= str_replace(array('{article_path}', '{article_title}', '{article_desc}'), array(urlencode($val['path']), $title, $desc), $this->theme['html']['article_list'][1]);
                }
                $count++;
            } elseif (strstr($article_category, $category)) //bug fix
            {
                if (trim($des_img) != '') {
                    $data .= str_replace(array('{article_path}', '{article_title}', '{article_des_img}', '{article_desc}'), array(urlencode($val['path']), $title, $des_img, $desc), $this->theme['html']['article_list'][2]);
                } else {
                    $data .= str_replace(array('{article_path}', '{article_title}', '{article_desc}'), array(urlencode($val['path']), $title, $desc), $this->theme['html']['article_list'][1]);
                }
                $count++;
            }
        }
        if ($count == 0) {
            $data = '<h3>这里是空的（</h3>';
        } else {
            $data .= '<div style="text-align:center;">共计：' . $count . '篇</div>';
        }
        $kword = array('{category}', '{3}');
        $param = array($category, $data);

        return str_replace($kword, $param, $this->theme['html']['article_list'][0]);
    }
    public function article($path) {
        $setting = new Setting;
        $parser = new HyperDown\Parser;
        $parser->enableHtml(true);
        $parser->_commonWhiteList .= '|img|cite|embed|iframe|video|source';
        $parser->_specialWhiteList = array_merge($parser->_specialWhiteList, array(
            'ol'            =>  'ol|li',
            'ul'            =>  'ul|li',
            'blockquote'    =>  'blockquote',
            'pre'           =>  'pre|code'
        ));
        $article = Text::article_parser($path, HTML_ENTITIES);

        if ($setting->get("enable_comment")) {
            if ($setting->get("reverse_comment")) {
                $article["comment"] = array_reverse($article["comment"]);
            }
            $data_card = '';
            foreach ($article["comment"] as $val)
                $data_card .= str_replace(
                    array('{comment_id}', '{comment_time}', '{comment_context}'),
                    array($val["id"], $val["date"], $val["content"]),
                    $this->theme['html']['article'][1]
                );
            $data = str_replace('{1}', $data_card, $this->theme['html']['article'][2]);
        } else {
            $data = '';
        }
        $kword = array('{title}', '{author}', '{date}', '{content}', '{2}');
        $param = array(
            $article['head']['title'], $article["head"]["author"], $article["head"]["date"],
            $parser->makeHtml(Text::article_parser($path)["content"]),
            //using a new php parser lib
            //Parsedown::instance()->text(Text::article_parser($path)["content"]),
            $data
        );

        return str_replace($kword, $param, $this->theme['html']['article'][0]);
    }
    public function file($param) {
        $setting = new Setting;
        $path = $param['path'];
        $login = $param['login'];
        $data = [];

        $folder = File::get_folder("file/" . $path, 0, 0);
        if ($folder != array()) {
            for ($i = 0; $i < count($folder["name"]); $i++)
                $data['dir'] .=  str_replace(
                    array('{path}', '{name}'),
                    array($path . '/' . $folder['name'][$i], $folder["name"][$i]),
                    $this->theme['html']['file'][2]
                );
            $data['dir'] = str_replace(array('{2}'), array($data['dir']), $this->theme['html']['file'][3]);
        } else
            $data['dir'] .=  $this->theme['html']['file'][4];

        $item = File::get_file("file" . $path, "*.*", 0, 0);
        if ($item != array()) {
            for ($i = 0; $i < count($item); $i++)
                if (File::file_detail($item[$i]["path"])["extension"] == "link")
                    $data['file'] .=  str_replace(
                        array('{path}', '{name}', '{time}', '{size}', '{id}', '{7}'),
                        array(
                            explode("=", explode(">-", file_get_contents($item[$i]["path"]))[1])[1],
                            explode("=", explode(">-", file_get_contents($item[$i]["path"]))[2])[1],
                            date('Y-m-d', filectime($item[$i]["path"])),
                            explode("=", explode(">-", file_get_contents($item[$i]["path"]))[3])[1],
                            $i, $login ? str_replace(array('{id}', '{path}'), array($i, $item[$i]["path"]), $this->theme['html']['file'][7]) : ''
                        ),
                        $this->theme['html']['file'][8]
                    );
                else
                    if ($setting->get("enable_file_detail"))
                    $data['file'] .=  str_replace(
                        array('{path}', '{name}', '{time}', '{size}', '{id}', '{7}'),
                        array(
                            "index.php?option=file_detail&path=" . $item[$i]['path'],
                            $item[$i]["name"],
                            date('Y-m-d', filectime($item[$i]["path"])),
                            File::get_size($item[$i]["path"]),
                            $i, $login ? str_replace(array('{id}', '{path}'), array($i, $item[$i]["path"]), $this->theme['html']['file'][7]) : ''
                        ),
                        $this->theme['html']['file'][8]
                    );
                else
                    $data['file'] .=  str_replace(
                        array('{path}', '{name}', '{time}', '{size}', '{id}', '{7}'),
                        array(
                            "$item[$i]['path']\" download=\"",
                            $item[$i]["name"],
                            date('Y-m-d', filectime($item[$i]["path"])),
                            File::get_size($item[$i]["path"]),
                            $i, $login ? str_replace(array('{id}', '{path}'), array($i, $item[$i]["path"]), $this->theme['html']['file'][7]) : ''
                        ),
                        $this->theme['html']['file'][8]
                    );
            $data['file'] = str_replace(array('{6}', '{8}'), array($login ? $this->theme['html']['file'][6] : '', $data['file']), $this->theme['html']['file'][9]);
        } else
            $data['file'] .=  $this->theme['html']['file'][10];

        return str_replace(
            array('{1}', '{5}', '{11}'),
            array($login ? $this->theme['html']['file'][1] : '', $data['dir'], $data['file']),
            $this->theme['html']['file'][0]
        );
    }
    public function msg_board() {
        $setting = new Setting;
        $path = 'lib/Database/msg.json';
        $text = file_get_contents($path);
        $msg = json_decode($text);
        if ($setting->get("reverse_msg_board"))
            $msg = array_reverse($msg);

        if (count($msg) != 0) {
            $data = '';
            foreach ($msg as $key => $val) {
                $val = (array)$val;
                $data .= str_replace(
                    array('{id}', '{date}', '{content}'),
                    array($val["id"], $val["date"], $val["content"]),
                    $this->theme['html']['msg_board'][1]
                );
            }
        } else
            $data = $this->theme['html']['msg_board'][2];

        return str_replace('{3}', $data, $this->theme['html']['msg_board'][0]);
    }
    public function file_detail($detail) {
        $kword = array('{file_name}', '{dir_name}', '{file_type}', '{file_size}', '{filectime}', '{filemtime}', '{fileatime}', '{path}', '{3}');
        $param = array(
            $detail["basename"], $detail["dirname"], $detail["filetype"],
            $detail["filesize"], $detail["filectime"], $detail["filemtime"],
            $detail["fileatime"], $detail["path"],
            $detail['extension'] == 'pdf' ? str_replace('{path}', $detail['path'], $this->theme['html']['file_detail'][2]) : str_replace('{path}', $detail['path'], $this->theme['html']['file_detail'][1])
        );

        return str_replace($kword, $param, $this->theme['html']['file_detail'][0]);
    }
    public function search($param) {
        if ($param["article"]["str"] == array() && $param["file"]["str"] == array()) {
            $data = $this->theme['html']['search'][1];
        } else {
            $data = '';
            if ($param["article"]["str"] != array())
                for ($i = 0; $i < count($param["article"]["str"]); $i++)
                    $data .= str_replace(array('{path}', '{name}'), array('index.php?page=article&article=' . urlencode($param["article"]["path"][$i]), $param["article"]["str"][$i]), $this->theme['html']['search'][2]);
            if ($param["file"]["str"] != array()) {
                for ($i = 0; $i < count($param["file"]["str"]); $i++) {
                    if (fnmatch('*.lanzous', $param["file"]["str"][$i])) {
                        $data .= str_replace(array('{path}', '{name}'), array(explode("=", explode(">-", file_get_contents($param["file"]["path"][$i]))[1])[1], $param["file"]["str"][$i]), $this->theme['html']['search'][2]);
                    } else {
                        $data .= str_replace(array('{path}', '{name}'), array('index.php?option=file_detail&path=' . urlencode($param["file"]["path"][$i]), $param["file"]["str"][$i]), $this->theme['html']['search'][2]);
                    }
                }
            }
            $data = str_replace('{2}', $data, $this->theme['html']['search'][3]);
        }

        return str_replace('{4}', $data, $this->theme['html']['search'][0]);
    }
    public function setting($option) {
        $setting = new Setting;
        $theme = new Theme;
        if ($option == "analytics") {
            $item = File::get_file("article", "*.md", 0, 0);
            for ($x = 0, $text = ''; $x < count($item); $x++)
                if (is_array(Text::article_parser($item[$x]["path"])))
                    $text = $text . Text::article_parser($item[$x]["path"])["content"];
            for ($i = 0, $tag = []; $i < count($item); $i++)
                if (is_array(Text::article_parser($item[$i]["path"])))
                    $tag[] = trim(Text::article_parser($item[$i]["path"])["head"]["class"]);
            $tags = explode(",", str_replace("，", ",", str_replace("Array", "", implode(",", $tag))));
            $res = Text::analyze_tags($tags, array_flip(array_flip($tags)));
            for ($i = 0, $data = ''; $i < count($res); $i++)
                $data .= str_replace(array('{tag}', '{font_size}', '{count}'), array($res[$i]["tag"], sprintf("%0.1f", 6 * (0.75 * ($res[$i]["num"] / count($tags) - 0.5) + 0.5)), $res[$i]["num"]), $this->theme['html']['setting_analytics'][1]);
            $kword = array('{article_count}', '{word_count}', '{tag_count}', '{1}', '{analyze_table}');
            $argument = array(count($item), strlen($text), count(array_flip(array_flip($tags))), $data, '');
        } elseif ($option == "article") {
            /* released articles */
            $item = File::get_file("article", "*.md", 0, 0);
            $item = File::article_arrange($item);
            if (count($item) > 0) {
                for ($xi = 0, $data['released'] = ''; $xi < count($item); $xi++) {
                    $article = Text::article_parser($item[$xi]["path"], HTML_ENTITIES);
                    $article_size = File::get_size($item[$xi]["path"]);
                    if (is_array($article)) {
                        $data['released'] .= str_replace(
                            array('{article_path}', '{article_title}', '{article_size}', '{article_date}', '{count}', '{article_url}', '{article_text}'),
                            array($item[$xi]['path'], $article['head']['title'], $article_size, $article['head']['date'], $xi, urlencode($item[$xi]['path']), $article['text']),
                            $this->theme['html']['setting_article'][1]
                        );
                    } else {
                        $data['released'] .= '<tr><td colspan="4">' .
                            $article
                            . '</td></tr>';
                    }
                }
                $data['released'] = str_replace('{1}', $data['released'], $this->theme['html']['setting_article'][2]);
            } else
                $data['released'] = $this->theme['html']['setting_article'][3];
            /* unreleased articles */
            $item = File::get_file("article", "*.notavaliable", 0, 0);
            $item = File::article_arrange($item);
            $y = $xi;
            if (count($item) > 0) {
                for ($xi = 0, $data['unreleased'] = ''; $xi < count($item); $xi++) {
                    $article = Text::article_parser($item[$xi]["path"], HTML_ENTITIES);
                    $article_size = File::get_size($item[$xi]["path"]);
                    if (is_array($article)) {
                        $data['unreleased'] .= str_replace(
                            array('{article_path}', '{article_title}', '{article_size}', '{article_date}', '{count}', '{article_url}', '{article_text}'),
                            array($item[$xi]['path'], $article['head']['title'], $article_size, $article['head']['date'], $xi + $y, urlencode($item[$xi]['path']), $article['text']),
                            $this->theme['html']['setting_article'][1]
                        );
                    } else {
                        $data['unreleased'] .= '<tr><td colspan="4">' .
                            $article
                            . '</td></tr>';
                    }
                }
                $data['unreleased'] = str_replace('{1}', $data['unreleased'], $this->theme['html']['setting_article'][2]);
            } else
                $data['unreleased'] = $this->theme['html']['setting_article'][3];
            $kword = array('{4}', '{7}');
            $argument = array($data['released'], $data['unreleased']);
        } elseif ($option == "custom") {
            $bool_options = $setting->get('enable_animation', 'enable_msg_board', 'enable_comment', 'open_link_in_new_tag', 'enable_tags', 'enable_login_entrance', 'enable_unsort', 'reverse_msg_board', 'reverse_comment', 'enable_file_detail');
            foreach ($bool_options as $key => $val)
                $bool_options[$key] = $val == true ? 'checked="checked"' : '';
            $all_category = $setting->get("all_category") == array() ? '' : implode(",", $setting->get("all_category"));
            $themes = '';
            foreach ($theme->get_theme_list() as $item)
                $themes .= str_replace(array('{theme}', '{checked}'), array($item['name'], $setting->get('theme') == $item['name'] ? 'checked' : ''), $this->theme['html']['setting_custom'][1]);
            $kword = array('{site_name}', '{sub_title}', '{site_footer}', '{all_category}', '{admin_id}', '{admin_password}', '{time_begin}', '{enable_animation}', '{enable_msg_board}', '{enable_comment}', '{open_link_in_new_tag}', '{enable_tags}', '{enable_login_entrance}', '{enable_unsort}', '{reverse_msg_board}', '{reverse_comment}', '{enable_file_detail}', '{language}', '{theme}', '{1}');
            $argument = array_merge($setting->get('site_name', 'sub_title', 'site_footer'), array($all_category), $setting->get('admin_id', 'admin_password', 'time_begin'), $bool_options, $setting->get('language', 'theme'), array($themes));
        } elseif ($option == "extra") {
            $kword = array();
            $argument = array();
        }

        return str_replace($kword, $argument, $this->theme['html']["setting_$option"][0]);
    }
    public function login() {
        return $this->theme['html']['login'][0];
    }
    public function tags() {
        $data = '';
        for ($i = 0, $item = File::get_file("article", "*.md", 0, 0); $i < count($item); $i++)
            if (is_array(Text::article_parser($item[$i]["path"])))
                $tag[] = trim(Text::article_parser($item[$i]["path"])["head"]["class"]);
        if ($i > 0) {
            $all_tags = $tags = explode(",", str_replace("，", ",", str_replace("Array", "", implode(",", $tag))));
            $tags = array_flip($tags);
            $tags = array_flip($tags);
            $res = Text::analyze_tags($all_tags, $tags);
            for ($i = 0; $i < count($res); $i++) {
                $data .= str_replace(array('{tag}', '{size}', '{count}'), array($res[$i]['tag'], sprintf("%0.1f", 6 * (0.75 * ($res[$i]["num"] / count($all_tags) - 0.5) + 0.5)), $res[$i]['num']), $this->theme['html']['tags'][1]);
            }
        } else
            $data = '空';

        return str_replace('{1}', $data, $this->theme['html']['tags'][0]);
    }
    public function footer() {
        $setting = new Setting;
        $kword = array('{time_begin}', '{time_now}', '{site_footer}');
        $param = array($setting->get('time_begin'), date("Y"), $setting->get('site_footer'));
        return str_replace($kword, $param, $this->theme['html']['footer'][0]);
    }
    public function script($param) {
        $data = '<script>';
        foreach ($param["script"] as $key => $val) {
            switch ($val) {
                case 'toggle':
                    $data .= 'function toggle(id) {
                        if (document.getElementById(id).style.display == "") {
                            document.getElementById(id).style.display = "none";
                            document.getElementById("container").style.display = "";
                        } else {
                            document.getElementById(id).style.display = "";
                            document.getElementById("container").style.display = "none";
                        }
                    }';
                    break;

                case 'toggle_editor':
                    $data .= 'function toggle_editor(id) {
                        if (document.getElementById("editor-" + id).style.display == "")
                            document.getElementById("editor-" + id).style.display = "none";
                        else
                            document.getElementById("editor-" + id).style.display = "";
                    }';
                    break;

                case 'confirm':
                    $data .= 'function confirm(id) {
                        if (document.getElementById("confirm-" + id).style.display == "") {
                            document.getElementById("confirm-" + id).style.display = "none";
                            document.getElementById("yes-" + id).style.display = "";
                            document.getElementById("no-" + id).style.display = "";
                        } else {
                            document.getElementById("confirm-" + id).style.display = "";
                            document.getElementById("yes-" + id).style.display = "none";
                            document.getElementById("no-" + id).style.display = "none";
                        }
                    }';
                    break;

                case 'search':
                    $data .= 'function search(e) {
                        var e = e || window.event;
                        if (e.keyCode == 13) {
                            var val = document.getElementById("search").value;
                            window.location.href = "index.php?option=search&keyword=" + encodeURIComponent(val);
                        }
                    }';
                    break;
            }
        }
        $data .= '</script>';

        return $data;
    }
    public function sub_window($param) {
        $data = '';
        foreach ($param["window"] as $val) {
            switch ($val) {
                case 'comment':
                    $admin = new AuthLogin;
                    $setting = new Setting;
                    $id = $admin->auth() ? ' value="' . $setting->get("admin_id") . '"' : "";
                    $data .= str_replace(
                        array('{id}', '{path}'),
                        array($id, $param["path"]),
                        $this->theme['html']['sub_window'][1]
                    );
                    break;

                case 'confirm':
                    break;

                case 'search':
                    break;

                case 'login':
                    $data .= $this->theme['html']['sub_window'][4];
                    break;

                case 'new_msg':
                    $admin = new AuthLogin;
                    $setting = new Setting;
                    $id = $admin->auth() ? ' value="' . $setting->get("admin_id") . '"' : "";
                    $data .= str_replace(
                        array('{id}', '{path}'),
                        array($id, $param["path"]),
                        $this->theme['html']['sub_window'][5]
                    );
                    break;

                case 'upload':
                    $data .= str_replace('{path}', $param['path'], $this->theme['html']['sub_window'][6]);
                    break;

                case 'recover':
                    $data .= $this->theme['html']['sub_window'][7];
                    break;

                case 'editor':
                    switch ($param["editor_mode"]) {
                        case 'new':
                            $setting = new Setting;
                            $data .= str_replace('{admin_id}', $setting->get("admin_id"), $this->theme['html']['sub_window'][3]);
                            break;

                        case 'edit':
                            $article = Text::article_parser($param["path"], HTML_ENTITIES);
                            $data .= str_replace(
                                array(
                                    '{title}', '{class}', '{desc}', '{desimg}',
                                    '{author}', '{create_time}', '{path}', '{content}'
                                ),
                                array(
                                    $article["head"]["title"], $article["head"]["class"],
                                    $article["head"]["desc"], $article["head"]["desimg"],
                                    $article["head"]["author"], $article["head"]["date"],
                                    $param["path"], $article["content"]
                                ),
                                $this->theme['html']['sub_window'][2]
                            );
                            break;
                    }
                    break;

                default:
                    break;
            }
        }

        return $data;
    }
    public function lang_tags_replace($str) {
        $setting = new Setting;
        $path = 'lib/Database/language.json';
        $lang_data = json_decode(file_get_contents($path), true);
        $lang_kword = array_keys($lang_data[$setting->get("language")]);
        $lang_value = array_values($lang_data[$setting->get("language")]);

        return str_replace($lang_kword, $lang_value, $str);
    }
}
