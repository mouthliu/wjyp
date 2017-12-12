<?php
/**
 * 后台公共文件
 * 主要定义后台公共函数库
 */

/**
 * 检测用户是否登录
 * @return integer 0-未登录，大于0-当前登录用户ID
 */
function is_login() {
    $admin = session('merchant_admin');
    if (empty($admin)) {
        return 0;
    } else {
        return session('merchant_admin_sign') == data_auth_sign($admin) ? $admin['a_id'] : 0;
    }
}

// function getMerchantId(){
//     return $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
// }

// function getMerchantName(){
//     $id = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
//     $name = M('merchant')->field("merchant_name")->where("id={$id}")->find()['merchant_name'];
//     return $name;
// }

/**
 * @param null $a_id
 * @return boolean true-超管理员，false-非超管理员
 * 是否是超级管理员
 */
function is_administrator($a_id = null) {
    $a_id = is_null($a_id) ? is_login() : $a_id;
    return $a_id && (intval($a_id) === C('USER_ADMINISTRATOR'));
}

/**
 * @param string $type
 * @return mixed
 * 获取属性类型信息
 */
function get_attribute_type($type = '') {
    // TODO 可以加入系统配置
    static $_type = array(
        'num'       =>  array('数字','int(10) UNSIGNED NOT NULL'),
        'string'    =>  array('字符串','varchar(255) NOT NULL'),
        'textarea'  =>  array('文本框','text NOT NULL'),
        'datetime'  =>  array('时间','int(10) NOT NULL'),
        'bool'      =>  array('布尔','tinyint(2) NOT NULL'),
        'select'    =>  array('枚举','char(50) NOT NULL'),
    	'radio'		=>	array('单选','char(10) NOT NULL'),
    	'checkbox'	=>	array('多选','varchar(100) NOT NULL'),
    	'editor'    =>  array('编辑器','text NOT NULL'),
    	'picture'   =>  array('上传图片','int(10) UNSIGNED NOT NULL'),
    	'file'    	=>  array('上传附件','int(10) UNSIGNED NOT NULL'),
    );
    return $type?$_type[$type][0]:$_type;
}

/**
 * 获取对应状态的文字信息
 * @param int $status
 * @return string 状态文字 ，false 未获取到
 */
function get_status_title($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case 0  : return    '禁用';     break;
        case 1  : return    '正常';     break;
        default : return    false;      break;
    }
}

/**
 * 获取性别
 * @param int $status
 * @return string 状态文字 ，false 未获取到
 */
function get_sex($sex = null) {
    if(!isset($sex)) {
        return false;
    }
    switch ($sex) {
        case 0  : return    '保密';     break;
        case 1  : return    '男';     break;
        case 2  : return    '女';     break;
        default : return    false;      break;
    }
}
/**
 * @param null $status
 * @return bool|string
 * 意见反馈对应状态
 */
function get_feedback_status_title($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case 0 : return    '未处理';   break;
        case 1  : return    '已处理';     break;
        default : return    false;      break;
    }
}
/**
 * @param null $status
 * @return bool|string
 * 获取评价状态
 */
function get_comment_status_title($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case 0 : return    '未审核';   break;
        case 1  : return    '已审核';     break;
        default : return    false;      break;
    }
}
/**
 * @param null $status
 * @return bool|string
 */
function get_comment_status_name($status = null) {
    if(!isset($status)) {
        return false;
    }
    switch ($status) {
        case 0 : return    '未审核';   break;
        case 1  : return    '已审核';     break;
        default : return    false;      break;
    }
}
/**
 * @param $status
 * @return bool|string
 * 获取数据的状态操作
 */
function show_status_name($status) {
    switch ($status) {
        case 0  : return    '启用';     break;
        case 1  : return    '禁用';     break;
        case 2  : return    '审核';	 break;
        default : return    false;     break;
    }
}

/**
 * @param $status
 * @return bool|string
 * 获取数据的状态操作
 */
function show_status_icon($status) {
    switch ($status) {
        case 0  : return    'fa fa-ok-sign';       break;
        case 1  : return    'fa fa-minus-sign';    break;
        case 2  : return    '';		       break;
        default : return    false;               break;
    }
}

/**
 * @param string $table
 * @return string
 * 获取表的中文名称
 */
function get_table_name($table = '') {
    switch ($table) {
        case 'Action'           : return    '行为表';       break;
        case 'ActionLog'        : return    '行为日志表';    break;
        case 'Administrator'    : return    '管理员表';    break;
        default                 : return    '';             break;
    }
}

/**
 * @param $status
 * @return string
 * 获取插件状态名称
 */
function get_plugins_status_title($status) {
    switch ($status) {
        case 1       : return    '启用';    break;
        case 9       : return    '损坏';    break;
        case null    : return    '未安装';  break;
        case 0       : return    '禁用';    break;
        default      : return    '';       break;
    }
}

