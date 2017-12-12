<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 评论模块控制器
 * Class CommentController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class CommentController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 评论列表
     * 传递参数的方式：post
     * 需要传递的参数：
     * 分页参数：p
     * 商品id : goods_id(可选)
     */
    public function commentList(){
        $user_id = $this->checkLogin();
        if(empty($_POST['goods_id']) && empty($user_id)){
            apiResponse('0','请输入查询条件');
        }

        if(empty($_POST['p'])){
            apiResponse('0','分页参数不能为空');
        }
        $_POST['user_id'] = $user_id;
        $res = D('Comment','Logic')->commentList(I('post.'));
        if($res){
            apiResponse('1','请求成功',$res['list'],$res['count']);
        }else{
            $message = $_POST['p']==1?'您暂无评论':'无更多评论';
            apiResponse('1',$message);
        }
    }

}