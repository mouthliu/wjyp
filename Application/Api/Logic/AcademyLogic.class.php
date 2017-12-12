<?php
namespace Api\Logic;

/**
 * Class AcademyLogic
 * @package Api\Logic
 * 逻辑层  收货地址模块
 *
 */
class AcademyLogic extends BaseLogic{

    /**
     * 无界书院首页
     * @param array $request
     * @param int $user_id
     */
    public function academyIndex($request = array()){
        //1获取文章分类列表
        $list['ac_type_list'] = M('AcademyType')->field('id AS type_id,type_name')->order('sort DESC')->select();
        $list['ac_type_list'] = $list['ac_type_list']?$list['ac_type_list']:array();
        $first = array('type_id'=>0,'type_name'=>'推荐');
        array_unshift($list['ac_type_list'],$first);
        //2.获取轮播图
        $list['bannerList'] = M('Ads')
            ->field('id AS ads_id,desc,href,picture')
            ->where('position=5 AND status=1')
            ->order('sort DESC')
            ->select();
        foreach($list['bannerList'] as $k=>$v){
            $list['bannerList'][$k]['picture'] = D('File')->getOneFilePath($v['picture']);
        }
        //3.获取文章列表
        if(!empty($request['ac_type_id'])){
           $where['ac_type_id'] = $request['ac_type_id'] ;
        }else{
            $where['is_recommend'] = 1;
        }

        $mod = M('Academy');
        $where['status'] = 1;
        $list['academy_list'] = $mod->field('id AS academy_id,title,logo,page_views,collect_num')
            ->where($where)
            ->order("sort DESC")
            ->page($request['p'],10)
            ->select();
        if(!$list['academy_list']){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('1',$msg,$list,0);
        }
        foreach($list['academy_list'] as $k=>$v){
            $list['academy_list'][$k]['logo'] = D('File')->getOneFilePath($v['logo']);
        }
        $count = $mod->where($where)->count();

        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 获取文章详情dsf
     * @param int $academy_id
     * @param $user_id
     */
    public function academyInfo($academy_id = 0,$user_id){
        $mod = M('Academy');
        $where['status'] = 1;
        $where['id'] = $academy_id;
        $info = $mod->field('id AS academy_id,title,logo,content,source,page_views,collect_num,create_time')
            ->where($where)
            ->find();
        if(!$info){
            apiResponse('0','暂无数据');
        }
        //判断是否收藏
        $is_collect = M("UserCollect")->where("type=3 AND user_id={$user_id} AND id_val={$academy_id} AND status = 1")->getField('id');
        $info['is_collect'] = $is_collect ? '1' : '0';
        //同时设置浏览量
        $mod->where($where)->setInc('page_views');
        $info['logo'] = D('File')->getOneFilePath($info['logo']);
        $info['create_time'] = date('Y-m-d',$info['create_time']);
        //足迹信息
        if(!empty($user_id)){
            D('User','Logic')->recordFoot(3,$user_id,$academy_id);
        }
        apiResponse('1','获取成功',$info);
    }

}