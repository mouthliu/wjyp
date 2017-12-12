<?php

namespace Merchant\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class PromotionLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        if(!empty($request['type']) && $request['type'] !=4 ) {
            //按类型筛选
            $param['where']['goods.type'] = $request['type'];
        }
        $param['where']['merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $param['where']['status'] = array('lt',9);
        $param['page_size'] = C('LIST_ROWS');//页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Promotion')->getList($param);

        //dump(D('Goods')->getLastSql()) ;

        foreach($result['list'] as $k=>$v){
            $result['list'][$k]['time_status'] = $v['start_time']>time()?'0':($v['end_time']<time()?'2':'1');
            switch($v['type']){
                case 1:
                    $result['list'][$k]['value'] = '￥'.$v['value'];
                    break;
                case 2:
                    $result['list'][$k]['value'] = $v['value'].'折';
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

        $param['where']['merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $row = D('Promotion')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['status'] = $row['start_time']>time()?'0':($row['end_time']<time()?'2':'1');
        return $row;
    }

    //设置状态
    function setStatus($request){
        if(!empty($request['ids'])){
            $data['id'] = array("IN",$request['ids']);
        }
        $newdata['update_time'] = time();
        $newdata['status'] = $request['status'];
        $res = D('Promotion')->where($data)->save($newdata);
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