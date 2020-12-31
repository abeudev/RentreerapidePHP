<?php

defined('BASEPATH') or exit('No direct script access allowed');

// Add admin_url
if (!function_exists('admin_url')) {
    function admin_url($uri = '', $protocol = null)
    {
        return get_instance()->config->site_url('admin/' . $uri, $protocol);
    }
}

// Add admin_redirect
if (!function_exists('admin_redirect')) {
    function admin_redirect($uri = '', $method = 'auto', $code = null)
    {
        if (!preg_match('#^(\w+:)?//#i', $uri)) {
            $uri = site_url('admin/' . $uri);
        }
        return redirect($uri, $method, $code);
    }
}

// Add shop_url
if (!function_exists('shop_url')) {
    function shop_url($uri = '', $protocol = null)
    {
        return get_instance()->config->site_url('shop/' . $uri, $protocol);
    }
}

// Add shop_redirect
if (!function_exists('shop_redirect')) {
    function shop_redirect($uri = '', $method = 'auto', $code = null)
    {
        if (!preg_match('#^(\w+:)?//#i', $uri)) {
            $uri = site_url('shop/' . $uri);
        }
        return redirect($uri, $method, $code);
    }
}

if(!function_exists('ROOTPATH'))
{
    function ROOTPATH($dir = '')
    {
        $path  = str_replace('system\\','',BASEPATH);
        $path = str_replace('\\','/',$path);
        return $path.$dir;


    }
}

if(!function_exists('random_string'))
{
    function random_string($type = 'alnum', $len = 8)
    {
        switch ($type)
        {
            case 'basic':
                return mt_rand();
            case 'alnum':
            case 'numeric':
            case 'nozero':
            case 'alpha':
                switch ($type)
                {
                    case 'alpha':
                        $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'alnum':
                        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        break;
                    case 'numeric':
                        $pool = '0123456789';
                        break;
                    case 'nozero':
                        $pool = '123456789';
                        break;
                }
                return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);
            case 'unique': // todo: remove in 3.1+
            case 'md5':
                return md5(uniqid(mt_rand()));
            case 'encrypt': // todo: remove in 3.1+
            case 'sha1':
                return sha1(uniqid(mt_rand(), TRUE));
        }
    }
}

if(!function_exists('discount')){
    function discount($item_price = 0 , $promo_price = 0){

        $discount = 0;
        if(!empty($item_price) and !empty($promo_price)){
            $discount = ((float)$promo_price * 100) / $item_price;
        }

        return round($discount) - 100;

    }
}

if ( ! function_exists('openJSONFile'))
{
    function openJSONFile($file_name)
    {
        $jsonString = [];
        if (file_exists(APPPATH.'files/'.$file_name.'.json')) {
            $jsonString = file_get_contents(APPPATH.'files/'.$file_name.'.json');
            $jsonString = json_decode($jsonString, true);
        }
        return $jsonString;
    }
}

if(!function_exists('writeJsonFile')){
    function writeJsonFile($file_name , $json){
//        $jsonData = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents(APPPATH.'files/'.$file_name.'.json', stripslashes($json));
    }
}


if(!function_exists('productImage')){
    function productImage($image = '' ,$thumb = true){
        $image = trim($image);
        if((preg_match("#^http#" , $image))){
            return $image;
        }
        else{
            return base_url('assets/uploads/'.(($thumb)?'thumbs/':''). $image);
        }
    }
}


if(!function_exists('libName')){
    function libName($name = ''){
       return trim(strtolower(str_replace(' ','_',$name)));
    }
}

