<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 全球管模块控制器
 * Class CountryController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class CountryController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取全球馆首页数据
     * 请求方式：post
     * 请求参数：
     * 分页参数: p
     */
    public function countryIndex(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        D('Country','Logic')->countryIndex(I('post.'));
    }

    /**
     * 获取该国家的商品
     * 请求方式：post
     * 请求参数:
     * 国家id : country_id
     *  分页参数：p
     *  分类ID :cate_id
     */
    public function countryGoods(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        if(empty($_POST['country_id'])){
            apiResponse('0','请选择国家');
        }
        D('Country','Logic')->countryGoods(I('post.'));
    }

    /**
     * 商品详情页
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
        if(empty($_POST['country_id'])){
            apiResponse('0','请输入国家id');
        }
        D('Country','Logic')->threeList(I('post.'));
    }


}