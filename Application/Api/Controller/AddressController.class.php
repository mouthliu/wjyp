<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 地址模块控制器
 * Class AddressController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class AddressController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 新增收货地址
     * 传递参数的方式：post
     * 需要传递的参数：
     * 收货人：receiver
     * 联系电话:phone
     * 省：province
     * 市：city
     * 区：area
     * 街道:street
     * 省id:province_id
     * 市id:city_id
     * 区id:area_id
     * 街道id:street_id
     * 详细地址:address
     * 经度：lng
     * 纬度：lat
     */
    public function addAddress(){
        //检查登录是否过期
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        //判断参数的合法性
        if (empty($_POST['receiver'])) {
            apiResponse('0', '请输入收货人');
        }
        if (empty($_POST['phone'])) {
            apiResponse('0', '请输入联系电话');
        }
        if (empty($_POST['province_id']) || empty($_POST['city_id']) || empty($_POST['area_id'])) {
            apiResponse('0', '请输入所在省市地区');
        }
        if (empty($_POST['street'])) {
            apiResponse('0', '请输入街道地址');
        }
        if (empty($_POST['address'])) {
            apiResponse('0', '请输入详细地址');
        }

        D('Address','Logic')->addAddress(I('post.'),$user_id);
    }

    /**
     * 收货地址列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 分页参数：p
     */
    public function addressList(){
        //检查登录是否过期
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        D('Address','Logic')->addressList(I('post.'),$user_id);
    }

    /*
     * 编辑收货地址
     * 传递参数的方式：post
     * 需要传递的参数:
     * 需要修改地址的ID: address_id
     * 收货人：receiver
     * 收货人联系方式：phone
     * 省：province
     * 市：city
     * 区：area
     * 街道:street
     * 省id:province_id
     * 市id:city_id
     * 区id:area_id
     * 街道id:street_id
     * 详细地址: address
     * 经度: lng
     * 纬度：lat
     */
    public function editAddress(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        //判断参数的合法性
        if (empty($_POST['address_id'])) {
            apiResponse('0', '请选择要编辑的收货地址');
        }
        //判断参数的合法性
        if (empty($_POST['receiver'])) {
            apiResponse('0', '请输入收货人');
        }
        //判断参数的合法性
        if (empty($_POST['phone'])) {
            apiResponse('0', '请输入收货人电话');
        }
        if (empty($_POST['province_id']) || empty($_POST['city_id']) || empty($_POST['area_id'])) {
            apiResponse('0', '请输入所在省市地区');
        }
        //判断参数的合法性
        if (empty($_POST['address'])) {
            apiResponse('0', '请输入详细地址');
        }
        D('Address','Logic')->editAddress(I('post.'),$user_id);
    }

    /**
     * 设置默认收货地址
     * 请求方式: post
     * 请求参数:
     *  地址id: address_id
     *  用户token
     */
    public function setDefault(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['address_id'])){
            apiResponse('0','请输入地址ID');
        }
        D('Address','Logic')->setDefault(I('post.'),$user_id);
    }
    /**
     * 获取一条收货地址
     * 请求方式: post
     * 请求参数:
     *     地址id: address_id
     */
    public function getOneAddress(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['address_id'])){
            apiResponse('0','请输入地址ID');
        }
        D('Address','Logic')->getOneAddress(I('post.'),$user_id);
    }

    /**
     * 删除收货地址
     * 请求方式: post
     * 请求参数:
     *     用户token :token
     *     地址id : address_id;
     */
    public function delAddress(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['address_id'])){
            apiResponse('0','请输入地址ID');
        }
        D('Address','Logic')->delAddress(I('post.'),$user_id);
    }

    /**
     * 获取省市区函数
     * 传递参数方式 : post
     * 需要传递的参数:
     *   上一级地域id : region_id（可选，默认返回北京）
     */
    public function getRegion(){
        //判断参数
        D('Address', 'Logic')->getRegion(I('post.'));
    }
    /**
     * 获取街道接口
     * 请求方式：post
     * 请求参数：
     *   区县ID ：area_id
     */
    public function getStreet(){
        if(empty($_POST['area_id'])){
            apiResponse('0','请传入区县ID');
        }
        D('Address','Logic')->getStreet($_POST['area_id']);
    }
    /**
     * 逆编码获取经纬度 0I7OFb4ZOAkK9LGlvV5UzM8j2moai9RC
     */
//    function getLA(){
//        $ak = C('BD_AK');
//        $url = "http://api.map.baidu.com/cloudgc/v1?ak={$ak}&address=北京市海淀区上地十街10号&city=北京";
//        $res = file_get_contents($url);
//        $data = json_decode($res,true);
//        if($data){
//            apiResponse('0','',$data['result'][0]['location']);
//            return $data['result']['location'];
//        }else{
//            return array('lat'=>'0','lng'=>'0');
//        }
//
//    }
}