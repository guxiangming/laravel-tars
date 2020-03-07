<?php

namespace App\Tars;

use Illuminate\Support\Facades\Log;
use Monolog\Logger;
use Dotenv\Dotenv;
use Tars\App;

class Boot
{
    private static $booted = false;

    public static function handle()
    {
        if (!self::$booted) {
            $localConfig = config('tars');
            $deployConfig = App::getTarsConfig();

            $tarsServerConf = $deployConfig['tars']['application']['server'];
            $appName = $tarsServerConf['app'];
            $serverName = $tarsServerConf['server'];
            $localConfig['tarsregistry']=$deployConfig['tars']['application']['client']['locator'];

            if (!empty($localConfig['tarsregistry'])) {
                $logLevel = isset($localConfig['log_level']) ? $localConfig['log_level'] : Logger::INFO;
                $communicatorConfigLogLevel = isset($localConfig['communicator_config_log_level']) ? $localConfig['communicator_config_log_level'] : 'INFO';
                self::fetchConfig($localConfig['tarsregistry'], $appName, $serverName,$localConfig['configName'],$communicatorConfigLogLevel);
                self::setTarsLog($localConfig['tarsregistry'], $appName, $serverName, $logLevel, $communicatorConfigLogLevel);
                config(['tarsregistry'=>$localConfig['tarsregistry']]);
            }
            self::$booted = true;
        }
    }

    private static function fetchConfig($tarsregistry, $appName, $serverName,$configName, $logLevel = 'INFO')
    {
        $configtext = Config::fetch($tarsregistry, $appName, $serverName, $configName,$logLevel);
        $open=fopen(__DIR__."/env.config",'w+');
        $openText=$configtext;
        fwrite($open, $openText);
        fclose($open);
        try {
            (new Dotenv(__DIR__, 'env.config'))->overload();
        } catch (InvalidPathException $e) {
           throw new Exception($e);   
        }
        // if ($configtext) {
        //     $remoteConfig = json_decode($configtext, true);
        //     foreach ($remoteConfig as $configName => $configValue) {
        //         app('config')->set($configName, array_merge(config($configName) ?: [], $configValue));
        //     }
        // }

        // $config = new \Tars\client\CommunicatorConfig();
        //     $localConfig = config('tars');
        //     $config->setLocator($localConfig['tarsregistry']);
        //     $config->setModuleName('QD.UserService');
        //     $config->setSocketMode(3);
        //     return $config;
    }

    private static function setTarsLog($tarsregistry, $appName, $serverName, $level = Logger::INFO, $communicatorConfigLogLevel = 'INFO')
    {
        // $config = Config::communicatorConfig($tarsregistry, $appName, $serverName, $communicatorConfigLogLevel);
        // Log::driver()->pushHandler(new \Tars\log\handler\TarsHandler($config, 'tars.tarslog.LogObj', $level));
    }
}
