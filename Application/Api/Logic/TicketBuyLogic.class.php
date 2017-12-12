<?php
namespace Api\Logic;

/**
 * Class TicketBuyLogic
 * @package Api\Logic
 * 逻辑层  商品信息模块
 *
 */
class TicketBuyLogic extends BaseLogic{

    /**
     * 获取票券区商品列表
     * @param array $request
     */
    public function ticketBuyIndex($request = array()){
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
        $list['ads'] = dealAds(D('Ads','Logic')->adsList(array('num'=>1,'position'=>'23'))[0]);

        $mod = M('Goods');

        //首先根据分类列出旗下所有分类id
        $cate_ids = D('GoodsCategory','Logic')->getCateIds($request['cate_id']);
        if($cate_ids){
            $cate_ids .= $request['cate_id'];
            $where['g.cat_id'] = array('IN',$cate_ids);
        }else{
            $where['g.cat_id'] = $request['cate_id'];
        }
        $where['g.ticket_buy_id'] = array('gt',0);
        $where['g.status'] = 2;
        $where['b.is_recommend'] = 1;//显示推荐商品
        $count = $mod->alias('g')->join(C('DB_PREFIX').'ticket_buy b ON b.goods_id=g.id')->where($where)->count();
        $list['ticket_buy_list'] = $mod->alias('g')
            ->field('g.id AS goods_id,g.goods_name,g.goods_img,g.shop_price,g.market_price,g.sell_num,g.integral,g.country_id,b.id AS ticket_buy_id,b.discount AS ticket_buy_discount')
            ->join(C('DB_PREFIX').'ticket_buy b ON b.goods_id=g.id')
            ->where($where)
            ->order('b.sort DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['ticket_buy_list']){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$list);
        }
        foreach($list['ticket_buy_list'] as $k=>$v){
            $list['ticket_buy_list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['ticket_buy_list'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['ticket_buy_list'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
        }

        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 获取票券区商品详情页信息
     * @param int $TicketBuy_id
     */
    public function ticketBuyInfo($request = array(),$user_id = 0){
        $id = D('TicketBuy')->where("id={$request['ticket_buy_id']}")->getField('goods_id');
        $goods_id = $id ? $id : '0';
        //调用商品详情函数
        $info = D('Goods','Logic')->goodsInfo($goods_id,$user_id);
        if($info){
            apiResponse('1','获取数据成功',$info);
        }else{
            apiResponse('0','获取数据失败');
        }

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
        $mod = M('Goods');
        $where['g.ticket_buy_id'] = array('gt',0);
        $where['g.status'] = 2;//审核通过
        $where['g.is_buy'] = 1;//上架
        $count = $mod->alias('g')->where($where)->count();
        $list['ticket_buy_list'] = $mod->alias('g')
            ->field('g.id AS goods_id,g.goods_name,g.goods_img,g.country_id,g.integral,g.shop_price,g.market_price,g.sell_num,b.id AS ticket_buy_id,b.discount AS ticket_buy_discount')
            ->join(C('DB_PREFIX').'ticket_buy b ON b.goods_id=g.id')
            ->where($where)
            ->order('b.is_recommend DESC,b.sort DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['ticket_buy_list']){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$list);
        }
        foreach($list['ticket_buy_list'] as $k=>$v){
            $list['ticket_buy_list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['ticket_buy_list'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $list['ticket_buy_list'][$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
        }
        apiResponse('1','获取成功',$list,$count);
    }
}