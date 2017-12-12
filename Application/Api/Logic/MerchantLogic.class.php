<?php
namespace Api\Logic;

/**
 * Class UserLogic
 * @package Api\Logic
 * 逻辑层  商家模块
 *
 */
class MerchantLogic extends BaseLogic{

    /**
     * 获取封面（一个商家 下面三个商品的那个）
     * @param int $merchant_id
     * @return bool
     */
    public function getFace($merchant_id = 0){
        //1.获取商家基本信息
        $info['merInfo'] = M('Merchant')
                        ->field('id AS merchant_id,merchant_name,merchant_desc,score,logo')
                        ->where("id={$merchant_id}")
                        ->find();
        $info['merInfo']['logo'] = D('File')->getOneFilePath($info['merInfo']['logo']);
        //2.获取封面商品信息
        $where['is_refer'] = 1;
        $where['merchant_id'] = $merchant_id;
        $where['status'] = 2;
        $where['is_buy'] = 1;
        $info['goodsList'] = M('Goods')
            ->field('id AS goods_id,goods_img,shop_price')
            ->where($where)
            ->limit(10)
            ->select();
        if($info['goodsList']){
            foreach($info['goodsList'] as $k=>$v){
                $info['goodsList'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            }
        }
        if($info){
            return $info;
        }else{
            return false;
        }
    }
    /**
     * 获取店铺首页信息
     * @param int $merchant_id
     */
    public function merIndex($request = array(),$user_id = 0){

        $info = $this->comFunc($request,$user_id);
        unset($info['goods_list']);
        //首页广告
        $ads = M('MerchantIndex')
            ->field('id AS ads_id,ads_pic,url,desc')
            ->where("merchant_id = {$request['merchant_id']} AND status=1")
            ->order('sort DESC')
            ->page($request['p'],10)
            ->select();
        $count = M('MerchantIndex')->where("merchant_id = {$request['merchant_id']} AND status=1")->count();
        $info['ads_list'] = array();
        if(!$ads){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$info);
        }
        foreach($ads as $k=>$v){
            $ads[$k]['ads_pic'] = D('File')->getOneFilePath($v['ads_pic']);
        }
        $info['ads_list'] = $ads;
        //添加访问量
        M('Merchant')->where("id = {$request['merchant_id']}")->setInc('visit_num');
        //足迹信息
        if(!empty($user_id)){
            D('User','Logic')->recordFoot(2,$user_id,$request['merchant_id']);
        }
        apiResponse('1','获取成功',$info,$count);
    }

