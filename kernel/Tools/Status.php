<?php
namespace Kernel\Tools;

class Status
{
    public static function getStatus($code)
    {
        $json = file_get_contents("../status.json");
        $res = json_decode($json);
        $key = array_search($code, array_column($res, 'code'));
        return $res[$key];
    }
}