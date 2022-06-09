<!DOCTYPE html>
<html>

<?php
//==============
//Repository-ver-7.1.9
//   by xeonds
//==============
//custom options
//
//manual:
//1.site_name:title showed in tags.
//2.sub_title:showed under title,also as signature.
//3.site_footer:copyright at the footer.
//4.enable_unsort:enable webdisk function.
//5.all_category:tags showed in navigator.
//6.admin_password:password for site management.
//7.time_begin:the site's beginning time.
//==============
// Develop Plan
//1.manage page include:custom bg-img,title-img,css style[wasted].
//2.article including commit area [finished]using float window[wasted].
//3.file detail page using float window[wasted].
//==============
$site_name = "神社的储物间";
$sub_title = "“我节操放哪去了？”";
$site_footer = "夢想天生";
$enable_unsort = true;
$enable_login_entrance = true;
$all_category = array("工具", "游戏", "计算机");
$admin_password = "1672";
$time_begin = 2020;
$enable_animation = true;
?>
<?php
//DO NOT EDIT FOLLOWING UNLESS YOU KNOW WHAT YOU ARE DOING.

//import php markdown render
include 'lib/Parser/Parser.php';

//function definations

function head($site_name, $title)
{
    if ($title == '') {
        echo $site_name;
    } else {
        echo $title . "| " . $site_name;
    }
}

function navigator($all_category, $enable_unsort)
{
    if ($all_category == "setting_nav") {
        echo "<button onClick=\"window.location.href='index.php?option=analytics'\">统计</button>" . PHP_EOL
            . "<button onClick=\"window.location.href='index.php?option=article'\">文章管理</button>" . PHP_EOL
            . "<button onClick=\"window.location.href='index.php?option=custom'\">自定义</button>" . PHP_EOL
            . "<button onClick=\"window.location.href='index.php?option=extra'\">高级设置</button>" . PHP_EOL;
    } else {
        echo "<button onClick=\"window.location.href='index.php'\">所有</button>" . PHP_EOL;
        if ($enable_unsort == true) {
            echo "<button onClick=\"window.location.href='index.php?path=.'\">杂物箱</button>" . PHP_EOL;
        }
        for ($i = 0; $i < count($all_category); $i++) {
            echo "<button onClick=\"window.location.href='index.php?category=" . $all_category[$i] . "'\">" . $all_category[$i] . "</button>" . PHP_EOL;
        }
    }
}

function unsort($link, $logged_in)
{
    echo '<div class="topic"><h2>文件夹</h2><div class="item">';

    $folder = get_folder("file/" . $link, 0, 0);
    if ($folder != array()) {
        for ($i = 0; $i < count($folder["name"]); $i++) {
            echo "<a href='index.php?path=" . $link . "/" . $folder["name"][$i] . "'>" . $folder["name"][$i] . "</a><br>";
        }
    } else {
        echo '<h3>这里是空的</h3>';
    }
    echo "</div></div>
    <div class='topic'>
    <div class='item'>
    <h2>文件</h2>";
    $item = get_file("file/" . $link, "*.*", 0, 0);
    if ($item != array()) {
        echo "<table style='width:100%;'>
                <thead>
                <td>文件名</td><td>&nbsp;</td><td>大小</td><td>创建时间</td></thead>";
        for ($i = 0; $i < count($item); $i++) {
            echo "<tr>";
            if (file_detail($item[$i]["path"])["extension"] == "lanzous") {
                echo "<td><a href='" . explode("=", explode(">-", file_get_contents($item[$i]["path"]))[1])[1]  . "' target='_blank'>" . explode("=", explode(">-", file_get_contents($item[$i]["path"]))[2])[1] . "</a></td>" .
                    "<td>&nbsp;</td>" .
                    "<td>" . explode("=", explode(">-", file_get_contents($item[$i]["path"]))[3])[1] . "</td>" .
                    "<td>" . date('Y-m-d', filectime($item[$i]["path"])) . "</td>";
            } else {
                echo "<td><a href='index.php?file=" . $item[$i]["path"] . "' target='_blank'>" . $item[$i]["name"] . "</a></td>" .
                    "<td>&nbsp;</td>" .
                    "<td>" . getSize(filesize($item[$i]["path"])), "</td>" .
                    "<td>" . date('Y-m-d', filectime($item[$i]["path"])) . "</td>";
            }
            if ($logged_in == true)
                echo
                "<td>" .
                    "<button id='confirm-" . $i . "' onClick=confirm(\"" . $i . "\")>删除</button>" .
                    "<button id='yes-" . $i . "' onClick='window.location.href=\"index.php?path=" . $item[$i]["path"] . "&delete=2\"' style='display:none;'>确认</button>" .
                    "<button id='no-" . $i . "' onClick=confirm(\"" . $i . "\") style='display:none;'>取消</button>" .
                    "</td>";
            echo
            "</tr>";
        }
        echo '</table></div></div>';
    } else {
        echo '<h3>这里是空的</h3></div></div>';
    }
}

function file_detail($file)
{
    $file_detail = [];
    $path_parts = pathinfo($file);

    $file_detail["dirname"]     = $path_parts['dirname'];                       //目录名称
    $file_detail["basename"]    = $path_parts['basename'];                      //文件全名
    $file_detail["extension"]   = $path_parts['extension'];                     //文件后缀
    $file_detail["filename"]    = $path_parts['filename'];                      //文件名称
    $file_detail["filetype"]    = filetype($file);                              //文件类型
    $file_detail["filesize"]    = getSize(filesize($file));                     //文件大小
    $file_detail["filectime"]   = date('Y年m月d日 H:i:s', filectime($file));    //文件创建时间
    $file_detail["filemtime"]   = date("Y年m月d日 H:i:s", filemtime($file));    //文件修改时间
    $file_detail["fileatime"]   = date("Y年m月d日 H:i:s", fileatime($file));    //最后访问时间
    //$file_detail["fileread"]    = var_dump(is_readable($file));                 //可读
    //$file_detail["filewrite"]   = var_dump(is_writeable($file));                //可写
    //$file_detail["fileexecute"] = var_dump(is_executable($file));               //可执行

    return $file_detail;
}

