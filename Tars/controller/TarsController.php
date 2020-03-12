<?php

namespace App\Tars\controller;

use Illuminate\Auth\AuthServiceProvider;
use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Container\Container;
use Tars\core\Response as TarsResponse;
use Illuminate\Support\ServiceProvider;
use App\Tars\component\Controller;
use App\Tars\Request;
use App\Tars\Response;
use Tars\App as Tapp;
use Log;
class TarsController extends Controller
{
   

     /**
     * The http kernel.
     *
     * @var \Illuminate\Contracts\Http\Kernel
     */
    protected $container;
    protected $httpKernel;
    protected $appReset=['auth','auth.driver','db','db.factory','db.connection'];
    protected $providerReset=['App\Providers\AuthServiceProvider','Illuminate\Database\DatabaseServiceProvider'];
    protected $timer=[];
    protected static $lock=true;
    public function actionRoute()
    {
        // \Swoole\Runtime::enableCoroutine();
        // go(function () 
        // {   
            try {
                //初始化
                if(TarsController::$lock){
            		app('tars_push_config');
                    //重新保存地址
                    $tarsregistry=config('tarsregistry');
                    app()->bootstrapWith(['\Illuminate\Foundation\Bootstrap\LoadConfiguration']);
                    // 切换上报日志方案 5.6以下
                    // app()->configureMonologUsing(function($monolog){ 
                    //     $monolog->pushHandler(new App\Tars\TarsLogHandler());
                    // });
                    //切换log上报方案
                    config(['logging.default'=>'stderr','logging.channels.stderr.handler'=>'App\Tars\TarsLogHandler']);

                    config(['tarsregistry'=>$tarsregistry]);
                    $tasks=Tapp::getSwooleInstance();
                    foreach($this->timer as $v){
                        $tasks->task(new $v());
                    }
                    
                    TarsController::$lock=false;
            	}
                $this->container =Container::getInstance();
                clearstatcache();
                $this->clearCache();
                Facade::clearResolvedInstance('events');
                //引入laravel http内核
                $this->httpKernel = app()->make(Kernel::class);
                $this->httpKernel->bootstrap();
                // $this->handle();
                list($illuminateRequest, $illuminateResponse) = $this->handle();
                // $this->terminate($illuminateRequest, $illuminateResponse);
                // event('laravel.tars.requested', [$illuminateRequest, $illuminateResponse]);
                $this->container['events']->fire('laravel.tars.requested', [$illuminateRequest, $illuminateResponse]);
                $this->clean();
            } catch (\Throwable $e) {
                $this->status(500);
                $this->sendRaw('tars swoole error'.PHP_EOL.$e->getMessage().PHP_EOL.$e->getTraceAsString());
            }
        // }); 
    }

    private function handle()
    {
        ob_start();
        try {
            $illuminateRequest = Request::make($this->getRequest())->toIlluminate();
        } catch (\Exception $e) {
            throw new \App\Tars\exceptions\SwooleException('检测表单提交异常 Contact Operations and Maintenance Personnel');          
        }
        // event('laravel.tars.requesting', [$illuminateRequest]);
        $this->container['events']->fire('laravel.tars.requesting', [$illuminateRequest]);

        $illuminateResponse = $this->httpKernel->handle($illuminateRequest);
   
         //set type static
        if ($this->handleStaticRequest($illuminateRequest,$illuminateResponse)) {
            return;
        }
        $content = $illuminateResponse->getContent();
        if (strlen($content) === 0 && ob_get_length() > 0) {
            $illuminateResponse->setContent(ob_get_contents());
        }
        ob_end_clean();
        $this->response($illuminateResponse);

        $this->httpKernel->terminate($illuminateRequest, $illuminateResponse);

        return [$illuminateRequest, $illuminateResponse];
    }
    protected function handleStaticRequest($illuminateRequest,$illuminateResponse)
    {
        $uri = $illuminateRequest->getRequestUri();
        $blackList = ['php', 'htaccess', 'config'];
        $extension = substr(strrchr($uri, '.'), 1);
        if ($extension && in_array($extension, $blackList)) {
            return;
        }

        $publicPath =  base_path('public');
        $filename = $publicPath . $uri;

        if (! is_file($filename) || filesize($filename) === 0) {
            return;
        }

        $getTarsResponse=Response::make($illuminateResponse, $this->getResponse())->getTarsResponse();
        $getTarsResponse->status(200);   
        // $swooleResponse->status(200);
        $mime = mime_content_type($filename);
        if ($extension === 'js') {
            $mime = 'text/javascript';
        } elseif ($extension === 'css') {
            $mime = 'text/css';
        }
        $getTarsResponse->resource->header('Content-Type', $mime);
        $getTarsResponse->resource->sendfile($filename);
        return true;
    }


    private function clean()
    {
        $application=app();
        $this->resetSession($application);
        $this->resetCookie($application);
        $this->resetApp();
        $this->resetProviders($application);
        // $this->clearInstances($application);
        // $this->bindRequest($application);
        // $this->rebindRouterContainer($application);
        // $this->rebindViewContainer($application);

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
     * Clear APC or OPCache.
     *
     * @return void
     */
    protected function clearCache()
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }
    private function response($illuminateResponse)
    {
        Response::make($illuminateResponse, $this->getResponse())->send();
    }
     /**
     * Get application's framework.
     */
    protected function isFramework(string $name)
    {
        return 'laravel';
    }
}
