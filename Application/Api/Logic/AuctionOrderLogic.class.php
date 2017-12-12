<?php
namespace Api\Logic;

/**
 * Class AuctionOrderLogic
 * @package Api\Logic
 * 逻辑层  预购 模块
 * User: Guo xin 2389541891@qq.com
 * Date: 2017-12-08 9:12
 */

class AuctionOrderLogic extends BaseLogic{
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
    public function OrderList($request = array(),$user_id = 0){
        $where['user_id'] = $user_id;
//        订单状态筛选
        if($request['order_status'] != 9 ){
            $where['order_status'] = $request['order_status'];
        }else{
            $where['order_status'] = array('lt',9);
        }
        $order_list = M('auction_order')
            ->where($where)
            ->field("id as order_id,order_status,merchant_name,order_price,merchant_name")
            ->page($request['p'],',10')
            ->order('update_time desc')
            ->select();
//        echo "<pre>";
//        print_r($order_list);
//        die;
        if(!$order_list){
            apiResponse('0',"暂无订单");
        }
        $start = 0;
        $end = 0;
        foreach($order_list as $k =>$v){

            $order_list[$k]['order_goods'] = M('pre_order_goods')
                ->where(array('pre_order_id'=>$v['order_id']))
                ->field("pre_order_id,goods_name,merchant_name,shop_price,goods_num,goods_attr,goods_img,start_price,final_price")
                ->select();
//            图片
            foreach($order_list[$k]['order_goods'] as $or_k=>$or_v){
                $img_where['id'] = $or_v['goods_img']  ;
                if(empty($img_where['id'])){
                    $order_list[$k]['order_goods'][$or_k]['pic'] = "";
                }else{
                    $order_list[$k]['order_goods'][$or_k]['pic'] = M('file')->where($img_where)->getField('abs_url');
                }
//                定金
                $start += $order_list[$k]['order_goods'][$or_k]['start_price'];
                $end += $order_list[$k]['order_goods'][$or_k]['final_price'];
                $attr = str_replace('|',';',$or_v['goods_attr']);
                $attr_val = str_replace('=',':',$attr);
                $order_list[$k]['order_goods'][$or_k]['goods_attr'] = $attr_val;
            }
            $order_list[$k]['order_goods']?$order_list[$k]['order_goods']:array();
//            定金
            $order_list[$k]['start']=$start;
//            尾款
            $order_list[$k]['end']=$end;
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
     *     购物车cart_ids(可选，可能是立即购买)
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
    public  function SetOrder($request = array(),$user_id = 0){
        $mod = M('auction_order');
        $mod->startTrans();//开启事务
//        获取商品
        $goods_where['id'] = $request['goods_id'];
        $goods = M('goods')->where($goods_where)
//            ->join("db_merchant  as m on g.merchant_id = m.id")
            ->field("merchant_name ,goods_name,id,shop_price,goods_num,goods_img,id")
            ->select();
        $time = M('auction')->where(array('goods_id'=>$request['goods_id']))
            //            ->field("start_time,end_time,pre_price,success_min_num,deposit,product_id")
            ->find();

        //        判断库存
        foreach($goods as $k){
            if($k['goods_num']<$request['goods_num']){
                apiResponse('0','库存不足');
            }
        $data['order_sn'] = time().rand(10000,99999);
            //收货地址
        $data['address_id'] = $request['address_id'];
        $data['goods_id'] = $goods[0]['id'];
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
            $data['create_time'] = time();
//            商家名称
            $data['merchant_name'] = $k['merchant_name'];
//            订单总价
//            $data['order_price'] = $time['pre_price']*$request['goods_num'];
        }
        //配送方式
        $data['shipping_id'] = $request['shipping_id']?$request['shipping_id']:1;
        //生成订单处理
//        $mod->checkCreate($data);
        $id =$mod->data($data)->add();

        if($id){
            $mod->commit();
            //往订单商品表中添加商品信息
//            if($request['cart_ids'] == 0){
                //说明是立即购买
                $goods_info = M('goods')->field('goods_sn,goods_name,merchant_name,merchant_id,market_price,shop_price,blue_discount,yellow_discount,discount,goods_img,goods_num')
                    ->where("id={$request['goods_id']}")->find();
                    $attr_goods['product_number'] = $goods_info['goods_num'];
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
                $g_data['goods_id'] = $request['goods_id'];
                $g_data['goods_num'] =1;
                if($time['product_id']) {
//                商品属性
                        $attr_goods = M('products')->where(array('id'=>$time['product_id']))->field('goods_attr,product_number')->find();
                        $attr_goods['goods_attr'] = explode('|',$attr_goods['goods_attr']);
//            库存
                        $attr_goods['product_number'] = $attr_goods['product_number'];
//            商品属性整理
                        $attr_where['g.id'] = array('in',$attr_goods['goods_attr']);
                        $attr_val = M('goods_attr')->alias('g')
                            ->where($attr_where)
                            ->join('db_attribute as a on a.id = g. attr_id')
                            ->field('g.attr_value,g.goods_id,a.attr_name,a.attr_type,g.attr_price')
                            ->select();
                        $g_data['goods_attr'] = '';
                        foreach($attr_val as $key){
                            $g_data['goods_attr'] .= $key['attr_name'].'='.$key['attr_value'].'|';
                        }
                        $g_data['goods_attr']=rtrim($g_data['goods_attr'],'|');
                }
                $g_data['order_id'] = $id;
                $g_data['create_time'] = time();
                $res = M('auction_order_goods')->data($g_data)->add();
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
                $order_pic['order_goods_id'] = $res;
                $order_pic['order_status'] = 0;
            //        判断 0->一口价；1->竞拍
            if($request['buy_type'] == 0){
//一口价
                $order_pic['pay_money'] = $time['one_price'] ;
                $result_data['order_price'] = $time['one_price'];
            }else{
//竞拍
                $order_pic['bid'] = $request['bid'];
                $result_data['order_price'] = $request['bid'];
            }
                $order_pic['buy_type'] = $request['buy_type'];
                $pic = M('auction_order')->where(array('id'=>$id))->save($order_pic);

                if($pic){
                    $result_data['order_id'] = $id;
                    $result_data['merchant_name'] = $g_data['merchant_name'];
//                    $result_data['discount'] = $order_pic['discount']>0.00?'1':'0';
//                    $result_data['yellow_discount'] = $order_pic['yellow_discount']>0.00?'1':'0';
//                    $result_data['blue_discount'] = $order_pic['blue_discount']>0.00?'1':'0';
                    $result_data['red_desc'] = "本产品最多可以使用".$order_pic['discount']."%红券抵扣现金";
                    $result_data['yellow_desc'] = "本产品最多可以使用".$order_pic['yellow_discount']."%黄券抵扣现金";
                    $result_data['blue_desc'] = "本产品最多可以使用".$order_pic['blue_discount']."%蓝券抵扣现金";
                    //                    余额
                    $balan = M('user')->where(array('id'=>$user_id))->field('balance')->select();
                    $result_data['balance'] = $balan[0]['balance'];

                    apiResponse('1','生成订单成功',$result_data);
                }else{
                    apiResponse('0','生成订单失败');
                }
//            }
        }else{
            $mod->rollback();
            apiResponse('0','生成订单失败');
        }
    }


    /**
     * 结算页
     * User: Ashin
     * 参数 cart_id 购物车id p分页 merchant_id商家id
     * Date: 2017-12-5
     */
    public function ShoppingCart($request = array(),$user_id = 0){
        $cart_list = M('address')->where(array('user_id'=>$user_id,'is_default'=>1))->field('id as address_id,receiver,phone,address,province,city,area')->find();
            $cart_list['item'][0]['goods_id'] = $request['goods_id'];
            $cart_list['item'][0]['num'] = $request['num'];
            $cart_list['item'][0]['shop_price'] = getName('Goods','shop_price',$request['goods_id']);
            $cart_list['item'][0]['order_type'] = $request['order_type'];
            $cart_list['item'][0]['product_id'] = $request['product_id'];
            $cart_list['item'][0]['goods_name'] = getName('Goods','goods_name',$request['goods_id']);
            $cart_list['item'][0]['goods_img'] = getName('Goods','goods_img',$request['goods_id']);

//        查询订单最高价
        $bid_list = M('auction_order')->where(array('goods_id'=>$request['goods_id']))->order('bid desc')->select();
        $start_bid = M('auction')->where(array('goods_id'=>$request['goods_id']))->field('start_price,start_time,end_time,add_price')->find();
        if(empty($bid_list)){
            if(($start_bid['start_price']+$start_bid['add_price']) > $request['bid']){
                apiResponse('0','竞拍不得低于起拍价');
            }
        }else{
            if(($bid_list[0]['bid']+$start_bid['add_price']) > $request['bid']){
                apiResponse('0','竞拍不得低于竞拍价');
            }
        }
        if($start_bid['start_time'] >time() || $start_bid['end_time']<time()){
            apiResponse('0','未到竞拍时间');
        }
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
     * 结算页
     */
    function resPage(){
        //获取收货地址
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
    function preBalanceBack($request,$user_id){
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
        if($request['type'] == '1'){
            //团购
            $data['order_status'] = 1;//待其他人成团
        }elseif($request['type'] == '2'){
            //预购
            $data['order_status'] = 2;//代付尾款
        }elseif($request['type'] == '3' || $request['type'] == '4' || $request['type'] == '5'){
            //3：竞拍 4：积分夺宝 5：无界商店
            $data['order_status'] = 1;//待发货
        }elseif($request['type'] == '6' || $request['type'] == '7'){
            //汽车，房产
            $data['order_status'] = 1;//手续办理中
        }else {

            //普通订单(限量购)
            $data['order_status'] = 1;//待发货
        }
        //执行修改
        $data['update_time'] = time();
        $res = M('pre_Order')->where("id={$request['order_id']}")->save($data);
        if($res){
            $order_info = M('pre_Order')->where("id={$request['order_id']}")->find();
            if($request['type'] == '1'){
                //团购
                $up_data['group_buy_id'] = $order_info['active_id'];
                $up_data['order_id'] = $request['order_id'];
                $up_data['type'] = $order_info['log_id'] ? '2' : '1';
                D('GroupBuy','Logic')->doPayGroup($up_data,$user_id);//生成团购记录
                //发送消息
                $msg = '【拼团】您的一笔拼团购订单支付成功,祝您购物愉快';
                $this->sendOrderMsg($msg,$request['order_id'],$user_id);
            }elseif($request['type'] == '2'){
                //预购
                $up_data['pre_buy_id'] = $order_info['active_id'];
                $up_data['order_id'] = $request['order_id'];
                //发送消息
                $msg = '【预购】您的一笔预购订单支付成功,请等待尾款支付,祝您购物愉快';
                $this->sendOrderMsg($msg,$request['order_id'],$user_id);
            }elseif($request['type'] == '3'){
                //3：竞拍
                $up_data['auct_id'] = $order_info['active_id'];
                $up_data['bid_price'] = $order_info['order_amount'];//应付款金额（竞拍价格）
                $up_data['order_id'] = $request['order_id'];
                D('Auction','Logic')->addLog($up_data,$user_id);
                //发送消息
                $msg = '【竞拍汇】您的一笔拍卖订单支付成功,祝您购物愉快';
                $this->sendOrderMsg($msg,$_POST['order_id'],$user_id);
            }else{
                //普通订单(限量购)
                $up_data['limit_buy_id'] = $order_info['active_id'];
                $up_data['goods_num'] = $order_info['goods_num'];//
                $up_data['order_id'] = $request['order_id'];
                D('LimitBuy','Logic')->addLog($up_data,$user_id);
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
        $order_info = M('pre_order')->where("id={$request['order_id']} AND user_id={$user_id}")->find();
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
        $type_arr = array('0'=>'普通','1'=>'拼团购','2'=>'预购','3'=>'拍卖','6'=>'汽车','7'=>'房产');
        //发送订单消息
//        $msg = '您的一笔'.$type_arr[$order_info['order_type']].'订单支付成功';
        $msg = '您的一笔预购订单支付成功';
        $r = $this->sendOrderMsg($msg,$request['order_id'],$user_id);
        if(!$r){
            $mod->rollback();
            apiResponse('0','交易失败,发送消息失败');
        }
        //回调修改订单状态
        $r2 = $this->balanceBack(array('order_id'=>$request['order_id'],'type'=>$order_info['order_type']),$user_id);

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

    /**
     * 评论页
     * User: Vernon
     * Date: 2017-12-06 13:40
     */
    public function Commentindex($request = array(),$user_id){
        if(empty($request['order_id'])){
            apiResponse('0','订单id不能为空');
        }
        $order_info = M('pre_order')->where(array('id'=>$request['order_id']))->find();
        $other_comment = M('other_comment')->where(array('order_id'=>$request['order_id'],'order_type'=>4))->field('merchant_star,delivery_star')->find();
        if(empty($order_info)){
            apiResponse('0','订单数据异常!');
        }
        if($other_comment){
            $other_comment['merchant_star'] = $other_comment['merchant_star']?$other_comment['merchant_star']:'';
            $other_comment['delivery_star'] = $other_comment['delivery_star']?$other_comment['delivery_star']:'';
            $other_comment['order_status'] = 1;
        }else{
            $other_comment['merchant_star'] = $other_comment['merchant_star']?$other_comment['merchant_star']:'';
            $other_comment['delivery_star'] = $other_comment['delivery_star']?$other_comment['delivery_star']:'';
            $other_comment['order_status'] = 0;
        }


        $goods_list = M('pre_order_goods')
            ->where(array('pre_order_id'=>$request['order_id']))
            ->field('id as order_goods_id,goods_id,goods_name')
            ->select();

        foreach($goods_list as $k => $v){
            if(!empty($v['goods_id'])){
                $goods_list[$k]['goods_img'] = C('API_URL'). M('File')->where(array('id'=>getName('Goods','goods_img',$v['goods_id'])))->getField('path');
                $goods_list[$k]['merchant_id'] = getName('Goods','merchant_id',$v['goods_id']);
            }
            if(!empty($v['order_goods_id'])){
                $comment = M('comment')->where(array('order_id'=>$request['order_id'],'order_goods_id'=>$v['order_goods_id']))->find();
                if($comment){
                    $goods_list[$k]['status'] = '1';
                    $goods_list[$k]['all_star'] = $comment['all_star']?$comment['all_star']:0;
                    $goods_list[$k]['content'] = $comment['content']?$comment['content']:'';
                    if(!empty($comment['pictures'])){
                        $comment['pictures'] = explode(',',$comment['pictures']);
                        foreach($comment['pictures'] as $a => $b){
                            $goods_list[$k]['pictures'][]['path'] = C('API_URL'). M('File')->where(array('id'=>$b))->getField('path');
                        }
                    }else{
                        $goods_list[$k]['pictures'] = [];
                    }
                }else{
                    $goods_list[$k]['status'] = '0';
                    $goods_list[$k]['all_star'] = '';
                    $goods_list[$k]['content'] = '';
                    $goods_list[$k]['pictures'] = [];
                }
            }

        }
        $other_comment['goods_list'] = $goods_list;
        apiResponse('1','请求成功',$other_comment);
    }

    /**
     * 评论商品
     * goods_id 商品id order_goods_id 订单商品表id product_id 商品货品id goods_name 商品名称 content 评论内容 pictures 图片多个用','隔开 all_star 综合评论星级 1-5 merchant_id 商家id
     * User: Vernon
     * Date: 2017-12-06 13:40
     */
    public function CommentGoods($request=array(),$user_id){
        if(empty($request['order_type'])){
//            （1=> 汽车,2=>房产,3=>'直接购买',4=>'预购购买',5=>'团购',6=>'拍卖'）
            apiResponse('0','订单类型不能为空');
        }
        if(empty($request['order_goods_id'])){
            apiResponse('0','订单商品表id不能为空');
        }
        if(empty($request['order_id'])){
            apiResponse('0','订单id不能为空');
        }
        if(empty($request['goods_id'])){
            apiResponse('0','商品id不能为空');
        }
        if(empty($request['product_id'])){
            apiResponse('0','商品货品id不能为空');
        }
        if(empty($request['goods_name'])){
            apiResponse('0','商品名称不能为空');
        }
        if(empty($request['content'])){
            $request['content'] = '该用户未做出任何评论';
        }else{
            $add['content'] = $request['content'];
        }
        if(empty($request['all_star'])){
            $request['all_star'] = 0;
        }else{
            $add['all_star'] = $request['all_star'];
        }
        if(empty($request['merchant_id'])){
            apiResponse('0','商家id不能为空');
        }
        $add['goods_id'] = $request['goods_id'];
        $add['goods_name'] = $request['goods_name'];
        $add['merchant_id'] = $request['merchant_id'];
        $add['order_type'] = 4;
        $add['user_id'] = $user_id;
        $add['order_id'] = $request['order_id'];
        $add['nickname'] = getName('User','nickname',$user_id);
        $add['content'] = $request['content'];
        $add['create_time'] = time();
        $add['product_id'] = $request['product_id'];
        $add['order_goods_id'] = $request['order_goods_id'];
        $res = api('UploadPic/upload', array(array('save_path' => 'Comment')));
        foreach ($res as $value) {
            $pictures[] = $value['id'];
        }
        $add['pictures'] = implode(',',$pictures);
        $data = M('merchant')->add($add);
        if($data){
            $status['status'] = '1';
            apiResponse('1','评论成功',$status);
        }else{
            $status['status'] = '0';
            apiResponse('1','评论失败',$status);
        }
    }

    /**
     * 评论订单
     * 参数 order_id  merchant_star 商家评分星级 delivery_star 物流评分星级
     * User: Vernon
     * Date: 2017-12-06 13:40
     */
    public function CommentOrder($request = array(),$user_id){
        if(empty($request['order_id'])){
            apiResponse('0','订单id不能为空');
        }
        if(empty($request['order_type'])){
//            （1=> 汽车,2=>房产,3=>'直接购买',4=>'预购购买',5=>'团购',6=>'拍卖'）
            apiResponse('0','订单类型不能为空');
        }
        $order_info = M('pre_order')->where(array('id'=>$request['order_id']))->find();
        if(empty($order_info)){
            apiResponse('0','订单数据出错...');
        }
        if(empty($request['merchant_star'])){
            $request['merchant_star'] = 0;
        }else{
            $add['merchant_star'] = $request['merchant_star'];
        }
        if(empty($request['delivery_star'])){
            $request['delivery_star'] = 0;
        }else{
            $add['delivery_star'] = $request['delivery_star'];
        }
        $add['user_id'] = $user_id;
        $add['nickname'] = getName('User','nickname',$user_id);
        $add['create_time'] = time();
        $add['shipping_id'] = $order_info['shipping_id'];
        $add['order_id'] = $request['order_id'];
        $add['order_type'] = $order_info['order_type'];
        $add['type'] = 4;
        $other_comment = M('other_comment');
        $order = M('pre_Order');
        $other_comment->startTrans();
        $r1 = $other_comment->add($add);
        $save['update_time'] = time();
        $save['order_status'] = 6;
        $r2 = $order->where(array('id'=>$request['order_id']))->save($save);
        if($r1 && $r2){
            $other_comment->commit();
            apiResponse('1','评论成功');
        }else{
            $other_comment->rollback();
            apiResponse('0','评论失败,稍后再试');
        }
    }

    /**
     * 取消订单
     * User: Vernon
     * Date: 2017-12-6
     */
    public function preCancelOrder($request = array(),$user_id=0){
        if(empty($request['order_id'])){
            apiResponse('0','请传订单id');
        }
        $order_info = M('pre_order')->where(array('id'=>$request['order_id']))->find();
        if(!$order_info){
            apiResponse('0','订单信息不存在');
        }
        $save['update_time'] = time();
        $save['order_status'] = 5;
        $data = M('pre_order')->where(array('id'=>$request['order_id']))->save($save);
        if($data){
            apiResponse('1','订单取消成功');
        }else{
            apiResponse('0','订单取消失败,请稍后再试!');
        }
    }

    /**
     * 删除订单
     * User: Vernon
     * Date: 2017-12-6
     */
    public function preDeleteOrder($request=array(),$user_id){
        if(empty($request['order_id'])){
            apiResponse('0','订单ID不能为空');
        }
        $order_info = M('pre_order')->where(array('id'=>$request['order_id']))->find();
        if(!$order_info){
            apiResponse('0','订单信息不存在');
        }
        $save['order_status'] = 9;
        $save['update_time'] = time();
        $data = M('pre_order')->where("id = {$request['order_id']}")->save($save);
        if($data){
            apiResponse('1','删除成功');
        }else{
            apiResponse('0','删除失败');
        }
    }
    /**
     * 确认收货
     * 参数
     * User: Vernon
     * Date: 2017-12-06 13:40
     */
    public function preReceiving($request=array(),$user_id){
        if(empty($request['order_id'])){
            apiResponse('0','订单id不能为空');
        }
        $order_info = M('pre_order')->where(array('id'=>$request['order_id']))->find();
        if(empty($order_info)){
            apiResponse('0','订单数据出错...');
        }
        $save['update_time']=time();
        $save['order_status'] = 4;
        $data = M('pre_order')->where(array('id'=>$request['order_id']))->save($save);
        if($data){
            apiResponse('1','确认收货成功');
        }else{
            apiResponse('0','确认收货失败!');
        }
    }


}