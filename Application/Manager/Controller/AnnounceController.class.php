<?php

namespace Manager\Controller;

/**
 * Class AnnounceController
 * @package Manager\Controller
 * 无界书院文章
 */
class AnnounceController extends BaseController {
    public function getAddRelation()
    {
//       $this->assign('type_list',M('AnnounceType')->where(array('status'=>array('neq',9)))->select());
    }

    public function getUpdateRelation()
    {
//        $this->assign('type_list',M('AnnounceType')->where(array('status'=>array('neq',9)))->select());
    }

}
