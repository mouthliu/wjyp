<?php
namespace Api\Logic;

/**
 * 房产购订单
 * Class HouseOrderLogic
 * @package Api\Logic
 */
class HouseOrderLogic extends BaseLogic{

    /**
     * 新增汽车购订单
     * @param array $request
     * @param int $user_id
     */
    public function addOrder($request = array(),$user_id = 0){
        $user_info = M('User')->where(array('id'=>$user_id))->find();
        if(!$user_info){
            apiResponse('0','用户信息查询失败');
        }
        if($request['order_id']){
            //表示订单列表里面继续支付
            $order_info = M('HouseOrder')->where(array('id'=>$_POST['order_id']))->find();
            $style_info = M('HouseStyle')->where(array('id'=>$order_info['style_id'],'status'=>array('eq',1)))->find();
            $result_data['order_id'] = $order_info['id'].'';
            $result_data['order_price'] = $order_info['goods_price'].'';
            $result_data['discount'] = $style_info['discount']>0.00?'1':'0';
            $result_data['yellow_discount'] = $style_info['yellow_discount']>0.00?'1':'0';
            $result_data['blue_discount'] = $style_info['blue_discount']>0.00?'1':'0';
            $result_data['is_integral'] = $style_info['is_integral'];
            $result_data['balance'] = $user_info['balance'];
            $result_data['integral'] = $user_info['integral'];
            $result_data['red_desc'] = "本产品最多可以使用".$style_info['discount']."%红券抵扣现金";
            $result_data['yellow_desc'] = "本产品最多可以使用".$style_info['yellow_discount']."%黄券抵扣现金";
            $result_data['blue_desc'] = "本产品最多可以使用".$style_info['blue_discount']."%蓝券抵扣现金";
            apiResponse('1','请求成功',$result_data);
        }else{
            if(empty($_POST['style_id'])){
                apiResponse('0','参数不完整');
            }
            if(empty($_POST['num'])){
                apiResponse('0','请选择购买数量');
            }

            //新增订单
            $style_info = M('HouseStyle')->where(array('id'=>$request['style_id'],'status'=>array('eq',1)))->find();
            if(empty($style_info)){
                apiResponse('0','户型信息查询失败');
            }
            $house_info = M('HouseBuy')->where(array('id'=>$style_info['house_id']))->find();
            if(empty($house_info)){
                apiResponse('0','房产购信息查询失败');
            }
            $order_sn = time().rand(10000,99999);
            $data['order_sn'] = $order_sn;
            $data['user_id'] = $user_id;
            $data['house_id'] = $house_info['id'];
            $data['house_name'] = $house_info['house_name'];
            $data['sell_address'] = $house_info['sell_address'];
            $data['link_name'] = $house_info['link_name'];
            $data['link_phone'] = $house_info['link_phone'];
            $data['lng'] = $house_info['lng'];
            $data['lat'] = $house_info['lat'];
            $data['style_id'] = $style_info['id'];
            $data['house_style_img'] = $style_info['house_style_img'];
            $data['all_price'] = $style_info['all_price'];
            $data['tags'] = $style_info['tags'];
            $data['one_price'] = $style_info['one_price'];
            $data['style_name'] = $style_info['style_name'];
            $data['pre_money'] = $style_info['pre_money'];
            $data['true_pre_money'] = $style_info['true_pre_money'];
            $data['num'] = $_POST['num'];
            $data['goods_price'] = $style_info['pre_money']*$_POST['num'];
            $data['order_price'] = $data['goods_price'];
            $data['create_time'] = time();
            $res = M('HouseOrder')->data($data)->add();
            if(!$res){
                apiResponse('0','下单失败');
            }else{
                $result_data['order_id'] = $res.'';
                $result_data['order_price'] = $data['order_price'].'';
                $result_data['discount'] = $style_info['discount']>0.00?'1':'0';
                $result_data['yellow_discount'] = $style_info['yellow_discount']>0.00?'1':'0';
                $result_data['blue_discount'] = $style_info['blue_discount']>0.00?'1':'0';
                $result_data['is_integral'] = $style_info['integral']>0.00?'1':'0';
                $result_data['balance'] = $user_info['balance'];
                $result_data['integral'] = $user_info['integral'];
                $result_data['red_desc'] = "本产品最多可以使用".$style_info['discount']."%红券抵扣现金";
                $result_data['yellow_desc'] = "本产品最多可以使用".$style_info['yellow_discount']."%黄券抵扣现金";
                $result_data['blue_desc'] = "本产品最多可以使用".$style_info['blue_discount']."%蓝券抵扣现金";
                apiResponse('1','下单成功',$result_data);
            }
        }
    }

}