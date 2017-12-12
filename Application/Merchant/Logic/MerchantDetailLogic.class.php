<?php

namespace Merchant\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class MerchantDetailLogic extends BaseLogic {
    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        if(!empty($request['order_sn'])){
            $param['where']['goods.order_sn'] = $request['order_sn'];
        }
        if(!empty($request['time'])){
            switch($request['time']){
                case 1: $time = time(); break;
                case 2: $time = strtotime('yesterday'); break;
                case 3: $time = strtotime('-7 day'); break;
                case 4: $time = strtotime('-1 month'); break;
            }
            $param['where']['goods.create_time'] = array('egt',$time);
        }
        if(!empty($request['pay_type'])){
            $param['where']['goods.pay_id'] = $request['pay_type'];
        }
        $param['where']['goods.merchant_id'] = getMerchantId();
//        $param['where']['goods.status'] = array('lt',9);//状态
//        $param['order'] = 'sort DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('MerchantDetail')->getList($param);
        foreach($result['list'] as $k =>$v){
            $result['list'][$k]['ads_pic'] = M('file')->where(array('id'=>$v['ads_pic']))->getField('path');
        }
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

        $param['where']['goods.merchant_id'] = getMerchantId();
        $param['where']['goods.status'] = array('lt',9);

        $row = D('MerchantDetail')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }

        //处理略缩图
        $row['ads_pic'] = api('System/getFiles', array($row['ads_pic']));
        return $row;
    }




}