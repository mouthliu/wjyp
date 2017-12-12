<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class HouseStyleController extends BaseController {

    protected function getIndexRelation(){

    }
    protected function getUpdateRelation(){


    }

    protected function getAddRelation(){

    }

    function setRecommend(){
        $data['is_recommend'] = isset($_POST['is_recommend'])?$_POST['is_recommend']: die('error');
        $res = D('HouseStyle')->where("id={$_POST['HouseStyle_id']}")->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
}
