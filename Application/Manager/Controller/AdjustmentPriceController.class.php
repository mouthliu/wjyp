<?php
namespace Manager\Controller;

/**
 * Class ArticleController
 * @package Manager\Controller
 * 费用单 控制器
 */
class AdjustmentPriceController extends BaseController {
    public function getIndexRelation(){
        $adjustment = M('adjustment')->where(array('id'=>$_GET['adjustment_id']))->field('id as adjustment_id,agreement_name')->find();
        $this->assign('adjustment',$adjustment);
    }
    public function getAddRelation(){
        $contract_id = M('adjustment')->where(array('id'=>$_GET['adjustment_id']))->getField('contract_id');
        $merchant_id = M('contract')->where(array('id'=>$contract_id))->getField('merchant_id');
        $goods = M('goods')->where(array('merchant_id'=>$merchant_id))->field('id as goods_id,goods_name,goods_code,settlement_price,shop_price,market_price')->select();
//        p($goods);
        $this->assign('goods',$goods);
        $range_id = M('goods_category')->where(array('parent_id'=>0))->getField('id',true);
        $range_id = implode(',',$range_id);
        $where['parent_id'] = array('in',$range_id);
        $cate_list = M('goods_category')->where($where)->select();
        $this->assign('cate_list',$cate_list);
        $adjustment = M('adjustment')->where(array('id'=>$_GET['adjustment_id']))->field('id as adjustment_id,agreement_name')->find();
        $this->assign('adjustment',$adjustment);
    }
    public function getUpdateRelation(){
        $contract_id = M('adjustment')->where(array('id'=>$_GET['adjustment_id']))->getField('contract_id');
        $merchant_id = M('contract')->where(array('id'=>$contract_id))->getField('merchant_id');
        $goods = M('goods')->where(array('merchant_id'=>$merchant_id))->field('id as goods_id,goods_name,goods_code,settlement_price,shop_price,market_price')->select();
//        p($goods);
        $this->assign('goods',$goods);
        $adjustment = M('adjustment')->where(array('id'=>$_GET['adjustment_id']))->field('id as adjustment_id,agreement_name')->find();
        $this->assign('adjustment',$adjustment);
        $range_id = M('goods_category')->where(array('parent_id'=>0))->getField('id',true);
        $range_id = implode(',',$range_id);
        $where['parent_id'] = array('in',$range_id);
        $cate_list = M('goods_category')->where($where)->select();
        $this->assign('cate_list',$cate_list);
    }
}