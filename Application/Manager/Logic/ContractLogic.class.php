<?php

namespace Manager\Logic;

/**
 * Class AcademyLogic
 * @package Manager\Logic
 *
 */
class ContractLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取广告列表
     */

    function getList($request = array()) {

        if(!empty($request['id'])){
            $param['where']['m_id'] = $request['id'];
        }
        if(!empty($request['type'])){
            $param['where']['type'] = $request['type'];
        }
        if(!empty($request['merchant_id'])){
            $param['where']['merchant_id'] = $request['merchant_id'];
        }
        if(!empty($request['agreement_number'])){
            $param['where']['agreement_number'] = $request['agreement_number'];
        }if(!empty($request['agreement_name'])){
            $param['where']['agreement_name'] = $request['agreement_name'];
        }

//        $param['where']['status']   = array('lt',9);
        $param['order']             = 'create_time DESC';
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Contract')->getList($param);
        foreach($result['list'] as $k => $v){
            if(!empty($v['merchant_id'])){
                $result['list'][$k]['m_name'] = M('merchant')->where(array('id'=>$v['merchant_id']))->getField('merchant_name');
            }
        }
        return $result;
    }

    /**
     * @param array $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        if(!empty($request['merchant_id'])){
            $param['where']['merchant_id'] = $request['merchant_id'];
        }
        $row = D('Contract')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }else{
            if(!empty($row['contract'])){
                $row['contract_img'] = api('System/getFiles',array($row['contract']));
            }
        }
//        p($row);
        return $row;
    }

    public function processData($data){
        $data['start_time'] = strtotime($data['start_time']);
        $data['end_time'] = strtotime($data['end_time']);
        return $data;
    }

    public function afterUpdate($result,$request){
        if($result == 1){
            if($request['status'] == 1){
                $contract_id = M('contract')->where(array('id'=>$request['id']))->getField('p_id');
                $save['contract_id'] = $request['id'];
                $save['update_time'] = time();
                $where['contract_id'] = $contract_id;
                M('fee')->where($where)->save($save);
                M('adjustment')->where($where)->save($save);
                M('price')->where($where)->save($save);
                return true;
            }
        }else{
            return true;
        }
    }
}