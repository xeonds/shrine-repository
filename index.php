<!DOCTYPE html>
<html>

<?php
//==============
//Repository-ver-7.0
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
//7.time_begin&time_now:the site's beginning time and current time.
//==============
// Develop Plan
//1.manage page include:analytics[finished],post[finished]/edit[finished]/upload[finished] articles,file manager with su permission,custom bg-img,title-img,css style.
//2.article including commit area using float window.
//3.file detail page using float window.
//4.search page:?search=[kywords].
//5.unsort add su mode:unsort($dir,true/false)
//==============
$site_name = "神社的储物间";
$sub_title = "“我节操放哪去了？”";
$site_footer = "夢想天生";
$enable_unsort = true;
$all_category = array("工具", "游戏", "书籍");
$admin_password = "1672";
$time_begin = 2020;
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
        echo "<button onClick=\"window.location.href='index.php?option=analytics'\">统计</button>" . PHP_EOL;
        echo "<button onClick=\"window.location.href='index.php?option=article'\">文章管理</button>" . PHP_EOL;
        echo "<button onClick=\"window.location.href='index.php?option=custom'\">个性化</button>" . PHP_EOL;
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

function unsort($link)
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
        for ($i = 0; $i < count($item["name"]); $i++) {
            echo "<tr><td><a href='index.php?path=" . $item["path"][$i] . "'>" . $item["name"][$i] . "</a></td>" . "<td>&nbsp;</td>" . "<td>" . getSize(filesize($item["path"][$i])), "</td><td>" . date('Y-m-d', filectime($item["path"][$i])) . "</td></tr>";
        }
        echo '</table></div></div>';
    } else {
        echo '<h3>这里是空的</h3></div></div>';
    }
}

function file_detail($file)
{
}

