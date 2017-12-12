<?php

namespace Merchant\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class BackApplyLogic extends BaseLogic {

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
//        $param['BackApply'] = "create_time DESC";
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('BackApply')->getList($param);

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
        $row = D('BackApply')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['back_img'] = M('file')->where("id={$row['back_img']}")->getField("path");
        //根据订单号获取到该订单下的商品
        $row['goods_info'] = M("order_goods")->where("id={$row['order_goods_id']}")->find();
        //获取到原订单信息
        $row['order_info'] = M("order")->where("id={$row['order_id']}")->find();

        return $row;
    }

    //设置状态
    function setStatus($request){
        if(!empty($request['ids'])){
            $data['id'] = array("IN",$request['ids']);
        }
        $newdata['update_time'] = time();
        $newdata['status'] = $request['status'];
        $res = D('BackApply')->where($data)->save($newdata);
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