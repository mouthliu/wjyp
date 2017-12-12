<?php

namespace Manager\Logic;

/**
 * Class AdministratorLogic
 * @package Manager\Logic
 * 商品
 */
class AttractGoodsLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
//        if(!empty($request['status'])) {
//            $param['where']['status'] = $request['status'];
//        }else{
//            $param['where']['status'] = array(array('gt',0),array('lt',9));
//        }
//        if(isset($request['t_status'])){
//            switch($request['t_status']){
//                case 1:
//                    $param['where']['start_time'] = array('elt',time());
//                    $param['where']['end_time'] = array('egt',time());
//                    break;
//                case 2:
//                    $param['where']['end_time'] = array('lt',time());
//                    break;
//                default:
//                    $param['where']['start_time'] = array('gt',time());
//            }
//            $param['where']['status'] = 2;
//        }
        $param['where']['status'] = array('neq',9);
        $param['where']['order'] = "create_time DESC";
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('AttractGoods')->getList($param);
        foreach($result['list'] as $k => $v){
            if($v['a_id']){
                $result['list'][$k]['a_name'] = M('administrator')->where(array('id'=>$v['a_id']))->getField('account');
            }
            if($v['merchant_id']){
                $result['list'][$k]['merchant_name'] = M('merchant')->where(array('id'=>$v['merchant_id']))->getField('merchant_name');
            }
        }
//        p($result);
        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $row = D('AttractGoods')->findRow($param);

        return $row;
    }



}