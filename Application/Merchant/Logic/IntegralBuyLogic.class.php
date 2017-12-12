<?php

namespace Merchant\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class IntegralBuyLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        $param['where']['merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $param['where']['status'] = 2;
        $param['where']['is_buy'] = 1;
        $param['where']['integral_buy_id'] = array("gt",0);
        $param['order'] = "g.click_num DESC";
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('IntegralBuy')->getList($param);
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
        $param['where']['status'] = array("lt",9);
        $row = D('IntegralBuy')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        $row['t_status'] = $row['start_time']>time()?'0':($row['end_time']<time()?'2':'1');
        //处理图片
        $row['IntegralBuy_img'] = api('System/getFiles',array($row['IntegralBuy_img']));
        return $row;
    }




}