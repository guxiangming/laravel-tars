<?php

namespace App\Tars;

use App\Tars\commands\TarsCommand;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

class TarsServiceProvider extends \Illuminate\Support\ServiceProvider
{
    protected $booted = false;
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    
        if(extension_loaded('phptars')&&extension_loaded('swoole')){
            //重新cli的打印模式
            $cloner = new VarCloner();
            $dumper = new class extends HtmlDumper {
            
                protected function echoLine($line, $depth, $indentPad)
                {
                    // return 88;
                    if (-1 !== $depth) {
                        echo str_repeat($indentPad, $depth).$line."\n";
                    }
                }
            };
            VarDumper::setHandler(function($var) use($dumper,$cloner){
                ob_start();
                $dumperClone=clone $dumper;
                $dumperClone->dump($cloner->cloneVar($var));
                $content = ob_get_clean();
                throw new \App\Tars\exceptions\SwooleException($content);
            });


            $this->registerCommands();
            $this->app->singleton('tars_push_config', function ($app) {
                return Boot::handle();
            });
        }
        
    }

    /**
     * Register commands.
     */
    protected function registerCommands()
    {
        $this->commands([
            TarsCommand::class,
        ]);
    }

    public function boot(){
        if(extension_loaded('phptars')&&extension_loaded('swoole')){
            if (!$this->booted) {
                //pusher config
                $this->mergeConfigFrom(__DIR__ . '/config/app.php', 'tars');
                $this->booted = true;
            }
        }   
    }
}