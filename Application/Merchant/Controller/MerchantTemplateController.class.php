<?php

namespace Merchant\Controller;

/**
 * 模板主表控制器
 * Class MerchantTemplateController
 * @package Merchant\Controller
 */

class MerchantTemplateController extends BaseController {

    /**
     * @desc:   添加地址页面
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-11-30 09:15
     */

    public function getAddRelation()
    {
        $shipping_address = M('MerchantAddress')->where(array('merchant_id'=>getMerchantId(),'type'=>1,'status'=>array('neq',9)))->select();
        foreach($shipping_address as $k => $v){
            $province_name = M('region')->where(array('id'=>$v['province_id']))->getField('region_name');
            $city_name = M('region')->where(array('id'=>$v['city_id']))->getField('region_name');
            $area_name = M('region')->where(array('id'=>$v['area_id']))->getField('region_name');
            $street_name = M('street')->where(array('street_id'=>$v['street_id']))->getField('street_name');
            $shipping_address[$k]['address'] = $province_name.$city_name.$area_name.$street_name.$v['address'];
        }
        $back_address = M('MerchantAddress')->where(array('merchant_id'=>getMerchantId(),'type'=>2,'status'=>array('neq',9)))->select();
        foreach($back_address as $k => $v){
            $province_name = M('region')->where(array('id'=>$v['province_id']))->getField('region_name');
            $city_name = M('region')->where(array('id'=>$v['city_id']))->getField('region_name');
            $area_name = M('region')->where(array('id'=>$v['area_id']))->getField('region_name');
            $street_name = M('street')->where(array('street_id'=>$v['street_id']))->getField('street_name');
            $back_address[$k]['address'] = $province_name.$city_name.$area_name.$street_name.$v['address'];
        }

        $express_company   = M('shipping')->field('id,shipping_name')->where(array('company_type'=>1,'status'=>1))->select();
        $logistics_company = M('shipping')->field('id,shipping_name')->where(array('company_type'=>2,'status'=>1))->select();
        $this->assign('express_company',$express_company);
        $this->assign('logistics_company',$logistics_company);
        $this->assign('back_address',$back_address);
        $this->assign('shipping_address',$shipping_address);
    }

    /**
     * @desc:   编辑展示
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-12-06 14:00
     */

    public function getUpdateRelation()
    {
        $shipping_address = M('MerchantAddress')->where(array('merchant_id'=>getMerchantId(),'type'=>1,'status'=>array('neq',9)))->select();
        foreach($shipping_address as $k => $v){
            $province_name = M('region')->where(array('id'=>$v['province_id']))->getField('region_name');
            $city_name = M('region')->where(array('id'=>$v['city_id']))->getField('region_name');
            $area_name = M('region')->where(array('id'=>$v['area_id']))->getField('region_name');
            $street_name = M('street')->where(array('street_id'=>$v['street_id']))->getField('street_name');
            $shipping_address[$k]['address'] = $province_name.$city_name.$area_name.$street_name.$v['address'];
        }
        $back_address = M('MerchantAddress')->where(array('merchant_id'=>getMerchantId(),'type'=>2,'status'=>array('neq',9)))->select();
        foreach($back_address as $k => $v){
            $province_name = M('region')->where(array('id'=>$v['province_id']))->getField('region_name');
            $city_name = M('region')->where(array('id'=>$v['city_id']))->getField('region_name');
            $area_name = M('region')->where(array('id'=>$v['area_id']))->getField('region_name');
            $street_name = M('street')->where(array('street_id'=>$v['street_id']))->getField('street_name');
            $back_address[$k]['address'] = $province_name.$city_name.$area_name.$street_name.$v['address'];
        }
        $this->assign('back_address',$back_address);
        $this->assign('shipping_address',$shipping_address);


    }


    /**
     * @desc:   默认运费模板列表
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-11-30 14:03
     */

