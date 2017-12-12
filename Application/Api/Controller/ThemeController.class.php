<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 会员模块控制器
 * Class ThemeController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class ThemeController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取主题街主题封面
     * 请求方式：post
     * 请求参数：
     * 分页参数: p
     */
    public function themeList(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        D('Theme','Logic')->themeList(I('post.'));
    }

    /**
     * 获取主题街下的商品
     * 请求方式：post
     * 请求参数:
     * 主题街id : theme_id
     *  分页参数：p
     */
    public function themeGoods(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        if(empty($_POST['theme_id'])){
            apiResponse('0','请选择主题');
        }
         D('Theme','Logic')->themeGoods(I('post.'));
    }

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
}