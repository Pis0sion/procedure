<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function sp_http($url, $params = false, $ispost = 0, $header = array(), $verify = false)
{
    $httpInfo = array();
    $ch = curl_init();
    if(!empty($header)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    }
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    //忽略ssl证书
    if($verify === true){
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    } else {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }
    if ($ispost) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_URL, $url);
    } else {
        if (is_array($params)) {
            $params = http_build_query($params);
        }
        if ($params) {
            curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        } else {
            curl_setopt($ch, CURLOPT_URL, $url);
        }
    }
    $response = curl_exec($ch);
    if ($response === FALSE) {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $httpInfo = array_merge($httpInfo, curl_getinfo($ch));
        return false;
    }
    curl_close($ch);
    return $response;
}

function sp_Infinite(array $data, $id, $condition, $type = "all", $field = null)
{
    $ancestry = array();
    array_walk($data, function($value, $key)use(&$ancestry,$data,$id,$condition,$type,$field){
        if($value[$condition] == $id){
            if($type == "all"){
                if(empty($field)){
                    $ancestry[] = $value;
                }else{
                    $ancestry[] = $value[$field];
                }
                unset($data[$key]);
                $ancestry = array_merge($ancestry, Infinite($data,$value['id'],$condition,$type,$field));
            }elseif($type == "tree"){
                $value['son'] = Infinite($data,$value['id'],$condition,$type,$field);
                $ancestry[] = $value;
                unset($data[$key]);
            }
        }
    });
    return $ancestry;
}

function sp_random($length)
{
    $hash = '';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
    $max = strlen($chars) - 1;
    mt_srand((double)microtime() * 1000000);
    for($i = 0; $i < $length; $i++)
    {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

function sp_getIP()
{
    static $realip;
    if (isset($_SERVER)){
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        } else {
            $realip = $_SERVER["REMOTE_ADDR"];
        }
    } else {
        if (getenv("HTTP_X_FORWARDED_FOR")){
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        } else if (getenv("HTTP_CLIENT_IP")) {
            $realip = getenv("HTTP_CLIENT_IP");
        } else {
            $realip = getenv("REMOTE_ADDR");
        }
    }
    return $realip;
}

function sp_dwz($url)  //TODO 待更新
{
    $api = 'http://api.t.sina.com.cn/short_url/shorten.json'; // json
    $source = '1340181653';
    $url_long = $url;
    $request_url = sprintf($api.'?source=%s&url_long=%s', $source, $url_long);
    $data = file_get_contents($request_url);
    $data = json_decode($data,1);
    $data = $data['0']['url_short'];
    return $data;
}


function sp_str_md5($str)
{
    if(defined('SITE_ENCRYPTION_KEY_BEGIN') && define('SITE_ENCRYPTION_KEY_END')){
        $str_md5 = '###'.md5("L".base64_encode(SITE_ENCRYPTION_KEY_BEGIN) . md5($str) . base64_encode(SITE_ENCRYPTION_KEY_END)."T");
    }else{
        $str_md5 = '###'.md5("L".base64_encode("bceogdiinnnging") . md5($str) . base64_encode("ceondding")."T");
    };
    return $str_md5;
}


function sp_str_compare_md5($password,$comPassword)
{
    return sp_str_md5($password) == $comPassword;
}


function sp_log($file="log", $content, $isTime = 1, $type = "ARRAY")
{
    date_default_timezone_set('PRC');
    if($isTime != 0){
        $file = $file."_".date("Y-m").".log";
    }else{
        $file = $file.".log";
    }
    switch($type){
        case "JSON":
            $content = json_encode($content);
            break;
    }
    file_put_contents($file,var_export($content,true).PHP_EOL.PHP_EOL,FILE_APPEND);
}

function sp_sumToArray(array $data, $field)
{
    return array_sum(array_column($data,$field));
}

function sp_filterVar(array $data, $field)
{
    return array_filter($data, function($value) use ($field){
        if(@$value[$field] == 0){
            return false;
        }
        return true;
    });
}

function sp_datetime($time = '')
{
    if(empty($time))
        $time = time();
    date_default_timezone_set('PRC');
    return date("Y-m-d H:i:s",$time);
}

function sp_data_transAtion($result, $Enter, $outOf)
{
    switch($outOf){
        case "JSON";
            switch($Enter){
                case "ARRAY";
                    $result = json_encode($result);
                    break;
            }
            break;
        case "ARRAY";
            switch($Enter){
                case "JSON";
                    $result = json_decode($result,true);
                    break;
            }
            break;
    }
    return $result;
}

function safe_string($str){ //过滤安全字符
    $str=str_replace("'","",$str);
    $str=str_replace('"',"",$str);
    $str=str_replace(" ","$nbsp;",$str);
    $str=str_replace("//","",$str);
    $str=str_replace("http","",$str);
    $str=str_replace("?","",$str);
    $str=str_replace(":","",$str);
    $str=str_replace("#","",$str);
    $str=str_replace("\n;","<br/>",$str);
    $str=str_replace("<","",$str);
    $str=str_replace(">","",$str);
    $str=str_replace("\t"," ",$str);
    $str=str_replace("\r","",$str);
    $str=str_replace("/[\s\v]+/"," ",$str);
    return $str;
}

if(!function_exists("str_md5"))
{
    function str_md5($str)
    {
        return md5(base64_encode("bceogdiinnnging") . md5($str) . base64_encode("ceondding"));
    }
}
//  存储过程
if(!function_exists("procedure"))
{
    function procedure($sql)
    {
        $fp = dbConnect();
        $fp->multi_query($sql);
        do {
            if ($result = $fp->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    $re[] = $row ;
                }
                $result->close();
            }
        } while ($fp->more_results()&&$fp->next_result());
        $fp->commit();
        return $re ;
    }
}
if(!function_exists("dbConnect"))
{
    function dbConnect()
    {
        return new mysqli(config("database.hostname"), config("database.username"), config("database.password"),config("database.database"));
    }
}

