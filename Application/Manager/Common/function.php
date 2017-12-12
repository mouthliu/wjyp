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
    $admin = session('admin');
    if (empty($admin)) {
        return 0;
    } else {
        return session('admin_sign') == data_auth_sign($admin) ? $admin['a_id'] : 0;
    }
}

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
 * 三方登录类型
 * 1.微信注册，2.微博注册，3.QQ注册，
 */
function registerType($type)
{
    switch ($type) {
        case '1': return '微信';     break;
        case '2': return '微博';     break;
        case '3': return 'QQ';     break;
        default : return '手机';    break;
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
 * [merOrderStatus 商家订单状态]
 * 0待支付，1待发货，2待收货，3待评价，4已完成
 * @return [string] [状态]
 */
//function merOrderStatus($status)
//{
// switch ($status) {
//        case '0': return '待支付';     break;
//        case '1': return '待发货';     break;
//        case '2': return '待收货';     break;
//        case '3': return '待评价';     break;
//        case '4': return '已完成';     break;
//        default : return '';           break;
//    }
//}

/**
 * 提现支付方式
 * 0未选择支付方式 1支付宝 2微信 3余额
 */
function payType2($status)
{
    switch ($status) {
        case '0': return 'N/A';     break;
        case '1': return '支付宝';     break;
        case '2': return '微信';     break;
        case '3': return '储蓄卡';     break;
        default : return '';           break;
    }
}

/**
 *  商家审核状态]
 * 0待审核，1已通过，2已拒绝，
 * @return [string] [状态]
 */

function merReferStatus($status)
{
    switch ($status) {
        case '0': return '待审核';     break;
        case '1': return '已通过';     break;
        case '2': return '已拒绝';     break;
        default : return '';           break;
    }
}
function getManagerId(){
    return $_SESSION['wjyp_manager']['admin']['a_id'];
}
function getManagerName(){
    $id = $_SESSION['wjyp_manager']['admin']['a_id'];
    $name = M('Administrator')->field("account")->where("id={$id}")->find()['account'];
    return $name;
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
 *  余额明细状态
 * 操作类型 1线上充值 2线下充值 3消费 4提现 5退款 6转账出 7转账收入 8积分兑换余额 9其他来源
 * @return [string] [状态]
 */

function balanceStatus($status){
    switch ($status) {
        case '1': return '线上充值';     break;
        case '2': return '线下充值';     break;
        case '3': return '消费';     break;
        case '4': return '提现';     break;
        case '5': return '退款';     break;
        case '6': return '转账支出';     break;
        case '7': return '转账收入';     break;
        case '8': return '积分兑换';     break;
        case '9': return '后台修改';     break;
        default : return '';           break;
    }
}

/**
 *  积分明细状态
 * 操作类型 1获的 2消费 3回退 4兑换 5其他减少
 * @return [string] [状态]
 */
function integralStatus($status){
    switch ($status) {
        case '1': return '增加';     break;
        case '2': return '消费';     break;
        case '3': return '回退';     break;
        case '4': return '兑换';     break;
        case '5': return '参与活动';     break;
        case '6': return '其他减少';     break;
        case '7': return '后台操作';     break;
        default : return '';           break;
    }
}
/**
 *  积分明细状态
 * 操作类型 1获得 2兑换余额 3消费 4退款 5后台操作
 * @return [string] [状态]
 */
function vouchersLog($status){
    switch ($status) {
        case '1': return '获得';     break;
        case '2': return '兑换余额';     break;
        case '3': return '消费';     break;
        case '4': return '退款';     break;
        case '5': return '后台操作';     break;
        default : return '';           break;
    }
}
/**
 * 品牌级别等级(字号)
 */
function zhLevel($brand_level){
    switch ($brand_level) {
        case '1': return '中华老字号';     break;
        case '2': return '百年老字号';     break;
        case '3': return '地方老字号';     break;
        case '4': return '民间老字号';     break;
        default : return '';           break;
    }
}
/**
 * 品牌级别等级(商标)
 */
function sbLevel($brand_level){
    switch ($brand_level) {
        case '1': return '驰名商标';     break;
        case '2': return '著名商标';     break;
        case '3': return '知名商标';     break;
        default : return '';           break;
    }
}
/**
 * 品牌级别等级(国内外)
 */
function countryLevel($brand_level)
{
    switch ($brand_level) {
        case '1':
            return '国内商标';
            break;
        case '2':
            return '国外商标';
            break;
        default :
            return '';
            break;
    }
}
/**
 * 获取分类路径
 */
function getCatePath2($model,$cate_id){
    if(!$cate_id){
        return '顶级分类';
    }
    $three_cate_info = M($model)->field('short_name,parent_id')->where("id = {$cate_id}")->find();
    if(!$three_cate_info['parent_id']){
        return '顶级分类';
    }
    $two_cate_info = M($model)->field('short_name,parent_id')->where("id = {$three_cate_info['parent_id']}")->find();
    if(!$three_cate_info['parent_id']){
        return '顶级分类 → '.$three_cate_info['short_name'];
    };
    $one_cate_name = M($model)->where("id = {$two_cate_info['parent_id']}")->getField('short_name');
    if(!$two_cate_info['parent_id']){
        return '顶级分类 → '.$two_cate_info['short_name'].' → '.$three_cate_info['short_name'];
    }
    if(!$one_cate_name){
        return '顶级分类 → '.$two_cate_info['short_name'].' → '.$three_cate_info['short_name'];
    }
    return $one_cate_name.' → '.$two_cate_info['short_name'].' → '.$three_cate_info['short_name'];
}
/**
 * @param $status
 * @return bool|string
 * 获取数据的状态操作
 */
function show_status_label($status) {
    switch ($status) {
        case 0  : return    'label-info label-info-hover';       break;
        case 1  : return    'label-inverse label-inverse-hover';    break;
        case 2  : return    '';		       break;
        default : return    false;               break;
    }
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