function setting($option)
{
    echo '<div id="setting">';
    if ($option == "analytics") {
        echo '<h2>统计</h2>';
        $item = get_file("article", "*.md", 0, 0);
        $text = '';
        echo '<p>文章数量:' . count($item) . '篇</p>';
        for ($x = 0; $x < count($item); $x++) {
            if (is_array(article_parser($item[$x]["path"])))
                $text = $text . article_parser($item[$x]["path"])["content"];
        }
        echo '<p>总字数:' . strlen($text) . '字</p>';
        $tag = [];
        for ($i = 0; $i < count($item); $i++) {
            if (is_array(article_parser($item[$i]["path"])))
                $tag[] = trim(article_parser($item[$i]["path"])["head"]["class"]);
        }
        $all_tags = $tags = explode(",", str_replace("，", ",", str_replace("Array", "", implode(",", $tag))));
        $tags = array_flip($tags);
        $tags = array_flip($tags);
        $res = analyze_tags($all_tags, $tags);
        echo '<p>标签：共' . count($tags) . '个</p>'
            . '<div class="tags">';   //imitated from hexo (*/ω＼*)
        for ($i = 0; $i < count($res); $i++) {
            echo '<a href="index.php?category=' . $res[$i]["tag"] . '" target="_blank" class="tag" style="font-size:' .  sprintf("%0.1f", 6 * (0.75 * ($res[$i]["num"] / count($all_tags) - 0.5) + 0.5)) . 'rem;">' . $res[$i]["tag"] . '：' . $res[$i]["num"] . '篇</a>';
        }
        echo '</div>'
            . '</div>';
    } elseif ($option == "article") {
        $item = get_file("article", "*.md", 0, 0);
        echo '
        <div style="display:flex;flex-flow:row;align-items:center;justify-content:space-between;">
        <h2>文章管理</h2>
        <button onClick=toggle("editor-new-article") style="margin-right:1rem;">新文章</button>
        </div>
        <table style="width:100%;">
        <thead><td>标题</td><td>大小</td><td>创建时间</td><td>操作</td></thead><tbody>';
        for ($xi = 0; $xi < count($item); $xi++) {
            $article = article_parser($item[$xi]["path"]);
            $article_size = getSize(filesize($item[$xi]["path"]));
            if (is_array($article))
                echo '
            <tr>
                <td><a href="index.php?article=' . urlencode($item[$xi]["path"]) . '" target="_blank">' . $article["head"]["title"] . '</a></td>
                <td>' . $article_size . '</td>
                <td>' . $article["head"]["date"] . '</td>
                <td>
                    <button onClick=toggle_editor(' . $xi . ')>编辑</button>
                    <button id="confirm-' . $xi . '" onClick=confirm("' . $xi . '")>删除</button>
                    <button id="yes-' . $xi . '" onClick="window.location.href=\'index.php?article=' . urlencode($item[$xi]["path"]) . '&delete=1\'" style="display:none;">确认</button>
                    <button id="no-' . $xi . '" onClick=confirm("' . $xi . '") style="display:none;">取消</button></td>
               </td>
            </tr>
            <tr>
                <td colspan="4">
                    <div class="edit-article" id="editor-' . $xi . '" style="margin:auto;padding:0.5rem;border-radius:0.5rem;border:solid 1px white;display:none;">
                        <form action="index.php" method="post">
                            <h3>编辑文章:' . $article["head"]["title"] . '</h3>
                            <textarea name="editor-content" style="width:100%;height:24rem;margin:auto;">' . $article["text"] . '</textarea>
                            <input type="hidden" name="editor-path" value="' . $item[$xi]["path"] . '"/>
                            <input type="hidden" name="editor-type" value="1" />
                            <input type="submit" name="submit" value="确认修改" style="width:12rem;margin:auto;">
                        </form>
                    </div>
                </td>
            </tr>';
            else {
                echo '<tr><td colspan="4">' .
                    $article
                    . '</td></tr>';
            }
        }
        echo '</tbody></table>'
            . '</div>';
    } elseif ($option == "custom") {
        echo '<h2>自定义</h2>'
            . '<h3>页面背景</h3>'
            . '<h3>过渡动画</h3>'
            . ''
            . '<h3>网站标题/副标题</h3>'
            . '<h3>标签页开关</h3>'
            . '<h3>杂物箱开关</h3>'
            . '<p>其他的再想想吧（咕</p>'
            . '</div>';
    } elseif ($option == "extra") {
        echo  '<h2>高级设置</h2>'
            . '<h3>升级</h3>'
            . '<p>开发中</p>'
            . '<h3>备份/导入备份</h3>'
            . '<a href="index.php?backup=article">备份文章</a>'
            . '<a href="index.php?backup=file">备份文件</a>'
            . '<p>项目：储物间文件，自定义设置，文章</p>'
            . '<h2>文章工具</h2>'
            . '<h3>文章更新&修复</h3>'
            . '<a href="index.php?option=article_repair">更新&修复</a>'
            . '</div>';
    } elseif ($option == "login") {
        echo '<div id="login" class="float-window" style="display:none;">'
            . '<button onClick=toggle("login") style="align-self:flex-end;">关闭</button>'
            . '<form method="post" action="index.php">'
            . '<p>输入密码</p>'
            . '<input type="password" name="password">'
            . '<input type="submit" name="login" value="登陆">'
            . '</form>'
            . '</div>'
            . '</div>';
    }
}

