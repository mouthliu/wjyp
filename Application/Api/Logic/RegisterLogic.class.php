<?php
namespace Api\Logic;

/**
 * Class RegisterLogic
 * @package Api\Logic
 * 逻辑层 登录模块
 *
 */
class RegisterLogic extends BaseLogic{

    /**
     * 发送验证码
     * @param array $request
     *  * 短信类型：type :
     *      注册 activate
     *      忘记密码：retrieve
     *      解绑旧手机号：mod_bind
     *      绑定新手机号：re_bind (三方登录绑定)
     *      重置支付密码：re_pay_pwd
     */
    public function sendVerify($request = array()){

        //注册判断手机号是否已存在
        unset($where);
        $where['phone']  = $request['phone'];
        $where['status'] = array('neq',9);
        $user_id = D('User')->where("phone={$request['phone']}")->getField('id');

        if($user_id && in_array($request['type'],array('activate'))){
             apiResponse('0','该手机号已经被注册');
        }
        if(!$user_id && in_array($request['type'],array('retrieve','mod_bind','re_pay_pwd'))){
            apiResponse('0','该手机号尚未注册');
        }
        //发送短信
        $res = D('Sms')->sendVerify($request['phone'],$request['type']);
        if($res['success']){
            apiResponse('1',$res['success']);
        }else{
            apiResponse('0',$res['error']);
        }
    }

    /**
     * 注册第一步
     * @param array $request
     */
    public function registerOne($request = array()){
        $where['phone'] = $request['phone'];
        $where['status'] = array('neq',9);
        $user_info = M('User')->where($where)->find();
        if($user_info){
            apiResponse('0','该手机号已被注册');
        }else{
            apiResponse('1','验证通过');
        }
    }

    /**
     * 验证短信验证码
     * @param array $request
     */
    public function checkVerify($request = array()){
        $where['way']  = $request['phone'];
        $where['vc']   = $request['verify'];
        $where['type'] = $request['type'];
        $res = M('Sms')->where($where)->find();

        if(!$res){
            apiResponse('0','验证码错误');
        }
        //判断验证码是否过期
        if($res['expire_time'] < time()){
            apiResponse('0','验证码已失效');
        }
        return true;
//        apiResponse('1','验证通过');
    }

