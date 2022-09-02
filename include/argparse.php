<?php
class Param {
    private $param = [];

    public function page($page = '') {
        switch ($page) {
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

    public function option($option) {
        switch ($option) {
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