function footer($time_begin, $site_name)
{
    echo "<div>&copy;" . $time_begin . "-" . date("Y") . " | " . $site_name . "</div>";
}

function md_to_html($file_path)
{
    $parser = new HyperDown\Parser;
    $text = $parser->makeHtml(explode("--------", file_get_contents($file_path))[2]);
    $comments = explode("--------", file_get_contents($file_path))[3];
    $comments_arr = explode(">--<", $comments);

    echo "<div class='topic markdown'>" . PHP_EOL .
        '<h1 style="text-align:center;">'
        . explode("=", explode(">-", explode("--------", file_get_contents($file_path))[1])[1], 2)[1]
        . '</h1>' .
        '<h4 style="text-align:center;">作者：'
        . explode("=", explode(">-", explode("--------", file_get_contents($file_path))[1])[5], 2)[1] . ' | '
        . explode("=", explode(">-", explode("--------", file_get_contents($file_path))[1])[6], 2)[1] . '</h4>' . PHP_EOL .
        $text . "</div><hr>";
    echo "<div class='topic comments'>
    <div style='display:flex;flex-flow:row;align-items:center;justify-content:space-between;'>
    <h2>评论</h2>
    <button onClick=toggle('send-comment') style='margin-right:1rem;'>发评论</button>
    </div>";
    for ($i = 1; $i < count($comments_arr); $i++) {
        echo '<div class="comment">'; /*
        if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
            echo '<div class="comment-id" style="display:flex;flex-flow:row;align-items:center;justify-content:space-between;">' . explode("=", (explode(">-", $comments_arr[$i])[1]))[1] . '<button onClick=alert("还没做好，先去文章管理里删吧（咕）")>删除</button>' . '</div>';
        }  //comment id with delete button
        else //*/
        echo '<div class="comment-id">' . explode("=", (explode(">-", $comments_arr[$i])[1]))[1] . '</div>';
        //comment id
        echo '<div class="comment-time">' . explode("=", (explode(">-", $comments_arr[$i])[2]))[1] . '</div>';  //comment date
        echo '<div class="comment-context">' . explode("=", (explode(">-", $comments_arr[$i])[3]))[1] . '</div>';  //comment context
        echo '</div>';
    }
    echo "</div>";
}

function getSize($filesize)
{
    if ($filesize >= 1073741824) {
        $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
    } elseif ($filesize >= 1048576) {
        $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
    } elseif ($filesize >= 1024) {
        $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
    } else {
        $filesize = $filesize . ' 字节';
    }

    return $filesize;
}

function get_file($path, $filetype, $min_depth, $max_depth)
{
    if (!file_exists($path)) {
        return [];
    }
    $file = [];
    if ($dir = opendir($path)) {                         //check whether the dir is correctly opened
        while (false !== $item = readdir($dir)) {        //search each item and check whether it's a file or a directory
            $nextPath = $path . '/' . $item;
            if ($item != '.' && $item != '..' && is_dir($nextPath) && $max_depth > 0) {
                $file = array_merge($file, get_file($nextPath, $filetype, $min_depth - 1, $max_depth - 1));
            } elseif ($item != '.' && $item != '..' && fnmatch($filetype, $item) && $min_depth <= 0) {
                $file[] = array("name" => $item, "path" => $nextPath, "time" => filectime($nextPath));
            }
        }
    }

    foreach ($file as $key => $val) {   //ordered by name
        $tmp[$key] = $val['name'];
    }
    if (is_array($tmp))             //in case of error:Argument #1 is expected to be an array or a sort flag 
        array_multisort($tmp, SORT_ASC, $file);

    return $file;
}

function get_folder($path, $min_depth, $max_depth)
{
    if (!file_exists($path)) {
        return [];
    }
    $folder = [];
    if ($dir = opendir($path)) {                        //check whether the dir is correctly opened
        while (false !== $item = readdir($dir)) {        //search each item and check whether it's a file or a directory
            $nextPath = $path . '/' . $item;
            if ($item != '.' && $item != '..' && is_dir($nextPath) && $max_depth > 0) {
                $folder = array_merge($folder, get_folder($nextPath, $min_depth - 1, $max_depth - 1));
            } elseif ($item != '.' && $item != '..' && is_dir($nextPath) && $min_depth <= 0) {
                $folder["path"][] = $nextPath;
                $folder["name"][] = $item;
            }
        }
    }
    return $folder;
}

function file_upload($type, $path)
{
    if ($_FILES["file"]["error"] <= 0) {
        if ($type == 'article')
            move_uploaded_file($_FILES["file"]["tmp_name"], "article/" . $_FILES["file"]["name"]);
        else if ($type == 'file')
            move_uploaded_file($_FILES["file"]["tmp_name"], "file/" . $path . "/" . $_FILES["file"]["name"]);
        else if ($type == 'img')
            move_uploaded_file($_FILES["file"]["tmp_name"], "file/img/" . $_FILES["file"]["name"]);
        echo "<script>alert('上传成功，储存在" . $path . "')</script>";
    }
}

