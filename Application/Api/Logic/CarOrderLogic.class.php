<?php
namespace Api\Logic;

/**
 * 汽车购订单
 * Class CarOrderLogic
 * @package Api\Logic
 */
class CarOrderLogic extends BaseLogic{

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
            $order_info = M('CarOrder')->where(array('id'=>$_POST['order_id']))->find();
            $car_info = M('CarBuy')->where(array('id'=>$order_info['car_id'],'status'=>array('eq',1)))->find();
            $result_data['order_id'] = $order_info['id'].'';
            $result_data['order_price'] = $order_info['goods_price'].'';
            $result_data['discount'] = $car_info['discount']>0.00?'1':'0';
            $result_data['yellow_discount'] = $car_info['yellow_discount']>0.00?'1':'0';
            $result_data['blue_discount'] = $car_info['blue_discount']>0.00?'1':'0';
            $result_data['is_integral'] = $car_info['is_integral'];
            $result_data['balance'] = $user_info['balance'];
            $result_data['integral'] = $user_info['integral'];
            $result_data['red_desc'] = "本产品最多可以使用".$car_info['discount']."%红券抵扣现金";
            $result_data['yellow_desc'] = "本产品最多可以使用".$car_info['yellow_discount']."%黄券抵扣现金";
            $result_data['blue_desc'] = "本产品最多可以使用".$car_info['blue_discount']."%蓝券抵扣现金";
            apiResponse('1','请求成功',$result_data);
        }else{
            if(empty($_POST['car_id'])){
                apiResponse('0','参数不完整');
            }
            if(empty($_POST['num'])){
                apiResponse('0','请选择购买数量');
            }

            //新增订单
            $car_info = M('CarBuy')->where(array('id'=>$request['car_id'],'status'=>array('eq',1)))->find();
            if(empty($car_info)){
                apiResponse('0','汽车购信息查询失败');
            }
            $order_sn = time().rand(10000,99999);
            $data['order_sn'] = $order_sn;
            $data['user_id'] = $user_id;
            $data['car_id'] = $car_info['id'];
            $data['car_img'] = $car_info['car_img'];
            $data['car_name'] = $car_info['car_name'];
            $data['all_price'] = $car_info['all_price'];
            $data['num'] = $request['num'];
            $data['pre_money'] = $car_info['pre_money'];
            $data['true_pre_money'] = $car_info['true_pre_money'];
            $data['create_time'] = time();
            $data['goods_price'] = $car_info['pre_money']*$request['num'];
            $data['order_price'] = $car_info['pre_money']*$request['num'];
            $data['shop_name'] = $car_info['shop_name'];
            $data['lng'] = $car_info['lng'];
            $data['lat'] = $car_info['lat'];
            $data['shop_mobile'] = $car_info['shop_mobile'];
            $data['contact_name'] = $car_info['contact_name'];
            $data['contact_mobile'] = $car_info['contact_mobile'];
            $province_name = M('Region')->where(array('id'=>$car_info['province_id']))->getField('region_name');
            $city_name = M('Region')->where(array('id'=>$car_info['city_id']))->getField('region_name');
            $area_name = M('Region')->where(array('id'=>$car_info['area_id']))->getField('region_name');
            $street_name = M('Street')->where(array('street_id'=>$car_info['street_id']))->getField('street_name');
            $province_name = $province_name?$province_name:'';
            $city_name = $city_name?$city_name:'';
            $area_name = $area_name?$area_name:'';
            $street_name = $street_name?$street_name:'';
            $data['address'] = $province_name.$city_name.$area_name.$street_name.$car_info['address'];
            $res = M('CarOrder')->data($data)->add();
            if(!$res){
                apiResponse('0','下单失败');
            }else{
                $result_data['order_id'] = $res.'';
                $result_data['order_price'] = $data['order_price'].'';
                $result_data['discount'] = $car_info['discount']>0.00?'1':'0';
                $result_data['yellow_discount'] = $car_info['yellow_discount']>0.00?'1':'0';
                $result_data['blue_discount'] = $car_info['blue_discount']>0.00?'1':'0';
                $result_data['is_integral'] = $car_info['is_integral'];
                $result_data['balance'] = $user_info['balance'];
                $result_data['integral'] = $user_info['integral'];

                $result_data['red_desc'] = "本产品最多可以使用".$car_info['discount']."%红券抵扣现金";
                $result_data['yellow_desc'] = "本产品最多可以使用".$car_info['yellow_discount']."%黄券抵扣现金";
                $result_data['blue_desc'] = "本产品最多可以使用".$car_info['blue_discount']."%蓝券抵扣现金";
                apiResponse('1','下单成功',$result_data);
            }

        }
    }

}