<?php

namespace Merchant\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class BonusLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        $param['where']['bonus_face_id'] = $request['bonus_face_id'];
        $param['where']['merchant_id'] = getMerchantId();
        $param['order'] = 'sort DESC';
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('Bonus')->getList($param);
//        dump(D('Bonus')->getLastSql());
//        dump($result);
        foreach($result['list'] as $k =>$v){
            $result['list'][$k]['bonus_ads'] = M('file')->where(array('id'=>$v['bonus_ads']))->getField('path');
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
        $param['where']['status'] = array("lt",9);
        $row = D('Bonus')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //处理图片
        if($row['type'] == 2){
            $row['bonus_ads'] = api('System/getFiles',array($row['bonus_ads']));
        }

        return $row;
    }

    function faceIndex($request = array()){

        $param['where']['goods.merchant_id'] = getMerchantId();//店铺筛选
        $param['where']['goods.status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('BonusFace')->getList($param);
        foreach($result['list'] as $k =>$v){
            $result['list'][$k]['bonus_face'] = M('file')->where(array('id'=>$v['bonus_face']))->getField('path');
            if($v['send_money'] >= $v['total_money']){
                $result['list'][$k]['t_status'] = "已被领完";
            }else{
                $result['list'][$k]['t_status'] = '剩余金额'.($v['total_money']-$v['send_money']).'元';
            }
        }
        return $result;

    }






}