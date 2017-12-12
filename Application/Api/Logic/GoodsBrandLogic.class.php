<?php
namespace Api\Logic;

/**
 * Class GoodsBrandLogic
 * @package Api\Logic
 * 逻辑层 分类模块
 *
 */
class GoodsBrandLogic extends BaseLogic{

    /**
     * 根据品牌id获取品牌信息
     * 请求参数：
     *   品牌ID : brand_id
     */
    function getBrandInfo($ids = array()){
        $mod = D('GoodsBrand');
        $where['status'] = 1;
        $where['id'] = array('IN',$ids);
        $list = $mod->field('id As brand_id,brand_logo,brand_name')
                   ->where($where)
                   ->select();
        if(!$list){
            return false;
        }else{
            foreach($list as $k=>$v){
                $list[$k]['brand_logo'] = D('File')->getOneFilePath($v['brand_logo']);
            }
            return $list;
        }

    }
}