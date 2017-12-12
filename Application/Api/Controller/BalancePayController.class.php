<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 余额支付模块控制器
 * Class GoodsController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class BalancePayController extends BaseController{

    /**
     * 余额支付
     *参数
     *  order_id 订单id
     *  discount_type代金券类型(多个用','分开)
     *  order_type 1普通订单 2拼单购 3预购 4比价购 5限量购
     * User: Vernon
     * Date: 2017-12-9
     */

    public function balancePay(){
//        $user_id = 47;
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('BalancePay','Logic')->balancePay(I('post.'),$user_id);
    }
}