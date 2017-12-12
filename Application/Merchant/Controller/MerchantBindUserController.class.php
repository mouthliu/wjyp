<?php

namespace Merchant\Controller;

/**
 * Class MerchantBindUserController
 * @package Merchant\Controller
 * 商家绑定用户账号
 */
class MerchantBindUserController extends BaseController {

    function getIndexRelation() {
        $this->assign('select',D('MerchantDepartment','Logic')->getSelect('department_id',I('request.department_id')));
        $id = getMerchantId();
        $user_id = M('merchant')->where(array('id'=>$id))->getField('user_id');
        $this->assign('user_id',$user_id);
    }

    public function save(){
        if(IS_POST){
            if(!empty($_POST['user_id'])){
                $save['user_id'] = $_POST['user_id'];
                $data = M('merchant')->where(array('id'=>$_POST['merchant_id']))->save($save);
                if($data){
                    $this->success();
                }else{
                    $this->error();
                }
            }
        }
    }

    /**
     * 检查用户数据是否合法
     */
    public function checkUserMsg(){
        $phone      = $_POST['phone'];
        $password   = $_POST['password'];
        $department_id = $_POST['department_id'];
        $position   = $_POST['position'];
        $is_leader  = $_POST['is_leader'];
        $user_info = M('User')->where(array('phone'=>$phone,'status'=>array('neq',9)))->find();
        if(!$user_info){
            $this->ajaxReturn(array('error'=>'手机号未注册'),'json');
        }
        if($user_info['password']!=md5($password)){
            $this->ajaxReturn(array('error'=>'密码错误'),'json');
        }
        if($user_info['auth_status']!=2){
            $this->ajaxReturn(array('error'=>'该用户还未实名认证'),'json');
        }
        $merchant_bind_user = M('MerchantBindUser')->where(array('user_id'=>$user_info['id']))->find();
        if($merchant_bind_user){
            $this->ajaxReturn(array('error'=>'该手机号已被绑定'),'json');
        }
        $data['merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $data['user_id'] = $user_info['id'];
        $data['department_id'] = $department_id;
        $data['position'] = $position;
        $data['is_leader'] = $is_leader;
        $data['create_time'] = time();
        $res = M('MerchantBindUser')->data($data)->add();
        if($res){
            $this->ajaxReturn(array('success'=>'绑定成功'),'json');
        }else{
            $this->ajaxReturn(array('error'=>'绑定失败','json'));
        }
    }
}
