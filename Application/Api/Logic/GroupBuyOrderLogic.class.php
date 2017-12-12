<?php
namespace Api\Logic;

/**
 * Class OrderLogic
 * @package Api\Logic
 * 逻辑层  拼单购 模块
 *
 */
class GroupBuyOrderLogic extends BaseLogic{
    /**
     * 订单列表
     * @param int $user_id
     */
    /**
     * User: Guo xin 2389541891@qq.com
     * Date: 2017-12-05 13:51
     * order_status  订单状态
     * order_type  购买渠道
     */
    public function orderList($request = array(),$user_id = 0){
        $where['user_id'] = $user_id;
//        订单状态筛选
        if($request['order_status'] != 9 ){
            $where['order_status'] = $request['order_status'];
        }else{
            $where['order_status'] = array('lt',9);
        }
        $order_list = M('GroupBuyOrder')
            ->where($where)
            ->field("id as group_buy_order_id,order_status,merchant_name,order_price,merchant_name,order_type,p_id,group_buy_id")
            ->page($request['p'],',10')
            ->order('update_time desc')
            ->select();
        if(!$order_list){
            apiResponse('0',"暂无订单");
        }
        foreach($order_list as $k =>$v){
            $order_list[$k]['order_goods'] = M('group_order_goods')
                ->where(array('group_order_id'=>$v['group_buy_order_id']))
                ->field("group_order_id,goods_name,merchant_name,shop_price,goods_num,goods_attr,goods_img,group_order_id")
                ->select();
//            图片
            foreach($order_list[$k]['order_goods'] as $or_k=>$or_v){
                $img_where['id'] = $or_v['goods_img']  ;

                if(empty($img_where['id'])){
                    $order_list[$k]['order_goods'][$or_k]['pic'] = "";
                }else{

                    $order_list[$k]['order_goods'][$or_k]['pic'] = M('file')->where($img_where)->getField('abs_url');
                }

            }

            $order_list[$k]['order_goods']?$order_list[$k]['order_goods']:array();
            $order_list[$k]['order_goods'] = array_values($order_list[$k]['order_goods']);
        }
        $order_list = array_values($order_list);
        apiResponse(1,'获取成功',$order_list);
    }

