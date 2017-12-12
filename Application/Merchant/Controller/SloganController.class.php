<?php

namespace Merchant\Controller;

/**
 * Class AdministratorController
 * @package Merchant\Controller
 * 管理员控制器
 */
class SloganController extends BaseController {

    function index(){
        //从session中获取到商家的id
        $merchant = $_SESSION['wjyp_merchant']['merchant_admin'];
        if(empty($merchant)){
            $this->error("参数错误");
            return false;
        }
        $id = getMerchantId();
        $this->assign('cate_list',$this->getCateList('0'));
        $cates_id = M('range')->where(array('merchant_id'=>$id))->getField('cates_id',true);
        foreach($cates_id as $k => $v){
            $second_id[] = M('goods_category')->where(array('id'=>$v))->getField('parent_id');
        }
        $second_id = array_unique($second_id);
//        p($second_id);
        //获取经营范围
        $range = M('range')->where(array('merchant_id'=>$id))->field('id as range_id,cates_id,min_rate,merchant_id')->select();


        $info = M('Merchant')->where("id={$id}")->find();
        if(!empty($info['a_id'])){
            $info['a_name'] = M('administrator')->where(array('id'=>$info['a_id']))->getField('account');
        }
        //处理省市区街道
        $info['province_id'] = M('region')->where(array('id'=>$info['province_id']))->getField('region_name');
        $info['city_id'] = M('region')->where(array('id'=>$info['city_id']))->getField('region_name');

        $info['area_name'] = M('region')->where("id={$info['area_id']}")->getField('region_name');

        $info['street_id'] = M('street')->where(array('street_id'=>$info['street_id']))->getField('street_name');
        //处理分类
        $info['cate_list'] = D('Goods','Logic')->getArrayCates('0',$info['range_id']);
        //处理经营范围
        $info['logo'] = api('System/getFiles', array($info['logo']));
        if($info['level'] == 1){
            $info['level'] = "旗舰店";
        }
        if($info['level'] == 2){
            $info['level'] = "专营店";
        }
        if($info['level'] == 3){
            $info['level'] = "专卖店";
        }
        if($info['brands']){
            //获取到所有【品牌
            $info['brand_list'] = M('GoodsBrand')->alias('b')
                ->field("b.id,b.brand_name,f.path AS logo")
                ->join(C('DB_PREFIX').'file f ON f.id=b.brand_logo')
                ->where("b.id IN ({$info['brands']})")
                ->select();
        }
        if($info['countrys']){
            //获取国家列表
            $info['country_list'] = M('Country')->alias('c')
                ->field("c.id,c.country_name,f.path")
                ->join(C('DB_PREFIX').'file f ON f.id=c.country_logo')
                ->where("c.id IN ({$info['countrys']})")
                ->select();
        }
        $range = M('range')->where(array('m_id'=>$id))->select();
        foreach($range as $k => $v){
            if(!empty($v['cates_id'])){
                $range[$k]['cates_name'] = M('goods_category')->where(array('id'=>$v['cates_id']))->getField('short_name');
            }
        }
        //营业执照
        if(!empty($info['business_license'])){
            $info['business_license'] = api('System/getFiles', array($info['business_license']))[0]['path'];
        }
        //其他资质信息
        if(!empty($info['other_license'])){
            $info['other_license'] = json_decode($info['other_license']);
        }
        $info['other_licenses'] = array();
        foreach($info['other_license'] as $k => $v){
            if($v->license_pic){
                $info['other_licenses'][$k]['license_pic'] = api('System/getFiles', array($v->license_pic))[0]['path'];
                $info['other_licenses'][$k]['license_name']=$v->license_name;
            }
        }
//        p($info);

        $this->assign("range",$range);
        $this->assign('second_id',$second_id);
        $this->assign('province',M('Region')->where(array('region_type'=>1))->field('id,region_name')->select());
        $this->assign('city',M('Region')->where(array('region_type'=>2,'parent_id'=>$info['province_id']))->field('id,region_name')->select());
        $this->assign('area',M('Region')->where(array('region_type'=>3,'parent_id'=>$info['city_id']))->field('id,region_name')->select());
        $this->assign('street',M('Street')->where("status=1 AND parent_id={$info['area_id']}")->field('street_id,street_name')->select());
        $this->assign('info',$info);
        $this->display('Merchant/index');
    }

