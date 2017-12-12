<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 售后订单
 * Class AfteraleController
 * @package Api\Controller
 */
class AfterSaleController extends BaseController{

    /**
     * 售后原因
     * 方式 post
     * 参数 无
     * User: Vernon
     * Date: 2017-12-8
     */
    public function cause(){
        $cause = M('cause')->field('id as cause_id,name')->select();
        apiResponse('1','请求成功',$cause);
    }

    /**
     * 售后类型
     * 方式 post
     * 参数 无
     * User: Vernon
     * Date: 2017-12-8
     */
    public function reason(){
        $reason = M('reason')->field('id as reason_id,name')->select();
        apiResponse('1','请求成功',$reason);
    }



    /**
     * 订单状态
     * 方式 post
     * 参数 cause_id 售后原因 reason_id 售后类型  back_money  退款金额 back_desc 退款说明 back_img 凭证
     * User: Vernon
     * Date: 2017-12-8
     */
    public function backApply(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['reason_id'])){
            apiResponse('0','请选择售后类型');
        }else{
            $add['reason_id'] = $_POST['reason_id'];
        }
        if(empty($_POST['cause_id'])){
            apiResponse('0','请选择售后原因');
        }else{
            $add['cause_id'] = $_POST['cause_id'];
        }
        if(empty($_POST['back_money'])){
            apiResponse('0','退款金额不能为空');
        }else{
            $add['back_money'] = $_POST['back_money'];
        }
        if(empty($_POST['goods_status'])){
            apiResponse('0','货物状态不能为空');
        }else{
            $add['goods_status'] = $_POST['goods_status'];
        }
        if(empty($_POST['order_id'])){
            apiResponse('0','订单ID不能为空');
        }else{
            $add['order_id'] = $_POST['order_id'];
        }
        if(empty($_POST['order_type'])){
            apiResponse('0','订单类型不能为空');
        }
        if(empty($_POST['goods_id'])){
            apiResponse('0','商品id不能为空');
        }
//        订单类型 1普通订单 2拼单购 3无界预购 4比价购 5积分抽奖
        switch($_POST['order_type']){
            case 1:
                $add['order_goods_id'] = M('order_goods')->where(array('order_id'=>$_POST['order_id'],'goods_id'=>$_POST['goods_id']))->getField('id');
                break;
            case 2:
                $add['order_goods_id'] = M('group_order_goods')->where(array('group_order_id'=>$_POST['order_id'],'goods_id'=>$_POST['goods_id']))->getField('id');
                break;
            case 3:
                $add['order_goods_id'] = M('pre_order_goods')->where(array('pre_order_id'=>$_POST['order_id'],'goods_id'=>$_POST['goods_id']))->getField('id');
                break;
        }
        $res = api('UploadPic/upload', array(array('save_path' => 'AfterSale')));
        foreach ($res as $value) {
            $back_img[] = $value['id'];
        }
        $add['back_img'] = implode(',',$back_img);
        $add['create_time'] = time();
        $data = M('back_apply')->add($add);
        if($data){
            apiResponse('1','申请售后成功!');
        }else{
            apiResponse('0','申请售后失败!');
        }
    }
}