    public function defaultIndex()
    {
        if($_GET['id']) {
            $template = M('MerchantTemplate')->field('id,template_name')->where(array('id' => $_GET['id'],'status' => array('neq', 9)))->select();
            foreach( $template as $k=>$v ){
                $template_name = $v['template_name'];
                $tem_id = $v['id'];
            }

            $list = M('TemplateList')->where(array('tem_id'=>$_GET['id'],'trans_address'=>0,'status' => array('neq', 9)))->select();
            foreach( $list as $k => $v ){
                if($v['unit']==1){
                    $list[$k]['unit']='件';
                }
                if($v['unit']==2){
                    $list[$k]['unit']='kg';
                }
                if($v['unit']==3){
                    $list[$k]['unit']='m³';
                }
                if($v['trans_method']==1){
                    $list[$k]['trans_method']='快递';
                }
                if($v['trans_method']==2){
                    $list[$k]['trans_method']='EMS';
                }
                if($v['trans_method']==3){
                    $list[$k]['trans_method']='平邮';
                }
            }

            $this->assign('list',$list);
            $this->assign('template_name', $template_name);
            $this->assign('tem_id', $tem_id);
        }

        $this->display();
    }

    /**
     * @desc:   添加默认运费模板
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-11-30 14:04
     */

    public function defaultAdd()
    {
        if($_GET['tem_id']){
            $this->assign('tem_id',$_GET['tem_id']);
            $this->display('defaultUpdate');
        }
    }

    /**
     * @desc:   修改默认运费模板
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-11-30 14:05
     */

    public function defaultUpdate()
    {
        if (!IS_POST) {
            if ($_GET['id']) {
                $Object = D(CONTROLLER_NAME, 'Logic');
                $row = $Object->findRowOne(I('get.'));
                if ($row) {
                    $this->assign('row', $row);
                }
            }
            $this->display('defaultUpdate');
        } else {
            $Object = D(CONTROLLER_NAME, 'Logic');
            $result = $Object->defaultUpdate(I('post.'));

            if ($result) {

                $this->success($Object->getLogicSuccess());
            } else {

                $this->error($Object->getLogicError());
            }
        }

    }

    /**
     * @desc:   删除默认运费模板
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-11-30 15:05
     */

