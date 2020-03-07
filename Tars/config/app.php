<?php
return [
    //http服务网关
    'registries' => [],

    'tarsregistry' => 'tars.tarsregistry.QueryObj@tcp -h 10.29.217.191 -p 17890',

    'log_level' => '',

    'communicator_config_log_level' => 'INFO',
    
    'configName'=>'env',
    
    'services' => [
        'namespaceName' =>  'App\Tars\\',
        'monitorStoreConf' => [
            //使用redis缓存主调上报信息
            //'className' => Tars\monitor\cache\RedisStoreCache::class,
            //'config' => [
            // 'host' => '127.0.0.1',
            // 'port' => 6379,
            // 'password' => ':'
            //],
            //使用swoole_table缓存主调上报信息（默认）
            'className' => Tars\monitor\cache\SwooleTableStoreCache::class,
            'config' => [
                'size' => 40960
            ]
        ],

//        'home-api' => '\App\Tars\servant\PHPTest\PHPServer\obj\TestTafServiceServant', //根据实际情况替换，遵循PSR-4即可，与tars.proto.php配置一致
//        'home-class' => '\App\Tars\impl\TestTafServiceImpl', //根据实际情况替换，遵循PSR-4即可
    ],
    'proto' => [
        'appName' => 'HTMSTHREE', //根据实际情况替换
        'serverName' => 'SROA', //根据实际情况替换
        'objName' => 'obj', //根据实际情况替换
    ],
    'clientproto' => [
        'appName' => 'HTMSTHREE',
        'serverName' => 'API',
        'objName' => 'obj',
        'withServant' => false, //决定是服务端,还是客户端的自动生成
        'tarsFiles' => array(
            './api.tars',
        ),
        'dstPath' => '../src/app/Tars/protocol',
        'namespacePrefix' => 'App\Tars\protocol',
    ],
];
