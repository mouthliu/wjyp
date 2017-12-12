<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 拼单购模块控制器
 * Class OrderController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 */
class GroupBuyOrderController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 订单列表
     * 请求方式: post
     * 请求参数: 订单类型
     *  order_type :　
     * 　0:普通 1：团购 2：预购 3：竞拍 4：一元夺宝 5：无界商店  8：线下商城
     */
    public function OrderList(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('GroupBuyOrder','Logic')->OrderList(I('post.'),$user_id);
    }
    /**
     * 生成订单（结算页过来）
     * 请求参数:
     *     收货地址id
     *     商品总数量 goods_num
     *     购物车id(可选，可能是立即购买,立即购买需要携带商品信息,如果是立即购买需要传入商品信息 goods_id product_id)
     *     配送方式id :shipping_id
     *     配送费:shipping_fee （包邮未0）
     *     使用余额 :use_balance (可选)
     *     使用购物券 :integral_money (可选)
     *     订单类型 ：order_type (可选)
     *     活动id ：在订单类型不为0的时候 活动商品可选 active_id
     *     优惠券id
     *     留言 leave_word(可选)
     *     店铺id : merchant_id
     *     商品总金额 :goods_amount 需要结算页算好传过来
     *     应付款金额 : order_amount 需要结算页算好传过来
     */
    public function setOrder()
    {
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['group_buy_order_id'])) {
            if (empty($_POST['address_id'])) {
                apiResponse('0', '请选择收货地址');
            }
            if (empty($_POST['order_type'])) {
                apiResponse('0', '请选择订单类型');
            }
            if (empty($_POST['cart_ids'])) {
                if (empty($_POST['goods_num'])) {
                    apiResponse('0', '请输入商品数量');
                }
                if (empty($_POST['goods_id'])) {
                    apiResponse('0', '请输入商品id');
                }
            }
            D('GroupBuyOrder', 'Logic')->setOrder(I('post.'), $user_id);
        } else {
            $og                             = M('group_buy_order')->where(array('id' => $_POST['group_buy_order_id']))
                ->field('id,order_price,discount,yellow_discount,blue_discount,merchant_name')
                ->find();
            $result_data['group_buy_order_id']        = $og['id'];
            $result_data['order_price']     = $og['order_price'];
            $result_data['merchant_name']   = $og['merchant_name'];
            $result_data['discount']        = $og['discount'] > 0.00 ? '1' : '0';
            $result_data['yellow_discount'] = $og['yellow_discount'] > 0.00 ? '1' : '0';
            $result_data['blue_discount']   = $og['blue_discount'] > 0.00 ? '1' : '0';
            $result_data['red_desc']        = "本产品最多可以使用" . $og['discount'] . "%红券抵扣现金";
            $result_data['yellow_desc']     = "本产品最多可以使用" . $og['yellow_discount'] . "%黄券抵扣现金";
            $result_data['blue_desc']       = "本产品最多可以使用" . $og['blue_discount'] . "%蓝券抵扣现金";
            //余额
            $balan = M('user')->where(array('id' => $user_id))->field('balance')->find();
            $result_data['balance'] = $balan['balance'];
            apiResponse('1', '生成订单成功', $result_data);
        }
    }

    /**
     * 购物车结算页
     * 操作类型
     * 对应的id
     */
    public function shoppingCart(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('Order','Logic')->shoppingCart(I('post.'),$user_id);
    }

    /**
     * 取消订单
     * 参数 order_id 订单id
     * User: Vernon
     * Date: 2017-12-6
     */
    public function cancelOrder(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('GroupBuyOrder','Logic')->cancelOrder(I('post.'),$user_id);
    }

    /**
     * 余额支付
     *参数 order_id 订单id discount_type代金券类型(多个用','分开)
     * User: Vernon
     * Date: 2017-12-6
     */
    public function balancePay(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
//        $user_id = 27;
        D('GroupBuyOrder','Logic')->balancePay(I('post.'),$user_id);
    }

    /**
     * 删除订单
     * 参数 order_id 订单id
     * User: Vernon
     * Date: 2017-12-6
     */
    public function deleteOrder(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('GroupBuyOrder','Logic')->deleteOrder(I('post.'),$user_id);
    }

    /**
     * 确认收货
     * 参数 order_id
     * User: Vernon
     * Date: 2017-12-06 13:40
     */
    public function receiving(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
//        $user_id = 27;
        D('GroupBuyOrder','Logic')->receiving($_POST,$user_id);
    }
    /**
     * 订单详情
     * User: Guo xin 2389541891@qq.com
     * Date: 2017-12-06 13:40
     */
    public  function  details(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['group_buy_order_id'])){
            apiResponse('0','请输入订单id');
        }
        $og = M('group_order_goods')->alias('g')->where(array('group_order_id'=>$_POST['group_buy_order_id']))
            ->join('db_group_buy_order as o on g.group_order_id=o.id')
            ->field('o.group_buy_id,g.total,o.order_sn,o.user_id,o.receiver,o.phone,o.address,o.discount,o.yellow_discount,o.blue_discount,g.merchant_name,g.goods_name,g.market_price,g.shop_price,g.goods_num,g.goods_attr,o.order_price,g.goods_img,o.order_status,o.order_type')->select();
        //直接购买
            $data1 = array();
            foreach($og as $k=>$v){
                $data1[$k]['goods_name'] = $v['goods_name'];
                $data1[$k]['market_price'] = $v['market_price'];
                $data1[$k]['shop_price'] = $v['shop_price'];
                $data1[$k]['goods_num'] = $v['goods_num'];
                $data1[$k]['group_buy_id'] = $v['group_buy_id'];
                $url = M('file')->where(array('id'=>$v['goods_img']))->getfield('abs_url');
                $data1[$k]['goods_img'] = $url;
                $all_attr = str_replace('=',':',$v['goods_attr']);
                $data1[$k]['attr'] = str_replace('|',';',$all_attr);
            }

            $data['order_type'] = $og[0]['order_type'];
            $data['user_name'] = $og[0]['receiver'];
            $data['phone'] = $og[0]['phone'];
            $data['order_status'] = $og[0]['order_status'];
            $data['address'] = $og[0]['address'];
            $data['group_buy_id'] = $og[0]['group_buy_id'];
            $data['merchant_name'] = $og[0]['merchant_name'];
            $data['leave_word'] = $og[0]['leave_word']?$og[0]['leave_word']:'';
            $data['order_price'] = $og[0]['order_price']?$og[0]['order_price']:0;
            $data['order_sn'] = $og[0]['order_sn'];
