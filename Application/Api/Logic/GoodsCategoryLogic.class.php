<?php
namespace Api\Logic;

/**
 * Class GoodsCategoryLogic
 * @package Api\Logic
 * 逻辑层 分类模块
 *
 */
class GoodsCategoryLogic extends BaseLogic{

    /**
     * 获取分类首页
     * @param array $request
     */
    public function cateIndex($request = array(),$user_id = 0){
        $list['msg_tip'] = '0';
        if($user_id){
            //获取消息提醒数
            $list['msg_tip'] = D('UserMessage','Logic')->getTips($user_id);
        }
        //1.获取顶级分类
        $mod = M('GoodsCategory');
        $where['parent_id'] = 0;
        $where['status'] = 1;
        $list['top_cate'] = $mod
            ->field('id AS cate_id,short_name,name')
            ->where($where)
            ->order("sort ASC,id ASC")
            ->select();
        if(!$list['top_cate']){
             apiResponse('0','暂无顶级分类');
        }
        if(empty($request['cate_id'])){
            //默认显示第一个的
            $request['cate_id'] = $list['top_cate'][0]['cate_id'];
        }
        //2.获取当前第一个分类下面的子类
        $list['two_cate'] = D('GoodsCategory','Logic')->getCate($request['cate_id']);
        apiResponse('1','获取成功',$list);
    }

    /**
     * 获取顶级分类
     * @param array $request
     */
    public function topNav(){
        $list = $this->getChildCate(0,1);
        if($list){
            $count = count($list);
            return array('list'=>$list,'count'=>$count);
        }
    }

    /**
     * 根据父级获取下一级分类
     * 参数：
     * 父级id : $pid
     * 扥也参数 p
     */
    public function getChildCate($pid = 0,$p = 1,$alias_name = 'cate_id',$is_img = 0){
        $mod = M('GoodsCategory');
        $where['parent_id'] = $pid;
        $where['status'] = 1;
        if($is_img == 1){
            $list = $mod
                ->field('id AS '.$alias_name.',short_name,name,cate_img')
                ->where($where)
//                ->page($p,10)
                ->order("sort ASC,id ASC")
                ->select();
            foreach($list as $k=>$v){
                $list[$k]['cate_img'] = D('File')->getOneFilePath($v['cate_img'],C('API_URL').'/Uploads/Cate/default.png');
            }
        }else{
            $list = $mod
                ->field('id AS '.$alias_name.',short_name,name')
                ->where($where)
//                ->page($p,10)
                ->order("sort ASC,id ASC")
                ->select();
        }
        if(!$list){
            $msg = $p==1?'暂无子类':'暂无更多分类';
            apiResponse('1',$msg);
        }
        return $list;
    }

    /**
     * 根据父级获取下一级分类ios
     * 参数：
     * 父级id : $pid
     * 扥也参数 p
     */
    public function getChildCateIos($pid = 0,$alias_name = 'cate_id',$is_img = 0){
        $mod = M('GoodsCategory');
        $where['parent_id'] = $pid;
        $where['status'] = 1;
        if($is_img == 1){
            $list = $mod
                ->field('id AS '.$alias_name.',short_name,name,cate_img')
                ->where($where)
                ->order("sort ASC,id ASC")
                ->select();
            foreach($list as $k=>$v){
                $list[$k]['cate_img'] = D('File')->getOneFilePath($v['cate_img'],C('API_URL').'/Uploads/Cate/default.png');
            }
        }else{
            $list = $mod
                ->field('id AS '.$alias_name.',short_name,name')
                ->where($where)
                ->order("sort ASC,id ASC")
                ->select();
        }
        if(!$list){
            apiResponse('0','暂无分类');
        }
        return $list;
    }


    /**
     * 获取某一分类下所有的分类
     * @param $pid
     * @return array
     */
    public function getCate($pid=0){
        $data = array();
        if(!$pid){
            return $data;
        }
        $mod                = M("GoodsCategory");
        $where['parent_id'] = $pid;
        $where['status']    = 1;

        $list = $mod->field('id AS cate_id,short_name,name,hot_brand,cate_img')
                    ->where($where)
                    ->order("sort ASC,id ASC")
                    ->select();
        foreach($list as $k=>$v){
            $list[$k]['cate_img'] = D('File')->getOneFilePath($v['cate_img'],C('API_URL').'/Uploads/Cate/default.png');
        }
        //遍历
        if ($list) {
            foreach ($list as $k => $v) {
                //获取到热门品牌
                if (!empty($v['hot_brand'])) {
                    unset($hot_brand);
                    $hot_brand = D('GoodsBrand', 'Logic')->getBrandInfo(explode(',', $v['hot_brand']));
                    $v['host_brand'] = $hot_brand?$hot_brand:array();
                }
                //$v['underCate'] 存放二级分类
                $v['three_cate'] = $this->getCate($v['cate_id'], $p);
                if(empty($v['three_cate'])){
                    unset($v['three_cate']);
                }
                $data[]         = $v;
            }
        }
        return $data;
    }

    /**
     * 获取某一分类下所有分类的id
     * @param int $pid
     */
    public function getCateIds($pid = 0){
        $mod=M("GoodsCategory");
        $list=$mod->field('id')->where("parent_id='{$pid}' AND status=1")->order("sort ASC,id ASC")->select();
        $r = '';
        //遍历
        if($list){
            foreach($list as $k=>$v){
                //$v['underCate'] 存放二级分类
                $r .= $v['id'].',';
                $r .= $this->getCateIds($v['id']);
            }
        }
        return $r;
    }


}