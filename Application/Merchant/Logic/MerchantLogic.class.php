<?php

namespace Merchant\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 管理员逻辑处理类
 */
class MerchantLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        if(!empty($request['account'])) {
            //按管理员账号查询
            $param['where']['admin.account'] = $request['account'];
        }
        $param['where']['admin.status'] = array('lt',9);//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Merchant')->getList($param);
        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['admin.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }

        $param['where']['admin.status'] = array('lt',9);
        $row = D('Merchant')->findRow($param);

        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }

        return $row;
    }



    /**
     * @param array $data
     * @return array
     * 处理data数据 密码MD5加密 创建唯一标示
     */
    protected function processData($data = array()) {
        if(empty($data['id'])) {
            $data['password'] = MD5(MD5($data['password']));
            $data['unique_code'] = date('YmdHis').get_vc(1,1);
        }
        return $data;
    }


    /**
     * @param array $request
     * @return bool
     * 登录函数
     */
    function login($request = array()) {
        if(empty($request['account'])) {
            $this->setLogicError('请输入登录账号！'); return false;
        }
        if(empty($request['password'])) {
            $this->setLogicError('请输入登录密码！'); return false;
        }
        if(empty($request['verify'])) {
            $this->setLogicError('请输入验证码！'); return false;
        }
        //检测验证码
        if(!check_verify($request['verify'])){
            $this->setLogicError('验证码输入错误！'); return false;
        }

        $param['where']['admin.account'] = $request['account'];
        $param['where']['admin.password'] = MD5($request['password']);

        $admin = D('Merchant')->findRow($param);

        if($admin) {
			//判断该账号是否正常
			if($admin['status'] != 1) {
				$this->setLogicError('登录失败，您的账号已不可用！'); return false;
			}

			//添加日志
            //api('Merchant/ActionLog/actionLog', array('login', 'Merchant', $admin['id'], $admin['id']));
            //登录成功 修改登录信息 设置session
            $this->autoCZ($admin);

            $this->setLogicSuccess('登陆成功！'); return true;
        } else {
            //登录失败
            $this->setLogicError('账号或密码错误！'); return false;
        }
    }

    /**
     * @param $admin
     * 更新登录信息
     * 设置session
     */
    private function autoCZ($admin) {
        /* 更新登录信息 */
        $data = array(
            'model'           => 'Merchant',
            'id'              => $admin['id'],
            'login'           => array('exp', '`login`+1'),
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => get_client_ip(1),
        );
        $this->update($data);

        /* 记录登录SESSION和COOKIES */
        $session = array(
            'a_id'            => $admin['id'],
            'account'         => $admin['account'],
            'range_id'        => $admin['range_id'],
            'last_login_time' => $admin['last_login_time'],
        );

        session('merchant_admin', $session);
        session('merchant_admin_sign', data_auth_sign($session));
    }

    /**
     * @param array $request
     * @return bool|mixed
     * 修改密码
     */
    function rePass($request = array()) {
        if(empty($request['oldpassword'])) {
            $this->setLogicError('请输入原密码！'); return false;
        } if(empty($request['newpassword'])) {
            $this->setLogicError('请输入新密码！'); return false;
        } if(strlen($request['newpassword']) < 5 || strlen($request['newpassword']) > 18) {
            $this->setLogicError('新密码长度在6--18位之间！'); return false;
        } if($request['repassword'] != $request['newpassword']) {
            $this->setLogicError('确认新密码与新密码不一致！'); return false;
        }

        //修改
        $data['password'] = MD5($request['newpassword']);
        $where['id'] = AID;
        $where['password'] = MD5($request['oldpassword']);
        $result = D('Merchant')->where($where)->data($data)->save();

        if($result){
            $this->setLogicSuccess('修改成功！');
        }else{
            $this->setLogicError('原密码不正确！');
        }
        return $result;
    }
}