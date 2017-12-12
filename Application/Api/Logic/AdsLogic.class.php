<?php
namespace Api\Logic;

/**
 * Class AdsLogic
 * @package Api\Logic
 * 逻辑层  广告轮播模块
 *
 */
class AdsLogic extends BaseLogic{

    public function adsList($request = array()){

        if(empty($request['num'])){
            $request['num'] = 10;
        }
        $mod = M('Ads');
        $where['position'] = $request['position'];
        $where['status'] = 1;
        $res = $mod->field('id AS ads_id,picture,desc,href,position')
            ->where($where)
            ->order('sort DESC')
//            ->limit($request['num'])
            ->select();
        if(!$res){
            return false;
        }
        foreach($res as $k=>$v){
            $res[$k]['picture'] = D('File')->getOneFilePath($v['picture']);
        }
        return $res;
    }

}