<?php

namespace App\Tars\commands;

use Illuminate\Console\Command;
use Tars\cmd\Command as TarsCommands;
use Symfony\Component\Console\Input\InputOption;
use Log;
class TarsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tars:entry';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'tasphp start stop';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    protected function configure()
    {
        $this->addOption('cmd', 'cmd', InputOption::VALUE_REQUIRED);
        $this->addOption('config_path', 'cfg', InputOption::VALUE_REQUIRED);
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cmd = $this->option('cmd');
        $cfg = $this->option('config_path');
        config(['tars.deploy_cfg' => $cfg]);
        //解析变量配置
        $class = new TarsCommands($cmd, $cfg);
        $class->run();
        
    }
}
