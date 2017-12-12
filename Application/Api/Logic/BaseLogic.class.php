<?php
namespace Api\Logic;

/**
 * Class BaseLogic
 * @package Api\Logic
 * 逻辑层  父类
 *
 */
abstract class BaseLogic {
    /**
     * @param array $request  model 模型  ids操作的主键ID  status要改为的状态
     * @return bool
     * 修改状态
     */
    public function setStatus($request = array()) {

        //判断是数组ID还是字符ID
        if(is_array($request['ids'])) {
            //数组ID
            $where['id'] = array('in',$request['ids']);
            $ids = implode(',',$request['ids']);
        } elseif (is_numeric($request['ids'])) {
            //数字ID
            $where['id'] = $request['ids'];
            $ids = $request['ids'];
        }

        $data = array(
            'status'        => $request['status'],
            'update_time'   => time()
        );
        $result = D($request['model'])->where($where)->data($data)->save();
        if($result) {
            //行为日志
            api('Manager/ActionLog/actionLog', array('change_status',$request['model'],$ids,AID));
            //执行后操作
            if(!$this->afterSetStatus($result,$request)) { return false; }
            $this->setLogicSuccess('操作成功！'); return true;
        } else {
            $this->setLogicError('操作失败！'); return false;
        }
    }

    /**
     * 处理商品是否正在进行别的活动
     * @param $info
     * @return array
     */
    public function getGoodsAction($info){
        $goods_action = array();
        $index = 0;
        if($info['goodsInfo']['one_buy_id']){
            $goods_action[$index]['act_type'] = '1';
            $goods_action[$index]['act_id'] = $info['goodsInfo']['one_buy_id'];
            $goods_action[$index]['act_desc'] = '本商品正在进行免费抽奖活动';
            $index = $index+1;
        }
        if($info['goodsInfo']['pre_buy_id']){
            $goods_action[$index]['act_type'] = '2';
            $goods_action[$index]['act_id'] = $info['goodsInfo']['pre_buy_id'];
            $goods_action[$index]['act_desc'] = '本商品正在进行预购活动';
            $index = $index+1;
        }
        if($info['goodsInfo']['auction_id']){
            $goods_action[$index]['act_type'] = '3';
            $goods_action[$index]['act_id'] = $info['goodsInfo']['auction_id'];
            $goods_action[$index]['act_desc'] = '本商品正在进行拍卖活动';
            $index = $index+1;
        }
        if($info['goodsInfo']['limit_buy_id']){
            $goods_action[$index]['act_type'] = '4';
            $goods_action[$index]['act_id'] = $info['goodsInfo']['limit_buy_id'];
            $goods_action[$index]['act_desc'] = '本商品正在进行限量购活动';
            $index = $index+1;
        }
        if($info['goodsInfo']['group_buy_id']){
            $goods_action[$index]['act_type'] = '5';
            $goods_action[$index]['act_id'] = $info['goodsInfo']['group_buy_id'];
            $goods_action[$index]['act_desc'] = '显示本商品正在进行拼团购活动';
        }
        return $goods_action;
    }

    /**
     * 处理代金券
     */
    public function dealTicket($info,$user_id){
        $dj_ticket = array();
        $index = 0;
        if($info['goodsInfo']['discount']>0.01){
            $dj_ticket[$index]['discount_desc'] = '本产品最多可以使用'.$info['goodsInfo']['discount'].'%红券抵扣现金,'.'您现在有'.$this->getLastVouchers($user_id,1).'元红券';
            $dj_ticket[$index]['type'] = "1";//红券
            $index = $index+1;
        }
        if($info['goodsInfo']['yellow_discount']>0.01){
            $dj_ticket[$index]['discount_desc'] = '本产品最多可以使用'. $info['goodsInfo']['yellow_discount'].'%黄券抵扣现金,'.'您现在有'.$this->getLastVouchers($user_id,2).'元黄券';
            $dj_ticket[$index]['type'] = "2";//黄券
            $index = $index+1;
        }
        if($info['goodsInfo']['blue_discount']>0.01){
            $dj_ticket[$index]['type'] = "3";//蓝券
            $dj_ticket[$index]['discount_desc'] = '本产品最多可以使用'.$info['goodsInfo']['blue_discount'].'%蓝券抵扣现金,'.'您现在有'.$this->getLastVouchers($user_id,3).'元蓝券';
        }
        return $dj_ticket;
    }

    /**
     * 商品详情获取商品二级分类id
     */
    public function goodsInfoGetSecCateId($thr_id){
        $sec_id = M('GoodsCategory')->where(array('id'=>$thr_id))->getField('parent_id');
        return $sec_id;
    }

    /**
     * 获取剩余的代金券
     * type 1 红券 2黄券  3蓝券
     */
    public function getLastVouchers($user_id,$type){
        unset($where);
        $where['type'] = $type;
        $where['user_id'] = $user_id;
        $where['end_time'] = array('egt',time());
        $where['status'] = array('eq',1);
        $sum = M('Vouchers')->where($where)->sum('money');
        $use_sum = M('Vouchers')->where($where)->sum('use_money');
        return $sum-$use_sum;
    }
}