    /**
     * @param int $pid
     * @return array
     */
    function getCateList($pid = 0){
        //根据传过来的pid获取子集
        $list = M("GoodsCategory")->field('id,name,short_name,parent_id')->where("parent_id={$pid} AND status=1")->select();
        if($list){
            $data = [];
            foreach($list as $k=>$v){
                $v['under'] = $this->getCateList($v['id']);
                $data[] = $v;
            }
        }
        return $data;
    }

    function update(){
        $Object = D('Merchant', 'Logic');
        if(empty($_POST['id'])){

            $this->error($Object->getLogicError());
            return false;
        }
        $res = D('Merchant')->where("id={$_POST['id']}")->save($_POST);
        if ($res) {
            $this->success('修改成功');
            return true;
        } else {
            $this->error('修改失败');
            return false;
        }
    }

    /**
     * 修改密码
     */
    function rePass() {
        if(!IS_POST) {
            $this->display('rePass');
        } else {
            $Object = D('Merchant', 'Logic');
            $result = $Object->rePass(I('request.'));
            if ($result) {
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }
    //收货地址
    function backAddress(){
        $mid = getMerchantId();
        $row = M('BackAddress')->where("merchant_id={$mid}")->find();
        //判断是否获取成功
        $this->assign('province',M('Region')->where(array('region_type'=>1))->field('id,region_name')->select());
        $this->assign('city',M('Region')->field('id,region_name')->where(array('parent_id'=>$row['province_id'],'region_type'=>2,'is_show'=>1))->select());
        $this->assign('area',M('Region')->field('id,region_name')->where(array('parent_id'=>$row['city_id'],'region_type'=>3,'is_show'=>1))->select());
        $this->assign('row',$row);
        $this->assign('province',M('Region')->where(array('region_type'=>1))->field('id,region_name')->select());
        $this->display('Merchant/backAddress');
    }
    function setBackAddress(){
        if(!$_POST['id']){
            $res = D("BackAddress")->add($_POST);
            $res == true ?$this->success("成功"):$this->error('失败');
        }else{
            $res = D("BackAddress")->where("id={$_POST['id']}")->save($_POST);
            $res == true ?$this->success("成功"):$this->error('失败');
        }

    }
    /**
     * 资质信息
     */
    public function license(){
        $mid = getMerchantId();

        //获取身份证正面照 反面照 手持照 营业执照 卫生许可 食品经营许可
        $info = M('Merchant')
            ->field("card_before,card_after,card_img,other_license,business_license,food_license,health_license,real_name,phone,card_code")
            ->where("id = {$mid}")->find();
//        $info['card_before'] = getPath($info['card_before']);
//        $info['card_after'] = getPath($info['card_after']);
//        $info['card_img'] = getPath($info['card_img']);
        $info['business_license'] = getPath($info['business_license']);
//        $info['food_license'] = getPath($info['food_license']);
//        $info['health_license'] = getPath($info['health_license']);
        $license_arr = json_decode($info['other_license'],true);
        foreach($license_arr as $k=>$v){
            $license_arr[$k]['license_pic'] = M('file')->field('path')->where("id={$v['license_pic']}")->find()['path'];
        }
        $info['other_license'] = $license_arr;
        $this->assign('info',$info);
        $this->display('Merchant/license');
    }
    /**
     * 口号设置
     */
    public function slogan(){
        $mid = getMerchantId();
        $slogan = M('Merchant')->where("id = {$mid}")->getField('slogan');
        $this->assign('slogan',$slogan);
        $this->display('Merchant/slogan');
    }
    public function doSlogan(){
        $data['slogan'] = $_POST['slogan']? $_POST['slogan'] : $this->error('参数不足');
        $where['id'] = $_POST['id'] ? $_POST['id'] : $this->error('参数不足');
        $res = D('Merchant')->where($where)->save($data);
        $res ? $this->success('修改成功') : $this->error('修改失败');
    }
    /**
     * 获取地区
     */
    public function ajaxGetRegion(){
        if(empty(I('get.id'))){
            $this->ajaxReturn(array(),'JSON');
        }else{
            $where['parent_id'] = I('get.id');
        }
        $result = M('Region')->where($where)->field('id,region_name')->select();
        $this->ajaxReturn($result,'JSON');
    }
    /**
     * 获取街道
     */
    public function ajaxGetStreet(){
        $where['parent_id'] = I('get.id');
        $where['status'] = 1;
        $result = M('Street')->where($where)->field('street_id,street_name')->select();
        $this->ajaxReturn($result,'JSON');
    }
}
