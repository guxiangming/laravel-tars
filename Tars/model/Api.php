<?php 

namespace App\Tars\model;

use Tars\client\CommunicatorConfig;

class Api
{
    private static $config;

    public static function getConfig($appName, $serverName, $logLevel = 'INFO')
    {
        if (self::$config && self::$config instanceof getConfig) {
            return self::$config;
        }
        $config = new \Tars\client\CommunicatorConfig(); //这里配置的是tars主控地址
        $config->setLocator(config('tarsregistry'));
        $config->setAsyncInvokeTimeout(9000);//超时时间
        $config->setModuleName($appName . '.' . $serverName); //主调名字用于显示再主调上报中。
        $config->setCharsetName("UTF-8"); //字符集
        $config->setLogLevel($logLevel);	//日志级别：`INFO`、`DEBUG`、`WARN`、`ERROR` 默认INFO
        $config->setSocketMode(1); //设置socket model为2 swoole tcp client，1为socket，3为swoole 协程 client
        return self::$config = $config;
    }
}