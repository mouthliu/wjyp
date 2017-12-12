<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 商家模块控制器
 * Class UserController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class MerchantController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取封面（一个商家 下面三个商品的那个）
     * 请求方式：post
     * 请求参数：
     * 商家id
     */
    public function getFace(){
        if(empty($_POST['merchant_id'])){
            apiResponse('0','请选择商家id');
        }
        $info = D('Merchant','Logic')->getFace($_POST['merchant_id']);
        if(!info){
            apiResponse('0','暂无数据');
        }else{
            apiResponse('1','获取成功',$info);
        }
    }
    /**
     * 店铺首页
     * 请求方式：post
     * 请求参数:
     *    店铺id : merchant_id
     *    分页参数:p
     */
    public function merIndex(){
        $user_id = $this->checkLogin();
        if(empty($_POST['merchant_id'])){
            apiResponse('0','请选择商家id');
        }
        $info = D('Merchant','Logic')->merIndex(I('post.'),$user_id);
        if(!info){
            apiResponse('0','暂无数据');
        }else{
            apiResponse('1','获取成功',$info);
        }
    }
    /**
     * 店铺详细信息
     * 请求方式:post
     * 请求参数：商家id
     */
    public function merInfo(){
        $user_id = $this->checkLogin();
        if(empty($_POST['merchant_id'])){
            apiResponse('0','请选择商家id');
        }

        D('Merchant','Logic')->merInfo($_POST['merchant_id'],$user_id);
    }
    /**
     * 店内商品列表
     * 请求方式:post
     * 请求参数:
     * 分页参数：p
     * 商家id : merchant_id
     * 可选参数
     * 热销商品  is_hot = 1
     * 最新上架  new_buy = 1`
     */
    public function goodsList(){
        $user_id = $this->checkLogin();
        if(empty($_POST['p'])){
            apiResponse('请选择分页参数');
        }
        if(empty($_POST['merchant_id'])){
            apiResponse('请选择商家ID');
        }
        D('Merchant','Logic')->goodsList(I('post.'),$user_id);
    }
    /**
     * 获取全部评论
     * 请求方式post
     * 请求参数:merchant_id
     * 分页参数:p
     */
    public function commentList(){
        if(empty($_POST['merchant_id'])){
            apiResponse('0','请选择商家ID');
        }
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        $res = D('Comment','Logic')->commentList(I('post.'));
        if($res){
            apiResponse('1','获取成功',$res['list'],$res['count']);
        }else{
            apiResponse('0','获取失败');
        }
    }
    /**
     * 商家资质
     * 请求参数 ：商家ID
     */
    public function license(){
        if(empty($_POST['merchant_id'])){
            apiResponse('0','请选择商家ID');
        }
        D('Merchant','Logic')->license($_POST['merchant_id']);
    }
    /**
     * 店内限量购商品列表
     * 请求方式:post
     * 请求参数:
     * 分页参数： p
     * 商家id : merchant_id
     */
    public function limitList(){
        $user_id = $this->checkLogin();
        if(empty($_POST['p'])){
            apiResponse('请选择分页参数');
        }
        if(empty($_POST['merchant_id'])){
            apiResponse('请选择商家ID');
        }
        $res = D('Merchant','Logic')->limitList(I('post.'),$user_id);
    }
    /**
     * 店内团购商品列表
     * 请求方式:post
     * 请求参数:
     * 分页参数： p
     * 商家id : merchant_id
     */
    public function groupList(){
        $user_id = $this->checkLogin();
        if(empty($_POST['p'])){
            apiResponse('请选择分页参数');
        }
        if(empty($_POST['merchant_id'])){
            apiResponse('请选择商家ID');
        }
        $res = D('Merchant','Logic')->groupList(I('post.'),$user_id);
        apiResponse('1','获取成功',$res['list'],$res['count']);
    }

    /**
     * 店内预购商品列表
     * 请求方式:post
     * 请求参数:
     * 分页参数： p
     * 商家id : merchant_id
     */
    public function preList(){
        $user_id = $this->checkLogin();
        if(empty($_POST['p'])){
            apiResponse('请选择分页参数');
        }
        if(empty($_POST['merchant_id'])){
            apiResponse('请选择商家ID');
        }
        D('Merchant','Logic')->preList(I('post.'),$user_id);

    }
    /**
     * 店内一元夺宝商品列表
     * 请求方式:post
     * 请求参数:
     * 分页参数： p
     * 商家id : merchant_id
     */
    public function oneBuyList(){
        $user_id = $this->checkLogin();
        if(empty($_POST['p'])){
            apiResponse('请选择分页参数');
        }
        if(empty($_POST['merchant_id'])){
            apiResponse('请选择商家ID');
        }
       D('Merchant','Logic')->oneBuyList(I('post.'),$user_id);
    }
    /**
     * 店内拍卖商品列表
     * 请求方式:post
     * 请求参数:
     * 分页参数： p
     * 商家id : merchant_id
     */
    public function auctionList(){
        $user_id = $this->checkLogin();
        if(empty($_POST['p'])){
            apiResponse('请选择分页参数');
        }
        if(empty($_POST['merchant_id'])){
            apiResponse('请选择商家ID');
        }
        D('Merchant','Logic')->auctionList(I('post.'),$user_id);
    }
    /**
     * 举报商家
     * 请求参数: 举报类型 ：report_type_id
     *          举报理由 : report_content
     *          商家id
     */
     function report(){
         $user_id = $this->checkLogin();
         $this->returnNotLoginMsg($user_id);
         if(empty($_POST['merchant_id'])){
             apiResponse('请选择商家ID');
         }
         if(empty($_POST['report_type_id'])){
             apiResponse('0','请输入举报类型');
         }
         if(empty($_POST['report_content'])){
             apiResponse('0','请输入举报理由');
         }
         D('Merchant','Logic')->report(I('post.'),$user_id);
     }
    /**
     * 举报类型
     * 请求参数 :无
     */
    public function reportType(){
        $list = M('ReportType')->field('id AS report_type_id,title')->where('status = 1')->select();
        if(!$list){
            apiResponse('0','获取失败');
        }
        apiResponse('1','获取成功',$list);
    }
    /**
     * 搜索
     * merchant_id
     * name 名称
     * p(可选)
     */
    public function MerSearch(){
        if(empty($_POST['merchant_id'])){
            apiResponse('0','请输入商家id');
        }
        if(empty($_POST['name'])){
            apiResponse('0','请输入要搜索的商品名称');
        }
        $user_id = $this->checkLogin();
        D('Merchant','Logic')->MerSearch(I('post.'),$user_id);
    }
}