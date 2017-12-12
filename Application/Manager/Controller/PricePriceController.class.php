<?php
namespace Manager\Controller;

/**
 * Class ArticleController
 * @package Manager\Controller
 * 费用单 控制器
 */
class PricePriceController extends BaseController {
    public function getIndexRelation(){
        $price = M('price')->where(array('id'=>$_GET['price_id']))->field('id as price_id,agreement_name')->find();
        $this->assign('price',$price);
    }
    public function getAddRelation(){
        $range_id = M('goods_category')->where(array('parent_id'=>0))->getField('id',true);
        $range_id = implode(',',$range_id);
        $where['parent_id'] = array('in',$range_id);
        $cate_list = M('goods_category')->where($where)->select();
        $this->assign('cate_list',$cate_list);
        $price = M('price')->where(array('id'=>$_GET['price_id']))->field('id as price_id,agreement_name')->find();
        $this->assign('price',$price);
    }
    public function getUpdateRelation(){
        $price = M('price')->where(array('id'=>$_GET['price_id']))->field('id as price_id,agreement_name')->find();
        $this->assign('price',$price);
        $range_id = M('goods_category')->where(array('parent_id'=>0))->getField('id',true);
        $range_id = implode(',',$range_id);
        $where['parent_id'] = array('in',$range_id);
        $cate_list = M('goods_category')->where($where)->select();
        $this->assign('cate_list',$cate_list);
    }
}