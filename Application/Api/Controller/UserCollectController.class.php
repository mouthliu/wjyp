<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 会员模块控制器
 * Class UserController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class UserCollectController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取我的收藏
     * 传递参数方式：post
     * 需要传递的参数：
     * 分页参数 ：p
     * 类型: type (1 商品(默认) 2店铺 3书院)
     */
    public function collectList(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        //判断参数
        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }

        D('UserCollect','Logic')->collectList(I('post.'),$user_id);
    }

    /**
     * 加入我的收藏
     * 传递参数方式：post
     * 需要传递的参数：
     * 类型: type (1 商品(默认) 2店铺 3书院)
     * id值：id_val
     */
    public function addCollect(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        //判断条件
        if(empty($_POST['type'])){
            apiResponse('0','类型未选择');
        }
        if(!in_array($_POST['type'],array('1','2','3'))){
            apiResponse('0','收藏类型不支持');
        }
        if(empty($_POST['id_val'])){
            apiResponse('0','请输入对应的id');
        }
        D('UserCollect','Logic')->addCollect(I('post.'),$user_id);
    }

    /**
     * 删除收藏品
     * 请求方式:post
     * 请求参数:
     * 收藏品id : collect_ids .通逗号隔开

     */
    function delCollect() {
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['collect_ids'])){
            apiResponse('0','请选择要删除的收藏品');
        }
        D('UserCollect','Logic')->delCollect($_POST['collect_ids']);
    }
    /**
     * 取消收藏
     * 请求方式:post
     * 请求参数:
     *  type 类型
     *  id_val
     */
    function delOneCollect() {
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        if(empty($_POST['type'])){
            apiResponse('0','请选择收藏品类型');
        }
        if(empty($_POST['id_val'])){
            apiResponse('0','请选择收藏品id');
        }
        D('UserCollect','Logic')->delOneCollect(I('post.'),$user_id);
    }

}