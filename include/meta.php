<?php
include_once('include/util/AuthCode.php');

/**
 * Meta: Basic data format for meta system
 * @param string $time Create time of meta
 * @param string $type Type of meta
 * @param array $tag Tags array of meta
 * @param int $uid Creator of this meta
 */
class Meta {
    private $id, $time, $type, $tag, $uid;

    public function __construct(string $Time, string $Type, array $Tag, int $Uid) {
        $this->id = (new AuthCode(0))->getCode();
        $this->time = $Time;
        $this->type = $Type;
        $this->tag = $Tag;
        $this->uid = $Uid;
    }

    public function get() {
        return array('id' => $this->id, 'time' => $this->time, 'type' => $this->type, 'tag' => $this->tag, 'uid' => $this->uid);
    }
}

/**
 * Text: Text meta
 * @param Meta $meta The meta data of this text
 * @param string $title The title of text
 * @param string $content The content of text
 */
class Text extends Meta {
    private $title, $content;

    public function __construct(Meta $meta, $t, $c) {
        $meta = $meta->get();
        parent::__construct($meta['time'], $meta['type'], $meta['tag'], $meta['uid']);
        $this->title = $t;
        $this->content = $c;
    }

    public function get() {
        return array('title' => $this->title, 'content' => $this->content) + parent::get();
    }

