<?php
namespace Api\Logic;

/**
 * Class OrderLogic
 * @package Api\Logic
 * 逻辑层  余额支付 模块
 *
 */
class BalancePayLogic extends BaseLogic{

    /**
     * 余额支付
     *参数
     *  order_id 订单id
     *  discount_type代金券类型(多个用','分开)
     *  order_type 1普通订单 2拼单购 3预购 4比价购 5限量购
     * User: Vernon
     * Date: 2017-12-9
     */
    function balancePay($request = array(),$user_id){
        //1普通订单 2拼单购 3预购 4比价购 5限量购
        switch($request['order_type']){
            case 1:
               //根据订单id获取到支付的类型,价格
                $order_info = M('order')->where("id={$request['order_id']} AND user_id={$user_id}")->find();
                if(!$order_info){
                    apiResponse('0','获取订单消息失败');
                }
                //消费金额
                $money = $order_info['order_price'];
                break;
            case 2:
                //根据订单id获取到支付的类型,价格
                $order_info = M('group_buy_order')->where("id={$request['order_id']} AND user_id={$user_id}")->find();
                if(!$order_info){
                    apiResponse('0','获取订单消息失败');
                }
                //消费金额
                $money = $order_info['order_price'];
                break;
            case 3:
                //根据订单id获取到支付的类型,价格
                $order_info = M('pre_order')->where("id={$request['order_id']} AND user_id={$user_id}")->find();
                if(!$order_info){
                    apiResponse('0','获取订单消息失败');
                }
                //消费金额
                $money = $order_info['order_price'];
                break;
            case 4:
                //根据订单id获取到支付的类型,价格
                $order_info = M('auction_order')->where("id={$request['order_id']} AND user_id={$user_id}")->find();
                if(!$order_info){
                    apiResponse('0','获取订单消息失败');
                }
                //消费金额
                $money = $order_info['order_price'];
                break;
            case 5:
                //根据订单id获取到支付的类型,价格
                $order_info = M('limit_buy_order')->where("id={$request['order_id']} AND user_id={$user_id}")->find();
                if(!$order_info){
                    apiResponse('0','获取订单消息失败');
                }
                //消费金额
                $money = $order_info['order_price'];
                break;
        }


        $mod = M('User');
        $mod->startTrans();
        //获取当前会员基本信息
        $balance = $mod->where("id={$user_id}")->getField('balance');
        if($balance < $order_info['order_price']){
            apiResponse('0','余额不足');
        }
        //账单明细
        $act_type = 3;
        $reason = '消费支出'.$order_info['order_price'].'元';
        $res = balanceChange($money,$act_type,$reason,$user_id);
        if(!$res){
            $mod->rollback();
            apiResponse('0','交易失败');
        }
        //会员表中增加消费金额
        $rs = $mod->where("id={$user_id}")->setInc('fee_score',$money);
        if(!$rs){
            $mod->rollback();
            apiResponse('0','交易失败,消费金额增加失败');
        }
        //  1普通订单 2拼单购 3预购 4比价购 5限量购
        $type_arr = array('1'=>'普通','1'=>'拼单购','3'=>'无界预购','4'=>'比价购','5'=>'限量购');
        //发送订单消息
        $msg = '您的一笔'.$type_arr[$request['order_type']].'订单支付成功';
        $r = $this->sendOrderMsg($msg,$request['order_id'],$user_id);
        if(!$r){
            $mod->rollback();
            apiResponse('0','交易失败,发送消息失败');
        }
        //回调修改订单状态
        $r2 = $this->orderStatus(array('order_id'=>$request['order_id'],'type'=>$request['order_type']),$user_id);
        if($r2){
            $mod->commit();
            apiResponse('1','支付成功');
        }else{
            $mod->rollback();
            apiResponse('0','交易失败,修改订单状态失败');
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
     * 从付款页面过来回调的时候修改订单状态(支付回调操作),根据不同的订单类型进行操作
     * 订单类型  1普通订单 2拼单购 3预购 4比价购 5限量购
     */

    function orderStatus($request,$user_id)
    {
        $data['pay_type'] = '4';//余额支付
        $data['pay_status'] = 1;//支付状态
        $data['pay_time'] = time();//支付时间
        //1普通订单 2拼单购 3预购 4比价购 5限量购
        $data['update_time'] = time();
        switch ($request['type']) {
            case 1:
                $data['order_status'] = 1;//待发货
                //执行修改
                $data['update_time'] = time();
                $res = M('order')->where("id={$request['order_id']}")->save($data);
                break;
            case 2:
                $order_info = M('group_buy_order')->where(array('id' => $request['order_id']))->find();
                //1直接下单 2开团 3参团
                if ($order_info['order_type'] == 1) {
                    $data['order_status'] = 2;//待发货
                }
                if ($order_info['order_type'] == 2) {
                    $data['order_status'] = 1;//待成团
                }
                if ($order_info['order_type'] == 3) {
                    //成团人数
                    $group_num = getName('GroupBuy','group_num',$order_info['group_buy_id']);
                    //团员数量
                    $user_num = M('group_buy_order')->where(array('p_id'=>$order_info['id']))->count() +1;
                    $num = $group_num - $user_num;
                    if($num == 0){
                        $data['order_status'] = 7;//已成团
                    }else{
                        $data['order_status'] = 1;//待成团
                    }
                }
                $res = M('group_buy_order')->where("id={$request['order_id']}")->save($data);
                break;
            case 3:
                $pre_order = M('pre_order')->where(array('id'=>$request['order_id']))->find();
                if($pre_order['order_status'] == 7){
                    $data['order_status'] = 0;//预购中
                }
                if($pre_order['order_status'] == 1){
                    $data['order_status'] = 2;//预购中
                }
                $res = M('pre_order')->where("id={$request['order_id']}")->save($data);
                break;
            case 4:
                $data['order_status'] = 1;//待发货
                break;
            case 5:
                $data['order_status'] = 1;//待发货
                $res = M('limit_buy_order')->where("id={$request['order_id']}")->save($data);
                break;
        }
//        if($request['type'] == '1'){
//            //团购
//            $data['order_status'] = 1;//待其他人成团
//        }elseif($request['type'] == '2'){
//            //预购
//            $data['order_status'] = 2;//代付尾款
//        }elseif($request['type'] == '3' || $request['type'] == '4' || $request['type'] == '5'){
//            //3：竞拍 4：积分夺宝 5：无界商店
//            $data['order_status'] = 1;//待发货
//        }elseif($request['type'] == '6' || $request['type'] == '7'){
//            //汽车，房产
//            $data['order_status'] = 1;//手续办理中
//        }else{
//
//            //普通订单(限量购)
//            $data['order_status'] = 1;//待发货
//        }
        //执行修改

//        $res = M('Order')->where("id={$request['order_id']}")->save($data);
        if ($res) {
            //1普通订单 2拼单购 3预购 4比价购 5限量购
            switch ($request['type']) {
                case 1:
                    $msg = '您的一笔订单支付成功,祝您购物愉快';
                    break;
                case 2:
                    $msg = '【拼单购】您的一笔拼团购订单支付成功,祝您购物愉快';
                    break;
                case 3:
                    $msg = '【无界预购】您的一笔预购订单支付成功,请等待尾款支付,祝您购物愉快';
                    break;
                case 4:
                    $msg = '【比价购】您的一笔拍卖订单支付成功,祝您购物愉快';
                    break;
                case 5:
                    $msg = '【限量购】您的一笔拼团购订单支付成功,祝您购物愉快';
                    break;

            }
            $r = $this->sendOrderMsg($msg, $request['order_id'], $user_id);
            if ($r) {
                return true;
            } else {
                return false;
            }
        }
    }
}