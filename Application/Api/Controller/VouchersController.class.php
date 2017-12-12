<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 购物券模块控制器
 * Class VouchersController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class VouchersController extends BaseController
{
    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 获取购物券列表
     * 请求方式：post
     * 请求参数：
     * 分页参数: p
     */
    public function vouchersList()
    {
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['p'])) {
            apiResponse('0', '请输入分页参数');
        }
        D('Vouchers', 'Logic')->vouchersList(I('post.'), $user_id);
    }

    /**
     * 获取购物券明细列表
     * 请求方式：post
     * 请求参数：
     * 分页参数: p
     */
    public function vouchersLog()
    {
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if (empty($_POST['p'])) {
            apiResponse('0', '请输入分页参数');
        }
        D('Vouchers', 'Logic')->vouchersLog(I('post.'), $user_id);
    }

    /**
     * 设置购物券记录表
     * 请求方式：post
     * 请求参数:
     * 购物券id: vouchers_id
     * 操作类型: act_type  1=>获得 2=>兑换月 3=>消费 4=>退款
     * 操作原因: reason
     */
    public function addLog(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['vouchers_id'])){
            apiResponse('0','请输入操作的购物券');
        }
        if(empty($_POST['act_type'])){
            apiResponse('0','请输入操作类型');
        }
        if(empty($_POST['reason'])){
            apiResponse('0','请输入原因');
        }
        $res = D('Vouchers','Logic')->addLog(I('post.'),$user_id);
        if($res){
            apiResponse('1','记录成功');
        }else{
            apiResponse('0','记录失败');
        }
    }

    /**
     * 增加购物券
     * 请求方式: post;
     * 请求参数：
     * 失效时间: end_time
     * 面值: money
     */
    public function addVoucher(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);

        if(empty($_POST['money'])){
            apiResponse('0','请输入购物券面值');
        }
        if(empty($_POST['type'])){
            apiResponse('0','请输入购物券类型');
        }
        D('Vouchers','Logic')->addVoucher(I('post.'),$user_id);
    }
    /**
     * 使用购物券
     */
    public function useVoucher(){
        $user_id = $this->checkLogin();
        D('Vouchers','Logic')->useVoucher($_POST['price'],$user_id,$_POST['type']);
    }
}