<?php

namespace Manager\Logic;

/**
 * Class AcademyTypeLogic
 * @package Manager\Logic
 * 无界书院分类
 */
class AcademyTypeLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time';   //排序
        $param['page_size']         = 15;        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('AcademyType')->getList($param);

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
        $param['where']['status'] = array('lt',9);
        $row = D('AcademyType')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}