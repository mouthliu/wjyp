<?php
namespace Api\Logic;

/**
 * Class WelfareLogic
 * @package Api\Logic
 * 逻辑层  购物券模块
 *
 */
class WelfareLogic extends BaseLogic{

    /**
     * 获取优惠券列表
     * @param array $request
     */
    public function ticketList($request = array(),$user_id = 0){
        //1获取顶级分类列表
        $list['top_nav'] = D('GoodsCategory','Logic')->topNav()['list'];
        $list['top_nav'] = $list['top_nav']?$list['top_nav']:array();
        $first = array('cate_id'=>0,'short_name'=>'推荐','name'=>'推荐');
        array_unshift($list['top_nav'],$first);

        if(!empty($request['cate_id'])){
            $where['t.cate_id'] = $request['cate_id'];
        }
        $mod = M('Ticket');
        $where['t.end_time'] = array('gt',time());//未过期的优惠券
        $where['t.status'] = 1;//已发布的优惠券
        $where['t.limit_num'] = 1;
        $list['ticket_list'] = $mod->alias('t')
            ->field('t.id as ticket_id,t.ticket_name,t.ticket_desc,t.ticket_type,t.value,t.condition,t.merchant_id,t.end_time,t.start_time,t.ticket_num,t.use_num,m.logo')
            ->join(C('DB_PREFIX').'merchant m ON m.id = t.merchant_id')
            ->where($where)
            ->order('t.create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['ticket_list']){
            $msg = $request['p']==1?'暂无优惠券':'无更多优惠券';
            apiResponse('1',$msg,$list);
        }
        $count = $mod->alias('t')->join(C('DB_PREFIX').'merchant m ON m.id = t.merchant_id')->where($where)->count();
        //根据优惠券领取情况分组
        foreach($list['ticket_list'] as $k=>$v){
            // 根据商家id 获取到商家logo
            $list['ticket_list'][$k]['logo'] = D('File')->getOneFilePath($v['logo']);
            //  判断我当前是否领过
            $id = M('UserTicket')->where("user_id={$user_id} AND ticket_id={$v['ticket_id']}")->getField('id');
            if($id){
                $list['ticket_list'][$k]['is_get'] = '1';
            }else{
                $list['ticket_list'][$k]['is_get'] = '0';
            }
        }
        apiResponse('1','获取数据成功',$list,$count);
    }
    /**
     * 领取优惠券
     */
    public function getTicket($request=array(),$user_id = 0){
        $ticket_info = M('Ticket')->where("id={$request['ticket_id']}")->find();
        if(!$ticket_info){
            apiResponse('0','优惠券信息获取失败');
        }
        //判断优惠券是否已被领完
        if($ticket_info['use_num'] >= $ticket_info['ticket_num']){
            apiResponse('0','抱歉，您来晚了,已被抢空');
        }
        //首先获取优惠券的限领数量
        $limit_num = $ticket_info['limit_num'];
        if($limit_num>0){
            //判断该用户领取过得数量()
            $user_get_num = M('UserTicket')->where("user_id={$user_id} AND ticket_id={$request['ticket_id']}")->count();
            if($user_get_num >= $limit_num){
//                apiResponse('1','每个人只能领取'.$limit_num.'张');
                apiResponse('0','您已经领取过该购物券');
            }
        }
        $data['user_id'] = $user_id;
        $data['ticket_id'] = $request['ticket_id'];
        $data['add_time'] = time();
        $data['status'] = 0;
        $id = M('UserTicket')->add($data);
        if($id){
            //领取数量增加
            M('Ticket')->where("id={$request['ticket_id']}")->setInc('use_num');
            apiResponse('1','领取成功');
        }else{
            apiResponse('0','领取失败');
        }

    }

