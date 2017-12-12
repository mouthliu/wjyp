<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class ThemeController extends BaseController {

    function getIndexRelation() {
        $this->assign("theme_list",M('Theme')->field('id,theme_name')->where("status=1")->select());
    }
//
//    function getUpdateRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }
//
//    function getAddRelation() {
//        $this->assign('select',D('Article','Logic')->getSelect('cate_id',I('get.cate_id')));
//    }

    function showGoods(){
        //$this->checkRule(self::$rule);
        $result = D('Theme', 'Logic')->showGoods(I('request.'));
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error("暂无商品");
        }
        $this->getIndexRelation();
        $this->display('themeGoods');

    }

    //获取商品函数
    function getGoods(){
        $good = D('Goods');
        $where['merchant_id'] = $_SESSION['wjyp_merchant']['merchant_admin']['a_id'];
        $where['status'] = 2;
        if(!empty($_POST['gid'])){
            $where['id'] = $_POST['gid'];
            $res = $good->field("id,goods_name,merchant_id,shop_price")->where($where)->find();
//            dump($good->getLastSql());exit;
            if($res){
                //创建节点
                $html = "<tr><td class='goods_id'>{$res['id']}</td><td class='goods_name' data-mid='{$res['merchant_id']}'>{$res['goods_name']}</td><td data-price='{$res['shop_price']}'><a href='javascript:;' class='xuan'>选定</a></td></tr>";
                echo $html;
            }else{
                return false;
            }
            exit;
        }
        if(!empty($_POST['gname'])){
            $where['goods_name'] = array("LIKE","%{$_POST['gname']}%");
            $res = $good->field("id,goods_name,merchant_id,shop_price")->where($where)->select();
            if($res){
                $html = '';
                //创建节点
                foreach($res as $k=>$v){
                    $html .= "<tr><td class='goods_id'>{$v['id']}</td><td class='goods_name' data-mid='{$v['merchant_id']}'>{$v['goods_name']}</td><td data-price='{$v['shop_price']}'><a href='javascript:;' class='xuan'>选定</a></td></tr>";
                }

                echo $html;
            }else{
                return false;
            }
            exit;
        }
    }

    public function addThemeGoods(){
        //获取到所有的主题
        $themeList = M('Theme')->field('id,theme_name')->select();
        foreach($themeList as $k=>$v){
            $tList[$v['id']] = $v['theme_name'];
        }
        $this->assign('themeList',$tList);
        $request = I('request.');
        $result = D('Theme','Logic')->addThemeGoods($request);
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        }
        $this->display('Theme/addThemeGoods');
    }

    public function doAddThemeGoods(){
        //将过来的商品进行修改
        $request = I('request.');
        if(empty($_POST['ids'])){
            $this->error('请选择商品');
        }

        $where['id'] = array('IN',$_POST['ids']);
        $res = D('Goods')->where($where)->save(array('theme_id'=>$request['theme_id']));
        if($res){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }
    //批量移出主题活动
    function removeThemeGoods(){
        $request = I('request.');
        if(empty($request['ids'])){
            $this->error('请选择商品');
        }

        $where['id'] = array('IN',$request['ids']);
        $res = D('Goods')->where($where)->save(array('theme_id'=>0));
        if($res){
            $this->success('移出成功');
        }else{
            $this->error('移出失败');
        }
    }
    function apply(){
        //获取到所有的主题
        $request = I('request.');
        $param['order'] = 'create_time DESC';//排序
        $param['page_size'] = C('LIST_ROWS'); //页码
        $param['parameter'] = $request; //拼接参数

        $result = D('ThemeApply')->getList($param);

        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        }
        $this->display('apply');
    }

    /**
     * 设置申请
     */
    function setApply(){
        $res = M('ThemeApply')->where("id = {$_GET['id']}")->save(array('status'=>1));
        if($res){
            //移出主题
            $goods_id = M('ThemeApply')->where("id = {$_GET['id']}")->getField('goods_id');
            $result = D('Goods')->where("id = {$goods_id}")->save(array('theme_id'=>0));
            if($result){
                $this->success('执行成功');
            }else{
                $this->error('执行失败');
            }
        }
    }
}
