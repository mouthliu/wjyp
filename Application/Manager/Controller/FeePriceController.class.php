<?php
namespace Manager\Controller;

/**
 * Class ArticleController
 * @package Manager\Controller
 * 费用单 控制器
 */
class FeePriceController extends BaseController {
    public function getIndexRelation(){
        $fee = M('fee')->where(array('id'=>$_GET['fee_id']))->field('id as fee_id,agreement_name')->find();
        $this->assign('fee',$fee);
    }
    public function getAddRelation(){
//        $serve_id = D('FeePrice','Logic')->getSelect('serve_id',0);
//        $this->assign('serve_id',$serve_id);
//
//        $festival_id = D('FeePrice','Logic')->getSelect('festival_id',0);
//        $this->assign('festival_id',$festival_id);
//
//        $credit_id = D('FeePrice','Logic')->getSelect('credit_id',0);
//        $this->assign('credit_id',$credit_id);
//
//        $use_id = D('FeePrice','Logic')->getSelect('use_id',0);
//        $this->assign('use_id',$use_id);

        $range_id = M('goods_category')->where(array('parent_id'=>0))->getField('id',true);
        $range_id = implode(',',$range_id);
        $where['parent_id'] = array('in',$range_id);
        $cate_list = M('goods_category')->where($where)->select();
        $this->assign('cate_list',$cate_list);
        $fee = M('fee')->where(array('id'=>$_GET['fee_id']))->field('id as fee_id,agreement_name')->find();
        $this->assign('fee',$fee);
    }
    public function getUpdateRelation(){
//        $this->assign('select',D('GoodsCategory','Logic')->getSelect('parent_id',I('get.parent_id')));
        $fee = M('fee')->where(array('id'=>$_GET['fee_id']))->field('id as fee_id,agreement_name')->find();
        $this->assign('fee',$fee);
        $range_id = M('goods_category')->where(array('parent_id'=>0))->getField('id',true);
        $range_id = implode(',',$range_id);
        $where['parent_id'] = array('in',$range_id);
        $cate_list = M('goods_category')->where($where)->select();
        $this->assign('cate_list',$cate_list);
    }
}