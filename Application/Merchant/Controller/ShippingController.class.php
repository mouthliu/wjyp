<?php
namespace Merchant\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class ShippingController extends BaseController {

//    function getIndexRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('request.cate_id')));
//    }
//
//    function getUpdateRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }
//
    function getAddRelation() {
        $mid = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $this->assign('shipping_list',M('Shipping')->field("id,shipping_name")->select());
        $mylist = M('MerchantShipping')->field('shipping_id')->where("merchant_id={$mid}")->select();
        foreach($mylist as $k=>$v){
            $mylist[$k] = $v['shipping_id'];
        }
        $this->assign("my_ship",$mylist);
    }

    function setDefault(){
        //将快递方式设为默认
        $request = I('request.');
        if(empty($request['sid'])){
            $this->error("参数不足");return false;
        }
        $mid = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
       M("MerchantShipping")->where("merchant_id={$mid}")->save(array("is_default"=>0));
       $res2 = M("MerchantShipping")->where("merchant_id={$mid} AND shipping_id={$request['sid']}")->save(array("is_default"=>1));
        if($res2){
            $this->success("设置成功");
        }else{
            $this->error('设置失败');
        }
    }

    /**
     * 添加配送方式
     */
    function addShip(){
        if(empty($_POST['shipping_id'])){
            $this->error("请选择配送方式");
            return false;
        }
        $mid = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        foreach($_POST['shipping_id'] as $k=>$v){
            $data[$k]['merchant_id'] = $mid;
            $data[$k]['shipping_id'] = $v;
        }
        $res = M("MerchantShipping")->addAll($data);
        if($res){
            $this->success("设置成功");
        }else{
            $this->error('设置失败');
        }
    }
    function delete(){
        if(empty($_GET['id'])){
            $this->error("请选择一个");
            return false;
        }
        $res = M("MerchantShipping")->where("id={$_GET['id']}")->delete();
        if($res){
            $this->success("设置成功");
        }else{
            $this->error('设置失败');
        }
    }

}