/**
 * @param $value
 * @param $config
 * @return mixed
 * 获取标记对应的数组类型配置信息
 */
function get_config_title($value, $config) {
    $list = C(''.$config.'');
    return empty($list[$value]) ? '' : $list[$value];
}

/**
 * @param $status
 * @return string
 * 获取发送状态
 */
function get_send_status($status) {
    switch ($status) {
        case 0       : return    '失败';    break;
        case 1       : return    '成功';    break;
        default      : return    '';       break;
    }
}

/**
 * @param $string
 * @return array
 * 分析枚举类型配置值 格式 a:名称1,b:名称2
 */
function parse_config_attr($string) {
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')) {
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    } else {
        $value  =   $array;
    }
    return $value;
}

/**
 * @param $string
 * @return array
 * 分析枚举类型字段值 格式 a:名称1,b:名称2
 * 暂时和 parse_config_attr功能相同
 * 但请不要互相使用，后期会调整
 */
function parse_field_attr($string) {
    if(0 === strpos($string,':')) {
        // 采用函数定义
        return   eval(substr($string,1).';');
    }
    $array = preg_split('/[,;\r\n]+/', trim($string, ",;\r\n"));
    if(strpos($string,':')) {
        $value  =   array();
        foreach ($array as $val) {
            list($k, $v) = explode(':', $val);
            $value[$k]   = $v;
        }
    } else {
        $value  =   $array;
    }
    return $value;
}

/**
 * @param $str  要执行替换的字符串
 * @param $rep_flag 替换标记
 * @param $tar_str 目标字符
 * @return mixed
 */
function replace($str, $rep_flag, $tar_str) {
    return $str = preg_replace("/{".$rep_flag."}/i", ''.$tar_str.'', $str);
}

/**
 * 创建像这样的查询: "IN('a','b')";
 * @access   public
 * @param    mix      $item_list      列表数组或字符串
 * @param    string   $field_name     字段名称
 * @return   void
 */
function db_create_in($item_list, $field_name = ''){
    if (empty($item_list)) {
        return $field_name . " IN ('') ";
    }
    else {
        if (!is_array($item_list)) {
            $item_list = explode(',', $item_list);
        }
        $item_list = array_unique($item_list);
        $item_list_tmp = '';
        foreach ($item_list AS $item) {
            if ($item !== '') {
                $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
            }
        }
        if (empty($item_list_tmp)) {
            return $field_name . " IN ('') ";
        }
        else {
            return $field_name . ' IN (' . $item_list_tmp . ') ';
        }
    }
}

/**
 * 	作用：将xml转为array
 */
function xmlToArray($xml){
    //将XML转为array
    $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    return $array_data;
}

/**
 * [feedbackStatus 反馈管理状态]
 */
function feedbackStatus($status)
{
     switch ($status) {

        case '0': return '未处理';     break;
        case '1': return '已处理';     break;
        default : return '';           break;

    }
}
/**
 * 用户注册类型
 * 1.手机注册，2.微博注册，3.微信注册，4.QQ注册
 */
function registerType($type)
{
    switch ($type) {
        case '1': return '手机注册';     break;
        case '2': return '微博注册';     break;
        case '3': return '微信注册';     break;
        case '4': return 'Q Q注册';     break;
        default : return '';           break;
    }
}

/**
 * 驿站类型
 */
function realyType($type)
{
    switch ($type) {
        case '1': return 'B端驿站';     break;
        case '2': return 'C端驿站';     break;
        default : return '';           break;
    }
}

/**
 * 驿站成员类型
 */
function staffType($type)
{
    switch ($type) {
        case '1': return '<span style="color:red;">管理员</span> ';     break;
        case '2': return '<span style="color:green;">员工</span> ';     break;
        default : return '';           break;
    }
}

/**
 * 意见反馈类型
 */
function feedBackObjectType($type)
{
    switch ($type) {
        case '1': return '用户';     break;
        case '2': return '企业用户（商贩）';     break;
        case '3': return '产地商家';     break;
        default : return '';           break;
    }
}

/**
 * 角色類型
 */
function roleType($type)
{
    switch ($type) {
        case '1': return '普通会员';     break;
        case '2': return '企业用户';     break;
        case '3': return '产地商家';     break;
        default : return '';           break;
    }
}

/**
 * 商家类型
 */
function merchantType($type)
{
    switch ($type) {
        case '1': return '<span style="color:red">产地商家</span>';     break;
        case '2': return '<span style="color:green">企业商家</span>';     break;
        default : return '<span style="color:#CCC">未选择</span>';           break;
    }
}

/**
 * 商家审核状态
 */