function show_article($category)
{
    echo '<div class="article-list">';
    echo '<h2>';
    if ($category == '') {
        echo '所有';
    } else {
        echo $category;
    }
    echo '</h2>';
    $item = get_file("article", "*.md", 0, 0);
    foreach ($item as $key => $val) {
        $tmp[$key] = $val['time'];
    }
    array_multisort($tmp, SORT_DESC, $item);
    for ($i = 0; $i < count($item); $i++) {
        $text = file_get_contents($item[$i]["path"]);
        $text_arr = explode("--------", $text);
        $text_head = $text_arr[1];
        $text_head_arr = explode(">-", $text_head);
        $title = explode("=", $text_head_arr[1], 2)[1];
        $article_category = explode("=", $text_head_arr[2], 2)[1];
        $desc = explode("=", $text_head_arr[3], 2)[1];
        $des_img = explode("=", $text_head_arr[4], 2)[1];
        if ($category == "") {
            echo "<article class='item'>";
            echo "<a href='index.php?article=" . urlencode($item[$i]["path"]) . "'>";
            echo "<h3>" . $title . "</h3>";
            echo "<div class=\"context\">";
            if (trim($des_img) != '') {
                echo "<div class='left'><img src='" . $des_img . "' alt=''></div><div class='right'><p>" . $desc . "</p></div>";
            } else {
                echo "<p>" . $desc . "</p>";
            }
            echo "</div></a></article>";
        } elseif (strstr($article_category, $category)) {
            echo "<article class='item'>" .
                "<a href='index.php?article=" . urlencode($item[$i]["path"]) . "'>" .
                "<h3>" . $title . "</h3>" .
                "<div class=\"context\">";
            if (trim($des_img) != '') {
                echo "<div class='left'>
                        <img src='" . $des_img . "' alt=''>
                    </div>
                    <div class='right'>
                        <p>" . $desc . "</p>
                    </div>";
            } else {
                echo "<p>" . $desc . "</p>";
            }
            echo "</div></a></article>";
        }
    }
    echo '</div>';
}

function search($text)
{
    $article = get_file("article", "*.md", 0, 0);
    $file = get_file("file", "*.*", 0, 3);
    $result = [];

    //search keyword in articles
    for ($i = 0; $i < count($article); $i++) {
        if (stristr(file_get_contents($article[$i]["path"]), $text)) {
            $result["article"]["str"][] = article_parser($article[$i]["path"])["head"]["title"];
            $result["article"]["path"][] = $article[$i]["path"];
        }
    }
    //search file names
    for ($i = 0; $i < count($file); $i++) {
        if (stristr($file[$i]["name"], $text)) {
            $result["file"]["str"][] = $file[$i]["name"];
            $result["file"]["path"][] = $file[$i]["path"];
        }
    }

    return $result;
}

function analyze_tags($str, $tags)
{
    $str = implode("", $str);
    $result = [];
    foreach ($tags as $key => $val) {
        $tag_list[] = $val;
    }
    for ($i = 0; $i < count($tag_list); $i++) {
        $result[] = array("num" => substr_count($str, $tag_list[$i]), "tag" => $tag_list[$i]);
    }

    return $result;
}

function backup($backup_type)
{
    $zip = new ZipArchive;

    switch ($backup_type) {
        case 'article':
            if ($zip->open('article-archive.zip', ZipArchive::CREATE) === true) {
                $res = get_file('article', '*.*', 0, 0);
                for ($i = 0; $i < count($res); $i++) {
                    $zip->addFile($res[$i]['path']);
                }
                $zip->close();
                return 'article-archive.zip';
            }
            break;

        case 'setting':
            break;

        case 'file':
            if ($zip->open('file-archive.zip', ZipArchive::CREATE) === true) {
                $res = get_file('file', '*.*', 0, 3);
                for ($i = 0; $i < count($res); $i++) {
                    $zip->addFile($res[$i]['path']);
                }
                $zip->close();
                return 'file-archive.zip';
            }
            break;
    }
}

function article_parser($path)
{
    $text = file_get_contents($path);
    $seperated_text = explode("--------", $text);
    $result = [];

    if (count($seperated_text) == 4) {
        $head = $seperated_text[1];

        //article head process
        $head_arr = explode(">-", $head);
        foreach ($head_arr as $key => $val) {
            $val_arr = explode("=", $val);
            $result["head"][$val_arr[0]] = $val_arr[1];
        }
        $result["content"] = $seperated_text[2];
        $result["comment"] = $seperated_text[3];
        $result["text"] = $text;
        return $result;
    } else {       //fix article format
    }
    return '错误：文章格式错误，请使用高级设置>文章修复工具进行修复。';
}

function article_fixer($path)
{
    //read content
    $text = file_get_contents($path);
    $seperated_text = explode("--------", $text);

    /*
        Generally, the most frequently problem is missing "--------" at the end of article.
            -I use "--------" as seperator between content area and comment area.
             So missing "--------" will result in failing to comment an article.
             Obviously, not really can`t making comments, the problem is 
             the comments can`t be correctly displayed.
        And another problem is missing article creating date(It is being added recently).
        Also, immigrating from hexo is also a problem. So I created a format converter. 
                ---
                title: 文章名
                date: 2017-10-31 20:38:17     //发表日期
                updated: 2017-10-31 21:58:03  //更新日期
                categories: Life              //文章分类
                tags: [tag1,tag2]             //文章标签，多标签时使用英文逗号隔开
                photos:                       //如果使用Fancybox（文章头部展示图片），如此设置  
                                            //注意冒号后面有空格
                ---
            this is normal hexo format. So similar to mine (Because I immatated some format from hexo)
    */

    //article format detector: Detect whether an article is from hexo or in normal format.
    if (count($seperated_text) == 0) {          //file format is hexo markdown
        //converter
        $result = '--------' . PHP_EOL;
        $text_arr = explode("---", $text, 3);
        $head_arr = explode(PHP_EOL, $text_arr[1]);
        foreach ($head_arr as $key => $val) {
            $val_arr = explode(": ", $val);
            if ($val_arr[0] == 'updated' || $val_arr[0] == 'categories')
                continue;
            if ($val_arr[0] == 'tags') {
                $result = $result . '>-class=' . $val_arr[1] . PHP_EOL;
            } else if ($val_arr[0] == 'photo') {
                $result = $result . '>-desimg=' . $val_arr[1] . PHP_EOL;
            } else {
                $result = $result . '>-' . $val_arr[0] . '=' . $val_arr[1] . PHP_EOL;
            }
        }
        $result = $result
            . '--------' . PHP_EOL
            .   $text_arr[2] . PHP_EOL
            . '--------';
        file_put_contents($path, $result);
    } else if (count($seperated_text) == 3) {       //missing seperaor between content and comment area
        file_put_contents(
            $path,
            '--------' .
                $seperated_text[1] . '--------' .
                $seperated_text[2] . '--------'
        );
    } else {                                        //struct is correct. All errors are in head
        if (!strstr($seperated_text[1], 'date'))    //if missing creating date
        {
            $result = '--------' . PHP_EOL
                . $seperated_text[1]
                . '>-date=' . date("Y.m.d H:i:s") . PHP_EOL
                . '--------' . PHP_EOL
                . $seperated_text[2] . PHP_EOL
                . '--------'
                . $seperated_text[3];
            file_put_contents($path, $result);
        }
    }
}
?>
<?php
/*fetch necessary values*/

