<?php
namespace Api\Controller;
/**
 * 获取支付参数
 * Class PayController
 * @package Api\Controller
 */
class PayController extends BaseController{
    /**
     * 获取支付宝支付参数
     * 传递参数的方式：post
     * 需要传递的参数：
     * 订单id：order_id
     * 使用代金券：discount_type 0不使用代金券  1使用红券  2使用黄券  3使用蓝券
     * 类型：type 1.充值,2汽车购订单  3房产购 4订单支付
     */
    public function getAlipayParam(){
        Vendor('Alipay.Alipay');
        if(empty($_POST['order_id'])){
            apiResponse('0','参数不完整');
        }
        $type_arr = array('1','2','3','4','5','6');
        if(!in_array($_POST['type'],$type_arr)){
            apiResponse('0','类型错误');
        }
//        if(!in_array($_POST['discount_type'],array('0','1','2','3'))){
//            apiResponse('0','使用代金券的类型错误');
//        }

        $order_sn = '';
        $pay_money = '';
        if($_POST['type'] == 1) {
            $info = M('UserMoney')->where(array('id' => $_POST['order_id']))->find();
            if (!$info) {
                apiResponse('0', '订单信息查询失败');
            }
            $order_sn  = $info['order_sn'];
            $pay_money = $info['money'];
        }else if($_POST['type'] == 2){
            $info = M('CarOrder')->where(array('id' => $_POST['order_id']))->find();
            if (!$info) {
                apiResponse('0', '订单信息查询失败');
            }
            if($_POST['discount_type']==1){
                //计算红券的使用金额
            }else if($_POST['discount_type']==2){

            }else if($_POST['discount_type']==3){

            }
            $order_sn  = $info['order_sn'];
            $pay_money = 0.01;
        }else if($_POST['type']==3){
            $info = M('HouseOrder')->where(array('id' => $_POST['order_id']))->find();
            if (!$info) {
                apiResponse('0', '订单信息查询失败');
            }
            if($_POST['discount_type']==1){
                //计算红券的使用金额
            }else if($_POST['discount_type']==2){

            }else if($_POST['discount_type']==3){

            }

            $order_sn  = $info['order_sn'];
            $pay_money = 0.01;
        }else if($_POST['type'] ==4){
            $info = M('order')->where(array('id'=>$_POST['order_id']))->find();
            if (!$info) {
                apiResponse('0', '订单信息查询失败');
            }
            $order_sn  = $info['order_sn'];
            $pay_money = 0.01;
        }else if($_POST['type'] == 5){
            $info = M('pre_order')->where(array('id'=>$_POST['order_id']))->find();
            if (!$info) {
                apiResponse('0', '订单信息查询失败');

            }
            $order_sn  = $info['order_sn'];
            $pay_money = 0.01;
        }else if($_POST['type'] == 6){
            $info = M('group_buy_order')->where(array('id'=>$_POST['order_id']))->find();
            if (!$info) {
                apiResponse('0', '订单信息查询失败');
            }
            $order_sn  = $info['order_sn'];
            $pay_money = 0.01;
        }

        //生成支付字符串
        $notify_url   = C('API_URL').'/index.php/Api/Pay/alipayNotify/type/'.$_POST['type'];
        $out_trade_no = $order_sn;
        $total_amount = $pay_money;
        $signType     = 'RSA';
        $payObject = new \Alipay($notify_url,$out_trade_no,$total_amount,$signType);
        $pay_string = $payObject->appPay();
        apiResponse('1','请求成功',array('pay_string'=>$pay_string));
    }
    /**
     * 支付宝回调
     */
    public function alipayNotify(){
        Vendor('Alipay.Notify');
        $notify = new \Notify();
        if ($notify->rsaCheck()) {
            $out_trade_no = $_POST['out_trade_no'];
            $trade_status = $_POST['trade_status'];
            if ($trade_status == 'TRADE_SUCCESS') {
                if ($_GET['type'] == 1) {
                    unset($where);
                    $where['order_sn'] = $out_trade_no;
                    $where['status'] = array('eq', 0);
                    $order_info = M('UserMoney')->where($where)->find();
                    if ($order_info) {
                        //修改订单状态
                        M('UserMoney')->where(array('id' => $order_info['id']))->data(array('status' => 1, 'pay_type' => 2))->save();
                        echo "success";
                    }
                } else if ($_GET['type'] == 2) {
                    unset($where);
                    $where['order_sn'] = $out_trade_no;
                    $where['status'] = array('eq', 0);
                    $order_info = M('CarOrder')->where($where)->find();
                    if ($order_info) {
                        //修改订单状态
                        M('CarOrder')->where(array('id' => $order_info['id']))->data(array('status' => 1, 'pay_type' => 1))->save();
                        echo "success";
                    }
                } else if ($_GET['type'] == 3) {
                    unset($where);
                    $where['order_sn'] = $out_trade_no;
                    $where['status'] = array('eq', 0);
                    $order_info = M('HouseOrder')->where($where)->find();
                    if ($order_info) {
                        //修改订单状态
                        M('HouseOrder')->where(array('id' => $order_info['id']))->data(array('status' => 1, 'pay_type' => 1))->save();
                        echo "success";
                    }
                }else if($_GET['type'] == 4){
                    unset($where);
                    $where['order_sn'] = $out_trade_no;
                    $where['order_status'] = array('eq', 0);
                    $order_info = M('order')->where($where)->find();
                    if ($order_info) {
                        //修改订单状态
                        M('order')->where(array('id' => $order_info['id']))->data(array('order_status' => 1,'pay_status'=>1, 'pay_type' => 1))->save();
                        echo "success";
                    }
                }else if($_GET['type'] == 5){
                    unset($where);
                    $where['order_sn'] = $out_trade_no;
                    $order_info = M('pre_order')->where($where)->find();
                    if ($order_info['order_status'] ==7) {
                        //修改订单状态
                        M('pre_order')->where(array('id' => $order_info['id']))->data(array('order_status' => 0,'pay_status'=>1, 'pay_type' => 1))->save();
                        echo "success";
                    }else if ($order_info['order_status'] ==1) {
                        //修改订单状态
                        M('pre_order')->where(array('id' => $order_info['id']))->data(array('order_status' => 2,'pay_status'=>1, 'pay_type' => 1))->save();
                        echo "success";
                    }
                }else if($_GET['type'] == 6){
                    //拼单购
                    unset($where);
                    $where['order_sn'] = $out_trade_no;
                    $order_info = M('group_buy_order')->where($where)->find();
                    //直接下单
                    if($order_info['order_type'] == 1 && $order_info['order_status'] == 0){
                        $save['update_time'] = time();
                        $save['order_status'] = 2;
                        M('group_buy_order')->where(array('id'=>$order_info['id']))->save($save);
                        echo "success";
                    }
                    //团购
                    if($order_info['order_type'] == 2 && $order_info['order_status'] == 0){
                        $save['update_time'] = time();
                        $save['order_status'] = 1;
                        M('group_buy_order')->where(array('id'=>$order_info['id']))->save($save);
                        echo "success";
                    }
                    if($order_info['order_type'] == 3 && $order_info['order_status'] == 0){
                        $save['update_time'] = time();
                        $save['order_status'] = 1;
                        M('group_buy_order')->where(array('id'=>$order_info['id']))->save($save);
                        echo "success";
                    }
                }
            }
        }
    }

