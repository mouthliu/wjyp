<?php

namespace Manager\Logic;

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
        if(!empty($request['merchant_name'])){
            $param['where']['merchant_name'] = array('LIKE','%'.$request['merchant_name'].'%');
        }
        $param['order'] = 'create_time DESC';//排序
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