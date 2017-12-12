<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 商品模块控制器
 * Class GoodsController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class GoodsController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取普通商品列表函数
     * 请求方式：POST
     * 请求参数：
     *  分类id: cate_id
     *  分页参数 : p
     */
    public function goodsList(){
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        if(empty($_POST['cate_id'])){
            apiResponse('0','顶级分类id不能为空');
        }
        D('Goods','Logic')->goodsList(I('post.'));

    }
    /**
     * 三级分类商品列表
     * 请求参数 : two_cate_id
     * three_cate_id (可选)
     */
    public function threeList(){
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        if(empty($_POST['two_cate_id'])){
            apiResponse('0','二级分类id不能为空');
        }
        D('Goods','Logic')->threeList(I('post.'));
    }
    /**
     * 获取商品详情页信息
     * 请求方式:post
     * 请求参数
     * 商品id ：goods_id
     */
    public function goodsInfo(){
        if(empty($_POST['goods_id'])){
            apiResponse('0','请输入商品ID');
        }
        $user_id = $this->checkLogin();
        $res = D('Goods','Logic')->goodsInfo(I('post.goods_id'),$user_id);
        if($res){
            apiResponse('1','获取成功',$res);
        }else{
            apiResponse('0','获取商品失败');
        }
    }
    /**
     * 搜索
     * type 1商品 2商家
     * name 名称
     */
    public function search(){
        if(empty($_POST['type'])){
            apiResponse('0','请选择搜索类型');
        }
        if(empty($_POST['name'])){
            apiResponse('0','请输入要搜索的内容');
        }
        $user_id = $this->checkLogin();
        D('Goods','Logic')->search(I('post.'),$user_id);
    }

    /**
     * 领取优惠券
     * 优惠券id：ticket_id
     */
    public function getTicket(){
        if(empty($_POST['ticket_id'])){
            apiResponse('0','参数不完整');
        }
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        D('Goods','Logic')->getTicket(I('post.'),$user_id);
    }

    /**
     * 搭配购列表
     * 商品ID：goods_id
     */
    public function groupGoodsList(){
        if(empty($_POST['goods_id'])){
            apiResponse('0','商品ID不能为空');
        }
        D('Goods','Logic')->groupGoodsList(I('post.'));
    }
}