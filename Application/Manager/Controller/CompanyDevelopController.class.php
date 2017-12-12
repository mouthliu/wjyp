<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class CompanyDevelopController extends BaseController {

    protected function getIndexRelation(){

    }
    protected function getUpdateRelation(){

    }

    protected function getAddRelation(){

    }
    //获取商品函数
    function getMerchants(){
        $good = D('Merchant');
        $where['status'] = 1;
        if(!empty($_POST['merchant_name'])){
            $where['merchant_name'] = array("LIKE","%{$_POST['merchant_name']}%");
            $res = $good->field("id,merchant_name")->where($where)->select();

            if($res){
                $html = '';
                //创建节点
                foreach($res as $k=>$v){
                    $html .= "<tr><td class='goods_id'>{$v['id']}</td><td class='goods_name' data-mid='{$v['id']}'>{$v['merchant_name']}</td><td ><a href='javascript:;' class='xuan'>选定</a></td></tr>";
                }
                echo $html;
            }else{
                return false;
            }
            exit;
        }
    }
    function setRecommend(){
        $data['is_recommend'] = isset($_POST['is_recommend'])?$_POST['is_recommend']: die('error');
        $res = D('CompanyDevelop')->where("id={$_POST['CompanyDevelop_id']}")->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
}