    /**
     * 生成订单(跳到支付页面的同时生成订单,支付成功，或者失败都有订单生成)
     * @param array $request
     * @param int $user_id
     *     收货地址id
     *     商品总数量
     *     购物车 cart_ids(可选，可能是立即购买)
     *     配送方式id :shipping_id
     *     使用余额 :use_balance
     *     使用购物券 :integral_money
     *     订单类型 ：order_type
     *     订单类型  0:普通 1：团购  2：预购 3：竞拍 4：一元夺宝 5：无界商店 6：汽车购 7：房产购 8：线下商城 9限量购
     *     活动id ：活动商品可选 active_id
     *     优惠券id
     *     留言 leave_word(可选)
     *     店铺id : merchant_id
     *     商品总金额 :goods_amount
     *     应付款金额 : order_amount
     *     订单来源：buy_where （默认0:直接购买 ，1：购物车）
     */
    /**
     * User: Guo xin 2389541891@qq.com
     * Date: 2017-12-05 13:15
     */
    public  function setOrder($request = array(),$user_id = 0){
        $mod = D('GroupBuyOrder');
        $mod->startTrans();//开启事务

        $goods_where['id'] = $request['goods_id'];
        $goods = M('goods')->where($goods_where)
            ->field("merchant_name ,goods_name,id,shop_price,goods_num,goods_img")
            ->select();
        //判断库存
        foreach($goods as $k){
            if($k['goods_num']<$request['goods_num']){
                apiResponse('0','库存不足');
            }
            //订单类型
            $data['order_type'] = !empty($request['order_type'])?$request['order_type'] : 0;
//            订单总价
            $data['order_price'] = $goods[0]['shop_price']*$request['goods_num'];
            //收货地址
            $data['address_id'] = $request['address_id'];
            $address_info = M('Address')->where("id={$request['address_id']} AND user_id={$user_id}")->find();
            $data['receiver'] = $address_info ? $address_info['receiver'] : '';
            $data['address_id'] = $address_info ? $address_info['id'] : '';
            $data['address'] = $address_info ? $address_info['province'].$address_info['city'].$address_info['area'].$address_info['street'].$address_info['address'] : '';
            $data['phone'] = $address_info ? $address_info['phone'] : '';
            //商品总数量
            $data['goods_num'] = $request['goods_num'];
//                用户id
            $data['user_id'] = $user_id;
            //留言
            $data['leave_word'] = !empty($request['leave_word'])? $request['leave_word'] : '';
//            商家名称
            $data['merchant_name'] = $k['merchant_name'];
        }
        //配送方式
        $data['shipping_id'] = $request['shipping_id']?$request['shipping_id']:1;
        $data['order_type'] = $request['order_type'];

        //订单号
        $data['order_sn'] = time().rand(10000,99999);
        $data['create_time']= time();
        $data['update_time'] = time();
        $data['group_buy_id'] = $request['group_buy_id'];
        //参团
        $group_buy_info = M('group_buy')->where(array('id'=>$request['group_buy_id']))->find();
        if($request['order_type'] == 3){
            if(empty($request['group_buy_order_id'])){
                apiResponse('0','团购订单id不能为空');
            }

            //已参团的人数
            $group_count = M('group_buy_order')->where(array('p_id'=>$request['group_buy_order_id']))->count() + 1;
            if($group_count > $group_buy_info['group_num']){
                apiResponse('0','该团人数已满');
            }
            $data['p_id'] = $request['group_buy_order_id'];
        }
        if($request['order_type'] == 2){
            $data['p_id'] = $request['group_buy_order_id'];
            if($group_buy_info['total'] > $group_buy_info['max_num']){
                apiResponse('0','该商品已到成团上限!');
            }
            //增加已团数量
            M('group_buy')->where(array('id'=>$request['group_buy_id']))->setInc('total',1);
        }
        $id = $mod->add($data);
        if($id){
            //往订单商品表中添加商品信息
            if($request['cart_ids'] == 0){
                //说明是立即购买
                $goods_info = M('Goods')->field('goods_sn,goods_name,merchant_name,merchant_id,market_price,shop_price,blue_discount,yellow_discount,discount,goods_img,goods_num')
                    ->where("id={$request['goods_id']}")->find();
                if($request['product_id']){
                    //商品属性
                    $attr_goods = M('products')->where(array('id'=>$request['product_id']))->field('goods_attr,product_number')->select();
                    $attr_goods['goods_attr'] = explode('|',$attr_goods[0]['goods_attr']);
                    //库存
                    $attr_goods['product_number'] = $attr_goods[0]['product_number'];
                    //商品属性整理
                    $attr_where['g.id'] = array('in',$attr_goods['goods_attr']);
                    $attr_val = M('goods_attr')->alias('g')
                        ->where($attr_where)
                        ->join('db_attribute as a on a.id = g. attr_id')
                        ->field('g.attr_value,g.goods_id,a.attr_name,a.attr_type,g.attr_price')
                        ->select();
                    $g_data['goods_attr'] = '';
                    $else_price = 0;
                    foreach($attr_val as $key){
                        $else_price += $key['attr_price'];
                        $g_data['goods_attr'] .= $key['attr_name'].'='.$key['attr_value'].'|';
                    }
                    $g_data['goods_attr']=rtrim($g_data['goods_attr'],'|');
                }else{
                    $attr_goods['product_number'] = $goods_info['goods_num'];
                }
                if(!$goods_info){
                    $mod->rollback();
                    apiResponse('0','生成订单失败');
                }
                $g_data['goods_sn'] = $goods_info['goods_sn'];
                $g_data['goods_img'] = $goods_info['goods_img'];
                $g_data['product_id'] = $request['product_id']?$request['product_id']:0;
                $g_data['goods_name'] = $goods_info['goods_name'];
                $g_data['merchant_name'] = $goods_info['merchant_name'];
                $g_data['merchant_id'] = $goods_info['merchant_id'];
                $g_data['market_price'] = $goods_info['market_price'];
                if($else_price){
                    $g_data['shop_price'] = $goods_info['shop_price'] + $else_price;
                }else{
                    $g_data['shop_price'] = $goods_info['shop_price'];
                }
                $g_data['goods_id'] = $request['goods_id'];
                $g_data['goods_num'] = $request['goods_num']?$request['goods_num']:'';
                $g_data['total'] = $g_data['shop_price']*$g_data['goods_num'];
                //根据货品IDh获取属性
                //$g_data['goods_attr'] = getAttrGroupId1($request['goods_id'],$request['product_id']);
                //库存
//                if($request['goods_num'] > $attr_goods['product_number']){
//                    $mod->rollback();
//                    apiResponse('0','库存不足');
//                }
                $g_data['group_order_id'] = $id;
                $og_creat = D('group_order_goods')->create($g_data);
                if($og_creat){
                    $res = D('group_order_goods')->data($og_creat)->add();
                }
                if(!$res){
                    $mod->rollback();
                    apiResponse('0','生成订单失败');
                }else{
                    $mod->commit();
                }
                //代金券
                $order_pic=array();
                $order_pic['discount'] += sprintf("%.2f", $goods_info['discount']/100*$goods_info['shop_price']*$g_data['goods_num']);
                $order_pic['yellow_discount'] += sprintf("%.2f", $goods_info['yellow_discount']/100*$goods_info['shop_price']*$g_data['goods_num']);
                $order_pic['blue_discount'] += sprintf("%.2f", $goods_info['blue_discount']/100*$goods_info['shop_price']*$g_data['goods_num']);
                $order_pic['sum_discount'] = $order_pic['discount']+$order_pic['yellow_discount']+$order_pic['blue_discount'];
                $pic = M('GroupBuyOrder')->where(array('id'=>$id))->save($order_pic);
                if($pic){
                    $result_data['merchant_name'] = $goods_info['merchant_name'];
                    $result_data['group_buy_order_id'] = $id;
                    $result_data['order_price'] = $g_data['total'];
                    $result_data['discount'] = $order_pic['discount']>0.00?'1':'0';
                    $result_data['yellow_discount'] = $order_pic['yellow_discount']>0.00?'1':'0';
                    $result_data['blue_discount'] = $order_pic['blue_discount']>0.00?'1':'0';
                    $result_data['red_desc'] = "本产品最多可以使用".$order_pic['discount']."%红券抵扣现金";
                    $result_data['yellow_desc'] = "本产品最多可以使用".$order_pic['yellow_discount']."%黄券抵扣现金";
                    $result_data['blue_desc'] = "本产品最多可以使用".$order_pic['blue_discount']."%蓝券抵扣现金";
                    //余额
                    $balan = M('user')->where(array('id'=>$user_id))->field('balance')->select();
                    $result_data['balance'] = $balan[0]['balance'];
                    apiResponse('1','生成订单成功',$result_data);
                }else{
                    apiResponse('0','生成订单失败1');
                }
            }
        }else{
            $mod->rollback();
            apiResponse('0','生成订单失败');
        }
    }

