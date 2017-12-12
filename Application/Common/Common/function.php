<?php
/**
 * 系统公共库文件
 * 主要定义系统公共函数库
 */
/**
 * @param int $flag 0数字字符混合 1字符 2数字
 * @param int $num 验证标识的个数
 * @return string
 */
function get_vc($num = 0, $flag = 0) {
    /**获取验证标识**/
    $arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',1,2,3,4,5,6,7,8,9,0);
    $vc  = '';
    switch($flag) {
        case 0 : $s = 0;  $e = 61; break;
        case 1 : $s = 0;  $e = 51; break;
        case 2 : $s = 52; $e = 61; break;
    }

    for($i = 0; $i < $num; $i++) {
        $index = rand($s, $e);
        $vc   .= $arr[$index];
    }
    return $vc;
}

/**
 * 字符串转换为数组，主要用于把分隔符调整到第二个参数
 * @param  string $str  要分割的字符串
 * @param  string $glue 分割符
 * @return array
 */
function str2arr($str, $glue = ',') {
    return explode($glue, $str);
}

/**
 * 数组转换为字符串，主要用于把分隔符调整到第二个参数
 * @param  array  $arr  要连接的数组
 * @param  string $glue 分割符
 * @return string
 */
function arr2str($arr, $glue = ',') {
    return implode($glue, $arr);
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param int $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param boolean $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    } else {
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 单位 秒
 * @return string
 */
function think_encrypt($data, $key = '', $expire = 0) {
    $key  = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    $str = sprintf('%010d', $expire ? $expire + time():0);

    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1)))%256);
    }
    return str_replace(array('+','/','='),array('-','_',''),base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key  加密密钥
 * @return string
 */
function think_decrypt($data, $key = '') {
    $key    = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
    $data   = str_replace(array('-','_'),array('+','/'),$data);
    $mod4   = strlen($data) % 4;
    if ($mod4) {
       $data .= substr('====', $mod4);
    }
    $data   = base64_decode($data);
    $expire = substr($data,0,10);
    $data   = substr($data,10);

    if($expire > 0 && $expire < time()) {
        return '';
    }
    $x      = 0;
    $len    = strlen($data);
    $l      = strlen($key);
    $char   = $str = '';

    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char .= substr($key, $x, 1);
        $x++;
    }

    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1))<ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        } else {
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 数据签名认证
 * @param  array  $data 被认证的数据
 * @return string       签名
 */
function data_auth_sign($data) {
    //数据类型检测
    if(!is_array($data)) {
        $data = (array)$data;
    }
    ksort($data); //排序
    $code = http_build_query($data); //url编码并生成query字符串
    $sign = sha1($code); //生成签名
    return $sign;
}

/**
* 对查询结果集进行排序
* @access public
* @param array $list 查询结果
* @param string $field 排序的字段名
* @param string $sort_by 排序类型
* asc正向排序 desc逆向排序 nat自然排序
* @return array
*/
function list_sort_by($list, $field, $sort_by = 'asc') {
   if(is_array($list)) {
       $refer = $resultSet = array();
       foreach ($list as $i => $data)
           $refer[$i] = &$data[$field];
       switch ($sort_by) {
           case 'asc': // 正向排序
                asort($refer);
                break;
           case 'desc':// 逆向排序
                arsort($refer);
                break;
           case 'nat': // 自然排序
                natcasesort($refer);
                break;
       }
       foreach ( $refer as $key=> $val)
           $resultSet[] = &$list[$key];
       return $resultSet;
   }
   return false;
}

/**
 * @param $list
 * @param string $pk
 * @param string $pid
 * @param string $child
 * @param int $root
 * @return array
 * 把返回的数据集转换成Tree
 */
function list_to_tree($list, $root = 0, $pk = 'id', $pid = 'parent_id', $child = '_child') {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            //以主键为键值的数组
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            //当前分类的父级分类是否等于父根节点
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            } else {
                if (isset($refer[$parentId])) {
                    //当前分类的伤及分类 引用
                    $parent =& $refer[$parentId];
                    //存入上级分类的子分类中
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 */
function tree_to_list($tree, &$list = array(), $child = '_child', $order = 'id'){
    if(is_array($tree)) {
        $refer = array();
        foreach ($tree as $key => $value) {
            $refer = $value;
            //是否有子分类
            if(isset($refer[$child])) {
                unset($refer[$child]);
                //递归
                tree_to_list($value[$child], $list, $child, $order);
            }
            $list[] = $refer;
        }
        //排序
        $list = list_sort_by($list, $order, $sort_by = 'asc');
    }
    return $list;
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 设置跳转页面URL
 * 使用函数再次封装，方便以后选择不同的存储方式（目前使用cookie存储）
 */
function set_redirect_url($url) {
    cookie('redirect_url', $url);
}

/**
 * 获取跳转页面URL
 * @return string 跳转页URL
 */
function get_redirect_url() {
    $url = cookie('redirect_url');
    return empty($url) ? __APP__ : $url;
}

/**
 * 处理插件钩子
 * @param string $hook   钩子名称
 * @param mixed $params 传入参数
 * @return void
 */
function hook($hook,$params=array()) {
    \Think\Hook::listen($hook,$params);
}

/**
 * 获取插件类的类名
 * @param string $name 插件名
 * @return mixed
 */
function get_plugin_class($name) {
    $class = "Plugins\\{$name}\\{$name}Plugin";
    return $class;
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 * @return mixed
 */
function get_plugin_config($name) {
    $class = get_plugin_class($name);
    if(class_exists($class)) {
        $plugin = new $class();
        return $plugin->getConfig();
    } else {
        return array();
    }
}

/**
 * 插件显示内容里生成访问插件的url
 * @param string $url url
 * @param array $param 参数
 * @return mixed
 */
function plugins_url($url, $param = array()) {
    $url        = parse_url($url);
    $case       = C('URL_CASE_INSENSITIVE');
    $plugins    = $case ? parse_name($url['scheme']) : $url['scheme'];
    $controller = $case ? parse_name($url['host']) : $url['host'];
    $action     = trim($case ? strtolower($url['path']) : $url['path'], '/');

    /* 解析URL带的参数 */
    if(isset($url['query'])) {
        parse_str($url['query'], $query);
        $param = array_merge($query, $param);
    }

    /* 基础参数 */
    $params = array(
        'plugins'    => $plugins,
        'controller' => $controller,
        'action'     => $action,
    );
    $params = array_merge($params, $param); //添加额外参数

    return U('Plugins/execute', $params);
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 */
function time_format($time = NULL,$format='Y-m-d H:i') {
    $time = $time === NULL ? NOW_TIME : intval($time);
    return date($format, $time);
}

/**
 * @param $files
 * 基于数组创建目录和文件
 */
function create_dir_or_files($files) {
    foreach ($files as $key => $value) {
        if(substr($value, -1) == '/') {
            mkdir($value);
        } else {
            @file_put_contents($value, '');
        }
    }
}

/**
 * 返回input数组中键值为$columnKey的列
 */
if(!function_exists('array_column')) {
    function array_column(array $input, $columnKey, $indexKey = null) {
        $result = array();
        if (null === $indexKey) {
            if (null === $columnKey) {
                $result = array_values($input);
            } else {
                foreach ($input as $row) {
                    $result[] = $row[$columnKey];
                }
            }
        } else {
            if (null === $columnKey) {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row;
                }
            } else {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row[$columnKey];
                }
            }
        }
        return $result;
    }
}

/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5');  调用Admin模块的User接口
 * @param  string  $name 格式 [模块名]/接口名/方法名
 * @param  array|string  $vars 参数
 * @return mixed
 */
function api($name,$vars = array()) {
    $array     = explode('/',$name);
    $method    = array_pop($array);
    $class_name = array_pop($array);
    $module    = $array? array_pop($array) : 'Common';
    $callback  = $module.'\\Api\\'.$class_name.'Api::'.$method;
    if(is_string($vars)) {
        parse_str($vars,$vars);
    }
    return call_user_func_array($callback,$vars);
}

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 */
function check_verify($code, $id = 1) {
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * @param array $by_array  按照该数组排序
 * @param array $list        要排序的列表
 * @param string $key_name  键值名称
 * @return array
 */
function sort_by_array($by_array, $list, $key_name = 'id') {
    if(empty($by_array))
        return $list;
    if(empty($list))
        return array();
    foreach ($list as $key => $data) {
        if(empty($data[$key_name]))
            return array();
        $refer[$data[$key_name]] =& $list[$key];
    }
    foreach($by_array as $val) {
        if(!empty($refer[$val])) {
            $sort_list[] = $refer[$val];
        }
    }
    return $sort_list;
}

/**
 * @param $str
 * @return string
 * 过滤掉html标签
 */
function filter_html($str) {
    return stripslashes(preg_replace("/(\<[^\<]*\>|\r|\n|\s|\&nbsp;|\[.+?\])/is", '', $str));
}

/**
 * @param $arr
 * @param $str
 * 去除数组中指定元素
 */
function remove_arr_str($arr,$str){
    foreach($arr as $key=>$value){
        if($value == $str){
            unset($arr[$key]);
        }
    }
    return $arr;
}

/**
 * 获取当前页面的url
 * @return string
 */
function get_url() {
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
    $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
    $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
    return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
}

/**
 * curl 执行post的请求
 * @param $url
 * @param string $post_data
 * @param int $timeout
 * @return mixed
 */
function post($url, $post_data = '', $timeout = 5){
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $url);
    curl_setopt ($ch, CURLOPT_POST, 1);
    if($post_data != ''){
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    }
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $file_contents = curl_exec($ch);
    curl_close($ch);
    return $file_contents;
}

/**
 * @param $ak API控制台申请得到的ak（此处ak值仅供验证参考使用）
 * @param $sk 应用类型为for server, 请求校验方式为sn校验方式时，系统会自动生成sk，可以在应用配置-设置中选择Security Key显示进行查看（此处sk值仅供验证参考使用）
 * @param $querystring_arrays
 * @param $method
 * @return string
 */
function getBdSn($ak,$sk,$querystring_arrays,$method){
    //get请求uri前缀
    $uri = '/geodata/v3/';


    //调用sn计算函数，默认get请求
    $sn = caculateAKSN($ak, $sk, $uri, $querystring_arrays,$method);

    //请求参数中有中文、特殊字符等需要进行urlencode，确保请求串与sn对应
    //$target = sprintf($url, urlencode($address), $output, $ak, $sn);

    return $sn;

    //输出计算得到的sn
    //echo "sn: $sn \n";

    //输出完整请求的url（仅供参考验证，故不能正常访问服务）
    //echo "url: $target \n";
}

/**
 * 百度应用 sn计算函数，默认get请求
 * @param $ak
 * @param $sk
 * @param $url
 * @param $querystring_arrays
 * @param string $method
 * @return string
 */
function caculateAKSN($ak, $sk, $url, $querystring_arrays, $method = 'GET')
{
    if ($method === 'POST'){
        ksort($querystring_arrays);
    }
    $querystring = http_build_query($querystring_arrays);
    return md5(urlencode($url.'?'.$querystring.$sk));
}
//将 xml数据转换为数组格式。
function xml_to_array($xml){
    $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
    if(preg_match_all($reg, $xml, $matches)){
        $count = count($matches[0]);
        for($i = 0; $i < $count; $i++){
            $subxml= $matches[2][$i];
            $key = $matches[1][$i];
            if(preg_match( $reg, $subxml )){
                $arr[$key] = xml_to_array( $subxml );
            }else{
                $arr[$key] = $subxml;
            }
        }
    }
    return $arr;
}

function postSms($curlPost,$url){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_NOBODY, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
    $return_str = curl_exec($curl);
    curl_close($curl);
    return $return_str;
}

/**
 * [merOrderType 商家订单类型]
 * 0:普通 1：团购 2：预购 3：竞拍 4：一元夺宝 5：无界商店 6：汽车购 7：房产购 8：线下商城
 * @return [string] [状态]
 */
function merOrderType($status)
{
    switch ($status) {
        case '0': return '普通订单';     break;
        case '1': return '团购订单';     break;
        case '2': return '无界预购';     break;
        case '3': return '竞拍汇';     break;
        case '4': return '一元夺宝';     break;
        case '5': return '无界商店';     break;
        case '6': return '汽车购';     break;
        case '7': return '房产购';     break;
        case '8': return '线下商城';     break;
        default : return '';           break;
    }
}

/**
 * [merOrderStatus 商家订单状态]
 * 根据订单类型选择自己对应的状态
 *
 * @return [string] [状态]
 */
function merOrderStatus($status,$order_type=0)
{
    switch ($order_type) {
        case '0':
            return comOrderStatus($status);
        case '1':
            return groupOrderStatus($status);
        case '2':
            return preOrderStatus($status);
        case '3':
            return comOrderStatus($status);
        case '4':
            return comOrderStatus($status);
        case '5':
            return comOrderStatus($status);
        case '6':
            return carOrderStatus($status);
        case '7':
            return carOrderStatus($status);
//        case '8': return comOrderStatus($status); //待定
    }
}
/**
 * [comOrderStatus 商家订单状态(普通通用类型)]
 * 0待支付，1待发货，2待收货，3待评价，4已完成
 * @return [string] [状态]
 */
function comOrderStatus($status)
{
    switch ($status) {
        case '0': return '待支付';     break;
        case '1': return '待发货';     break;
        case '2': return '待收货';     break;
        case '3': return '待评价';     break;
        case '4': return '已完成';     break;
        default : return '';           break;
    }
}
/**
 * [comOrderStatus 商家订单状态(团购类型)]
 * 0待支付，1待成团，2已成团，待发货，3待收货，4待评价 5交易成功 6未成团，退款成功
 * @return [string] [状态]
 */
function groupOrderStatus($status){
    switch ($status) {
        case '0': return '待付款';     break;
        case '1': return '待成团';     break;
        case '2': return '已成团，待发货';     break;
        case '3': return '待收货';     break;
        case '4': return '待评价';     break;
        case '5': return '交易成功';     break;
        case '6': return '未成团，退款成功';     break;
        default : return '';           break;
    }
}

/**
 * [comOrderStatus 商家订单状态(预购类型)]
 * 0待支付定金，1交易关闭，2待付尾款，3待发货，4代收货 5待评价 6交易成功 7交易关闭
 * @return [string] [状态]
 */
function preOrderStatus($status){
    switch ($status) {
        case '0': return '待付定金';     break; //一阶段(未交定金)
        case '1': return '交易关闭';     break; //一阶段(交定金时间过期)
        case '2': return '待付尾款';     break; //二阶段(待付尾款)
        case '3': return '待发货';     break; //二阶段(待付尾款)
        case '4': return '待收货';     break; //之后二阶段
        case '5': return '待评价';     break;
        case '6': return '交易成功';     break;
        case '7': return '交易关闭';     break; //二阶段(交尾款时间过期)
        default : return '';           break;
    }
}

/**
 * [carOrderStatus 商家订单状态(房产狗，汽车购类型)]
 *0：代付款 2手续办理中 3交易成功 4交易关闭
 * @return [string] [状态]
 */
function carOrderStatus($status){
    switch ($status) {
        case '0': return '待付款';     break;
        case '1': return '手续办理中';     break;
        case '2': return '交易成功';     break; //线下付款完成
        case '3': return '交易关闭';     break; //不买了

        default : return '';           break;
    }
}
/**
 * //补零函数
 * @param $num 输出要补的数值
 * @param $n 表示位数

 * @return string
 */
function zero($num,$n){
    if(($re = $n-strlen($num))<0){
        return false;
    }
    return str_repeat("0",$re).$num;
}
/**
 *
 * 根据id获取任意字段值
 * @return [string] [状态]
 */
function getName($model='',$field='',$id=0){
    if($id && $model && $field){
        return M($model)->where("id={$id}")->getField($field).'';
    }else{
        return '';
    }

}

/**
 * 根据货品id和商品id取得属性的标示值
 * @param int $goods_id
 * @param int $product_id
 * @return string
 */
function getAttrGroupId($goods_id = 0,$product_id = 0){

    //获取到goods_attr属性值数组
    $attr = M('GoodsAttr')->where("goods_id={$goods_id}")->select();
    //创建属性值对应数组
    foreach($attr as $k1=>$v1){
        $attr_arr[$v1['id']] = $v1['attr_value'];
    }

    $goods_attr = M('Products')->field('goods_attr')->where("id='{$product_id}'")->find()['goods_attr'];
    if(!$goods_attr){
        return '';
    }
    $gArr = explode('|',$goods_attr);

    foreach($gArr as $k2=>$v2){
        $gArr[$k2] = $attr_arr[$v2];
    }
    $gArr = implode(',',$gArr);
    return $gArr;

}
/**
 * 根据货品id和商品id取得属性的标示值(带有属性名称)
 * @param int $goods_id
 * @param int $product_id
 * @return string
 */
function getAttrGroupId1($goods_id = 0,$product_id = 0){

    //获取到goods_attr属性值数组
    $attr = M('GoodsAttr')->alias('t')
        ->field("t.*,a.attr_name")
        ->join(C('DB_PREFIX').'attribute a ON a.id = t.attr_id')
        ->where("goods_id={$goods_id}")
        ->select();
    //创建属性值对应数组
    foreach($attr as $k1=>$v1){
        $attr_arr[$v1['id']] = $v1['attr_name'].':'.$v1['attr_value'];

    }
    $goods_attr = M('Products')->field('goods_attr')->where("id='{$product_id}'")->find()['goods_attr'];
    if(!$goods_attr){
        return '';
    }
    $gArr = explode('|',$goods_attr);

    foreach($gArr as $k2=>$v2){
        $gArr[$k2] = $attr_arr[$v2];
    }
    $gArr = implode(',',$gArr);
    return $gArr;

}
/**
 * 根据货号和商品id取得属性的标示值
 * @param int $goods_id
 * @param string $product_sn
 * @return string
 */
function getAttrGroup($goods_id = 0,$product_sn = ''){

    //获取到goods_attr属性值数组
    $attr = M('GoodsAttr')->where("goods_id={$goods_id}")->select();
    //创建属性值对应数组
    foreach($attr as $k1=>$v1){
        $attr_arr[$v1['id']] = $v1['attr_value'];
    }

    $goods_attr = M('Products')->field('goods_attr')->where("product_sn='{$product_sn}'")->find()['goods_attr'];

    $gArr = explode('|',$goods_attr);

    foreach($gArr as $k2=>$v2){
        $gArr[$k2] = $attr_arr[$v2];
    }
    $gArr = implode(',',$gArr);
    return $gArr;

}

/**
 * 将首字母大写的字符转换成下划线小写  BuyBuy ---> buy_buy
 * @param $name
 * @return string
 */
function cc_format($name){
    $temp_array = array();
    for($i=0;$i<strlen($name);$i++){
        $ascii_code = ord($name[$i]);
        if($ascii_code >= 65 && $ascii_code <= 90){
            if($i == 0){
                $temp_array[] = chr($ascii_code + 32);
            }else{
                $temp_array[] = '_'.chr($ascii_code + 32);
            }
        }else{
            $temp_array[] = $name[$i];
        }
    }
    return implode('',$temp_array);
}

/**
 * 根据id获取图片路径
 */
function getPath($id){
    if(!$id){
        return '';
    }
    $path = M('File')->where("id = {$id}")->getField('path');
    return $path ? $path : '';
}
/**
 * 获取分类路径
 */
function getCatePath($model,$cate_id){
    if(!$cate_id){
        return '';
    }
    $three_cate_info = M($model)->field('short_name,parent_id')->where("id = {$cate_id}")->find();
    $three_cate_name = $three_cate_info['short_name'];
    $two_cate_id = M($model)->where("id = {$cate_id}")->getField('parent_id');
    $two_cate_id = $two_cate_id?$two_cate_id:0;
    $two_cate_info = M($model)->field('short_name,parent_id')->where("id = {$two_cate_id}")->find();
    $two_cate_name = $two_cate_info['short_name'];
    $two_cate_info['parent_id'] = $two_cate_info['parent_id']?$two_cate_info['parent_id']:0;
    $one_cate_name = M($model)->where("id = {$two_cate_info['parent_id']}")->getField('short_name');
    return $one_cate_name.' → '.$two_cate_name.' → '.$three_cate_name;
}

/**
 * 发送系统消息
 */
function sendSystemMsg($content,$user_id=0){
    $data['content'] = $content;
    $data['create_time'] = time();
    $text_id = M('MessageText')->add($data);
    if($text_id){
        $m_data['user_id'] = $user_id? $user_id : '0';
        $m_data['text_id'] = $text_id;
        $m_data['status'] = 0;
        $res = M('Message')->add($m_data);
        return $res;
    }else{
        return false;
    }
}
/**
 * 发送订单消息
 */
function sendOrderMsg($content,$order_id,$user_id=0){
    $data['content'] = $content;
    $data['create_time'] = time();
    $data['status'] = 0;
    $data['order_id'] = $order_id;
    $data['user_id'] = $user_id;
    $id = M('OrderMessage')->add($data);
    if($id){
        return true;
    }else{
        return false;
    }
}
/**
 * 积分计算公式
 */
function getIntegral($price = 0,$rate = 0){
    $res = floatval(($price-$price*$rate)*0.5*(1-$rate)*(1+$rate));
    $integral = sprintf("%.2f", $res);
    return $integral;
}

/**
 * 计算红包大约数
 */
function getBonusCount($all_money,$min_money,$max_money){
//    x*0.95*min+X*0.05*max = all
    $count = ceil($all_money/(($min_money*0.95+$max_money*0.05)));
    return $count+0;
}
/**
 * 店铺类型配置
 */
function merType($type = 0){
    if(!$type){return '普通店';}
    switch($type){
        case 1: return '旗舰店'; break;
        case 2: return '专卖店'; break;
        case 3: return '专营店'; break;
    }
}
/**
 * 发货地描述
 */
function countryDesc($type){
    if(!$type){return '';}
    switch($type){
        case 1: return '境内发货'; break;
        case 2: return '保税仓发货'; break;
        case 3: return '境外发货'; break;
        default: return $type;
    }
}
/**
 * 经纬度计算
 */
function getDistance($lat1=0, $lng1=0, $lat2=0, $lng2=0)
{
    $earthRadius = 6367000; //approximate radius of earth in meters
    $lat1 = ($lat1 * pi() ) / 180;
    $lng1 = ($lng1 * pi() ) / 180;

    $lat2 = ($lat2 * pi() ) / 180;
    $lng2 = ($lng2 * pi() ) / 180;

    $calcLongitude = $lng2 - $lng1;
    $calcLatitude = $lat2 - $lat1;
    $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
    $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
    $calculatedDistance = $earthRadius * $stepTwo;
    //公里数 保留一位小数
    return sprintf("%.1f",round($calculatedDistance)/1000);
}

/**
* 积分增减函数（带发消息）sendSystemMsg
* 1获的 2消费获得 3回退 4兑换 5参与活动 6其他减少
*/
function integralChange($integral=0,$act_type,$content='',$user_id){
    if(!$act_type) return false;
    if(!$user_id) return false;
    if(!$integral) return false;
    //积分明细记录
    $data['use_integral'] = $integral;
    $data['user_id'] = $user_id;
    $data['act_type'] = $act_type;
    $data['reason'] = $content;
    $data['create_time'] = time();
    if($act_type == '1' || $act_type == '2' || $act_type == '3'){
        $res = M('User')->where("id={$user_id}")->setInc('integral',$integral);
    }elseif($act_type == '4' || $act_type == '5' || $act_type == '6'){

        $res = M('User')->where("id={$user_id}")->setDec('integral',$integral);
    }
    if($res){
        M('IntegralLog')->add($data);
        sendSystemMsg($content,$user_id);
        return true;
    }else{
        return false;
    }
}
/**
 * 余额增减函数（带发消息）sendSystemMsg
 * 1线上充值 2线下充值 3消费 4提现 5退款 6转账出 7转账收入 8积分兑换余额 9其他增加 10其他减少
 */
function balanceChange($money=0,$act_type,$reason='',$user_id){
    if(!$act_type) return false;
    if(!$user_id) return false;
    if(!$money) return false;
    //余额明细记录
    $data['money'] = $money;
    $data['user_id'] = $user_id;
    $data['act_type'] = $act_type;
    $data['reason'] = $reason;
    $data['create_time'] = time();
    $res = '';

    if($act_type == '1' || $act_type == '2' || $act_type == '5' || $act_type == '7' || $act_type == '8' || $act_type == '9'){
        $res = M('User')->where("id={$user_id}")->setInc('balance',$money);
    }elseif($act_type == '3' || $act_type == '4' || $act_type == '6' || $act_type == '10'){
        $res = M('User')->where("id={$user_id}")->setDec('balance',$money);
    }elseif($act_type == '10'){
        $res = M('User')->where("id={$user_id}")->setDec('integral',$money);
    }
    if($res){
        M('BalanceLog')->add($data);
        sendSystemMsg($reason,$user_id);return true;
    }else{
        return false;
    }
}
/**
 * 代金券增减函数(还需修改)
 * $act_type操作类型 1获得 2兑换余额 3消费 4退款
 * $voucher_type 购物券类型 1红红 2黄 3蓝
 */
//function voucherChange($money,$voucher_type,$act_type,$reason='',$user_id){
//    if(!$act_type) return false;
//    if(!$voucher_type) return false;
//    if(!$user_id) return false;
//    if(!$money) return false;
//    //代金券明细记录
//    $data['money'] = $money;
//    $data['user_id'] = $user_id;
//    $data['act_type'] = $act_type;
//    $data['reason'] = $reason;
//    $data['type'] = $reason;
//    $data['create_time'] = time();
//    $res = '';
//    if($act_type == '1' || $act_type == '2' || $act_type == '4' ){
//        $res = M('Vouchers')->where("user_id={$user_id} AND type={$voucher_type}")->setInc('vouchers_val',$money);
//    }elseif($act_type == '3'){
//        $res = M('Vouchers')->where("id={$user_id} AND type={$voucher_type}")->setDec('vouchers_val',$money);
//    }
//    if($res){
//        M('VouchersLog')->add($data);
//        sendSystemMsg($reason,$user_id);return true;
//    }else{
//        return false;
//    }
//}

/**
 * 代金券增加函数(还需修改)
 * $act_type操作类型 1获得 2兑换余额 3消费 4退款
 * $voucher_type 购物券类型 1红红 2黄 3蓝
 */
function voucherChange($money,$voucher_type,$act_type,$reason='',$user_id){
    if(!$act_type) return false;
    if(!$voucher_type) return false;
    if(!$user_id) return false;
    if(!$money) return false;
    //代金券明细记录
    $data['money'] = $money;
    $data['user_id'] = $user_id;
    $data['act_type'] = $act_type;
    $data['reason'] = $reason;
//    $data['type'] = $voucher_type;
    $data['create_time'] = time();
    $res = '';
    if($act_type == '1' || $act_type == '2' || $act_type == '4' ){
        $add_data['user_id'] = $user_id;
        $add_data['type'] = $voucher_type;
        $add_data['money'] = $money;
        $add_data['create_time'] = time();
        $add_data['end_time'] = time()+C('DELAY_TIME');
        $res = M('Vouchers')->add($add_data);
    }
    if($res){
        $data['vouchers_id'] = $res;
        M('VouchersLog')->add($data);
        sendSystemMsg($reason,$user_id);return true;
    }else{
        return false;
    }
}
//打印函数
function p($data){
    echo '<pre style="display: block;padding: 9.5px;margin: 0px 0px 10px;font-size: 13px;line-height: 1.42857;
    color: #333;word-break: break-all;word-wrap: break-word;background-color: #F5F5F5;
    border: 1px solid #CCC;border-radius: 4px;">'.print_r($data,true).'</pre>';
}
//获取登录id
function getMerchantId(){
    return $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
}
function getMerchantName(){
    $id = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
    $name = M('merchant')->field("merchant_name")->where("id={$id}")->find()['merchant_name'];
    return $name;
}
