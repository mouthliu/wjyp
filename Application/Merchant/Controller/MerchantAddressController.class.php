<?php

namespace Merchant\Controller;

/**
 * 运费模板地址控制器
 * Class MerchantAddressController
 * @package Merchant\Controller
 */
class MerchantAddressController extends BaseController {

    /**
     * @desc:   获取四级地址
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-11-22 13:48
     */

    public function getUpdateRelation(){
        $param['where']['id'] = $_GET['id'];
        //获取数据
        $row = D('MerchantAddress')->findRow($param);
        //判断是否获取成功
        if(!$row){
            $this->error('未查到此记录！');
        }else{
            $this->assign('province',M('Region')->where(array('region_type'=>1))->field('id,region_name')->select());
            $this->assign('city',M('Region')->field('id,region_name')->where(array('parent_id'=>$row['province_id'],'region_type'=>2,'is_show'=>1))->select());
            $this->assign('area',M('Region')->field('id,region_name')->where(array('parent_id'=>$row['city_id'],'region_type'=>3,'is_show'=>1))->select());
            $this->assign('street',M('street')->field('street_id,street_name')->where(array('parent_id'=>$row['area_id'],'status'=>1))->select());
            $this->assign('row',$row);
        }
    }

    /**
     * @desc:   获取省id
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-11-22 13:53
     */

    function getAddRelation() {
        $this->assign('province',M('Region')->where(array('region_type'=>1))->field('id,region_name')->select());
    }


    /**
     * 获取地区
     */

    public function ajaxGetRegion(){
        if(empty($_POST['id'])){
            $this->ajaxReturn(array(),'JSON');
        }
        $where['parent_id'] = $_POST['id'];
        $result = M('Region')->where($where)->field('id,region_name')->select();
        $this->ajaxReturn($result,'JSON');
    }

    /**
     * 获取街道
     */

    public function ajaxGetStreet(){
        $where['parent_id'] = $_POST['id'];
        $where['status'] = 1;
        $result = M('Street')->where($where)->field('street_id,street_name')->select();
        $this->ajaxReturn($result,'JSON');
    }

}
