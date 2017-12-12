<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 书院模块控制器
 * ClassAcademyController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class AcademyController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 书院文章列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 分页参数：p
     * 分类ID :ac_type_id(可选)
     */
    public function academyIndex(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        D('Academy','Logic')->academyIndex(I('post.'));
    }

    /**
     * 获取文章详情
     * 请求方式L:post
     * 参数：文章id : academy_id
     */
    public function academyInfo(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['academy_id'])){
            apiResponse('0','请选择文章id');
        }
        D('Academy','Logic')->academyInfo($_POST['academy_id'],$user_id);
    }

}