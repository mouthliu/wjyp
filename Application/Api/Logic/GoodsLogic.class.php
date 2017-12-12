<?php
namespace Api\Logic;

/**
 * Class GoodsLogic
 * @package Api\Logic
 * 逻辑层  商品信息模块
 *
 */
class GoodsLogic extends BaseLogic{

    /**
     * 获取商品列表
     * @param array $request
     */
    public function goodsList($request = array()){
        if(!empty($request['country_id'])){//(为进口馆模块调用)
            $where['country_id'] = $request['country_id'];
            $list['title'] = getName('Country','country_name',$request['country_id']);
        }
        //1获取顶级分类列表
        $list['top_nav'] = D('GoodsCategory','Logic')->topNav()['list'];
        $list['top_nav'] = $list['top_nav']?$list['top_nav']:array();
        $first = array('cate_id'=>0,'short_name'=>'首页','name'=>'首页');
        array_unshift($list['top_nav'],$first);
        if(empty($request['cate_id'])){
            //获取到默认的第一个顶级分类(为其他模块调用)
            $request['cate_id'] = $list['top_nav'][1]['cate_id'];
        }
        //根据顶级分类获取到二级分类
        $list['two_cate_list'] = D('GoodsCategory','Logic')->getChildCate($request['cate_id'],1,'two_cate_id',1);
        //获取广告
        $list['ads'] = D('Ads','Logic')->adsList(array('num'=>1,'position'=>'19'))[0];
        $mod = M('Goods');
        $order = 'is_recommend DESC';//显示推荐商品
        //首先根据分类列出旗下所有分类id
        $cate_ids = D('GoodsCategory','Logic')->getCateIds($request['cate_id']);
        if($cate_ids){
            $cate_ids .= $request['cate_id'];
            $where['cat_id'] = array('IN',$cate_ids);
        }else{
            $where['cat_id'] = $request['cate_id'];
        }
        $where['status'] = 2;//审核通过
        $where['is_buy'] = 1;//上架
        $where['is_active'] = 0;//非活动专用
        $count = $mod->where($where)->count();
        $list['list'] = $mod->field('id AS goods_id,goods_name,goods_img,market_price,shop_price,integral,sell_num,country_id,ticket_buy_id')
            ->where($where)
            ->order($order)
            ->page($request['p'],10)
            ->select();
        if(!$list['list']){
            $msg = $request['p'] == 1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$list);
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
        $where['is_active'] = 0;//非活动专用
        $count = $mod->where($where)->count();
        $list['list'] = $mod->field('id AS goods_id,goods_name,goods_img,market_price,shop_price,integral,sell_num,country_id,ticket_buy_id')
            ->where($where)
            ->order('create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['list']){
            $msg = $request['p'] == 1?'暂无商品数据':'无更多数据';
            apiResponse('1',$msg,$list);
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
        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 获取商品详情页信息
     * @param int $goods_id
     */
    public function goodsInfo($goods_id = 0,$user_id = 0){
        $config = D('Config')->parseList();
        $is_col = array();
        $info['msg_tip'] = $info['cart_num'] = '0';//预先定义消息数量和购物车的数量都为0

        if(!empty($user_id)){
            //判断该商品是否被收藏
            $is_col = M('UserCollect')->where("user_id={$user_id} AND type=1 AND id_val={$goods_id} AND status = 1")->find();

            //获取消息提醒数
            $info['msg_tip'] = D('UserMessage','Logic')->getTips($user_id);

            //判断购物车数量
            $num = M('Cart')->where("user_id = {$user_id}")->count();
            $info['cart_num'] = $num ? $num :'0';
        }
        $info['is_collect'] = empty($is_col)?'0':'1';

        //1.获取商品的信息
        $info['goodsInfo'] = D('Goods')
            ->field('id AS goods_id,cat_id as cate_id,goods_name,market_price,shop_price,sell_num,cat_id,goods_img,share_content,integral,goods_desc,goods_brief,merchant_id,ticket_buy_id,country_id,country_desc,country_tax,server,discount,yellow_discount,blue_discount,is_new_goods,a_fee_new,is_end,end_date,goods_num,integral_buy_id,yellow_discount,blue_discount,package_list,after_sale_service,one_buy_id,pre_buy_id,auction_id,limit_buy_id,group_buy_id')
            ->where("id={$goods_id}")
            ->find();
        if(!$info['goodsInfo']){
            return false;
        }
        //获取商品的二级分类名称
        $two_cate_id = M('GoodsCategory')->where(array('id'=>$info['goodsInfo']['cate_id']))->getField('parent_id');
        $two_cate_id = $two_cate_id?$two_cate_id:0;
        $two_cate_name = M('GoodsCategory')->where(array('id'=>$two_cate_id))->getField('name');
        $info['goodsInfo']['two_cate_name'] = $two_cate_name?$two_cate_name:'二级分类';
        $info['goodsInfo']['cate_id'] = $two_cate_id;

        $info['goodsInfo']['sales'] = '0';//预定义销量为0
        $info['goodsInfo']['delivery_price'] = '0';//预定义运费为0
        //处理临期商品和旧货商品的描述
        if($info['goodsInfo']['is_new_goods']==0){
            $temp_data = $info['goodsInfo']['a_fee_new']*100;
            $info['goodsInfo']['is_new_goods_desc'] = '此件商品是旧货'.$temp_data.'成新';
        }else{
            $info['goodsInfo']['is_new_goods_desc'] = '';
        }
        if($info['goodsInfo']['is_end']==1){
            $info['goodsInfo']['is_end_desc'] = '此商品属于临期商品，商品保质期到期日为'.date('Y年m月d日').'。';
        }else{
            $info['goodsInfo']['is_end_desc'] = '';
        }

        //计算是否可以使用积分兑换该商品
        if($info['goodsInfo']['integral_buy_id']){
            $use_integral_info = M('IntegralBuy')->where(array('id'=>$info['goodsInfo']['integral_buy_id']))->find();
            if($use_integral_info){
                $info['goodsInfo']['use_integral'] = $use_integral_info['use_integral'];
            }else{
                $info['goodsInfo']['integral_buy_id'] = '0';
                $info['goodsInfo']['use_integral'] = '0';
            }
        }else{
            $info['goodsInfo']['use_integral'] = '0';
        }

        //新增:计算会员价格(无优会员,优享会员)
        $rate = getName('GoodsCategory','min_rate',$info['goodsInfo']['cat_id']);
        $info['goodsInfo']['wy_price'] = sprintf('%.2f',$info['goodsInfo']['shop_price']*(1-0.05+0.05*$rate));
        $info['goodsInfo']['yx_price'] = sprintf('%.2f',$info['goodsInfo']['shop_price']*(1-0.1+0.1*$rate));

        //获取价格说明
        $price_desc = M('GoodsPriceDesc')->where(array('status'=>array('neq',9)))->field('icon,price_name,desc')->select();
        $price_desc = $price_desc?$price_desc:array();
        foreach($price_desc as $k =>$v){
            $path = M('File')->where(array('id'=>$v['icon']))->getField('path');
            $price_desc[$k]['icon'] = $path?C('API_URL').$path:'';
        }
        $path = M('File')->where(array('id'=>$info['goodsInfo']['goods_img']))->getField('path');
        $info['goodsInfo']['goods_img'] = C('API_URL').$path;
        $info['goods_price_desc'] = $price_desc;//弹窗的价格说明

        $info['price_desc'] = $config['price_desc']; //商品详情的价格说明
        $info['vouchers_desc'] = $config['vouchers_desc'];//代金券说明

        //红 黄 蓝三种代金券处理
        $info['goodsInfo']['dj_ticket'] = $this->dealTicket($info,$user_id);

        //商品正在进行的活动
        $info['goodsInfo']['goods_active'] = $this->getGoodsAction($info);

        $info['goodsInfo']['country_desc'] = countryDesc($info['goodsInfo']['country_desc']);
        preg_match_all('/src=\"\/?(.*?)\"/', $info['goodsInfo']['goods_desc'], $match);
        foreach ($match[1] as $key => $src) {
            if (!strpos($src, '://')) {
                $info['goodsInfo']['goods_desc'] = str_replace('/' . $src, C('API_URL') . "/" . $src . "\" width=100%", $info['goodsInfo']['goods_desc']);
            }
        }

        //1.代金券
        if(!empty($info['goodsInfo']['ticket_buy_id'])){
            $info['goodsInfo']['ticket_buy_discount'] = getName('TicketBuy','discount',$info['goodsInfo']['ticket_buy_id']);
        }else{
            $info['goodsInfo']['ticket_buy_discount'] = '0';
        }

        //2.国家
        if(!empty($info['goodsInfo']['country_id'])){
            $c_path = D('File')->getOneFilePath(getName('Country','country_logo',$info['goodsInfo']['country_id']));
            $info['goodsInfo']['country_logo'] = $c_path?$c_path:'';
        }else{
            $info['goodsInfo']['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
        }

        //3.获取商家信息
        $info['mInfo'] = M('Merchant')
            ->field('id AS merchant_id,user_id,merchant_name,level,logo,view_num,easemob_account')
            ->where("id={$info['goodsInfo']['merchant_id']}")
            ->find();
        $mid = $info['mInfo']['merchant_id'];
        $info['mInfo']['view_num'] = M('UserCollect')->where("type=2 AND id_val={$mid} AND status=1")->count();
        $info['mInfo']['logo'] = D('File')->getOneFilePath($info['mInfo']['logo']);
        //获取商家的环信客服账号
        $user_info = M('User')->where(array('id'=>$info['mInfo']['user_id']))->find();
        $info['mInfo']['merchant_easemob_account'] = $user_info['easemob_account']?$user_info['easemob_account']:'';
        $head_pic = $user_info['head_pic']?$user_info['head_pic']:0;
        $path = M('File')->where(array('id'=>$head_pic))->getField('path');
        $info['mInfo']['merchant_head_pic'] = $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
        $info['mInfo']['merchant_nickname'] = $user_info['nickname']?$user_info['nickname']:'商家客服';

        //获取商家全部宝贝
        $info['mInfo']['all_goods'] = M('Goods')->where("merchant_id = {$info['mInfo']['merchant_id']} AND status=2 AND is_buy=1")->count();
        //获取评分
        $info['mInfo']['goods_score'] = '4.7';
        $info['mInfo']['merchant_score'] = '4.8';
        $info['mInfo']['shipping_score'] = '4.7';

        //4.获取店铺促销信息
        $time = time();
        $pro = M('promotion')->field("title,id AS promotion_id,type")->where("status = 1 AND merchant_id = {$info['mInfo']['merchant_id']} AND end_time>{$time}")->select();
        $info['promotion'] = empty($pro)?array():$pro;
        //5.获取该店的优惠券信息
        $info['ticketList'] = M('Ticket')
            ->field('id AS ticket_id,ticket_name,ticket_desc,ticket_type,value,condition,start_time,end_time')
            ->where("merchant_id = {$info['mInfo']['merchant_id']} AND status=1 AND end_time>{$time}")
            ->select();
        $info['ticketList'] = $info['ticketList']?$info['ticketList']:array();
        foreach($info['ticketList'] as $k => $v){
            //判断会员是否已经领取了该优惠券
            if($user_id){
                $user_ticket_id = M('UserTicket')->where(array('ticket_id'=>$v['ticket_id'],'user_id'=>$user_id))->getField('id');
                $info['ticketList'][$k]['get_receive']= $user_ticket_id?'1':'0';
            }else{
                $info['ticketList'][$k]['get_receive'] = '0';
            }
        }
        //6.获取规格信息
        $info['goods_common_attr'] = M('Attribute')->alias('a')
            ->field("a.id,a.attr_name,v.attr_value")
            ->join(C('DB_PREFIX').'goods_attr AS v ON v.attr_id = a.id')
            ->where("a.attr_type=2 AND a.status !=9 AND v.goods_id = {$goods_id}")
            ->select();

        //6.获取可选属性信息
        $goods_attr = M('Attribute')->alias('a')
            ->field("a.id,v.id as goods_attr_id,a.attr_name,v.attr_value,v.attr_price")
            ->join(C('DB_PREFIX').'goods_attr AS v ON v.attr_id = a.id')
            ->where("a.attr_type=1 AND a.status !=9 AND v.goods_id = {$goods_id}")
            ->select();

        $i = -1;
        $attr_id = 0;
        $new_goods_attr = array();
        foreach($goods_attr as $k=>$v){
            if($attr_id !=  $v['id']){
                $i++;
            }
            $new_goods_attr[$i]['attr_name'] = $v['attr_name'];
            $new_goods_attr[$i]['attr_list'][] = $v;
            $attr_id = $v['id'];
        }
        $info['goods_attr'] = $new_goods_attr;

        //7.获取商品相册
        $allPic = M("GoodsGallery")->where("goods_id={$goods_id}")->order('sort DESC')->select();
        $i = 0;
        $info['goods_banner'] = array();
        foreach($allPic as $k=>$v){
            if($v['goods_attr_name'] == '0'){
                $info['goods_banner'] = D('File')->getArrayFilePath(explode(",",$v['pictures']));
            }else{
                $attr_pic[$i]['goods_attr_id'] = $v['goods_attr_name'] ;
                $attr_pic[$i]['pic'] = D('File')->getOneFilePath(explode(",",$v['pictures'])[0]);
                $i++;
            }
        }
        $info['attr_images'] = empty($attr_pic)?array():$attr_pic;

        //8.获取属性组合 （货品）
        $attr_group = M('Products')->where("goods_id={$goods_id}")->field('id,goods_id,goods_attr as goods_attr_str,product_sn,product_number')->select();
        $info['product'] = empty($attr_group)?array():$attr_group;

        //9.商品评价（调取接口获取）
        $comment = D('Comment','Logic')->commentList(array('p'=>1,'goods_id'=>$goods_id));
        if(!empty($comment['list'])){
            $info['comment']['body'] = $comment['list'][0];
        }else{
            $info['comment']['body'] = array();
        }
        $info['comment']['total'] = $comment ? $comment['count'] : '0';

        //分享内容
        $info['share_url'] = "http://wjyp.txunda.com";
        $info['share_img'] = $info['goodsInfo']['goods_img'];
        $info['share_content'] = $info['goodsInfo']['goods_brief'];

        //运费(根据地区判断)需要获取当前地区和商品发货地区(根据店铺获取门店地址)
        $city = M('Merchant')->alias('m')->field('r.region_name')->join(C('DB_PREFIX').'region r ON r.id=m.city_id')->where("m.id={$mid}")->find();
        $info['send_city'] = $city['region_name'] ? $city['region_name'] : '您的位置';
        $info['send_fee'] = '10';

        //商品服务信息
        $server = $info['goodsInfo']['server'] ? $info['goodsInfo']['server'] : '';
        $server = M('GoodsServer')->field('id,server_name,desc,icon')
            ->where(array('id'=>array('IN',$server)))
            ->order('sort DESC')
            ->select();

        if($server){
            foreach($server as $k=>$v){
                $server[$k]['icon'] = D('File')->getOneFilePath($v['icon']);
            }
            $info['goods_server'] = $server;
        }else{
            $info['goods_server'] = array();
        }
        unset($info['goodsInfo']['server']);

        //获取搭配购
        unset($where);
        $where['goods_id'] = $info['goodsInfo']['goods_id'];
        $cheap_group_id_list = M('GroupGoods')->where($where)->getField('cheap_group_id',true);
        if($cheap_group_id_list){
            $id_list = array_unique($cheap_group_id_list);
            unset($where);
            $where['id'] = array('in',$id_list);
            $where['status'] = array('eq',1);
            $where['start_time'] = array('elt',time());
            $where['end_time'] = array('egt',time());
            $cheap_group_info = M('CheapGroup')->where($where)->field('id,group_name,group_price')->find();
            if($cheap_group_info){
                //获取当前搭配购的商品，
                unset($where);
                $where['cheap_group_id'] = $cheap_group_info['id'];
                $goods_id_list = M('GroupGoods')->where($where)->getField('goods_id',true);
                $goods = array();
                $index = 0;
                $goods_price = 0;
                $integral = 0;
                foreach($goods_id_list as $k => $v){
                    $goods_info = M('Goods')->where(array('id'=>$v))->find();
                    $goods[$index]['goods_img'] = D('File')->getOneFilePath($goods_info['goods_img']);
                    $goods[$index]['shop_price'] = $goods_info['shop_price'];
                    $goods_price = $goods_price+$goods_info['shop_price'];
                    $integral = $integral+$goods_info['integral'];
                    $index = $index+1;
                }
                $cheap_group_info['integral'] = $integral.'';
                $cheap_group_info['ticket_buy_discount'] = '10';
                $cheap_group_info['goods_price'] = $goods_price.'';
                $cheap_group_info['goods'] = $goods;
                $info['cheap_group'] = $cheap_group_info;
            }else{
                $info['cheap_group'] = array();
            }
        }else {
            $info['cheap_group'] = array();
        }
        $page = $_POST['p']?$_POST['p']:1;
        //猜你喜欢
        $ticketWhere['g.cat_id'] = $info['goodsInfo']['cat_id'];
        $ticketWhere['g.status'] = 2;
        $ticketWhere['g.is_buy'] = 1;
        $goods_list = M('Goods')->alias('g')
            ->field('g.id AS goods_id,g.goods_name,g.goods_img,g.sell_num,g.shop_price,g.market_price,g.country_id,b.id AS ticket_buy_id,b.discount AS ticket_buy_discount')
            ->join(C('DB_PREFIX').'ticket_buy b ON b.goods_id=g.id')
            ->where($ticketWhere)
            ->page($page.',10')
            ->select();
        $goods_list = $goods_list?$goods_list:array();
        foreach($goods_list as $k=>$v){
            $goods_list[$k]['goods_img'] = D('File')->getOneFilePath($v['goods_img']);
            //判断如果是外国显示图标
            if($v['country_id'] > 0){
                $goods_list[$k]['country_logo'] = D('File')->getOneFilePath(getName('Country','country_logo',$v['country_id']));
            }else{
                $goods_list[$k]['country_logo'] = C('API_URL').'/Uploads/Country/default.png';
            }
            $goods_list[$k]['ticket_buy_id'] = $v['ticket_buy_id']?$v['ticket_buy_id']:0;
            $goods_list[$k]['ticket_buy_discount'] = $v['ticket_buy_discount']?$v['ticket_buy_discount']:0;
        }
        $info['guess_goods_list'] = $goods_list;
        //添加足迹信息
        if(!empty($user_id)){
           D('User','Logic')->recordFoot(1,$user_id,$goods_id);
        }
        return $info;
    }

    /**
     * 搜索
     * @param array $request
     * @param int $user_id
     */
    public function search($request = array(),$user_id = 0){
        $res = array();
        $request['p'] =  $request['p'] ? $request['p'] : '1';
        if($request['type'] == '1'){
            //搜商品
            $where['goods_name'] = array('LIKE','%'.$request['name'].'%');
            $where['status'] = 2;//审核通过
            $where['is_buy'] = 1;//上架
            $where['is_active'] = 0;//非活动专用
            $list = $this->getGoodsList($where,$request['p']);
            $count = $list['count'];
            $res = $list['list'];
        }elseif($request['type'] == '2'){
            //搜商家getFace
            $where['merchant_name'] = array('LIKE','%'.$request['name'].'%');
            $where['status'] = 1;
            $m_list = M('Merchant')->field('id AS merchant_id')
                ->where($where)->page($request['p'],10)
                ->select();
            if($m_list){
                $count = count($m_list);
                foreach($m_list as $k=>$v){
                    $res['list'][$k] = D('Merchant','Logic')->getFace($v['merchant_id']);
                }
            }else{
                $count = '0';
                $res['list'] = array();
            }
        }
        $res['search_content'] = $request['name'];
        if(!$res['list']){
            $msg = $request['p']==1?'暂无搜索结果':'无更多搜索结果';
            apiResponse('0',$msg,$res);
        }
        apiResponse('1','搜索成功',$res,$count);
    }

    /**
     * 获取商品列表
     * @param array $request
     * $p 页数
     */
    function getGoodsList($where = array(),$p = 0){
        $mod = M('Goods');
        $count = $mod->where($where)->count();
        $list['list'] = $mod->field('id AS goods_id,goods_name,goods_img,market_price,shop_price,integral,sell_num,country_id,ticket_buy_id')
            ->where($where)
            ->order('create_time DESC')
            ->page($p,10)
            ->select();
        if(!$list['list']){
            return array();
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
        return array('list'=>$list,'count'=>$count);
    }

    //获取可使用购物券数量
    function getVoucherNum($type,$price,$user_id){
        if(!$user_id || !$price || !$type){
            return '0';
        }
        //首先判断该用户的购物券金额是否足够
        $mod = M('Vouchers');
        $where['user_id'] = $user_id;
        $where['type'] = $type;
        $where['status'] = 1;//正常
        $where['end_time'] = array('gt',time());//未过期
        $all_money = $mod->where($where)->sum('money');
        if(floatval($all_money) < $price){
            return '0';
        }
        $my_voucher = $mod->field('id,money,use_money')->where($where)->order('end_time DESC')->select();
        //进行挑选使用代金券（根据过期时间使用）
        $i = $need_money = 0;
        foreach($my_voucher as $k=>$v){
            $need_money += $v['money'];
            //判断金额是否达到
            if($need_money >= $price){
                return ++$i.'';
                break;
            }elseif($need_money < $price){
                $i++;
            }
        }
    }

    /**
     * 领取优惠券
     * 优惠券id：ticket_id
     */
    public function getTicket($request = array(),$user_id = 0){
        $where['ticket_id'] = $request['ticket_id'];
        $where['user_id'] = $user_id;
        $res = M('UserTicket')->where($where)->find();
        if($res){
            apiResponse('0','你已领取该优惠券');
        }
        unset($where);
        $where['id'] = $_POST['ticket_id'];
        $ticket_info = M('Ticket')->where($where)->find();
        if(!$ticket_info){
            apiResponse('0','优惠券查询失败');
        }
        $data['ticket_id'] = $request['ticket_id'];
        $data['user_id'] = $user_id;
        $data['status'] = 0;
        $data['add_time'] = time();
        $res = M('UserTicket')->data($data)->add();
        if($res){
            apiResponse('1','领取优惠券成功');
        }else{
            apiResponse('0','领取优惠券失败');
        }

    }

    /**
     * 优惠组合列表
     * @param array $request
     */
    public function groupGoodsList($request = array()){
        $goods_id = $request['goods_id'];
        //获取搭配购
        unset($where);
        $where['goods_id'] = $goods_id;
        $cheap_group_id_list = M('GroupGoods')->where($where)->getField('cheap_group_id',true);
        if($cheap_group_id_list){
            $id_list = array_unique($cheap_group_id_list);
            unset($where);
            $where['id'] = array('in',$id_list);
            $where['status'] = array('eq',1);
            $where['start_time'] = array('elt',time());
            $where['end_time'] = array('egt',time());
            $cheap_group_list = M('CheapGroup')->where($where)->field('id as cheap_group_id,group_name,group_price')->select();
            if($cheap_group_list){
                //获取当前搭配购的商品，
                foreach($cheap_group_list as $key => $value){
                    unset($where);
                    $where['cheap_group_id'] = $value['cheap_group_id'];
                    $goods_id_list = M('GroupGoods')->where($where)->getField('goods_id',true);
                    $goods = array();
                    $index = 0;
                    $goods_price = 0;
                    $integral = 0;
                    foreach($goods_id_list as $k => $v){
                        $goods_info = M('Goods')->where(array('id'=>$v))->find();
                        $goods[$index]['goods_id'] = $goods_info['id'];
                        $goods[$index]['goods_name'] = $goods_info['goods_name'];
                        $goods[$index]['goods_img'] = D('File')->getOneFilePath($goods_info['goods_img']);
                        $goods[$index]['shop_price'] = $goods_info['shop_price'];
                        $goods_price = $goods_price+$goods_info['shop_price'];
                        $integral = $integral+$goods_info['integral'];
                        $index = $index+1;
                    }
                    $cheap_group_list[$key]['integral'] = $integral.'';
                    $cheap_group_list[$key]['ticket_buy_discount'] = '10';
                    $cheap_group_list[$key]['goods_price'] = $goods_price.'';
                    $cheap_group_list[$key]['goods'] = $goods;
                }
            }else{
                $cheap_group_list = array();
            }
        }else {
            $cheap_group_list = array();
        }
        apiResponse('1','请求成功',$cheap_group_list);
    }
}