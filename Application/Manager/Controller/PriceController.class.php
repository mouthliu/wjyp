<?php
namespace Manager\Controller;

/**
 * Class ArticleController
 * @package Manager\Controller
 * 费用单 控制器
 */
class PriceController extends BaseController {
    public function getIndexRelation(){
        $contract = M('contract')->where(array('id'=>$_GET['contract_id']))->field('id as contract_id,agreement_name')->find();
//        p($contract);
        $this->assign('contract',$contract);
    }
    public function getUpdateRelation(){
        $contract = M('contract')->where(array('id'=>$_GET['contract_id']))->field('id as contract_id,agreement_name')->find();
        $this->assign('contract',$contract);
    }
}