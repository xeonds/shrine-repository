<?php
class AuthCode
{
    private $type;
    private $func;

    /**
     * 生成永远唯一的密钥码
     * sha512(返回128位) sha384(返回96位) sha256(返回64位) md5(返回32位)
     * 还有很多Hash函数......
     * @author xiaochaun
     * @param int $type 返回格式：0大小写混合  1全大写  2全小写
     * @param string $func 启用算法：
     * @return string
     */
    public function __construct($type, $func = 'sha512')
    {
        $this->type = $type;
        $this->func = $func;
    }

    public function getCode()
    {
        return $this->gen_auth_code();
    }

    private function gen_auth_code()
    {
        $uid = md5(uniqid(rand(), true) . microtime());
        $hash = hash($this->func, $uid);
        $arr = str_split($hash);
        foreach ($arr as $v)
        {
            if ($this->type == 0)
            {
                $newArr[] = empty(rand(0, 1)) ? strtoupper($v) : $v;
            }
            if ($this->type == 1)
            {
                $newArr[] = strtoupper($v);
            }
            if ($this->type == 2)
            {
                $newArr[] = $v;
            }
        }
        return implode('', $newArr);
    }
}
