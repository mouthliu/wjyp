<?php
namespace Api\Logic;

/**
 * Class UserLogic
 * @package Api\Logic
 * 逻辑层  会员模块
 *
 */
class UserLogic extends BaseLogic{

    /**
     * 个人中心
     * @param int $user_id
     */
    public  function userCenter($user_id = 0){
        //返回数据
        $user_info = M('User')
            ->field("nickname,head_pic,easemob_account,easemob_pwd,integral,balance,ticket_num,invite_code,token,expired_time,rank_id,grow_point,auth_status,is_gold,is_silver,is_copper,is_masonry,is_iron")
            ->where("id={$user_id}")
            ->find();
        if(empty($user_info)){
            apiResponse('0','用户信息查询失败');
        }
        $user_info['invite_code'] = C('API_URL').'/index.php/Api/Register/scanPage/invite_code/'.$user_info['invite_code'];
        $all_voucher = M('Vouchers')->where("user_id = {$user_id} AND status=1")->sum('money');
        $user_info['ticket_num'] = $all_voucher ? $all_voucher : '0.00';
        $user_info['head_pic'] = D('File')->getOneFilePath($user_info['head_pic']);
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
        //获取环信客服的账号信息
        $customer_service_id = $config['customer_service_id'];
        $customer_service_info = M('User')->where(array('id'=>$customer_service_id))->find();
        $user_info['service_easemob_account'] = $customer_service_info['easemob_account']?$customer_service_info['easemob_account']:'';
        $user_info['service_nickname'] = $customer_service_info['nickname']?$customer_service_info['nickname']:'无界优品客服';
        if($customer_service_info){
            $path = M('File')->where(array('id'=>$customer_service_info['head_pic']))->getField('path');
            $user_info['service_head_pic'] =  $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
        } else{
            $user_info['service_head_pic'] = C('API_URL').'/Uploads/Member/default.png';
        }
        
       //获取未读消息
        $user_info['new_msg'] = D('UserMessage','Logic')->getTips($user_id);
        apiResponse('1','请求成功',$user_info);
    }

