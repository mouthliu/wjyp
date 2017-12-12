<?php

namespace Merchant\Logic;

/**
 * Class AdministratorLogic
 * @package Merchant\Logic
 * 商品
 */
class GoodsLogic extends BaseLogic {

    /**
     *
     * @param array $request
     * @return mixed
     */
    function getList($request = array()) {

        if(!empty($request['cat_id'])) {
            //按分类查询
            $param['where']['goods.cat_id'] = array('eq',$request['cat_id']);
        }
        if(!empty($request['goods_name'])) {
            //按
            $param['where']['goods.goods_name'] = array('like',"%{$request['goods_name']}%");
        }
        if(!empty($request['goods_sn'])) {
            //按货号查询
            $param['where']['goods.goods_sn'] = array('eq',$request['goods_sn']);
        }
        $param['where']['goods.merchant_id'] = getMerchantId();
//        $param['where']['goods.status'] = array('lt',9);//状态
        $param['where']['goods.status'] = $request['status'];//状态
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('Goods')->getList($param);
//        dump(D('Goods')->getLastSql()) ;
        //dump($result);
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

        $param['where']['goods.merchant_id'] = getMerchantId();
        $param['where']['goods.status'] = array('lt',9);

        $row = D('Goods')->findRow($param);

        if(!$row){
            $this->setLogicError('未查到此记录！'); return false;
        }
        //处理分类 获取到该分类下的商品类型
        $row['type_list'] = M('GoodsType')->where("cate_id = {$row['cat_id']} AND status=1")->select();
        //处理略缩图
        $row['goods_img'] = api('System/getFiles', array($row['goods_img']));
//        dump($row['type_list']);
        return $row;
    }

