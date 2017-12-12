<?php
namespace Api\Logic;

/**
 * Class ThemeLogic
 * @package Api\Logic
 * 逻辑层  主题街模块
 *
 */
class ThemeLogic extends BaseLogic{

    /**
     * 获取主题街列表
     * @param array $request
     */
    public function themeList($request = array()){
        $mod = M('Theme');
        //获取活动时间到的主题
        $where['start_time'] = array('lt',time());
        $where['end_time'] = array('gt',time());
        $where['status'] = 1;
        $count = $mod->where($where)->count();
        $list = $mod->field('id AS theme_id,theme_img,theme_name')
            ->where($where)
            ->order('create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('0',$msg);
        }
        foreach($list as $k=>$v){
            $list[$k]['theme_img'] = D('File')->getOneFilePath($v['theme_img']);
        }
        apiResponse('1','获取数据成功',$list,$count);
    }

    /**
     * 获取主题商品
     * @param array $request
     * @return mixed
     */
    public function themeGoods($request = array()){
        //获取到当前主题封面
        $path = D('File')->getOneFilePath(getName('Theme','theme_img',$request['theme_id']));
        $list['theme_img'] = $path?$path:C('API_URL').'/Uploads/Theme/default.png';
        $mod = M('Goods');
        //获取到该主题下的商品
        $where['status'] = 2;//审核通过
        $where['is_buy'] = 1;//上架
        $where['theme_id'] = $request['theme_id'];
        $count = $mod->where($where)->count();
        $list['list'] = $mod->field('id AS goods_id,goods_name,goods_img,market_price,shop_price,integral,sell_num,ticket_buy_id,country_id')
            ->where($where)
            ->order('sell_num DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['list']){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$list);
        }
        foreach($list['list'] as $k=>$v){
            $list['list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            if($v['country_id'] > 0){
                $list['list'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['list'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']>0){
                $list['list'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['list'][$k]['ticket_buy_discount'] = '0';
            }
        }
        apiResponse('1','获取成功',$list,$count);
    }
}