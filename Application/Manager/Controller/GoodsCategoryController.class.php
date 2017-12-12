<?php
namespace Manager\Controller;

/**
 * Class ArticleCategoryController
 * @package Manager\Controller
 * 文章分类 控制器
 */
class GoodsCategoryController extends BaseController {

    function getIndexRelation(){
        $this->assign('select',D('GoodsCategory','Logic')->getSelect('parent_id','cate_name'));
    }

    /**
     * 添加时关联数据
     */
    function getAddRelation() {
        $this->assign('select',D('GoodsCategory','Logic')->getSelect('parent_id',I('get.id')));
    }

    /**
     * 修改时关联数据
     */
    function getUpdateRelation() {
        $this->assign('select',D('GoodsCategory','Logic')->getSelect('parent_id',I('get.parent_id')));
    }
    function index(){
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->getList(I('request.'));

        $this->assign('list', $result);
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->getIndexRelation();
        $this->display('test');
    }
    /**
     * 获取旗下热门品牌列表
     */
    function getBrands() {
        if(!empty($_POST['ids'])){
//            $ids = explode(',',$_POST['ids']);
            $where['b.id'] = array('IN',$_POST['ids']);
            $where['b.status'] = 1;
            $list = M('GoodsBrand')->alias("b")->field('b.id,b.brand_name,f.path')->join(C('DB_PREFIX')."file f ON b.brand_logo=f.id")->where($where)->select();
            $html = '';
            if(empty($list)){return false;}

            foreach($list as $k=>$v){
                $str = '<div class="span2">
                <img src="'.__ROOT__.$v['path'].'" alt="" class="img-polaroid " style="max-width:90%;>
                <p data-id="'.$v['id'].'">'.$v['brand_name'].'</p></div>';
                $html .= $str;
            }
            echo $html;
        }
    }

    /**
     * 添加热门品牌
     */
    function changeHotBrand(){
        // 判断是否有分类的ID过来
        if(empty($_GET['id'])){
            $this->error('参数不足');
            die();
        }
        if(empty($_GET['act'])){
            $this->error('参数不足');
            die();
        }
        if($_GET['act'] == 'add'){
            //添加操作
            $ids = M('GoodsCategory')->field('name')->where("id={$_GET['id']}")->find();
            $this->assign('name',$ids['name']);
        }else{
           $ids = M('GoodsCategory')->field('name,hot_brand')->where("id={$_GET['id']}")->find();
            $this->assign('name',$ids['name']);
            $this->assign('row',$ids);
        }
        //获取到所有的品牌
        $this->assign("brand_list",M('GoodsBrand')->alias("b")->field('b.id,b.brand_name,f.path')->join(C('DB_PREFIX')."file f ON b.brand_logo=f.id")->where('b.status=1')->select());
        //加载模板
        $this->display('GoodsCategory/brandUpdate');
    }

    function updateBrand(){
       //判断
        if(empty($_POST['id']) && empty($_POST['model']) && empty($_POST['brand_id'])){
            $this->error("参数不足");
            die();
        }
        //处理
       $hotbrand = implode(',',$_POST['brand_id']);
       $res =  D('GoodsCategory')->where("id={$_POST['id']}")->save(array("hot_brand"=>$hotbrand));
        if($res){
            $this->success("修改成功");
            return true;
        }else{
            $this->error("修改失败");
            return false;
        }
    }

}