    // 获取商品类型下对应的属性  并ajax返回
    function getGoodsAttr(){
        // p($_POST);die;
        //需要传过来的类型id
        if(empty($_POST['typeid'])){
            return false;
        }
        $data['at.type_id'] = $_POST['typeid'];
        $data['at.status'] = 1; //状态正常
        //获取到对应的属性
        $data = array();
        if(!empty($_POST['goodsid'])){
            $data['gt.goods_id'] = $_POST['goodsid'];
            $attrList = M('attribute')->query("SELECT a.id,a.attr_name,a.attr_values,a.attr_input_type,a.attr_type,v.id AS aid,v.attr_value,v.attr_price FROM  db_attribute AS a LEFT JOIN db_goods_attr AS v ON v.attr_id = a.id AND v.goods_id = {$_POST['goodsid']} WHERE a.type_id = {$_POST['typeid']} AND a.status !=9 ORDER BY a.attr_type DESC");
        }else{
            //说明是添加
            $attrList = M('attribute')->query("SELECT a.id,a.attr_name,a.attr_values,a.attr_input_type,a.attr_type FROM  db_attribute AS a WHERE a.type_id = {$_POST['typeid']} AND a.status !=9 ORDER BY a.attr_type DESC");
        }
        $tmp = '';
        //dump(M('attribute')->getLastSql());exit;
        $t = 1;
        $attr_id = 0;
        foreach($attrList as $k=>$v){
            if($v['attr_input_type'] == 1){
                $str = $this->getInput($v,$t,$attr_id);
                $tmp .= $str;
            }else if($v['attr_input_type'] == 2){
                $str = $this->getSelect($v,$t,$attr_id);
                $tmp .= $str;
            }
        }
        echo $tmp;
    }
    //商品添加函数
    function actUpdate($request = array()){

        $_POST = $request;
        $_POST['server'] = implode(',',$_POST['server']);
        $min_rate = getName('GoodsCategory','min_rate',$_POST['cat_id']);//分成比例
        $discount = floor($min_rate*10);//折扣
        $flagGoods = false;
        //在这里处理数据
        //计算积分
        $_POST['integral'] = getIntegral($_POST['shop_price'],$min_rate);
        //计算代金券(红)(根据分成比例)
        $_POST['discount'] = intval(90-($discount*10));
        //计算代金券(黄)
        $_POST['yellow_discount'] = sprintf('%.2f',$_POST['integral']/$_POST['shop_price']*100);
        //计算代金券(蓝)
        $_POST['blue_discount'] = sprintf('%.2f',$_POST['integral']/$_POST['shop_price']*100);
        if(empty($_POST['id'])){
            $mid = getMerchantId();
            //首先插入商品
            if(D('Goods')->create($_POST)){
                $id = D('Goods')->add();
                if($id){
                    $this->setTicketGoods($id,$discount);
                    $flagGoods = true;
                    //获取到刚插入商品的id 货号添加
                    $data['goods_sn'] = "WJ".$mid.zero($id,5);
                    M('Goods')->where("id={$id}")->save($data);
                }
            }
        }else{
            //修改商品
            $id = $_POST['id'];

            if(D('Goods')->create($_POST)){
                $result = D('Goods')->save();
                if($result){
                    $this->setTicketGoods($_POST['id'],$discount);
                    $flagGoods = true;
                }
            }
        }
        if(!$id){
            $this->setLogicError('参数错误！'); return false;
        }
        $where = array();
        if(!empty($_POST['goods_typeid'])){

            $where['id'] = array("not in",$_POST['goods_attr_id_list']);
            $where['goods_id'] = $id;
            //删除不在这里的
            M('GoodsAttr')->where($where)->delete();
            //更新原有的
            //将属性的列出来  进行批量插入  把数据处理一下
            $attr_value = array_reverse($_POST['attr_value_list']);
            $attr_price = array_reverse($_POST['attr_price_list']);
            $attr_id = array_reverse($_POST['attr_id_list']);
            $goods_attr_id = array_reverse($_POST['goods_attr_id_list']);
//            dump($goods_attr_id);exit;
            foreach( $attr_id as $k=>$v){

                if($goods_attr_id[$k]){
                    //如果存在就是修改
                    M('GoodsAttr')->where("id={$goods_attr_id[$k]}")->data(
                        array("goods_id"=>$id,
                            "attr_id"=>$v,
                            "attr_value"=>$attr_value[$k],
                            "attr_price"=>$attr_price[$k])
                    )->save();
                }else{
                    //就是增加
                    $attr_list[] = array("goods_id"=>$id,
                            "attr_id"=>$v,
                            "attr_value"=>$attr_value[$k],
                            "attr_price"=>isset($attr_price[$k])?$attr_price[$k]:'0');
                }
            }
            //  操作属性插入
            $res = M('GoodsAttr')->addAll($attr_list);

            if($res){
                $flagGoods = true;
            }
        }

        //处理商品图片
        if(!empty($_POST['common_pic']) or !empty($_POST['goods_attr_id'])){
            $gallery = M('GoodsGallery');
            //首先清除原有的
//            $gallery->where("goods_id={$id}")->delete();
            if(!empty($_POST['common_pic'])){
                //先判断是否存在
                if($gallery->where("goods_id={$id} AND goods_attr_name=0")->find()){
                    //修改
                    $gallery->where("goods_id={$id} AND goods_attr_name=0")->save(array("pictures"=>$_POST['common_pic']));
                }else{
                    //说明是添加
                    $compic['goods_id'] = $id;
                    $compic['goods_attr_name'] = 0; //0说明是普通相册的
                    $compic['pictures'] = $_POST['common_pic'];
                    $gallery->add($compic);
                }
            }
            if(!empty($_POST['goods_attr_name'])){
                //说明有图片上传
                //同理先判断有没有
                foreach($_POST['goods_attr_name'] as $k=>$v){
                    if($gallery->where("goods_id={$id} AND goods_attr_name={$v}")->find()){
                        $gallery->where("goods_id={$id} AND goods_attr_name={$v}")->save(array("pictures"=>$_POST[$v]));
                    }else{
                        $picdata['goods_id'] = $id;
                        $picdata['goods_attr_name'] = $v;
                        $picdata['pictures'] = $_POST[$v];
                        $gallery->add($picdata);
                    }

                }
            }
        }
        if($flagGoods){
            $this->setLogicError('操作成功！'); return true;
        }else{
            $this->setLogicError('操作失败！'); return false;
        }
    }

