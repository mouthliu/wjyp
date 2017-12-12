<?php

namespace Manager\Controller;

/**
 * Class AcademyController
 * @package Manager\Controller
 * 无界书院文章
 */
class AcademyController extends BaseController {
    public function getAddRelation()
    {
       $this->assign('type_list',M('AcademyType')->where(array('status'=>array('neq',9)))->select());
    }

    public function getUpdateRelation()
    {
        $this->assign('type_list',M('AcademyType')->where(array('status'=>array('neq',9)))->select());
    }
}
