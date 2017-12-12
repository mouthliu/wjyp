<?php

namespace Merchant\Controller;

/**
 * Class MerchantDepartmentController
 * @package Merchant\Controller
 * 商家职位表
 */
class MerchantDepartmentController extends BaseController {

    /**
     * 添加时关联数据
     */
    function getAddRelation() {
        $this->assign('select',D('MerchantDepartment','Logic')->getSelect('parent_id',I('get.id')));
    }

    /**
     * 修改时关联数据
     */
    function getUpdateRelation() {
        $this->assign('select',D('MerchantDepartment','Logic')->getSelect('parent_id',I('get.parent_id')));
    }
}
