<?php

namespace Manager\Logic;

/**
 * Class GoodsLogic
 * @package Manager\Logic
 * 商品
 */
class GoodsLogic extends BaseLogic {

    /**
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {
        $param['where']['goods.status'] = array('lt',9);//状态
        if(!empty($request['shenhe'])) {
            //按审核状态查询
            $param['where']['goods.status'] = array('eq',$request['shenhe']);
        }
        if(!empty($request['cat_id'])) {
            //按分类查询
            $param['where']['goods.cat_id'] = array('eq',$request['cat_id']);
        }
        if(!empty($request['merchant_id'])) {
            //按店铺查询
            $param['where']['goods.merchant_id'] = array('eq',$request['merchant_id']);
        }
        if(!empty($request['merchant_name'])) {
            //按店铺查询
            $param['where']['goods.merchant_name'] = array('like',"%{$request['merchant_name']}%");
        }
        if(!empty($request['goods_name'])) {
            //按店铺查询
            $param['where']['goods.goods_name'] = array('like',"%{$request['goods_name']}%");
        }
        if(!empty($request['goods_sn'])) {
            //按货号查询
            $param['where']['goods.goods_sn'] = array('eq',$request['goods_sn']);
        }
        $param['where']['goods.status'] = $request['status'];//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数
        $result = D('Goods')->getList($param);
        foreach($result['list'] as $k=>$v){
            //新增:计算会员价格(无优会员,优享会员)
            $rate = getName('GoodsCategory','min_rate',$v['cat_id']);
            $result['list'][$k]['wy_price'] = sprintf('%.2f',$v['shop_price']*(1-0.05+0.05*$rate));
            $result['list'][$k]['yx_price'] = sprintf('%.2f',$v['shop_price']*(1-0.1+0.1*$rate));
        }
        return $result;
    }

    /**
     * @param $request
     * @return mixed
     */
    function findRow($request = array()) {
        if(!empty($request['id'])) {
            $param['where']['goods.id'] = $request['id'];
        } else {
            $this->setLogicError('参数错误！'); return false;
        }
        $param['where']['goods.status'] = array('lt',9);
        $row = D('Goods')->findRow($param);
        if(!$row) {
            $this->setLogicError('未查到此记录！'); return false;
        }
        //处理分类名称
        $cate_name = getCatePath2('GoodsCategory',$row['cat_id']);
        $row['cat_name'] = $cate_name;
        //处理略缩图
        $row['goods_img'] = M('file')->field('path')->where("id={$row['goods_img']}")->find()["path"];
        $row['type_name'] = M('GoodsType')->field('type_name')->where("id={$row['goods_typeid']}")->find()['type_name'];
        //会员价格
        $rate = getName('GoodsCategory','min_rate',$row['cat_id']);
        $row['wy_price'] = sprintf('%.2f',$row['shop_price']*(1-0.05+0.05*$rate));
        $row['yx_price'] = sprintf('%.2f',$row['shop_price']*(1-0.1+0.1*$rate));
        return $row;
    }

    /**
     * 获取商品类型下对应的属性  并ajax返回
     * @return bool
     */
    function getGoodsAttr(){
        //需要传过来的类型id
        if(empty($_POST['typeid'])){
            return false;
        }
        $data['at.type_id'] = $_POST['typeid'];
        $data['at.status'] = 1; //状态正常
        //获取到对应的属性
//        $attrList = M('attribute')->query("SELECT a.attr_id, a.attr_name, a.attr_input_type, a.attr_type,a.attr_txm, a.attr_values, v.attr_value, v.attr_price
//            FROM  ecs_attribute AS a
//            LEFT JOIN ecs_goods_attr AS v
//            ON v.attr_id = a.attr_id AND v.goods_id = 283 (根据商品id)
//            WHERE a.type_id = 2 (连表查询出对应商品的属性)");
        $data = array();
        if(!empty($_POST['goodsid'])){
            $data['gt.goods_id'] = $_POST['goodsid'];
            $attrList = M('attribute')->query("SELECT a.id,a.attr_name,a.attr_type,v.attr_value,v.attr_price FROM  db_attribute AS a LEFT JOIN db_goods_attr AS v ON v.attr_id = a.id AND v.goods_id = {$_POST['goodsid']} WHERE a.type_id = {$_POST['typeid']} AND a.status !=9 ORDER BY a.attr_type DESC");

        }else{
            //说明是添加
            $attrList = M('attribute')->query("SELECT a.id,a.attr_name,a.attr_type FROM  db_attribute AS a WHERE a.type_id = {$_POST['typeid']} AND a.status !=9 ORDER BY a.attr_type DESC");

        }
        $tmp = '';
//        dump(M('attribute')->getLastSql());
        $t = 1;
        foreach($attrList as $k=>$v){
            if($v['attr_type']==2){
                $str = <<<EOF
            <div class="control-group">
                <label class="control-label">{$v['attr_name']}</label>
                <div class="controls">
                    <input type="hidden" name="attr_id_list[]" value="{$v['id']}" disabled>
                    <input type="text" name="attr_value_list[]" value="{$v['attr_value']}" disabled>
                </div>
            </div>
EOF;

            }else if($v['attr_type']==1){

                $str = <<<EOF
            <div class="control-group anniu">
                <label class="control-label">{$v['attr_name']}</label>
                <div class="controls">
                    <input type="hidden" name="attr_id_list[]" value="{$v['id']}" disabled>
                    <input type="text" name="attr_value_list[]" value="{$v['attr_value']}" disabled>
                    附加价格:<input type="number" name="attr_price_list[]" value="{$v['attr_price']}" disabled>
                    <span class="help-block">提示2信息</span>
                </div>
            </div>
EOF;
            }
            $tmp .= $str;
        }
        echo $tmp;

    }

    //获取数据类别
    public function getArrayCates($pids,$cates = ''){
        $mod=M("GoodsCategory");
        $where['status'] = 1;
        $where['parent_id'] = $pids;
        $where['id'] = $cates ? array('IN',$cates) : array('gt','0');
        $list=$mod->where($where)->select();
        $data = array();
        //遍历
        if($list){
            foreach($list as $k=>$v){
                //$v['underCate'] 存放二级分类
                $v['underCate'] = $this->getArrayCates($v['id']);
                $data[]=$v;
            }
        }
        return $data;
    }
}