    /**
     * 结算页
     * User: Vernon
     *  参数 goods_id 商品id  num 购买数量 order_type 1直接购买 2拼单  merchant_id商家id product_id 组和属性id
     * Date: 2017-12-7
     */
    public function shoppingCart($request = array(),$user_id = 0){
        $cart_list = M('address')->where(array('user_id'=>$user_id,'is_default'=>1))->field('id as address_id,receiver,phone,address,province,city,area')->find();
            $cart_list['item'][0]['goods_id'] = $request['goods_id'];
            $cart_list['item'][0]['num'] = $request['num'];
            $cart_list['item'][0]['shop_price'] = getName('Goods','shop_price',$request['goods_id']);
            $cart_list['item'][0]['order_type'] = $request['order_type'];
            $cart_list['item'][0]['product_id'] = $request['product_id'];
            $cart_list['item'][0]['goods_name'] = getName('Goods','goods_name',$request['goods_id']);
            $cart_list['item'][0]['goods_img'] = getName('Goods','goods_img',$request['goods_id']);
        //是否有默认地址
        if(!empty($cart_list)){
            $cart_list['is_default'] = '1';
        }else{
            $cart_list['is_default'] = '0';
        }
        //商家姓名
        $cart_list['merchant_name'] = M('merchant')->where(array('id'=>$request['merchant_id']))->getField('merchant_name');
        foreach($cart_list['item'] as $k =>$v){
            $v['sum_shop_price'] = $v['shop_price']*$v['num'];
            $cart_list['sum_shop_price'] += $v['sum_shop_price'];
            //处理商品缩略图
            $path = M('File')->where(array('id'=>$v['goods_img']))->getField('path');
            $cart_list['item'][$k]['goods_img'] = C('API_URL').$path;
            //商品所属活动
            $cart_list['item'][$k]['order_type'] = '1';
            //组和属性价格 商品基础价格+属性组和属性价格
            if(!empty($v['product_id'])){
                $goods_attr = explode('|',M('products')->where(array('id'=>$v['product_id']))->getField('goods_attr'));
                $where_['id']=array('in',implode(',',$goods_attr));
                $cart_list['item'][$k]['shop_price'] = $v['num'] *($v['shop_price'] +M('goods_attr')->where($where_)->sum('attr_price'));
            }
            //代金券
            $cart_list['discount'] += sprintf("%.2f", $v['discount']/100*$v['shop_price']*$v['num']);
            $cart_list['yellow_discount'] += sprintf("%.2f", $v['yellow_discount']/100*$v['shop_price']*$v['num']);
            $cart_list['blue_discount'] += sprintf("%.2f", $v['blue_discount']/100*$v['shop_price']*$v['num']);
        }
        $cart_list['sum_discount'] = $cart_list['discount']+$cart_list['yellow_discount']+$cart_list['blue_discount'];
        apiResponse('1','请求成功',$cart_list);
    }