    /**
     * 设置
     * @param int $user_id
     */
    public  function setting($user_id = 0){
        $user_info = M('User')
            ->field('id AS user_id,auth_status,phone,password,pay_password,comp_auth_status')
            ->where("id={$user_id}")
            ->find();
        if(!$user_info){
            apiResponse('0','获取资料失败');
        }
        $user_info['is_password'] = $user_info['password']?'1':'0';
        $user_info['is_pay_password'] = $user_info['pay_password']?'1':'0';
        unset($user_info['password']);
        unset($user_info['pay_password']);
        //查询绑定的第三方
        $bind_list = M('UserBind')->where("user_id = {$user_id} AND status=1")->select();
        $user_info['qq_bind']['is_bind'] = '0';
        $user_info['qq_bind']['bind_info'] = array('bind_id'=>'0','nickname'=>'');
        $user_info['wx_bind']['is_bind'] = '0';
        $user_info['wx_bind']['bind_info'] = array('bind_id'=>'0','nickname'=>'');
        $user_info['weibo_bind']['is_bind'] = '0';
        $user_info['weibo_bind']['bind_info'] = array('bind_id'=>'0','nickname'=>'');
        if($bind_list){
            foreach($bind_list as $k=>$v){
                $nickname = $v['nickname'] ? $v['nickname'] : '已绑定';
                if($v['type'] == '3'){
                    $user_info['qq_bind']['is_bind'] = '1';
                    $user_info['qq_bind']['bind_info'] = array('bind_id'=>$v['id'],'nickname'=>$nickname);
                }elseif($v['type'] == '1'){
                    $user_info['wx_bind']['is_bind'] = '1';
                    $user_info['wx_bind']['bind_info'] = array('bind_id'=>$v['id'],'nickname'=>$nickname);
                }elseif($v['type'] == '2'){
                    $user_info['weibo_bind']['is_bind'] = '1';
                    $user_info['weibo_bind']['bind_info'] = array('bind_id'=>$v['id'],'nickname'=>$nickname);
                }
            }
        }
        apiResponse('1','获取信息成功',$user_info);
    }
    /**
     * 绑定第三方接口
     * open_id
     * type
     */
    public function bindOther($request = array(),$user_id){
        $arr = array('1'=>'微信','2'=>'微博','3'=>'QQ');
        //先判断是否绑定过
        $where['openid'] = $request['openid'];
        $where['type'] = $request['type'];
        $where['status'] = 1;
        //查找user_bind表中是否存在
        $res = M('UserBind')->where($where)->find();
        if($res){
            apiResponse('0','该'.$arr[$request['type']].'已被绑定');
        }
        //说明没有进行过三方登录 创建新数据在user_bind
        $data['openid'] = $request['openid'];
        $data['type'] = $request['type'];
        $data['nickname'] = $request['nickname']?$request['nickname']:'';
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['user_id'] = $user_id;
        $data['status'] = 1;
        $bind_id = M('UserBind')->add($data);

        if($bind_id){
            apiResponse('1','绑定'.$arr[$request['type']].'成功',array('bind_id'=>$bind_id));
        }else{
            apiResponse('0','绑定'.$arr[$request['type']].'失败');
        }
    }
    /**
     * 解除第三方
     * 传过来会员id 和 绑定类型
     */
    public function removeBind($bind_type,$user_id){
        $mod = M('UserBind');
        $where['user_id'] = $user_id;
        $where['type'] = $bind_type;
        $where['status'] = 1;
        $bind_id = $mod->where($where)->getField('id');
        if(!$bind_id){
            apiResponse('0','未查到此绑定信息');
        }
        $res = $mod->where("id={$bind_id}")->delete();
        if(!$res){
            apiResponse('0','解除绑定失败');
        }
        apiResponse('1','解除绑定成功');
    }
    /**
     * 会员个人资料
     * @param int $user_id
     */
    public function userInfo($user_id = 0){
        $mod         = D('User');
        $where['id'] = $user_id;
        $user_info    = $mod->field("id AS user_id,nickname,head_pic,real_name,id_card_num,sex,email,province_id,city_id,area_id,street_id,parent_id,auth_status,parent_alliance_merchant_id,hidden_parent_id,auth_status,comp_auth_status")
            ->where($where)
            ->find();
        if(!$user_info){
            apiResponse('0','个人资料暂无数据');
        }
        if($user_info['auth_status']==2) {
            $user_info['real_name'] = $user_info['real_name'] ? $user_info['real_name'] :'请先去认证';
            $user_info['id_card_num'] = $user_info['id_card_num'] ? $user_info['id_card_num'] :'请先去认证';
            if($user_info['sex']==0){
                $user_info['sex'] = '请先去认证';
            }else if($user_info['sex']==1){
                $user_info['sex'] = '男';
            }else{
                $user_info['sex'] = '女';
            }
        }else{
            $user_info['real_name'] = '请先去认证';
            $user_info['id_card_num'] = '请先去认证';
            $user_info['sex'] = '请先去认证';
        }
        $path = M('File')->where(array('id'=>$user_info['head_pic']))->getField('path');
        $user_info['head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/User/default.png';

        //获取到推荐人信息
        $refer = $mod->field('nickname,phone')->where("id={$user_info['parent_id']}")->find();
        $user_info['parent_name'] = $refer['nickname']?$refer['nickname']:'暂无';
        $user_info['parent_phone'] = $refer['phone']?$refer['phone']:'暂无';
        //update by chenml 2017-08-22 start
        $province_name = M('Region')->where(array('id' => $user_info['province_id']))->getField('region_name');
        $city_name     = M('Region')->where(array('id' => $user_info['city_id']))->getField('region_name');
        $area_name     = M('Region')->where(array('id' => $user_info['area_id']))->getField('region_name');
        $street_name   = M('Street')->where(array('street_id' => $user_info['street_id']))->getField('street_name');
        $user_info['province_name'] = $province_name ? $province_name : '';
        $user_info['city_name'] = $city_name ? $city_name : '';
        $user_info['area_name'] = $area_name ? $area_name : '';
        $user_info['street_name'] = $street_name ? $street_name : '';
        //update by chenml 2017-08-22 end

        //update by chenml 2017-11-09 start
        //获取所属联盟商家名称和编号
        if($user_info['parent_alliance_merchant_id']){
            $parent_alliance_merchant_info = M('Merchant')->where(array('id'=>$user_info['parent_alliance_merchant_id']))->find();
            $user_info['parent_alliance_merchant_name'] = $parent_alliance_merchant_info['merchant_name']?$parent_alliance_merchant_info['merchant_name']:'暂无';
            $user_info['parent_alliance_merchant_sn'] = $parent_alliance_merchant_info['merchant_name']?$parent_alliance_merchant_info['merchant_name']:'暂无';
        }else{
            $user_info['parent_alliance_merchant_name'] = '暂无';
            $user_info['parent_alliance_merchant_sn'] = '暂无';
        }

        if($user_info['hidden_parent_id']){
            $hidden_parent_info = M('User')->where(array('id'=>$user_info['hidden_parent_id']))->find();
            $user_info['hidden_parent_name'] = $hidden_parent_info['real_name']?$this->addStringStar($hidden_parent_info['real_name']):'暂无';
            $user_info['hidden_parent_phone'] = $hidden_parent_info['phone']?$this->addStringStar($hidden_parent_info['phone']):'暂无';
        }else{
            $user_info['hidden_parent_name'] = '暂无';
            $user_info['hidden_parent_phone'] = '暂无';
        }
        //update by chenml 2017-11-09 end

        apiResponse('1','获取个人资料成功',$user_info);
    }
    public function addStringStar($name){
        return mb_strlen($name,"UTF8") <=2 ? mb_substr($name,0,1,"UTF8") . "*": mb_substr($name,0,1,"UTF8") . "**" . mb_substr($name,mb_strlen($name,"UTF8") - 1,1,"UTF8");
    }
    /**
     * 编辑个人资料
     * @param array $request
     * @param int $user_id
     */
    public function editInfo($request= array(),$user_id = 0){
        $mod = D('User');
        //处理头像
        if(!empty($_FILES['head_pic']['name'])){
            $res = api('UploadPic/upload', array(array('save_path' => 'User')));
            foreach ($res as $value) {
                $head_pic = $value['id'];
                $data['head_pic'] = $value['id'];
            }
        }else{
            $head_pic = $mod->where("id={$user_id}")->getField('head_pic');
        }
        $data['nickname']    = $request['nickname'];
//        $data['sex']         = $request['sex'];
        $data['email']       = $request['email'] ? $request['email'] : '';
        $data['province_id'] = $request['province_id'] ? $request['province_id'] : '0';
        $data['city_id']     = $request['city_id'] ? $request['city_id'] : '0';
        $data['area_id']     = $request['area_id'] ? $request['area_id'] : '0';
        $data['street_id']   = $request['street_id'] ? $request['street_id'] : '0';
        $data['id']          = $user_id;
        $data['update_time'] = time();
        //将数据进行验证
        $mod->checkCreate($data);
        $res = $mod->where("id={$user_id}")->save($data);

        if($res){
            //修改成功,将头像数据返回
            $path = M('File')->where(array('id'=>$head_pic))->getField('path');
            $head_pic = $path?C('API_URL').$path:C('API_URL').'/Uploads/User/default.png';
            apiResponse('1','修改成功',array('head_pic'=>$head_pic));
        }else{
            apiResponse('0','修改失败');
        }
    }
    /**
     * 获取认证信息
     */
    public function getAuth($user_id){
        $auth_info = M('User')->field('real_name,id_card_num,id_card_pic,auth_status')
            ->where("id={$user_id}")
            ->find();
        if(!$auth_info){
            apiResponse('0','获取失败');
        }
        $path = M('File')->where(array('id'=>$auth_info['id_card_pic']))->getField('path');
        $auth_info['id_card_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/User/id_card_default.png';
            apiResponse('1','获取成功',$auth_info);
    }
    /**
     * 添加认证信息
     * @param array $request
     * @param int $user_id
     */
    public function addAuth($request = array(), $user_id = 0){
        $mod = D('User');
        //判断身份或者能好有没有被认证
        $h = $mod->where("id_card_num = '{$request['id_card_num']}' AND status=2")->getField('id');
        if($h){
            apiResponse('0','此身份证已被注册');
        }
        $data['real_name'] = $request['real_name'];
        $data['id_card_num'] = $request['id_card_num'];
        //身份证头像处理
        $res = api('UploadPic/upload', array(array('save_path' => 'User')));
        foreach ($res as $value) {
            $data['id_card_pic'] = $value['id'];
        }
        $data['auth_status'] = 1; //认证状态设置为认证审核中
        $data['id'] = $user_id;
        $data['update_time']  = time();
        //执行验证
        $mod->checkCreate($data);
        $res = $mod->where("id={$user_id}")->save();
        if($res){
            apiResponse('1','认证审核中，请稍等');
        }else{
            apiResponse('0','认证失败,请确保信息的合法性');
        }
    }

    /**
     * 设置密码
     * @param array $request
     * @param int $user_id
     */
    public function setPassword($request = array(),$user_id = 0){
        $mod = D('User');
        $where['id'] = $user_id;
        $data['password']    = md5($request['newPassword']);
        $data['update_time'] = time();
        $res = $mod->where($where)->save($data);
        if($res){
            apiResponse('1','设置密码成功');
        }else{
            apiResponse('0','设置密码失败');
        }
    }


    /**
     * 修改登录密码函数
     * @param array $request
     * @param $user_id
     *
     */
    public function changePassword($request = array(),$user_id = 0){
        $mod = D('User');
        $where['id'] = $user_id;
        //根据id判断是否有密码
        $oldPassword = $mod->where($where)->getField('password');
        if(!empty($oldPassword)){
            if($oldPassword != md5($request['oldPassword'])){
                apiResponse('0','原密码不正确');
            }
        }
        $data['password'] = md5($request['rePassword']);
        $data['update_time']  = time();
        $res = $mod->where($where)->save($data);
        if($res){
            if(empty($oldPassword)){
                apiResponse('1','密码设置成功');
            }else{
                apiResponse('1','密码修改成功');
            }

        }else{
            apiResponse('0','密码修改失败');
        }
    }

    /**
     * 设置支付密码
     * @param array $request
     * @param $user_id
     */
    public function setPayPwd($request = array(),$user_id = 0){
        $mod = D('User');
        $where['id'] = $user_id;

        $data['pay_password'] = md5($request['newPayPwd']);
        $data['update_time']  = time();
        $res = $mod->where($where)->save($data);
        if($res){
            apiResponse('1','支付密码设置成功,请妥善保管');

        }else{
            apiResponse('0','支付密码设置失败');
        }
    }


    /**
     * 修改支付密码函数
     * @param array $request
     * @param $user_id
     */
    public function rePayPwd($request = array(),$user_id = 0){
        $mod = D('User');
        $where['id'] = $user_id;
        //根据id判断是否有密码
        $oldPayPwd = $mod->where($where)->getField('pay_password');
        if(!empty($oldPayPwd)){
            if($oldPayPwd != md5($request['oldPayPwd'])){
                apiResponse('0','原支付密码不正确');
            }
        }
        $data['pay_password'] = md5($request['rePayPwd']);
        $data['update_time'] = time();
        $res = $mod->where($where)->save($data);
        if($res){
            if(!empty($oldPayPwd)){
                apiResponse('1','支付密码修改成功');
            }else{
                apiResponse('1','支付密码设置成功,请妥善保管');
            }

        }else{
            apiResponse('0','支付密码修改失败');
        }
    }
    /**
     * 短信重置支付密码
     * @param array $request
     */
    public function resetPayPwd($request = array(),$user_id = 0){
        $mod = D('User');
        //检测验证码
        D('Register','Logic')->checkVerify(array('phone'=>$request['phone'],'verify'=>$request['verify'],'type'=>'re_pay_pwd'));
        $where['id'] = $user_id;
        //  这里可以加验证
        $data['pay_password'] = md5($request['rePayPwd']);
        $data['update_time']  = time();
        $res = $mod->where($where)->save($data);
        if($res){
            apiResponse('1','重置支付密码成功');
        }else{
            apiResponse('0','重置支付密码失败');
        }
    }
    /**
     * 更换绑定手机操作
     * @param array $request
     */
    public function changePhone($request = array(),$user_id){
        $mod = D('User');
        //判断新手机是否注册过
        $res = $mod->where(array('phone'=>$request['newPhone']))->getField('id');
        if($res){
            apiResponse('0','此手机号已被绑定过');
        }
        //检测验证码
        D('Register','Logic')->checkVerify(array('phone'=>$request['newPhone'],'verify'=>$request['verify'],'type'=>'re_bind'));
        $where['phone'] = $request['newPhone'];

        $res = $mod->where("id={$user_id}")->save(array('phone'=>$request['newPhone'],'update_time'=>time()));
        if($res){
            apiResponse('1','更换手机成功');
        }else{
            apiResponse('0','更换手机失败');
        }
    }
    /**
     * 我的优惠券
     * @param array $request
     * @param int $user_id
     */
    public function myTicket($request= array(),$user_id = 0){
        $mod = M('UserTicket');
        $where['u.status'] = 0;
        $where['u.user_id'] = $user_id;
        $list = $mod->alias('u')
            ->field('u.id AS userTicket_id,u.user_id,t.ticket_name,t.ticket_desc,t.ticket_type,t.value,t.condition,t.merchant_id,t.end_time,t.status')
            ->where($where)
            ->join(C('DB_PREFIX').'ticket t ON t.id=u.ticket_id')
            ->order('u.add_time DESC')
            ->select();
        if(!$list){
            apiResponse('0','暂无数据');
        }
        $count = $mod->alias('u')->where($where)->count();
        foreach($list as $k=>$v){
            $picture = D('File')->getOneFilePath(getName('Merchant','logo',$v['merchant_id']));
            $v['picture'] = $picture?$picture:C('API_URL').'/Uploads/Ticket/default.png';
            if(($v['end_time'] > time()) && ($v['status'] == '1')){
                unset($v['status']);
                $new_list['normal'][] = $v;
            }else{
                unset($v['status']);
                $new_list['out'][] = $v;
            }
        }
        apiResponse('1','获取成功',$new_list,$count);
    }

    /**
     * 我的足迹
     * @param $user_id
     */
    public function myfooter($request = array(),$user_id){
        $where['user_id'] = $user_id;
        if($request['type'] == '1'){
            //获取到足迹的商品
            $where['f.type'] = 1;
            $where['f.status'] = 1;
            $count = M('Myfooter')->alias('f')->where($where)->count();
            $list = M('Myfooter')->alias('f')
                ->field('f.id AS footer_id,g.id AS goods_id,g.goods_name,g.goods_img,g.market_price,g.shop_price,g.integral,g.sell_num,g.ticket_buy_id,country_id,g.is_buy,f.add_time')
                ->join(C('DB_PREFIX').'goods g ON g.id=f.id_val')
                ->where($where)
                ->order('f.add_time DESC')
                ->page($request['p'],10)
                ->select();
            if(!$list){
                $msg = $request['p']==1?'暂无数据':'无更多数据';
                apiResponse('1',$msg);
            }
            foreach($list as $k=>$v){
                $goods_img = D('File')->getOneFilePath($v['goods_img']);
                $list[$k]['goods_img'] = $goods_img?$goods_img:C('API_URL').'/Uploads/Goods/default.png';
                if($v['country_id'] > 0){
                    $country_logo = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
                    $list[$k]['country_logo'] = $country_logo?$country_logo:C('API_URL').'/Uploads/Country/default.png';
                }else{
                    $list[$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
                }
                //如果是票券区商品显示可使用的 票券
                if($v['ticket_buy_id']>0){
                    $disc = getName('TicketBuy','discount',$v['ticket_buy_id']);
                    $list[$k]['ticket_buy_discount'] = $disc?$disc:'0';
                }else{
                    $list[$k]['ticket_buy_discount'] = '0';
                }
            }
        }elseif($request['type']=='2'){
            //获取商家
            $where['type'] = 2;
            $where['status'] = 1;
            $count = M('Myfooter')->where($where)->count();
            $mer_list = M('Myfooter')->field('id AS footer_id,id_val')->where($where)->page($request['p'],10)->select();
            if(!$mer_list){
                $msg = $request['p']==1?'暂无数据':'无更多数据';
                apiResponse('1',$msg);
            }
            foreach($mer_list as $k=>$v){
                $mer_list[$k] = D('Merchant','Logic')->getFace($v['id_val']);
                $mer_list[$k]['footer_id'] = $v['footer_id'];
            }
            $list = $mer_list;
        }elseif($request['type']=='3'){
            //获取书院文章
            $where['m.type'] = 3;
            $where['m.status'] = 1;
            $count = M('Myfooter')->alias('m')->where($where)->count();
            $aca_list = M('Myfooter')->alias('m')
                ->field('m.id AS footer_id,m.id_val as academy_id,a.title,a.logo,a.page_views,a.collect_num')
                ->join(C('DB_PREFIX').'academy a ON a.id=m.id_val')
                ->where($where)
                ->order('add_time DESC')
                ->page($request['p'],10)
                ->select();
            if(!$aca_list){
                $msg = $request['p']==1?'暂无数据':'无更多数据';
                apiResponse('1',$msg);
            }
            foreach($aca_list as $k=>$v){
                $aca_list[$k]['logo'] = D('File')->getOneFilePath($v['logo']);
            }
            $list = $aca_list;
        }
        apiResponse('1','获取成功',$list,$count);
    }
    /**
     * 删除足迹
     * @param string $collect_ids
     */
    public function delFooter($footer_ids = ''){
        $mod = D('Myfooter');
        $where['id'] = array('IN',$footer_ids);
        $res = $mod->where($where)->save(array('status'=>9));
        if($res){
            apiResponse('1','删除足迹成功');
        }else{
            apiResponse('0','删除足迹失败');
        }
    }
    /**
     * 我的积分
     * @param array $request
     * @param int $user_id
     */
    public  function myIntegral($request = array(),$user_id = 0){
        $num = getName('Goods','integral',$user_id);
        $info['my_integral'] = $num? $num :'0';
        $date = date('Y-m-d',time());
        $int_info = M('Point')->where("date='{$date}' AND status=1")->select();
        $info['point_list']['point_num'] = $int_info['point_num']?$int_info['point_num']:'0';
        $info['point_list']['int_rate'] = $int_info['int_rate']?$int_info['int_rate']:'0';
        $info['point_list']['send_rate'] = $int_info['send_rate']?$int_info['send_rate']:'0';
        $info['point_list']['date'] = $int_info['date']?$int_info['date']:date('Y-m-d',time());
        if(!$info['point_list']){
            apiResponse('0','今日暂无指数');
        }
        apiResponse('1','获取成功',$info);
    }
    /**
     * 积分明细
     * @param int $user_id
     * //1获的 2消费 3回退 4兑换
     */
    public function integralLog($request = array(),$user_id = 0){
        $mod = M('IntegralLog');
        $list = $mod
            ->field('id AS log_id,reason,use_integral,create_time,act_type')
            ->where("user_id = {$user_id}")
            ->order('create_time DESC')
            ->page($request['p'],20)
            ->select();
        if(!$list){
            $msg = $request['p']==1? '暂无明细':'无更多明细';
            apiResponse('0',$msg);
        }
        //根据月份分组
        foreach($list as $k=>$v){
            $time = date('Y-m',$v['create_time']);
            $v['create_time'] = date('Y-m-d',$v['create_time']);
            $data_list[$time][] = $v;
        }
        $i = 0;
        foreach($data_list as $key=>$value){
            $res[$i]['time'] = $key;
            $res[$i]['list'] = $value;
            $i++;
        }
        apiResponse('1','获取成功',$res);
    }

    /**
     * 增加积分
     */
//    public function addIntegral($request = array(),$user_id = 0){
//        $res = D('User')->where("id = {$user_id}")->setInc('integral',$request['get_integral']);
//        if(!$res){
//            apiResponse('0','积分增加失败');
//        }
//        $mod = D("LevelLog");
//        $data['reason'] = $request['reason'];
//        $data['use_integral'] = $request['get_integral'];
//        $data['user_id'] = $user_id;
//        $data['create_time'] = time();
//        $data['update_time']  = time();
//        $mod->add($data);
//
//        apiResponse('1','添加成功');
//
//    }
    /**
     * 商家推荐
     * @param array $request
     * @param int $user_di   product_pic(多图) business_license（多图） other_license(单图)
     */
    public function merchantRefer($request = array(),$user_id = 0){
        $data = $request;
        //处理图片
        if(!empty($_FILES['product_pic']['name']) || !empty($_FILES['business_license']['name']) || !empty($_FILES['other_license']['name'])){
            $product_pic      = array();
            $other_license    = array();
            $business_license = 0;
            $res = api('UploadPic/upload', array(array('save_path' => 'MerchantRefer')));

            foreach ($res as $k=>$v) {
                if($v['key'] == 'product_pic'){
                    $product_pic[] = $v['id'];
                }
                if($v['key'] == 'other_license'){
                    $other_license[] = $v['id'];
                }
                if($v['key'] == 'business_license'){
                    $business_license = $v['id'];
                }
            }
            $data['product_pic'] = $product_pic?implode(',',$product_pic):'';
            $data['other_license'] = $other_license?implode(',',$other_license):'';
            $data['business_license'] = $business_license?$business_license:0;
        }
        $data['create_time'] = time();
        $data['user_id'] = $user_id;
        $mod = D('MerchantRefer');
        $data['service_id'] = M('administrator')->where(array('is_service'=>1))->order('rand()')->getField('id');
        $data['attract_id'] = M('administrator')->where(array('is_attract'=>1))->order('rand()')->getField('id');
        $id = $mod->add($data);

        if(!$id){
            apiResponse('0','推荐失败');
        }
        apiResponse('1','推荐成功,正在审核',array('refer_id'=>$id));
    }

    /**
     * 获取经营范围（顶级分类）
     * @return bool|mixed
     */
    public function getRange(){
        $range_list = M('GoodsCategory')->field('id AS cate_id,short_name')->where("parent_id = 0")->select();
        if(!$range_list){
            return false;
        }else{
            return $range_list;
        }
    }

    /**
     * 获取商家推荐列表
     * @param int $user_id
     */
    public function referList($user_id= 0 ){
        $where['user_id'] = $user_id;
        $list = M('Merchant_refer')
            ->field('id AS refer_id,name,create_time,status,product_pic')
            ->where($where)
            ->order('create_time DESC')
            ->select();
        if(!$list){
            apiResponse('1','暂无推荐信息');
        }
        foreach($list as $k=>$v){
            if(!empty($v['product_pic'])){
                $list[$k]['product_pic'] = D('File')->getOneFilePath($v['product_pic']);
            }
            $list[$k]['create_time'] = date('Y-m-d',$v['create_time']);
        }
        apiResponse('1','获取成功',$list);
    }
    /**
     * 获取商家推荐信息
     */
    public function referInfo($request = array()){
        $where['id'] = $request['refer_id'];
        $info = M('MerchantRefer')->where($where)->find();
        $info['desc'] = '店铺介绍，店铺介绍';

        //处理经营范围
        $info['range_id'] = getName('GoodsCategory','name',$info['range_id']);
        //处理图片
        $info['product_pic'] = D('File')->getArrayFilePath(explode(',',$info['product_pic']));
        $info['other_license'] = D('File')->getArrayFilePath(explode(',',$info['other_license']));
        $info['business_license'] = D('File')->getOneFilePath($info['business_license']);
        $info['create_time'] = date('Y-m-d',$info['create_time']);
        $info['logo'] = $info['product_pic'][0]['path'];
        $info['score'] = '4.5';
        apiResponse('1','获取成功',$info);
    }
    /**
     * 会员成长
     */
    public function userDevelop($user_id = 0){
        //获取年度成长值
//        $start = strtotime(date('Y').'-01-01');
//        $end = strtotime(date('Y').'-12-31');

        $mod = M('UserLevel');
        $list = $mod->field('id AS level_id,level_name,icon,min_points,max_points')->where('status = 1')->select();
        if(!$list){
            apiResponse('0','暂无数据');
        }
        //判断等级
        $my_point = M('User')->where("id={$user_id}")->getField("grow_point");
        foreach($list as $k=>$v){
            if($my_point >= $v['min_points'] && $my_point <= $v['max_points']){
                $list[$k]['is_get'] = '1';
                $level = $v['level_name'];
                $icon = $v['icon'];
            }else if($my_point >= $v['max_points']){
                $list[$k]['is_get'] = '1';
            }else{
                $list[$k]['is_get'] = '0';
            }
        }
        $first = array('level_id'=>0,'level_name'=>'注册','is_get'=>'1');
        array_unshift($list,$first);
        $res['level_list'] = $list;
        $res['head_pic'] = D('File')->getOneFilePath(getName('User','head_pic',$user_id),C('API_URL').'/Uploads/User/default.png');
        $res['level'] = $level;
        $res['my_point'] = $my_point;
        $res['year'] = date('Y');
        //获取当前等级会员头像
        $res['icon'] = D('File')->getOneFilePath($icon);
        apiResponse('1','获取成功',$res);
    }

    /**
     * 成长明细
     * @param int $user_id
     */
    public function userDevelopLog($request = array(),$user_id = 0){
        $mod = M('LevelLog');
        $list = $mod
            ->field('id AS log_id,reason,get_point,create_time')
            ->where("user_id = {$user_id}")
            ->order('create_time DESC')
            ->page($request['p'],20)
            ->select();
        if(!$list){
            $msg = $request['p']==1? '暂无明细':'无更多明细';
            apiResponse('0',$msg);
        }
        //根据月份分组
        foreach($list as $k=>$v){
            $time = date('Y-m',$v['create_time']);
            $v['create_time'] = date('Y-m-d',$v['create_time']);
            $data_list[$time][] = $v;
        }
        $i = 0;
        foreach($data_list as $key=>$value){
            $res[$i]['time'] = $key;
            $res[$i]['list'] = $value;
            $i++;
        }
        apiResponse('1','获取成功',$res);
    }

    /**
     * 增加成长值
     * @param array $request
     * @param int $user_id
     */
    public function addPoint($request = array(),$user_id = 0){
        $res = D('User')->where("id = {$user_id}")->setInc('grow_point',$request['get_point']);
        if(!$res){
            return false;
        }
        $mod = D("LevelLog");
        $data['reason'] = $request['reason'];
        $data['get_point'] = $request['get_point'];
        $data['user_id'] = $user_id;
        $data['create_time'] = time();
        $id = $mod->add($data);
        return $id ? true : false;
    }

    /**
     * 会员等级
     * @param int $user_id
     */
    public function userRank($user_id = 0){
        $mod = M('UserRank');
        $list = $mod->field('id AS rank_id,rank_name,desc,icon')->where('status = 1')->select();
        if(!$list){
            apiResponse('0','暂无数据');
        }
        //判断等级
        $my_rank = M('User')->where("id={$user_id}")->getField("rank_id");
        foreach($list as $k=>$v){
            if($my_rank >= $v['rank_id']){
                $list[$k]['is_get'] = '1';
                $icon = $v['icon'];
            }else{
                $list[$k]['is_get'] = '0';
            }
            $list[$k]['fee'] = '';
        }
        $res['rank_list'] = $list;
        $res['head_pic'] = D('File')->getOneFilePath(getName('User','head_pic',$user_id),C('API_URL').'/Uploads/User/default.png');
        $res['my_rank'] = getName('UserRank','rank_name',$my_rank);
//        $time = getName('User','rank_end_time',$user_id);
//        if($time > 0){
//            $res['end_time'] = date('Y-m-d',$time);
//        }else{
//            $res['end_time'] = '永久有效';
//        }
        $res['end_time'] = '';
        $res['icon'] = D('File')->getOneFilePath($icon);
        apiResponse('1','获取成功',$res);
    }

    /**
     * 升级会员
     * 等级id
     */
    public function updateRank(){
        //当小费满足一定条件调取此函数

    }

    /**
     * 获取注册码(邀请码)
     */
    public function getSignCode($user_id = 0){

        $code = M('User')->where("id={$user_id}")->getField('invite_code');
        $head_pic = D('File')->getOneFilePath(getName('User','head_pic',$user_id));

        $invite_url = C('API_URL').'/index.php/Api/Register/scanPage/invite_code/'.$code;
        if(!$code){
            apiResponse('0','暂无注册码',array('code'=>'','head_pic'=>$head_pic));
        }
        apiResponse('1','获取成功',array('code'=>$invite_url,'head_pic'=>$head_pic));
    }
    /**
     * 分享好友(获取分享内容)
     */
    public function shareFriend($user_id = 0){
        //获取广告位置
        $share_info = D('Ads','Logic')->adsList(array('num'=>1,'position'=>'21'))[0];
        $data['share_id'] = $share_info['ads_id'];
        $data['share_img'] = $share_info['picture'];
        $data['share_title'] = $share_info['desc'];
        $data['share_url'] = $share_info['href'];
        apiResponse('1','获取成功',$data);
    }

    /**
     * 分享回调
     */
    public function shareBack($request = array(),$user_id = 0){
        $mod = M('Share');
        $data['type'] = $request['type'];
        $data['content'] = $request['content'] ? $request['content'] : '';
        //从数据库查询(根据分享内容类型获取分享图片)
        if($request['share_type'] == '1'){
            $data['pic'] = getName('Goods','goods_img',$request['id_val']);
        }else if($request['share_type'] == '2'){
            $data['pic'] = getName('Merchant','logo',$request['id_val']);
        }else if($request['share_type'] == '3'){
            $data['pic'] = getName('Academy','logo',$request['id_val']);
        }else if($request['share_type'] == '4'){
            $data['pic'] = getName('BonusFace','bonus_face',$request['id_val']);
        }else if($request['share_type'] == '5'){
            $data['pic'] = getName('Ads','picture',$request['share_id']);
        }else{
            $data['pic'] = C('API_URL').'/Uploads/Share/default.png';
        }
        $data['url'] = $request['url'];
        $data['title'] = '分享';
        $data['share_type'] = $request['share_type'];//其他类型(系统设置的)
        $data['id_val'] = $request['id_val'];
        $data['user_id'] = $user_id;
        $data['create_time'] = time();
        $id = $mod->add($data);
        if($id){
            if($request['share_type'] == '4'){
                //调用领取红包接口
                D('Welfare','Logic')->getBonus2(array('bonus_id'=>$request['id_val'],'type'=>$request['type']),$user_id);
            }else{
                apiResponse('1','分享成功');
            }
        }else{
            apiResponse('0','分享失败');
        }
    }
    /**
     * 我的分享
     */
    public function myShare($request = array(),$user_id = 0){
        $mod = M('Share');
        $count = $mod->where("user_id = {$user_id}")->count();
        $list = $mod->where("user_id = {$user_id}")->page($request['p'],10)->order('create_time')->select();
        if(!$list){
            $msg = $request['p']==1?'暂无内容':'无更多内容';
            apiResponse('0',$msg);
        }
        foreach($list as $k=>$v){
            $list[$k]['pic'] = D('File')->getOneFilePath($v['pic']);
            $list[$k]['create_time'] = date('Y-m-d',$v['create_time']);
            switch($v['type']){
                case '1': $list[$k]['type'] = "微信分享"; break;
                case '2': $list[$k]['type'] = "微博分享"; break;
                case '3': $list[$k]['type'] = "QQ分享"; break;
            }
        }
        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 我的推荐
     * @param array $request
     * @param int $user_id
     */
    public function myRecommend($request = array(),$user_id = 0){
        $mod = M('User');
        if(!empty($request['parent_id'])){
            $where['parent_id'] = $request['parent_id'];
        }else{
            $where['parent_id'] = $user_id;
        }
        $count = $mod->where($where)->count();
        $list = $mod->field('id,nickname,head_pic,phone,create_time,rank_id,grow_point')
            ->where($where)
            ->page($request['p'],10)
            ->order('create_time')
            ->select();
        if(!$list){
            $msg = $request['p']==1?'暂无内容':'无更多内容';
            apiResponse('0',$msg);
        }
        foreach($list as $k=>$v){
            //根据成长值 获取图
            $level_icon = $this->getLevel($v['grow_point']);
            $list[$k]['level_icon'] = $level_icon ? D('File')->getOneFilePath($level_icon['icon']):'';
            $list[$k]['level_id'] = $level_icon ? $level_icon['id']:'0';
            //根据等级id获取图
            $rank_icon = M('UserRank')->where("id = {$v['rank_id']}")->find();
            unset($list[$k]['rank_id']);
            $list[$k]['rank_icon'] = $rank_icon ? D('File')->getOneFilePath($rank_icon['icon']) : '';
            $list[$k]['head_pic'] = D('File')->getOneFilePath($v['head_pic']);
            $list[$k]['create_time'] = date('Y-m-d',$v['create_time']);
            $list[$k]['phone'] = substr_replace($v['phone'],'****',3,4);
            $list[$k]['recommend_num'] = $mod->where("parent_id = {$v['id']}")->count();
        }
        apiResponse('1','获取成功',$list,$count);
    }
    /**
     * 成绩页
     * @param array $request
     * @param int $user_id
     */
    public function gradeRank($request = array(),$user_id = 0){
        $mod = M('User');
        $my_info = $mod->field('id,nickname,head_pic,parent_id')->where("id = {$user_id}")->find();
        if(!$my_info){
            apiResponse('0','无数据');
        }
        //根据id获取到推荐人昵称
        $pName = getName('User','nickname',$my_info['parent_id']);
        $my_info['parent_name'] = $pName ? $pName : '暂无推荐人';
        $my_info['head_pic'] = D('File')->getOneFilePath($my_info['head_pic']);
        $my_info['recommend_num'] = M('User')->where("parent_id = {$my_info['id']}")->count();
        $my_info['share_num'] = M('Share')->where("user_id = {$my_info['id']}")->count();
        if(!empty($request['city_id'])){
            $where['u.city_id'] = $request['city_id'];
        }elseif(!empty($request['city_name'])){
            $city_name = $request['city_name'];
            //根据名称获取到id
            $city_id = M('Region')->where("region_name = '{$city_name}' AND region_type=2")->getField('id');
            $where['u.city_id'] = $city_id;
        }
        if(empty($request['type']) || $request['type']=='share'){
            $list = M('Share')->alias('s')
                ->field("u.head_pic,u.nickname,count(*) AS num,s.user_id")
                ->join(C('DB_PREFIX').'user u ON u.id = s.user_id')
                ->where($where)
                ->group("s.user_id")
                ->page($request['p'],10)
                ->order("num DESC")
                ->select();
        }else if($request['type']=='recommend') {
            $where['s.parent_id'] = array('neq','0');
            $list = M('User')->alias('s')
                ->field("s.parent_id,count(*) AS num,u.nickname,u.head_pic")
                ->join(C('DB_PREFIX').'user u ON u.id = s.parent_id')
                ->where($where)
                ->group('s.parent_id')
                ->page($request['p'],10)
                ->order("num DESC")
                ->select();
        }
        foreach($list as $k=>$v){
            $list[$k]['head_pic'] = D('File')->getOneFilePath($v['head_pic']);
        }
        if(!$list){
            $msg = $request['p']==1?'暂无内容':'无更多数据';
            apiResponse('0',$msg,$my_info);
        }
        $my_info['rank_list'] = $list;
        apiResponse('1','获取成功',$my_info);
    }

    /**
     * 公用函数 ： 添加我的足迹
     */
    function recordFoot($type,$user_id,$id_val){
        //先判断有没有足迹信息
        $mod = M('Myfooter');
        $where['type'] = $type;
        $where['user_id'] = $user_id;
        $where['id_val'] = $id_val;
        $where['status'] = 1;
        $id = $mod->where($where)->getField('id');
        if($id){
            $mod->where("id = {$id}")->save(array('add_time'=>time()));
        }else{
            $where['add_time'] = time();
            $mod->add($where);
        }
    }
    /**
     * 公用函数 ：根据成长值获取等级信息
     */
    public function getLevel($grow_point = 0){
        $where['min_points'] = array('elt',$grow_point);
        $where['max_points'] = array('egt',$grow_point);
        $info = M('UserLevel')->where($where)->find();
        return $info;
    }

    /**
     * 个人认证
     *real_name  sex  id_card_num id_card_start_time id_card_end_time
     * auth_province_id auth_city_id auth_area_id auth_street_id positive_id_card back_id_card
     */
    public function personalAuth($request,$user_id = 0){
        if(empty($request['real_name'])){
            apiResponse('0','请输入真实姓名');
        }
        if(!in_array($request['sex'],array('1','2'))){
            apiResponse('0','请选择性别');
        }
        if(empty($request['id_card_num'])){
            apiResponse('0','请输入身份证号');
        }
        if(empty($request['auth_province_id']) || empty($request['auth_city_id']) || empty($request['auth_area_id'])){
            apiResponse('0','请选择所在地区');
        }
        if(empty($request['auth_street_id'])){
            apiResponse('0','请选择所在街道');
        }
        //判断身份或者能好有没有被认证
        $h = M('User')->where("id_card_num = '{$request['id_card_num']}' AND status=2")->getField('id');
        if($h){
            apiResponse('0','此身份证已被认证');
        }

        if($_FILES['positive_id_card']['name'] || $_FILES['back_id_card']['name']){
            $res = api('UploadPic/upload', array(array('save_path' => 'User')));
            foreach ($res as $k => $v) {
                if($v['key']=='positive_id_card'){
                    $data['positive_id_card'] = $v['id'];
                }
                if($v['key']=='back_id_card'){
                    $data['back_id_card'] = $v['id'];
                }
            }
        }
        $data['real_name'] = $request['real_name'];
        $data['sex'] = $request['sex'];
        $data['id_card_num'] = $request['id_card_num'];
        $data['id_card_start_time'] = $request['id_card_start_time']?$request['id_card_start_time']:0;
        $data['id_card_end_time'] = $request['id_card_end_time']?$request['id_card_end_time']:0;
        $data['auth_province_id'] = $request['auth_province_id'];
        $data['auth_city_id'] = $request['auth_city_id'];
        $data['auth_area_id'] = $request['auth_area_id'];
        $data['auth_street_id'] = $request['auth_street_id'];
        $data['auth_status'] = 1;
        $data['update_time'] = time();
        $where['id'] = $user_id;
        $res = M('User')->where($where)->data($data)->save();
        if($res){
            apiResponse('1','认证审核中，请稍等...');
        }else{
            apiResponse('0','认证失败,请确保信息的合法性');
        }

    }

    /**
     * 获取个人认证详情
     */
    public function personalAuthInfo($request,$user_id = 0){
        $where['id'] = $user_id;
        $info = M('User')->where($where)
            ->field('real_name,sex,id_card_num,id_card_start_time,id_card_end_time,auth_province_id,auth_city_id,auth_area_id,auth_street_id,positive_id_card,back_id_card,auth_status,auth_desc,com_name,comp_reg_num,comp_start_time,comp_end_time,comp_province_id,comp_city_id,comp_area_id,comp_street_id,comp_business_license,comp_auth_status,comp_desc')
            ->find();
        if(!$info){
            apiResponse('0','用户信息查询失败');
        }
        $auth_province_name = M('Region')->where(array('id'=>$info['auth_province_id']))->getField('region_name');
        $info['auth_province_name'] = $auth_province_name?$auth_province_name:'';
        $auth_province_name = M('Region')->where(array('id'=>$info['auth_city_id']))->getField('region_name');
        $info['auth_city_name'] = $auth_province_name?$auth_province_name:'';
        $auth_province_name = M('Region')->where(array('id'=>$info['auth_area_id']))->getField('region_name');
        $info['auth_area_name'] = $auth_province_name?$auth_province_name:'';
        $auth_province_name = M('Street')->where(array('street_id'=>$info['auth_street_id']))->getField('street_name');
        $info['auth_street_name'] = $auth_province_name?$auth_province_name:'';
        $path = M('File')->where(array('id'=>$info['positive_id_card']))->getField('path');
        $info['positive_id_card'] = $path?C('API_URL').$path:'';
        $path = M('File')->where(array('id'=>$info['back_id_card']))->getField('path');
        $info['back_id_card'] = $path?C('API_URL').$path:'';
        $info['id_card_start_date'] = $info['id_card_start_time']?''.date('Y-m-d',$info['id_card_start_time']):'0';
        $info['id_card_end_date'] = $info['id_card_end_time']?''.date('Y-m-d',$info['id_card_end_time']):'0';
        $info['comp_start_date'] = $info['comp_start_time']?''.date('Y-m-d',$info['comp_start_time']):'0';
        $info['comp_end_date'] = $info['comp_end_time']?''.date('Y-m-d',$info['comp_end_time']):'0';

        $comp_province_name = M('Region')->where(array('id'=>$info['comp_province_id']))->getField('region_name');
        $info['comp_province_name'] = $comp_province_name?$comp_province_name:'';
        $comp_province_name = M('Region')->where(array('id'=>$info['comp_city_id']))->getField('region_name');
        $info['comp_city_name'] = $comp_province_name?$comp_province_name:'';
        $comp_province_name = M('Region')->where(array('id'=>$info['comp_area_id']))->getField('region_name');
        $info['comp_area_name'] = $comp_province_name?$comp_province_name:'';
        $comp_province_name = M('Street')->where(array('street_id'=>$info['comp_street_id']))->getField('street_name');
        $info['comp_street_name'] = $comp_province_name?$comp_province_name:'';
        $path = M('File')->where(array('id'=>$info['comp_business_license']))->getField('path');
        $info['comp_business_license'] = $path?C('API_URL').$path:'';

        apiResponse('1','请求成功',$info);
    }

    /**
     * 企业认证
     * com_name  comp_reg_num comp_start_time comp_end_time
     * comp_province_id comp_city_id comp_area_id comp_street_id comp_business_license
     */
    public function compAuth($request,$user_id = 0){
        if(empty($request['com_name'])){
            apiResponse('0','请输入企业名称');
        }
        if(empty($request['comp_reg_num'])){
            apiResponse('0','请输入注册号');
        }
        if(empty($request['comp_province_id']) || empty($request['comp_city_id']) || empty($request['comp_area_id'])){
            apiResponse('0','请选择所在地区');
        }
        if(empty($request['comp_street_id'])){
            apiResponse('0','请选择所在街道');
        }

        if($_FILES['comp_business_license']['name']){
            $res = api('UploadPic/upload', array(array('save_path' => 'User')));
            foreach ($res as $k => $v) {
                $data['comp_business_license'] = $v['id'];
            }
        }
        $data['com_name'] = $request['com_name'];
        $data['comp_reg_num'] = $request['comp_reg_num'];
        $data['comp_start_time'] = $request['comp_start_time']?$request['comp_start_time']:0;
        $data['comp_end_time'] = $request['comp_end_time']?$request['comp_end_time']:0;
        $data['comp_province_id'] = $request['comp_province_id'];
        $data['comp_city_id'] = $request['comp_city_id'];
        $data['comp_area_id'] = $request['comp_area_id'];
        $data['comp_street_id'] = $request['comp_street_id'];
        $data['comp_auth_status'] = 1;
        $data['update_time'] = time();
        $where['id'] = $user_id;
        $res = M('User')->where($where)->data($data)->save();
        if($res){
            apiResponse('1','认证审核中，请稍等...');
        }else{
            apiResponse('0','认证失败,请确保信息的合法性');
        }
    }

//    /**
//     * 企业认证详情
//     */
//    public function compAuthInfo($request,$user_id = 0){
//        $where['id'] = $user_id;
//        $info = M('User')->where($where)
//            ->field('com_name,comp_reg_num,comp_start_time,comp_end_time,comp_province_id,comp_city_id,comp_area_id,comp_street_id,comp_business_license,comp_auth_status,comp_desc')
//            ->find();
//        if(!$info){
//            apiResponse('0','用户信息查询失败');
//        }
//        $comp_province_name = M('Region')->where(array('id'=>$info['comp_province_id']))->getField('region_name');
//        $info['comp_province_name'] = $comp_province_name?$comp_province_name:'';
//        $comp_province_name = M('Region')->where(array('id'=>$info['comp_city_id']))->getField('region_name');
//        $info['comp_city_name'] = $comp_province_name?$comp_province_name:'';
//        $comp_province_name = M('Region')->where(array('id'=>$info['comp_area_id']))->getField('region_name');
//        $info['comp_area_name'] = $comp_province_name?$comp_province_name:'';
//        $comp_province_name = M('Street')->where(array('street_id'=>$info['comp_street_id']))->getField('street_name');
//        $info['comp_street_name'] = $comp_province_name?$comp_province_name:'';
//        $path = M('File')->where(array('id'=>$info['comp_business_license']))->getField('path');
//        $info['comp_business_license'] = $path?C('API_URL').$path:'';
//        apiResponse('1','请求成功',$info);
//    }

}