//               物流状态
            $data['logistics'] ='您的订单待配货';
            $data['logistics_time'] ='2017-12-7 11:11:11';
            $data['order_sn'] = $og[0]['order_sn'];
            $data['create_time'] = date('Y-m-d H:i:s',$og[0]['create_time'])?date('Y-m-d H:i:s',$og[0]['create_time']):'2017-12-6';
            $data['pay_time'] = date('Y-m-d H:i:s',$og[0]['pay_time'])?date('Y-m-d H:i:s',$og[0]['pay_time']):'2017-12-6';
            $data['list']=$data1;

            apiResponse('1','',$data);
    }
    /**
     * 参团
     * 参数 order_id 订单id
     * User: Vernon
     * Date: 2017-12-06 13:40
     */
    public function offered(){
        if(empty($_POST['group_buy_order_id'])){
            apiResponse('0','请输入订单id');
        }
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        $group_buy_order_info = M('group_buy_order')->where(array('id'=>$_POST['group_buy_order_id']))->find();
        $goods_id = M('group_order_goods')->where(array('group_order_id'=>$_POST['group_buy_order_id']))->getField('goods_id');
        $goods_info = M('goods')->where(array('id'=>$goods_id))->find();
        $data['goods_name'] = $goods_info['goods_name'];
        $url = M('file')->where(array('id'=>$goods_info['goods_img']))->getfield('abs_url');
        $data['goods_img'] = $url;
        $data['shop_price'] = $goods_info['shop_price'];
        $data['already'] = getName('GroupBuy','total',$group_buy_order_info['group_buy_id']);
        $data['number'] = getName('GroupBuy','group_num',$group_buy_order_info['group_buy_id']).'人团';
        //团长头像
        $data['colonel_head_pic'] = M('file')->where(array('id'=>getName('User','head_pic',$group_buy_order_info['user_id'])))->getfield('abs_url');
        $user_ids = M('group_buy_order')->where(array('p_id'=>$_POST['group_buy_order_id']))->field('user_id')->select();
//        apiResponse(1,'',$user_ids);
        $count =  M('group_buy_order')->where(array('p_id'=>$_POST['group_buy_order_id']))->count();
        //团未满
        if(($count +1) < getName('GroupBuy','group_num',$group_buy_order_info['group_buy_id'])){
            $data['status'] = '0';
            $data['m_short'] = getName('GroupBuy','group_num',$group_buy_order_info['group_buy_id']) - $count - 1;
        }else{
            //团已满
            $data['status'] = '1';
            $data['m_short'] = '';
        }
        $data['offered'] = array(
            array('oneself'=>'说明1'),
        );

        if(!empty($user_ids)){
            foreach($user_ids as $k => $v){
                $data['head_pic'][]['pic'] = M('file')->where(array('id'=>getName('User','head_pic',$v['user_id'])))->getField('abs_url');
            }
        }else{
            $data['head_pic'] =[];
        }
        //判断是否是团长
        $data['is_colonel'] = M('group_buy_order')->where(array('id'=>$_POST['group_buy_order_id'],'user_id'=>$user_id,'order_type'=>2))->count();
        //判断是否是团员
        $data['is_member'] = M('group_buy_order')->where(array('p_id'=>$_POST['group_buy_order_id'],'user_id'=>$user_id,'order_type'=>3))->count();
        apiResponse(1,'',$data);
    }


    /**
     * 评论页
     * 参数 order_id 订单id
     * User: Vernon
     * Date: 2017-12-06 13:40
     */
    public function Commentindex(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('Order','Logic')->Commentindex($_POST,$user_id);
    }

    /**
     * 评论商品
     * 参数 goods_id 商品id order_goods_id 订单商品表id product_id 商品货品id goods_name 商品名称 content评论内容 pictures图片多个用','隔开 all_star综合评论星级 1-5 merchant_id 商家id
     * User: Vernon
     * Date: 2017-12-06 13:40
     */
    public function CommentGoods(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('Order','Logic')->CommentGoods($_POST,$user_id);
    }


    /**
     * 评论订单
     * 参数
     * User: Vernon
     * Date: 2017-12-06 13:40
     */
    public function CommentOrder(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('Order','Logic')->CommentOrder($_POST,$user_id);
    }
}