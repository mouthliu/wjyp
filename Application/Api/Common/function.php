<?php
/**
 *  API返回信息格式函数 ；失败：code=-1登录失效，code=0失败 code=1成功
 * @param string $code
 * @param string $message
 * @param array $data
 */
function apiResponse($code = '0', $message = '',$data = array(),$nums =0){
    header('Access-Control-Allow-Origin: *');
    header('Content-Type:application/json; charset=utf-8');
    $result = array(
        'code'=>$code,
        'message'=>$message,
        'data'=>$data,
        'nums'=>''.$nums
    );
    die(json_encode($result,JSON_UNESCAPED_UNICODE));
}

/**
 * 检查是否是手机号
 * @param $mobile
 * @return bool
 */
function isMobile($mobile){
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match(C('MOBILE'), $mobile) ? true : false;
}

/**
 * xml转换成数组
 * @param $xml
 * @return mixed
 */
function xmlToArray($xml)
{
    //将XML转为array
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}

/**
 * 获取一个月的开始时间和结束时间
 */
function getMonthStartEnd($date){
    $start_time = $date . '-01';
    $end_time   = date('Y-m-d', strtotime("$start_time +1 month -1 day"));
    return array('start_time' => $start_time, 'end_time' => $end_time);
}

/**
 * 退换货原因类型
 * @return [string] [状态]
 */
function reason_type($reason_type){
    switch ($reason_type) {
        case '1': return '不喜欢';     break;
        case '2': return '退运费';     break;
        case '3': return '质量问题';     break;
        case '4': return '商品与描述不符';     break;
        case '5': return '商品破损';     break;
        case '6': return '未按约定时间发货';     break;
        case '7': return '假冒品牌';     break;

        default : return '';           break;
    }
}

/**
 * 退换货申请状态
 * @return [string] [状态]
 */
function apply_status($status){
    switch ($status) {
        case '0': return '待审核';     break;
        case '1': return '已同意申请';     break;
        case '2': return '已拒绝申请';     break;
        case '3': return '已完成';     break;
        default : return '';           break;
    }
}
/**
 * 前台快递公司选项
 * @return [string] [状态]
 */
function shipping_company($status){
    switch ($status) {
        case '0': return '中通快递';     break;
        case '1': return '申通快递';     break;
        case '2': return '圆通快递';     break;
        case '3': return '宅急送';     break;
        case '4': return '韵达快递';     break;
        case '5': return 'EMS';     break;
        case '6': return '汇通快递';     break;
        case '7': return '天天快递';     break;
        default : return '';           break;
    }
}

/**
 * 处理ads图片
 */
function dealAds($ads_row){
    if($ads_row){
    }else{
        $ads_row =  array(
            "ads_id"=> "0",
            "picture"=> "",
            "desc"=> "",
            "href"=> "",
            "position"=> ""
        );
    }
    return $ads_row;
}

/**
 * @param $content
 * @return $content
 * APP设置文章的图片宽度为100%，并拼接成绝对地址
 */
function setPictureStyle($content){
    preg_match_all('/src=\"\/?(.*?)\"/',$content,$match);
    foreach($match[1] as $key => $src){
        if(!strpos($src,'://')){
            $content = str_replace('/'.$src,C('API_URL')."/".$src."\" width=100%",$content);
        }
    }
    return $content;
}