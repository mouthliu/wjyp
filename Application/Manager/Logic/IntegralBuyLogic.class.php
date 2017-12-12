<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class IntegralBuyLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {

        $param['where']['status'] = 2;
        $param['where']['is_buy'] = 1;
        $param['where']['integral_buy_id'] = array("gt",0);
        $param['order'] = "g.click_num DESC";
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('IntegralBuy')->getList($param);
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
        $row = D('IntegralBuy')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['t_status'] = $row['start_time']>time()?'0':($row['end_time']<time()?'2':'1');
        //处理图片
        $row['IntegralBuy_img'] = api('System/getFiles',array($row['IntegralBuy_img']));
        return $row;
    }



    //时间插件所用到processData
    protected function processData($data = array()) {
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time']   = strtotime($data['end_time']);
        return $data;
    }


    function addIntegralBuyGoods($request = array()){
        $param['where']['goods.status'] = 2;//状态
        $param['where']['goods.integral_buy_id'] = 0;//已经加入的去了
        $param['where']['goods.is_buy'] = 1;//上架的
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

    public function afterRemove($result = 0, $request = array()){
        //删除后将商品那边的设置为0
        //判断数组ID 字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['integral_buy_id'] = array('IN', $request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['integral_buy_id'] = $request['ids'];
            $ids = $request['ids'];
        }
        //将商品中有这样id的都设为
        D('Goods')->where($where)->save(array('integral_buy_id'=>0));
        return true;
    }

}