<?php

namespace App\Tars\exceptions;
use Exception;
class SwooleException extends Exception
{
    public function __construct($message)
    {
        $this->message=$message;
        parent::__construct();
    }

    public function render()
    {
        return $this->message;
    }
}
