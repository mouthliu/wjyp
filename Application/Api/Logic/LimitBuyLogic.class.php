<?php
namespace Api\Logic;

/**
 * Class LimitBuyLogic
 * @package Api\Logic
 * 逻辑层  拍卖 模块
 *
 */
class LimitBuyLogic extends BaseLogic{

    /**
     * 获取限量购首页
     * @param array $request
     */

    public function limitBuyIndex($request = array(),$user_id = 0){
        //获取场次时间
        $stage_list = M('LimitStage')->field('id AS stage_id,stage_name,start_time,end_time')->select();
        foreach($stage_list as $k=>$v){
            $hour = date('H');
            if($hour >= $v['start_time'] && $hour < $v['end_time']){
                $stage_list[$k]['status'] = '抢购进行中';
            }elseif($hour < $v['start_time']){
                $stage_list[$k]['status'] = '即将开始';

            }elseif($hour >= $v['end_time']){
                $stage_list[$k]['status'] = '已结束';

            }
            $stage_list[$k]['start_time'] = $v['start_time'].':00';
            $stage_list[$k]['end_time'] = $v['end_time'].':00';
        }
        $list['stage_list'] = $stage_list ? $stage_list : array();
        //.获取广告
        $list['ads'] = D('Ads','Logic')->adsList(array('num'=>1,'position'=>'22'))[0];
        $mod = M('LimitBuy');
        $where['b.status'] = 2;
        $where['date'] = date('Y-m-d');
        //根据当前时间获取到该时间段内(默认选中单前时间段被)
        if(empty($request['stage_id'])){
            $hour = date('H');
            $l_where['start_time'] = array('elt',$hour);
            $l_where['end_time'] = array('gt',$hour);
            $stage_id = M('LimitStage')->where($l_where)->getField('id');
            //根据时间段筛选(判断当前时间，筛选出今天这个时间点之后的)
            if($stage_id){
                $where['begin_stage'] = $stage_id;
                $list['end_time'] = getName('LimitStage','end_time',$stage_id).':00';
                $list['start_time'] = getName('LimitStage','start_time',$stage_id).':00';
            }
        }else{
            $list['end_time'] = getName('LimitStage','end_time',$request['stage_id']).':00';
            $list['start_time'] = getName('LimitStage','start_time',$request['stage_id']).':00';
            $where['begin_stage'] = $request['stage_id'];
        }
        $count = $mod->alias('b')->where($where)->count();
        $list['limitBuyList'] = $mod->alias('b')
            ->field('b.id AS limit_buy_id,b.limit_price,b.limit_store,b.limit_num,b.date,b.begin_stage,b.integral,b.sell_num,g.market_price,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')
            ->where($where)
            ->page($request['p'],10)
            ->select();
        if(!$list['limitBuyList']){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$list);
        }
        foreach($list['limitBuyList'] as $k=>$v){
            //根据stage_id 获取到结束时间
            $start_time = getName('LimitStage','start_time',$v['begin_stage']).':00';
            $list['limitBuyList'][$k]['start_time'] = strtotime(date('Y-m-d').' '.$start_time).'';
            $end_time = getName('LimitStage','end_time',$v['begin_stage']).':00';
            $list['limitBuyList'][$k]['end_time'] = strtotime(date('Y-m-d').' '.$end_time).'';

            //判断是否设置提醒
            $id_remind = '0';
            if($user_id){
                $id_remind = M('Remind')->where("type = 2 AND user_id={$user_id} AND act_id={$v['limit_buy_id']} AND status=1")->find();
            }
            $list['limitBuyList'][$k]['is_remind'] = $id_remind ? '1':'0';

            $list['limitBuyList'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['limitBuyList'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['limitBuyList'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['limitBuyList'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['limitBuyList'][$k]['ticket_buy_discount'] = '0';
            }
            unset($list['limitBuyList'][$k]['date']);
            unset($list['limitBuyList'][$k]['date']);
            unset($list['limitBuyList'][$k]['begin_stage']);
        }
        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 获取限量购商品详情页信息
     * @param int $LimitBuy_id
     */
    public function limitBuyInfo($request = array(),$user_id = 0){
        $mod = M('LimitBuy');
        //调用商品详情函数
        $goods_id = getName('LimitBuy','goods_id',$request['limit_buy_id']);
        if(empty($goods_id)){
            apiResponse('0','信息查询失败');
        }
        $info = D('Goods','Logic')->goodsInfo($goods_id,$user_id);
        unset($info['goodsInfo']['shop_price']);

        //获取到限量购信息
        $where['id'] = $request['limit_buy_id'];

        $limit_info = $mod->alias('b')
            ->field('b.id AS limit_buy_id,b.limit_price,b.limit_store,b.limit_num,b.date,b.begin_stage,b.integral,b.sell_num')
            ->where($where)
            ->find();
        if(!$limit_info){
            apiResponse('0','获取限量购信息失败');
        }

        ///根据场次获取到时间
        //获取场次时间
        $stage_info = M('LimitStage')
            ->field('id AS stage_id,stage_name,start_time,end_time')
            ->where("id = {$limit_info['begin_stage']}")
            ->find();
        $hour = date('H');
        if($hour >= $stage_info['start_time'] && $hour < $stage_info['end_time']){
            $stage_info['status'] = '抢购进行中';

        }elseif($hour < $stage_info['start_time']){
            $stage_info['status'] = '即将开始';
        }elseif($hour >= $stage_info['end_time']){
            $stage_info['status'] = '已结束';
        }

        //根据stage_id 获取到结束时间
        $start_time = getName('LimitStage','start_time',$limit_info['begin_stage']).':00';
        $info['goodsInfo']['start_time'] = strtotime($limit_info['date'].' '.$start_time).'';
        $end_time = getName('LimitStage','end_time',$limit_info['begin_stage']).':00';
        $info['goodsInfo']['end_time'] = strtotime($limit_info['date'].' '.$end_time).'';

        //判断是否设置提醒
        $id_remind = '0';
        if($user_id){
            $id_remind = M('Remind')->where("type = 2 AND user_id={$user_id} AND act_id={$limit_info['limit_buy_id']} AND status=1")->getField('id');
        }
        $info['goodsInfo']['is_remind'] = $id_remind ? '1':'0';
        $info['goodsInfo']['limit_price'] = $limit_info['limit_price'];
        $info['goodsInfo']['limit_store'] = $limit_info['limit_store'];
        $info['goodsInfo']['sell_num'] = $limit_info['sell_num'];
        $info['goodsInfo']['integral'] = $limit_info['integral'];
        $info['goodsInfo']['stage_status'] = $stage_info['status'];

        apiResponse('1','获取数据成功',$info);
    }


    /**
     * 提醒函数
     * @param array $request
     * @param int $user_id
     */
    public function remindMe($request = array(),$user_id=0){
        $mod = D('Remind');
        $data['user_id'] = $user_id;
        $data['act_id'] = $request['limit_buy_id'];
        $data['type'] = 2;
        //判断是否有过提醒
        $data['status'] =1;
        if($mod->where($data)->getField('id')){
            apiResponse('1','您已经设置提醒,将在活动前十分钟提醒');
        }
        //提前十分钟提醒
        $info = M('LimitBuy')->where("id={$request['limit_buy_id']}")->find();
        $a = getName('LimitStage','start_time',$info['begin_stage']);
        $start_time = strtotime($info['date'].' '.$a.':00:00');
        $data['remind_time'] = $start_time-600;
        $id = $mod->data($data)->add();
        if($id){
            apiResponse('1','设置提醒成功,将在活动前十分钟提醒');
        }else{
            apiResponse('0','设置提醒失败');
        }
    }
    /**
     * 限量购记录表(付款成功(修改订单状态的时候)就记录ID )（可以用来限制id购买量）
     */
    public function addLog($request,$user_id){
        $data['limit_buy_id'] = $request['limit_buy_id'];
        $data['user_id'] = $user_id;
        $data['num'] = $request['goods_num'];
        $data['create_time'] = time();
        $data['order_id'] = $request['order_id'];
        $res = M('LimitBuyLog')->add($data);
        if($res){
            return true;
        }else{
            return false;
        }
    }
}