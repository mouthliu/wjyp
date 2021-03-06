<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 汽车购订单
 * Class CarOrderController
 * @package Api\Controller
 */
class CarOrderController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 新增汽车购订单
     * 传递参数的方式：post
     * 需要传递的参数：
     * 汽车购ID：car_id
     * 购买数量：num
     */
    public function addOrder(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('CarOrder','Logic')->addOrder(I('post.'),$user_id);
    }

    /**
     * 余额支付
     * 订单ID：order_id,
     */
    public function balancePay(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['order_id'])){
            apiResponse('0','订单ID不能为空');
        }
        if(!in_array($_POST['discount_type'],array('0','1','2','3'))){
            apiResponse('0','使用代金券的类型错误');
        }
        $order_info = M('CarOrder')->where(array('id'=>$_POST['order_id']))->find();
        if(empty($order_info)){
            apiResponse('0','订单信息查询失败');
        }
        $user_info = M('User')->where(array('id'=>$user_id))->find();
        if($user_info['balance']<$order_info['order_price']){
            apiResponse('0','余额不足');
        }

        //扣除余额。修改订单状态
        M('User')->where(array('id'=>$user_id))->setDec('balance',$order_info['order_price']);
        $res = M('CarOrder')->where(array('id'=>$order_info['id']))->data(array('status'=>1,'pay_type'=>3,'update_time'=>time()))->save();
        if($res){
            apiResponse('1','支付成功');
        }else{
            apiResponse('0','支付失败');
        }
    }

    /**
     * 积分支付
     */
    public function integralPay(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['order_id'])){
            apiResponse('0','订单ID不能为空');
        }
        $order_info = M('CarOrder')->where(array('id'=>$_POST['order_id']))->find();
        if(empty($order_info)){
            apiResponse('0','订单信息查询失败');
        }
        $car_info = M('CarBuy')->where(array('id'=>$order_info['car_id']))->find();
        $user_info = M('User')->where(array('id'=>$user_id))->find();
        if($user_info['integral']<$car_info['integral']){
            apiResponse('0','积分不足');
        }

        //扣除积分。修改订单状态
        M('User')->where(array('id'=>$user_id))->setDec('integral',$car_info['integral']);
        $res = M('CarOrder')->where(array('id'=>$order_info['id']))->data(array('status'=>1,'pay_type'=>4,'integral'=>$car_info['integral'],'update_time'=>time()))->save();
        if($res){
            apiResponse('1','支付成功');
        }else{
            apiResponse('0','支付失败');
        }
    }

    /**
     * 订单列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 类型：type 1全部 2待付款 3办手续中 4待评价
     * 分页参数:p
     */
    public function orderList(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(!in_array($_POST['type'],array('1','2','3','4'))){
            apiResponse('0','类型错误');
        }
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        switch($_POST['type']){
            case 1:$where['status'] = array('neq',9);break;
            case 2:$where['status'] = array('eq',0);break;
            case 3:$where['status'] = array('eq',1);break;
            case 4:$where['status'] = array('eq',2);break;
        }
        $where['user_id'] = $user_id;
        $where['is_user_delete'] = array('eq',0);
        $list = M('CarOrder')->where($where)
            ->field('id as order_id,car_id,car_img,car_name,pre_money,num,discount,yellow_discount,blue_discount,goods_price,order_price,integral,shop_name,lng,lat,pay_type,true_pre_money,status')
            ->order('create_time desc')
            ->page($_POST['p'].',10')
            ->select();
        if(empty($list)){
            $message = $_POST['p']==1?'暂无订单数据':'无更多订单数据';
            apiResponse('1',$message);
        }
        foreach($list as $k => $v){
            $path = M('File')->where(array('id'=>$v['car_img']))->getField('path');
            $list[$k]['car_img'] = $path?C('API_URL').$path:'';
        }
        apiResponse('1','请求成功',$list);
    }

    /**
     * 订单详情
     * 传参： 订单ID order_id
     */
    public function orderInfo(){
        if(empty($_POST['order_id'])){
            apiResponse('0','订单ID不能为空');
        }
        $where['id'] = $_POST['order_id'];
        $order_info = M('CarOrder')->where($where)
            ->field('id as order_id,order_sn,car_img,car_name,all_price,num,pre_money,true_pre_money,discount,yellow_discount,blue_discount,goods_price,order_price,integral,pay_type,shop_name,lng,lat,address,shop_mobile,contact_name,contact_mobile,create_time,status')
            ->find();
        if(!$order_info){
            apiResponse('0','信息查询失败');
        }
        $path = M('File')->where(array('id'=>$order_info['car_img']))->getField('path');
        $order_info['car_img'] = $path?C('API_URL').$path:'';
        $order_info['create_time'] = date('Y-m-d H:i',$order_info['create_time']);

        $order_info['discount'] = number_format($order_info['discount']*$order_info['goods_price']*0.01,2);
        $order_info['yellow_discount'] = number_format($order_info['yellow_discount']*$order_info['goods_price']*0.01,2);
        $order_info['blue_discount'] = number_format($order_info['blue_discount']*$order_info['goods_price']*0.01,2);

        apiResponse('1','请求成功',$order_info);
    }

    /**
     * 评价标签
     * 订单ID：order_id
     */
    public function commentPage(){
        if(empty($_POST['order_id'])){
            apiResponse('0','订单ID不能为空');
        }
        $order_info = M('CarOrder')->where(array('id'=>$_POST['order_id']))
            ->field('car_img,car_name,pre_money,num,goods_price,order_price,true_pre_money,all_price')
            ->find();
        if(!$order_info){
            apiResponse('0','订单信息查询失败');
        }
        $path = M('File')->where(array('id'=>$order_info['car_img']))->getField('path');
        $order_info['car_img'] = $path?C('API_URL').$path:'';
        $label_list = M('CarLabel')->where(array('status'=>array('eq',1)))
            ->field('id as label_id,car_label')
            ->select();
        $order_info['label_list'] = $label_list?$label_list:array();
        apiResponse('1','请求成功',$order_info);
    }

    /**
     * 新增评价
     * order_id,exterior ,space  controllability consumption label_str pictures content
     */
    public function addComment(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['order_id'])){
            apiResponse('0','订单ID不能为空');
        }
        $order_info = M('CarOrder')->where(array('id'=>$_POST['order_id']))->find();
        if(!$order_info){
            apiResponse('0','订单信息不存在');
        }
        $res = api('UploadPic/upload', array(array('save_path' => 'CarOrder')));
        $pictures = array();
        foreach ($res as $value) {
            $pictures[] = $value['id'];
        }
        $data['pictures'] = $pictures?implode(',',$pictures):"";
        $data['order_id'] = $order_info['id'];
        $data['car_id'] = $order_info['car_id'];
        $data['user_id'] = $user_id;
        $data['exterior'] = $_POST['exterior']?$_POST['exterior']:0;
        $data['space'] = $_POST['space']?$_POST['space']:0;
        $data['controllability'] = $_POST['controllability']?$_POST['controllability']:0;
        $data['consumption'] = $_POST['exterior']?$_POST['consumption']:0;
        $data['label_str'] = $_POST['label_str']?$_POST['label_str']:"";
        $data['content'] = $_POST['content']?$_POST['content']:"";
        $data['create_time'] = time();
        $res = M('CarComment')->data($data)->add();
        if($res){
            M('CarOrder')->where(array('id'=>$_POST['order_id']))->data(array('status'=>4))->save();
            apiResponse('1','评价成功');
        }else{
            apiResponse('0','评价失败');
        }
    }

    /**
     * 取消订单
     * 传递参数的方式：post
     * 需要传递的参数：
     * 订单id order_id
     */
    public function cancelOrder(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['order_id'])){
            apiResponse('0','订单ID不能为空');
        }
        $order_info = M('CarOrder')->where(array('id'=>$_POST['order_id']))->find();
        if(!$order_info){
            apiResponse('0','订单信息查询失败');
        }
        if($order_info['status']==0){

        }else{
            if($order_info['pay_type']= 4){
                M('User')->where(array('id'=>$order_info['user_id']))->setInc('integral',$order_info['integral']);
            }else{
                M('User')->where(array('id'=>$order_info['user_id']))->setInc('balance',$order_info['order_price']);
            }
        }
        $res = M('CarOrder')->where(array('id'=>$order_info['id']))->data(array('status'=>5))->save();
        if($res){
            apiResponse('1','取消订单成功');
        }else{
            apiResponse('0','取消订单失败');
        }
    }

    /**
     * 删除订单
     */
    public function deleteOrder(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['order_id'])){
            apiResponse('0','订单ID不能为空');
        }
        $order_info = M('CarOrder')->where(array('id'=>$_POST['order_id']))->find();
        if(!$order_info){
            apiResponse('0','订单信息查询失败');
        }
        $res = M('CarOrder')->where(array('id'=>$order_info['id']))->data(array('is_user_delete'=>1))->save();
        if($res){
            apiResponse('1','删除订单成功');
        }else{
            apiResponse('0','删除订单失败');
        }
    }
}