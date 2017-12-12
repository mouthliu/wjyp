<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class TicketBuyLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {

        $param['where']['status'] = 2;
        $param['where']['is_buy'] = 1;
        $param['where']['ticket_buy_id'] = array("gt",0);
        $param['order'] = 'sort DESC';
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('TicketBuy')->getList($param);
//        dump(D('TicketBuy')->getLastSql());
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
        $param['where']['status'] = array("lt",9);
        $row = D('TicketBuy')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['t_status'] = $row['start_time']>time()?'0':($row['end_time']<time()?'2':'1');
        //处理图片
        $row['TicketBuy_img'] = api('System/getFiles',array($row['TicketBuy_img']));
        return $row;
    }


    //时间插件所用到processData
    protected function processData($data = array()) {
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time']   = strtotime($data['end_time']);
        return $data;
    }


    function addTicketBuyGoods($request = array()){
        $param['where']['goods.status'] = 2;//审核通过的状态
        $param['where']['goods.ticket_buy_id'] = 0;//已经加入的去了
        $param['where']['goods.is_buy'] = 1;//上架的

        $param['where']['goods.is_active'] = 0;//除掉活动专属的
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
            $where['ticket_buy_id'] = array('IN', $request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['ticket_buy_id'] = $request['ids'];
            $ids = $request['ids'];
        }
        //将商品中有这样id的都设为o
        D('Goods')->where($where)->save(array('ticket_buy_id'=>0));
        return true;
    }

}