<?php
class Theme {
    public function get_theme_list() {
        $path = 'lib/Database/theme';
        $res = [];

        $folder = File::get_folder($path, 0, 0);
        foreach ($folder['path'] as $item) {
            $res[] = array('name' => json_decode(file_get_contents($item . '/theme.json'), true)['name'], 'path' => $item . '/');
        }

        return $res;
    }
    public function parser($path) {
        $data = json_decode(file_get_contents($path . 'theme.json'), true);
        foreach ($data['file'] as $val) {
            $file_arr = File::get_file($path, "*.$val", 0, 0);
            foreach ($file_arr as $v) {
                if (File::file_detail($path . $v['name'])['extension'] == 'html')
                    $data[$val][File::file_detail($path . $v['name'])['filename']] = file_get_contents($path . $v['name']);
                else
                    $data[$val][File::file_detail($path . $v['name'])['filename']] = $path . $v['name'];
            }
        }

        return $data;
    }
    public function parser_v2($name) {
        $path = 'lib/Database/theme/' . $name . '/';
        $data = json_decode(file_get_contents($path . 'theme.json'), true);
        foreach ($data['file'] as $val) {
            $file_arr = File::get_file($path, "*.$val", 0, 0);
            foreach ($file_arr as $v) {
                if (File::file_detail($path . $v['name'])['extension'] == 'html')
                    $data[$val][File::file_detail($path . $v['name'])['filename']] = $this->template_parser(file_get_contents($path . $v['name']));
                else
                    $data[$val][File::file_detail($path . $v['name'])['filename']] = $path . $v['name'];
            }
        }

        return $data;
    }
    private function template_parser($data) {
        $l = '<template>';
        $r = '</template>';
        $res[0] = $data;

        if (!strstr($data, $l))
            return $res;
        else {
            for ($i = 1; strstr($res[0], $l); $i++) {
                $res[$i] = ($arr = explode($l, strstr($res[0], $r, true)))[count($arr) - 1];
                $res[0] = str_replace($l . $res[$i] . $r, '{' . $i . '}', $res[0]);
            }

            return $res;
        }
    }
    public function install($package_path) {
        $path = 'lib/Database/theme/';

        $zip = new ZipArchive;
        $zip->open($package_path);
        $zip->extractTo($path);
        $zip->close();
    }
    public function uninstall($name) {
        $path = 'lib/Database/theme/';

        if ($this->parser($path . $name . '/')['removable'] == false) {
            echo 'Can`t remove.';
        } else {
            echo 'Removing...';
        }
    }
}
