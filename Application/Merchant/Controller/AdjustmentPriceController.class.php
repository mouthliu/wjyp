<?php

namespace Merchant\Controller;

/**
 * Class AdjustmentPriceController
 * @package Merchant\Controller
 * 调价单控制器
 */
class AdjustmentPriceController extends BaseController {

    public function getAddRelation()
    {
        $goods_id = $_GET['goods_id'];
        $goods_info = M('Goods')->where(array('id'=>$goods_id))->find();
        $this->assign('goods_info',$goods_info);
    }

    /**
     * 查看详情
     */
    public function detail(){
        if ($_GET['id']) {
            $Object = D(CONTROLLER_NAME, 'Logic');
            $row = $Object->findRow(I('get.'));
            if ($row) {
                $row['adjustment_picture'] = api('System/getFiles', array($row['adjustment_picture']));
                $this->assign('row', $row);
            } else {
                $this->error($Object->getLogicError());
            }
        }
        $this->display('detail');
    }
}
