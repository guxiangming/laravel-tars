<?php

namespace App\Tars\rpc;
use App\Tars\model\Api;
use App\Tars\protocol\HTMSTHREE\API\obj\classes\CommonInParam;
use App\Tars\protocol\HTMSTHREE\API\obj\classes\CommonOutParam;
use App\Tars\protocol\HTMSTHREE\API\obj\ApiServiceServant;

class ApiRpc
{   

    // if(extension_loaded('phptars')&&extension_loaded('swoole')&&env('tarsRpc',true)&&class_exists('\App\Tars\rpc\ApiRpc')){
    //     return \App\Tars\rpc\ApiRpc::Rpc($curlRequest['method'],$curlRequest['route'],$params);
    // }

    public static function Rpc($mehtod,$route,$params){
        // return config('tarsregistry');
        $commonIn=new CommonInParam();
        $commonIn->method=$mehtod;
        $commonIn->route=$route;
        $commonIn->params=http_build_query($params);
        //获取回调
        $commonOut=new CommonOutParam();
        $servant=new ApiServiceServant(api::getConfig('HTMSTHREE','API'));
        $servant->Controller($commonIn,$commonOut);
        return $commonOut->response;
    }
}