    /**
     * 删除订单
     * User: Vernon
     * Date: 2017-12-6
     */
    public function deleteOrder($request=array(),$user_id){
        if(empty($request['group_buy_order_id'])){
            apiResponse('0','订单ID不能为空');
        }
        $order_info = M('GroupBuyOrder')->where(array('id'=>$request['group_buy_order_id']))->find();
        if(!$order_info){
            apiResponse('0','订单信息不存在');
        }
        $save['order_status'] = 9;
        $save['update_time'] = time();
        $data = M('GroupBuyOrder')->where("id = {$request['group_buy_order_id']}")->save($save);
        if($data){
            apiResponse('1','删除成功');
        }else{
            apiResponse('0','删除失败');
        }
    }

    /**
     * 取消订单
     * User: Vernon
     * Date: 2017-12-6
     */

    public function cancelOrder($request = array(),$user_id=0){
        if(empty($request['group_buy_order_id'])){
            apiResponse('0','订单ID不能为空');
        }
        $order_info = M('GroupBuyOrder')->where(array('id'=>$request['group_buy_order_id']))->find();
        if(!$order_info){
            apiResponse('0','订单信息不存在');
        }
        $save['update_time'] = time();
        $save['order_status'] = 5;
        $data = M('GroupBuyOrder')->where(array('id'=>$request['group_buy_order_id']))->save($save);
        if($data){
            apiResponse('1','订单取消成功');
        }else{
            apiResponse('0','订单取消失败,请稍后再试!');
        }
    }

    /**
     * 确认收货
     * 参数
     * User: Vernon
     * Date: 2017-12-06 13:40
     */
    public function receiving($request=array(),$user_id){
        if(empty($request['group_buy_order_id'])){
            apiResponse('0','订单id不能为空');
        }
        $order_info = M('group_buy_order')->where(array('id'=>$request['group_buy_order_id']))->find();
        if(empty($order_info)){
            apiResponse('0','订单数据出错...');
        }
        $save['update_time']=time();
        $save['order_status'] = 4;
        $data = M('group_buy_order')->where(array('id'=>$request['group_buy_order_id']))->save($save);
        if($data){
            apiResponse('1','确认收货成功');
        }else{
            apiResponse('0','确认收货失败!');
        }
    }
    /**
     * 订单付款页面(调到这个页面的时候就调用生成订单函数)
     * 订单号 order_id
     */
    function payOrder($request = array(),$user_id = 0){
        //根据订单id获取到支付信息
        $order_info = M('Order')->where("id = {$request['order_id']}")->find();
        //获取到支付金额,
        $pay_money = $order_info['order_amount'];
        apiResponse('1','获取订单信息成功',array('money'=>$pay_money));
    }
    /**
     * 支付操作(获取支付宝参数，获取微信支付参数)
     * 订单号 order_id
     */
    function doPay(){

    }
    /**
     * 支付宝支付回调修改订单状态
     */
    /**
     * 微信支付回调修改订单状态
     */
    /**
     * 余额支付回调修改订单状态
     */
    function balanceBack($request,$user_id){
//        p($request);die;
        $data['type'] = $request['type'];//订单类型
        $data['pay_type'] = '支付类型';//余额支付
        $data['order_id'] = $request['order_id'];
        $res = $this->orderStatus($data,$user_id);
        if($res){
            return true;
        }else{
            return false;
        }

    }
    /**
     * 从付款页面过来回调的时候修改订单状态(支付回调操作),根据不同的订单类型进行操作
     * 订单类型  0:普通(限量购) 1：团购  2：预购 3：竞拍 4：一元夺宝 5：无界商店 6：汽车购 7：房产购 8：线下商城
     */

