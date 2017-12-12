<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class HouseBuyListController extends BaseController {

    protected function getIndexRelation(){}
    protected function getUpdateRelation(){}
    protected function getAddRelation(){}

    function setRecommend(){
        $data['is_recommend'] = isset($_POST['is_recommend'])?$_POST['is_recommend']: die('error');
        $res = D('HouseBuy')->where("id={$_POST['HouseBuy_id']}")->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
    //获取评论
    function comment(){
        $result = D('OtherComment','Logic')->getList(I('request.'));
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        }
        $this->display('OtherComment/index');
    }
}
