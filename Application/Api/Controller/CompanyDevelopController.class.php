<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 企业孵化模块控制器
 * Class CompanyDevelopController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class CompanyDevelopController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取企业孵化列表
     * 请求方式：post
     * 请求参数：
     * 分页参数: p
     */
    public function companyList(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        D('CompanyDevelop','Logic')->companyList(I('post.'));
    }

    /**
     * 企业简介
     */
    public function companyInfo(){
        if(empty($_POST['company_id'])){
            apiResponse('0','请输入企业ID');
        }
        D('CompanyDevelop','Logic')->companyInfo(I('post.'));

    }
}