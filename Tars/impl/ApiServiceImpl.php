<?php


namespace App\Tars\impl;


use App\Tars\protocol\HTMSTHREE\API\obj\classes\CommonInParam;
use App\Tars\protocol\HTMSTHREE\API\obj\classes\CommonOutParam;
use App\Tars\protocol\HTMSTHREE\API\obj\ApiServiceServant;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Illuminate\Http\Request;
use Illuminate\Contracts\Http\Kernel;
use Tars\App as Tapp;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Container\Container;

use App\Http\ModuleClass\LockOrderClass;
class ApiServiceImpl implements ApiServiceServant
{
    protected $providerReset=['App\Providers\AuthServiceProvider','Illuminate\Database\DatabaseServiceProvider'];
    protected $appReset=['auth','auth.driver','db','db.factory','db.connection'];

    // protected $timer=['App\Tars\task\LockOrder'];
    protected static $lock = true;
    /**
     * @return response
     */
    public function __construct()
    { }

    public function buildRequest($inParam)
    {
        $data = [];
        //解析变量
        // $params = json_decode($inParam->params, true);
        parse_str($inParam->params, $params);
        // ob_start();
        // var_dump($params);
        // $cc=ob_get_clean();
        // $a=fopen(__DIR__.'/d.txt','w');
        // fwrite($a, $cc.PHP_EOL);
        // fclose($a);

        $params = is_array($params) ? $params : [];
        $data['header'] = [
            'host' => 'tarsrpc',
            "x-real-ip" => "0.0.0.0",
            "x-forwarded-for" => "0.0.0.0",
            "connection" => "close",
            // "content-length" => "19",
            // "accept" => "application/json, text/javascript, */*; q=0.01",
            "origin" => "tarsrpc",
            "x-requested-with" => "XMLHttpRequest",
            "user-agent" => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36",
            // "content-type" => "application/x-www-form-urlencoded; charset=UTF-8",
            "referer" => "",
            "accept-language" => "zh-CN,zh;q=0.9,en;q=0.8",
        ];
        $data['cookie'] = [];
        //上传文件
        $data['files'] = [];
        $data['get'] = [];
        $data['post'] = [];
        $inParam->method = strtoupper($inParam->method);
        if ($inParam->method == "GET") {
            $data['get'] = $params;
        } else {
            $data['post'] = $params;
        }

        $data['server'] = [
            "query_string" => !empty($data['get']) ? http_build_query($data['get']) : '',
            "request_method" => $inParam->method,
            "request_uri" => $inParam->route,
            "path_info" =>  $inParam->route,
            "server_port" => 80,
            "remote_port" => 80,
            "remote_addr" => "0.0.0.0",
            "server_protocol" => "HTTP/1.1",
        ];
        $request = new \stdClass;
        $request->data = $data;
        return $request;
    }

    public function Controller(CommonInParam $inParam, CommonOutParam &$outParam)
    {
        try {

            if (ApiServiceImpl::$lock) {
                app('tars_push_config');
                //重新保存地址
                $tarsregistry = config('tarsregistry');
                app()->bootstrapWith(['\Illuminate\Foundation\Bootstrap\LoadConfiguration']);
                // 切换上报日志方案 5.6以下
                // app()->configureMonologUsing(function($monolog){ 
                //     $monolog->pushHandler(new App\Tars\TarsLogHandler());
                // });
                //切换log上报方案
                config(['logging.default' => 'stderr', 'logging.channels.stderr.handler' => 'App\Tars\TarsLogHandler']);
                config(['tarsregistry' => $tarsregistry]);
                // $tasks=Tapp::getSwooleInstance();
                //     foreach($this->timer as $v){
                //         $tasks->task(new $v());
                // }
                //定时任务区
                \swoole_timer_tick(1000*60*60, function () {//小时
                   LockOrderClass::SystemLockOrder();
               });
               \swoole_timer_tick(1000*60*5, function () {//5分钟
                   LockOrderClass::SystemAuditCost();
               });
               \swoole_timer_tick(1000*60*60, function () {//小时
                   LockOrderClass::SystemLockInsurance();
               });
               \swoole_timer_tick(1000*60*60*24, function () {//天
                    LockOrderClass::SystemCheckCarrierStatus();
                });

               
                ApiServiceImpl::$lock = false;
            }

            //伪造swoole http request
            $request = $this->buildRequest($inParam);
            // $this->httpKernel = app()->make(Kernel::class);
            // $this->httpKernel->bootstrap();
            $get = isset($request->data['get']) ? $request->data['get'] : [];
            $post = is_array($request->data['post']) ? $request->data['post'] : [];
            $cookie = isset($request->data['cookie']) ? $request->data['cookie'] : [];
            $files = isset($request->data['files']) ? $request->data['files'] : [];
            $header = isset($request->data['header']) ? $request->data['header'] : [];
            $server = isset($request->data['server']) ? $request->data['server'] : [];
            $server = ApiServiceImpl::transformServerParameters($server, $header);
            global $_GET, $_POST, $_SERVER;
            $_GET = &$get;
            $_POST = &$post;
            $_COOKIE = &$cookie;
            $_SERVER = &$server;
            $_FILES = &$files;

            //全局变量配置
            // $content = $request->data['post'] ?(is_array($request->data['post']) ? http_build_query($request->data['post']) : $request->data['post']) : 
            //     null;

            //写入request信息
            $kernel = app()->make(\Illuminate\Contracts\Http\Kernel::class);

            $response = $kernel->handle(
                $request = \Illuminate\Http\Request::capture()
            );
  

           

            $responseData = $response->getContent();
            // if ($response instanceof \Illuminate\Http\Response) {
                
            // }

            if (!is_string($responseData)) {
                //only sup string
                $responseData = json_encode($responseData);
            }

            // ob_start();
            // var_dump(gettype($responseData));
            // $bb=ob_get_clean();
            // $lenth=fopen(__DIR__.'/d.txt','w');
            // fwrite($lenth,gettype($responseData).PHP_EOL);
            // fclose($lenth);

            $outParam->response = $responseData;
            $kernel->terminate($request, $response);
            $this->clean();
            return true;
            // $this->createIlluminateRequest($get, $post, $cookie, $files, $server, $content);
            // $selfRequest = \Request::instance();

            // $controller = "App\\Api\\Controller\\" . ucfirst('Api') . 'Controller';
            // $action = 'index';
            // //转入api 网关
            // $response = \App::make($controller)->$action($selfRequest);
            // if ($response instanceof \Illuminate\Http\JsonResponse) {
            //     $response = $response->getOriginalContent();
            // }

            // if (!is_string($response)) {
            //     //only sup string
            //     $response = json_encode($response);
            // }
  
            // $outParam->response = $response;
            // // $outParam->response="{'name':'czm'}";
            // return true;
        } catch (\App\Api\Exception\ForbiddenApiException $t) {
            $outParam->response = json_encode(['code' => 600, 'data' => "路由未开启，检查center配置"]);
        } catch (\Throwable $t) {
          
            $response = json_encode(['code' => 600, 'data' => "tars-rpc produce throwable!".$t->getMessage(),'t' => $t->getFile(), 'line' => $t->getLine()]);
            $outParam->response = $response;
        }
        return false;
    }