    /**
     * 内部方法 检查手机号是否已经被注册
     * @param $phone
     * @return string
     */
    public function _checkIsRegister($phone){
        $where['phone'] = $phone;
        $where['status'] = array('neq',9);
        $count= M('User')->where($where)->count();
        if($count>0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 内部方法 生成唯一邀请码
     */
    public function _createInviteCode(){
        $invite_code = get_vc(8,0);
        while(1){
            $count = M('User')->where(array('invite_code'=>$invite_code,'status'=>array('neq',9)))->count();
            if($count>0){
                $invite_code = get_vc(8,0);
            }else{
                break;
            }
        }
        return $invite_code;
    }

    /**
     * 创建环信账户
     */
    public function _createEasemob(){
        $option['username'] = time().rand(10000,99999).'';
        $option['password'] = time().'';
        while(1){
            $res = D('Easemob','Service')->createUser($option);
            if(empty($res['error'])){
                break;
            }
        }
        return $option;
    }

    /**
     * 内部方法，生成token
     * @return  string
     */
    public function _createToken(){
        $token = time().rand(10000,99999);
        return $token;
    }

    /**
     * 内部方法，验证token有效性
     */
    public function _checkLogin(){
        $user = M('User')->where(array('token'=>$_SERVER['HTTP_TOKEN']))->find();
        if(!$user){
            apiResponse('-1','该账号已在其他设备登录');
        }
        if($user['expired_time']<time()){
            apiResponse('-1','登录已过期，请重新登录');
        }
    }

    /**
     * 注册
     * @param array $request
     */
    public function register($request = array()){
        //判断手机号是否被注册
        if($this->_checkIsRegister($request['phone'])){
            apiResponse('0','该手机号已被注册');
        }
        //注册环信
        $option = $this->_createEasemob();
        $data['easemob_account'] = $option['username'];
        $data['easemob_pwd'] = $option['password'];
        //处理会员注册数据
        $data['phone'] = $request['phone'];
        $data['password'] = md5($request['password']);
        $data['nickname'] = '无界新人';
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['auth_status'] = 0; //认证状态设为0
        $data['invite_code'] = $this->_createInviteCode(); //邀请码
        $data['token'] = $this->_createToken();
        $data['expired_time'] = time()+(60*60*24*7);//有效期设置为一周

        //获取推荐人，隐藏推荐人  和联盟商家
        $config = D('Config')->parseList();
        $default_recommend_id = M('User')->where(array('phone'=>$config['default_recommend_phone']))->getField('id');//获取默认推荐人ID
        $default_parent_id    = M('User')->where(array('phone'=>$config['default_parent_phone']))->getField('id');//获取隐藏推荐人
        $default_alliance_merchant_id = M('Merchant')->where(array('alliance_merchant_sn'=>$config['default_alliance_merchant_sn']))->getField('id');
        if(!empty($request['invite_code'])){
            //根据邀请码获取到推荐人id
            $i_where['invite_code'] = $request['invite_code'];
            $parent_id = M('User')->where($i_where)->getField('id');
            //获取到该用户的推荐人
            $hidden_parent_id = M('User')->where("id={$parent_id}")->getField('parent_id');

            $data['parent_alliance_merchant_id'] = $default_alliance_merchant_id?$default_alliance_merchant_id:0;

            $data['parent_id'] = $parent_id?$parent_id:0;
            $data['hidden_parent_id'] = $hidden_parent_id?$hidden_parent_id:0;
        }else{
            $data['parent_alliance_merchant_id'] = $default_alliance_merchant_id?$default_alliance_merchant_id:0;
            $data['parent_id'] = $default_recommend_id?$default_recommend_id:0;
            $data['hidden_parent_id'] = $default_parent_id?$default_parent_id:0;
        }
        //执行注册
        $id = M('User')->data($data)->add();
        if(!$id){
            apiResponse('0','注册失败');
        }
        //注册成功后的操作(赠送积分啥的)

        //返回数据
        $user_info = M('User')->field("nickname,head_pic,easemob_account,easemob_pwd,integral,balance,ticket_num,token,expired_time,rank_id,grow_point,auth_status")->where("id={$id}")->find();
        if(!$user_info['head_pic']){
            $user_info['head_pic'] = C('API_URL').'Uploads/User/default.png';
        }
        //获取重置等级
        $user_info['rank'] = getName('UserRank','rank_name',$user_info['rank_id']);
        $user_info['rank_icon'] = D('File')->getOneFilePath(getName('UserRank','icon',$user_info['rank_id']));
        $l_where['min_point'] = array('nlt',$user_info['grow_point']);
        $l_where['min_point'] = array('gt',$user_info['grow_point']);
        $l_where['status'] = 1;
        $lev = M('UserLevel')->field('level_name,icon')->where($l_where)->find();
        $user_info['level'] = $lev ? $lev['level_name']:'';
        $user_info['level_icon'] = $lev ? D('File')->getOneFilePath($lev['icon']):'';

        //获取客服电话
        $config = D('Config')->parseList();
        $user_info['server_line'] = $config['SERVICE_LINE']?$config['SERVICE_LINE']:'';
        //获取未读消息
        $user_info['new_msg'] = D('UserMessage','Logic')->getTips($id);
        apiResponse('1','注册成功',$user_info);
    }


    /**
     * 登录函数
     * @param array $request
     */
    public function login($request = array()){
        $mod = D('User');
        $where['phone'] = $request['phone'];
        $where['password'] = MD5($request['password']);
        $uInfo = $mod
            ->field("id AS user_id,phone,easemob_account,easemob_pwd,balance,auth_status,head_pic,nickname,integral,ticket_num,token,status,rank_id,grow_point,auth_status")
            ->where($where)
            ->find();

        if($uInfo) {
            //判断该账号是否正常
            if($uInfo['status'] != 1) {
                apiResponse('0','登录失败，您的账号已不可用！');
            }
            /* 登录成功 更新登录信息 */
            $token = $this->_createToken();
            $data = array(
                'id'                 => $uInfo['user_id'],
                'last_login_time' => time(),
                'last_login_ip'   => get_client_ip(1),
                'token'              => $token,
                'expired_time'       => time() + (60 * 60 * 24 * 7),
            );
            $res = $mod->where("id={$uInfo['user_id']}")->save($data);
            //设置返回的数据
            $info = $uInfo;
            //获取重置等级
            $info['rank'] = getName('UserRank','rank_name',$info['rank_id']);
            $info['rank_icon'] = D('File')->getOneFilePath(getName('UserRank','icon',$info['rank_id']));
            $l_where['min_point'] = array('nlt',$info['grow_point']);
            $l_where['min_point'] = array('gt',$info['grow_point']);
            $l_where['status'] = 1;
            $lev = M('UserLevel')->field('level_name,icon')->where($l_where)->find();
            $info['level'] = $lev ? $lev['level_name']:'';
            $info['level_icon'] = $lev ? D('File')->getOneFilePath($lev['icon']):'';
            $info['token'] = $token;
            $info['expired_time'] = $data['expired_time'];
            $info['head_pic'] = D('File')->getOneFilePath($info['head_pic']);
            unset($info['status']);
            //获取未读消息
            $info['new_msg'] = D('UserMessage','Logic')->getTips($info['user_id']);
            //获取客服电话
            $config = D('Config')->parseList();
            $info['server_line'] = $config['SERVICE_LINE']?$config['SERVICE_LINE']:'';
            apiResponse('1','登录成功',$info);
        } else {
            //登录失败 , 判断手机号是否注册
            $id = M('User')->where("phone = {$request['phone']}")->getField('id');
            if(!$id){
                apiResponse('0','手机号未注册');
            }else{
                apiResponse('0','密码错误');
            }

        }
    }


    /**
     * 忘记密码
     * @param array $request
     */
    public function resetPassword($request = array()){
            //根据手机号发送短信
            $mod = D('User');
            $where['phone'] = $request['phone'];
            //检测验证码
            $this->checkVerify(array('phone'=>$request['phone'],'verify'=>$request['verify'],'type'=>'retrieve'));
            //进行重置密码
            $id = $mod->where($where)->getField('id');
            if(!$id){
                apiResponse('0','手机号有误');
            }
            $data['password'] = md5($request['newPassword']);
            $data['update_time'] = time();
            $res = $mod->where("id={$id}")->save($data);
            if($res){
                apiResponse('1','找回密码成功');
            }else{
                apiResponse('0','找回密码失败');
            }
    }

    /**
     * 三方登录
     * @param array $request
     */
    public function otherLogin($request = array()){
        $where['openid'] = $request['openid'];
        $where['type'] = $request['type'];
        $where['status'] = 1;
        //查找user_bind表中是否存在
        $res = M('UserBind')->where($where)->find();
        if($res){
            //说明登录过(判断是否绑定用户)
            if($res['user_id']){
                //已经绑定用户，直接登录
                $uInfo = M('User')
                    ->field("id AS user_id,phone,easemob_account,easemob_pwd,balance,auth_status,head_pic,nickname,integral,ticket_num,token,status,rank_id,grow_point,auth_status")
                    ->where("id={$res['user_id']}")
                    ->find();
                if($uInfo) {
                    //判断该账号是否正常
                    if($uInfo['status'] != 1) {
                        apiResponse('0','登录失败，您的账号已不可用！');
                    }
                    /* 登录成功 更新登录信息 */
                    $token = $this->_createToken();
                    $data = array(
                        'id'                 => $uInfo['user_id'],
                        'last_login_time' => time(),
                        'last_login_ip'   => get_client_ip(1),
                        'token'              => $token,
                        'expired_time'       => time() + (60 * 60 * 24 * 7),
                    );
                    D('User')->where("id={$uInfo['user_id']}")->save($data);
                    //设置返回的数据
                    $info = $uInfo;
                    $info['token'] = $token;
                    $info['expired_time'] = $data['expired_time'];
                    $info['head_pic'] = D('File')->getOneFilePath($info['head_pic']);
                    unset($info['status']);
                    //设置返回的数据
                    if(!$info['head_pic']){
                        $info['head_pic'] = C('API_URL').'Uploads/User/default.png';
                    }
                    //获取重置等级
                    $info['rank'] = getName('UserRank','rank_name',$info['rank_id']);
                    $info['rank_icon'] = D('File')->getOneFilePath(getName('UserRank','icon',$info['rank_id']));
                    $l_where['min_point'] = array('nlt',$info['grow_point']);
                    $l_where['min_point'] = array('gt',$info['grow_point']);
                    $l_where['status'] = 1;
                    $lev = M('UserLevel')->field('level_name,icon')->where($l_where)->find();
                    $info['level'] = $lev ? $lev['level_name']:'';
                    $info['level_icon'] = $lev ? D('File')->getOneFilePath($lev['icon']):'';
                    //获取客服电话
                    $config = D('Config')->parseList();
                    $info['server_line'] = $config['SERVICE_LINE']?$config['SERVICE_LINE']:'';
                    //获取未读消息
                    $info['new_msg'] = D('UserMessage','Logic')->getTips($info['id']);
                    $info['is_bind_phone'] = '1';
                    $info['bind_id'] = $res['id'];
                    apiResponse('1','登录成功',$info);
                 }else{
                    apiResponse('0','三方登录失败');
                }
            }else{
                //登录过，但是没有绑定用户
                apiResponse('1','三方登录成功',array('is_bind_phone'=>'0','bind_id'=>$res['id']));
            }
        }else{
            //说明没有进行过三方登录 创建新数据在user_bind
            $data['openid'] = $request['openid'];
            $data['type'] = $request['type'];
            $data['nickname'] = $request['nickname']?$request['nickname']:'';
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['status'] = 1;
            //判断头像
            if(!empty($_FILES['head_pic']['name'])){
                $file = api('UploadPic/upload', array(array('save_path' => 'User')));
                foreach ($file as $v) {
                    $data['head_pic'] = $v['id'];
                }
            }
            $bind_id = M('UserBind')->add($data);
            if(!$bind_id){
                apiResponse('0','三方登录失败');
            }
            apiResponse('1','三方登录成功',array('is_bind_phone'=>'0','bind_id'=>$bind_id));
        }
    }

    /**
     * 三方登录绑定手机
     */
    public function otherLoginBind($request = array()){
        $this->checkVerify(array('phone'=>$request['phone'],'verify'=>$request['verify'],'type'=>'re_bind'));
        $bind_info =    M('UserBind')->where("id={$request['bind_id']} AND status=1")->find();
        if(!$bind_info){
            apiResponse('0','查询信息失败，绑定失败');
        }
        //获取推荐人，隐藏推荐人  和联盟商家
        $config = D('Config')->parseList();
        $default_recommend_id = M('User')->where(array('phone'=>$config['default_recommend_phone']))->getField('id');//获取默认推荐人ID
        $default_parent_id    = M('User')->where(array('phone'=>$config['default_parent_phone']))->getField('id');//获取隐藏推荐人
        $default_alliance_merchant_id = M('Merchant')->where(array('alliance_merchant_sn'=>$config['default_alliance_merchant_sn']))->getField('id');
        if(!empty($request['invite_code'])){
            //根据邀请码获取到推荐人id
            $i_where['invite_code'] = $request['invite_code'];
            $parent_id = M('User')->where($i_where)->getField('id');
            //获取到该用户的推荐人
            $hidden_parent_id = M('User')->where("id={$parent_id}")->getField('parent_id');

            $data['parent_alliance_merchant_id'] = $default_alliance_merchant_id?$default_alliance_merchant_id:0;

            $data['parent_id'] = $parent_id?$parent_id:0;
            $data['hidden_parent_id'] = $hidden_parent_id?$hidden_parent_id:0;
        }else{
            $data['parent_alliance_merchant_id'] = $default_alliance_merchant_id?$default_alliance_merchant_id:0;
            $data['parent_id'] = $default_recommend_id?$default_recommend_id:0;
            $data['hidden_parent_id'] = $default_parent_id?$default_parent_id:0;
        }
        
        $sel_where['phone'] = $request['phone'];
        $user_info = M('User')->where($sel_where)->find();
        if($user_info){
            //说明用户存在
            $user_id = $user_info['id'];
            $res = M('UserBind')->where("id={$request['bind_id']} AND status=1")->data(array("user_id"=>$user_id))->save();
            if(!$res){
                apiResponse('0','绑定手机失败');
            }
        }else{
            //用户不存在（获取到绑定表信息添加,进行注册添加）
            $data['phone'] = $request['phone'];
            $data['head_pic'] = $bind_info['head_pic']?$bind_info['head_pic']:'0';
            $data['nickname'] = $bind_info['nickname']?$bind_info['nickname']:'无界新人';
            //注册环信
            $option = $this->_createEasemob();
            $data['easemob_account'] = $option['username'];
            $data['easemob_pwd'] = $option['password'];
            //处理会员注册数据
            $data['create_time'] = time();
            $data['update_time'] = time();
            $data['auth_status'] = 0; //认证状态设为0
            $data['invite_code'] = $this->_createInviteCode(); //邀请码
            $data['token'] = $this->_createToken();
            $data['expired_time'] = time()+(60*60*24*7);//有效期设置为一周
            $user_id = D('User')->data($data)->add();
            if(!$user_id){
                apiResponse('0','绑定手机失败');
            }
            //成功后将会员ID 加到绑定表中
            $res = M('UserBind')->where("id={$request['bind_id']} AND status=1")->data(array("user_id"=>$user_id))->save();
        }
        //查询出用户的信息
        $user_info = M('User')
            ->field("id AS user_id,phone,easemob_account,easemob_pwd,balance,auth_status,head_pic,nickname,integral,ticket_num,token,expired_time,rank_id,grow_point,auth_status")
            ->where("id={$user_id}")->find();
        //设置返回的数据
        if(!$user_info['head_pic']){
            $user_info['head_pic'] = C('API_URL').'Uploads/User/default.png';
        }
        //获取重置等级
        $user_info['rank'] = getName('UserRank','rank_name',$user_info['rank_id']);
        $user_info['rank_icon'] = D('File')->getOneFilePath(getName('UserRank','icon',$user_info['rank_id']));
        $l_where['min_point'] = array('nlt',$user_info['grow_point']);
        $l_where['min_point'] = array('gt',$user_info['grow_point']);
        $l_where['status'] = 1;
        $lev = M('UserLevel')->field('level_name,icon')->where($l_where)->find();
        $user_info['level'] = $lev ? $lev['level_name']:'';
        $user_info['level_icon'] = $lev ? D('File')->getOneFilePath($lev['icon']):'';
        //获取客服电话
        $config = D('Config')->parseList();
        $user_info['server_line'] = $config['SERVICE_LINE']?$config['SERVICE_LINE']:'';
        //获取未读消息
        $user_info['new_msg'] = D('UserMessage','Logic')->getTips($user_id);
        apiResponse('1','登录成功',$user_info);
    }

    /**
     * 扫码注册
     * 手机号
     * 密码
     * 验证码
     * 邀请码
     */
    public function scanRegister($request = array()){
        //判断手机号是否被注册
        if($this->_checkIsRegister($request['phone'])){
            apiResponse('0','该手机号已被注册');
        }
        //检测验证码
        $this->checkVerify(array('phone'=>$request['phone'],'verify'=>$request['verify'],'type'=>'activate'));
        //注册环信
        $option = $this->_createEasemob();
        $data['easemob_account'] = $option['username'];
        $data['easemob_pwd'] = $option['password'];
        //处理会员注册数据
        $data['phone'] = $request['phone'];
        $data['password'] = md5($request['password']);
        $data['nickname'] = '无界新人';
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['auth_status'] = 0; //认证状态设为0
        $data['invite_code'] = $this->_createToken();

        $data['token'] = $this->_createToken();
        $data['expired_time'] = time()+(60*60*24*7);//有效期设置为一周
        //从后台config表中拿到这个推荐信息
        $config = D('Config')->parseList();
        $config_parent_id = $config['default_parent_id'];
        $config_parent_league_id = $config['default_parent_league_id'];
        $data['hidden_parent_id'] = $config_parent_id.'';
        $data['parent_league_id'] = $config_parent_league_id.'';
        //判断是否是推荐注册
        if(!empty($request['invite_code'])){
            //根据邀请码获取到推荐人id
            $i_where['invite_code'] = $request['invite_code'];
            $u_id = M('User')->where($i_where)->getField('id');
            //获取到该用户的推荐人
            $parent_id = M('User')->where("id={$u_id}")->getField('parent_id');
            //获取到推荐商家联盟的推荐人
            $parent_league_id = M('User')->where("id={$u_id}")->getField('parent_league_id');
            //设置注册信息
            $data['parent_id'] = $u_id ? $u_id.'' : $parent_id.'';
            $data['hidden_parent_id'] = $parent_id ? $parent_id.'' : $config_parent_id.'';
            $data['parent_league_id'] = $parent_league_id ? $parent_league_id.'' : $config_parent_league_id.'';
        }
        //执行注册
        $id = M('User')->data($data)->add();
        if(!$id){
            apiResponse('0','注册失败');
        }
        //注册成功后的操作(赠送积分啥的)

        //返回数据
        $user_info = M('User')->field("nickname,head_pic,easemob_account,easemob_pwd,integral,balance,ticket_num,token,expired_time,rank_id,grow_point,auth_status")->where("id={$id}")->find();
        if(!$user_info['head_pic']){
            $user_info['head_pic'] = C('API_URL').'Uploads/User/default.png';
        }
        //获取重置等级
        $user_info['rank'] = getName('UserRank','rank_name',$user_info['rank_id']);
        $user_info['rank_icon'] = D('File')->getOneFilePath(getName('UserRank','icon',$user_info['rank_id']));
        $l_where['min_point'] = array('nlt',$user_info['grow_point']);
        $l_where['min_point'] = array('gt',$user_info['grow_point']);
        $l_where['status'] = 1;
        $lev = M('UserLevel')->field('level_name,icon')->where($l_where)->find();
        $user_info['level'] = $lev ? $lev['level_name']:'';
        $user_info['level_icon'] = $lev ? D('File')->getOneFilePath($lev['icon']):'';

        //获取客服电话
        $config = D('Config')->parseList();
        $user_info['server_line'] = $config['SERVICE_LINE']?$config['SERVICE_LINE']:'';
        //获取未读消息
        $user_info['new_msg'] = D('UserMessage','Logic')->getTips($id);
        apiResponse('1','注册成功',$user_info);
    }




}