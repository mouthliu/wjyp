<?php
namespace Manager\Controller;

/**
 * Class ArticleController
 * @package Manager\Controller
 * 商品属性 控制器 给予商品类类型
 */
class AttributeController extends BaseController {

    protected function getIndexRelation(){
        $this->assign("type_list",M('GoodsType')->field('id,type_name')->select());
    }
    protected function getUpdateRelation(){
        $this->assign("type_list",M('GoodsType')->field('id,type_name')->select());
    }
    protected function getAddRelation(){
        $this->assign("type_list",M('GoodsType')->field('id,type_name')->select());
    }

}
