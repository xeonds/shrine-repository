<?php

/**
 * Old controller from previous version of shrine-repository.
 */
if (isset($_GET["page"]) || !isset($_GET["option"])) {
    $page = $_GET["page"];
    $param = $param->page($_GET["page"]);
    $element = array();
    $sub_window = array('window' => array());
    $script = array('script' => array());
    $login = false;
    /* set page extra components */
    switch ($page) {
        case '':
        case 'search':
            if ($setting->get('enable_login_entrance')) {
                $sub_window = array('window' => array("login"));
                $script = array('script' => array("search", "toggle"));
            } else {
                $script = array('script' => array("search"));
            }
            break;

        case 'file':
            if ($setting->get('enable_login_entrance')) {
                $sub_window = array('window' => array("login"));
                $script = array('script' => array("search", "toggle", "confirm"));
            } else {
                $script = array('script' => array("search", "toggle", "confirm"));
            }
            if ($admin->auth()) {
                $sub_window = array('window' => array('upload'), 'path' => $param["path"]);
            }
            break;

        case 'article':
            if ($admin->auth()) {
                $sub_window = array('window' => array('comment', 'editor'), 'path' => $param["path"], 'editor_mode' => 'edit');
                $script = array('script' => array('toggle'));
            } else {
                $sub_window = array('window' => array('comment'), 'path' => $param["path"]);
                $script = array('script' => array('toggle'));
            }
            break;

        case 'msg_board':
            if ($setting->get('enable_login_entrance')) {
                $sub_window = array('window' => array('login', 'new_msg'), "path" => 'lib/Database/msg.json');
                $script = array('script' => array('search', 'toggle'));
            } else {
                $sub_window = array('window' => array('new_msg'), "path" => 'lib/Database/msg.json');
                $script = array('script' => array('toggle'));
            }
            break;

        case 'manage':
            switch ($param["manage"]) {
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
            if ($setting->get('enable_login_entrance')) {
                $sub_window = array('window' => array('login'));
                $script = array('script' => array('toggle'));
            }
            break;

        default:
            break;
    }

    /* set page basic components */
    if ($admin->auth()) {
        switch ($page) {
            case 'manage':
                $element = array("head" => '管理', 'header' => $param, 'navigator' => 'manage', 'setting' => $param["manage"], 'footer' => '');
                break;

            case 'file':
                $login = true;
                break;
        }
    }
    switch ($page) {
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
            if (!$admin->auth()) {
                echo '<script>alert("未登录，即将跳转首页")</script>';
                header("refresh:0;url='index.php'");
            }
            break;
    }
    $ui->set($element, $page, $sub_window, $script);
    if ($setting->get('open_link_in_new_tag')) {
        echo str_replace("<a href", "<a target='_blank' href", $ui->get());
    } else {
        echo $ui->get();
    }
} else {
    $option = $_GET["option"];
    $param = $param->option($_GET["option"]);
    if ($admin->auth()) {
        switch ($option) {
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
                if ($param["link"] !== '' && $param["name"] !== '' && $param["size"] !== '') {
                    $data =
                        '>-path=' . $param["link"] . PHP_EOL .
                        '>-name=' . $param["name"] . PHP_EOL .
                        '>-size=' . $param["size"] . PHP_EOL;
                    File::write_file('file' . $param["path"]  . '/' . md5($data) . ".link", $data, "链接上传成功", "index.php?page=file&path=" . $param["path"]);
                } else {
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
                if (isset($param["text"])) {
                    File::write_file($param["path"], $param["text"], "编辑成功，即将跳转至文章", "index.php?page=article&article=" . urlencode($param["path"]));
                } else {
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
                for ($i = 0; $i < count($item); $i++) {
                    Text::article_fixer($item[$i]["path"]);
                }
                echo '<script>alert("修复成功，即将跳转")</script>';
                header("refresh:0;url='index.php?page=manage&manage=extra'");
                break;

            case 'create_book':
                // $item = File::get_file("article", "*.md", 0, 0);
                // $parser = new HyperDown\Parser;
                // $text = '';
                // for ($x = 0; $x < count($item); $x++) {
                //     if (is_array(Text::article_parser($item[$x]["path"]))) {
                //         $text = $text . '<h1>' . Text::article_parser($item[$x]["path"], HTML_ENTITIES)["head"]["title"] . '</h1>' . $parser->makeHtml(Text::article_parser($item[$x]["path"])["content"]);
                //     }
                // }
                // $pdf = new Pdf;
                // $pdf->createPDF(array("user" => "xeonds", "title" => "test_title", "subject" => "test_subject", "keywords" => "test_keywords", "HT" => true, "content" => $text, "path" => "book.pdf"));
                break;

            case 'edit_setting':
                if ($param["admin_password"] !== $setting->get("admin_password")) {
                    $setting->set($param);
                    echo '<script>alert("密码已更改，新密码为' . $param["admin_password"] . '")</script>';
                } else {
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
                for ($i = 0; $i < count($item); $i++) {
                    if (fnmatch("*.lanzous", $item[$i]["name"])) {
                        rename($item[$i]["path"], explode($item[$i]["name"], $item[$i]["path"])[0] . md5(file_get_contents($item[$i]["path"])) . ".link");
                    }
                }
                echo '<script>alert("修复成功，即将跳转")</script>';
                header("refresh:0;url='index.php?page=manage&manage=extra'");
                break;

            case 'recover':
                $file = array("name" => $_FILES["file"]["name"], "path" => $_FILES["file"]["tmp_name"]);
                if (fnmatch("*.zip", $file["name"])) {
                    move_uploaded_file($file["path"], $file["name"]);
                    $zip = new ZipArchive;
                    $zip->open($file["name"]);
                    $zip->extractTo('./');
                    $zip->close();
                    unlink($file["name"]);
                    echo '<script>alert("恢复成功")</script>';
                    header("refresh:0;url='index.php?page=manage&manage=extra'");
                } else if (fnmatch("*.md", $file["name"]) || fnmatch("*.notavaliable", $file["name"])) {
                    File::upload('article/', '恢复成功', 'index.php?page=manage&manage=extra');
                } else {
                    echo '<script>alert("恢复失败")</script>';
                    header("refresh:0;url='index.php?page=manage&manage=extra'");
                }
                break;


            default:
                break;
        }
    }
    switch ($option) {
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
            if ($param["id"] != '' && $param["content"] != '') {
                $admin = new AuthLogin;
                $setting = new Setting;
                if ($param["id"] == $setting->get("admin_id") && !$admin->auth()) {
                    echo '<script>alert("评论失败：不能使用管理员id")</script>';
                    header("refresh:0;url='index.php?page=msg_board'");
                    exit;
                }
                $data =  PHP_EOL . '>--<' . PHP_EOL .
                    '>-id=' . $param["id"] . PHP_EOL .
                    '>-date=' . $param["date"] . PHP_EOL .
                    '>-content=' . $param["content"] . PHP_EOL;
                File::write_file($param["path"], $data, "评论成功", "index.php?page=article&article=" . urlencode($param["path"]), 'append');
            } else              //ignore empty comment
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
            if ($param["id"] != '' && $param["content"] != '') {
                $admin = new AuthLogin;
                $setting = new Setting;
                if ($param["id"] == $setting->get("admin_id") && !$admin->auth()) {
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
            } else              //ignore empty comment
            {
                echo '<script>alert("留言失败：内容与id不能为空")</script>';
                header("refresh:0;url='index.php?page=msg_board'");
            }
            break;

        default:
            if (!$admin->auth()) {
                echo '<script>alert("未登录，即将跳转首页")</script>';
                header("refresh:0;url='index.php'");
            }
            break;
    }
}
