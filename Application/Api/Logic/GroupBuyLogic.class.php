<?php
namespace Api\Logic;

/**
 * Class GroupBuyLogic
 * @package Api\Logic
 * 逻辑层  商品信息模块
 *
 */
class GroupBuyLogic extends BaseLogic{

    /**
     * 获取团购商品列表
     * @param array $request
     */
    public function groupBuyIndex($request = array()){
        //获取顶级分类
        $list['top_nav'] = D('GoodsCategory','Logic')->topNav()['list'];
        $list['top_nav'] = $list['top_nav']?$list['top_nav']:array();
        $first = array('cate_id'=>0,'short_name'=>'推荐','name'=>'推荐');
        array_unshift($list['top_nav'],$first);
        //1.首先根据分类列出旗下所有分类id
        if(empty($request['cate_id'])){
            //获取到默认的第一个顶级分类
            $request['cate_id'] = $list['top_nav'][1]['cate_id'];
        }
        //根据顶级分类获取到二级分类
        $list['two_cate_list'] = D('GoodsCategory','Logic')->getChildCate($request['cate_id'],1,'two_cate_id',1);
        //获取广告
        $list['ads'] = dealAds(D('Ads','Logic')->adsList(array('num'=>1,'position'=>'24'))[0]);

        $mod = M('GroupBuy');

        //首先根据分类列出旗下所有分类id
        $cate_ids = D('GoodsCategory','Logic')->getCateIds($request['cate_id']);
        if($cate_ids){
            $cate_ids .= $request['cate_id'];
            $where['g.cat_id'] = array('IN',$cate_ids);
        }else{
            $where['g.cat_id'] = $request['cate_id'];
        }
        $where['b.is_recommend'] = 1;//显示推荐商品
        $where['b.status'] = 2;
        $count = $mod->alias('b')->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')->where($where)->count();
        $list['group_buy_list'] = $mod->alias('b')
            ->field('b.id AS group_buy_id,b.group_price,b.group_num,b.total,b.integral,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id,g.shop_price')
            ->where($where)
            ->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')
            ->order('b.create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['group_buy_list']){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$list);
        }
        foreach($list['group_buy_list'] as $k=>$v){
            $list['group_buy_list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['group_buy_list'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['group_buy_list'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['group_buy_list'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['group_buy_list'][$k]['ticket_buy_discount'] = '0';
            }
            //根据活动id取两个活动参加用户(获取最近的两个开团的人)
            $two_first = M('GroupBuyLog')->alias('l')
                ->field('l.id AS log_id,l.user_id,u.head_pic')
                ->join(C('DB_PREFIX').'user u ON u.id = l.user_id')
                ->where("l.group_buy_id = {$v['group_buy_id']}")
                ->order('l.start_time DESC')
                ->limit(2)->select();
            foreach($two_first as $k1=>$v1){
                $two_first[$k1]['head_pic'] = D('File')->getOneFilePath($v1['head_pic']);
            }
            $list['group_buy_list'][$k]['append_person'] = $two_first ? $two_first : array();
        }

        apiResponse('1','获取成功',$list,$count);
    }
    /**
     * 获取三级分类商品列表
     * @param array $request
     */
    public function threeList($request = array()){
        //根据顶级分类获取到二级分类
        $list['three_cate_list'] = D('GoodsCategory','Logic')->getChildCate($request['two_cate_id'],1,'three_cate_id');
        $list['three_cate_list'] = $list['three_cate_list']?$list['three_cate_list']:array();
        $first = array('three_cate_id'=>0,'short_name'=>'全部','name'=>'全部');
        array_unshift($list['three_cate_list'],$first);
        if(empty($request['three_cate_id'])){
            //根据所选二级分类获取到旗下所有分类
            $cate_ids = D('GoodsCategory','Logic')->getCateIds($request['two_cate_id']);
            if($cate_ids){
                $cate_ids .= $request['cate_id'];
                $where['g.cat_id'] = array('IN',$cate_ids);
            }else{
                $where['g.cat_id'] = $request['two_cate_id'];
            }
        }else{
            $where['g.cat_id'] = $request['three_cate_id'];
        }
        $mod = M('GroupBuy');
        $where['g.status'] = 2;//审核通过
        $where['g.is_buy'] = 1;//上架
        $count = $mod->alias('b')->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')->where($where)->count();
        $list['group_buy_list'] = $mod->alias('b')
            ->field('b.id AS group_buy_id,b.group_price,b.group_num,b.total,b.integral,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->where($where)
            ->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')
            ->order('b.create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['group_buy_list']){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$list);
        }
        foreach($list['group_buy_list'] as $k=>$v){
            $list['group_buy_list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['group_buy_list'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['group_buy_list'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['group_buy_list'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['group_buy_list'][$k]['ticket_buy_discount'] = '0';
            }
            //根据活动id取两个活动参加用户(获取最近的两个开团的人)
            $two_first = M('GroupBuyLog')->alias('l')
                ->field('l.id AS log_id,l.user_id,u.head_pic')
                ->join(C('DB_PREFIX').'user u ON u.id = l.user_id')
                ->where("l.group_buy_id = {$v['group_buy_id']}")
                ->order('l.start_time DESC')
                ->limit(2)->select();
            foreach($two_first as $k1=>$v1){
                $two_first[$k1]['head_pic'] = D('File')->getOneFilePath($v1['head_pic']);
            }
            $list['group_buy_list'][$k]['append_person'] = $two_first ? $two_first : array();
        }

        apiResponse('1','获取成功',$list,$count);
    }
    /**
     * 获取商品详情页信息
     * @param int $GroupBuy_id
     */
    public function groupBuyInfo($request = array(),$user_id = 0){
        $goods_id = getName('GroupBuy','goods_id',$request['group_buy_id']);
        //调用商品详情函数
        $info = D('Goods','Logic')->goodsInfo($goods_id,$user_id);
        //找到当前商品团购的 组团信息 查找待成团的人
//        $where['l.status'] = 0;
//        $where['l.group_buy_id'] = $request['group_buy_id'];
        //先查出团购小组
        $where['p_id'] = array('eq',0);
        $where['order_type'] = 2;
        $where['order_status'] = array('in','1,2,6');
        $list = M('group_buy_order')
            ->where($where)
            ->field('id,create_time as start_time,order_status as status,group_buy_id,user_id')
            ->select();
//        apiResponse(1,'',$list);
//        $list = M('GroupBuyLog')->alias('l')->field('l.*,g.group_num')
//            ->join(C('DB_PREFIX').'group_buy g ON g.id=l.group_buy_id','LEFT')
//            ->where($where)
////            ->limit(2)
//            ->select();
        //在查出小组中的信息
        $list = $list?$list:array();
        foreach($list as $k=>$v){

            if($v['status'] == 1){
                $v['status'] = 0;
            }
            if($v['status'] == 2){
                $v['status'] = 1;
            }
            if($v['status'] ==6){
                $v['status'] = 2;
            }
            //开团时间
            $list[$k]['start_time'] = $v['start_time']+(3600*12).'';

//            $u = M('LogUsers')->field('user_id')->where('log_id='.$v['id'])->order('create_time ASC')->limit(1)->select()[0];
            $list[$k]['head_user']['head_pic'] = D('File')->getOneFilePath((getName('User','head_pic',$v['user_id'])));
            $list[$k]['head_user']['nickname'] = getName('User','nickname',$v['user_id']);
            //查出还缺几人
//            $diff = $v['group_num']-(M('LogUsers')->where("log_id={$v['id']}")->count());
            $diff = M('group_buy_order')->where(array('p_id'=>$v['id']))->count();
            $group_num = M('group_buy')->where(array('id'=>$v['group_buy_id']))->getField('group_num');
            $num = $group_num - $diff - 1;
            $list[$k]['diff'] = $num == 0?'团已满': '还差' .$num .'人';
        }
        $info['group'] = $list;
        $info['group_price'] = M('GroupBuy')->where("id={$request['group_buy_id']}")->getField('group_price');
        $info['one_price'] = M('GroupBuy')->where("id={$request['group_buy_id']}")->getField('one_price');
        $info['total'] = M('GroupBuy')->where("id={$request['group_buy_id']}")->getField('total');
        apiResponse('1','获取数据成功',$info,2);
    }

    /**
     * 单独购买
     * @param array $request
     * @param int $user_id
     */
    public function buyToOne($request = array(),$user_id = 0){
        $info = M('GroupBuy')->where("id = {$request['group_buy_id']}")->find();
        //获取到原价
        $shop_price = getName('Goods','shop_price',$info['goods_id']);
        //获取到属性id（之后拿购买模块的）

        //生成订单
    }

    /**
     * 一键开团
     * @param array $request
     * @param int $user_id
     */
    public function justBegin($request = array(),$user_id = 0){
        //判断是否已经参加的团购完成
        $status = M('GroupBuyLog')->alias('g')
            ->join(C('DB_PREFIX').'log_users u ON u.log_id=g.id')
            ->where("g.group_buy_id = {$request['group_buy_id']} AND u.user_id = {$user_id}")
            ->getField('g.status');
        if($status){
            if($status != '2'){
                //判断是否付款
                apiResponse('0','此商品您有未完成订单');
            }
        }
        //跳转到订单支付页面,执行付款操作，支付成功完就开团

        //M('LogUsers')  M('group_buy_log')
        apiResponse('1','请求开团成功',array('type'=>'1','group_buy_id'=>$request['group_buy_id']));
    }

    /**
     * 拼团付款完成回调的函数
     * type 1 开团 2参团
     * group_buy_id
     * log_id(参团才会有 默认)
     *
     */
    function doPayGroup($request = array(),$user_id){
        if($request['type']== '1'){
            //开团  获取到团购id
            $data['group_buy_id'] = $request['group_buy_id'];
            $data['start_time'] = time();
            $data['status'] = 0;
            $data['user_id'] = $user_id;
            M('GroupBuyLog')->startTrans();//启用回滚
            $id = M('GroupBuyLog')->add($data);
            if($id){
                //将记录表id添加到订单表中
                M('Order')->where("id = {$request['order_id']}")->save(array("log_id"=>$id));
                //设置参团人记录表
                $p_data['user_id'] = $user_id;
                $p_data['log_id'] = $id;
                $p_data['is_first'] = 1;
                $p_data['create_time'] = time();
                $res = M('LogUsers')->add($p_data);
                if($res){
                    //开团数增加
                    M('GroupBuy')->setInc("total");
                    M('GroupBuyLog')->commit();
                    apiResponse('1','开团成功',array('log_id'=>$id));
                }else{
                    M('GroupBuyLog')->rollback();//失败回滚
                    apiResponse('0','开团失败');
                }
            }else{
                M('GroupBuyLog')->rollback();//失败回滚
                apiResponse('0','开团失败');
            }
        }elseif($request['type']== '2'){
            //参团
            if(!$request['log_id']){
                apiResponse('0','请输入参团id');
            }
            $data['log_id'] = $request['log_id'];
            $data['user_id'] = $user_id;
            $data['is_first'] = 0;
            $data['create_time'] = time();
            $res = M('LogUsers')->add($data);
            if($res){
                //判断当前参团人数
                $true_num = M('LogUsers')->where("log_id = {$request['log_id']}")->count();
                //判断当前团是否人数已满
                $num = M('GroupBuy')->where("id = {$request['group_buy_id']}")->getField('group_num');
                if(($num - $true_num) === 0){
                    //修改订单表种状态 变为已成团
                    M('Order')->where("active_id = {$request['group_buy_id']} AND log_id={$request['log_id']}")->save(array("order_status"=>2));//已成团
                }
                apiResponse('1','参团成功，请耐心等待');
            }else{
                apiResponse('1','参团失败，发生未知错误');
            }
        }else{
            apiResponse('0','请输入团购参与类型');
        }
    }
    /**
     * 参团页
     */
    public function goGroup($request = array()){
        $info = M('GroupBuyLog')->alias('l')
            ->field('l.id AS log_id,l.user_id,g.group_num,g.group_price,g.total,g.goods_id')
            ->join(C('DB_PREFIX').'group_buy g ON g.id=l.group_buy_id','LEFT')
            ->where("l.id = {$request['log_id']}")
            ->find();
        if(!$info){
            apiResponse('0','获取参团信息失败');
        }
        //获取商品信息
        $g_info = M('Goods')->field('goods_img,goods_name')->where("id = {$info['goods_id']}")->find();
        $info['goods_img'] = D('File')->getOneFilePath($g_info['goods_img']);
        $info['goods_name'] = $g_info['goods_name'];
        //根据ID获取到现在团里面的人
        $person = M('LogUsers')->alias('l')->field('u.head_pic,u.nickname,l.is_first')
            ->join(C('DB_PREFIX').'user u ON u.id = l.user_id')
            ->where("l.log_id = {$request['log_id']}")
            ->select();
        if($person){
            foreach($person as $k=>$v){
                $person[$k]['head_pic'] = D('File')->getOneFilePath($v['head_pic']);
            }
        }else{
            apiResponse('0','获取参团信息失败');
        }
        $list['info'] = $info;
        $list['person'] = $person;
        $diff = $info['group_num']-(count($person));
        $list['diff'] = $diff==0?'团已满':'还差'.$diff.'人';
        $list['log_id'] = $request['log_id'];
        //拼团须知
        $list['rule'] = '拼团须知';
        apiResponse('1','获取成功',$list);
    }

    /**
     * 参加团操作(跳转到付款页面)
     * @param array $request
     * @param int $user_id
     */
    public function addGroup($request = array(),$user_id = 0){
        //参加记录表id
        //判断当前参团人数
        $true_num = M('LogUsers')->where("log_id = {$request['log_id']}")->count();
        //获取需要参团人数
        $group_buy_id = getName('GroupBuyLog','group_buy_id',$request['log_id']);
        $num = M('GroupBuy')->where("id = {$group_buy_id}")->getField('group_num');
        if(($num - $true_num) <= 0){
            apiResponse('0','您下手晚了，团已满');
        }
        //跳转到支付页面
        $res_data['type'] = '2';
        $res_data['group_buy_id'] = $group_buy_id;
        $res_data['log_id'] = $request['log_id'];
        apiResponse('1','参加团请求成功',$res_data);
    }

}