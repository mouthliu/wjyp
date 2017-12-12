<?php

namespace Merchant\Controller;

/**
 * Class AdministratorController
 * @package Merchant\Controller
 * 管理员控制器
 */
class ThemeController extends BaseController {

    protected function getIndexRelation(){
        $this->assign("theme_list",M('Theme')->field('id,theme_name')->where("status=1")->select());
    }

    /**
     * 申请移出
     */
    function apply(){
        $info = M('Goods')->field("merchant_id,id as goods_id,theme_id")->where("id = {$_GET['goods_id']}")->find();
        if($info){
            $data['goods_id'] = $info['goods_id'];
            $data['merchant_id'] = $info['merchant_id'];
            $data['theme_id'] = $info['theme_id'];
            $data['create_time'] = time();
            $res = M('ThemeApply')->add($data);
            $res ? $this->success('已申请') : $this->error('申请失败');
        }else{
            $this->error('暂无数据');
        }
    }
    function removeApply(){
       $res = M('ThemeApply')->where("goods_id = {$_GET['goods_id']}")->delete();
       if($res){
           $this->success('取消成功');
       }else{
           $this->error('取消失败');
       }
    }
}

