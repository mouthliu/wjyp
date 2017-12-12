<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 省市区模块控制器
 * Class RegionController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class RegionController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取省市区函数
     * 传递参数方式 : post
     * 需要传递的参数:
     *   上一级地域id : parent_id
     */
    public function getRegion(){
        //判断参数
        $parent_id = empty($_POST['parent_id']) ? 1 : $_POST['parent_id'];

        D('Region', 'Logic')->getRegion($parent_id);
    }
    //    public function test(){
//        $mod = M('Region');
//        $res = $mod->where("parent_id = 9")->select();
//        foreach($res as $k=>$v){
//            if($v['id'] != '120'){
//                $data['letter'] = $v['letter'];
//                $data['parent_id'] = $v['id'];
//                $data['region_name'] = $v['region_name'];
//                $data['region_type'] = 3;
//                $data['lng'] = $v['lng'];
//                $data['lat'] = $v['lat'];
//               $res1 =  $mod->add($data);die();
//            }
//        }
//        apiResponse('1','',$res1);
//    }
}