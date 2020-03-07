<?php
namespace App\Tars\contracts;
interface TaskContract{
    /**
     * /swoole/http/server task
     */
    public function handle($server, $taskId, $fromId, $data);
}
