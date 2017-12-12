<?php
namespace Api\Logic;

/**
 * Class CommentLogic
 * @package Api\Logic
 * 逻辑层  收货地址模块
 *
 */
class CommentLogic extends BaseLogic{

    /**
     * 评论列表
     * @param array $request
     * @param int $user_id
     */
    public function commentList($request = array()){
        if(!empty($request['user_id'])){
            $where['user_id'] = $request['user_id'];
        }
        if(!empty($request['merchant_id'])){
            $where['merchant_id'] = $request['merchant_id'];
        }
        if(!empty($request['goods_id'])){
            $where['goods_id'] = $request['goods_id'];
            unset($where['user_id']);
        }
        //根据评论星级筛选
        if(!empty($request['all_star'])){
            $where['all_star'] = $request['all_star'];
        }
        //根据有无图片筛选
        if(!empty($request['pictures'])){

            $where['pictures'] = array('neq','');
        }
        $where['status'] = 1;
        $count = M('Comment')->where($where)->count();
        $comment_list = M('Comment')
            ->where($where)
            ->field('id as Comment_id,goods_id,goods_name,user_id,nickname,pictures,content,all_star,product_id,order_goods_id,create_time')
            ->order('create_time desc')
            ->page($request['p'].',10')
            ->select();
        if(!$comment_list){
            array('list'=>array(),'count'=>$count);
        }
        //处理数据
        foreach($comment_list as $k=>$v){
            //获取到用户头像
            $comment_list[$k]['user_head_pic'] = D('File')->getOneFilePath(getName('User','head_pic',$v['user_id']));
            //获取到评论图片
            $comment_list[$k]['pictures'] = D('File')->getArrayFilePath(explode(',',$v['pictures']));
            //根据货品id获取到属性值
            $comment_list[$k]['good_attr'] = getAttrGroupId($v['goods_id'],$v['product_id']);
            //根据订单号获取到所卖商品的数量以及价格
            $comment_list[$k]['goods_num'] = getName('OrderGoods','goods_num',$v['order_goods_id']);
            $comment_list[$k]['shop_price'] = getName('OrderGoods','shop_price',$v['order_goods_id']);
            $comment_list[$k]['goods_img'] = D('File')->getOneFilePath(getName('Goods','goods_img',$v['goods_id']));
            //获取到订单的类型
            $comment_list[$k]['order_type'] = getName("Order","order_type",getName('OrderGoods','order_id',$v['order_goods_id']));
        }

        return array('list'=>$comment_list,'count'=>$count);
    }


}