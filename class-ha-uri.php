<?php
/**
 * ha的uri处理函数
 *
 */
class HA_Uri{
    private $uri;
    function __construct($uri){
        $this->uri = $uri;
    }

    // 取出uri中的值
    function queryRemove($uri, $query_key){
        $arr_uri = explode('?', $uri);
        $str_query = '';
        if(!empty($arr_uri[1])){
            $str_query = $arr_uri[1];
        }
        $arr_query = array();
        parse_str($str_query, $arr_query);
        
        $arr_dest_query = array();
        foreach($arr_query as $key => $value){
            if($key != $query_key)
                $arr_dest_query[$key] = $value;    
        }
        $dest_uri = $arr_uri[0] . '?' . http_build_query($arr_dest_query);
        return $dest_uri;
    }

    // 更新uri中的值
    function queryUpdate($arr, $uri = null){
        // 这个方法有可能被静态调用
        if(isset($this)){
            $uri = $this->uri;
        }
        $arr_uri = explode('?', $uri);
        $str_query = '';
        if(!empty($arr_uri[1])){
            $str_query = $arr_uri[1];
        }
        $dest_arr = array();
        parse_str($str_query, $dest_arr);

        $dest_arr = array_merge($dest_arr, $arr);
        $dest_uri = $arr_uri[0] . '?' . http_build_query($dest_arr);
        return $dest_uri;
    }

    // 通过uri的参数获取其中的值
    function queryValue($key, $uri = null){
        // 从uri里面获取当前,这里可能有点问题
        $arr = explode('?', $uri);
        parse_str($arr[1], $params);        
        return $params[$key];
    }
}
