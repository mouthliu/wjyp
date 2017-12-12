<?php

namespace Manager\Logic;

/**
 * 商品价格说明
 * Class GoodsPriceDescLogic
 * @package Manager\Logic
 */
class GoodsPriceDescLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        $param['where']['status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('GoodsPriceDesc')->getList($param);
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


        $param['where']['status'] = array('lt',9);
        $row = D('GoodsPriceDesc')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['icon'] = api('System/getFiles',array($row['icon']));
        return $row;
    }
}