//main variables
$category = $_REQUEST["category"];
$article = $_REQUEST["article"];
$path = $_REQUEST["path"];
$option = $_REQUEST["option"];
$search = $_REQUEST["search"];
if ($article != '') {
    $title = article_parser($article)["head"]["title"];
}
$delete = $_REQUEST["delete"];
$backup = $_REQUEST["backup"];

//comment variables
$comm_id = $_POST["comment-id"];
$comm_context = $_POST["comment-context"];
$comm_article = $_POST["comment-article"];

//editor variables
$article_content = $_POST["editor-content"];
$article_path = $_POST["editor-path"];
$article_edit_type = $_POST["editor-type"];

//new article variables
$new_article_title = $_POST["new-article-title"];
$new_article_class = $_POST["new-article-class"];
$new_article_desc = $_POST["new-article-desc"];
$new_article_desimg = $_POST["new-article-desimg"];
$new_article_author = $_POST["new-article-author"];
$new_article_content = $_POST["new-article-content"];

//new lanzous variables
$new_lanzous_path = $_POST["new-lanzous-path"];
$new_lanzous_name = $_POST["new-lanzous-name"];
$new_lanzous_size = $_POST["new-lanzous-size"];


//upload variables
$upload_type = $_POST["upload-type"];
$upload_path = $_POST["upload-path"];

//file deteils variable
$file = $_REQUEST["file"];
?>
<?php
//login process.uses php session so will auto log out when closing browser.
session_start();
if (isset($_POST['password'])) {
    $password = trim($_POST['password']);
    if ($password === $admin_password) {    //verify password
        $_SESSION["logged_in"] = true;
    } else {
        $_SESSION["logged_in"] = false;
    }
    $_POST = array();
    header("refresh:0;url='index.php?option=analytics'");
}

//important function. Require login
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    //file upload process
    if ($_FILES["file"]["size"] != 0) {
        if ($upload_type == 'article') {
            file_upload('article', $upload_path);
            header("refresh:0;url='index.php?option=article'");
        } else if ($upload_type == 'file') {
            file_upload('file', $upload_path);
            header("refresh:0;url='index.php?path=.'");
        } else if ($upload_type == 'img') {
            file_upload('img', '');
            header("refresh:0;url='index.php?option=article'");
        }
    }
    if ($upload_type == 'lanzous') {
        $text =
            '>-path=' . $new_lanzous_path . PHP_EOL .
            '>-name=' . $new_lanzous_name . PHP_EOL .
            '>-size=' . $new_lanzous_size . PHP_EOL;
        $handle = fopen("file/" . $upload_path . '/' . urlencode($new_lanzous_name) . ".lanzous", "w");   //in case of special characters like / 
        fwrite($handle, $text);
        fclose($handle);
        header("refresh:0;url='index.php?path=.'");
    }
    if ($delete == 1) {
        unlink($article);
        echo '<script>alert("文章删除成功")</script>';
        header("refresh:0;url='index.php?option=article'");
    }
    if ($delete == 2) {
        unlink($path);
        echo '<script>alert("删除成功")</script>';
        header("refresh:0;url='index.php?path=.'");
    }

    //edit article process
    if ($article_edit_type == "1") {
        file_put_contents($article_path, $article_content);
        $article_edit_type = "0";
        echo '<script>alert("编辑成功，即将跳转至文章")</script>';
        header("refresh:0;url='index.php?article=" . urlencode($article_path) . "'");
    }

    //new article process
    if (isset($new_article_content)) {
        $new_article = '--------' . PHP_EOL .
            '>-title=' . $new_article_title . PHP_EOL .
            '>-class=' . $new_article_class . PHP_EOL .
            '>-desc=' . $new_article_desc . PHP_EOL .
            '>-desimg=' . $new_article_desimg . PHP_EOL .
            '>-author=' . $new_article_author . PHP_EOL .
            '>-time=' . date("Y.m.d H:i:s") . PHP_EOL .
            '--------' . PHP_EOL .
            $new_article_content . PHP_EOL .
            '--------';
        $new_article_title = str_replace("/", "[slash]", $new_article_title);   //replace special characters
        $handle = fopen("article/" . $new_article_title . ".md", "w");
        fwrite($handle, $new_article);
        fclose($handle);
        echo '<script>alert("发布成功，即将跳转至文章")</script>';
        header("refresh:0;url='index.php?article=article/" . urlencode($new_article_title) . ".md'");
    }

    //backup process
    if (isset($backup) && $backup != '') {
        $file_name = backup($backup);
        $file = fopen($file_name, 'rb');

        Header("Content-type: application/octet-stream");                   //inform broser that this is a stream file
        Header("Accept-Ranges: bytes");                                     //accept range
        Header("Accept-Length: " . filesize($file_name));                   //data length
        Header("Content-Disposition: attachment; filename=" . $file_name);  //tell broser this is a downloadable file and filename

        echo fread($file, filesize($file_name));        //read file content and echo file stream to broser
        fclose($file);
        unlink($file_name);                             //remove backup file in case of file leak

        header("refresh:0;url='index.php?option=analytics'");
    }

    if (isset($option) && $option == 'article_repair') {
        $item = get_file('article', '*.md', 0, 0);
        for ($i = 0; $i < count($item); $i++) {
            article_fixer($item[$i]["path"]);      //not avaliable now. Because it seems can`t work fine now
        }
        echo '<script>alert("repair success.")</script>';
        header("refresh:0;url='index.php?option=extra'");
    }
}

