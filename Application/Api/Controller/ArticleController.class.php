<?php
namespace Api\Controller;

class ArticleController extends BaseController{

    /**
     * 初始化
     */
    public function _initialize(){
        parent::_initialize();
    }

    /**
     * 获取APP文章
     * 类型: type  1注册 服务条款
     */
    public function getArticle()
    {
        if (!in_array($_POST['type'], array('1'))) {
            apiResponse('0', '类型错误');
        }
        switch ($_POST['type']) {
            case 1:
                $article_id = 2;
                break;
            default:
                $article_id = 2;
                break;
        }
        $where['id']     = $article_id;
        $where['status'] = array('neq', 9);
        $info            = M('Article')->where($where)->field('title,content')->find();
        if (!$info) {
            apiResponse('0', '信息查询失败');
        }
        preg_match_all('/src=\"\/?(.*?)\"/', $info['content'], $match);
        foreach ($match[1] as $key => $src) {
            if (!strpos($src, '://')) {
                $info['content'] = str_replace('/' . $src, C('API_URL') . "/" . $src . "\" width=100%", $info['content']);
            }
        }
        apiResponse('1', '请求成功', $info);
    }

    /**
     * 关于我们
     */
    public function aboutUs(){
        $config = D('Config')->parseList();
        $result_data['company_name'] = $config['COMPANY_NAME']?$config['COMPANY_NAME']:'';
        $result_data['copyright'] = $config['CONFIG_COPYRIGHT']?$config['CONFIG_COPYRIGHT']:'';
        apiResponse('1','请求成功',$result_data);
    }

    /**
     * 意见反馈问题类型
     */
    public function feedbackType(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);

        $result_data = array();
        $user_info = M('User')->where(array('id'=>$user_id,'status'=>array('eq',1)))->field('id as user_id,real_name')->find();
        if(!$user_info){
            apiResponse('-1','该账号已被删除或已被禁用');
        }

        $feedback_type = M('FeedbackType')->where(1)->field('id as f_type_id,f_type_name')->select();
        $feedback_type = $feedback_type?$feedback_type:array();

        $result_data['user_id']   = $user_info['user_id'];
        $result_data['real_name'] = $user_info['real_name']?$user_info['real_name']:'未实名认证';
        $result_data['feedback_type'] = $feedback_type;
        apiResponse('1','请求成功',$result_data);
    }

    /**
     * 意见反馈
     * 传递参数的方式：post
     * 需要传递的参数：
     * 问题类型id：f_type_id
     * 意见反馈内容:content
     */
    public function feedback(){
        $user_id = $this->checkLogin();
        $this->returnNotLoginMsg($user_id);
        $user_info = M('User')->where(array('id'=>$user_id,'status'=>array('eq',1)))->field('id as user_id,real_name')->find();
        if(!$user_info){
            apiResponse('-1','该账号已被删除或已被禁用');
        }
        $f_type_list = M('FeedbackType')->where(array('id'=>$_POST['f_type_id']))->find();
        if(!$f_type_list){
            apiResponse('0','类型查询失败');
        }
        if(empty($_POST['content'])){
            apiResponse('0','请描述您的问题');
        }
        $data['f_type_id']   = $_POST['f_type_id'];
        $data['f_type_name'] = $f_type_list['f_type_name'];
        $data['m_id']        = $user_info['user_id'];
        $data['real_name']   = $user_info['real_name'] ? $user_info['real_name'] : '';
        $data['content']     = $_POST['content'];
        $data['create_time'] = time();
        $res = M('Feedback')->data($data)->add();
        if($res){
            apiResponse('1','意见反馈成功');
        }else{
            apiResponse('0','意见反馈失败');
        }
    }

    /**
     * 帮助中心
     * 传递参数的方式：post
     * 需要传递的参数：
     * 类型:type:1商家篇，2用户篇，3运营中心篇
     */
    public function helpCenter(){
        if(!in_array($_POST['type'],array('1','2','3'))){
            apiResponse('0','类型错误');
        }
        $list = M('HelpCenter')->where(array('type'=>$_POST['type']))->field('title,content')->order('sort desc')->select();
        $list = $list?$list:array();
        $message = empty($list)?'暂无数据':'请求成功';
        apiResponse('1',$message,$list);
    }

}