function setting($option)
{
    echo '<div id="setting">';
    if ($option == "analytics") {
        echo '<h2>统计</h2>';
        $item = get_file("article", "*.md", 0, 0);
        $text = '';
        echo '<p>文章数量:' . count($item["name"]) . '篇</p>';
        for ($x = 0; $x < count($item["name"]); $x++) {
            $text = $text . explode("--------", file_get_contents($item["path"][$x]))[2];
        }
        echo '<p>总字数:' . strlen($text) . '字</p>';
        echo '</div>';
    } elseif ($option == "article") {
        $item = get_file("article", "*.md", 0, 0);
        echo '
        <div style="display:flex;flex-flow:row;align-items:center;justify-content:space-between;">
        <h2>文章管理</h2>
        <button onClick=toggle("editor-new-article") style="margin-right:1rem;">新文章</button>
        </div>
        <table style="width:100%;">
        <thead><td>标题</td><td>大小</td><td>创建时间</td><td>操作</td></thead><tbody>';
        for ($xi = 0; $xi < count($item["name"]); $xi++) {
            $article_title = explode("=", explode(">-", explode("--------", file_get_contents($item["path"][$xi]))[1])[1], 2)[1];
            $article_content = file_get_contents($item["path"][$xi]);
            $article_size = getSize(filesize($item["path"][$xi]));
            $article_date = date('Y-m-d', filectime($item["path"][$xi]));
            echo '
            <tr>
                <td><a href="index.php?article=' . urlencode($item["path"][$xi]) . '" target="_blank">' . $article_title . '</a></td>
                <td>' . $article_size . '</td>
                <td>' . $article_date . '</td>
                <td>
                    <button onClick=toggle_editor(' . $xi . ')>编辑</button>
                    <button id="confirm-' . $xi . '" onClick=confirm("' . $xi . '")>删除</button>
                    <button id="yes-' . $xi . '" onClick="window.location.href=\'index.php?article=' . $item["path"][$xi] . '&delete=1\'" style="display:none;">确认</button>
                    <button id="no-' . $xi . '" onClick=confirm("' . $xi . '") style="display:none;">取消</button></td>
               </td>
            </tr>
            <tr>
                <td colspan="4">
                    <div class="edit-article" id="editor-' . $xi . '" style="margin:auto;padding:0.5rem;border-radius:0.5rem;border:solid 1px white;display:none;">
                        <form action="index.php" method="post">
                            <h3>编辑文章:' . $article_title . '</h3>
                            <textarea name="editor-content" style="width:100%;height:24rem;margin:auto;">' . $article_content . '</textarea>
                            <input type="hidden" name="editor-path" value="' . $item["path"][$xi] . '"/>
                            <input type="hidden" name="editor-type" value="1" />
                            <input type="submit" name="submit" value="确认修改" style="width:12rem;margin:auto;">
                        </form>
                    </div>
                </td>
            </tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
    } elseif ($option == "custom") {
        echo '<h2>个性化</h2><p>敬请期待</p>';
        echo '</div>';
    }
}

function footer($time_begin, $site_name)
{
    echo "<div>&copy;" . $time_begin . "-" . date("Y") . "&nbsp;" . $site_name . "</div>";
}

function md_to_html($file_path)
{
    $parser = new HyperDown\Parser;
    $text = $parser->makeHtml(explode("--------", file_get_contents($file_path))[2]);
    $comments = explode("--------", file_get_contents($file_path))[3];
    $comments_arr = explode(">--<", $comments);

    echo "<div class='topic markdown'>" . $text . "</div><hr>";
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
    if ($dir = opendir($path)) {                    //check whether the dir is correctly opened
        while (false !== $item = readdir($dir)) {        //search each item and check whether it's a file or a directory
            $nextPath = $path . '/' . $item;
            if ($item != '.' && $item != '..' && is_dir($nextPath) && $max_depth > 0) {
                $file = array_merge($file, get_file($nextPath, $filetype, $min_depth - 1, $max_depth - 1));
            } elseif ($item != '.' && $item != '..' && fnmatch($filetype, $item) && $min_depth <= 0) {
                $file["path"][] = $nextPath;
                $file["name"][] = $item;
            }
        }
    }
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

function file_upload()
{
    if ($_FILES["file"]["error"] <= 0) {
        move_uploaded_file($_FILES["file"]["tmp_name"], "article/" . $_FILES["file"]["name"]);
        echo "<script>alert('上传成功')</script>";
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
    for ($i = 0; $i < count($item["path"]); $i++) {
        $text = file_get_contents($item["path"][$i]);
        $text_arr = explode("--------", $text);
        $text_head = $text_arr[1];
        $text_head_arr = explode(">-", $text_head);
        $title = explode("=", $text_head_arr[1], 2)[1];
        $article_category = explode("=", $text_head_arr[2], 2)[1];
        $desc = explode("=", $text_head_arr[3], 2)[1];
        $des_img = explode("=", $text_head_arr[4], 2)[1];
        if ($category == "") {
            echo "<article class='item'>";
            echo "<a href='index.php?article=" . $item["path"][$i] . "'>";
            echo "<h3>" . $title . "</h3>";
            echo "<div class=\"context\">";
            if ($des_img != '') {
                echo "<div class='left'><img src='" . $des_img . "' alt=''></div><div class='right'><p>" . $desc . "</p></div>";
            } else {
                echo "<p>" . $desc . "</p>";
            }
            echo "</div></a></article>";
        } elseif (strstr($article_category, $category)) {
            echo "<article class='item'>" .
                "<a href='index.php?article=" . $item["path"][$i] . "'>" .
                "<h3>" . $title . "</h3>" .
                "<div class=\"context\">";
            if ($des_img != '') {
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
    $title = explode("=", explode(">-", explode("--------", file_get_contents($article))[1])[1], 2)[1];
}
$delete = $_REQUEST["delete"];

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
$new_article_content = $_POST["new-article-content"];
?>
<?php
//login process.uses php session so will auto log out when closing broser.
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

//file upload process
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
    if ($_FILES["file"]["size"] != 0) {
        file_upload();
        header("refresh:0;url='index.php?option=article'");
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
}

//send comment process
if (isset($comm_article) && isset($comm_context) && isset($comm_id)) {
    $comment =  PHP_EOL . '>--<' . PHP_EOL .
        '>-id=' . $comm_id . PHP_EOL .
        '>-date=' . date("Y.m.d H:i:s") . PHP_EOL .
        '>-context=' . $comm_context . PHP_EOL;
    file_put_contents($comm_article, $comment, FILE_APPEND);
    echo '<script>alert("评论成功")</script>';
    header("refresh:0;url='index.php?article=" . urlencode($comm_article) . "'");
}

//edit article process
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true  && $article_edit_type == "1") {
    file_put_contents($article_path, $article_content);
    $article_edit_type = "0";
    echo '<script>alert("编辑成功，即将跳转至文章")</script>';
    header("refresh:0;url='index.php?article=" . urlencode($article_path) . "'");
}

//new article process
if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true  && isset($new_article_content)) {
    $new_article = '--------' . PHP_EOL .
        '>-title=' . $new_article_title . PHP_EOL .
        '>-class=' . $new_article_class . PHP_EOL .
        '>-desc=' . $new_article_desc . PHP_EOL .
        '>-desimg=' . $new_article_desimg . PHP_EOL .
        '--------' . PHP_EOL .
        $new_article_content . PHP_EOL .
        '--------';
    $handle = fopen("article/" . $new_article_title . ".md", "w");
    fwrite($handle, $new_article);
    fclose($handle);
    echo '<script>alert("发布成功，即将跳转至文章")</script>';
    header("refresh:0;url='index.php?article=article/" . urlencode($new_article_title) . ".md'");
}
?>

<head>
    <?php
    echo "<title>";
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
        <div style="position:absolute;top: 50vh;right: 50vw;z-index:5;transform: translateX(50%) translateY(-50%);display:none;">
            <svg>
                <polyline points="1,81 1,41 21,41 21,1 31,1 31,11 1,11 1,1 11,1 11,31 1,31 1,21 41,21 41,1 81,1" style="fill:none;stroke:white;stroke-width:2" />
            </svg>
        </div>
        <div id="header">

            <div id="left">
                <div><img src="img/title.png" class="logo" /></div>
                <div>
                    <h3 id="sub-title"><?php echo $sub_title; ?>
                    </h3>
                </div>
            </div>
            <div id="right">
                <div><input type="text" name="search" placeholder="找找看？" /></div>
                <div>
                    <?php
                    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true && $option != '') {
                        echo '<button onClick="window.location.href=\'index.php\'">返回主页</button>';
                    }
                    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true && $option == '') {
                        echo '<button onClick="window.location.href=\'index.php?option=analytics\'">管理</button>';
                    } elseif (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true && $option == 'article') {
                        echo '<button onClick=toggle("upload")>上传文章</button>';
                    } elseif (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] === false) {
                        echo '<button onClick=toggle("login")>管理</button>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true) {
            if ($option != '') {
                echo '<div id="nav">';
                navigator("setting_nav", $enable_unsort);
                echo '</div>';
                echo '<div id="main">';
                setting($option);
                echo '</div>';
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
            } elseif ($article != '') {
                echo '<div id="nav">';
                navigator($all_category, $enable_unsort);
                echo '</div>';
                echo '<div id="main">';
                md_to_html($article);
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

        <div id="footer">
            <?php
            footer($time_begin, $site_footer);
            ?>
        </div>
    </div>
    <div id="bg"></div>
    <?php
    if (isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true && $option == 'article') {
        echo '
        <div id="upload" class="float-window" style="display:none;">
            <button onClick=toggle("upload") style="align-self:flex-end;">关闭</button>
            <form action="index.php" method="post" enctype="multipart/form-data">
                <input type="file" name="file" id="file">
                <input type="submit" name="submit" value="上传文章">
            </form>
        </div>
        <div id="editor-new-article" class="float-window" style="display:none;">
            <button onClick=toggle("editor-new-article") style="align-self:flex-end;">关闭</button>
            <form action="index.php" method="post">
                标题：<input type="text" name="new-article-title" style="width:50vw;">
                分类：<input type="text" name="new-article-class" style="width:50vw;">
                摘要：<input type="text" name="new-article-desc" style="width:50vw;">
                题图链接：<input type="text" name="new-article-desimg" style="width:50vw;">
                正文：<textarea name="new-article-content" style="width:50vw;height:25vh;margin:auto;"></textarea>
                <input type="submit" name="submit" value="发布" style="width:50vw;">
            </form>
        </div>';
    }
    if (!isset($_SESSION["logged_in"]) || $_SESSION["logged_in"] === false) {
        echo '
    <div id="login" class="float-window" style="display:none;">
        <button onClick=toggle("login") style="align-self:flex-end;">关闭</button>
        <form method="post" action="index.php">
            <p>输入密码</p>
            <input type="password" name="password">
            <input type="submit" name="login" value="登陆">
        </form>
    </div>';
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
</script>

</html>
