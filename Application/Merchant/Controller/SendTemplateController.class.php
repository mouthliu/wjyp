<?php
namespace Merchant\Controller;

/**
 * Class SendTemplateController
 * @package Merchant\Controller
 * 发信模板控制器
 */
class SendTemplateController extends BaseController {


    /**
     * 修改时关联数据
     */
    function getUpdateRelation() {
        $this->assign('types',C('SEND_TEMPLATE_TYPES'));
    }

    /**
     * 新添时关联数据
     */
    function getAddRelation() {
        $this->assign('types',C('SEND_TEMPLATE_TYPES'));
    }
}
