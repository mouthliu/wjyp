<?php

namespace Manager\Logic;

/**
 * Class HeadlinesLogic
 * @package Manager\Logic
 */
class HeadlinesLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
        if(!empty($request['title'])){
            $param['where']['title']   =  likeArr($request['title']); // 账号
        }
        if(!empty($request['source'])){
            $param['where']['source']   =  likeArr($request['source']); // 昵称
        }

        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('Headlines')->getList($param);

        return $result;
    }

    function processData($data = array())
    {
        $data['status'] = 1;
        $data['content'] = $_POST['content'];
        return $data;
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
        $row = D('Headlines')->findRow($param);

        $row['logo'] = api('System/getFiles',array($row['logo']));

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}