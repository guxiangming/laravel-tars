<?php

namespace App\Tars\exceptions;
use Exception;
class ExcelException extends Exception
{
    /**
     * 兼容导出模块需要修改 composer
     */
    public function __construct($string,$headers)
    {
        $this->headers=$headers;
        $this->string=$string;
        parent::__construct();
    }

    public function render()
    {
        return response($this->string)->withHeaders($this->headers);
    }
}