    /**
     * @return (array)$result["head"] => {["title"],["class"],["desc"],["desimg"],["author"],["date"]}
     *                       ["content"]
     *                       ["comment"][$i]{["id"],["date"],["context"]}
     *                       ["text"]
     *                       [1][2][3]   
     */
    public static function article_parser($path, $flag = '') {
        $text = file_get_contents($path);
        $seperated_text = explode("--------", $text);
        $result = [];

        if (count($seperated_text) == 4) {
            if ($flag == '') {
                $head = $seperated_text[1];

                //article head parse
                $head_arr = explode(">-", $head);
                foreach ($head_arr as $key => $val) {
                    $val_arr = explode("=", $val);
                    $result["head"][$val_arr[0]] = substr($val, strlen($val_arr[0]) + 1);
                }
                $result["content"] = $seperated_text[2];
                //comment parse
                $comments_arr = explode(">--<", $seperated_text[3]);
                $result["comment"] = [];
                foreach ($comments_arr as $key => $val) {
                    $comment_arr = explode(">-", $val);
                    $tmp_arr = [];
                    foreach ($comment_arr as $_key => $_val) {
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
            } else if ($flag == HTML_ENTITIES) {
                $head = $seperated_text[1];

                //article head parse
                $head_arr = explode(">-", $head);
                foreach ($head_arr as $key => $val) {
                    $val_arr = explode("=", $val);
                    $result["head"][$val_arr[0]] = htmlentities(substr($val, strlen($val_arr[0]) + 1));
                }
                $result["content"] = htmlentities($seperated_text[2]);
                //comment parse
                $comments_arr = explode(">--<", $seperated_text[3]);
                $result["comment"] = [];
                foreach ($comments_arr as $key => $val) {
                    $comment_arr = explode(">-", $val);
                    $tmp_arr = [];
                    foreach ($comment_arr as $_key => $_val) {
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
        } else {       //fix article format
            return '错误：文章格式错误，请使用高级设置>文章修复工具进行修复。';
        }
    }

    public static function article_fixer($path) {
        //read content
        $text = file_get_contents($path);
        $seperated_text = explode("--------", $text);
        //article format detector: Detect whether an article is from hexo or in normal format.
        if (count($seperated_text) == 0) {          //file format is hexo markdown
            //converter
            $result = '--------' . PHP_EOL;
            $text_arr = explode("---", $text, 3);
            $head_arr = explode(PHP_EOL, $text_arr[1]);
            foreach ($head_arr as $val) {
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
                $result = '--------'
                    . $seperated_text[1]
                    . '>-date=' . date("Y.m.d H:i:s")
                    . '--------'
                    . $seperated_text[2]
                    . '--------'
                    . $seperated_text[3];
                file_put_contents($path, $result);
            } else if (stristr($seperated_text[3], '>-context')) {
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

    public static function article_structor($param) {
        $param_list = array('title', 'class', 'desc', 'desimg', 'author', 'date', 'path', 'content', 3);
        $article = Text::article_parser($param["path"]);
        //If parameter is not set, it means not edit it. So get it from origin article.
        foreach ($param_list as $val) {
            if (!isset($param[$val])) {
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

    public static function search($text) {
        $article = File::get_file("article", "*.md", 0, 0);
        $file = File::get_file("file", "*.*", 0, 3);
        $result = [];

        //search keyword in articles
        for ($i = 0; $i < count($article); $i++) {
            if (stristr(file_get_contents($article[$i]["path"]), $text)) {
                $result["article"]["str"][] = Text::article_parser($article[$i]["path"])["head"]["title"];
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

    public static function analyze_tags($str, $tags) {
        $str = implode("", $str);
        $result = [];
        foreach ($tags as $key => $val) {
            $tag_list[] = $val;
        }
        if ($str != '') {
            for ($i = 0; $i < count($tag_list); $i++) {
                $result[] = array("num" => substr_count($str, $tag_list[$i]), "tag" => $tag_list[$i]);
            }
        } else {
            $result[] = array("num" => 0, "tag" => "所有文章");
        }

        return $result;
    }

    public static function desense_char($str) {
        return str_replace("/", "[slash]", $str);
    }

    public static function date_to_int($str) {
        if (strstr($str, "-")) {
            $arr = explode("-", $str);
            $arr2 = explode(" ", $arr[2]);
            $arr3 = explode(":", $arr2[1]);

            $res = $arr[0] . $arr[1] . $arr2[0] . $arr3[0] . $arr3[1] . $arr3[2];
        } else {
            $arr = explode(".", $str);
            $arr2 = explode(" ", $arr[2]);
            $arr3 = explode(":", $arr2[1]);

            $res = $arr[0] . $arr[1] . $arr2[0] . $arr3[0] . $arr3[1] . $arr3[2];
        }

        return $res;
    }
}

/**
 * File: Meta file
 * @param Meta $meta The meta data of this meta file
 * @param string $fileName The filename of this meta file
 */
class File extends Meta {
    private $fileName;

    public function __construct(Meta $meta, $i) {
        $meta = $meta->get();
        parent::__construct($meta['time'], $meta['type'], $meta['tag'], $meta['uid']);
        $this->fileName = $i;
    }

    public function get() {
        return array('fileName' => $this->fileName) + parent::get();
    }

    public static function delete($path, $tip, $headto) {
        unlink($path);
        echo '<script>alert("' . $tip . '")</script>';
        header("refresh:0;url='" . $headto . "'");
    }

    public static function upload($path, $tip, $headto) {
        if ($_FILES["file"]["size"] != 0) {
            if ($_FILES["file"]["error"] <= 0) {
                move_uploaded_file($_FILES["file"]["tmp_name"], $path . $_FILES["file"]["name"]);
                echo "<script>alert('" . $tip . "[path:" . $path . "]')</script>";
                header("refresh:0;url='" . $headto . "'");
            } else {
                echo "<script>alert('上传失败，错误代码：" . $_FILES["file"]["error"] . "')</script>";
                header("refresh:0;url='" . $headto . "'");
            }
        }
    }

    public static function write_file($path, $data, $tip, $headto,  $mode = 'overwrite') {
        if (file_exists($path)) {
            $mode == 'overwrite' ? file_put_contents($path,  $data) : file_put_contents($path, $data, FILE_APPEND);
        } else {
            $handle = fopen($path, "w");   //in case of special characters like / 
            fwrite($handle, $data);
            fclose($handle);
        }
        echo "<script>alert('" . $tip . "')</script>";
        header("refresh:0;url='" . $headto . "'");
    }

    public static function file_detail($path) {
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

    public static function get_size($path) {
        $filesize = filesize(trim($path));
        if ($filesize >= 1073741824) {
            $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';
        } elseif ($filesize >= 1048576) {
            $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';
        } elseif ($filesize >= 1024) {
            $filesize = round($filesize / 1024 * 100) / 100 . ' KB';
        } else {
            $filesize = $filesize . ' Byte';
        }

        return $filesize;
    }

    public static function get_file($path, $filetype, $min_depth, $max_depth) {
        if (!file_exists($path)) {
            return [];
        }
        $file = [];
        if ($dir = opendir($path)) {                         //check whether the dir is correctly opened
            while (false !== $item = readdir($dir)) {        //search each item and check whether it's a file or a directory
                $nextPath = $path . '/' . $item;
                if ($item != '.' && $item != '..' && is_dir($nextPath) && $max_depth > 0) {
                    $file = array_merge($file, File::get_file($nextPath, $filetype, $min_depth - 1, $max_depth - 1));
                } elseif ($item != '.' && $item != '..' && fnmatch($filetype, $item) && $min_depth <= 0) {
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

    public static function get_folder($path, $min_depth, $max_depth) {
        if (!file_exists($path)) {
            return [];
        }
        $folder = [];
        if ($dir = opendir($path)) {                        //check whether the dir is correctly opened
            while (false !== $item = readdir($dir)) {        //search each item and check whether it's a file or a directory
                $nextPath = $path . '/' . $item;
                if ($item != '.' && $item != '..' && is_dir($nextPath) && $max_depth > 0) {
                    $folder = array_merge($folder, File::get_folder($nextPath, $min_depth - 1, $max_depth - 1));
                } elseif ($item != '.' && $item != '..' && is_dir($nextPath) && $min_depth <= 0) {
                    $folder["path"][] = $nextPath;
                    $folder["name"][] = $item;
                }
            }
        }
        return $folder;
    }

    public static function backup($backup_type) {
        $zip = new ZipArchive;

        switch ($backup_type) {
            case 'article':
                $fname = 'article-archive-' . date('Y-m-d-H-i-s') . '.zip';
                if ($zip->open($fname, ZipArchive::CREATE) === true) {
                    $res = File::get_file('article', '*.*', 0, 0);
                    for ($i = 0; $i < count($res); $i++) {
                        $zip->addFile($res[$i]['path']);
                    }
                    $zip->close();
                    return $fname;
                }
                break;

            case 'setting':
                $fname = 'setting-archive-' . date('Y-m-d-H-i-s') . '.zip';
                if ($zip->open($fname, ZipArchive::CREATE) === true) {
                    $zip->addFile('lib/Database/setting.json');
                    $zip->close();
                    return $fname;
                }
                break;

            case 'file':
                $fname = 'file-archive-' . date('Y-m-d-H-i-s') . '.zip';
                if ($zip->open($fname, ZipArchive::CREATE) === true) {
                    $res = File::get_file('file', '*.*', 0, 3);
                    for ($i = 0; $i < count($res); $i++) {
                        $zip->addFile($res[$i]['path']);
                    }
                    $zip->close();
                    return $fname;
                }
                break;

            case 'msg':
                $fname = 'msg-archive-' . date('Y-m-d-H-i-s') . '.zip';
                if ($zip->open($fname, ZipArchive::CREATE) === true) {
                    $zip->addFile('lib/Database/msg.json');
                    $zip->close();
                    return $fname;
                }
                break;
        }
    }

    public static function switch_md($path) {
        $res = File::file_detail($path);
        if ($res["extension"] == 'md') {
            rename($path, 'article/' . $res["filename"] . '.notavaliable');
        } else {
            rename($path, 'article/' . $res["filename"] . '.md');
        }
    }

    public static function article_arrange($item) {
        foreach ($item as $key => $val) {
            $tmp[$key] = Text::article_parser($val['path'])["head"]["title"];
        }
        if (is_array($tmp)) {
            array_multisort($tmp, SORT_DESC, $item);
        }
        foreach ($item as $key => $val) {
            $tmp[$key] = Text::date_to_int(Text::article_parser($val['path'])["head"]["date"]);
        }
        if (is_array($tmp)) {
            array_multisort($tmp, SORT_DESC, $item);
        }

        return $item;
    }
}

/**
 * MetaArray: A set of structured metas
 * @param Meta $meta Meta data of this meta
 * @param array $metas Metas that constitute this meta array
 */
class MetaArray extends Meta {
    private $metaArray;

    public function __construct(Meta $meta, array $a) {
        $meta = $meta->get();
        parent::__construct($meta['time'], $meta['type'], $meta['tag'], $meta['uid']);
        $this->metaArray = $a;
    }

    public function get() {
        return array('metaArray' => $this->metaArray) + parent::get();
    }
}


/**
 * MetaDB: A util class for meta management
 * @method createMeta($data, $id):bool Create meta with $data: array('meta'=>...(,'filePath'=>...,'fileName'=>...))
 * @method deleteMeta(string $meta)
 * @method getList()
 * @method getMeta($metaID)
 * @method modifyMeta($id, $data) This $data is same as the one createMeta() needs.
 */
class MetaDB {
    private $dbPath = "post/meta/";

    public function createMeta(array $data): bool {
        try {
            mkdir($metaPath = $this->dbPath . $data['meta']->get()['id'] . '/');
            switch ($data['meta']->get()['type']) {
                case 'text':
                    break;

                case 'metaArray':
                    break;

                default:
                    move_uploaded_file($data['filePath'], $metaPath . $data['fileName']);
                    break;
            }
            file_put_contents($metaPath . 'meta.json', json_encode($data['meta']->get()));
        } catch (Exception $e) {
            return false;
        } finally {
            return true;
        }
    }

    public function deleteMeta(string $meta_id): bool {
        try {
            while (false !== $item = readdir(opendir($metaPath = $this->dbPath . $meta_id . '/')))
                if ($item != '.' && $item != '..')
                    unlink($metaPath . $item);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getList() {
        if (!file_exists($this->dbPath))
            return [];
        $dir = opendir($this->dbPath);
        while (false !== $item = readdir($dir))
            if ($item != '.' && $item != '..' && file_exists($this->dbPath . $item . '/meta.json'))
                $data[] = array(
                    "metaId" => $item
                ) + $this->getMeta($item);

        return $data;
    }

    public function getMeta($id) {
        $metaData = file_get_contents($this->dbPath . $id . '/meta.json');

        return json_decode($metaData, true);
    }

    public function modifyMeta(string $id, array $data): bool {
        try {
            $metaPath = $this->dbPath . $id . '/';
            switch ($this->getMeta($id)['type']) {
                case 'text':
                    break;

                case 'metaArray':
                    break;

                default:
                    unlink(($metaPath = $this->dbPath . $id . '/') . $this->getMeta($id)['fileName']);
                    move_uploaded_file($data['filePath'], $metaPath . $data['fileName']);
                    break;
            }
            file_put_contents($metaPath . 'meta.json', json_encode($data['meta']->get()));
        } catch (Exception $e) {
            return false;
        } finally {
            return true;
        }
    }
}
