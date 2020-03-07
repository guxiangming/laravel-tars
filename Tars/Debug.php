<?php
/*
 * @Description: 调试变量 \App\Tars\Debug::var_dump();
 * @Author: czm
 * @Date: 2019-04-10 14:44:09
 * @LastEditTime: 2019-04-22 23:44:20
 */
namespace App\Tars;

class Debug
{

    public function __construct()
    {
        
    }

    public static function var_dump($param){
        ob_start();
        var_dump($param);
        $param=ob_get_clean();
        \Log::debug($param);
    }

    public static function dump($param){
        ob_start();
        var_export($param);
        $param=ob_get_clean();
        \Log::debug($param);
    }

    public static function print($param){
        ob_start();
        print($param);
        $param=ob_get_clean();
        \Log::debug($param);
    }

    public static function print_r($param){
        ob_start();
        print_r($param);
        $param=ob_get_clean();
        \Log::debug($param);
    }

    public static function echo($param){
        ob_start();
        echo($param);
        $param=ob_get_clean();
        \Log::debug($param);
    }
}