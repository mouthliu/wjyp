<?php

namespace Manager\Logic;

/**
 * Class HelpCenterLogic
 * @package Manager\Logic
 */
class HelpCenterLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        if(!empty($_REQUEST['type'])){
            $param['where']['type'] = array('eq',$_REQUEST['type']);
        }
        if(!empty($_REQUEST['title'])){
            $param['where']['title'] = array('like','%'.$_REQUEST['title'].'%');
        }
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('HelpCenter')->getList($param);
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
        $row = D('HelpCenter')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * @param array $data
     * @return array
     */
    function processData($data = array())
    {
        $data['content'] = $_POST['content'];
        return $data;
    }
}