<?php

namespace  App\Tars\task;
use App\Tars\contracts\TaskContract;
class curlTask implements TaskContract
{
    
    
    /**
     * Mail task
     * 
     * @var array $mail
     * @return void
     */
    public function __construct()
    {
        
    }

    /**
     * Task handler.
     *
     * @param \Swoole\Server $server
     * @param int $taskId
     * @param int $srcWorkerId
     * @return void
     */
    public function handle($server, $taskId, $fromId, $data)
    {
        // use Tars\App as Tapp;
        // $tasks=Tapp::getSwooleInstance();
        // $tasks->task(new \App\Tars\task\curlTask());
    }
 
  
}
