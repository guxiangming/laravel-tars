<?php
namespace App\Tars;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class TarsLogHandler extends AbstractProcessingHandler
{
    protected $appConfig;

    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        
        parent::__construct($level, $bubble);
        $this->appConfig = require __DIR__."/config/app.php";

    }
    //传入信息
    protected function write(array $record)
    {
        $data = [
            'Instance'=>isset($_SERVER['HTTP_HOST'])?gethostname().':'. $_SERVER['HTTP_HOST']:'no',
            'ServerName' =>implode('.',$this->appConfig['proto']),
            'Message' => $record['message'],
            'Context' => (string) $record['formatted'],
            'Channel' => $record['channel'],
            'Level' => $record['level'],
            'LevelName' => $record['level_name'],
            'RemoteAddr' =>$_SERVER['HTTP_X_FORWARDED_FOR']??'检测代理服务', 
            'UserAgent' => $_SERVER['HTTP_USER_AGENT']??'no',
            'CreatedBy' => $this->getUserInfo(), //sso_token解析
            'CreatedAt' => $record['datetime']->format('Y-m-d H:i:s'),
            'UpdatedAt' => date('Y-m-d H:i:s'),
        ];
        //信息上报center
        $this->curl(['method'=>'POST','params'=>['data'=>$data,'encrypt'=>md5('XXX')],'route'=>'/api/log/report']);
    }

    protected function getUserInfo(){
        $request=\Request::all();
        if(isset($request['sso_token'])){
            return json_encode($request['sso_token'],JSON_FORCE_OBJECT);
        }else{
            return '';
        }
    }

    protected function curl($curlRequest, $timeout = 10){
        $url=env('MESSAGE_URL','').$curlRequest['route'];
        $params=$curlRequest['params'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);//超时限制
        switch ($curlRequest['method']){
            case "GET" :
             $url=$url.'?'.http_build_query($params);
             curl_setopt($ch, CURLOPT_HTTPGET, true);break;
            case "POST": curl_setopt($ch, CURLOPT_POST,true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));break;
            case "FILE": curl_setopt($ch, CURLOPT_POST,true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);    // 要求结果为字符串且输出到屏幕上
                    curl_setopt($ch, CURLOPT_POST,1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                break;
            case "PUT" : curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));break;
            case "PATCH": curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));break;
            case "DELETE":curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));break;
            default:curl_setopt($ch, CURLOPT_POST,true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));break;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        $output = curl_exec($ch);
        // $open=fopen(__DIR__."/log.txt",'w+');
        // fwrite($open, json_encode($_SERVER));
        // fclose($open);
        $httpCode=curl_getinfo($ch,CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $output;
    }
 
}
