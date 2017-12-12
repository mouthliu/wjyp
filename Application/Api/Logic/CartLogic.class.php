<?php
namespace Api\Logic;

/**
 * Class CartLogic
 * @package Api\Logic
 * 逻辑层  购物车 模块
 *
 */
class CartLogic extends BaseLogic{

    /**
     * 加入购物车
     * @param array $request
     * @param int $user_id
     */
      public function addCart($request = array(),$user_id = 0){
          $mod = D('Cart');
          //查询商品是否存在
          $goods_info = M('Goods')->where(array('id'=>$request['goods_id']))->find();
          if(!$goods_info){
              apiResponse('0','商品信息查询失败');
          }
          //判定商品在购物车中是否存在
          $where['user_id'] = $user_id;
          $where['goods_id'] = $request['goods_id'];
          $where['product_id'] = $request['product_id']?$request['product_id']:0;
          $cart_info = M('Cart')->where($where)->find();
          if($cart_info){
              //购物车已经存在该商品
              unset($where);
              $where['id'] = $cart_info['id'];
              $data['num'] = $cart_info['num']+$request['num'];
              $data['update_time'] = time();
              $res = M('Cart')->where($where)->data($data)->save();
          }else{
              //购物车中不存在该商品
              unset($data);
              $data['user_id'] = $user_id;
              $data['merchant_id'] = $goods_info['merchant_id'];
              $data['goods_id'] = $request['goods_id'];
              $data['product_id'] = $request['product_id']?$request['product_id']:0;
              $data['num'] = $request['num'];
              $data['create_time'] = time();
              $res = M('Cart')->data($data)->add();
          }
          if($res){
              apiResponse('1','添加购物车成功');
          }else{
              apiResponse('0','添加购物车失败');
          }

      }
    /**
     * 购物车列表
     * @param int $user_id
     */
    public function cartList($user_id = 0){
        $mod = M('Cart');
        $where['c.user_id'] = $user_id;
        $list = $mod->alias('c')
            ->field('c.id AS cart_id,c.goods_id,c.product_id,c.merchant_id,c.num,g.goods_name,g.shop_price,g.goods_img')
            ->where($where)
            ->join(C('DB_PREFIX').'goods g ON g.id = c.goods_id')
            ->order('c.create_time DESC')
            ->select();

        if(!$list){
            apiResponse('0','购物车空空如也');
        }

        $cart_list = array();
        foreach($list as $k=>$v){
            //根据货品id获取到属性组合信息和库存
            $v['attr_group'] = getAttrGroupId($v['goods_id'],$v['product_id']);
            $store =M('Products')->where("id={$v['product_id']}")->getField('product_number');
            $v['attr_group_num'] = $store?$store:'0';

            //处理商品缩略图
            $path = M('File')->where(array('id'=>$v['goods_img']))->getField('path');
            $v['goods_img'] = C('API_URL').$path;
            //获取商品的可选属性
            $goods_attr = M('Attribute')->alias('a')
                ->field("a.id,v.id as goods_attr_id,a.attr_name,v.attr_value,v.attr_price")
                ->join(C('DB_PREFIX').'goods_attr AS v ON v.attr_id = a.id')
                ->where("a.attr_type=1 AND a.status !=9 AND v.goods_id = {$v['goods_id']}")
                ->select();
            $i = -1;
            $attr_id = 0;
            $new_goods_attr = array();
            foreach($goods_attr as $key=>$val){
                if($attr_id !=  $val['id']){
                    $i++;
                }
                $new_goods_attr[$i]['attr_name'] = $val['attr_name'];
                $new_goods_attr[$i]['attr_list'][] = $val;
                $attr_id = $v['id'];
            }
            $v['goods_attr'] = $new_goods_attr;

//            foreach($goods_attr as $k2=>$v2){
//                // 属性对应的相册
//                $pic = M("GoodsGallery")->where("goods_attr_name = {$v2['aid']} AND goods_id={$v['goods_id']}")->getField('pictures');
//                $h = D('File')->getOneFilePath(explode(',',$pic)[0]);
//
//                $goods_attr[$k2]['attr_pic'] = $h?$h:D('File')->getOneFilePath(getName('Goods','goods_img',$v['goods_id']));
//            }
//            $v['goods_attr'] = $goods_attr?$goods_attr : '';

            //获取属性组合 （货品）
            $attr_group = M('Products')->where("goods_id={$v['goods_id']}")->field('id,goods_id,goods_attr as goods_attr_str,product_sn,product_number')->select();
            $v['product'] = empty($attr_group)?array():$attr_group;
            //根据商家分组
            $cart_list[$v['merchant_id']]['merchant_id'] = $v['merchant_id'];
            $cart_list[$v['merchant_id']]['merchant_name'] = getName('Merchant','merchant_name',$v['merchant_id']);
            $cart_list[$v['merchant_id']]['goods'][] = $v;
        }
        $i = 0;
        $new_list = array();
        foreach($cart_list as $k1=>$v1){
            $new_list[$i] = $v1;
            $i++;
        }
        apiResponse('1','',$new_list);
    }

    /**
     * 编辑购物车
     * @param array $request
     * @param int $user_id
     * 参数：cart_json 购物车json格式[{"cart_id":"购物车ID","goods_id":"商品ID"，"product_id":"货品ID","num":"数量"}]
     */
     function editCart($request = array(),$user_id = 0){
         if(empty($_POST['cart_json'])){
             apiResponse('0','json不能为空');
         }
         $cart_list = json_decode($_POST['cart_json'],true);
         foreach($cart_list as $k => $v){
             unset($where);
             unset($data);
             $where['id'] = $v['cart_id'];
             $data['goods_id'] = $v['goods_id'];
             $data['product_id'] = $v['product_id'];
             $data['num'] = $v['num'];
             $data['update_time'] = time();
             M('Cart')->where($where)->data($data)->save();
         }
         apiResponse('1','操作成功');

     }

    /**
     *删除购物车
     * @param array $request
     */
    function delCart($request = array()){
        $cart_id_list = json_decode($_POST['cart_id_json'],true);
        foreach($cart_id_list as $k => $v){
            M('Cart')->where(array('id'=>$v['cart_id']))->delete();
        }
        apiResponse('1','删除成功');
    }
    /**
     * 加入我的收藏
     */
    function addCollect($request = array(),$user_id = 0){
        $mod = D('UserCollect');
        $cart_id_list = json_decode($_POST['cart_id_json'],true);
        foreach($cart_id_list as $k => $v){
            $cart_info = M('Cart')->where(array('id'=>$v['cart_id']))->find();
            //从购物车中删除
            M('Cart')->where(array('id'=>$v['cart_id']))->delete();
            //加入到收藏夹
            //1.查询该商品是否已经收藏
            $res = $mod->where(array('user_id'=>$user_id,'type'=>1,'id_val'=>$cart_info['goods_id']))->find();
            if(!$res){
                unset($data);
                $data['user_id'] = $user_id;
                $data['type'] = 1;
                $data['id_val'] = $cart_info['goods_id'];
                $data['create_time'] = time();
                $mod->data($data)->add();
            }

        }
        apiResponse('1','操作成功');
    }
}