<?php

namespace Manager\Logic;

/**
 * Class AcademyLogic
 * @package Manager\Logic
 */
class PriceLogic extends BaseLogic {
    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        if(!empty($request['contract_id'])) {
            //按管理员账号查询
            $param['where']['contract_id'] = $request['contract_id'];
        }
        $param['where']['status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Price')->getList($param);
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
        if(!empty($request['agreement_number'])){
            $param['where']['agreement_number'] = $request['agreement_number'];
        }
        if(!empty($request['agreement_name'])){
            $param['where']['agreement_name'] = $request['agreement_name'];
        }
        $param['where']['status'] = array('lt',9);
        $row = D('Price')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['contract'] = api('System/getFiles',array($row['contract']));

        return $row;
    }
    public function processData($data){
        $data['create_time'] = strtotime($data['create_time']);
        return $data;
    }
}