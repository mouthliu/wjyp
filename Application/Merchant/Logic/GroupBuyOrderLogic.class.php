<?php

namespace Merchant\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class GroupBuyOrderLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
//        if(!empty($request['account'])) {
//            //按管理员账号查询
//            $param['where']['goods.account'] = $request['account'];
//        }
        $param['where']['goods.merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $param['order'] = "create_time DESC";
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('GroupBuyOrder')->getList($param);

        //dump(D('Goods')->getLastSql()) ;
//        dump($result);
        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['goods.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }

        $param['where']['goods.merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $row = D('GroupBuyOrder')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }

        //根据订单号获取到该订单下的商品
        $row['goods_info'] = M("OrderGoods")->where("order_id={$row['id']}")->select();
        //折扣信息
        //    =$row['discount'] = 0.00;
        if($row['ticket_id']){
            //获取到优惠券信息
            $ticket_info = M('Ticket')->where("id = {$row['ticket_id']}")->find();
            if($row['goods_amount'] >= $ticket_info['condition']){
                //判断优惠券类型
                if($ticket_info['ticket_type'] == '1'){ //满减
                    $row['discount'] = $ticket_info['value'];
                }else if($ticket_info['ticket_type'] == '2'){//满折
                    $row['discount'] = (10-$ticket_info['value'])*$row['goods_amount'];
                }
            }
        }
        return $row;
    }

    //设置状态
    function setStatus($request){
        if(!empty($request['ids'])){
            $data['id'] = array("IN",$request['ids']);
        }
        $newdata['update_time'] = time();
        $newdata['status'] = $request['status'];
        $res = D('Order')->where($data)->save($newdata);
        if($res){
            $this->setLogicSuccess("修改成功"); return true;
        }else{
            $this->setLogicError("修改失败"); return false;
        }
    }



    //时间插件所用到processData
    protected function processData($data = array()) {
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time']   = strtotime($data['end_time']);
        return $data;
    }





}