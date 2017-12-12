<?php
namespace Api\Logic;

/**
 * Class IntegralBuyLogic
 * @package Api\Logic
 * 逻辑层  商品信息模块
 *
 */
class IntegralBuyLogic extends BaseLogic{

    /**
     * 获取预购商品列表
     * @param array $request
     */
    public function integralBuyIndex($request = array()){
        //获取顶级分类
        $list['top_nav'] = D('GoodsCategory','Logic')->topNav()['list'];
        $list['top_nav'] = $list['top_nav']?$list['top_nav']:array();
        $first = array('cate_id'=>0,'short_name'=>'推荐','name'=>'推荐');
        array_unshift($list['top_nav'],$first);
        //1.首先根据分类列出旗下所有分类id
        if(empty($request['cate_id'])){
            //获取到默认的第一个顶级分类
            $request['cate_id'] = $list['top_nav'][1]['cate_id'];
        }
        //根据顶级分类获取到二级分类
        $list['two_cate_list'] = D('GoodsCategory','Logic')->getChildCate($request['cate_id'],1,'two_cate_id',1);
        //2.获取广告
        $list['ads'] = dealAds(D('Ads','Logic')->adsList(array('num'=>1,'position'=>'10'))[0]);
        $mod = M('Goods');

        //首先根据分类列出旗下所有分类id
        $cate_ids = D('GoodsCategory','Logic')->getCateIds($request['cate_id']);
        if($cate_ids){
            $cate_ids .= $request['cate_id'];
            $where['g.cat_id'] = array('IN',$cate_ids);
        }else{
            $where['g.cat_id'] = $request['cate_id'];
        }
//        $where['b.is_recommend'] = 1;//显示推荐商品
        $where['g.integral_buy_id'] = array('gt',0);
        $where['g.status'] = 2;
        $count = $mod->alias('g')->join(C('DB_PREFIX').'integral_buy b ON b.goods_id=g.id')->where($where)->count();
        $list['integral_buy_list'] = $mod->alias('g')
            ->field('g.id AS goods_id,g.goods_name,g.goods_img,g.country_id,b.id AS integral_buy_id,b.use_integral')
            ->join(C('DB_PREFIX').'integral_buy b ON b.goods_id=g.id')
            ->where($where)
            ->order('b.is_recommend DESC,b.exchange_num DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$list);
        }
        foreach($list['integral_buy_list'] as $k=>$v){
            $list['integral_buy_list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['integral_buy_list'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['integral_buy_list'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
        }

        apiResponse('1','获取成功',$list,$count);
    }
    /**
     * 获取三级分类商品列表
     * @param array $request
     */
    public function threeList($request = array()){
        //根据顶级分类获取到二级分类
        $list['three_cate_list'] = D('GoodsCategory','Logic')->getChildCate($request['two_cate_id'],1,'three_cate_id');
        $list['three_cate_list'] = $list['three_cate_list']?$list['three_cate_list']:array();
        $first = array('three_cate_id'=>0,'short_name'=>'全部','name'=>'全部');
        array_unshift($list['three_cate_list'],$first);
        if(empty($request['three_cate_id'])){
            //根据所选二级分类获取到旗下所有分类
            $cate_ids = D('GoodsCategory','Logic')->getCateIds($request['two_cate_id']);
            if($cate_ids){
                $cate_ids .= $request['cate_id'];
                $where['g.cat_id'] = array('IN',$cate_ids);
            }else{
                $where['g.cat_id'] = $request['two_cate_id'];
            }
        }else{
            $where['g.cat_id'] = $request['three_cate_id'];
        }
        $mod = M('Goods');
        $where['g.integral_buy_id'] = array('gt',0);
        $where['g.status'] = 2;//审核通过
        $where['g.is_buy'] = 1;//上架
        $count = $mod->alias('g')->where($where)->count();
        $list['integral_buy_list'] = $mod->alias('g')
            ->field('g.id AS goods_id,g.goods_name,g.goods_img,g.country_id,b.id AS integral_buy_id,b.use_integral')
            ->join(C('DB_PREFIX').'integral_buy b ON b.goods_id=g.id')
            ->where($where)
            ->order('b.exchange_num DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$list);
        }
        foreach($list['integral_buy_list'] as $k=>$v){
            $list['integral_buy_list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['integral_buy_list'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['integral_buy_list'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
        }
        apiResponse('1','获取成功',$list,$count);
    }
    /**
     * 获取商品详情页信息
     * @param int $IntegralBuy_id
     */
    public function integralBuyInfo($request = array(),$user_id=0){
        $goods_id = getName('IntegralBuy','goods_id',$request['integral_buy_id']);
        //调用商品详情函数
        $info = D('Goods','Logic')->goodsInfo($goods_id,$user_id);
        //根据商品ID获取到对赢得所需积分
        $info['goodsInfo']['use_integral'] = getName('IntegralBuy','use_integral',$request['integral_buy_id']);
        $info['goodsInfo']['exchange_num'] = getName('IntegralBuy','exchange_num',$request['integral_buy_id']);

        //去掉店铺优惠活动和代金券
        $info['promotion'] = array();
        $info['ticketList'] = array();
        apiResponse('1','获取数据成功',$info);
    }

    /**
     * 积分兑换操作
     *
     */
    public function doChange($request,$user_id){
        //查询出该商品兑换所需要的积分
        $info = M('IntegralBuy')->where("id = {$request['integral_buy_id']}")->find();
        //判断用户的积分够不够
        $my_integral = M('User')->where("id={$user_id}")->getField('integral');
        $true_integral = $info['use_integral'] * $request['goods_num'];
        if($true_integral > $my_integral){
            apiResponse('0','您的积分不足');
        }
        $mod = D('Order');
        $mod->startTrans();//启用回滚
        //进行积分兑换支付
        $s = integralChange($true_integral,4,$content='无界商店兑换商品'.$request['goods_num'].'件，消耗积分'.$true_integral,$user_id);
        if(!$s){
            apiResponse('0','积分兑换失败');
        }
        $mid = getName('Goods','merchant_id',$info['goods_id']);
        //使用余额
        $data['use_balance'] = 0;
        //使用积分
        $data['use_integral'] = $true_integral;
        //使用积分抵现(使用公式计算)
        $data['integral_money'] = 0;
        //订单类型
        $data['order_type'] = 5;
        //优惠券
        $data['ticket_id'] = 0;
        $data['ticket_name'] = '';
        //留言
        $data['leave_word'] = !empty($request['leave_word'])? $request['leave_word'] : '';
        //商家id
        $data['merchant_id'] = $mid;
        $data['merchant_name'] = getName('Merchant','merchant_name',$mid);
        //配送方式
        $data['shipping_id'] = $request['shipping_id'];
        $data['shipping_name'] = getName('Shipping','shipping_name',$request['shipping_id']);
        $data['shipping_fee'] = getName('Goods','send_fee',$info['goods_id']);
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
        $data['goods_amount'] = getName('Goods','shop_price',$info['goods_id']);
        //应付款金额
        $data['order_amount'] = 0;
        //订单支付状态
        $data['pay_status'] = 1;
        $data['pay_id'] = 5;//积分支付
        $data['pay_time'] = time();
        //订单状态
        $data['order_status'] = 1;//已支付
        //会员ID
        $data['user_id'] = $user_id;
        //活动处理
        $data['active_id'] = $request['integral_buy_id'];
        //生成订单处理
        $mod->checkCreate($data);
        $id = $mod->add($data);
        if($id){
            //订单号
            $s_data['order_sn'] = date('Y').date('m').date('d').date('H').zero($id,4);
            $mod->where("id = {$id}")->save($s_data);
            //往订单商品表中添加商品信息
            //根据活动id获取到活动商品信息
            $goods_info = M('Goods')->field('goods_sn,goods_name,merchant_name,merchant_id,market_price,shop_price')
                ->where("id={$info['goods_id']}")->find();
            if(!$goods_info){
                $mod->rollback();
                apiResponse('0','生成订单失败');
            }
            $g_data = $goods_info;
            $g_data['goods_id'] = $info['goods_id'];
            $g_data['product_id'] = $request['product_id']?$request['product_id']:0;
            $g_data['goods_num'] = $request['goods_num'];
            $g_data['total'] = $g_data['shop_price']*$g_data['goods_num'];
            if($request['product_id']){
                //根据货品IDh获取属性
                $g_data['goods_attr'] = getAttrGroupId1($info['goods_id'],$request['product_id']);
            }else{
                $g_data['goods_attr'] = '';
            }

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
                D('Goods')->where("id={$info['goods_id']}")->setDec('goods_num',$request['goods_num']);
            }
            //兑换数增加
            M('IntegralBuy')->where("id={$request['integral_buy_id']}")->setInc('exchange_num');
            $content = '【无界商店】您有一笔新的交易订单';
            $r = sendOrderMsg($content,$id,$user_id);
            if(!$r){
                $mod->rollback();
                apiResponse('0','生成订单失败');
            }
            $mod->commit();
            apiResponse('1','生成订单成功',array('order_id'=>$id,'order_sn'=>$s_data['order_sn']));
        }else{
            $mod->rollback();
            apiResponse('0','生成订单失败');
        }
    }

}