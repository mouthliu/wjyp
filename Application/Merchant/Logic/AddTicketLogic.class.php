<?php

namespace Merchant\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class AddTicketLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        if(!empty($request['type']) && $request['type'] !=4 ) {
            //按类型筛选
            $param['where']['goods.ticket_type'] = $request['type'];
        }
        $param['where']['merchant_id'] = getMerchantId();
        $param['where']['status'] = array('lt',9);
        $param['order'] = "create_time DESC";
        $param['page_size'] = C('LIST_ROWS');//页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Ticket')->getList($param);

        foreach($result['list'] as $k=>$v){
            $result['list'][$k]['time_status'] = $v['start_time']>time()?'0':($v['end_time']<time()?'2':'1');
            switch($v['ticket_type']){
                case 1:
                    $result['list'][$k]['value'] = '￥'.$v['value'];
                    break;
                case 2:
                    $result['list'][$k]['value'] = $v['value'].'折';
                    break;
                case 3:
                    $result['list'][$k]['value'] = getName('Goods','goods_name',$v['value']);
                    break;
            }
        }

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

        $param['where']['merchant_id'] = getMerchantId();
        $row = D('Ticket')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['status'] = $row['start_time']>time()?'0':($row['end_time']<time()?'2':'1');
        switch($row['ticket_type']){
            case 3:
                $row['goods_name'] = getName('Goods','goods_name',$row['value']);
                break;
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
        $res = D('Ticket')->where($data)->save($newdata);
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