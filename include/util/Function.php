<?php

function keyNN(&$array)
{
    $res = key($array);

    next($array);

    return $res;
}