//normal function. Don`t require login
//send comment process
if (isset($comm_article) && isset($comm_context) && isset($comm_id)) {
    if ($comm_id != '' && $comm_context != '') {
        $comment =  PHP_EOL . '>--<' . PHP_EOL .
            '>-id=' . $comm_id . PHP_EOL .
            '>-date=' . date("Y.m.d H:i:s") . PHP_EOL .
            '>-context=' . $comm_context . PHP_EOL;
        file_put_contents($comm_article, $comment, FILE_APPEND);
        echo '<script>alert("评论成功")</script>';
        header("refresh:0;url='index.php?article=" . urlencode($comm_article) . "'");
    } else        //ignore empty comment
    {
        echo '<script>alert("评论失败：内容与id不能为空")</script>';
        header("refresh:0;url='index.php?article=" . urlencode($comm_article) . "'");
    }
}

//search process
if (isset($search) && $search != '') {
    $result = search($search);
}

//file detail process
if (isset($file) && $file != '') {
    $detail = file_detail($file);
}
?>

<head>
    <?php
    echo "<title>";
    if ($result != '')
        echo '搜索结果 | ' . $site_name;
    else if ($detail != '')
        echo '文件详情 | ' . $site_name;
    else if ($option == 'login')
        echo '登录 | ' . $site_name;
    else if ($category != '')
        echo $category . ' | ' . $site_name;
    else if ($path != '')
        echo '杂物箱 | ' . $site_name;
    else
        head($site_name, $title);
    echo "</title>" . PHP_EOL;
    $logged_in = false;
    session_start();
    ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <link rel="stylesheet" href="css/repo.css">
</head>

