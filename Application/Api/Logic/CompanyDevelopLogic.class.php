<?php
namespace Api\Logic;

/**
 * Class CompanyDevelopLogic
 * @package Api\Logic
 * 逻辑层  企业模块
 *
 */
class CompanyDevelopLogic extends BaseLogic{

    /**
     * 获取企业列表
     * @param array $request
     */
    public function companyList($request = array()){
        //.获取广告图
        $list['ads'] = D('Ads','Logic')->adsList(array('position'=>'20'));
        $mod = M('CompanyDevelop');
        $where['status'] = 2; //已通过
        $count = $mod->where($where)->count();
        $list['mer_list'] = $mod->field('id AS company_id,face_img,merchant_id')
            ->where($where)
            ->order('create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list['mer_list']){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('0',$msg,$list);
        }
        foreach($list['mer_list'] as $k=>$v){
            $list['mer_list'][$k]['face_img'] = D('File')->getOneFilePath($v['face_img']);
        }

        apiResponse('1','获取数据成功',$list,$count);
    }

    /**
     * 企业简介
     */
    public function companyInfo($request = array()){
        $mod = M('CompanyDevelop');
        $content = $mod->where("id = {$request['company_id']}")->getField("content");
        $merchant_id = $mod->where("id = {$request['company_id']}")->getField("merchant_id");
        if(!$content){
            apiResponse('0','暂无简介');
        }
        apiResponse('1','获取成功',array('content'=>$content,'merchant_id'=>$merchant_id));
    }
}