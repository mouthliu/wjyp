<?php
namespace Api\Logic;

/**
 * Class IndexLogic
 * @package Api\Logic
 * 逻辑层  首页
 *
 */
class IndexLogic extends BaseLogic{

    /**
     * 获取首页信息
     * @param array $request
     */
    public function index($request = array(),$user_id){
        //获取消息提醒数
        $list['msg_tip'] = D('UserMessage','Logic')->getTips($user_id);

        //1.获取广告
        $index_banner = D('Ads','Logic')->adsList(array('num'=>3,'position'=>'1'));
        $list['index_banner'] = $index_banner?$index_banner:array();

        //2.获取顶级分类
        $list['top_nav'] = D('GoodsCategory','Logic')->topNav()['list'];
        $list['top_nav'] = $list['top_nav']?$list['top_nav']:array();
        $first = array('cate_id'=>0,'short_name'=>'推荐','name'=>'推荐');
        array_unshift($list['top_nav'],$first);
        //3.获取头条
        $hed = M('Headlines')->field("id AS headlines_id,title")->where("is_index_show=1")->order("sort DESC")->limit(10)->select();
        $list['headlines'] = empty($hed)?array():$hed;
        //广告位置图片 统一获取
        $ads_position = array(2,3,7,8,9,11,12,13,14,15,16,17);
        $ads_list = D('Ads','Logic')->adsList(array('num'=>count($ads_position),'position'=>array('IN',$ads_position)));
        $new_ads = array();
        foreach($ads_list as $k1=>$v1){
            $new_ads[$v1['position']] = $v1;
        }
        //4.获取品牌团，中国制造，科技前沿的图
        $list['three_img']['brand'] = dealAds($new_ads['13']);
        $list['three_img']['china'] = dealAds($new_ads['14']);
        $list['three_img']['science'] = dealAds($new_ads['15']);

        //5.限量购区域
        $list['limit_buy']['ads'] = dealAds($new_ads['12']);
        //根据当前时间获取到场次信息
        $hour = date('H');
        $l_where['start_time'] = array('elt',$hour);
        $l_where['end_time'] = array('gt',$hour);
        $stage_id = M('LimitStage')->where($l_where)->getField('id');
        $limitWhere['b.begin_stage'] = $stage_id;
        $limitWhere['b.status'] = 2;
        $limitWhere['b.date'] = date('Y-m-d');
        $limitWhere['g.status'] = 2;
        $limitWhere['g.is_buy'] = 1;
        $list['limit_buy']['goodsList'] = M('LimitBuy')->alias('b')
            ->field('b.id AS limit_buy_id,b.limit_price,b.begin_stage,b.limit_num,b.integral,b.sell_num,b.limit_store,g.market_price,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')
            ->where($limitWhere)
            ->limit(4)
            ->select();
        foreach($list['limit_buy']['goodsList'] as $k=>$v){
            $list['limit_buy']['goodsList'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //根据stage_id 获取到结束时间
            $stage_time = M('LimitStage')->field('start_time,end_time')->where("id = {$v['begin_stage']}")->find();
            $start_time = $stage_time['start_time'] ? $stage_time['start_time'].':00' : '';
            $list['limit_buy']['goodsList'][$k]['start_time'] = strtotime(date('Y-m-d').' '.$start_time).'';
            $end_time = $stage_time['end_time'] ? $stage_time['end_time'].':00' : '';
            $list['limit_buy']['goodsList'][$k]['end_time'] = strtotime(date('Y-m-d').' '.$end_time).'';
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['limit_buy']['goodsList'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['limit_buy']['goodsList'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['limit_buy']['goodsList'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['limit_buy']['goodsList'][$k]['ticket_buy_discount'] = '0';
            }
        }
        //6.票券区
        $list['ticket_buy']['ads'] = dealAds($new_ads['11']);
        $ticketWhere['g.ticket_buy_id'] = array('gt',0);
        $ticketWhere['g.status'] = 2;
        $ticketWhere['g.is_buy'] = 1;
        $list['ticket_buy']['goodsList'] = M('Goods')->alias('g')
            ->field('g.id AS goods_id,g.goods_name,g.goods_img,g.sell_num,g.shop_price,g.market_price,g.country_id,b.id AS ticket_buy_id,b.discount AS ticket_buy_discount')
            ->join(C('DB_PREFIX').'ticket_buy b ON b.goods_id=g.id')
            ->where($ticketWhere)
            ->order('b.sort DESC')
            ->limit(4)
            ->select();
        foreach($list['ticket_buy']['goodsList'] as $k=>$v){
            $list['ticket_buy']['goodsList'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['ticket_buy']['goodsList'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['ticket_buy']['goodsList'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
        }
        //7.无界预购
        $list['pre_buy']['ads'] = dealAds($new_ads['9']);
        $preWhere['b.status'] = 2;
        $preWhere['g.status'] = 2;
        $preWhere['g.is_buy'] = 1;
        $list['pre_buy']['goodsList'] = M('PreBuy')->alias('b')
            ->field('b.id AS pre_buy_id,b.deposit,b.pre_store,b.sell_num,b.success_max_num,b.start_time,b.end_time,b.integral,g.market_price,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')
            ->where($preWhere)
            ->order('b.create_time DESC')
            ->limit(4)
            ->select();
        foreach($list['pre_buy']['goodsList'] as $k=>$v){
            $list['pre_buy']['goodsList'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['pre_buy']['goodsList'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['pre_buy']['goodsList'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['pre_buy']['goodsList'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['pre_buy']['goodsList'][$k]['ticket_buy_discount'] = '0';
            }
        }
        //8.进口馆
        $list['country']['ads'] = dealAds($new_ads['7']);
        $cWhere['g.status'] = 2;//审核通过
        $cWhere['g.is_buy'] = 1;//上架
        $cWhere['g.country_id'] = array('gt',0);//获取到非中国的
        $list['country']['goodsList'] = M('Goods')->alias('g')
            ->field('g.id AS goods_id,g.goods_name,g.goods_img,g.sell_num,g.market_price,g.shop_price,g.integral,g.country_id,g.ticket_buy_id,c.country_logo')
            ->join(C('DB_PREFIX').'country c ON c.id=g.country_id')
            ->where($cWhere)
            ->order('is_recommend DESC')
            ->limit(4)
            ->select();
        foreach($list['country']['goodsList'] as $k=>$v){
            $list['country']['goodsList'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            $list['country']['goodsList'][$k]['country_logo'] = D('File')->getOneFilePath($v['country_logo']);
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']>0){
                $list['country']['goodsList'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['country']['goodsList'][$k]['ticket_buy_discount'] = '0';
            }
        }
        //9.竞拍汇
        $list['auction']['ads'] = dealAds($new_ads['2']);
        $aucWhere['start_time'] = array('elt',strtotime(date('Y-m-d')));
        $aucWhere['end_time'] = array('egt',strtotime(date('Y-m-d').' 23:59:59'));
        $aucWhere['a.status'] = 2;
        $aucWhere['g.status'] = 2;
        $aucWhere['g.is_buy'] = 1;
        $list['auction']['goodsList'] = M('Auction')->alias('a')
            ->field('a.id AS auction_id,a.start_time,a.end_time,a.integral,a.start_price,g.market_price,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->join(C('DB_PREFIX').'goods g ON a.goods_id=g.id')
            ->where($aucWhere)
            ->order('a.end_time DESC')
            ->limit(4)
            ->select();
        foreach($list['auction']['goodsList'] as $k=>$v){
            $list['auction']['goodsList'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['auction']['goodsList'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['auction']['goodsList'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['auction']['goodsList'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['auction']['goodsList'][$k]['ticket_buy_discount'] = '0';
            }
        }
        //10.一元夺宝
        $list['one_buy']['ads'] = dealAds($new_ads['3']);
        $oneWhere['a.status'] = 2;
        $oneWhere['g.status'] = 2;
        $oneWhere['g.is_buy'] = 1;
        $list['one_buy']['goodsList'] = M('OneBuy')->alias('a')
            ->field('a.id AS one_buy_id,a.person_num,a.add_num,a.integral,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id,a.start_time,a.end_time')
            ->join(C('DB_PREFIX').'goods g ON a.goods_id=g.id')
            ->where($oneWhere)
            ->order('add_num DESC')
            ->limit(4)
            ->select();
        foreach($list['one_buy']['goodsList'] as $k=>$v){
            //统计出参加这个活动的人数
            $list['one_buy']['goodsList'][$k]['diff_num'] = $v['person_num'] - $v['add_num'];
            $list['one_buy']['goodsList'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['one_buy']['goodsList'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['one_buy']['goodsList'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['one_buy']['goodsList'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['one_buy']['goodsList'][$k]['ticket_buy_discount'] = '0';
            }
        }

        //11.汽车
        $list['car']['ads'] = dealAds($new_ads['16']);
        $list['car']['goodsList'] = M('CarBuy')->alias('c')
            ->field('c.id AS car_id,c.car_name,c.car_img,c.lng,c.lat,c.brand_id,c.pre_money,c.true_pre_money,c.all_price,c.integral,b.true_brand_logo')
            ->join(C('DB_PREFIX').'car_brand b ON b.id=c.brand_id')
            ->where("c.status = 1")
            ->order('create_time DESC')
            ->limit(4)
            ->select();
        foreach($list['car']['goodsList'] as $k=>$v){
            $list['car']['goodsList'][$k]['car_img'] = D('File')->getOneFilePath($v['car_img']);
            //根据经纬度算出当前距离
            $distance = getDistance($request['lat'],$request['lng'],$v['lat'],$v['lng']);
            $list['car']['goodsList'][$k]['distance'] = $distance ? $distance : '0';

            //票券
            $list['car']['goodsList'][$k]['ticket_discount'] = '0';
            //国家
            $list['car']['goodsList'][$k]['brand_logo'] = D('File')->getOneFilePath($v['true_brand_logo']);
        };

        //12.房产
        $list['house']['ads'] = dealAds($new_ads['17']);
        $houseWhere['status'] = 1;
        $list['house']['goodsList'] = M('HouseBuy')->field('id AS house_id,house_name,house_img,lng,lat,min_price,max_price,now_num,developer')
            ->where($houseWhere)
            ->order('now_num DESC')
            ->limit(4)
            ->select();
        foreach($list['house']['goodsList'] as $k=>$v){
            $list['house']['goodsList'][$k]['house_img'] = D('File')->getOneFilePath($v['house_img']);
            //根据经纬度算出当前距离
            $distance = getDistance($request['lat'],$request['lng'],$v['lat'],$v['lng']);
            $list['house']['goodsList'][$k]['distance'] = $distance ? $distance :'0';;
        }
        //13.拼单购
        $list['group_buy']['ads'] = dealAds($new_ads['8']);
        $gWhere['b.status'] = 2;
        $gWhere['g.status'] = 2;
        $gWhere['g.is_buy'] = 1;
        $list['group_buy']['goodsList'] = M('GroupBuy')->alias('b')
            ->field('b.id AS group_buy_id,b.group_price,b.group_num,b.total,b.integral,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id,g.shop_price')
            ->where($gWhere)
            ->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')
            ->order('b.create_time DESC')
            ->limit(4)
            ->select();
        foreach($list['group_buy']['goodsList'] as $k=>$v){
            $list['group_buy']['goodsList'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['group_buy']['goodsList'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['group_buy']['goodsList'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }

            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['group_buy']['goodsList'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
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
            $list['group_buy']['goodsList'][$k]['append_person'] = $two_first ? $two_first : array();
        }
        apiResponse('1','获取成功',$list);
    }
}