    //获取数据类别
    public function getCates($pid){
        $mod=M("GoodsCategory");
        $list=$mod->where("parent_id='{$pid}' AND status=1")->select();
        $data = array();
        //遍历
        if($list){
            foreach($list as $k=>$v){
                //$v['underCate'] 存放二级分类
                $v['underCate'] = $this->getCates($v['id']);
                $data[]=$v;
            }
        }
        return $data;
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

    /**
     * 获取普通输入框属性
     * @param array $v
     * @return string
     */
    function getInput($v = array(),&$t,&$attr_id){
        $str = '';
        if($v['attr_type']==2){
            $str = <<<EOF
            <div class="control-group attr-list" >
                <label class="control-label">{$v['attr_name']}</label>
                <div class="controls">
                    <input type="hidden" name="attr_id_list[]" value="{$v['id']}">
                    <input type="hidden" name="goods_attr_id_list[]" value="{$v['aid']}">
                    <input type="text" name="attr_value_list[]" value="{$v['attr_value']}">
                </div>
            </div>
EOF;
        }else if($v['attr_type']==1){
            if($t == 1){
                //这里判断是否id已经存在
                $icon = 'icon-plus';
                $class = 'add';
                $attr_id = $v['id'];//将这个id保存下来
                $t = 2;
            }else{
                if($attr_id == $v['id']){
                    $icon = 'icon-minus';
                    $class = 'desc';
                }else{
                    $icon = 'icon-plus';
                    $class = 'add';
                    $attr_id = $v['id'];
                }
            }
            $str = <<<EOF
            <div class="control-group anniu attr-list">
                <label class="control-label"><a href="javascript:void(0);" class="{$class}"><i class="{$icon}" ></i></a>{$v['attr_name']}</label>
                <div class="controls">
                    <input type="hidden" name="attr_id_list[]" value="{$v['id']}">
                    <input type="hidden" name="goods_attr_id_list[]" value="{$v['aid']}">
                    <input type="text" name="attr_value_list[]" value="{$v['attr_value']}">
                    附加价格:<input type="number" name="attr_price_list[]" value="{$v['attr_price']}">
                    <span class="help-block">提示2信息</span>
                </div>
            </div>
EOF;
        }
        return $str;
    }
    /**
     * 获取下拉输入框属性
     * @param array $v
     * @return string
     */
    function getSelect($v = array(),&$t,&$attr_id){
        $str = '';
        $attr_values = explode('+',$v['attr_values']);
        $count = count($attr_values);
        if($v['attr_type']==2){
            $str = <<<EOF
            <div class="control-group attr-list">
                <label class="control-label">{$v['attr_name']}</label>
                <div class="controls">
                    <input type="hidden" name="attr_id_list[]" value="{$v['id']}">
                    <input type="hidden" name="goods_attr_id_list[]" value="{$v['aid']}">
EOF;
            $str .= '<select name="attr_value_list[]">';
            foreach($attr_values as $key=>$val){
                if($val == $v['attr_value']){
                    $str .= "<option value='{$val}' selected>{$val}</option>";
                }else{
                    $str .= "<option value='{$val}'>{$val}</option>";
                }
            }
            $str .= '</select>';
            $str .= '</div>';
            $str .= '</div>';

        }else if($v['attr_type']==1){
            if($t == 1){
                //这里判断是否id已经存在
                $icon = 'icon-plus';
                $class = 'add';
                $attr_id = $v['id'];//将这个id保存下来
                $t=2;
            }else{
                if($attr_id == $v['id']){
                    $icon = 'icon-minus';
                    $class = 'desc';
                }else{
                    $icon = 'icon-plus';
                    $class = 'add';
                    $attr_id = $v['id'];
                }
            }

            $str = <<<EOF
            <div class="control-group anniu attr-list">
                <label class="control-label"><a href="javascript:void(0);" data-total='{$count}' class="{$class}"><i class="{$icon}" ></i></a>{$v['attr_name']}</label>
                <div class="controls">
                    <input type="hidden" name="attr_id_list[]" value="{$v['id']}">
                    <input type="hidden" name="goods_attr_id_list[]" value="{$v['aid']}">
EOF;
            $str .= "<select name='attr_value_list[]' data-total='{$count}'>";
            foreach($attr_values as $k=>$val){
                if($val == $v['attr_value']){
                    $str .= "<option value='{$val}' selected>{$val}</option>";
                }else{
                    $str .= "<option value='{$val}'>{$val}</option>";
                }
            }
            $str .= '</select>';
            $str .= "附加价格:<input type='number' name='attr_price_list[]' value='{$v['attr_price']}'>";
            $str .= '</div>';
            $str .= '</div>';

        }
        return $str;
    }

    /**
     * 设置商品加入票券区
     */
    function setTicketGoods($goods_id,$discount){
        if(!$goods_id) return false;

        if($discount < 5){
            //判断商品是否已经加入票券区
            $is_have = M('TicketBuy')->where("goods_id={$goods_id}")->getField('id');
            if(!$is_have){
                //加入票券区
                $data['goods_id'] = $goods_id;
                $goods_discount = getName('Goods','discount',$goods_id);
                $data['discount'] = $goods_discount;
                $id = D('TicketBuy')->add($data);
                if($id){
                    M('Goods')->where("id={$goods_id}")->save(array('ticket_buy_id'=>$id));
                }
            }
        }else{
            $res = D('TicketBuy')->where("goods_id={$goods_id}")->delete();
            if($res){
                M('Goods')->where("id={$goods_id}")->save(array('ticket_buy_id'=>0));
            }
        }
    }
}