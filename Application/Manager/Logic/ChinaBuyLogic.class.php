<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class ChinaBuyLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {

        $param['where']['status'] = 2;
        $param['where']['is_buy'] = 1;
        $param['order'] = "sort DESC";
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('ChinaBuy')->getList($param);
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
        $row = D('ChinaBuy')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['t_status'] = $row['start_time']>time()?'0':($row['end_time']<time()?'2':'1');
        //处理图片
        $row['ChinaBuy_img'] = api('System/getFiles',array($row['ChinaBuy_img']));
        return $row;
    }



    //时间插件所用到processData
    protected function processData($data = array()) {
//        $data['start_time'] = strtotime($data['start_time']);
//        $data['end_time']   = strtotime($data['end_time']);
        return $data;
    }


    function addChinaBuyGoods($request = array()){
        $param['where']['goods.status'] = 2;//状态
        $param['where']['goods.is_buy'] = 1;//上架的
        $param['where']['goods.is_china'] = 0;//上架的
        $param['where']['goods.country_id'] = 0;////国内的
        if(!empty($request['merchant_name'])){
            $param['where']['merchant_name'] = array('LIKE','%'.$request['merchant_name'].'%');
        }
        if(!empty($request['goods_name'])){
            $param['where']['goods_name'] = array('LIKE','%'.$request['goods_name'].'%');
        }
        if(!empty($request['goods_sn'])){
            $param['where']['goods_sn'] = $request['goods_sn'];
        }

        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Goods')->getList($param);
//        dump(D('Goods')->getLastSql());
        return $result;
    }


}