    /**
     * 红包列表
     * @param array $request
     * @param $user_id
     */
    public function faceList($request = array(),$user_id = 0){
        $new_list = array();
        //获取公告
        $ano =  M('Announce')->field('id AS announce_id,title')->where("status = 1")->select();
        $new_list['announce'] = empty($ano)?array():$ano;
        //获取封面列表
        $list = M('BonusFace')
            ->field("id AS bonus_id,bonus_face,min_val,max_val,merchant_id")
            ->where('status = 2')
            ->order('sort DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list){
            $msg = $request['p']==1 ? '暂无红包':'无更多红包';
            apiResponse('0',$msg,$new_list);
        }
        $count = M('BonusFace')->where('status = 2')->count();
        foreach($list as $k=>$v){
            //判断是否领g过
//            $is_get = M('UserBonusLog')->where("user_id = {$user_id} AND bonus_id={$v['bonus_id']}")->getField('id');
//            $new_list['list'][$k]['is_get'] = $is_get ? '1':'0';
//            $bonus_val = $this->getRand($v['min_val']*10,$v['max_val']*10,95,$v['min_val']*10)/10;
            $new_list['list'][$k]['bonus_id'] = $v['bonus_id'];
//            $new_list['list'][$k]['bonus_val'] = $bonus_val;
            $new_list['list'][$k]['bonus_face'] = D('File')->getOneFilePath($v['bonus_face']);
            $new_list['list'][$k]['merchant_id'] = $v['merchant_id'];
        }
        apiResponse('1','获取成功',$new_list,$count);
    }
    /**
     * 红包广告列表
     * @param array $request
     * @param int $user_id
     */
    public function bonusList($request = array(),$user_id = 0){
        $mid = M('BonusFace')->field("id AS bonus_id,bonus_face,min_val,max_val,merchant_id")
            ->where("id = {$request['bonus_id']}")
            ->find();
        $mod = M('Bonus');
        $where['bonus_face_id'] = $request['bonus_id'];
        $b_list = $mod->field("id AS ads_id,bonus_title,bonus_ads,type,merchant_id")
            ->where($where)
            ->order('sort DESC')
            ->select();
        if(!$b_list){
            apiResponse('0','获取广告失败');
        }
        $count =$mod->where($where)->count();
        foreach($b_list as $k=>$v){
            $b_list[$k]['bonus_ads'] = D('File')->getOneFilePath($v['bonus_ads']);
            if($v['type'] == '2'){
                $b_list[$k]['delay_time'] = '4';
            }else{
                $b_list[$k]['delay_time'] = '0';
            }
        }
        $list['ads_list'] = $b_list;
        //红包id
        $list['bonus_id'] = $request['bonus_id'];
        //获取红包金额
        $bonus_val = $this->getRand($mid['min_val']*10,$mid['max_val']*10,95,$mid['min_val']*10)/10;
        $list['bonus_val'] = $bonus_val;
        //获取到商家信息
        $m_info = M('Merchant')->field('logo,merchant_name')->where("id = {$mid['merchant_id']}")->find();
        $list['logo'] = D('File')->getOneFilePath($m_info['logo']);
        $list['merchant_name'] = $m_info?$m_info['merchant_name']: '';
        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 领取红包
     * @param array $request
     * @param int $user_id
     */
    public function getBonus($request = array(),$user_id = 0){
        $mod = M('UserBonusLog');
        //先查询领过这个红包没有
        $id =  $mod->where("user_id = {$user_id} AND bonus_id={$request['bonus_id']}")->getField('id');
        if($id){
            apiResponse('0','您已经领过该红包，快去抢下一家');
        }
        //查询红包满了没有
        $info  = M('BonusFace')->where("id = {$request['bonus_id']}")->find();
        if(!$info){
            apiResponse('0','红包信息获取失败');
        }else{
            if($info['total_money'] <= $info['send_money']){
                apiResponse('0','对不起,您来晚了,红包没了');
            }
        }
        $data['user_id'] = $user_id;
        $data['bonus_id'] = $request['bonus_id'];
        //防止超出总额
        if(($info['send_money']+$request['bonus_val']) > $info['total_money']){
            $data['bonus_val'] = $info['total_money'] - $info['send_money'];
        }else{
            $data['bonus_val'] = $request['bonus_val'];
        }
        $data['create_time'] = time();
        if(!empty($request['type'])){
            $data['type'] = $request['type'];
        }
        $res = $mod->add($data);
        if($res){
            $path = M('File')->where(array('id'=>getName('User','head_pic',$user_id)))->getField('path');
            $head_pic = $path?C('API_URL').$path:C('API_URL').'/Uploads/User/default.png';
            //将余额增加
            D('User')->where("id={$user_id}")->setInc('balance',$data['bonus_val']);
            //将已发出金额增加
            M('BonusFace')->where("id={$data['bonus_id']}")->setInc('send_money',$data['bonus_val']);
            //将发出红包数量增加
            M('BonusFace')->where("id={$data['bonus_id']}")->setInc('send_num');
            apiResponse('1','领取红包成功',array('bonus_val'=>$data['bonus_val'].'','head_pic'=>$head_pic));
        }else{
            apiResponse('0','领取红包失败');
        }
    }
    /**
     * 回调成功领取红包
     * @param array $request
     * @param int $user_id
     */
    public function getBonus2($request = array(),$user_id = 0){
        //根据id获取到红包信息
        $mid = M('BonusFace')->where("id= {$request['bonus_id']}")->find();
        //获取红包金额
        $bonus_val = $this->getRand($mid['min_val']*10,$mid['max_val']*10,95,$mid['min_val']*10)/10;
        $request['bonus_val'] = $bonus_val;
        $mod = M('UserBonusLog');
        //先查询领过这个红包没有
        $id =  $mod->where("user_id = {$user_id} AND bonus_id={$request['bonus_id']} AND type={$request['type']}")->getField('id');
        if($id){
            apiResponse('0','您已经领过该红包，快去抢下一家');
        }
        //查询红包满了没有
        $info  = M('BonusFace')->where("id = {$request['bonus_id']}")->find();
        if(!$info){
            apiResponse('0','红包信息获取失败');
        }else{
            if($info['total_money'] <= $info['send_money']){
                apiResponse('0','对不起,您来晚了,红包没了');
            }
        }
        $data['user_id'] = $user_id;
        $data['bonus_id'] = $request['bonus_id'];
        //防止超出总额
        if(($info['send_money']+$request['bonus_val']) > $info['total_money']){
            $data['bonus_val'] = $info['total_money'] - $info['send_money'];
        }else{
            $data['bonus_val'] = $request['bonus_val'];
        }
        $data['create_time'] = time();
        if(!empty($request['type'])){
            $data['type'] = $request['type'];
        }
        $res = $mod->add($data);
        if($res){
            $path = M('File')->where(array('id'=>getName('User','head_pic',$user_id)))->getField('path');
            $head_pic = $path?C('API_URL').$path:C('API_URL').'/Uploads/User/default.png';
            D('User')->startTrans();
            $merchant_name = getName('Merchant','merchant_name',$mid['merchant_id']);
            //将余额增加
            $msg = "分享福利社广告成功获得".$merchant_name."送出的".$data['bonus_val']."元红包";
            $res1 = balanceChange($data['bonus_val'],9,$msg,$user_id);
            //将已发出金额增加
            $res2 = M('BonusFace')->where("id={$data['bonus_id']}")->setInc('send_money',$data['bonus_val']);
            //将发出红包数量增加
            $res3 = M('BonusFace')->where("id={$data['bonus_id']}")->setInc('send_num');
            if($res1 && $res2 &&$res3){
                D('User')->commit();
            }else{
                D('User')->rollback();
            }
            apiResponse('1','领取红包成功',array('bonus_val'=>$data['bonus_val'].'','head_pic'=>$head_pic));
        }else{
            apiResponse('0','领取红包失败');
        }
    }
    /**
     * 领取红包分享内容
     * 分享类型 type 分享类型 1微信 2微博 3QQ
     * 分享图片 pic
     * 分享标题 title
     * 分享内容 content
     * 分享链接 url
     * 分享内容类型 share_type 1 商品 2商家 3书院 4红包 5其他
     *
     */
    public function shareContent($request = array()){
        $info['id_val'] = $request['bonus_id'];
        $info['title'] = '分享标题';
        $info['content'] = '分享内容';
        $info['pic'] = C('API_URL')."/Uploads/Ads/2017-09-19/59c0888465eb4.png";
        $info['url'] = 'www.baidu.com';
        $info['share_type'] = '4';
        apiResponse('1','获取成功',$info);
    }

    /**
     * 获取概率（整数）
     * @param $min 最小值
     * @param $max 最大值
     * @param $rate 最小值 （目标值出现最多概率）
     * @param $aim 目标值
     * @return int
     */
    function getRand($min = 0,$max = 0,$rate = 0,$aim = 0){
        //获取随机红包
        $rand = 0; // 定义rank随机变量
        if(mt_rand(1,100) <= $rate) { // 控制2出现的概率为80%
            $rand = $aim;
        } else { // 剩余数字的概率为20%
            $rand = mt_rand($min,$max); // 排除掉2号位置,用1去补2号位
            if($rand == $aim) { // 当随机到2号位,则用1去补2号
                $rand = 1;
            }
        }
        return sprintf("%.2f", $rand); //输出结果
    }
}