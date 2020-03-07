<?php
//引导入口
//start tarsphp 启动命令
$config_path = $argv[1];
$pos = strpos($config_path, '--config=');
$config_path = substr($config_path, $pos + 9);
$cmd = strtolower($argv[2]);


include_once __DIR__ . '/vendor/autoload.php';
if(class_exists('\Dotenv\Dotenv')&&file_exists(__DIR__.'/app/Tars/env.config')){
	(new \Dotenv\Dotenv(__DIR__.'/app/Tars' ,'env.config'))->load();
}

if($cmd==='stop'){	

	$class = new \Tars\cmd\Command($cmd, $config_path);
	$class->run();
	  
}else if($cmd==='start'){
	$_SERVER['argv'][0] = $argv[0] = __DIR__ .'/artisan';
	$_SERVER['argv'][1] = $argv[1] = 'tars:entry';
	$_SERVER['argv'][2] = $argv[2] = '--cmd=' . $cmd;
	$_SERVER['argv'][3] = $argv[3] = '--config_path=' . $config_path;
	$_SERVER['argc'] = $argc = count($_SERVER['argv']);
	include_once __DIR__ . '/artisan';
}else{

}





