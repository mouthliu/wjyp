<?php

namespace Merchant\Logic;

/**
 * 商家绑定用户控制器
 * Class MerchantBindUserLogic
 * @package Manager\Logic
 */
class MerchantBindUserLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取列表
     */
    function getList($request = array()) {
        $param['where']['m_b_u.merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $param['order']             = 'm_b_u.create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数
        $result = D('MerchantBindUser')->getList($param);

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
        $row = D('MerchantBindUser')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }
}