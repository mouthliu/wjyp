<?php
namespace Api\Logic;

/**
 * Class PreBuyLogic
 * @package Api\Logic
 * 逻辑层  商品信息模块
 *
 */
class PreBuyLogic extends BaseLogic{

    /**
     * 获取预购商品列表
     * @param array $request
     */
    public function preBuyIndex($request = array()){
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
        //2.获取广告
        $list['ads'] = dealAds(D('Ads','Logic')->adsList(array('num'=>1,'position'=>'25'))[0]);
        $mod = M('PreBuy');

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
        $count = $mod->alias('b')->where("status = 2")->count();
        $list['pre_buy_list'] = $mod->alias('b')
            ->field('b.id AS pre_buy_id,b.deposit,b.pre_store,b.success_max_num,b.sell_num,b.start_time,b.end_time,b.integral,g.market_price,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')
            ->where($where)
            ->order('b.create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['pre_buy_list']){
            $msg = $request['p']==1?'暂无数据':"无更多数据";
            apiResponse('1',$msg,$list);
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
        $mod = M('PreBuy');
        $where['g.ticket_buy_id'] = array('gt',0);
        $where['g.status'] = 2;//审核通过
        $where['g.is_buy'] = 1;//上架
        $count = $mod->alias('b')->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')->where($where)->count();
        $list['pre_buy_list'] = $mod->alias('b')
            ->field('b.id AS pre_buy_id,b.deposit,b.pre_store,b.sell_num,b.success_max_num,b.start_time,b.end_time,b.integral,g.market_price,g.goods_name,g.goods_img,g.country_id,g.ticket_buy_id')
            ->join(C('DB_PREFIX').'goods g ON b.goods_id=g.id')
            ->where($where)
            ->order('b.create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['pre_buy_list']){
            $msg = $request['p']==1?'暂无数据':"无更多数据";
            apiResponse('1',$msg,$list);
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
        apiResponse('1','获取成功',$list,$count);
    }
    /**
     * 获取商品详情页信息
     * @param int $PreBuy_id
     */
    public function preBuyInfo($request = array(),$user_id = 0){
        $mod = M('PreBuy');
        //调用商品详情函数
        $goods_id = getName('PreBuy','goods_id',$request['pre_buy_id']);
        $info = D('Goods','Logic')->goodsInfo($goods_id,$user_id);
        //获取到拍卖信息
        $where['b.id'] = $request['pre_buy_id'];

        $pre_buy_info = $mod->alias('b')
            ->field('b.id AS pre_buy_id,b.pre_price,b.deposit,b.end_delivery_date,b.sell_num,b.start_time,b.end_time,b.integral,b.desc,b.is_integral,b.integral_price,b.success_max_num')
            ->where($where)
            ->find();
        if(!$pre_buy_info){
            apiResponse('0','获取预购商品信息失败');
        }
//        unset($info['goodsInfo']['shop_price']);
        $time = time();
        if($time >= $pre_buy_info['start_time'] && $time < $pre_buy_info['end_time']){
            $pre_buy_info['status'] = '距离结束';

        }elseif($time < $pre_buy_info['start_time']){
            $pre_buy_info['status'] = '即将开始';
        }elseif($time >= $pre_buy_info['end_time']){
            $pre_buy_info['status'] = '已结束';
        }

        $info['goodsInfo']['pre_price'] = $pre_buy_info['pre_price'];
        $info['goodsInfo']['deposit'] = $pre_buy_info['deposit'];
        $info['goodsInfo']['end_delivery_date'] = date('Y/m/d',$pre_buy_info['end_delivery_date']);
        $info['goodsInfo']['sell_num'] = $pre_buy_info['sell_num'];
        $info['goodsInfo']['start_time'] = $pre_buy_info['start_time'];
        $info['goodsInfo']['end_time'] = $pre_buy_info['end_time'];
        $info['goodsInfo']['integral'] = $pre_buy_info['integral'];
        $info['goodsInfo']['desc'] = $pre_buy_info['desc'];
        $info['goodsInfo']['is_integral'] = $pre_buy_info['is_integral'];
        $info['goodsInfo']['integral_price'] = $pre_buy_info['integral_price'];
        $info['goodsInfo']['success_max_num'] = $pre_buy_info['success_max_num'];
        $info['goodsInfo']['stage_status'] = $pre_buy_info['status'];
        apiResponse('1','获取数据成功',$info);
    }
}