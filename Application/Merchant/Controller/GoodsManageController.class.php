<?php

namespace Merchant\Controller;

/**
 * Class AdministratorController
 * @package Merchant\Controller
 * 管理员控制器
 */
class GoodsManageController extends BaseController {
    protected function getIndexRelation(){
        $this->assign('breadcrumb_nav','hha');
        $this->assign("type_list",M('GoodsType')->field('id,type_name')->select());
    }
    protected function getUpdateRelation(){
        //根据商品id获取到该商品的属性
        $attr_img = M("Attribute")->query("SELECT a.id,v.id as aid,a.attr_name,v.attr_value FROM  ".C('DB_PREFIX')."attribute AS a LEFT JOIN ".C('DB_PREFIX')."goods_attr AS v ON v.attr_id = a.id  WHERE a.attr_type=1 AND a.status !=9 AND v.goods_id = {$_GET['id']} AND a.is_attr_gallery = 1 ");
//        dump($attr_img);
        //查询出商品的所用图片
        if(!empty($_GET['id'])){
            //获取到这个商品的所有图片
            $allPic = M("GoodsGallery")->where("goods_id={$_GET['id']}")->select();
            foreach($allPic as $k=>$v){
                $attr_pic[$v['goods_attr_name']]['id'] = $v['id'];
                $attr_pic[$v['goods_attr_name']]['pic'] = api('System/getFiles', array(explode(",",$v['pictures'])));
                $attr_pic[$v['goods_attr_name']]['sort'] = $v['sort'];
                $attr_pic[$v['goods_attr_name']]['is_show'] = $v['is_show'];
            }
            //处理图片 根据不同的属性
            $this->assign('attr_pic',$attr_pic);
        }
        $id = getMerchantId();

        //获取到分类 品牌 进口地
        $mylist = M('Merchant')->field("id,a_id,cates,brands,countrys,merchant_name,range_id")->where("id={$id}")->find();
        //获取招商人员
        $group = M('administrator')->where(array('id'=>$mylist['a_id']))->field('id as a_id,account')->find();
        $this->assign('group',$group);
        $this->assign("myinfo",$mylist);
        $this->assign("type_list",M('GoodsType')->field('id,type_name')->where("status=1")->select());
        $this->assign("attr_img",$attr_img);
        //根据商家所选的  第三级分类
        $this->assign("cate_list",D('Goods','Logic')->getArrayCates('0',$mylist['range_id']));
        //获取到国家
        $this->assign("country_list",M('Country')->field("id,country_name")->where("id IN ({$mylist['countrys']})")->order("sort DESC")->select());
        $this->assign("brand_list",M('GoodsBrand')->alias('g')->field('g.id,g.brand_name,f.path')->join(C('DB_PREFIX')."file AS f ON f.id=g.brand_logo")->where("g.id IN ({$mylist['brands']}) AND g.status=1")->select());
        $this->assign('server_list',M('GoodsServer')->where("status =1")->order('sort DESC')->select());
    }

    protected function getAddRelation(){
        $id = getMerchantId();
        //获取到分类 品牌 进口地
        $mylist = M('Merchant')->field("id,a_id,cates,brands,countrys,merchant_name,range_id")->where("id={$id}")->find();

        //获取招商人员
        $group = M('administrator')->where(array('id'=>$mylist['a_id']))->field('id as a_id,account')->find();
        $this->assign('group',$group);
        $this->assign("myinfo",$mylist);
        //根据商家所选的顶级分类获取到二三级分类
        $cate_list = D('Goods','Logic')->getArrayCates('0',$mylist['range_id']);
        $this->assign("cate_list",$cate_list);
        $this->assign("type_list",M('GoodsType')->field('id,type_name')->where("status = 1")->select());
        //获取到国家
        $this->assign("country_list",M('Country')->field("id,country_name")->where("id IN ({$mylist['countrys']})")->order("sort DESC")->select());
        $this->assign("brand_list",M('GoodsBrand')->alias('g')->field('g.id,g.brand_name,f.path')->join(C('DB_PREFIX')."file AS f ON f.id=g.brand_logo")->where("g.id IN ({$mylist['brands']}) AND g.status=1")->select());
        $this->assign('server_list',M('GoodsServer')->where("status =1")->order('sort DESC')->select());
    }
    // 获取商品类型下对应的属性  并ajax返回
    function getGoodsAttr(){
        //执行逻辑层
        D('Goods',"Logic")->getGoodsAttr();
    }

