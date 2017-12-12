<?php

namespace Manager\Controller;

/**
 * Class DepartmentController
 * @package Manager\Controller
 * 商家职位表
 */
class DepartmentController extends BaseController {

    /**
     * 添加时关联数据
     */
    function getAddRelation() {
        $this->assign('select',D('Department','Logic')->getSelect('parent_id',I('get.id')));
    }

    /**
     * 修改时关联数据
     */
    function getUpdateRelation() {
        $this->assign('select',D('Department','Logic')->getSelect('parent_id',I('get.parent_id')));
    }
}