    public  function clean()
    {
        $application=app();
        $this->container =Container::getInstance();
        $this->resetSession($application);
        $this->resetCookie($application);
        $this->resetApp();
        $this->resetProviders($application);
        // $this->clearInstances($application);
        // $this->bindRequest($application);
        // $this->rebindRouterContainer($application);
        // $this->rebindViewContainer($application);
        // ['db','db.driver']
        
    }


    protected function resetSession($application)
    {
        if (isset($application['session'])) {
            $session = $application->make('session');
            $session->flush();
        }
    }

    protected function resetProviders($application)
    {
        $providers=$this->providerReset;
        foreach ($providers as $provider) {
            if (class_exists($provider)) {
                $provider = new $provider($application);
                $this->providers[get_class($provider)] = $provider;
            }
        }
        //get provider application
        foreach ($this->providers as $provider) {
            $this->rebindProviderContainer($provider, $application);
            if (method_exists($provider, 'register')) {
                $provider->register();
            }
            if (method_exists($provider, 'boot')) {
                $application->call([$provider, 'boot']);
            }
        }
    }
     /**
     * Rebind service provider's container.
     */
    protected function rebindProviderContainer($provider, $application)
    {
        $closure = function () use ($application) {
            $this->app = $application;
        };
        $resetProvider = $closure->bindTo($provider, $provider);
        $resetProvider();
    }

    protected function resetCookie($application)
    {
        if (isset($application['cookie'])) {
            $cookies = $application->make('cookie');
            foreach ($cookies->getQueuedCookies() as $key => $value) {
                $cookies->unqueue($key);
            }
        }
    }
   
   
    protected function resetApp()
    {
        $resets = $this->appReset;
        //reset auth provider   
        foreach ($resets as $abstract) {
            if ($abstract instanceof ServiceProvider) {
                $this->container->register($abstract, [], true);

            } elseif ($this->container->has($abstract)) {
                $this->rebindAbstract($abstract);
                Facade::clearResolvedInstance($abstract);
            }
        }
    }

    /**
     * Rebind abstract.
     *
     * @param string $abstract
     * @return void
     */
    protected function rebindAbstract($abstract)
    {
        $abstract = $this->container->getAlias($abstract);
        
        $binding = array_get($this->container->getBindings(), $abstract);

        unset($this->container[$abstract]);

        if ($binding) {
            $this->container->bind($abstract, $binding['concrete'], $binding['shared']);
        }
    }

    /**
     * Transforms $_SERVER array.
     *
     * @param array $server
     * @param array $header
     * @return array
     */
    protected static function transformServerParameters(array $server, array $header)
    {
        $__SERVER = [];

        foreach ($server as $key => $value) {
            $key = strtoupper($key);
            $__SERVER[$key] = $value;
        }

        foreach ($header as $key => $value) {
            $key = str_replace('-', '_', $key);
            $key = strtoupper($key);

            if (!in_array($key, ['REMOTE_ADDR', 'SERVER_PORT', 'HTTPS'])) {
                $key = 'HTTP_' . $key;
            }

            $__SERVER[$key] = $value;
        }

        return $__SERVER;
    }


    
}