    function defaultDelete()
    {
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->setDefaultStatus(I('request.'));
        if ($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }

    /**
     * @desc:   地区运费模板列表
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-12-02 15:05
     */

    public function areaIndex()
    {
        if($_GET['id']) {
            $template = M('MerchantTemplate')->field('id,template_name')->where(array('id' => $_GET['id'], 'status' => array('neq', 9)))->select();
            foreach( $template as $k=>$v ){
                $template_name = $v['template_name'];
                $tem_id = $v['id'];
            }

            $list = M('TemplateList')->where(array('tem_id'=>$_GET['id'],'trans_address'=>array('neq', 0),'status' => array('neq', 9)))->select();
            foreach( $list as $k => $v ){
                if($v['unit']==1){
                    $list[$k]['unit']='件';
                }
                if($v['unit']==2){
                    $list[$k]['unit']='kg';
                }
                if($v['unit']==3){
                    $list[$k]['unit']='m³';
                }
                if($v['trans_method']==1){
                    $list[$k]['trans_method']='快递';
                }
                if($v['trans_method']==2){
                    $list[$k]['trans_method']='EMS';
                }
                if($v['trans_method']==3){
                    $list[$k]['trans_method']='平邮';
                }
            }

            foreach( $list as $k=>$v){
                foreach (explode(',',$v['trans_address']) as $key=>$val){
                  $a[$key] = M('region')->field('region_name')->where(array('id'=>$val))->select();
                  $list[$k]['address'] .= $a[$key][0]['region_name'].',';
                }

            }

            $this->assign('list',$list);
            $this->assign('template_name', $template_name);
            $this->assign('tem_id', $tem_id);
        }
        $this->display();
    }

    /**
     * @desc:   添加地区运费模板
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-12-03 16:05
     */

    public function areaAdd()
    {
        if($_GET['tem_id']){
            $this->assign('tem_id',$_GET['tem_id']);
        }

        $area = M('region')->field('id,region_name,belong_area_id')->select();

        foreach ($area as $k => $v ) {
            if($v['belong_area_id']==1){
                $area_ec[$k]['region_name']=$v['region_name'];
                $area_ec[$k]['id']=$v['id'];
                $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                foreach($city[$k] as $key =>$val ){
                    $area_ec[$k]['city'][$key]['city_name'] = $val['region_name'];
                    $area_ec[$k]['city'][$key]['id'] = $val['id'];
                }
            }
            if($v['belong_area_id']==2){
                $area_sc[$k]['region_name']=$v['region_name'];
                $area_sc[$k]['id']=$v['id'];
                $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                foreach($city[$k] as $key =>$val ){
                    $area_sc[$k]['city'][$key]['city_name'] = $val['region_name'];
                    $area_sc[$k]['city'][$key]['id'] = $val['id'];
                }
            }
            if($v['belong_area_id']==3){
                $area_mc[$k]['region_name']=$v['region_name'];
                $area_mc[$k]['id']=$v['id'];
                $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                foreach($city[$k] as $key =>$val ){
                    $area_mc[$k]['city'][$key]['city_name'] = $val['region_name'];
                    $area_mc[$k]['city'][$key]['id'] = $val['id'];
                }
            }
            if($v['belong_area_id']==4){
                $area_nc[$k]['region_name']=$v['region_name'];
                $area_nc[$k]['id']=$v['id'];
                $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                foreach($city[$k] as $key =>$val ){
                    $area_nc[$k]['city'][$key]['city_name'] = $val['region_name'];
                    $area_nc[$k]['city'][$key]['id'] = $val['id'];
                }
            }
            if($v['belong_area_id']==5){
                $area_sw[$k]['region_name']=$v['region_name'];
                $area_sw[$k]['id']=$v['id'];
                $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                foreach($city[$k] as $key =>$val ){
                    $area_sw[$k]['city'][$key]['city_name'] = $val['region_name'];
                    $area_sw[$k]['city'][$key]['id'] = $val['id'];
                }
            }
            if($v['belong_area_id']==6){
                $area_ne[$k]['region_name']=$v['region_name'];
                $area_ne[$k]['id']=$v['id'];
                $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                foreach($city[$k] as $key =>$val ){
                    $area_ne[$k]['city'][$key]['city_name'] = $val['region_name'];
                    $area_ne[$k]['city'][$key]['id'] = $val['id'];
                }
            }
            if($v['belong_area_id']==7){
                $area_nw[$k]['region_name']=$v['region_name'];
                $area_nw[$k]['id']=$v['id'];
                $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                foreach($city[$k] as $key =>$val ){
                    $area_nw[$k]['city'][$key]['city_name'] = $val['region_name'];
                    $area_nw[$k]['city'][$key]['id'] = $val['id'];
                }
            }
            if($v['belong_area_id']==8){
                $area_hmt[$k]['region_name']=$v['region_name'];
                $area_hmt[$k]['id']=$v['id'];
                $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                foreach($city[$k] as $key =>$val ){
                    $area_hmt[$k]['city'][$key]['city_name'] = $val['region_name'];
                    $area_hmt[$k]['city'][$key]['id'] = $val['id'];
                }
            }
        }
        $this->assign('area_ec',$area_ec);
        $this->assign('area_sc',$area_sc);
        $this->assign('area_mc',$area_mc);
        $this->assign('area_nc',$area_nc);
        $this->assign('area_sw',$area_sw);
        $this->assign('area_ne',$area_ne);
        $this->assign('area_nw',$area_nw);
        $this->assign('area_hmt',$area_hmt);

        $this->display('areaUpdate');
    }

    /**
     * @desc:   修改地区运费模板
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-12-03 17:05
     */

    public function areaUpdate()
    {
        if (!IS_POST) {
            if ($_GET['id']) {
                $areas ='';
                $list = M('TemplateList')->where(array('id'=>$_GET['id'],'trans_address'=>array('neq', 0),'status' => array('neq', 9)))->select();
                foreach( $list as $k=>$v){
                    foreach (explode(',',$v['trans_address']) as $key=>$val){
                        $address[$key] = M('region')->field('region_name')->where(array('id'=>$val))->select();
                        $areas .= $address[$key][0]['region_name'].',';
                    }
                    $city_id=explode(',',$v['trans_address']);
                }
                $this->assign('city_id',$city_id);


                $area = M('region')->field('id,region_name,belong_area_id')->select();

                foreach ($area as $k => $v ) {
                    if($v['belong_area_id']==1){
                        $area_ec[$k]['region_name']=$v['region_name'];
                        $area_ec[$k]['id']=$v['id'];
                        $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                        foreach($city[$k] as $key =>$val ){
                            $area_ec[$k]['city'][$key]['city_name'] = $val['region_name'];
                            $area_ec[$k]['city'][$key]['id'] = $val['id'];

                        }
                    }
                    if($v['belong_area_id']==2){
                        $area_sc[$k]['region_name']=$v['region_name'];
                        $area_sc[$k]['id']=$v['id'];
                        $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                        foreach($city[$k] as $key =>$val ){
                            $area_sc[$k]['city'][$key]['city_name'] = $val['region_name'];
                            $area_sc[$k]['city'][$key]['id'] = $val['id'];
                        }
                    }
                    if($v['belong_area_id']==3){
                        $area_mc[$k]['region_name']=$v['region_name'];
                        $area_mc[$k]['id']=$v['id'];
                        $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                        foreach($city[$k] as $key =>$val ){
                            $area_mc[$k]['city'][$key]['city_name'] = $val['region_name'];
                            $area_mc[$k]['city'][$key]['id'] = $val['id'];
                        }
                    }
                    if($v['belong_area_id']==4){
                        $area_nc[$k]['region_name']=$v['region_name'];
                        $area_nc[$k]['id']=$v['id'];
                        $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                        foreach($city[$k] as $key =>$val ){
                            $area_nc[$k]['city'][$key]['city_name'] = $val['region_name'];
                            $area_nc[$k]['city'][$key]['id'] = $val['id'];
                        }
                    }
                    if($v['belong_area_id']==5){
                        $area_sw[$k]['region_name']=$v['region_name'];
                        $area_sw[$k]['id']=$v['id'];
                        $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                        foreach($city[$k] as $key =>$val ){
                            $area_sw[$k]['city'][$key]['city_name'] = $val['region_name'];
                            $area_sw[$k]['city'][$key]['id'] = $val['id'];
                        }
                    }
                    if($v['belong_area_id']==6){
                        $area_ne[$k]['region_name']=$v['region_name'];
                        $area_ne[$k]['id']=$v['id'];
                        $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                        foreach($city[$k] as $key =>$val ){
                            $area_ne[$k]['city'][$key]['city_name'] = $val['region_name'];
                            $area_ne[$k]['city'][$key]['id'] = $val['id'];
                        }
                    }
                    if($v['belong_area_id']==7){
                        $area_nw[$k]['region_name']=$v['region_name'];
                        $area_nw[$k]['id']=$v['id'];
                        $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                        foreach($city[$k] as $key =>$val ){
                            $area_nw[$k]['city'][$key]['city_name'] = $val['region_name'];
                            $area_nw[$k]['city'][$key]['id'] = $val['id'];
                        }
                    }
                    if($v['belong_area_id']==8){
                        $area_hmt[$k]['region_name']=$v['region_name'];
                        $area_hmt[$k]['id']=$v['id'];
                        $city[$k] =  M('region')->field('id,region_name')->where(array('parent_id'=>$v['id']))->select();
                        foreach($city[$k] as $key =>$val ){
                            $area_hmt[$k]['city'][$key]['city_name'] = $val['region_name'];
                            $area_hmt[$k]['city'][$key]['id'] = $val['id'];
                        }
                    }
                }
                $this->assign('area_ec',$area_ec);
                $this->assign('area_sc',$area_sc);
                $this->assign('area_mc',$area_mc);
                $this->assign('area_nc',$area_nc);
                $this->assign('area_sw',$area_sw);
                $this->assign('area_ne',$area_ne);
                $this->assign('area_nw',$area_nw);
                $this->assign('area_hmt',$area_hmt);
                $this->assign('areas',$areas);

                $Object = D(CONTROLLER_NAME, 'Logic');
                $row = $Object->findRowOne(I('get.'));

                if ($row) {
                    $this->assign('row', $row);
                }
            }
            $this->display('areaUpdate');
        } else {
            $Object = D(CONTROLLER_NAME, 'Logic');
            $result = $Object->areaUpdate(I('post.'));

            if ($result) {
                $this->success($Object->getLogicSuccess());
            } else {

                $this->error($Object->getLogicError());
            }
        }
    }

    /**
     * @desc:   删除地区运费模板
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-12-04 10:05
     */

    public function areaDelete()
    {
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->setDefaultStatus(I('request.'));
        if ($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }

    /**
     * @desc:   包邮模板列表
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-12-04 11:05
     */

    public function postIndex()
    {
        if($_GET['id']) {
            $template = M('MerchantTemplate')->field('id,template_name')->where(array('id' => $_GET['id'], 'status' => array('neq', 9)))->select();
            foreach( $template as $k=>$v ){
                $template_name = $v['template_name'];
                $tem_id = $v['id'];
            }

            $list = M('EfPostage')->where(array('t_id'=>$_GET['id'],'status' => array('neq', 9)))->select();
            foreach( $list as $k => $v ){
                if($v['ef_postage_condition']==1){
                    $list[$k]['condition']='满'.$v['condition_fill_one'].'件包邮';
                }
                if($v['ef_postage_condition']==2){
                    $list[$k]['condition']='满'.$v['condition_fill_one'].'元包邮';
                }
                if($v['ef_postage_condition']==3){
                    $list[$k]['condition']='满'.$v['condition_fill_one'].'件'.$v['condition_fill_two'].'元包邮';
                }

                if($v['ef_postage_type']==1){
                    $list[$k]['ef_postage_type']='快递';
                }
                if($v['ef_postage_type']==2){
                    $list[$k]['ef_postage_type']='EMS';
                }
                if($v['ef_postage_type']==3){
                    $list[$k]['ef_postage_type']='平邮';
                }
            }

            foreach( $list as $k=>$v){
                foreach (explode(',',$v['ef_postage_area']) as $key=>$val){
                    $a[$key] = M('region')->field('region_name')->where(array('id'=>$val))->select();
                    $list[$k]['address'] .= $a[$key][0]['region_name'].',';
                }
            }
            $this->assign('list',$list);
            $this->assign('template_name', $template_name);
            $this->assign('tem_id', $tem_id);
        }
        $this->display();
    }


    /**
     * @desc:   添加包邮模板
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-12-04 13:05
     */

    public function postAdd()
    {
        if($_GET['t_id']){
            $this->assign('t_id',$_GET['t_id']);
        }
        $area = M('region')->field('id,region_name,belong_area_id')->select();

        foreach ($area as $k => $v ) {
            if($v['belong_area_id']==1){
                $area_ec[$k]['region_name']=$v['region_name'];
                $area_ec[$k]['id']=$v['id'];
            }
            if($v['belong_area_id']==2){
                $area_sc[$k]['region_name']=$v['region_name'];
                $area_sc[$k]['id']=$v['id'];
            }
            if($v['belong_area_id']==3){
                $area_mc[$k]['region_name']=$v['region_name'];
                $area_mc[$k]['id']=$v['id'];
            }
            if($v['belong_area_id']==4){
                $area_nc[$k]['region_name']=$v['region_name'];
                $area_nc[$k]['id']=$v['id'];
            }
            if($v['belong_area_id']==5){
                $area_sw[$k]['region_name']=$v['region_name'];
                $area_sw[$k]['id']=$v['id'];
            }
            if($v['belong_area_id']==6){
                $area_ne[$k]['region_name']=$v['region_name'];
                $area_ne[$k]['id']=$v['id'];
            }
            if($v['belong_area_id']==7){
                $area_nw[$k]['region_name']=$v['region_name'];
                $area_nw[$k]['id']=$v['id'];
            }
            if($v['belong_area_id']==8){
                $area_hmt[$k]['region_name']=$v['region_name'];
                $area_hmt[$k]['id']=$v['id'];
            }
        }
        $this->assign('area_ec',$area_ec);
        $this->assign('area_sc',$area_sc);
        $this->assign('area_mc',$area_mc);
        $this->assign('area_nc',$area_nc);
        $this->assign('area_sw',$area_sw);
        $this->assign('area_ne',$area_ne);
        $this->assign('area_nw',$area_nw);
        $this->assign('area_hmt',$area_hmt);

        $this->display('postUpdate');
    }

    /**
     * @desc:   修改包邮模板
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-12-05 10:52
     */

    public function postUpdate()
    {
        if (!IS_POST) {
            if ($_GET['id']) {
                $areas ='';
                $list = M('EfPostage')->where(array('id'=>$_GET['id'],'status' => array('neq', 9)))->select();
                foreach( $list as $k=>$v){
                    foreach (explode(',',$v['ef_postage_area']) as $key=>$val){
                        $address[$key] = M('region')->field('region_name')->where(array('id'=>$val))->select();
                        $areas .= $address[$key][0]['region_name'].',';
                    }
                    $city_id=explode(',',$v['ef_postage_area']);
                }
                $this->assign('city_id',$city_id);

                $area = M('region')->field('id,region_name,belong_area_id')->select();

                foreach ($area as $k => $v ) {
                    if($v['belong_area_id']==1){
                        $area_ec[$k]['region_name']=$v['region_name'];
                        $area_ec[$k]['id']=$v['id'];
                    }
                    if($v['belong_area_id']==2){
                        $area_sc[$k]['region_name']=$v['region_name'];
                        $area_sc[$k]['id']=$v['id'];
                    }
                    if($v['belong_area_id']==3){
                        $area_mc[$k]['region_name']=$v['region_name'];
                        $area_mc[$k]['id']=$v['id'];
                    }
                    if($v['belong_area_id']==4){
                        $area_nc[$k]['region_name']=$v['region_name'];
                        $area_nc[$k]['id']=$v['id'];
                    }
                    if($v['belong_area_id']==5){
                        $area_sw[$k]['region_name']=$v['region_name'];
                        $area_sw[$k]['id']=$v['id'];
                    }
                    if($v['belong_area_id']==6){
                        $area_ne[$k]['region_name']=$v['region_name'];
                        $area_ne[$k]['id']=$v['id'];
                    }
                    if($v['belong_area_id']==7){
                        $area_nw[$k]['region_name']=$v['region_name'];
                        $area_nw[$k]['id']=$v['id'];
                    }
                    if($v['belong_area_id']==8){
                        $area_hmt[$k]['region_name']=$v['region_name'];
                        $area_hmt[$k]['id']=$v['id'];
                    }
                }
                $this->assign('area_ec',$area_ec);
                $this->assign('area_sc',$area_sc);
                $this->assign('area_mc',$area_mc);
                $this->assign('area_nc',$area_nc);
                $this->assign('area_sw',$area_sw);
                $this->assign('area_ne',$area_ne);
                $this->assign('area_nw',$area_nw);
                $this->assign('area_hmt',$area_hmt);
                $this->assign('areas',$areas);
                $Object = D(CONTROLLER_NAME, 'Logic');
                $row = $Object->findRowPostOne(I('get.'));
                if ($row) {
                    $this->assign('row', $row);
                }
            }
            $this->display('postUpdate');
        } else {
            $Object = D(CONTROLLER_NAME, 'Logic');
            $result = $Object->postUpdate(I('post.'));

            if ($result) {
                $this->success($Object->getLogicSuccess());
            } else {

                $this->error($Object->getLogicError());
            }
        }
    }

    /**
     * @desc:   修改地区运费模板
     * @Author: mouthliu@foxmail.com 刘凯龙
     * @Date:   2017-12-05 10:52
     */

    public function postDelete()
    {
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->setPostStatus(I('request.'));
        if ($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }


}
