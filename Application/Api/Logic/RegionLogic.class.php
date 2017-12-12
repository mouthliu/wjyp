<?php
namespace Api\Logic;

/**
 * Class RegionLogic
 * @package Api\Logic
 * 逻辑层  省市区模块
 *
 */
class RegionLogic extends BaseLogic{

    /**
     * 获取地区
     * @param int $parent_id
     */
    public function getRegion($parent_id = 0){
        $mod = M('Region');
        $where['parent_id'] = $parent_id;
        $count = $mod->where($where)->count();
        $result = $mod->where($where)->field('id,region_name')->select();
        if(!$result){
            apiResponse('1','暂无数据');
        }else{
            apiResponse('1','获取成功',$result,$count);
        }
    }
}