function merchantAuditStatus($status)
{
    switch ($status) {
        case '0': return '<span >新注册</span>';     break;
        case '1': return '<span style="color:red">待审核</span>';     break;
        case '2': return '<span style="color:green">成功</span>';     break;
        case '3': return '<span style="color:#cccccc">拒绝</span>';     break;
        default : return '';           break;
    }
}

/**
 * @param $type
 * 属性组类型
 */
function groupAttrType($type)
{
    switch ($type) {
        case '1': return '<span style="color:red">规格</span>';     break;
        case '2': return '<span style="color:green">品相</span>';     break;
        default : return '';           break;
    }
}
/**
 * 返回查询
 */
function likeArr($condition)
{
    return array('like','%'.trim($condition).'%');
}

/**
 * 图的位置
 */
function picListMenu($position)
{
    switch ($position) {
        case '1': return '首页';     break;
        default : return '';           break;
    }
}

/**
 * 商品状态
 */
function goodsType($type)
{
    switch ($type) {
        case '0': return '未出售';     break;
        case '1': return '出售中';     break;
        default : return '';           break;
    }
}
/**
 * 商品状态
 */
function auditType($type)
{
    switch ($type) {
        case '0': return '未审核';     break;
        case '1': return '已审核';     break;
        default : return '';           break;
    }
}

    /**
 * 图的位置
 */
function GoodsTypeAttrType($type)
{
    switch ($type) {
        case '1': return '<span style="color:red">规格</span>';     break;
        case '2': return '<span style="color:green">品相</span>';     break;
        default : return '';           break;
    }
}

/**
 * 角色
 * @param $type
 * @return string
 */
function role($type)
{
    switch ($type) {
        case '1': return '<span style="color:red">用户</span>';     break;
        case '2': return '<span style="color:green">企业</span>';     break;
        default : return '';           break;
    }
}

/**
 * 性别
 */
function sex($type)
{
    switch ($type) {
        case '1': return '男';     break;
        case '2': return '女';     break;
        default : return '';           break;
    }
}

/**
 * 订单类型
 *  0待支付，1已支付，2待发货(汇总之后转变)，3待取货，4待评价 5已完成 6已取消
 */
function orderStatus($status)
{
    switch ($status) {
        case '0': return '待支付';     break;
        case '1': return '已支付';     break;
        case '2': return '待发货';     break;
        case '3': return '待取货';     break;
        case '4': return '待评价';     break;
        case '5': return '已完成';     break;
        case '6': return '已取消';     break;
        default : return '';           break;
    }
}

/**
 * 箱子
 *  0未使用 1使用
 */
function isBox($status)
{
    switch ($status) {
        case '0': return '未使用';     break;
        case '1': return '已使用';     break;
        default : return '';           break;
    }
}

/**
 * 支付方式
 * 0未选择支付方式 1支付宝 2微信 3余额
 */
function payType($status)
{
    switch ($status) {
        case '0': return '未支付';     break;
        case '1': return '支付宝';     break;
        case '2': return '微信';     break;
        case '3': return '余额';     break;
        default : return '';           break;
    }
}

/**
 * 类型
 *  1普通用户 2企业用户，3 B端驿站，4 C端驿站  5原产地
 */
function objectType($type)
{
    switch ($type) {
        case '1': return '普通用户';     break;
        case '2': return '企业用户';     break;
        case '3': return 'B端驿站';     break;
        case '4': return 'C端驿站';     break;
        case '5': return '原产地';     break;
        default : return '';           break;
    }
}
/**
 * [carType 车型]
 * 1：4.2米车型，2：6.8米车型；3:9.6米车型；4：13米车型；5:17.5米车型
 */
function carType($type='')
{
    switch ($type) {
        case '1': return '4.2米车型';     break;
        case '2': return '6.8米车型';     break;
        case '3': return '9.6米车型';     break;
        case '4': return '13米车型';     break;
        case '5': return '17.5米车型';     break;
        default : return '';           break;
    } 
}
/**
 * @author mr.zhou
 * 获取 百度地图坐标
 */
function getMapCoord($address,$city)
{
    $ch = curl_init("http://api.map.baidu.com/geocoder/v2/?ak=".C('BAIDU_MAP_AK')."&output=json&address=".$address."&city=".$city) ;
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
    $coord = curl_exec($ch) ;
    $coord = json_decode($coord,true);
    return $coord['result']['location'];
    curl_close($ch);
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
 * 活动置0
 * @param $model
 * @param $field //要检测字段
 */
function checkActive($model,$field){

}
/**
 * @param $status
 * @return bool|string
 * 获取汽车购的的状态操作
 */
function get_car_order_status($status) {
    switch ($status) {
        case 0  : return    '待付款';       break;
        case 1  : return    '办手续中';    break;
        case 2  : return    '待评价';		       break;
        case 3  : return    '未设置';		       break;
        case 4  : return    '已完成';       break;
        case 5  : return    '已取消';    break;
        default : return    false;               break;
    }
}
