<?php
namespace Api\Logic;

/**
 * Class CountryLogic
 * @package Api\Logic
 * 逻辑层  主题街模块
 *
 */
class CountryLogic extends BaseLogic{

    /**
     * 获取进口馆首页数据
     * @param array $request
     */
    public function countryIndex($request = array()){
        //.获取广告图
        $list['ads'] = dealAds(D('Ads','Logic')->adsList(array('num'=>1,'position'=>'26'))[0]);
        //1.获取国家列表
        $mod = M('Country');
        $where['status'] = 1;
        $list['country_list'] = $mod->field('id AS country_id,house_img,country_name')
            ->where($where)
            ->order('sort DESC')
            ->limit(10)
            ->select();
        if(!$list['country_list']){
            apiResponse('1','暂无国家数据',$list);
        }
        foreach($list['country_list'] as $k=>$v){
            $list['country_list'][$k]['house_img'] = D('File')->getOneFilePath($v['house_img']);
        }
        //获取商品
        //获取到当前国家logo
        $mod = M('Goods');
        //首先根据分类列出旗下所有分类id
        $where['status'] = 2;//审核通过
        $where['is_buy'] = 1;//上架
        $where['country_id'] = array('gt',0);//获取到非中国的
        $count = $mod->where($where)->count();
        $list['goods_list'] = $mod->field('id AS goods_id,goods_name,goods_img,market_price,sell_num,shop_price,integral,country_id,ticket_buy_id')
            ->where($where)
            ->order('sell_num DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['goods_list']){
            $msg = $request['p']==1?'暂无商品':'无更多商品';
            apiResponse('1',$msg,$list);
        }
        foreach($list['goods_list'] as $k=>$v){
            $list['goods_list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            $list['goods_list'][$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']>0){
                $list['goods_list'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['goods_list'][$k]['ticket_buy_discount'] = '0.0';
            }
        }
        apiResponse('1','获取数据成功',$list,$count);
    }

    /**
     * 获取商品列表
     * @param array $request
     * @return mixed
     */
    public function countryGoods($request = array()){
        D('Goods','Logic')->goodsList($request);
    }
    /**
     * 获取三级分类商品列表
     * @param array $request
     */
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
                $where['cat_id'] = array('IN',$cate_ids);
            }else{
                $where['cat_id'] = $request['two_cate_id'];
            }
        }else{
            $where['cat_id'] = $request['three_cate_id'];
        }
        $mod = M('Goods');
        $where['status'] = 2;//审核通过
        $where['is_buy'] = 1;//上架
        $where['country_id'] = array('IN',$request['country_id']);
        $count = $mod->where($where)->count();
        $list['list'] = $mod->field('id AS goods_id,goods_name,goods_img,market_price,shop_price,integral,sell_num,country_id,ticket_buy_id')
            ->where($where)
            ->order('sell_num DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['list']){
            $msg = $request['p'] == 1?'暂无商品数据':'无更多数据';
            apiResponse('1',$msg,$list);
        }
        $country_logo = D('File')->getOneFilePath(getName('Country','country_logo',$request['country_id']));
        foreach($list['list'] as $k=>$v){
            $list['list'][$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $list['list'][$k]['country_logo'] = $country_logo ? $country_logo : '';
            }else{
                $list['list'][$k]['country_logo'] = '';
            }
            //如果是票券区商品显示可使用的票券
            if($v['ticket_buy_id']){
                $list['list'][$k]['ticket_buy_discount'] = getName('TicketBuy','discount',$v['ticket_buy_id']);
            }else{
                $list['list'][$k]['ticket_buy_discount'] = '0';
            }
        }
        apiResponse('1','获取成功',$list,$count);
    }
}