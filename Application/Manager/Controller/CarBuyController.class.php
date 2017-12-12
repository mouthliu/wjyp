<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class CarBuyController extends BaseController {

    protected function getIndexRelation(){
        //获取到 品牌
        $this->assign("brand_list",M('CarBrand')
            ->alias('g')
            ->field('g.id,g.brand_name,f.path')
            ->join(C('DB_PREFIX')."file AS f ON f.id=g.brand_logo")
            ->where("g.status=1")
            ->select());
        //获取到 车型
        $this->assign("style_list",M('CarStyle')
            ->alias('g')
            ->field('g.id,g.style_name,f.path')
            ->join(C('DB_PREFIX')."file AS f ON f.id=g.style_img")
            ->where("g.status=1")
            ->select());
    }
    protected function getUpdateRelation(){
        //获取汽车属性
        $car_property = M('car_property')->where(array('car_id'=>$_GET['id']))->select();
        $this->assign('car_property',$car_property);
        //获取到 品牌
        $this->assign("brand_list",M('CarBrand')
            ->alias('g')
            ->field('g.id,g.brand_name,f.path')
            ->join(C('DB_PREFIX')."file AS f ON f.id=g.brand_logo")
            ->where("g.status=1")
            ->select());
        //获取到 车型
        $this->assign("style_list",M('CarStyle')
            ->alias('g')
            ->field('g.id,g.style_name,f.path')
            ->join(C('DB_PREFIX')."file AS f ON f.id=g.style_img")
            ->where("g.status=1")
            ->select());
        $param['where']['id'] = $_GET['id'];
        //获取数据
        $row = D('CarBuy')->findRow($param);
        //判断是否获取成功
        if(!$row){
            $this->error('未查到此记录！');
        }else{

            $this->assign('province',M('Region')->where(array('region_type'=>1))->field('id,region_name')->select());
            $this->assign('city',M('Region')->field('id,region_name')->where(array('parent_id'=>$row['province_id'],'region_type'=>2,'is_show'=>1))->select());
            $this->assign('area',M('Region')->field('id,region_name')->where(array('parent_id'=>$row['city_id'],'region_type'=>3,'is_show'=>1))->select());
            $this->assign('row',$row);
        }
    }

    protected function getAddRelation(){
        //获取到 品牌
        $this->assign("brand_list",M('CarBrand')
            ->alias('g')
            ->field('g.id,g.brand_name,f.path')
            ->join(C('DB_PREFIX')."file AS f ON f.id=g.brand_logo")
            ->where("g.status=1")
            ->select());
        //获取到 车型
        $this->assign("style_list",M('CarStyle')
            ->alias('g')
            ->field('g.id,g.style_name,f.path')
            ->join(C('DB_PREFIX')."file AS f ON f.id=g.style_img")
            ->where("g.status=1")
            ->select());
        $this->assign('province',M('Region')->where(array('region_type'=>1))->field('id,region_name')->select());
    }

    function setRecommend(){
        $data['is_recommend'] = isset($_POST['is_recommend'])?$_POST['is_recommend']: die('error');
        $res = D('CarBuy')->where("id={$_POST['CarBuy_id']}")->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
}
