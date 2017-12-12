<?php
namespace Api\Logic;

/**
 * Class OneBuyLogic
 * @package Api\Logic
 * 逻辑层  一元购 模块
 *
 */
class OneBuyLogic extends BaseLogic{

    /**
     * 获取一元购首页
     * @param array $request
     */
    public function oneBuyIndex($request = array()){
        //获取广告
        $list['ads'] = D('Ads','Logic')->adsList(array('num'=>3,'position'=>'29'));
        $list['ads'] = $list['ads']?$list['ads']:array();
        //获取头条
        $list['news'] = M('Headlines')->field("id AS headlines_id,title")->order('sort DESC')->limit(5)->select();
        $mod = M('OneBuy');
        $where['a.status'] = 2;
        $order = 'add_num DESC';
        if(!empty($request['is_hot'])){
            $order = 'a.is_hot DESC';
        }
        if(!empty($request['add_num'])){
            $order = $request['add_num']==1?'a.add_num ASC':'a.add_num DESC';
        }
        if(!empty($request['person_num'])){
            $order = $request['person_num']==1?'a.person_num ASC':'a.person_num DESC';
        }
        if(!empty($request['integral'])){
            $order = $request['integral']==1?'a.integral ASC':'a.integral DESC';
        }
        $count = $mod->alias('a')->where($where)->count();

        $list['oneBuyList'] = $mod->alias('a')
            ->field('a.id AS one_buy_id,a.person_num,a.add_num,a.integral,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id,a.start_time,a.end_time')
            ->join(C('DB_PREFIX').'goods g ON a.goods_id=g.id')
            ->where($where)
            ->order($order)
            ->page($request['p'],10)
            ->select();
        if(!$list['oneBuyList']){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$list);
        }
        foreach($list['oneBuyList'] as $k=>$v){
            //统计出参加这个活动的人数
            $list['oneBuyList'][$k]['diff_num'] = $v['person_num'] - $v['add_num'];
            $list['oneBuyList'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['oneBuyList'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['oneBuyList'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['oneBuyList'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['oneBuyList'][$k]['ticket_buy_discount'] = '0';
            }
        }
        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 获取一元夺宝商品详情页信息
     * @param int $one_Buy_id
     */
    public function oneBuyInfo($request = array()){
        //调用商品详情函数
        //  $info = D('Goods','Logic')->goodsInfo($goods_id);
        //获取到拍卖信息
        $where['id'] = $request['one_buy_id'];
        $mod = M('OneBuy');
        $info['oneBuyInfo'] = $mod->alias('a')
            ->field('a.person_num,a.add_num,a.integral,a.start_time,a.goods_id,a.end_time,a.time_num')
            ->where($where)
            ->find();
        if(!$info['oneBuyInfo']){
            apiResponse('0','获取信息失败');
        }
        $info['oneBuyInfo']['t_status'] = $info['oneBuyInfo']['start_time']>time()?'未开始':($info['oneBuyInfo']['end_time']<time()?'已结束':'进行中');
        $info['oneBuyInfo']['start_time'] = date('Y-m-d H:i:s',$info['oneBuyInfo']['start_time']);
        $info['oneBuyInfo']['end_time'] = date('Y-m-d H:i:s',$info['oneBuyInfo']['end_time']);
        $info['oneBuyInfo']['goods_name'] = getName('Goods','goods_name',$info['oneBuyInfo']['goods_id']);
        //获取到商品的相册
        $gallery = M("GoodsGallery")->where("goods_id={$info['oneBuyInfo']['goods_id']} AND goods_attr_name='0'")->getField('pictures');
        $info['goodsGallery'] = D('File')->getArrayFilePath(explode(',',$gallery));

        //获取纪录
        $info['oneBuyLog'] = M('OneBuyLog')->alias('a')
            ->field('a.id AS log_id,a.bid_time,u.nickname,u.head_pic,a.phone,a.time_num,a.bid_user_id')
            ->join(C('DB_PREFIX').'user u ON u.id=a.bid_user_id')
            ->where("a.one_buy_id = {$request['one_buy_id']}")
            ->order('a.bid_time DESC')
            ->limit(10)
            ->select();
        foreach($info['oneBuyLog'] as $k=>$v){
            $info['oneBuyLog'][$k]['count'] = M('OneBuyLog')->where("bid_user_id='{$v['bid_user_id']}'")->count();
            $info['oneBuyLog'][$k]['bid_time'] = date('Y-m-d H:i:s',$v['bid_time']);
            $info['oneBuyLog'][$k]['head_pic'] = D('File')->getOneFilePath($v['head_pic']);
        }
        //图文详情
        $info['goods_desc'] = getName('Goods','goods_desc',$info['oneBuyInfo']['goods_id']);
        //往期揭晓
        $time =time();
        $info['outTime_log'] = M('OneBuy')->field('id AS one_buy_id,add_num,winer_code,time_num,end_true_time')->where("status=2 AND end_time<{$time}")->order('end_true_time DESC')->select();
        foreach($info['outTime_log'] as $k1=>$v1){
            $userInfo = M('OneBuyLog')->alias('l')
                ->field('l.bid_user_id,u.nickname,u.head_pic')
                ->join(C('DB_PREFIX').'user u ON u.id=l.bid_user_id')
                ->where("l.add_code='{$v1['winer_code']}' ")->find();
            //根据id获取到头像和名称
            $info['outTime_log'][$k1]['head_pic'] = D('File')->getOneFilePath($userInfo['head_pic']);
            $info['outTime_log'][$k1]['nickname'] = $userInfo['nickname'] ? $userInfo['nickname'] : '';
            //本期参与人数
            $info['outTime_log'][$k1]['count'] = $v1['add_num'] ? $v1['add_num'] :'0';
            $info['outTime_log'][$k1]['end_true_time'] = date('Y-m-d H:i:s',$v1['end_true_time']);
        }
        //规则说明
        $rule = M('HelpCenter')->field("title,content")->where("type = 4")->find();
        $info['rules'] = $rule?$rule:'暂无说明';
        apiResponse('1','获取数据成功',$info);
    }

    /**
     * 积分夺宝记录
     *one_buy_id
     */
    function addLog($request,$user_id){

        //设置记录表
        $data['one_buy_id'] = $request['one_buy_id'];
        $data['bid_user_id'] = $user_id;
        $data['bid_time'] = time();
        $data['phone'] = getName('User','phone',$user_id);
        $data['time_num'] = getName('OneBuy','time_num',$request['one_buy_id']);
        $data['append_num'] = 1; //参与次数统计出来
        $data['use_integral'] = $request['integral_num'] ? $request['integral_num'] : 0; //参与使用积分
        $data['discount_num'] = $request['discount_num'] ? $request['discount_num'] : 0; //参与使用红券
        $data['yellow_discount_num'] = $request['yellow_discount_num'] ? $request['yellow_discount_num'] : 0; //参与使用黄券
        $data['blue_discount_num'] = $request['blue_discount_num']?$request['blue_discount_num']:0; //参与使用蓝券

        $id = D('OneBuyLog')->add($data);
        if($id){
            //设置参与号码 活动id加上自己的id
            $addCode['add_code'] = zero($request['one_buy_id'].zero($id,4),9);
            D('OneBuyLog')->where("id={$id}")->save($addCode);
            //活动参与数增加
            M('OneBuy')->where("id = {$request['one_buy_id']}")->setInc('add_num');
            //返回参与号码
//            apiResponse('1','参加成功',$addCode);
            return $addCode;
        }else{
            return false;
        }
    }
    /**
     * 参与夺宝
     * 支付的类型 1积分 2红券 3黄券 4蓝券
     * one_buy_id
     */
    function joinOneBuy($request = array(),$user_id){
        $mod = M('OneBuyLog');
        $mod->startTrans();
        //查询到参与活动所需要的资源
        $need = M('OneBuyConfig')->where("id = 1")->find();
        //查询到活动中限制
        $this->checkLimit($request['one_buy_id']);
        if($request['type'] == '1'){
             $request['integral_num'] = $need['integral_num'];
            //积分参与(在获奖后才生成订单)1获的 2消费获得 3回退 4兑换 5参与活动 6其他减少
             $integral = $need['integral_num'];
             $my_integral = M('User')->where("id={$user_id}")->getField('integral');
             if($my_integral < $integral){
                 apiResponse('0','您的积分不足');
             }
             $content = '参与夺宝成功，消耗积分'.$integral;
             $res = integralChange($integral,5,$content,$user_id);
             if(!$res){
                 $mod->rollback();
                 apiResponse('0','积分参与失败');
             }
         }elseif($request['type'] == '2'){
             $request['discount_num'] = $need['discount_num'];
             $res = D('Vouchers','Logic')->useVoucher($need['discount_num'],$user_id,1);
             if(!$res){
                 $mod->rollback();
                 apiResponse('0','红券参与失败');
             }
             if($res){
                 $content = '参与夺宝成功，消耗红券'.count($res).'张，共消耗额度'.$need['discount_num'];
                 //发送通知消息
                 sendSystemMsg($content,$user_id);
             }
         }elseif($request['type'] == '3'){
             $request['yellow_discount_num'] = $need['yellow_discount_num'];
             $res = D('Vouchers','Logic')->useVoucher($need['yellow_discount_num'],$user_id,2);
             if(!$res){
                 $mod->rollback();
                 apiResponse('0','黄券参与失败');
             }
             if($res){
                 $content = '参与夺宝成功，消耗黄券'.count($res).'张，共消耗额度'.$need['yellow_discount_num'];
                 //发送通知消息
                 sendSystemMsg($content,$user_id);
             }
         }elseif($request['type'] == '4'){
             $request['blue_discount_num'] = $need['blue_discount_num'];
             $res = D('Vouchers','Logic')->useVoucher($need['blue_discount_num'],$user_id,3);
             if(!$res){
                 $mod->rollback();
                 apiResponse('0','蓝券参与失败');
             }
             if($res){
                 $content = '参与夺宝成功，消耗蓝券'.count($res).'张，共消耗额度'.$need['blue_discount_num'];
                 //发送通知消息
                 sendSystemMsg($content,$user_id);
             }
         }else{
            apiResponse('0','参数错误');
        }
        $res1 = $this->addLog($request,$user_id);
        if(!$res1){
            $mod->rollback();
            apiResponse('0','参与失败');
        }else{
            $mod->commit();
            apiResponse('0','参与成功',$res1);
        }
    }
    /**
     * 判断限制条件
     */
    function checkLimit($one_buy_id){
        $one_info = M('OneBuy')->field('integral_num,ticket_num,add_num,person_num')->where("id={$one_buy_id}")->find();
        if($one_info['add_num'] >= $one_info['person_num']){
            //修改活动状态
            $data['end_true_time'] = time();
            apiResponse('0','该活动参与人数已满');
        }
        $where['one_buy_id'] = $one_buy_id;
        $where['use_integral'] = array('gt',0);
        $integral_total = M('OneBuyLog')->where($where)->count();
        if($integral_total >= $one_info['integral_num']){
            apiResponse('0','该活动积分参与份数已满,请使用代金券参与');
        }
        $where['use_integral'] = 0;
        $ticket_total = M('OneBuyLog')->where($where)->count();
        if($ticket_total >= $one_info['ticket_num']){
            apiResponse('0','该活动代金券参与份数已满,请使用积分参与');
        }
    }
    /**
     * 接下来就是抽奖，根据获奖者使用的支付类型获取到值
     * 完成后生成订单(调用订单接口,生成订单)----待完善
     */
    public  function setOrder($request = array(),$user_id = 0){
        $mod = D('Order');
        $mod->startTrans();//启用回滚
        //使用余额
        $data['use_balance'] = !empty($request['use_balance'])? $request['use_balance'] : 0;
        //使用购物券
        $data['integral_money'] = !empty($request['integral_money'])? $request['integral_money'] : 0;
        //订单类型
        $data['order_type'] = 4;
        //优惠券
        $data['ticket_id'] = 0;
        $data['ticket_name'] = '';
        //留言
        $data['leave_word'] = !empty($request['leave_word'])? $request['leave_word'] : '';
        //商家id
        $data['merchant_id'] = $request['merchant_id'];
        $data['merchant_name'] = getName('Merchant','merchant_name',$request['merchant_id']);
        //配送方式
        $data['shipping_id'] = $request['shipping_id'];
        $data['shipping_name'] = getName('Shipping','shipping_name',$request['shipping_id']);
        $data['shipping_fee'] = !empty($request['shipping_fee'])? $request['ticket_id'] : 0;
        //商品总数量
        $data['goods_num'] = $request['goods_num'];
        //收货地址
        $data['address_id'] = $request['address_id'];
        $address_info = M('Address')->where("id={$request['address_id']} AND user_id={$user_id}")->find();
        $data['receiver'] = $address_info ? $address_info['receiver'] : '';
        $data['province_id'] = $address_info ? $address_info['province_id'] : 0;
        $data['city_id'] = $address_info ? $address_info['city_id'] : 0;
        $data['area_id'] = $address_info ? $address_info['area_id'] : 0;
        $data['street_id'] = $address_info ? $address_info['street_id'] : 0;
        $data['address'] = $address_info ? $address_info['province'].$address_info['city'].$address_info['area'].$address_info['street'].$address_info['address'] : '';
        $data['phone'] = $address_info ? $address_info['phone'] : '';
        //商品总金额
        $data['goods_amount'] = !empty($request['goods_amount'])? $request['goods_amount'] : 0;
        //应付款金额
        $data['order_amount'] = !empty($request['order_amount'])? $request['order_amount'] : 0;
        //订单支付状态
        $data['pay_status'] = 1;
        $data['pay_time'] = '根据记录表出价时间记录';
        //订单状态
        $data['order_status'] = 1;//已支付
        //会员ID
        $data['user_id'] = $user_id;
        //活动处理
        if(!empty($request['order_type'])){
            $data['order_type'] = $request['order_type'];
            $data['active_id'] = $request['active_id'];
            $g_data['active_id'] = $request['active_id'];
        }else{
            $data['order_type'] = 0;
            $data['active_id'] = 0;
        }
        //生成订单处理
        $mod->checkCreate($data);
        $id = $mod->add($data);
        if($id){
            //订单号
            $s_data['order_sn'] = date('Y').date('m').date('d').date('H').zero($id,4);
            $mod->where("id = {$id}")->save($s_data);
            //往订单商品表中添加商品信息

            if(1){
                //根据活动id获取到活动商品信息
                $goods_info = M('Goods')->field('goods_sn,goods_name,merchant_name,merchant_id,market_price,shop_price')
                    ->where("id={$request['goods_id']}")->find();
                if(!$goods_info){
                    $mod->rollback();
                    apiResponse('0','生成订单失败');
                }
                $g_data = $goods_info;
                $g_data['goods_id'] = $request['goods_id'];
                $g_data['product_id'] = $request['product_id'];
                $g_data['goods_num'] = $request['goods_num'];
                $g_data['total'] = $g_data['shop_price']*$g_data['goods_num'];
                //根据货品IDh获取属性
                $g_data['goods_attr'] = getAttrGroupId1($request['goods_id'],$request['product_id']);
                $g_data['order_id'] = $id;
                $res = D('OrderGoods')->add($g_data);
                if(!$res){
                    $mod->rollback();
                    apiResponse('0','生成订单失败');
                }
                if($request['product_id']){
                    //货品库存减少
                    D('Products')->where("id={$request['product_id']}")->setDec('product_number',$request['goods_num']);
                }else{
                    //商品库存减少
                    D('Goods')->where("id={$request['goods_id']}")->setDec('goods_num',$request['goods_num']);
                }
                $mod->commit();
            }
            apiResponse('1','生成订单成功',array('order_id'=>$id,'order_sn'=>$s_data['order_sn']));
        }else{
            $mod->rollback();
            apiResponse('0','生成订单失败');
        }
    }
}