    function actUpdate(){
        if($_POST['is_end'] == '1'){
            if(empty($_POST['end_date'])){
                $this->error('此商品是临期商品，请填写过期时间'); return false;
            }else{
                $_POST['end_date'] = strtotime($_POST['end_date']);
            }
        }
//        $res = M('attract_goods')->where(array('a_id'=>$_POST['a_id'],'goods_code'=>$_POST['goods_code'],'lead_status'=>2))->find();
//        if(empty($res)){
//            $this->error('与预加商品信息不符,请联系招商人员!');return false;
//        }
        //执行逻辑层
        //判断该商品是否上架在售(之后再加)
//        $is_buy = M('Goods')->where("id={$_POST['id']}")->getField('is_buy');
//        if($is_buy=='1'){
//            $this->error('该商品正在售卖，请下架后在修改！'); return false;
//        }
        $res = D('Goods',"Logic")->actUpdate($_POST);
        if($res){
            $this->success("执行成功");
        }else{
            $this->error(D('Goods')->getError());
        }
    }

    //货品列表
    function products(){
        //根据id获取到商品的信息
        $ginfo = M("Goods")->alias('g')
            ->field('g.id,g.goods_name,g.goods_sn,g.goods_brief')
            ->where("id={$_GET['id']}")->find();

        $attr = M("Attribute")->query("SELECT a.id,v.id as aid,a.attr_name,v.attr_value
                        FROM  db_attribute AS a
                        LEFT JOIN db_goods_attr AS v
                        ON v.attr_id = a.id
                        WHERE a.attr_type=1 AND a.status !=9 AND v.goods_id = {$_GET['id']} ");

        //创建属性值对应数组
        foreach($attr as $k1=>$v1){
            $attr_arr[$v1['aid']] = $v1['attr_value'];
        }
        //将相同属性id的放一起
        foreach($attr as $k=>$v){
            $newAttr[$v['id']][] = $v;
        }
        $list = M("Products")->where("goods_id={$_GET['id']}")->select();

        $this->assign('attr_arr',$attr_arr);
        $this->assign("ginfo",$ginfo);
        $this->assign("newAttr",$newAttr);
        $this->assign("list",$list);
        $this->display('Goods/product');
    }
    //设置货品
    function setPruduct(){
        if(empty($_POST['id'])){
            $this->error("参数错误");
            return false;
        }
        $data['goods_id'] = $_POST['id'];
        $data['product_number'] = $_POST['product_num'];
        $data['goods_attr'] = implode('|',$_POST['attr']);
        //添加的时候判断这一类型是否存在
        $have = M('Products')->where("goods_attr='{$data['goods_attr']}'")->find();
        if($have){
            $this->error("该组合已存在");
            return false;
        }
        $id = M('Products')->add($data);
        if($id){
            $sn['product_sn'] = $_POST['product_sn'].'_P'.$id;
            M('Products')->where("id={$id}")->save($sn);
            $this->success("添加成功");
        }else{
            $this->error("添加失败");
        }
    }

    function setProductNum(){
        if(empty($_POST['id'])){
            $this->error("参数错误");
            return false;
        }
        $res = M('Products')->where("id={$_POST['id']}")->save($_POST);

        if($res){
            $this->success("修改成功");
        }else{
            $this->error("修改失败");
        }
    }
    //删除货品
    function delProduct(){
        $res = M('Products')->where("id={$_GET['id']}")->delete();
        if($res){
            $this->success("删除货品成功");
        }else{
            $this->error("删除货品失败");
        }
    }
    //上下架
    function setBuy(){
        $request = I('request.');

        if(!empty($request['id'])){
            //判断是否属于活动商品
            $where['ticket_buy_id'] = 0;
            $where['integral_buy_id'] = 0;
            $where['theme_id'] = 0;
            $where['limit_buy_id'] = 0;
            $where['group_buy_id'] = 0;
            $where['pre_buy_id'] = 0;
            $where['one_buy_id'] = 0;
            $where['auction_id'] = 0;
            $where['id'] = $request['id'];
            $d = D('Goods')->where($where)->find();
            if(!$d && ($request['is_buy']==0)){
                $this->error('该商品属于活动商品，不可随意下架');
                return  false;
            }

            $res = D('Goods')->where("id={$request['id']}")->save(array("is_buy"=>$request['is_buy']));
            if($res){
                $this->success("执行成功");
                return true;
            }else{
                $this->error("执行失败");
                return false;
            }
        }
        if(!empty($request['ids'])){
            //判断是否属于活动商品
//            $where['ticket_buy_id'] = 0;//票券区除外
            $where['integral_buy_id'] = 0;
            $where['theme_id'] = 0;
            $where['limit_buy_id'] = 0;
            $where['group_buy_id'] = 0;
            $where['pre_buy_id'] = 0;
            $where['one_buy_id'] = 0;
            $where['auction_id'] = 0;
            $where['id'] = array("IN",$request['ids']);
            $d = D('Goods')->where($where)->find();
            if(!$d && ($request['is_buy']==0)){
                $this->error('该商品属于活动商品，不可随意下架');
                return  false;
            }
            $where1['id'] = array("IN",$request['ids']);
            $res = D('Goods')->where($where1)->save(array("is_buy"=>$request['is_buy']));

            if($res){
                $this->success("执行成功");
                return true;
            }else{
                $this->error("执行失败");
                return false;
            }
        }
    }

    /**
     * 重写删除 假删除 状态置9
     */
    function delete()
    {
        $request = I('request.');

        //判断是否属于活动商品
        $where['ticket_buy_id'] = 0;
        $where['integral_buy_id'] = 0;
        $where['theme_id'] = 0;
        $where['limit_buy_id'] = 0;
        $where['group_buy_id'] = 0;
        $where['pre_buy_id'] = 0;
        $where['one_buy_id'] = 0;
        $where['auction_id'] = 0;
        $where['id'] = array("IN",$request['ids']);
        $d = D('Goods')->where($where)->find();
        if(!$d){
            $this->error('该商品属于活动商品，不可删除');
            return  false;
        }
        //判断是否上架
        if($d['is_buy']==1){
            $this->error('请先下架商品');
            return  false;
        }
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->setStatus(I('request.'));
        if ($result) {

            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }
    //设置热销
    function setHot(){
        $data['is_hot'] = isset($_POST['is_hot'])?$_POST['is_hot']: die('error');
        $res = D('Goods')->where("id={$_POST['goods_id']}")->save($data);
        echo $res? 1:0;
    }
    /**
     * 商品刷新
     */
    function setFreshTime(){
        $data['fresh_time'] = time();
        $data['last_ip'] = get_client_ip();
        $res = D('Goods')->where("id = {$_GET['id']}")->save($data);
        $res ? $this->success("刷新成功") : $this->error('刷新失败');
    }

    /**
     * 根据三级分类id获取到下面的商品类型跟服务
     *
     */
    function getGoodsType(){
        if(!$_POST['cate_id']){
            return false;
        }
        $where['cate_id'] = $_POST['cate_id'];
        $where['status'] = 1;
        $list = M('GoodsType')->field('id,type_name,cate_id')->where($where)->select();
        if($list){
            $html1 = '';
            foreach($list as $k=>$v){
                $str = <<<EOF
        <li data-value="{$v['id']}" data-title="{$v['type_name']}" >
        <a href="javascript:void(0)">{$v['type_name']}</a>
        </li>
EOF;
                $html1 .= $str;
            }
        }
        // $where['is_default'] = 1;
        $where['status'] = 1;
        $info = M('goods_server')->where($where)->select();
        foreach($info as $k =>$v){
            if(!empty($v['icon'])){
                $info[$k]['icon'] = api('System/getFiles',array($v['icon']))[0]['path'];
            }
        }
        $html2 = '';
        $html3 = '';
        $html4 = '';
        foreach ($info as $k => $v){
            if($v['is_default'] == 1){
                if(in_array($_POST['cate_id'],explode(',',$info[$k]['cat_id']))){
                    $res = <<<EOF
                    <div class="controls bbb">
                        <label id="abc" class="checkbox inline">
                            <input type="checkbox" disabled checked >
                            <input type="hidden"  name="server[]"  value="{$info[$k]['id']}" checked >
                            <img src="{$info[$k]['icon']}" alt="" width="18">{$info[$k]['server_name']}
                        </label>
                        <span class="help-block">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;tip：  {$info[$k]['desc']}</span>
                    </div>
EOF;
                    $html2 .=$res;
                }else{

                    $r = <<<EOF
                    <div class="controls bbb">
                        <label id="abc" class="checkbox inline">
                                <input type="checkbox" name="server[]"  value="{$info[$k]['id']}">
                                <img src="{$info[$k]['icon']}" alt="" width="18">{$info[$k]['server_name']}
                        </label>
                        <span class="help-block">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;tip：  {$info[$k]['desc']}</span>
                    </div>
EOF;
                    $html3 .=$r;
                }
            }else{
                $re = <<<EOF
                    <div class="controls bbb">
                        <label id="abc" class="checkbox inline">
                                <input type="checkbox" name="server[]"  value="{$info[$k]['id']}">
                                <img src="{$info[$k]['icon']}" alt="" width="18">{$info[$k]['server_name']}
                        </label>
                        <span class="help-block">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;tip：  {$info[$k]['desc']}</span>
                    </div>
EOF;
                $html4 .=$re;
            }
        }

//         $where['is_default'] = 1;
//         $where['status'] = 1;
//         $info = M('goods_server')->where($where)->select();
//             foreach ($info as $k => $v) {
//                 if(!empty($v['cat_id'])){
//                     if(!in_array($_POST['cate_id'],explode(',',$v['cat_id']))){
//                         unset($info[$k]);
//                     }
//                 }else{
//                     unset($info[$k]);
//                 }
//             }
//             foreach($info as $k =>$v){
//                 if(!empty($v['icon'])){
//                     $info[$k]['icon'] = api('System/getFiles',array($v['icon']))[0]['path'];
//                 }
//             }
//             $infos = M('goods_server')->where($where)->select();

//             foreach ($infos as $k => $v) {
//                 if(!empty($v['cat_id'])){
//                     if(in_array($_POST['cate_id'],explode(',',$v['cat_id']))){
//                         unset($infos[$k]);
//                     }
//                 }else{
//                     unset($infos[$k]);
//                 }
//             }
//             foreach($infos as $k =>$v){
//                 if(!empty($v['icon'])){
//                     $infos[$k]['icon'] = api('System/getFiles',array($v['icon']))[0]['path'];
//                 }
//             }
//             if($infos){
//                 $html3 = '';
//                 foreach ($infos as $key => $val) {
//                     $r = <<<EOF
//                     <div style="margin-left:222px;" class="controls bbb">
// 	                    <label class="checkbox inline">
// 	                    <input style="display: block; height:16px; width:16px;" type="checkbox" name="server[]"  value="{$val['id']}">
//                         <img src="{$val['icon']}" alt="" width="18">{$val['server_name']}
// 	                    </label>
// 	                    <span class="help-block">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;tip：  {$val['desc']}</span>
// 	                </div>
// EOF;
//                     $html3 .=$r;
//                 }
//             }
//             if($info){
//                 $html2 = '';
//                 foreach ($info as $key => $val) {
//                     $res = <<<EOF
//                     <div style="margin-left:222px;" class="controls bbb">
// 	                    <label class="checkbox inline">
// 	                        <input style="display:block; height:16px; width:16px;" type="checkbox" disabled checked value="{$val['id']}">
// 	                        <input type="hidden"  name="server[]"  value="{$val['id']}" checked >
// 	                        <img src="{$val['icon']}" alt="" width="18">{$val['server_name']}
// 	                    </label>
// 	                    <span class="help-block">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;tip：  {$val['desc']}</span>
// 	                </div>
// EOF;
// 			$html2 .=$res;
//                 }
//             }
        $data = array(
            'html1'=>$html1,
            'html2'=>$html2,
            'html3'=>$html3,
            'html4'=>$html4,
        );
        $this->ajaxReturn($data,json);
    }
    /**
     * 调价单
     */
    public function modifyPrice(){
        if(IS_POST){
            $adjustment_sn = time();
            $data['adjustment_picture'] = $_POST['adjustment_picture'];
            $data['goods_id'] = $_POST['id'];
            $data['adjustment_sn'] = $adjustment_sn;
            $data['create_time'] = time();
            $data['market_price'] = $_POST['market_price'];
            $data['shop_price'] = $_POST['shop_price'];
            $data['settlement_price'] = $_POST['settlement_price'];
            M('AdjustmentPrice')->data($data)->add();

            M('Goods')->where(array('id'=>$_POST['id']))->data(array(
                'is_doing_adjustment'=>1,
                'adjustment_sn'=>$adjustment_sn,
                'update_time'=>time()
            ))->save();
            $this->success('上传调价单成功', Cookie('__forward__'));
        }else{
            $this->display('modifyPrice');
        }
    }

    /**
     * 提交审核
     */
    public function audit(){
        if(IS_GET){
                $save['status'] = 1;
                $save['update_time'] = time();
                $data = M('goods')->where(array('id'=>$_GET['goods_id']))->save($save);
                if($data){
                    $this->success('提交审核成功');
                }else{
                    $this->error('提交审核失败,请重试!');
                }
        }
    }
}
