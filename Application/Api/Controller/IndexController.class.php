<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 团购商品模块控制器
 * Class IndexController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class IndexController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取首页函数
     * 请求方式：POST
     */
    public function index(){
        $user_id = $this->checkLogin();
        D('Index','Logic')->index(I('post.'),$user_id);
    }
    /**
     * 获取无界头条列表
     */
    public function headLineList(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        $hed = M('Headlines')->field("id AS headlines_id,title,source,logo")
            ->where("status = 1")
            ->order("sort DESC")
            ->page($_POST['p'],10)
            ->select();
        $count = M('Headlines')->where("status = 1")->count();
        if(!$hed){
            $msg = $_POST['p']==1 ? '暂无数据':'无更多数据';
            apiResponse('0',$msg);
        }
        foreach($hed as $k=>$v){
            $hed[$k]['logo'] = D('File')->getOneFilePath($v['logo']);
        }
        apiResponse('1','获取成功',$hed,$count);
    }
    /**
     * 获取无界头条详情
     */
    public function headInfo(){
        if(empty($_POST['headlines_id'])){
            apiResponse('0','请输入头条id');
        }
        $info = M('Headlines')->where("id={$_POST['headlines_id']}")->find();
        if(!$info){
            apiResponse('0','暂无内容');
        }
        $info['logo'] = D('File')->getOneFilePath($info['logo']);
        $info['create_time'] = date('Y-m-d',$info['create_time']);
        $info['update_time'] = date('Y-m-d',$info['update_time']);
        unset($info['is_index_show']);
        unset($info['status']);
        unset($info['sort']);
        apiResponse('1','获取成功',$info);
    }
    function up(){
        $file = '11.jpg';
        $res = api('UploadPic/upPic', array($file));

        apiResponse('1','11',$res);
    }
}