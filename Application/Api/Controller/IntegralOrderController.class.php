<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 无界预购控制器
 * Class OrderController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 * User: Guo xin 2389541891@qq.com
 * Date: 2017-12-07 10:36
 *
 */

class IntegralOrderController extends BaseController{
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
        D('IntegralOrder','Logic')->OrderList(I('post.'),$user_id);
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
    public function SetOrder(){
//        buy_type 购买途径（购物车2 、立即购买1）
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['order_id'])){
            if(empty($_POST['address_id'])){
                apiResponse('0','请选择收货地址');
            }
             if(empty($_POST['goods_num'])){
                    apiResponse('0','请输入商品数量');
                }
             if(empty($_POST['goods_id'])){
                    apiResponse('0','请输入商品id');
                }
            D('IntegralOrder','Logic')->SetOrder(I('post.'),$user_id);
        }else{
            $og = M('integral_order')->where(array('id'=>$_POST['order_id']))->field('order_price,discount,yellow_discount,blue_discount,merchant_name,integral_num,balance_num,merchant_id,id,goods_id,goods_num')->find();

            $inte_num = M('integral_order_goods')->alias("g")
                ->where(array('g.goods_id'=>$og['goods_id']))
                ->join("db_integral_order as o on o.id=g.order_id")
                ->where(array('order_status'=>8))
                ->field('o.goods_num')->select();
            //        获取活动商品信息
            $time = M('one_buy')->where(array('goods_id' => $og['goods_id']))
                ->find();
            //                    判断库存
            $inte_pric = count($inte_num);
            $inte_pric += $og['goods_num'];
            if($time['person_num']<$inte_pric){
                apiResponse('0', '库存不足');
            }
            //            超时
            if ($time['end_time'] < time()) {
                apiResponse('0', '抢购时间已过');
            }
//        未到
            if (time() < $time['start_time']) {
                apiResponse('0', '未到抢购时间');
            }
            $result_data['order_id'] = $og['id'];
            $result_data['order_price'] = $og['order_price'];
            $result_data['merchant_name'] = $og['merchant_name'];
            $result_data['merchant_id'] = $og['merchant_id'];
            $result_data['red_desc']      = "本产品需使用" . $og['discount'] . "红券";
            $result_data['yellow_desc']   = "本产品需使用" . $og['yellow_discount'] . "黄券";
            $result_data['balance_num']   = "本产品需使用" . $og['balance_num'] . "余额";
            $result_data['integral_num']  = "本产品需使用" . $og['integral_num'] . "积分";
            //                    余额
            $balan = M('user')->where(array('id'=>$user_id))->field('balance')->find();
            $result_data['balance'] = $balan['balance'];
            apiResponse('1','生成订单成功',$result_data);
        }

    }

    /**
     * 购物车结算页
     * 操作类型 type(团购的时候有,默认为0)
     * 对应的id
     */
    public function ShoppingCart(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('IntegralOrder','Logic')->ShoppingCart(I('post.'),$user_id);
    }

    /**
     * 取消订单
     * 参数 order_id 订单id
     * User: Vernon
     * Date: 2017-12-6
     */
    public function CancelOrder(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('IntegralOrder','Logic')->CancelOrder(I('post.'),$user_id);
    }

    /**
     * 余额支付
     *参数 order_id 订单id discount_type代金券类型(多个用','分开)
     * User: Vernon
     * Date: 2017-12-6
     */
    public function BalancePay(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('IntegralOrder','Logic')->BalancePay(I('post.'),$user_id);
    }
    /**
     * 付款页面
     * 订单类型 order_type
     * 操作类型 type(团购的时候有,默认为0)
     * 对应的id
     */
    function payOrder(){

    }

    /**
     * 评价订单
     * 参数 order_id 订单id delivery_star配送评分星级 merchant_star店铺评分星级  all_star综合评分星级 content 评论内容 pictures图片
     * User: Vernon
     * Date: 2017-12-6
     */
    public function evaluateOrder(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('Order','Logic')->evaluateOrder(I('post.'),$user_id);
    }

    /**
     * 删除订单
     * 参数 order_id 订单id
     * User: Vernon
     * Date: 2017-12-6
     */
    public function DeleteOrder(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('IntegralOrder','Logic')->DeleteOrder(I('post.'),$user_id);
    }

    /**
    * 订单详情
    * User: Guo xin 2389541891@qq.com
    * Date: 2017-12-06 13:40
     * order_id 订单id
    */
    public  function  preDetails(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['order_id'])){
            apiResponse('0','请输入订单id');
        }
            $og = M('integral_order_goods')->alias('g')->where(array('order_id'=>$_POST['order_id']))
                ->join('db_integral_order as o on g.order_id=o.id')
//                ->field('o.order_sn,o.user_id,o.receiver,o.phone,o.address,o.discount,o.yellow_discount,o.blue_discount,g.merchant_name,g.goods_name,g.shop_price,g.goods_num,g.goods_attr,o.order_price,g.goods_img,o.order_status,g.start_price,g.final_price,o.create_time,o.pay_time,o.order_status')
                ->select();
            $data1 = array();
            foreach($og as $k=>$v){
                $data1[$k]['goods_name'] = $v['goods_name'];
                $data1[$k]['goods_img'] = $v['goods_img'];
                $data1[$k]['number'] = $v['number'];
                $all_attr = str_replace('=',':',$v['goods_attr']);
                $data1[$k]['attr'] = str_replace('|',';',$all_attr);
                $url = M('file')->where(array('id'=>$v['goods_img']))->getfield('abs_url');

                $data1[$k]['goods_img'] = $url;
            }
//                $data['user_name'] = $og[0]['receiver'];
//                $data['order_status'] = $og[0]['order_status'];
//                $data['phone'] = $og[0]['phone'];
//                $data['address'] = $og[0]['address'];
                $data['merchant_name'] = $og[0]['merchant_name'];
                $data['order_status'] = $og[0]['order_status'];
//                $data['leave_word'] = $og[0]['leave_word']?$og[0]['leave_word']:'';
//                $data['order_price'] = $og[0]['order_price']?$og[0]['order_price']:0;
                $data['order_sn'] = $og[0]['order_sn'];
                $data['goods_num'] = $v['goods_num'];
                $data['list']=$data1;

            apiResponse('1','',$data);
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
        D('preOrder','Logic')->Commentindex($_POST,$user_id);
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
        D('preOrder','Logic')->CommentGoods($_POST,$user_id);
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

    /**
     * 确认收货
     * 参数 order_id
     * User: Vernon
     * Date: 2017-12-06 13:40
     */
    public function Receiving(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('IntegralOrder','Logic')->Receiving($_POST,$user_id);
    }

}