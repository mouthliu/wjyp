<?php
namespace Manager\Controller;

/**
 * Class ArticleController
 * @package Manager\Controller
 * 商品类型 控制器//处理分类

 */
class GoodsTypeController extends BaseController {
    /**
     * 添加时 获取相关系数据
     * 例：添加文章时 要获取文章分类列表，添加管理员获取组列表等
     */
    protected function getAddRelation()
    {
        $this->assign('cateSelect',D('Goods','Logic')->getArrayCates('0'));
    }

    /**
     * 修改时 获取相关系数据
     * 例：添加文章时 要获取文章分类列表，添加管理员获取组列表等
     */
    protected function getUpdateRelation()
    {
        $this->assign('cateSelect',D('Goods','Logic')->getArrayCates('0'));
    }



}
