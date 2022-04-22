<?php
/* import libs */

include 'lib/Parser/Parser.php';
include 'lib/TCPDF/tcpdf.php';

/* Setting Process functions */
class Setting
{
    var $json_path = 'lib/Database/setting.json';

    public function init()
    {
        if (is_file($this->json_path) && file_get_contents($this->json_path) != '')
        {
            return 'Init failed:already inited.';
        }
        else
        {
            $param = array(
                'site_name' => "博客名称",
                'sub_title' => "博客副标题",
                'site_footer' => "页脚名称",
                'all_category' => array("示例分类"),
                'admin_id' => 'admin',
                'admin_password' => "admin",
                'time_begin' => 2020,
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
            file_put_contents($this->json_path, json_encode($param, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }

    public function get($value = 'all')
    {
        if (is_file($this->json_path) && file_get_contents($this->json_path) != '')
        {
            $argv = func_get_args();
            if (count($argv) == 0)
            {
                return json_decode(file_get_contents($this->json_path), true);
            }
            else if (count($argv) == 1)
            {
                return (json_decode(file_get_contents($this->json_path), true))[$argv[0]];
            }
            else if (count($argv) >= 2)
            {
                $res = [];
                foreach ($argv as $val)
                {
                    $res[] = (json_decode(file_get_contents($this->json_path), true))[$val];
                }

                return $res;
            }
        }
        else
        {
            return 'Failed to get setting:not inited.';
        }
    }

    public function set($param)
    {
        if (!is_array($param["all_category"]))
        {
            $param["all_category"] = explode(",", $param["all_category"]);
        }
        if (is_file($this->json_path) && file_get_contents($this->json_path) != '')
        {
            file_put_contents($this->json_path, json_encode($param, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        else
        {
            return 'Failed to save setting:not inited.';
        }
    }
}

class UI
{
    var $data;
    var $page;
    var $sub_window;
    var $script;
    var $theme;

    public function __construct()
    {
        $setting = new Setting;
        $theme   = new Theme;
        $this->theme = $theme->parser_v2($setting->get("theme"));
        foreach ($this->theme['html'] as $key => $val)
            foreach ($val as $k => $v)
                $this->theme['html'][$key][$k] = $this->lang_tags_replace($v);
    }
    public function get()
    {
        switch ($this->page)
        {
            case 'custom':
                foreach ($this->data as $key => $val)
                {
                    $data = $val;
                }
                break;

            default:
                $data = $this->theme['html']['index'][0];
                $main = '';
                foreach ($this->data as $key => $val)
                {
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
    public function set($param, $page, $sub_window, $script)
    {
        $this->script = $script;
        $this->sub_window = $sub_window;
        $this->page = $page;
        $this->data = [];
        foreach ($param as $key => $val)
        {
            $this->data[$key] = $this->$key($val);
        }
    }
    public function head($title = '')
    {
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
    public function header($param)
    {
        $setting = new Setting;
        $admin = new AuthLogin;
        $page = $this->page;
        $enable_login_entrance = $setting->get("enable_login_entrance");
        switch ($page)
        {
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
        switch ($page)
        {
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
                switch ($param["manage"])
                {
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
                if ($admin->auth())
                {
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
    public function navigator($page)
    {
        $setting = new Setting();
        $all_category = $setting->get('all_category');
        $enable_unsort = $setting->get('enable_unsort');
        $enable_msg_board = $setting->get('enable_msg_board');
        $enable_tags = $setting->get('enable_tags');
        switch ($page)
        {
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
                if (is_array($all_category))
                {
                    foreach ($all_category as $val)
                    {
                        $data .= str_replace(array('{url}', '{text}'), array("index.php?page=&class=$val", $val), $this->theme['html']['navigator'][1]) . PHP_EOL;
                    }
                }
                break;
        }
        return str_replace('{1}', $data, $this->theme['html']['navigator'][0]);
    }
    public function article_list($category)
    {
        $data = '';
        $count = 0;
        $item = File::article_arrange(File::get_file("article", "*.md", 0, 0));

        foreach ($item as $val)
        {
            $article = Text::article_parser($val["path"]);
            $title = $article["head"]["title"];
            $article_category = $article["head"]["class"];
            $desc = $article["head"]["desc"];
            $des_img = $article["head"]["desimg"];
            if ($category == "")
            {
                if (trim($des_img) != '')
                {
                    $data .= str_replace(array('{article_path}', '{article_title}', '{article_des_img}', '{article_desc}'), array(urlencode($val['path']), $title, $des_img, $desc), $this->theme['html']['article_list'][2]);
                }
                else
                {
                    $data .= str_replace(array('{article_path}', '{article_title}', '{article_desc}'), array(urlencode($val['path']), $title, $desc), $this->theme['html']['article_list'][1]);
                }
                $count++;
            }
            elseif (strstr($article_category, $category)) //bug fix
            {
                if (trim($des_img) != '')
                {
                    $data .= str_replace(array('{article_path}', '{article_title}', '{article_des_img}', '{article_desc}'), array(urlencode($val['path']), $title, $des_img, $desc), $this->theme['html']['article_list'][2]);
                }
                else
                {
                    $data .= str_replace(array('{article_path}', '{article_title}', '{article_desc}'), array(urlencode($val['path']), $title, $desc), $this->theme['html']['article_list'][1]);
                }
                $count++;
            }
        }
        if ($count == 0)
        {
            $data = '<h3>这里是空的（</h3>';
        }
        else
        {
            $data .= '<div style="text-align:center;">共计：' . $count . '篇</div>';
        }
        $kword = array('{category}', '{3}');
        $param = array($category, $data);

        return str_replace($kword, $param, $this->theme['html']['article_list'][0]);
    }
    public function article($path)
    {
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

        if ($setting->get("enable_comment"))
        {
            if ($setting->get("reverse_comment"))
            {
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
        }
        else
        {
            $data = '';
        }
        $kword = array('{title}', '{author}', '{date}', '{content}', '{2}');
        $param = array($article['head']['title'], $article["head"]["author"], $article["head"]["date"], 
                    $parser->makeHtml(Text::article_parser($path)["content"]),
                    //using a new php parser lib
                    //Parsedown::instance()->text(Text::article_parser($path)["content"]),
                    $data);

        return str_replace($kword, $param, $this->theme['html']['article'][0]);
    }
    public function file($param)
    {
        $setting = new Setting;
        $path = $param['path'];
        $login = $param['login'];
        $data = [];

        $folder = File::get_folder("file/" . $path, 0, 0);
        if ($folder != array())
        {
            for ($i = 0; $i < count($folder["name"]); $i++)
                $data['dir'] .=  str_replace(
                    array('{path}', '{name}'),
                    array($path . '/' . $folder['name'][$i], $folder["name"][$i]),
                    $this->theme['html']['file'][2]
                );
            $data['dir'] = str_replace(array('{2}'), array($data['dir']), $this->theme['html']['file'][3]);
        }
        else
            $data['dir'] .=  $this->theme['html']['file'][4];

        $item = File::get_file("file" . $path, "*.*", 0, 0);
        if ($item != array())
        {
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
        }
        else
            $data['file'] .=  $this->theme['html']['file'][10];

        return str_replace(
            array('{1}', '{5}', '{11}'),
            array($login ? $this->theme['html']['file'][1] : '', $data['dir'], $data['file']),
            $this->theme['html']['file'][0]
        );
    }
    public function msg_board()
    {
        $setting = new Setting;
        $path = 'lib/Database/msg.json';
        $text = file_get_contents($path);
        $msg = json_decode($text);
        if ($setting->get("reverse_msg_board"))
            $msg = array_reverse($msg);

        if (count($msg) != 0)
        {
            $data = '';
            foreach ($msg as $key => $val)
            {
                $val = (array)$val;
                $data .= str_replace(
                    array('{id}', '{date}', '{content}'),
                    array($val["id"], $val["date"], $val["content"]),
                    $this->theme['html']['msg_board'][1]
                );
            }
        }
        else
            $data = $this->theme['html']['msg_board'][2];

        return str_replace('{3}', $data, $this->theme['html']['msg_board'][0]);
    }
    public function file_detail($detail)
    {
        $kword = array('{file_name}', '{dir_name}', '{file_type}', '{file_size}', '{filectime}', '{filemtime}', '{fileatime}', '{path}', '{3}');
        $param = array(
            $detail["basename"], $detail["dirname"], $detail["filetype"],
            $detail["filesize"], $detail["filectime"], $detail["filemtime"],
            $detail["fileatime"], $detail["path"],
            $detail['extension'] == 'pdf' ? str_replace('{path}', $detail['path'], $this->theme['html']['file_detail'][2]) : str_replace('{path}', $detail['path'], $this->theme['html']['file_detail'][1])
        );

        return str_replace($kword, $param, $this->theme['html']['file_detail'][0]);
    }
    public function search($param)
    {
        if ($param["article"]["str"] == array() && $param["file"]["str"] == array())
        {
            $data = $this->theme['html']['search'][1];
        }
        else
        {
            $data = '';
            if ($param["article"]["str"] != array())
                for ($i = 0; $i < count($param["article"]["str"]); $i++)
                    $data .= str_replace(array('{path}', '{name}'), array('index.php?page=article&article=' . urlencode($param["article"]["path"][$i]), $param["article"]["str"][$i]), $this->theme['html']['search'][2]);
            if ($param["file"]["str"] != array())
            {
                for ($i = 0; $i < count($param["file"]["str"]); $i++)
                {
                    if (fnmatch('*.lanzous', $param["file"]["str"][$i]))
                    {
                        $data .= str_replace(array('{path}', '{name}'), array(explode("=", explode(">-", file_get_contents($param["file"]["path"][$i]))[1])[1], $param["file"]["str"][$i]), $this->theme['html']['search'][2]);
                    }
                    else
                    {
                        $data .= str_replace(array('{path}', '{name}'), array('index.php?option=file_detail&path=' . urlencode($param["file"]["path"][$i]), $param["file"]["str"][$i]), $this->theme['html']['search'][2]);
                    }
                }
            }
            $data = str_replace('{2}', $data, $this->theme['html']['search'][3]);
        }

        return str_replace('{4}', $data, $this->theme['html']['search'][0]);
    }
    public function setting($option)
    {
        $setting = new Setting;
        $theme = new Theme;
        if ($option == "analytics")
        {
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
        }
        elseif ($option == "article")
        {
            /* released articles */
            $item = File::get_file("article", "*.md", 0, 0);
            $item = File::article_arrange($item);
            if (count($item) > 0)
            {
                for ($xi = 0, $data['released'] = ''; $xi < count($item); $xi++)
                {
                    $article = Text::article_parser($item[$xi]["path"], HTML_ENTITIES);
                    $article_size = File::get_size($item[$xi]["path"]);
                    if (is_array($article))
                    {
                        $data['released'] .= str_replace(
                            array('{article_path}', '{article_title}', '{article_size}', '{article_date}', '{count}', '{article_url}', '{article_text}'),
                            array($item[$xi]['path'], $article['head']['title'], $article_size, $article['head']['date'], $xi, urlencode($item[$xi]['path']), $article['text']),
                            $this->theme['html']['setting_article'][1]
                        );
                    }
                    else
                    {
                        $data['released'] .= '<tr><td colspan="4">' .
                            $article
                            . '</td></tr>';
                    }
                }
                $data['released'] = str_replace('{1}', $data['released'], $this->theme['html']['setting_article'][2]);
            }
            else
                $data['released'] = $this->theme['html']['setting_article'][3];
            /* unreleased articles */
            $item = File::get_file("article", "*.notavaliable", 0, 0);
            $item = File::article_arrange($item);
            $y = $xi;
            if (count($item) > 0)
            {
                for ($xi = 0, $data['unreleased'] = ''; $xi < count($item); $xi++)
                {
                    $article = Text::article_parser($item[$xi]["path"], HTML_ENTITIES);
                    $article_size = File::get_size($item[$xi]["path"]);
                    if (is_array($article))
                    {
                        $data['unreleased'] .= str_replace(
                            array('{article_path}', '{article_title}', '{article_size}', '{article_date}', '{count}', '{article_url}', '{article_text}'),
                            array($item[$xi]['path'], $article['head']['title'], $article_size, $article['head']['date'], $xi + $y, urlencode($item[$xi]['path']), $article['text']),
                            $this->theme['html']['setting_article'][1]
                        );
                    }
                    else
                    {
                        $data['unreleased'] .= '<tr><td colspan="4">' .
                            $article
                            . '</td></tr>';
                    }
                }
                $data['unreleased'] = str_replace('{1}', $data['unreleased'], $this->theme['html']['setting_article'][2]);
            }
            else
                $data['unreleased'] = $this->theme['html']['setting_article'][3];
            $kword = array('{4}', '{7}');
            $argument = array($data['released'], $data['unreleased']);
        }
        elseif ($option == "custom")
        {
            $bool_options = $setting->get('enable_animation', 'enable_msg_board', 'enable_comment', 'open_link_in_new_tag', 'enable_tags', 'enable_login_entrance', 'enable_unsort', 'reverse_msg_board', 'reverse_comment', 'enable_file_detail');
            foreach ($bool_options as $key => $val)
                $bool_options[$key] = $val == true ? 'checked="checked"' : '';
            $all_category = $setting->get("all_category") == array() ? '' : implode(",", $setting->get("all_category"));
            $themes = '';
            foreach ($theme->get_theme_list() as $item)
                $themes .= str_replace(array('{theme}', '{checked}'), array($item['name'], $setting->get('theme') == $item['name'] ? 'checked' : ''), $this->theme['html']['setting_custom'][1]);
            $kword = array('{site_name}', '{sub_title}', '{site_footer}', '{all_category}', '{admin_id}', '{admin_password}', '{time_begin}', '{enable_animation}', '{enable_msg_board}', '{enable_comment}', '{open_link_in_new_tag}', '{enable_tags}', '{enable_login_entrance}', '{enable_unsort}', '{reverse_msg_board}', '{reverse_comment}', '{enable_file_detail}', '{language}', '{theme}', '{1}');
            $argument = array_merge($setting->get('site_name', 'sub_title', 'site_footer'), array($all_category), $setting->get('admin_id', 'admin_password', 'time_begin'), $bool_options, $setting->get('language', 'theme'), array($themes));
        }
        elseif ($option == "extra")
        {
            $kword = array();
            $argument = array();
        }

        return str_replace($kword, $argument, $this->theme['html']["setting_$option"][0]);
    }
    public function login()
    {
        return $this->theme['html']['login'][0];
    }
    public function tags()
    {
        $data = '';
        for ($i = 0, $item = File::get_file("article", "*.md", 0, 0); $i < count($item); $i++)
            if (is_array(Text::article_parser($item[$i]["path"])))
                $tag[] = trim(Text::article_parser($item[$i]["path"])["head"]["class"]);
        if ($i > 0)
        {
            $all_tags = $tags = explode(",", str_replace("，", ",", str_replace("Array", "", implode(",", $tag))));
            $tags = array_flip($tags);
            $tags = array_flip($tags);
            $res = Text::analyze_tags($all_tags, $tags);
            for ($i = 0; $i < count($res); $i++)
            {
                $data .= str_replace(array('{tag}', '{size}', '{count}'), array($res[$i]['tag'], sprintf("%0.1f", 6 * (0.75 * ($res[$i]["num"] / count($all_tags) - 0.5) + 0.5)), $res[$i]['num']), $this->theme['html']['tags'][1]);
            }
        }
        else
            $data = '空';

        return str_replace('{1}', $data, $this->theme['html']['tags'][0]);
    }
    public function footer()
    {
        $setting = new Setting;
        $kword = array('{time_begin}', '{time_now}', '{site_footer}');
        $param = array($setting->get('time_begin'), date("Y"), $setting->get('site_footer'));
        return str_replace($kword, $param, $this->theme['html']['footer'][0]);
    }
    public function script($param)
    {
        $data = '<script>';
        foreach ($param["script"] as $key => $val)
        {
            switch ($val)
            {
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
    public function sub_window($param)
    {
        $data = '';
        foreach ($param["window"] as $val)
        {
            switch ($val)
            {
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
                    switch ($param["editor_mode"])
                    {
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
    public function lang_tags_replace($str)
    {
        $setting = new Setting;
        $path = 'lib/Database/language.json';
        $lang_data = json_decode(file_get_contents($path), true);
        $lang_kword = array_keys($lang_data[$setting->get("language")]);
        $lang_value = array_values($lang_data[$setting->get("language")]);

        return str_replace($lang_kword, $lang_value, $str);
    }
}

class Theme
{
    public function get_theme_list()
    {
        $path = 'lib/Database/theme';
        $res = [];

        $folder = File::get_folder($path, 0, 0);
        foreach ($folder['path'] as $item)
        {
            $res[] = array('name' => json_decode(file_get_contents($item . '/theme.json'), true)['name'], 'path' => $item . '/');
        }

        return $res;
    }
    public function parser($path)
    {
        $data = json_decode(file_get_contents($path . 'theme.json'), true);
        foreach ($data['file'] as $val)
        {
            $file_arr = File::get_file($path, "*.$val", 0, 0);
            foreach ($file_arr as $v)
            {
                if (File::file_detail($path . $v['name'])['extension'] == 'html')
                    $data[$val][File::file_detail($path . $v['name'])['filename']] = file_get_contents($path . $v['name']);
                else
                    $data[$val][File::file_detail($path . $v['name'])['filename']] = $path . $v['name'];
            }
        }

        return $data;
    }
    public function parser_v2($name)
    {
        $path = 'lib/Database/theme/' . $name . '/';
        $data = json_decode(file_get_contents($path . 'theme.json'), true);
        foreach ($data['file'] as $val)
        {
            $file_arr = File::get_file($path, "*.$val", 0, 0);
            foreach ($file_arr as $v)
            {
                if (File::file_detail($path . $v['name'])['extension'] == 'html')
                    $data[$val][File::file_detail($path . $v['name'])['filename']] = $this->template_parser(file_get_contents($path . $v['name']));
                else
                    $data[$val][File::file_detail($path . $v['name'])['filename']] = $path . $v['name'];
            }
        }

        return $data;
    }
    private function template_parser($data)
    {
        $l = '<template>';
        $r = '</template>';
        $res[0] = $data;

        if (!strstr($data, $l))
            return $res;
        else
        {
            for ($i = 1; strstr($res[0], $l); $i++)
            {
                $res[$i] = ($arr = explode($l, strstr($res[0], $r, true)))[count($arr) - 1];
                $res[0] = str_replace($l . $res[$i] . $r, '{' . $i . '}', $res[0]);
            }

            return $res;
        }
    }
    public function install($package_path)
    {
        $path = 'lib/Database/theme/';

        $zip = new ZipArchive;
        $zip->open($package_path);
        $zip->extractTo($path);
        $zip->close();
    }
    public function uninstall($name)
    {
        $path = 'lib/Database/theme/';

        if ($this->parser($path . $name . '/')['removable'] == false)
        {
            echo 'Can`t remove.';
        }
        else
        {
            echo 'Removing...';
        }
    }
}
/* Text process functions */
class Text
{
    /**
     * @return (array)$result["head"] => {["title"],["class"],["desc"],["desimg"],["author"],["date"]}
     *                       ["content"]
     *                       ["comment"][$i]{["id"],["date"],["context"]}
     *                       ["text"]
     *                       [1][2][3]   
     */
    public static function article_parser($path, $flag = '')
    {
        $text = file_get_contents($path);
        $seperated_text = explode("--------", $text);
        $result = [];

        if (count($seperated_text) == 4)
        {
            if ($flag == '')
            {
                $head = $seperated_text[1];

                //article head parse
                $head_arr = explode(">-", $head);
                foreach ($head_arr as $key => $val)
                {
                    $val_arr = explode("=", $val);
                    $result["head"][$val_arr[0]] = substr($val, strlen($val_arr[0]) + 1);
                }
                $result["content"] = $seperated_text[2];
                //comment parse
                $comments_arr = explode(">--<", $seperated_text[3]);
                $result["comment"] = [];
                foreach ($comments_arr as $key => $val)
                {
                    $comment_arr = explode(">-", $val);
                    $tmp_arr = [];
                    foreach ($comment_arr as $_key => $_val)
                    {
                        $_val_arr = explode("=", $_val);
                        $tmp_arr[$_val_arr[0]] = $_val_arr[1];
                    }
                    $result["comment"][] = $tmp_arr;
                }
                $result["text"] = $text;
                unset($result["comment"][0]);
                $result[1] = $seperated_text[1];
                $result[2] = $seperated_text[2];
                $result[3] = $seperated_text[3];
                return $result;
            }
            else if ($flag == HTML_ENTITIES)
            {
                $head = $seperated_text[1];

                //article head parse
                $head_arr = explode(">-", $head);
                foreach ($head_arr as $key => $val)
                {
                    $val_arr = explode("=", $val);
                    $result["head"][$val_arr[0]] = htmlentities(substr($val, strlen($val_arr[0]) + 1));
                }
                $result["content"] = htmlentities($seperated_text[2]);
                //comment parse
                $comments_arr = explode(">--<", $seperated_text[3]);
                $result["comment"] = [];
                foreach ($comments_arr as $key => $val)
                {
                    $comment_arr = explode(">-", $val);
                    $tmp_arr = [];
                    foreach ($comment_arr as $_key => $_val)
                    {
                        $_val_arr = explode("=", $_val);
                        $tmp_arr[$_val_arr[0]] = htmlentities($_val_arr[1]);
                    }
                    $result["comment"][] = $tmp_arr;
                }
                $result["text"] = htmlentities($text);
                unset($result["comment"][0]);
                $result[1] = htmlentities($seperated_text[1]);
                $result[2] = htmlentities($seperated_text[2]);
                $result[3] = htmlentities($seperated_text[3]);
                return $result;
            }
        }
        else
        {       //fix article format
            return '错误：文章格式错误，请使用高级设置>文章修复工具进行修复。';
        }
    }

    public static function article_fixer($path)
    {
        //read content
        $text = file_get_contents($path);
        $seperated_text = explode("--------", $text);
        //article format detector: Detect whether an article is from hexo or in normal format.
        if (count($seperated_text) == 0)
        {          //file format is hexo markdown
            //converter
            $result = '--------' . PHP_EOL;
            $text_arr = explode("---", $text, 3);
            $head_arr = explode(PHP_EOL, $text_arr[1]);
            foreach ($head_arr as $val)
            {
                $val_arr = explode(": ", $val);
                if ($val_arr[0] == 'updated' || $val_arr[0] == 'categories')
                    continue;
                if ($val_arr[0] == 'tags')
                {
                    $result = $result . '>-class=' . $val_arr[1] . PHP_EOL;
                }
                else if ($val_arr[0] == 'photo')
                {
                    $result = $result . '>-desimg=' . $val_arr[1] . PHP_EOL;
                }
                else
                {
                    $result = $result . '>-' . $val_arr[0] . '=' . $val_arr[1] . PHP_EOL;
                }
            }
            $result = $result
                . '--------' . PHP_EOL
                .   $text_arr[2] . PHP_EOL
                . '--------';
            file_put_contents($path, $result);
        }
        else if (count($seperated_text) == 3)
        {       //missing seperaor between content and comment area
            file_put_contents(
                $path,
                '--------' .
                    $seperated_text[1] . '--------' .
                    $seperated_text[2] . '--------'
            );
        }
        else
        {                                        //struct is correct. All errors are in head
            if (!strstr($seperated_text[1], 'date'))    //if missing creating date
            {
                $result = '--------'
                    . $seperated_text[1]
                    . '>-date=' . date("Y.m.d H:i:s")
                    . '--------'
                    . $seperated_text[2]
                    . '--------'
                    . $seperated_text[3];
                file_put_contents($path, $result);
            }
            else if (stristr($seperated_text[3], '>-context'))
            {
                $result = '--------' . PHP_EOL
                    . $seperated_text[1]
                    . '--------' . PHP_EOL
                    . $seperated_text[2] . PHP_EOL
                    . '--------'
                    . str_replace(">-context=", ">-content=", $seperated_text[3]);
                file_put_contents($path, $result);
            }
            /* Fix file name error */
            rename($path, "article/" . md5(Text::article_parser($path)["text"]) . ".md");
        }
    }

    public static function article_structor($param)
    {
        $param_list = array('title', 'class', 'desc', 'desimg', 'author', 'date', 'path', 'content', 3);
        $article = Text::article_parser($param["path"]);
        //If parameter is not set, it means not edit it. So get it from origin article.
        foreach ($param_list as $val)
        {
            if (!isset($param[$val]))
            {
                $param[$val] = $article[$val];
            }
        }
        $data = '--------' . PHP_EOL .
            '>-title=' . $param["title"] . PHP_EOL .
            '>-class=' . $param["class"] . PHP_EOL .
            '>-desc=' . $param["desc"] . PHP_EOL .
            '>-desimg=' . $param["desimg"] . PHP_EOL .
            '>-author=' . $param["author"] . PHP_EOL .
            '>-date=' . $param["date"] . PHP_EOL .
            '--------' . PHP_EOL .
            $param["content"] .
            '--------' . PHP_EOL .
            $param[3];

        return $data;
    }

    public static function search($text)
    {
        $article = File::get_file("article", "*.md", 0, 0);
        $file = File::get_file("file", "*.*", 0, 3);
        $result = [];

        //search keyword in articles
        for ($i = 0; $i < count($article); $i++)
        {
            if (stristr(file_get_contents($article[$i]["path"]), $text))
            {
                $result["article"]["str"][] = Text::article_parser($article[$i]["path"])["head"]["title"];
                $result["article"]["path"][] = $article[$i]["path"];
            }
        }
        //search file names
        for ($i = 0; $i < count($file); $i++)
        {
            if (stristr($file[$i]["name"], $text))
            {
                $result["file"]["str"][] = $file[$i]["name"];
                $result["file"]["path"][] = $file[$i]["path"];
            }
        }

        return $result;
    }

    public static function analyze_tags($str, $tags)
    {
        $str = implode("", $str);
        $result = [];
        foreach ($tags as $key => $val)
        {
            $tag_list[] = $val;
        }
        if ($str != '')
        {
            for ($i = 0; $i < count($tag_list); $i++)
            {
                $result[] = array("num" => substr_count($str, $tag_list[$i]), "tag" => $tag_list[$i]);
            }
        }
        else
        {
            $result[] = array("num" => 0, "tag" => "所有文章");
        }

        return $result;
    }

    public static function desense_char($str)
    {
        return str_replace("/", "[slash]", $str);
    }

    public static function date_to_int($str)
    {
        if (strstr($str, "-"))
        {
            $arr = explode("-", $str);
            $arr2 = explode(" ", $arr[2]);
            $arr3 = explode(":", $arr2[1]);

            $res = $arr[0] . $arr[1] . $arr2[0] . $arr3[0] . $arr3[1] . $arr3[2];
        }
        else
        {
            $arr = explode(".", $str);
            $arr2 = explode(" ", $arr[2]);
            $arr3 = explode(":", $arr2[1]);

            $res = $arr[0] . $arr[1] . $arr2[0] . $arr3[0] . $arr3[1] . $arr3[2];
        }

        return $res;
    }
}

/* File Process functions */
class File
{
    public static function delete($path, $tip, $headto)
    {
        unlink($path);
        echo '<script>alert("' . $tip . '")</script>';
        header("refresh:0;url='" . $headto . "'");
    }

    public static function upload($path, $tip, $headto)
    {
        if ($_FILES["file"]["size"] != 0)
        {
            if ($_FILES["file"]["error"] <= 0)
            {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $_FILES["file"]["name"]);
                echo "<script>alert('" . $tip . "[path:" . $path . "]')</script>";
                header("refresh:0;url='" . $headto . "'");
            }
            else
            {
                echo "<script>alert('上传失败，错误代码：" . $_FILES["file"]["error"] . "')</script>";
                header("refresh:0;url='" . $headto . "'");
            }
        }
    }

    public static function write_file($path, $data, $tip, $headto,  $mode = 'overwrite')
    {
        if (file_exists($path))
        {
            $mode == 'overwrite' ? file_put_contents($path,  $data) : file_put_contents($path, $data, FILE_APPEND);
        }
        else
        {
            $handle = fopen($path, "w");   //in case of special characters like / 
            fwrite($handle, $data);
            fclose($handle);
        }
        echo "<script>alert('" . $tip . "')</script>";
        header("refresh:0;url='" . $headto . "'");
    }

    public static function file_detail($path)
    {
        $file_detail = [];
        $path_parts = pathinfo($path);

        $file_detail["path"]        = $path;
        $file_detail["dirname"]     = $path_parts['dirname'];                       //目录名称
        $file_detail["basename"]    = $path_parts['basename'];                      //文件全名
        $file_detail["extension"]   = $path_parts['extension'];                     //文件后缀
        $file_detail["filename"]    = $path_parts['filename'];                      //文件名称
        $file_detail["filetype"]    = filetype($path);                              //文件类型
        $file_detail["filesize"]    = File::get_size($path);                     //文件大小
        $file_detail["filectime"]   = date('Y年m月d日 H:i:s', filectime($path));    //文件创建时间
        $file_detail["filemtime"]   = date("Y年m月d日 H:i:s", filemtime($path));    //文件修改时间
        $file_detail["fileatime"]   = date("Y年m月d日 H:i:s", fileatime($path));    //最后访问时间
        // $file_detail["fileread"]    = var_dump(is_readable($path));                 //可读
        // $file_detail["filewrite"]   = var_dump(is_writeable($path));                //可写
        // $file_detail["fileexecute"] = var_dump(is_executable($path));               //可执行

        return $file_detail;
    }

    public static function get_size($path)
    {
        $filesize = filesize(trim($path));
        if ($filesize >= 1073741824)
        {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
        }
        elseif ($filesize >= 1048576)
        {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
        }
        elseif ($filesize >= 1024)
        {
            $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
        }
        else
        {
            $filesize = $filesize . ' Byte';
        }

        return $filesize;
    }

    public static function get_file($path, $filetype, $min_depth, $max_depth)
    {
        if (!file_exists($path))
        {
            return [];
        }
        $file = [];
        if ($dir = opendir($path))
        {                         //check whether the dir is correctly opened
            while (false !== $item = readdir($dir))
            {        //search each item and check whether it's a file or a directory
                $nextPath = $path . '/' . $item;
                if ($item != '.' && $item != '..' && is_dir($nextPath) && $max_depth > 0)
                {
                    $file = array_merge($file, File::get_file($nextPath, $filetype, $min_depth - 1, $max_depth - 1));
                }
                elseif ($item != '.' && $item != '..' && fnmatch($filetype, $item) && $min_depth <= 0)
                {
                    $file[] = array("name" => $item, "path" => $nextPath, "time" => filectime($nextPath));
                }
            }
        }

        foreach ($file as $key => $val)   //order by name
        {
            $tmp[$key] = $val['name'];
        }
        if (is_array($tmp))             //in case of error:Argument #1 is expected to be an array or a sort flag 
        {
            array_multisort($tmp, SORT_ASC, $file);
        }

        return $file;
    }

    public static function get_folder($path, $min_depth, $max_depth)
    {
        if (!file_exists($path))
        {
            return [];
        }
        $folder = [];
        if ($dir = opendir($path))
        {                        //check whether the dir is correctly opened
            while (false !== $item = readdir($dir))
            {        //search each item and check whether it's a file or a directory
                $nextPath = $path . '/' . $item;
                if ($item != '.' && $item != '..' && is_dir($nextPath) && $max_depth > 0)
                {
                    $folder = array_merge($folder, File::get_folder($nextPath, $min_depth - 1, $max_depth - 1));
                }
                elseif ($item != '.' && $item != '..' && is_dir($nextPath) && $min_depth <= 0)
                {
                    $folder["path"][] = $nextPath;
                    $folder["name"][] = $item;
                }
            }
        }
        return $folder;
    }

    public static function backup($backup_type)
    {
        $zip = new ZipArchive;

        switch ($backup_type)
        {
            case 'article':
                $fname = 'article-archive-' . date('Y-m-d-H-i-s') . '.zip';
                if ($zip->open($fname, ZipArchive::CREATE) === true)
                {
                    $res = File::get_file('article', '*.*', 0, 0);
                    for ($i = 0; $i < count($res); $i++)
                    {
                        $zip->addFile($res[$i]['path']);
                    }
                    $zip->close();
                    return $fname;
                }
                break;

            case 'setting':
                $fname = 'setting-archive-' . date('Y-m-d-H-i-s') . '.zip';
                if ($zip->open($fname, ZipArchive::CREATE) === true)
                {
                    $zip->addFile('lib/Database/setting.json');
                    $zip->close();
                    return $fname;
                }
                break;

            case 'file':
                $fname = 'file-archive-' . date('Y-m-d-H-i-s') . '.zip';
                if ($zip->open($fname, ZipArchive::CREATE) === true)
                {
                    $res = File::get_file('file', '*.*', 0, 3);
                    for ($i = 0; $i < count($res); $i++)
                    {
                        $zip->addFile($res[$i]['path']);
                    }
                    $zip->close();
                    return $fname;
                }
                break;

            case 'msg':
                $fname = 'msg-archive-' . date('Y-m-d-H-i-s') . '.zip';
                if ($zip->open($fname, ZipArchive::CREATE) === true)
                {
                    $zip->addFile('lib/Database/msg.json');
                    $zip->close();
                    return $fname;
                }
                break;
        }
    }

    public static function switch_md($path)
    {
        $res = File::file_detail($path);
        if ($res["extension"] == 'md')
        {
            rename($path, 'article/' . $res["filename"] . '.notavaliable');
        }
        else
        {
            rename($path, 'article/' . $res["filename"] . '.md');
        }
    }

    public static function article_arrange($item)
    {
        foreach ($item as $key => $val)
        {
            $tmp[$key] = Text::article_parser($val['path'])["head"]["title"];
        }
        if (is_array($tmp))
        {
            array_multisort($tmp, SORT_DESC, $item);
        }
        foreach ($item as $key => $val)
        {
            $tmp[$key] = Text::date_to_int(Text::article_parser($val['path'])["head"]["date"]);
        }
        if (is_array($tmp))
        {
            array_multisort($tmp, SORT_DESC, $item);
        }

        return $item;
    }
}

/* Parameter Process functions */
class Param
{
    private $param = [];

    public function page($page = '')
    {
        switch ($page)
        {
            case '':
                $this->param["class"] = $_GET["class"];
                break;

            case 'article':
                $this->param["path"] = $_GET["article"];
                break;

            case 'file':
                $this->param["path"] = $_GET["path"];
                break;

            case 'manage':
                $this->param["manage"] = $_GET["manage"];
                break;

            case 'search':
                $this->param["result"] = $_SESSION["search-result"];
                break;

            case 'msg_board':
                break;

            case 'file_detail':
                $this->param["result"] = $_SESSION["file-detail"];
                break;

            case 'login':
                break;

            case 'tags':
                break;

            default:
                break;
        }

        return $this->param;
    }

    public function option($option)
    {
        switch ($option)
        {
            case 'upload_file':
                $this->param["path"] = $_POST["upload-path"];
                break;

            case 'upload_link':
                $this->param["path"] = $_POST["upload-path"];
                $this->param["link"] = $_POST["link-path"];
                $this->param["name"] = $_POST["link-name"];
                $this->param["size"] = $_POST["link-size"];
                break;

            case 'delete_article':
                $this->param["path"] = $_GET["article"];
                break;
            case 'delete_file':
                $this->param["path"] = $_GET["path"];
                break;

            case 'edit_article':
                $this->param["title"] = $_POST["title"];
                $this->param["class"] = $_POST["class"];
                $this->param["desc"] = $_POST["desc"];
                $this->param["desimg"] = $_POST["desimg"];
                $this->param["author"] = $_POST["author"];
                $this->param["date"] = $_POST["date"];
                $this->param["path"] = $_POST["path"];
                $this->param["content"] = $_POST["content"];
                $this->param["text"] = $_POST["text"];
                break;

            case 'new_article':
                $this->param["title"] = $_POST["article-title"];
                $this->param["class"] = $_POST["article-class"];
                $this->param["desc"] = $_POST["article-desc"];
                $this->param["desimg"] = $_POST["article-desimg"];
                $this->param["author"] = $_POST["article-author"];
                $this->param["date"] = date("Y.m.d H:i:s");
                $this->param["content"] = $_POST["article-content"];
                break;

            case 'backup':
                $this->param["type"] = $_GET["backup"];
                break;

            case 'login':
                $this->param["password"] = $_POST["password"];
                break;

            case 'comment_article':
                $this->param["id"] = $_POST["comment-id"];
                $this->param["content"] = $_POST["comment-content"];
                $this->param["date"] = date("Y.m.d H:i:s");
                $this->param["path"] = $_POST["comment-article"];
                break;

            case 'search':
                $this->param["keyword"] = $_GET["keyword"];
                break;

            case 'file_detail':
                $this->param["path"] = $_GET["path"];
                break;

            case 'new_msg':
                $this->param["id"] = $_POST["comment-id"];
                $this->param["content"] = $_POST["comment-content"];
                $this->param["date"] = date("Y.m.d H:i:s");
                break;

            case 'edit_setting':
                $this->param = $_POST;
                if ($_POST["all_category"] == '')
                    $this->param["all_category"] = array();
                break;

            case 'switch_article_stat':
                $this->param["path"] = $_GET["path"];
                break;

            default:
                break;
        }

        return $this->param;
    }
}

/* Login Process functions */
class AuthLogin
{
    var $password;
    var $ok;
    var $salt = 'as5d64f65';
    var $domain = 'mxts.jiujiuer.xyz';

    public function auth()
    {
        $this->ok = false;

        if (!$this->check_session())
            $this->check_cookie();

        return $this->ok;
    }

    public function login($password)
    {
        if ($this->check(md5($password . $this->salt)))
        {
            $this->ok = true;
            $_SESSION['password'] = md5($password . $this->salt);
            setcookie("password", md5($password . $this->salt), time() + 60 * 60 * 24 * 30, "/", $this->domain);

            return true;
        }
        else
        {
            return false;
        }
    }

    public function logout()
    {
        $this->ok = false;

        $_SESSION['password'] = "";
        setcookie("password", "", time() - 3600, "/", $this->domain);
    }

    private function check_session()
    {
        if (!empty($_SESSION['password']))
            return $this->check($_SESSION['password']);
        else
            return false;
    }

    private function check_cookie()
    {
        if (!empty($_COOKIE['password']))
            return $this->check($_COOKIE['password']);
        else
            return false;
    }

    private function check($password_md5)
    {
        $setting = new Setting;
        $admin_password = md5($setting->get("admin_password") . $this->salt);

        if ($admin_password == $password_md5)
        {
            $this->ok = true;
            return true;
        }
        else
        {
            return false;
        }
    }
}

class Pdf
{
    # 常量设置
    const APP_NAME       = '';
    const PDF_LOGO       = 'logo.png';                  // LOGO路径 该路径以tcpdf.php为参照
    const PDF_LOGO_WIDTH = '10';                        // LOGO宽度
    const PDF_TITLE      = '夢想天生';
    const PDF_HEAD       = '测试中';
    const PDF_FONT       = 'stsongstdlight';
    const PDF_FONT_STYLE = '';
    const PDF_FONT_SIZE  = 10;
    const PDF_FONT_MONOSPACED = 'courier';
    const PDF_IMAGE_SCALE = '1.25';


    # tcpdf对象存储
    protected $pdf = null;

    /**
     * 构造函数 引入插件并实例化
     */
    public function __construct()
    {
        # 实例化该插件
        $this->pdf = new TCPDF();
    }

    /**
     * 设置文档信息    
     * @param  $user        string  文档作者
     * @param  $title       string  文档标题
     * @param  $subject     string  文档主题
     * @param  $keywords    string  文档关键字
     * @return null
     */
    protected function setDocumentInfo($user = '', $title = '', $subject = '', $keywords = '')
    {
        if (empty($user) || empty($title)) return false;
        # 文档创建者名称
        $this->pdf->SetCreator(self::APP_NAME);
        # 作者
        $this->pdf->SetAuthor($user);
        # 文档标题
        $this->pdf->SetTitle($title);
        # 文档主题
        if (!empty($subject)) $this->pdf->SetSubject($subject);
        # 文档关键字
        if (!empty($keywords)) $this->pdf->SetKeywords($keywords);
    }

    /**
     * 设置文档的页眉页脚信息
     * @param  null
     * @return null
     */
    protected function setHeaderFooter()
    {
        # 设置页眉信息 
        # 格式 logo地址 logo宽度 页眉标题 页眉说明文字 页眉字体颜色 页眉下划线颜色
        $this->pdf->SetHeaderData(self::PDF_LOGO, self::PDF_LOGO_WIDTH, self::PDF_TITLE, self::PDF_HEAD, array(35, 35, 35), array(221, 221, 221));
        # 设置页脚信息
        # 格式 页脚字体颜色 页脚下划线颜色
        $this->pdf->setFooterData(array(35, 35, 35), array(221, 221, 221));

        # 设置页眉页脚字体
        $this->pdf->setHeaderFont(array('stsongstdlight', self::PDF_FONT_STYLE, self::PDF_FONT_SIZE));
        $this->pdf->setFooterFont(array('helvetica', self::PDF_FONT_STYLE, self::PDF_FONT_SIZE));
    }

    /**
     * 关闭页眉页脚
     * @param  null
     * @return null
     */
    protected function closeHeaderFooter()
    {
        # 关闭页头
        $this->pdf->setPrintHeader(false);
        # 关闭页脚
        $this->pdf->setPrintFooter(false);
    }

    /**
     * 设置间距 包括正文间距 页眉页脚间距
     * @param  null
     * @return null
     */
    protected function setMargin()
    {
        # 设置默认的等宽字体
        $this->pdf->SetDefaultMonospacedFont('courier');
        # 正文左侧 上侧 右侧间距
        $this->pdf->SetMargins(15, 25, 15);
        # 页眉间距
        $this->pdf->SetHeaderMargin(5);
        # 页脚间距
        $this->pdf->SetFooterMargin(10);
    }

    /**
     * 正文设置 包括 分页 图片比例 正文字体
     * @param  null
     * @return null  
     */
    protected function setMainBody()
    {

        # 开启分页 true开启 false关闭 开启分页时参数2起作用 表示正文距底部的间距
        $this->pdf->SetAutoPageBreak(true, 25);
        # 设置图片比例
        $this->pdf->setImageScale(self::PDF_IMAGE_SCALE);
        #
        $this->pdf->setFontSubsetting(true);
        # 设置正文字体 stsongstdlight是Adobe Reader默认字体
        $this->pdf->SetFont('stsongstdlight', '', 14);
        # 添加页面 该方法如果前面已有页面 会在将页脚添加到页面中 并自动添加下一页 否则添加新一页
        $this->pdf->AddPage();
    }

    /**
     * 生成pdf
     * @param  $info    array   
     *   array(
     *          'user'=>'文档作者' , 
     *          'title'=>'文档标题' , 
     *          'subject'=>'文档主题' , 
     *          'keywords'=>'文档关键字' , 
     *          'content'=>'文档正文内容' , 
     *          'HT'=>'是否开启页眉页脚' , 
     *          'path'=>'文档保存路径');
     * @return null  
     */
    public function createPDF($info = array())
    {
        if (empty($info) || !is_array($info)) return false;

        $this->setDocumentInfo($info['user'], $info['title'], $info['subject'], $info['keywords']);
        if (!$info['HT'])
        {

            $this->closeHeaderFooter();
        }
        else
        {
            $this->setHeaderFooter();
        }

        $this->setMargin();
        $this->setMainBody();

        # 写入内容
        $this->pdf->writeHTML($info['content'], true, false, true, false, '');

        # 输出  I输出到浏览器 F输出到指定路径
        $this->pdf->Output("book.pdf", 'D');
    }
}

class AnalyzeTable
{
    public function bar_table()
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" version="1.1">' .
            '<polyline points="20,20 20,100 150,100" style="fill:none;stroke:black;stroke-width:3" />' .
            '</svg>';
    }
}

/**
 * Main Process
 */
session_start();

$admin = new AuthLogin;
$param = new Param;
$setting = new Setting;
$ui = new UI;

/**
 * Page Process & Action Process
 */
if (isset($_GET["page"]) || !isset($_GET["option"]))
{
    $page = $_GET["page"];
    $param = $param->page($_GET["page"]);
    $element = array();
    $sub_window = array('window' => array());
    $script = array('script' => array());
    $login = false;
    /* set page extra components */
    switch ($page)
    {
        case '':
        case 'search':
            if ($setting->get('enable_login_entrance'))
            {
                $sub_window = array('window' => array("login"));
                $script = array('script' => array("search", "toggle"));
            }
            else
            {
                $script = array('script' => array("search"));
            }
            break;

        case 'file':
            if ($setting->get('enable_login_entrance'))
            {
                $sub_window = array('window' => array("login"));
                $script = array('script' => array("search", "toggle", "confirm"));
            }
            else
            {
                $script = array('script' => array("search", "toggle", "confirm"));
            }
            if ($admin->auth())
            {
                $sub_window = array('window' => array('upload'), 'path' => $param["path"]);
            }
            break;

        case 'article':
            if ($admin->auth())
            {
                $sub_window = array('window' => array('comment', 'editor'), 'path' => $param["path"], 'editor_mode' => 'edit');
                $script = array('script' => array('toggle'));
            }
            else
            {
                $sub_window = array('window' => array('comment'), 'path' => $param["path"]);
                $script = array('script' => array('toggle'));
            }
            break;

        case 'msg_board':
            if ($setting->get('enable_login_entrance'))
            {
                $sub_window = array('window' => array('login', 'new_msg'), "path" => 'lib/Database/msg.json');
                $script = array('script' => array('search', 'toggle'));
            }
            else
            {
                $sub_window = array('window' => array('new_msg'), "path" => 'lib/Database/msg.json');
                $script = array('script' => array('toggle'));
            }
            break;

        case 'manage':
            switch ($param["manage"])
            {
                case 'article':
                    $sub_window = array('window' => array('editor', 'confirm'), 'editor_mode' => 'new');
                    $script = array('script' => array('toggle_editor', 'toggle', 'confirm'));
                    break;

                case 'extra':
                    $sub_window = array('window' => array('recover'));
                    $script = array('script' => array('toggle'));
                    break;
            }
            break;

        case 'tags':
            if ($setting->get('enable_login_entrance'))
            {
                $sub_window = array('window' => array('login'));
                $script = array('script' => array('toggle'));
            }
            break;

        default:
            break;
    }

    /* set page basic components */
    if ($admin->auth())
    {
        switch ($page)
        {
            case 'manage':
                $element = array("head" => '管理', 'header' => $param, 'navigator' => 'manage', 'setting' => $param["manage"], 'footer' => '');
                break;

            case 'file':
                $login = true;
                break;
        }
    }
    switch ($page)
    {
        case '':
            $element = array("head" => $param["class"], 'header' => $param, 'navigator' => '', 'article_list' => $param["class"], 'footer' => '');
            break;

        case 'article':
            $element = array("head" => Text::article_parser($param["path"])["head"]["title"], 'header' => $param, 'article' => $param["path"], 'footer' => '');
            break;

        case 'file':
            $element = array("head" => '杂物箱', 'header' => $param, 'navigator' => '', 'file' => array("path" => $param["path"], "login" => $login), 'footer' => '');
            break;

        case 'file_detail':
            $element = array("head" => '文件详情', 'header' => $param, 'file_detail' => $param["result"], 'footer' => '');
            break;

        case 'search':
            $element = array("head" => '搜索结果', 'header' => $param, 'search' => $param["result"], 'footer' => '');
            break;

        case 'msg_board':
            $element = array("head" => '留言板', 'header' => $param, 'navigator' => '', 'msg_board' => '', 'footer' => '');
            break;

        case 'login':
            $element = array("head" => '登录', 'login' => '');
            break;

        case 'tags':
            $element = array("head" => '标签', 'header' => $param, 'navigator' => '', 'tags' => '', 'footer' => '');
            break;

        default:
            if (!$admin->auth())
            {
                echo '<script>alert("未登录，即将跳转首页")</script>';
                header("refresh:0;url='index.php'");
            }
            break;
    }
    $ui->set($element, $page, $sub_window, $script);
    if ($setting->get('open_link_in_new_tag'))
    {
        echo str_replace("<a href", "<a target='_blank' href", $ui->get());
    }
    else
    {
        echo $ui->get();
    }
}
else
{
    $option = $_GET["option"];
    $param = $param->option($_GET["option"]);
    if ($admin->auth())
    {
        switch ($option)
        {
            case 'upload_article':
                File::upload('article/', '文章上传成功', 'index.php?page=manage&manage=article');
                break;

            case 'upload_file':
                File::upload('file' . $param["path"] . '/', '文件上传成功', 'index.php?page=file&path=' . $param["path"]);
                break;

            case 'upload_image':
                File::upload('file/img/', '题图上传成功', 'index.php?page=manage&manage=article');
                break;

            case 'upload_link':
                if ($param["link"] !== '' && $param["name"] !== '' && $param["size"] !== '')
                {
                    $data =
                        '>-path=' . $param["link"] . PHP_EOL .
                        '>-name=' . $param["name"] . PHP_EOL .
                        '>-size=' . $param["size"] . PHP_EOL;
                    File::write_file('file' . $param["path"]  . '/' . md5($data) . ".link", $data, "链接上传成功", "index.php?page=file&path=" . $param["path"]);
                }
                else
                {
                    echo '<script>alert("错误：内容为空")</script>';
                    header("refresh:0;url='index.php?page=file&path='");
                }
                break;

            case 'delete_article':
                File::delete($param["path"], "文章删除成功", "index.php?page=manage&manage=article");
                break;

            case 'delete_file':
                File::delete($param["path"], "文件删除成功", "index.php?page=file&path=");
                break;

            case 'edit_article':
                if (isset($param["text"]))
                {
                    File::write_file($param["path"], $param["text"], "编辑成功，即将跳转至文章", "index.php?page=article&article=" . urlencode($param["path"]));
                }
                else
                {
                    File::write_file($param["path"], Text::article_structor($param), "编辑成功，即将跳转至文章", "index.php?page=article&article=" . urlencode($param["path"]));
                }
                break;

            case 'new_article':
                $data = '--------' . PHP_EOL .
                    '>-title=' . $param["title"] . PHP_EOL .
                    '>-class=' . $param["class"] . PHP_EOL .
                    '>-desc=' . $param["desc"] . PHP_EOL .
                    '>-desimg=' . $param["desimg"] . PHP_EOL .
                    '>-author=' . $param["author"] . PHP_EOL .
                    '>-date=' . $param["date"] . PHP_EOL .
                    '--------' . PHP_EOL .
                    $param["content"] . PHP_EOL .
                    '--------';
                File::write_file('article/' . md5($data) . '.md', $data, "发布成功，即将跳转至文章", "index.php?page=article&article=" . urlencode('article/' . md5($data) . '.md'));
                break;

            case 'backup':
                $file_name = File::backup($param["type"]);
                $file = fopen($file_name, 'rb');

                Header("Content-type: application/octet-stream");                   //inform broser that this is a stream file
                Header("Accept-Ranges: bytes");                                     //accept range
                Header("Accept-Length: " . filesize($file_name));                   //data length
                Header("Content-Disposition: attachment; filename=" . $file_name);  //tell broser this is a downloadable file and filename

                echo fread($file, filesize($file_name));        //read file content and echo file stream to broser
                fclose($file);
                unlink($file_name);                             //remove backup file in case of file leak

                header("refresh:0;url='index.php?page=manage&manage=extra'");
                break;

            case 'article_repair':
                $item = File::get_file('article', '*.md', 0, 0);
                for ($i = 0; $i < count($item); $i++)
                {
                    Text::article_fixer($item[$i]["path"]);
                }
                echo '<script>alert("修复成功，即将跳转")</script>';
                header("refresh:0;url='index.php?page=manage&manage=extra'");
                break;

            case 'create_book':
                $item = File::get_file("article", "*.md", 0, 0);
                $parser = new HyperDown\Parser;
                $text = '';
                for ($x = 0; $x < count($item); $x++)
                {
                    if (is_array(Text::article_parser($item[$x]["path"])))
                    {
                        $text = $text . '<h1>' . Text::article_parser($item[$x]["path"], HTML_ENTITIES)["head"]["title"] . '</h1>' . $parser->makeHtml(Text::article_parser($item[$x]["path"])["content"]);
                    }
                }
                $pdf = new Pdf;
                $pdf->createPDF(array("user" => "xeonds", "title" => "test_title", "subject" => "test_subject", "keywords" => "test_keywords", "HT" => true, "content" => $text, "path" => "book.pdf"));
                break;

            case 'edit_setting':
                if ($param["admin_password"] !== $setting->get("admin_password"))
                {
                    $setting->set($param);
                    echo '<script>alert("密码已更改，新密码为' . $param["admin_password"] . '")</script>';
                }
                else
                {
                    $setting->set($param);
                    header("refresh:0;url='index.php?page=manage&manage=custom'");
                }
                break;

            case 'switch_article_stat':
                File::switch_md($param["path"]);
                echo '<script>alert("切换成功")</script>';
                header("refresh:0;url='index.php?page=manage&manage=article'");
                break;

            case 'link_repair':
                $item = File::get_file('file', '*.*', 0, 5);
                for ($i = 0; $i < count($item); $i++)
                {
                    if (fnmatch("*.lanzous", $item[$i]["name"]))
                    {
                        rename($item[$i]["path"], explode($item[$i]["name"], $item[$i]["path"])[0] . md5(file_get_contents($item[$i]["path"])) . ".link");
                    }
                }
                echo '<script>alert("修复成功，即将跳转")</script>';
                header("refresh:0;url='index.php?page=manage&manage=extra'");
                break;

            case 'recover':
                $file = array("name" => $_FILES["file"]["name"], "path" => $_FILES["file"]["tmp_name"]);
                if (fnmatch("*.zip", $file["name"]))
                {
                    move_uploaded_file($file["path"], $file["name"]);
                    $zip = new ZipArchive;
                    $zip->open($file["name"]);
                    $zip->extractTo('./');
                    $zip->close();
                    unlink($file["name"]);
                    echo '<script>alert("恢复成功")</script>';
                    header("refresh:0;url='index.php?page=manage&manage=extra'");
                }
                else if (fnmatch("*.md", $file["name"]) || fnmatch("*.notavaliable", $file["name"]))
                {
                    File::upload('article/', '恢复成功', 'index.php?page=manage&manage=extra');
                }
                else
                {
                    echo '<script>alert("恢复失败")</script>';
                    header("refresh:0;url='index.php?page=manage&manage=extra'");
                }
                break;


            default:
                break;
        }
    }
    switch ($option)
    {
        case 'login':
            $admin->login($param["password"]);
            header("refresh:0;url='index.php?page=manage&manage=analytics'");
            break;

        case 'logout':
            $admin->logout();
            echo '<script>alert("退出登录成功，即将跳转首页")</script>';
            header("refresh:0;url='index.php'");
            break;

        case 'comment_article':
            if ($param["id"] != '' && $param["content"] != '')
            {
                $admin = new AuthLogin;
                $setting = new Setting;
                if ($param["id"] == $setting->get("admin_id") && !$admin->auth())
                {
                    echo '<script>alert("评论失败：不能使用管理员id")</script>';
                    header("refresh:0;url='index.php?page=msg_board'");
                    exit;
                }
                $data =  PHP_EOL . '>--<' . PHP_EOL .
                    '>-id=' . $param["id"] . PHP_EOL .
                    '>-date=' . $param["date"] . PHP_EOL .
                    '>-content=' . $param["content"] . PHP_EOL;
                File::write_file($param["path"], $data, "评论成功", "index.php?page=article&article=" . urlencode($param["path"]), 'append');
            }
            else              //ignore empty comment
            {
                echo '<script>alert("评论失败：内容与id不能为空")</script>';
                header("refresh:0;url='index.php?page=article&article=" . urlencode($param["path"]) . "'");
            }
            break;

        case 'search':
            $_SESSION["search-result"] = Text::search($param["keyword"]);
            header("refresh:0;url='index.php?page=search'");
            break;

        case 'file_detail':
            $_SESSION["file-detail"] = File::file_detail($param["path"]);
            header("refresh:0;url='index.php?page=file_detail'");
            break;

            /* Finished */
        case 'new_msg':
            if ($param["id"] != '' && $param["content"] != '')
            {
                $admin = new AuthLogin;
                $setting = new Setting;
                if ($param["id"] == $setting->get("admin_id") && !$admin->auth())
                {
                    echo '<script>alert("留言失败：不能使用管理员id")</script>';
                    header("refresh:0;url='index.php?page=msg_board'");
                    exit;
                }
                $path = 'lib/Database/msg.json';
                $msg = file_get_contents($path);
                $data = array('id' => $param["id"], 'date' => $param["date"], 'content' => $param["content"]);
                $msg = json_decode($msg);
                array_push($msg, $data);
                File::write_file($path, json_encode($msg), "留言成功", 'index.php?page=msg_board');
            }
            else              //ignore empty comment
            {
                echo '<script>alert("留言失败：内容与id不能为空")</script>';
                header("refresh:0;url='index.php?page=msg_board'");
            }
            break;

        default:
            if (!$admin->auth())
            {
                echo '<script>alert("未登录，即将跳转首页")</script>';
                header("refresh:0;url='index.php'");
            }
            break;
    }
}
