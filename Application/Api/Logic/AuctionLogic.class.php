<?php
namespace Api\Logic;

/**
 * Class AuctionLogic
 * @package Api\Logic
 * 逻辑层  拍卖 模块
 *
 */
class AuctionLogic extends BaseLogic{

    /**
     * 获取拍卖商品首页(今日)
     * @param array $request
     */
    public function auctionIndex($request = array(),$user_id = 0){
        //获取广告
        $list['ads'] = D('Ads','Logic')->adsList(array('num'=>1,'position'=>'27'))[0];
        $mod = M('Auction');
        if($request['next'] != 1){
            //查出今日的AuctionLogic.class.php
            $where['start_time'] = array('elt',strtotime(date('Y-m-d')));
            $where['end_time'] = array('egt',strtotime(date('Y-m-d').' 23:59:59'));
        }else{
            //查出大于今天的
            $where['start_time'] = array('egt',strtotime(date('Y-m-d').' 23:59:59'));
        }
        $where['a.status'] = 2;
        $count = $mod->alias('a')->where($where)->count();

        $list['auction_list'] = $mod->alias('a')
            ->field('a.id AS auction_id,a.start_price,g.market_price,a.start_time,a.end_time,a.integral,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->join(C('DB_PREFIX').'goods g ON a.goods_id=g.id')
            ->where($where)
            ->order('a.end_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['auction_list']){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$list);
        }
        foreach($list['auction_list'] as $k=>$v){
            $id_remind = '0';
            if($user_id){
                //判断是否设置提醒
                $id_remind = M('Remind')->where("type = 1 AND user_id={$user_id} AND act_id={$v['auction_id']} AND status=1")->getField('id');
            }
            $list['auction_list'][$k]['is_remind'] = $id_remind ? '1':'0';
            $list['auction_list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['auction_list'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['auction_list'][$k]['country_logo'] = '';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['auction_list'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['auction_list'][$k]['ticket_buy_discount'] = '0';
            }
        }


        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 获取拍卖商品详情页信息
     * @param int $Auction_id
     */
    public function auctionInfo($request = array(),$user_id = 0){
        $goods_id = getName('Auction','goods_id',$request['auction_id']);
        //调用商品详情函数
        $info = D('Goods','Logic')->goodsInfo($goods_id,$user_id);

        //获取到拍卖信息
        $where['a.id'] = $request['auction_id'];
        $mod = M('Auction');
        $auctionInfo = $mod->alias('a')->where($where)->find();
        if(!$auctionInfo){
            apiResponse('0','获取信息失败');
        }
        $info['goodsInfo']['auct_name'] = $auctionInfo['auct_name'];
        $info['goodsInfo']['auct_desc'] = $auctionInfo['auct_desc'];
        $info['goodsInfo']['start_price'] = $auctionInfo['start_price'];
        $info['goodsInfo']['now_price'] = $auctionInfo['now_price'];
        $info['goodsInfo']['add_price'] = $auctionInfo['add_price'];
        $info['goodsInfo']['leave_price'] = $auctionInfo['leave_price'];
        $info['goodsInfo']['delay_time'] = $auctionInfo['delay_time'];
        $info['goodsInfo']['base_money'] = $auctionInfo['base_money'];

        $info['goodsInfo']['start_time'] = date('Y-m-d',$auctionInfo['start_time']);
        $info['goodsInfo']['end_time'] = date('Y-m-d',$auctionInfo['end_time']);

        $info['goodsInfo']['click_num'] = $auctionInfo['click_num'];
        $info['goodsInfo']['apply_num'] = $auctionInfo['apply_num'];
        $info['goodsInfo']['remind_num'] = $auctionInfo['remind_num'];
        if($auctionInfo['start_time']>time()){
            $info['goodsInfo']['stage_status'] = '即将开始 : '.date('Y-m-d',$auctionInfo['start_time']).'开始';
        }else if($auctionInfo['start_time']<time() && $auctionInfo['end_time']+86399>time()){
            $info['goodsInfo']['stage_status'] = '正在进行 : '.date('Y-m-d H:i:s',$auctionInfo['end_time']).'结束';
        }else{
            $info['goodsInfo']['stage_status'] = '已结束';
        }
        $id_remind = '0';
        if($user_id){
            //判断是否设置提醒
            $id_remind = M('Remind')->where("type = 1 AND user_id={$user_id} AND act_id={$auctionInfo['id']} AND status=1")->getField('id');
        }
        $info['goodsInfo']['is_remind'] = $id_remind ? '1':'0';
        //拍卖纪录
        //先判断有无自己的拍卖纪录
        if($user_id){
            $id = M('AuctionLog')->where("bid_user_id ={$user_id} AND auct_id={$auctionInfo['id']}")->getField('id');
            $info['mybid'] = $id?$id:'0';
        }else{
            $info['mybid'] = '0';
        }
        $tiao = ceil(($auctionInfo['one_price']-$auctionInfo['start_price'])/$auctionInfo['add_price']);
        $limit = $tiao ? $tiao : '10';

        //获取拍卖纪录
        $info_log = M('AuctionLog')->alias('a')
            ->field('a.id AS log_id,a.bid_price,a.bid_time,u.nickname')
            ->join(C('DB_PREFIX').'user u ON u.id=a.bid_user_id')
            ->where("a.auct_id = {$request['auction_id']} AND a.is_pay_base=1")
            ->order('a.bid_time DESC')
            ->limit($limit)
            ->select();
        if($info_log){
            foreach($info_log as $k=>$v){
                $info_log[$k]['bid_time_format'] = date('H:i:s',$v['bid_time']);
                $info_log[$k]['bid_time'] = date('H:i:s',$v['bid_time']);
            }
        }
        $info['auction_log'] = $info_log ? $info_log :array();

        D('Auction')->where("id={$request['auction_id']}")->setInc('click_num');
        apiResponse('1','获取数据成功',$info);
    }

    /**
     * 缴纳保证金函数
     */
    public function payBase(){

    }
    /**
     * 报名拍卖活动
     * @param array $request
     */
    public function applyAuction($request = array(),$user_id=0){
        $mod = M('Auction');
        $mod->startTrans();
        //获取到当前拍卖活动的信息
        $auction_info = M('Auction')
            ->field('id,auct_name,start_price,now_price,add_price,one_price,base_money,start_time,end_time')
            ->where("id={$request['auction_id']}")
            ->find();
        $start_time = $auction_info['start_time'];
        $end_time = $auction_info['end_time'];
        //判断活动是否正在进行中
        if( $start_time < time() && $end_time>time() ) {

        }else{
            apiResponse('0','拍卖活动已结束');
        }
        if($request['bid_price'] < $auction_info['start_price']){
            apiResponse('0','出价请不要低于起拍价'.$auction_info['start_price'].'元');
        }
        //加价幅度是否正确
        if($request['bid_price'] > $auction_info['now_price']){
            if(($request['bid_price']-$auction_info['now_price'])%($auction_info['add_price']) !== 0){
                apiResponse('0','加价幅度为'.$auction_info['add_price'].',当前价'.$auction_info['now_price'].'元');
            }
        }else{
            apiResponse('0','出价请不要低于当前价'.$auction_info['now_price'].'元');
        }
        //是否是一口价
        if($request['bid_price'] > $auction_info['one_price']){
            apiResponse('0','出价金额不能超过一口价'.$auction_info['one_price'].'元');
        }
        //判定这个用户是否已经出过价,如果已经出过价那么不需要再支付保证金，如果没有出过价格，在最终返回的时候需要返回需要支付保证金，
        $log_where['bid_user_id'] = $user_id;
        $log_where['auct_id'] = $request['auction_id'];
        $log_where['status'] = 0;
        $res = M('AuctionLog')->where($log_where)->order('bid_time DESC')->find();
        $is_pay = $res['is_pay_base'] ? '0' : '1';
        if(!$res){
            //如果查不到说明第一次
            $is_pay = '1';//需要支付保证金
        }
        //增加出价记录
        $add_data['auct_id'] = $request['auction_id'];
        $add_data['auct_name'] = getName('Auction','auct_name',$request['auction_id']);
        $add_data['bid_user_id'] = $user_id;
        $add_data['bid_price'] = $request['bid_price'];
        $add_data['bid_time'] = time();
        $add_data['status'] = 0;
        $add_data['is_pay_base'] = 1;
        $result = M('AuctionLog')->add($add_data);
        if($result){
            //出价成功之后把现有的价格加入到活动表中(仅限缴纳保证金的)(未缴纳保证金的需要在出价后再回来修改)
            if($is_pay != '1'){
                $res1 = M('Auction')->where("id={$request['auction_id']}")->save(array('now_price'=> $request['bid_price']));
                if(!$res1){
                    $mod->rollback();
                    apiResponse('0','出价失败');
                }
            }
            $res_data['is_need_pay'] = $is_pay;
            $res_data['base_money'] = $auction_info['base_money'];
            $res_data['bid_price'] = $request['bid_price'];
            $res_data['order_id'] = $result.'';
            $mod->commit();
            apiResponse('1','出价成功',$res_data);
        }else{
            $mod->rollback();
            apiResponse('0','出价失败');
        }
    }

    public function addLog($request = array(),$user_id){
        $data['bid_user_id'] = $user_id;
        $data['auct_id'] = $request['auction_id'];
        $data['auct_name'] = getName('Auction','auct_name',$request['auction_id']);
        $data['bid_price'] = $request['bid_price'];
        $data['bid_time'] = time();
        $bid_id = D('AuctionLog')->add($data);
        if($bid_id){
            //将记录表id添加到订单表中
            M('Order')->where("id = {$request['order_id']}")->save(array("log_id"=>$bid_id));
        }
        //报名数+1
        D('Auction')->where("id={$request['auction_id']}")->setInc('apply_num');
        apiResponse('1','参加竞拍成功',array('bid_id'=>$bid_id));
    }
    /**
     * 提醒函数
     * @param array $request
     * @param int $user_id
     */
    public function remindMe($request = array(),$user_id=0){
        $mod = D('Remind');
        //判断是否设置过提醒
        $res = $mod->where("act_id={$request['auction_id']} AND user_id={$user_id} AND type=1")->getField('id');
        if($res){
            apiResponse('1','您已经设置提醒,无需重复设置');
        }
        //提前十分钟提醒
        $start_time = M('Auction')->where("id={$request['auction_id']}")->getField('start_time');
        $data['remind_time'] = $start_time-600;
        $data['user_id'] = $user_id;
        $data['act_id'] = $request['auction_id'];
        $data['type'] = 1;
        $id = $mod->data($data)->add();
        if($id){
            //设置提醒人数+1
            D('Auction')->where("id={$request['auction_id']}")->setInc('remind_num');
             apiResponse('1','设置提醒成功,将在活动前十分钟提醒');
        }else{
            apiResponse('0','设置提醒失败');
        }
    }
}