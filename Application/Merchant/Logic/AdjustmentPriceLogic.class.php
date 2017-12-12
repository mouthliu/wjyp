<?php

namespace Merchant\Logic;

/**
 * Class AdjustmentPriceLogic
 * @package Merchant\Logic
 */
class AdjustmentPriceLogic extends BaseLogic {

    /**
     * @param array $request
     * @return array
     * 获取行为列表
     */
    function getList($request = array()) {
//        $param['where']['parent_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $param['where']['goods_id']  = $_REQUEST['goods_id'];
        $param['where']['status']   = array('lt',9);        //状态
        $param['order']             = 'create_time DESC';   //排序
        $param['page_size']         = C('LIST_ROWS');        //页码
        $param['parameter']         = $request;             //拼接参数

        $result = D('AdjustmentPrice')->getList($param);

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
        $row = D('AdjustmentPrice')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        return $row;
    }

    /**
     * 处理提交的数据
     * @param array $data
     * @return array
     */
    protected function processData($data = array()) {
        $goods_id = $_POST['goods_id'];
        $adjustment_sn = time();
        $goods_info = M('Goods')->where(array('id'=>$goods_id))->find();
        $data['goods_id'] = $goods_id;
        $data['old_market_price'] = $goods_info['market_price'];
        $data['old_shop_price'] = $goods_info['shop_price'];
        $data['old_settlement_price'] = $goods_info['settlement_price'];
        $data['create_time'] = time();
        $data['adjustment_sn'] = $adjustment_sn;

        M('Goods')->where(array('id'=>$goods_id))->data(array(
            'is_doing_adjustment'=>1,
            'adjustment_sn'=>$adjustment_sn,
            'update_time'=>time()
        ))->save();

        return $data;
    }
}