    /**
     * 查询支付结果
     * 传递参数的方式：post
     * 需要传递的参数：
     * 订单id：order_id
     *  类型：type 1.充值,2汽车购订单 3房产购 4 订单支付 5 预购 6拼单购
     */
    public function findPayResult(){
        if(empty($_POST['order_id'])){
            apiResponse('error','参数不完整');
        }
        if($_POST['type']==1) {

        }else if($_POST['type']==2){
            $info = M('CarOrder')->where(array('id' => $_POST['order_id']))->find();
            if (!$info) {
                $result_data['status'] = '0';
            } else {
                if ($info['status'] == 0) {
                    $result_data['status'] = '0';
                }else{
                    $result_data['status'] = '1';
                }
            }
        }else if($_POST['type']==3){
            $info = M('HouseOrder')->where(array('id' => $_POST['order_id']))->find();
            if (!$info) {
                $result_data['status'] = '0';
            } else {
                if ($info['status'] == 0) {
                    $result_data['status'] = '0';
                }else{
                    $result_data['status'] = '1';
                }
            }
        }else if($_POST['type'] == 4){
            //普通下单
            $info = M('order')->where(array('id' => $_POST['order_id']))->find();
            if (!$info) {
                $result_data['status'] = '0';
            } else {
                if ($info['order_status'] == 0) {
                    $result_data['status'] = '0';
                }else{
                    $result_data['status'] = '1';
                }
            }
        }else if($_POST['type'] == 5){
//            预购下单
            $info = M('pre_order')->where(array('id' => $_POST['order_id']))->find();
            if (!$info) {
                $result_data['status'] = '0';
            } else {
                if ($info['order_status'] == 0) {
                    $result_data['status'] = '0';
                }else if($info['order_status'] == 2){
                    $result_data['status'] = '0';
                }else{
                    $result_data['status'] = '1';
                }
            }
        }else if ($_POST['type'] == 6){
            //拼单购下单
            $info = M('group_buy_order')->where(array('id' => $_POST['order_id']))->find();
            if (!$info) {
                $result_data['status'] = '0';
            } else {
                if($info['order_type'] == 1 && $info['order_status'] == 2){
                    $result_data['status'] = '1';
                }else if($info['order_type'] == 1 && $info['order_status'] == 0){
                    $result_data['status'] = '0';
                }
                if($info['order_type'] == 2 && $info['order_status'] == 1){
                    $result_data['status'] = '1';
                }else if($info['order_type'] == 1 && $info['order_status'] == 0){
                    $result_data['status'] = '0';
                }
                if($info['order_type'] == 3 && $info['order_status'] == 1){
                    $result_data['status'] = '1';
                }else if($info['order_type'] == 1 && $info['order_status'] == 0){
                    $result_data['status'] = '0';
                }
            }
        }
        apiResponse('1','请求成功',$result_data);
    }


}