<?php
namespace Manager\Controller;

/**
 * Class ArticleController
 * @package Manager\Controller
 * 文章咨询 控制器
 */
class MerchantReferController extends BaseController {

    function getIndexRelation() {
        //获取经营范围
        $range = M("GoodsCategory")->field("id,short_name")->where("parent_id=0")->select();
        $this->assign("range",$range);
    }
    function getUpdateRelation() {

        $user = M('user')->where(array('id'=>$_GET['id']))->find();
        $this->assign('user',$user);
        //获取经营范围
        $range = M("GoodsCategory")->field("id,short_name")->where("parent_id=0")->select();
        //获取招商人员列表
        $group = M('administrator')->where(array('group_id'=>6,'status'=>array('neq',9)))->field('id as a_id,account')->select();
        $this->assign('group',$group);
        $this->assign("range",$range);
    }
    
    function getCateList($pid = 0){
        //根据传过来的pid获取子集
        $list = M("GoodsCategory")->field('id,name,short_name,parent_id')->where("parent_id={$pid}")->select();
        if($list){
            $data = [];
            foreach($list as $k=>$v){
                $v['under'] = $this->getCateList($v['id']);
                $data[] = $v;
            }
        }
        return $data;
    }


    //获取分类函数
    function getChild(){

        if(empty($_POST['rid'])){
            echo "no";
            return false;
        }
        $list = $this->getCateList($_POST['rid']);
        echo json_encode($list);
    }
}