    /**
     * 获取店铺详细信息
     * @param int $merchant_id
     * @param int $user_id
     */
    public function merInfo($merchant_id = 0,$user_id = 0){
        //判断该店铺是否存在
        $a = M('Merchant')->where("id={$merchant_id}")->find();
        if(!$a){
            apiResponse('0','无此店铺信息');
        }
        //判断该商品是否被收藏
        $is_col = array();
        if(!empty($user_id)){
            $is_col = M('UserCollect')->where("user_id={$user_id} AND type=2 AND id_val={$merchant_id}")->find();
        }
        $info['share_url'] = "http://wjyp.txunda.com";
        $info['share_img'] = D('File')->getOneFilePath($a['logo']);
        $info['share_content'] = $a['merchant_desc'];
        $info['is_collect'] = empty($is_col)?'0':'1';
        //店铺评价(获取到对店铺的评价)
        $info['mer_comment']['total'] = M('Comment')->where("merchant_id = {$merchant_id}")->count();
        $info['mer_comment']['score'] =  M('Merchant')->where("id = {$merchant_id}")->getField('score');
        //获取一条评论
        $one = M('Comment')->alias('c')
            ->field('c.user_id,c.nickname,c.create_time,c.all_star,u.head_pic')
            ->join(C('DB_PREFIX').'user u ON u.id = c.user_id')
            ->where("c.merchant_id = {$merchant_id}")
            ->order('c.create_time DESC')
            ->limit(1)
            ->select()[0];
        if($one['head_pic']){
            $one['head_pic'] = D('File')->getOneFilePath($one['head_pic']);
        }
        $one_arr = array(
            'user_id'=>'',
            'nickname'=>'',
            'create_time'=>'',
            'all_star'=>'',
            'head_pic'=>''
        );
        $info['mer_comment']['one_comment'] = empty($one)?$one_arr:$one;

        //店铺信息
        $where['merchant_id'] = $merchant_id;
        $where['status'] = 2;
        $where['is_buy'] = 1;
        $info['mer_info']['goods_total'] =  M('Goods')->where($where)->count().'';//商品总数
        $m_where['pay_time']  = array('gt',strtotime("-1 month"));
        $m_where['merchant_id'] = $merchant_id;
        $month = M('Order')->where($m_where)->sum('goods_num');
        $info['mer_info']['goods_month_num'] =  $month ? $month :'0';//商品月销量
        $info['mer_info']['view_num'] = M('UserCollect')->where("type=2 AND id_val={$merchant_id} AND status=1")->count();;
        $address = M('Merchant')->field('province_id,city_id,open_time,area_id,street_id,address,merchant_phone')->where("id={$merchant_id}")->find();
        $info['mer_info']['address'] = $address?getName('Region','region_name',$address['province_id']).getName('Region','region_name',$address['city_id']).getName('Region','region_name',$address['area_id']).$address['address']:'暂无地址';
        $info['mer_info']['phone'] = $address['merchant_phone'].'';
        $info['mer_info']['open_time'] = $address['open_time'].'';
        apiResponse('1','获取成功',$info);
    }
    /**
     * 获取店内商品列表
     * @param array()
     */
    public function goodsList($request = array(),$user_id = 0){
        $info = $this->comFunc($request,$user_id);
        $mod = M('Goods');
        //是否是最新上架
        if(empty($request['new_buy'])){
            $order = 'click_num DESC';
        }else{
            $order = "fresh_time,update_time DESC";
        }
        //热销排序
        if(!empty($request['is_hot'])){
            $order = "is_hot DESC";
        }
        $where['merchant_id'] = $request['merchant_id'];
        $list['list'] = $mod->field('id AS goods_id,goods_name,goods_img,market_price,shop_price,integral,sell_num,country_id,ticket_buy_id')
            ->where($where)
            ->order($order)
            ->page($request['p'],10)
            ->select();
        if(!$list['list']){
            $info['goods_list'] = array();
            $msg = $request['p']==1?'暂无数据':"无更多数据";
            apiResponse('1',$msg,$info);
        }
        foreach($list['list'] as $k=>$v){
            $list['list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $c_path = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
                $list['list'][$k]['country_logo'] = $c_path?$c_path:'';
            }else{
                $list['list'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['list'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['list'][$k]['ticket_buy_discount'] = '0';
            }
        }
        $info['goods_list'] = $list['list'];
       apiResponse('1','获取成功',$info);
    }
    /**
     * 获取店内限量购购商品列表
     * @param array()
     */
    public function limitList($request = array(),$user_id){
        $info = $this->comFunc($request,$user_id);
        $mod = M('LimitBuy');
        $where['b.status'] = 2;
        $where['date'] = array('egt',date('Y-m-d'));
        $count = $mod->alias('b')->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')->where($where)->count();
        $list['limit_buy_list'] = $mod->alias('b')
            ->field('b.id AS limit_buy_id,b.limit_price,b.limit_store,b.limit_num,b.date,b.begin_stage,b.integral,b.sell_num,g.market_price,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')
            ->where($where)
            ->order('b.create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['limit_buy_list']){
            $info['goods_list'] = array();
            $msg = $request['p']==1?'暂无数据':"无更多数据";
            apiResponse('1',$msg,$info);
        }
        foreach($list['limit_buy_list'] as $k=>$v){
            //根据stage_id 获取到开始时间 结束时间
            $start_time = getName('LimitStage','start_time',$v['begin_stage']).':00';
            $list['limit_buy_list'][$k]['start_time'] = strtotime($v['date'].' '.$start_time).'';
            $end_time = getName('LimitStage','end_time',$v['begin_stage']).':00';
            $list['limit_buy_list'][$k]['end_time'] = strtotime($v['date'].' '.$end_time).'';
            $list['limit_buy_list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['limit_buy_list'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['limit_buy_list'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['limit_buy_list'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['limit_buy_list'][$k]['ticket_buy_discount'] = '0';
            }
            unset($list['limit_buy_list'][$k]['date']);
            unset($list['limit_buy_list'][$k]['begin_stage']);
        }
        $info['goods_list'] = $list['limit_buy_list'];
        apiResponse('1','获取成功',$info,$count);
    }
    /**
     * 获取店内团购商品列表
     * @param array()
     */
    public function groupList($request = array(),$user_id){
        $info = $this->comFunc($request,$user_id);
        $mod = M('GroupBuy');
        $where['b.merchant_id'] = $request['merchant_id'];
        $where['b.status'] = 2;
        $count = $mod->alias('b')->where($where)->count();
        $list['group_buy_list'] = $mod->alias('b')
            ->field('b.id AS group_buy_id,b.group_price,b.group_num,b.total,b.integral,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->where($where)
            ->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')
            ->order('b.create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['group_buy_list']){
            $info['goods_list'] = array();
            $msg = $request['p']==1?'暂无数据':"无更多数据";
            apiResponse('1',$msg,$info);
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

        $info['goods_list'] = $list['group_buy_list'];
        apiResponse('1','获取成功',$info,$count);
    }

       /**
     * 获取店内预购商品列表
     * @param array()
     */
    public function preList($request = array(),$user_id = 0){
        $info = $this->comFunc($request,$user_id);
        $mod = M('PreBuy');
        $where['b.merchant_id'] = $request['merchant_id'];
        $where['b.status'] = 2;
        $count = $mod->alias('b')->where("status = 2")->count();
        $list['pre_buy_list'] = $mod->alias('b')
            ->field('b.id AS pre_buy_id,b.deposit,b.pre_store,b.sell_num,b.start_time,b.end_time,b.integral,g.market_price,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')
            ->where($where)
            ->order('b.create_time DESC')
            ->page($request['p'],10)
            ->select();

        if(!$list['pre_buy_list']){
            $info['goods_list'] = array();
            $msg = $request['p']==1?'暂无数据':"无更多数据";
            apiResponse('1',$msg,$info);
        }
        foreach($list['pre_buy_list'] as $k=>$v){
            $list['pre_buy_list'][$k]['goods_img'] =  D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['pre_buy_list'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['pre_buy_list'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['pre_buy_list'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['pre_buy_list'][$k]['ticket_buy_discount'] = '0';
            }
        }
        $info['goods_list'] = $list['pre_buy_list'];
        apiResponse('1','获取成功',$info,$count);
    }
    /**
     * 获取店内一元购商品列表
     * @param array()
     */
    public function oneBuyList($request = array(),$user_id = 0){
        $info = $this->comFunc($request,$user_id);
        $where['a.merchant_id'] = $request['merchant_id'];
        $mod = M('OneBuy');
        $where['a.status'] = 2;
        $count = $mod->alias('a')->where($where)->count();
        $list['one_buy_list'] = $mod->alias('a')
            ->field('a.id AS one_buy_id,a.person_num,a.add_num,a.integral,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->join(C('DB_PREFIX').'goods g ON a.goods_id=g.id')
            ->where($where)
            ->order('add_num DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['one_buy_list']){
            $info['goods_list'] = array();
            $msg = $request['p']==1?'暂无数据':"无更多数据";
            apiResponse('1',$msg,$info);
        }
        foreach($list['one_buy_list'] as $k=>$v){
            //统计出参加这个活动的人数
            $list['one_buy_list'][$k]['diff_num'] = $v['person_num'] - $v['add_num'];
            $list['one_buy_list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['one_buy_list'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['one_buy_list'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['one_buy_list'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['one_buy_list'][$k]['ticket_buy_discount'] = '0';
            }
        }
        $info['goods_list'] = $list['one_buy_list'];
        apiResponse('1','获取成功',$info,$count);
    }

    /**
     * 获取店内拍卖商品列表
     * @param array $request
     * @param int $user_id
     */
    public function auctionList($request = array(),$user_id = 0){
        $info = $this->comFunc($request,$user_id);
        $where['a.merchant_id'] = $request['merchant_id'];
        $mod = M('Auction');
        //查出今日的AuctionLogic.class.php
        $where['start_time'] = array('egt',strtotime(date('Y-m-d')));
        $where['end_time'] = array('elt',strtotime(date('Y-m-d').' 23:59:59'));

        $where['a.status'] = 2;
        $count = $mod->alias('a')->where($where)->count();
        $list['AuctionList'] = $mod->alias('a')
            ->field('a.id AS auction_id,a.start_price,g.market_price,a.start_time,a.end_time,a.integral,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->join(C('DB_PREFIX').'goods g ON a.goods_id=g.id')
            ->where($where)
            ->order('a.end_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['AuctionList']){
            $info['goods_list'] = array();
            $msg = $request['p']==1?'暂无数据':"无更多数据";
            apiResponse('1',$msg,$info);
        }
        foreach($list['AuctionList'] as $k=>$v){
            $list['AuctionList'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['AuctionList'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['AuctionList'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['AuctionList'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['AuctionList'][$k]['ticket_buy_discount'] = '0';
            }
        }

        $info['goods_list'] = $list['AuctionList'];
        apiResponse('1','获取成功',$info,$count);
    }
    /**
     * 店铺首页公功函数
     * @param array $request
     * @param int $user_id
     * @return mixed
     */
    function comFunc($request = array(),$user_id = 0){
        $is_col = array();
        if(!empty($user_id)){
            //判断该店是否被收藏
            $is_col = M('UserCollect')->where("user_id={$user_id} AND type=2 AND id_val={$request['merchant_id']} AND status=1")->find();
        }
        $info['is_collect'] = empty($is_col)?'0':'1';

        //获取店铺信息
        $merchant = M('Merchant')->where("id={$request['merchant_id']}")->find();
        if(!$merchant){
            apiResponse('0','店铺不存在');
        }
        $info['merchant_id'] = $merchant['id'];
        $info['merchant_name'] = $merchant['merchant_name']?$merchant['merchant_name']:'';
        $info['merchant_desc'] = $merchant['merchant_desc']?$merchant['merchant_desc']:'';
        $info['merchant_slogan'] = $merchant['slogan']?$merchant['slogan']:'';
        $info['logo'] = D("File")->getOneFilePath($merchant['logo']);
        //公告
        $announce = M('Announce')->field('id AS announce_id,title,content')
            ->where("parent_id = {$request['merchant_id']} AND status=1")
            ->order('update_time DESC')
            ->find();
        $info['announce'] = $announce['content'] ? $announce['content'] : '';
        //优惠券
        $ticket_list = $this->getTicket($request['merchant_id'],$user_id);
        $info['ticket_num'] = $ticket_list ? $ticket_list['total'] : '0';
        $info['ticket_list'] = $ticket_list ? $ticket_list['list'] : array();
        $info['goods_list'] = array();
        $info['share_url'] = "http://wjyp.txunda.com";
        $info['share_img'] = $info['logo'];
        $info['share_content'] = $info['merchant_desc'];
        return $info;
    }

    /**
     * 获取商家资质
     * @param $merchant_id
     */
    public function license($merchant_id){
        $info = M('Merchant')
            ->field("business_license,other_license")
            ->where("id = {$merchant_id}")->find();
        $business[] = array('name'=>'营业执照','status'=>1);
        $other = json_decode($info['other_license'],true);
        if(!$other){
            apiResponse('1','获取成功',$business);
        }
        foreach($other as $k=>$v){
            $business[] = array('name'=>$v['license_name'],'status'=>1);
        }
        apiResponse('1','获取成功',$business);
    }

    /**
     * 举报商家
     * @param array $request
     * @param int $user_id
     */
    public function report($request = array(),$user_id = 0){
        $mod = D('MerchantReport');
        $data['report_type_id'] = $request['report_type_id'];
        $data['report_content'] = $request['report_content'];
        $data['merchant_id'] = $request['merchant_id'];
        $data['user_id'] = $user_id;
        $data['create_time'] = time();
        $data['status'] = 0;
        $id = $mod->add($data);
        if($id){
            apiResponse('1','举报成功，我们会认真考虑你的举报');
        }else{
            apiResponse('0','举报失败');
        }
    }
    /**
     * 举报类型
     */
    public function reportType(){
        $mod = M('ReportType');
        $list = $mod->field('id AS report_type_id,title')->where('status = 1')->select();
        if(!$list){
            apiResponse('0','暂无数据');
        }
        apiResponse('1','获取成功',$list);
    }

    /**
     * 公共函数 获取店铺优惠券
     */
    public function getTicket($merchant_id,$user_id){
        $mod = M('Ticket');
        $where['t.end_time'] = array('gt',time());//未过期的优惠券
        $where['t.status'] = 1;//已发布的优惠券
        $where['t.limit_num'] = 1;
        $where['t.merchant_id'] = $merchant_id;
        $list['ticket_list'] = $mod->alias('t')
            ->field('t.id as ticket_id,t.ticket_name,t.ticket_desc,t.ticket_type,t.value,t.condition,t.merchant_id,t.end_time,t.start_time,t.ticket_num,t.use_num,m.logo')
            ->join(C('DB_PREFIX').'merchant m ON m.id = t.merchant_id')
            ->where($where)
            ->order('t.create_time DESC')
            ->select();
        if(!$list['ticket_list']){
            return array();
        }
        $count = $mod->alias('t')->join(C('DB_PREFIX').'merchant m ON m.id = t.merchant_id')->where($where)->count();
        //根据优惠券领取情况分组
        foreach($list['ticket_list'] as $k=>$v){
            // 根据商家id 获取到商家logo
            $list['ticket_list'][$k]['logo'] = D('File')->getOneFilePath($v['logo']);
            $id = '';
            if($user_id){
                //  判断我当前是否领过
                $id = M('UserTicket')->where("user_id={$user_id} AND ticket_id={$v['ticket_id']}")->getField('id');
            }
            if($id){
                $list['ticket_list'][$k]['is_get'] = '1';
            }else{
                $list['ticket_list'][$k]['is_get'] = '0';
            }
        }
        return array('list'=>$list['ticket_list'],'total'=>$count);
    }

    /**
     * 店内搜索
     */
    function merSearch($request = array(),$user_id){
        $info = $this->comFunc($request,$user_id);
        $request['p'] =  $request['p'] ? $request['p'] : '1';
        //搜商品
        $where['goods_name'] = array('LIKE','%'.$request['name'].'%');
        $where['status'] = 2;//审核通过
        $where['is_buy'] = 1;//上架
        $where['merchant_id'] = $request['merchant_id'];
        $list = D('Goods','Logic')->getGoodsList($where,$request['p']);
        $count = $list['count'];
        $info['search_content'] = $request['name'];
        $res = $list['list'];
        $info['goods_list'] = array();
        if(!$res['list']){
            $msg = $request['p']==1?'暂无商品搜索结果':'无更多商品搜索结果';
            apiResponse('0',$msg,$info);
        }
        $info['goods_list'] = $res['list'];
        apiResponse('1','搜索成功',$info,$count);
    }
}