    function orderStatus($request,$user_id){

        $data['pay_status'] = 1;//支付状态
        $data['pay_time'] = time();//支付时间

        if($request['type'] == '2'){
            //团购
            $data['order_status'] = 1;//待成团
        }else{
            //普通订单(限量购)
            $data['order_status'] = 2;//待发货
        }
        //执行修改
        $data['update_time'] = time();

        $res = M('GroupBuyOrder')->where("id={$request['order_id']}")->save($data);

        if($res){
            $order_info = M('GroupBuyOrder')->where("id={$request['order_id']}")->find();
            if($request['type'] == '2'){
                //团购
                $up_data['group_buy_id'] = $order_info['active_id'];
                $up_data['order_id'] = $request['order_id'];
                $up_data['type'] = $order_info['log_id'] ? '2' : '1';
//                D('GroupBuy','Logic')->doPayGroup($up_data,$user_id);//生成团购记录
                //发送消息
                $msg = '【拼单购】您的一笔拼团购订单支付成功,祝您购物愉快';
                $this->sendOrderMsg($msg,$request['order_id'],$user_id);
                if($res){
                    return true;
                }else{
                    return false;
                }
            }else{
                //直接下单
//                $up_data['limit_buy_id'] = $order_info['active_id'];
//                $up_data['goods_num'] = $order_info['goods_num'];//
//                $up_data['order_id'] = $request['order_id'];
//                D('LimitBuy','Logic')->addLog($up_data,$user_id);
                //发送消息
                $msg = '您的一笔订单支付成功,祝您购物愉快';
                $res = $this->sendOrderMsg($msg,$request['order_id'],$user_id);
                if($res){
                    return true;
                }else{
                    return false;
                }
            }
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
     * 余额支付
     *
     */
    function balancePay($request = array(),$user_id){
        //根据订单id获取到支付的类型,价格
        $order_info = M('GroupBuyOrder')->where("id={$request['group_buy_order_id']}")->find();
        if(!$order_info){
            apiResponse('0','获取订单消息失败');
        }

        $mod = M('User');
        $mod->startTrans();
        //获取当前会员基本信息
        $balance = $mod->where("id={$user_id}")->getField('balance');
        if($balance < $order_info['order_price']){
            apiResponse('0','余额不足');
        }
        $money = $order_info['order_price'];
        $act_type = 3;
        $reason = '消费支出'.$order_info['order_price'].'元';
        $res = balanceChange($money,$act_type,$reason,$user_id);

        if(!$res){
            $mod->rollback();
            apiResponse('0','交易失败');
        }
        //会员表中增加消费金额
        $rs = $mod->where("id={$user_id}")->setInc('fee_score',$order_info['order_price']);
        if(!$rs){
            $mod->rollback();
            apiResponse('0','交易失败,消费金额增加失败');
        }
        //  0:普通 1：团购  2：预购 3：竞拍 4：一元夺宝 5：无界商店 6：汽车购 7：房产购 8：线下商城s
//        $type_arr = array('0'=>'普通','1'=>'拼团购','2'=>'预购','3'=>'拍卖','6'=>'汽车','7'=>'房产');
        //发送订单消息
        $msg = '您的一笔拼团购订单支付成功';
        $r = $this->sendOrderMsg($msg,$request['group_buy_order_id'],$user_id);
        if(!$r){
            $mod->rollback();
            apiResponse('0','交易失败,发送消息失败');
        }
        //回调修改订单状态
        $r2 = $this->balanceBack(array('order_id'=>$request['group_buy_order_id'],'type'=>$order_info['order_type']),$user_id);

        if($r2){
            $mod->commit();
            apiResponse('1','支付成功');
        }else{
            $mod->rollback();
            apiResponse('0','交易失败,修改订单状态失败');
        }
    }

    /**
     * 验证支付密码
     */
    function checkPayPwd($user_id,$pay_pwd){
        $pwd = M('User')->where("id = {$user_id}")->getField('pay_password');
        if(md5($pay_pwd) == $pwd){
            return true;
        }else{
            return false;
        }
    }

}