<body>
    <div id="container">
        <div id="header">
            <div id="left">
                <div><img src="img/title.png" class="logo" /></div>
                <div>
                    <h3 id="sub-title"><?php echo $sub_title; ?>
                    </h3>
                </div>
            </div>
            <div id="right">
                <div><input type="text" id="search" <?php if ($search == '') echo 'value=""';
                                                    else echo 'value="' . $search . '"'; ?>placeholder="找找看？" onkeydown="search(arguments[0])" /></div>
                <div>
                    <?php
                    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true && $option != '') {
                        echo '<button onClick="window.location.href=\'index.php\'">返回主页</button>';
                    }
                    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true && $path != '') {
                        echo '<button onClick=toggle("upload_file")>上传文件</button>';
                        echo '<button onClick=toggle("create_lanzous")>上传链接</button>';
                    }
                    if (isset($_SESSION["logged_in"]) && ($_SESSION["logged_in"] === true) && ($option == '')) {
                        echo boolval($enable_login_entrance) ? '<button onClick="window.location.href=\'index.php?option=analytics\'">管理</button>' : '';
                    } elseif (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true && $option == 'article') {
                        echo '<button onClick=toggle("upload_article")>上传文章</button>';
                        echo '<button onClick=toggle("upload_img")>上传题图</button>';
                    } elseif (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] === false) {
                        echo boolval($enable_login_entrance) ? '<button onClick=toggle("login")>管理</button>' : '';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
            if ($option != '') {
                if ($option == 'login') {
                    echo '<div id="main">' .
                        '<h2>你已登陆，无需再次登录，即将跳转设置首页</h2>' .
                        '</div>';
                    header("refresh:3;url='index.php?option=analytics'");
                } else {
                    echo '<div id="nav">';
                    navigator("setting_nav", $enable_unsort);
                    echo '</div>';
                    echo '<div id="main">';
                    setting($option);
                    echo '</div>';
                }
            } elseif ($path != '') {
                echo '<div id="nav">';
                navigator($all_category, $enable_unsort);
                echo '</div>';
                echo '<div id="main">';
                unsort($path, true);
                echo '</div>';
            } elseif ($article != '') {
                echo '<div id="nav">';
                navigator($all_category, $enable_unsort);
                echo '</div>';
                echo '<div id="main">';
                md_to_html($article);
                echo '</div>';
            } elseif ($search != '') {
                echo '<div id="nav">';
                navigator($all_category, $enable_unsort);
                echo '</div>';
                echo '<div id="main">';
                if ($result["article"]["str"] == array() && $result["file"]["str"] == array())
                    echo '<h2>什么都没有找到（' . '</h2>' . '</div>';
                else {
                    echo '<h3>搜索结果</h3><table style="width:100%;">';
                    if ($result["article"]["str"] != array())
                        for ($i = 0; $i < count($result["article"]["str"]); $i++) {
                            echo '<tr>' .
                                '<td><a href="index.php?article=' . urlencode($result["article"]["path"][$i]) . '" target="_blank">' . $result["article"]["str"][$i] . '</a></td>' .
                                '</tr>';
                        }
                    if ($result["file"]["str"] != array())
                        for ($i = 0; $i < count($result["file"]["str"]); $i++) {
                            if (fnmatch('*.lanzous', $result["file"]["str"][$i])) {
                                echo '<tr>' .
                                    '<td><a href="' . explode("=", explode(">-", file_get_contents($result["file"]["path"][$i]))[1])[1] . '" target="_blank">' . $result["file"]["str"][$i] . '</a></td>' .
                                    '</tr>';
                            } else {
                                echo '<tr>' .
                                    '<td><a href="index.php?file=' . urlencode($result["file"]["path"][$i]) . '" target="_blank">' . $result["file"]["str"][$i] . '</a></td>' .
                                    '</tr>';
                            }
                        }
                    echo '</table></div>';
                }
            } elseif ($detail != '') {
                echo '<div id="nav">';
                navigator($all_category, $enable_unsort);
                echo '</div>';
                echo '<div id="main">';
                echo '<div class="topic">' .
                    '<h3>文件：' . $detail["basename"] . '</h3>' .
                    '<div class="item"></hr>' .
                    '目录名称: ' . $detail["dirname"] . '<br/>' .
                    '文件类型：' . $detail["filetype"] . '<br/>' .
                    '文件大小：' . $detail["filesize"] . '<br/>' .
                    '文件创建时间：' . $detail["filectime"] . '<br/>' .
                    '文件修改时间：' . $detail["filemtime"] . '<br/>' .
                    '最后访问时间：' . $detail["fileatime"] . '<br/><hr/>' .
                    '</div>
                        <div class="item">
                            <h3>操作</h3>
                            <div style="display:flex;justify-content:space-around;align-items:center;">
                            <a href="' . $file . '" target="_blank" download="" class="a-button">点击下载</a>
                            <button onClick="window.location.href=\'' . $file . '\'">点击预览</button>
                            </div><hr/><br/>
                            注意：文件预览仅对图片，音乐（如果浏览器支持，如Chrome等）有效。其他文件类型会直接下载。
                        </div>
                     </div>';
                echo '</div>';
            } else {
                echo '<div id="nav">';
                navigator($all_category, $enable_unsort);
                echo '</div>';
                echo '<div id="main">';
                show_article($category);
                echo '</div>';
            }
        } else {
            if ($path != '') {
                echo '<div id="nav">';
                navigator($all_category, $enable_unsort);
                echo '</div>';
                echo '<div id="main">';
                unsort($path, false);
                echo '</div>';
            } elseif ($option == 'login') {
                echo '
                <div id="login" class="float-window" style="width:auto;">
                    <h3>登录</h3>
                    <form method="post" action="index.php">
                        <p>输入密码</p>
                        <input type="password" name="password">
                        <input type="submit" name="login" value="登陆">
                    </form>
                </div>' .
                    '<script>' .
                    'document.getElementById("header").style.display = "none";' .
                    '</script>';
            } elseif ($article != '') {
                echo '<div id="nav">';
                navigator($all_category, $enable_unsort);
                echo '</div>';
                echo '<div id="main">';
                md_to_html($article);
                echo '</div>';
            } elseif ($search != '') {
                echo '<div id="nav">';
                navigator($all_category, $enable_unsort);
                echo '</div>';
                echo '<div id="main">';
                if ($result["article"]["str"] == array() && $result["file"]["str"] == array())
                    echo '<h2>什么都没有找到（' . '</h2>' . '</div>';
                else {
                    echo '<h3>搜索结果</h3><table style="width:100%;">';
                    if ($result["article"]["str"] != array())
                        for ($i = 0; $i < count($result["article"]["str"]); $i++) {
                            echo '<tr>' .
                                '<td><a href="index.php?article=' . urlencode($result["article"]["path"][$i]) . '" target="_blank">' . $result["article"]["str"][$i] . '</a></td>' .
                                '</tr>';
                        }
                    if ($result["file"]["str"] != array())
                        for ($i = 0; $i < count($result["file"]["str"]); $i++) {
                            if (fnmatch('*.lanzous', $result["file"]["str"][$i])) {
                                echo '<tr>' .
                                    '<td><a href="' . explode("=", explode(">-", file_get_contents($result["file"]["path"][$i]))[1])[1] . '" target="_blank">' . $result["file"]["str"][$i] . '</a></td>' .
                                    '</tr>';
                            } else {
                                echo '<tr>' .
                                    '<td><a href="index.php?file=' . urlencode($result["file"]["path"][$i]) . '" target="_blank">' . $result["file"]["str"][$i] . '</a></td>' .
                                    '</tr>';
                            }
                        }
                    echo '</table></div>';
                }
            } elseif ($detail != '') {
                echo '<div id="nav">';
                navigator($all_category, $enable_unsort);
                echo '</div>';
                echo '<div id="main">';
                echo '<div class="topic">' .
                    '<h3>文件：' . $detail["basename"] . '</h3>' .
                    '<div class="item"></hr>' .
                    '目录名称: ' . $detail["dirname"] . '<br/>' .
                    '文件类型：' . $detail["filetype"] . '<br/>' .
                    '文件大小：' . $detail["filesize"] . '<br/>' .
                    '文件创建时间：' . $detail["filectime"] . '<br/>' .
                    '文件修改时间：' . $detail["filemtime"] . '<br/>' .
                    '最后访问时间：' . $detail["fileatime"] . '<br/><hr/>' .
                    '</div>
                        <div class="item">
                            <h3>操作</h3>
                            <div style="display:flex;justify-content:space-around;align-items:center;">
                            <a href="' . $file . '" target="_blank" download="" class="a-button">点击下载</a>
                            <button onClick="window.location.href=\'' . $file . '\'">点击预览</button>
                            </div><hr/><br/>
                            注意：文件预览仅对图片，音乐（如果浏览器支持，如Chrome等）有效。其他文件类型会直接下载。
                        </div>
                     </div>';
                echo '</div>';
            } else {
                echo '<div id="nav">';
                navigator($all_category, $enable_unsort);
                echo '</div>';
                echo '<div id="main">';
                show_article($category);
                echo '</div>';
            }
        }
        ?>

        <?php
        if ($option != 'login') {
            echo '<div id="footer">';
            footer($time_begin, $site_footer);
            echo '</div>';
        }
        ?>
    </div>
    <div id="bg"></div>
    <?php
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true && $option == 'article') {
        echo '
        <div id="upload_article" class="float-window" style="display:none;">
            <button onClick=toggle("upload_article") style="align-self:flex-end;">关闭</button>
            <form action="index.php" method="post" enctype="multipart/form-data">
                <input type="file" name="file" id="file">
                <input type="hidden" name="upload-type" value="article">
                <input type="submit" name="submit" value="上传文章">
            </form>
        </div>
        <div id="upload_img" class="float-window" style="display:none;">
            <button onClick=toggle("upload_img") style="align-self:flex-end;">关闭</button>
            <form action="index.php" method="post" enctype="multipart/form-data">
                <input type="file" name="file" id="file">
                <input type="hidden" name="upload-type" value="img">
                <input type="submit" name="submit" value="上传题图">
            </form>
        </div>
        <div id="editor-new-article" class="float-window" style="display:none;">
            <button onClick=toggle("editor-new-article") style="align-self:flex-end;">关闭</button>
            <form action="index.php" method="post">
                标题：<input type="text" name="new-article-title" style="width:50vw;">
                分类：<input type="text" name="new-article-class" style="width:50vw;">
                摘要：<input type="text" name="new-article-desc" style="width:50vw;">
                题图链接：<input type="text" name="new-article-desimg" style="width:50vw;">
                作者：<input type="text" name="new-article-author" style="width:50vw;">
                正文：<textarea name="new-article-content" style="width:50vw;height:25vh;margin:auto;"></textarea>
                <input type="submit" name="submit" value="发布" style="width:50vw;">
            </form>
        </div>';
    }
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true && $path !== '') {
        echo '
        <div id="upload_file" class="float-window" style="display:none;">
            <button onClick=toggle("upload_file") style="align-self:flex-end;">关闭</button>
            <form action="index.php" method="post" enctype="multipart/form-data">
                <input type="file" name="file" id="file">
                <input type="hidden" name="upload-type" value="file">
                <input type="hidden" name="upload-path" value="' . $path . '">
                <input type="submit" name="submit" value="上传文件">
            </form>
        </div>';
        echo '
        <div id="create_lanzous" class="float-window" style="display:none;">
            <button onClick=toggle("create_lanzous") style="align-self:flex-end;">关闭</button>
            <form action="index.php" method="post">
            地址：<input type="text" name="new-lanzous-path">
            名称：<input type="text" name="new-lanzous-name">
            大小：<input type="text" name="new-lanzous-size">
                <input type="hidden" name="upload-type" value="lanzous">
                <input type="hidden" name="upload-path" value="' . $path . '">
                <input type="submit" name="submit" value="上传链接" style="width:50vw;">
            </form>
        </div>';
    }
    if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] === false || $option != 'login') {
        echo boolval($enable_login_entrance) ?
            '<div id="login" class="float-window" style="display:none;">
        <button onClick=toggle("login") style="align-self:flex-end;">关闭</button>
        <form method="post" action="index.php">
            <p>输入密码</p>
            <input type="password" name="password">
            <input type="submit" name="login" value="登陆">
        </form>
    </div>' : '';
    }
    if ($article != '') {
        echo '
        <div id="send-comment" class="float-window" style="display:none;">
            <button onClick=toggle("send-comment") style="align-self:flex-end;">关闭</button>
            <form action="index.php" method="post">
                <input type="text" name="comment-id" style="width:16rem;margin-bottom:0.5rem;" placeholder="昵称">
                <textarea name="comment-context" style="width:16rem;height:4rem;margin:auto;" placeholder="评论内容"></textarea>
                <input type="hidden" name="comment-article" value="' . $article . '">
                <input type="submit" name="comment-send" value="发送">
            </form>
        </div>';
    }
    ?>
</body>

<script>
    function toggle(id) {
        if (document.getElementById(id).style.display == "") {
            document.getElementById(id).style.display = "none";
            document.getElementById("container").style.display = "flex";
        } else {
            document.getElementById(id).style.display = "";
            document.getElementById("container").style.display = "none";
        }
    }

    function toggle_editor(id) {
        if (document.getElementById("editor-" + id).style.display == "")
            document.getElementById("editor-" + id).style.display = "none";
        else
            document.getElementById("editor-" + id).style.display = "";
    }

    function confirm(id) {
        if (document.getElementById("confirm-" + id).style.display == "") {
            document.getElementById("confirm-" + id).style.display = "none";
            document.getElementById("yes-" + id).style.display = "";
            document.getElementById("no-" + id).style.display = "";
        } else {
            document.getElementById("confirm-" + id).style.display = "";
            document.getElementById("yes-" + id).style.display = "none";
            document.getElementById("no-" + id).style.display = "none";
        }
    }

    function search(e) {
        var e = e || window.event;
        if (e.keyCode == 13) {
            var val = document.getElementById("search").value;
            window.location.href = "index.php?search=" + encodeURIComponent(val);
        }
    }
</script>

</html>
