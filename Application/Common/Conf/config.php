<?php

/**
 * 系统配文件
 * 所有系统级别的配置
 */
return array(
    /* 模块相关配置 */
    'AUTOLOAD_NAMESPACE' => array('Plugins' => PLUGIN_PATH), //扩展模块列表
    'DEFAULT_MODULE'     => 'Home',
    'MODULE_DENY_LIST'   => array('Common'),
    'MODULE_ALLOW_LIST'  => array('Manager','Home','Api','Merchant','Wap'),

    /* 系统数据加密设置 */
    'DATA_AUTH_KEY' => 'Too%Q$L!I#P(3)%@%&J[%$D+a(v5`ni}W|N^o@4c^9<G=VK%cms', //默认数据加密KEY

    /* 调试配置 */
    'SHOW_PAGE_TRACE' => false,

    /* 用户相关设置 */
    'USER_MAX_CACHE'     => 1000, //最大缓存用户数
    'USER_ADMINISTRATOR' => 1, //管理员用户ID

    /* URL配置 */
    'URL_CASE_INSENSITIVE' => false, //默认false 表示URL区分大小写 true则表示不区分大小写
    'URL_MODEL'            => 2, //URL模式 2重写模式
    'VAR_URL_PARAMS'       => '', // PATHINFO URL参数变量
    'URL_PATHINFO_DEPR'    => '/', //PATHINFO URL分割符

    /* 全局过滤配置 */
    'DEFAULT_FILTER' => 'htmlspecialchars', //全局过滤函数

    /* 数据库配置 */
    'DB_TYPE'   => 'Mysql', // 数据库类型
    'DB_HOST'   => '101.201.252.128', // 服务器地址
    'DB_NAME'   => 'wjyp', // 数据库名
    'DB_USER'   => 'chenml', // 用户名
    'DB_PWD'    => 'txunda2017',  // 密码
    'DB_PORT'   => '3306', // 端口
    'DB_PREFIX' => 'db_', // 数据库表前缀

    'LANG_SWITCH_ON'    => true,    // 开启语言包功能
    'LANG_AUTO_DETECT'  => false,    // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST'         => 'zh-cn,en-us,zh-tw', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE'      => 'l',     // 默认语言切换变量
    'DEFAULT_LANG'      => 'zh-cn',

    //扩展配置
    'LOAD_EXT_CONFIG'       => 'regular',
    'LOAD_EXT_FILE'         => 'discount',

    /*阿里云OSS配置信息*/
    "OSS_ACCESS_ID" => '',
    "OSS_ACCESS_KEY"=> '',
    "OSS_ENDPOINT" => 'http://oss-cn-beijing.aliyuncs.com',    //地区
    "OSS_TEST_BUCKET" => 'wujieyoupin', //存储空间名称--自己定义
    "OSS_WEB_SITE" =>'http://wujieyoupin.oss-cn-beijing.aliyuncs.com',     //外网域名

    /*阿里云短信配置*/
    'SMS_ACCESS_ID'=>'LTAISRr0W785pOD3',
    'SMS_ACCESS_KEY'=>'tdp5dKFNPXsagtmF4T9TeOUhWMd9gY',

    //代金券过期时间
    'DELAY_TIME'  => 60*60*24*7,
);
