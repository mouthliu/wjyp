<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class GoodsServerController extends BaseController {

//    function getIndexRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('request.cate_id')));
//    }
//
    function getUpdateRelation() {
        $this->assign('cate_list',$this->getCateList('0'));
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
    }
//
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
    function getAddRelation() {
        $this->assign('cate_list',$this->getCateList('0'));
    }
    //添加
    public function doAdd(){

        if (!IS_POST) {
            $this->getAddRelation();
            $this->display('add');
        } else {

            if($_POST['is_default']==1 && !is_array($_POST['cates'])){
                $this->error('请选择分类!');die;
            }
            if(empty($_POST['id'])){
                if(!empty($_POST['cates'])){
                    $_POST['cat_id'] = implode(',',$_POST['cates']);
                }
                $data = D('GoodsServer')->create($_POST);
                if($data){
                    D('GoodsServer')->data($data)->add();
                    $this->success('添加成功',U('GoodsServer/index'));
                }else{
                    $this->error(D('GoodsServer')->getError());
                }
            }else{
                if(!empty($_POST['cates'])){
                    $_POST['cat_id'] = implode(',',$_POST['cates']);
                }
                $data = D('GoodsServer')->create($_POST);
                if($data){
                    D('GoodsServer')->data($data)->save();
                    $this->success('编辑成功',U('GoodsServer/index'));
                }else{
                    $this->error(D('GoodsServer')->getError());
                }
            }
            
        }
    }
}
