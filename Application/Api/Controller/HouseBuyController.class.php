<?php
namespace Api\Controller;
use Think\Controller;

/**
 * 房产购模块控制器
 * Class HouseBuyController
 * @package Api\Controller
 * 注意： 1.不返回null 2.不返回整型（转换成字符串）
 *
 */
class HouseBuyController extends BaseController{
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * 楼盘列表
     *     请求参数:
     *     integral_sort(积分) 1 正序 2倒序
     *     distance_sort（距离） 1 正序 2倒序
     *     price_sort(价格) 1 正序 2倒序
     *     lng lat 用户经纬度
     *     p 分页参数
     */
    public function houseList(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }

        D('HouseBuy','Logic')->houseList(I('post.'));

    }

    /**
     * 楼盘详情页
     *    请求参数 楼盘id
     */
    public function houseInfo(){
        $user_id = $this->checkLogin();
        if(empty($_POST['house_id'])){
            apiResponse('0','请输入楼盘id');
        }
        D('HouseBuy','Logic')->houseInfo(I('post.'),$user_id);
    }

    /**
     * 户型列表
     * 楼盘ID house_id
     */
    public function houseStyleList(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }

        if(empty($_POST['house_id'])){
            apiResponse('0','请输入楼盘id');
        }
        $res = D('HouseBuy','Logic')->houseStyleList(I('post.'));
        apiResponse('1','获取成功',$res['list'],$res['count']);
    }
    /**
     * 户型详情
     *    户型id style_id
     */
    public function styleInfo(){
        if(empty($_POST['style_id'])){
            apiResponse('0','请输入户型id');
        }
        D('HouseBuy','Logic')->styleInfo(I('post.'));
    }

    /**
     * 评价列表------------------------废弃
     */
    public function comment(){
        if(empty($_POST['p'])){
            apiResponse('0','请输入分页参数');
        }
        if(empty($_POST['house_id'])){
            apiResponse('0','请输入楼盘id');
        }
       $res = D('HouseBuy','Logic')->comment(I('post.'));
        if($res){
            apiResponse('1','获取成功',$res['info'],$res['count']);
        }else{
            $msg = $_POST['p']==1 ? '暂无评论':'无更多评论';
            apiResponse('0',$msg);
        }
    }

    /**
     * 评价列表
     * 参数:楼盘ID：house_id
     * 标签ID：label_id
     * 分页参数：p
     */
    public function commentList(){
        $result_data = array();
        if(empty($_POST['house_id'])){
            apiResponse('0','参数不完整');
        }
        if(empty($_POST['p'])){
            apiResponse('0','参数不完整');
        }
        //获取所有的评价标签
        $label_list = M('HouseLabel')->where(array('status'=>array('eq',1)))
            ->field('id as label_id,house_label as label_name')
            ->select();
        $label_list = $label_list?$label_list:array();
        foreach($label_list as $k => $v){
            unset($where);
            $where['house_id'] = $_POST['house_id'];
            $where['label_str'] = array('like','%,'.$v['label_id'].',%');
            $count = M('HouseComment')->where($where)->count();
            $label_list[$k]['num'] = $count?$count.'':'0';
        }
        unset($where);
        $where['car_id'] = $_POST['car_id'];
        $count = M('HouseComment')->where($where)->count();
        $row['label_id'] = '0';
        $row['label_name'] = '全部';
        $row['num'] = $count?$count.'':'0';
        array_unshift($label_list,$row);
        $result_data['label_list'] = $label_list;

        unset($where);
        if($_POST['label_id']){
            $where['label_id'] =$_POST['label_id'];
        }
        $where['house_id'] = $_POST['house_id'];
        $where['status'] = 1;
        $comment_list = M('HouseComment')->where($where)
            ->field('user_id,price,lot,supporting,traffic,environment,label_str,pictures,content,create_time')
            ->limit($_POST['p'],'10')
            ->select();
        foreach($comment_list as $k => $v){
            $comment_list[$k]['comment_star'] = ''.number_format(($v['price']+$v['lot']+$v['supporting']+$v['traffic']+$v['environment'])/5,1);
            if($v['pictures']){
                $comment_list[$k]['pictures_arr'] = D('File')->getArrayFilePath(explode(',',$v['pictures']));
            }else{
                $comment_list[$k]['pictures_arr'] = array();
            }
            if($v['label_str']){
                $label_arr = explode(',',substr($v['label_str'],1,-1));
                $label = array();
                foreach($label_arr as $key =>$value){
                    $label_name = M('HouseLabel')->where(array('id'=>$value))->getField('house_label');
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
        $house_total = M('HouseComment')->where(array('house_id'=>$_POST['house_id']))
            ->field("SUM(price) as price,SUM(lot) as lot,SUM(supporting) as supporting, SUM(traffic) AS traffic, SUM(environment) AS environment,COUNT(id) as all_num")
            ->select();
        if($house_total[0]['all_num']>0){
            $result_data['composite'] = ''.number_format(($house_total[0]['lot']+$house_total[0]['supporting']+$house_total[0]['traffic']+$house_total[0]['price']+$house_total[0]['environment'])/(5*$house_total[0]['all_num']),1);
            $result_data['price'] = ''.number_format($house_total[0]['price']/$house_total[0]['all_num'],1);
            $result_data['lot'] = ''.number_format($house_total[0]['lot']/$house_total[0]['all_num'],1);
            $result_data['supporting'] = ''.number_format($house_total[0]['supporting']/$house_total[0]['all_num'],1);
            $result_data['traffic'] = ''.number_format($house_total[0]['traffic']/$house_total[0]['all_num'],1);
            $result_data['environment'] = ''.number_format($house_total[0]['environment']/$house_total[0]['all_num'],1);
        }else{
            $result_data['composite'] = '0.0';
            $result_data['price'] = '0.0';
            $result_data['lot'] ='0.0';
            $result_data['supporting'] ='0.0';
            $result_data['traffic'] = '0.0';
            $result_data['environment'] = '0.0';
        }
        apiResponse('1','请求成功',$result_data);
    }

}