<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 汽车购模块控制器
 * Class CarBuyController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class CarBuyController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }
    
    /**
     * 汽车筛选页
     * 请求方式: post
     * 请求参数: 无
     */
    public function carSelect(){
        D('CarBuy','Logic')->carSelect();
    }
    /**
     * 汽车列表
     *     请求参数:
     *     车价格区间：min_price max_price
     *     车型：style_id(可选)
     *     车品牌：brand_id（可选）
     *     p 分页参数
     */
    public function carList(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        if(empty($_POST['min_price']) || empty($_POST['max_price'])){
            apiResponse('0','请输入价格区间');
        }
        D('CarBuy','Logic')->carList(I('post.'));
    }
    /**
     * 汽车详情
     *   车id car_id
     */
    public function carInfo(){
        $user_id = $this->checkLogin();
        if(empty($_POST['car_id'])){
            apiResponse('0','请输入车id');
        }
        D('CarBuy','Logic')->carInfo(I('post.'),$user_id);
    }

    /**
     * 获取汽车评论---------------------废弃
     * p:分页参数
     * car_id:汽车id
     */
    public function comment(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        if(empty($_POST['car_id'])){
            apiResponse('0','请输入汽车id');
        }
        $res =  D('CarBuy','Logic')->comment(I('post.'));
        if($res){
            apiResponse('1','获取成功',$res['info'],$res['count']);
        }else{
            $msg = $_POST['p']==1 ? '暂无评论':'无更多评论';
            apiResponse('0',$msg);
        }
    }
    
    /**
     * 评价列表
     * 参数:车ID：car_id
     * 标签ID：label_id
     */
    public function commentList(){
        $result_data = array();
        if(empty($_POST['car_id'])){
            apiResponse('0','参数不完整');
        }
        if(empty($_POST['p'])){
            apiResponse('0','参数不完整');
        }
        //获取所有的评价标签
        $label_list = M('CarLabel')->where(array('status'=>array('eq',1)))
            ->field('id as label_id,car_label as label_name')
            ->select();
        $label_list = $label_list?$label_list:array();
        foreach($label_list as $k => $v){
            unset($where);
            $where['car_id'] = $_POST['car_id'];
            $where['label_str'] = array('like','%,'.$v['label_id'].',%');
            $count = M('CarComment')->where($where)->count();
            $label_list[$k]['num'] = $count?$count.'':'0';
        }
        unset($where);
        $where['car_id'] = $_POST['car_id'];
        $count = M('CarComment')->where($where)->count();
        $row['label_id'] = '0';
        $row['label_name'] = '全部';
        $row['num'] = $count?$count.'':'0';
        array_unshift($label_list,$row);
        $result_data['label_list'] = $label_list;

        unset($where);
        if($_POST['label_id']){
            $where['label_id'] =$_POST['label_id'];
        }
        $where['car_id'] = $_POST['car_id'];
        $where['status'] = 1;
        $comment_list = M('CarComment')->where($where)
            ->field('user_id,exterior,space,controllability,consumption,label_str,pictures,content,create_time')
            ->limit($_POST['p'],'10')
            ->select();
        foreach($comment_list as $k => $v){
            $comment_list[$k]['comment_star'] = ''.number_format(($v['exterior']+$v['space']+$v['controllability']+$v['consumption'])/4,2);
            if($v['pictures']){
                $comment_list[$k]['pictures_arr'] = D('File')->getArrayFilePath(explode(',',$v['pictures']));
            }else{
                $comment_list[$k]['pictures_arr'] = array();
            }
            if($v['label_str']){
                $label_arr = explode(',',substr($v['label_str'],1,-1));
                $label = array();
                foreach($label_arr as $key =>$value){
                    $label_name = M('CarLabel')->where(array('id'=>$value))->getField('car_label');
                    $label[$key]['label'] = $label_name?$label_name:"";
                }
                $comment_list[$k]['label_arr'] = $label;
            }else{
                $comment_list[$k]['label_arr'] = array();
            }
            $comment_list[$k]['create_time'] = date('Y-m-d H:i',$v['create_time']);
            $user_info = M('User')->where(array('id'=>$v['user_id']))->find();
            if($user_info){
                $comment_list[$k]['nickname'] = $user_info['nickname']?$user_info['nickname']:"无界优品用户";
                $comment_list[$k]['head_pic']= D('File')->getOneFilePath($user_info['head_pic'],C('API_URL').'/Uploads/Member/default.png');
            }else{
                $comment_list[$k]['nickname'] = '无界优品用户';
                $comment_list[$k]['head_pic'] = C('API_URL').'/Uploads/Member/default.png';
            }
        }
        $result_data['comment_list'] = $comment_list?$comment_list:array();

        //获取总评分
        $car_total = M('CarComment')->where(array('car_id'=>$_POST['car_id']))
            ->field("SUM(exterior) as exterior,SUM(space) as space,SUM(controllability) as controllability, SUM(consumption) AS consumption,COUNT(id) as all_num")
            ->select();
        if($car_total[0]['all_num']>0){
            $result_data['composite'] = ''.number_format(($car_total[0]['exterior']+$car_total[0]['space']+$car_total[0]['controllability']+$car_total[0]['consumption'])/(5*$car_total[0]['all_num']),1);
            $result_data['exterior'] = ''.number_format($car_total[0]['exterior']/$car_total[0]['all_num'],1);
            $result_data['space'] = ''.number_format($car_total[0]['space']/$car_total[0]['all_num'],1);
            $result_data['controllability'] = ''.number_format($car_total[0]['controllability']/$car_total[0]['all_num'],1);
            $result_data['consumption'] = ''.number_format($car_total[0]['consumption']/$car_total[0]['all_num'],1);
        }else{
            $result_data['composite'] = '0.0';
            $result_data['exterior'] = '0.0';
            $result_data['space'] ='0.0';
            $result_data['controllability'] ='0.0';
            $result_data['consumption'] = '0.0';
        }
        apiResponse('1','请求成功',$result_data);
    }

}