<?php
/**
 * 正则验证
 */
return array(
    //验证邮箱
    'EMAIL'                 => '/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/',
    //网址验证
    'URL'                   => '/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/',
    //验证手机号
    'MOBILE'                => '/^0?(13[0-9]|15[0-9]|18[0-9]|14[57]|17[0-9])[0-9]{8}$/',
    //验证固话号
    'PHONE'                 => '/^\d{3,4}-\d{7,8}(-\d{3,4})?$/',
    //日期格式验证
    'DATE'                  => '/^\d{4}\-\d{2}\-\d{2}$/',
    //时间格式验证
    'TIME'                  => '/^(0\d{1}|1\d{1}|2[0-3]):([0-5]\d{1})$/',
    //img标签匹配
    'IMG_ALL'               => '/src=\"\/?(.*?)\"/',
    'IMG_ONE'               => '<img.*src=[\"](.*?)[\"].*?>',
);