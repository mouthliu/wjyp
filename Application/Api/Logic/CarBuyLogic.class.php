<?php
namespace Api\Logic;

/**
 * Class CarBuyLogic
 * @package Api\Logic
 * 逻辑层  购物车 模块
 *
 */
class CarBuyLogic extends BaseLogic{
    /**
     * 筛选汽车页
     */
    public function carSelect(){
        //车型
        $style_list = M('CarStyle')->field('id AS style_id,style_name,style_img,true_style_img')
            ->where('status = 1')
            ->order('sort DESC')
            ->select();
        foreach($style_list as $k=>$v){
            $style_list[$k]['style_img'] = D('File')->getOneFilePath($v['style_img']);
            $style_list[$k]['true_style_img'] = D('File')->getOneFilePath($v['true_style_img']);
        }
        $list['style_list'] = $style_list ? $style_list : array();
        $first1 = array('style_id'=>0,'style_name'=>'不限',
                       'style_img'=>C('API_URL').'/Uploads/CarStyle/any.png',
                       'true_style_img'=>C('API_URL').'/Uploads/CarStyle/true_any.png');
        array_unshift($list['style_list'],$first1);
        //品牌
        $brand_list = M('CarBrand')->field('id AS brand_id,brand_name,brand_logo,true_brand_logo')
            ->where('status = 1')
            ->order('sort DESC')
            ->select();
        foreach($brand_list as $k=>$v){
            $brand_list[$k]['brand_logo'] = D('File')->getOneFilePath($v['brand_logo']);
            $brand_list[$k]['true_brand_logo'] = D('File')->getOneFilePath($v['true_brand_logo']);
        }
        $list['brand_list'] = $brand_list ? $brand_list : array();
        $first2 = array('brand_id'=>0,'brand_name'=>'不限',
                        'brand_logo'=>C('API_URL').'/Uploads/CarBrand/any.png',
                        'true_brand_logo'=>C('API_URL').'/Uploads/CarBrand/true_any.png'
            );
        array_unshift($list['brand_list'],$first2);
        apiResponse('1','获取成功',$list);

    }
    public function carList($request = array()){
        if(!empty($request['style_id'])){
            //说明是限车型
            $temp_data = explode(',',$request['style_id']);
            if($temp_data[0]!=0){
                $where['car_style_id'] = array('IN',$request['style_id']);
            }
        }
        if(!empty($request['brand_id'])){
           //限品牌
            $temp_data = explode(',',$request['brand_id']);
            if($temp_data[0]!=0){
                $where['brand_id'] = array('IN',$request['brand_id']);
            }
        }
        $mod = M('CarBuy');
        $where['status'] = 1;
        $where['all_price'] = array('between',array($request['min_price']*10000,$request['max_price']*10000));
        $list = $mod->field('id AS car_id,car_name,car_img,lng,lat,brand_id,pre_money,true_pre_money,all_price,integral')
            ->where($where)
            ->order('create_time DESC')
            ->page($request['p'],10)
            ->select();
        if(!$list){
            $msg = $request['p']==1?'暂无数据':'无更多数据';
            apiResponse('0',$msg);
        }
        $count = $mod->where($where)->count();
        foreach($list as $k=>$v){
            $list[$k]['car_img'] = D('File')->getOneFilePath($v['car_img']);
            //根据经纬度算出当前距离
            $distance = getDistance($request['lat'],$request['lng'],$v['lat'],$v['lng']);
            $list[$k]['distance'] = $distance ? $distance : '0';
            //票券
            $list[$k]['ticket_discount'] = '0';
            //国家
            $list[$k]['brand_logo'] = D('File')->getOneFilePath(getName('CarBrand','true_brand_logo',$v['brand_id']));
        }
        apiResponse('1','获取成功',$list,$count);
    }

    /**
     * 汽车详情
     */
    public function carInfo($request = array(),$user_id = 0){
        $is_col = array();
        $info['msg_tip'] = $info['cart_num'] = '0';
        if(!empty($user_id)){
            //判断该商品是否被收藏
            $is_col = M('UserCollect')->where("user_id={$user_id} AND type=5 AND id_val={$request['car_id']} AND status = 1")->find();
            //获取消息提醒数
            $info['msg_tip'] = D('UserMessage','Logic')->getTips($user_id);
            //判断购物车数量
            $num = M('Cart')->where("user_id = {$user_id}")->count();
            $info['cart_num'] = $num ? $num :'0';
        }
        $info['is_collect'] = empty($is_col)?'0':'1';

        //1.获取汽车的信息 ticket_discount
        $info['car_info'] = D('CarBuy')
            ->field('id AS car_id,car_name,car_img,discount,yellow_discount,blue_discount,share_content,lng,lat,pre_money,true_pre_money,all_price,integral,pictures,car_desc,content')
            ->where("id={$request['car_id']}")
            ->find();
        $info['car_info']['content'] = setPictureStyle($info['car_info']['content']);
        if(!$info['car_info']){
            apiResponse('0','获取失败');
        }
        //代金券处理
        $info['car_info']['ticket_discount'] = $info['car_info']['discount']+$info['car_info']['yellow_discount']+$info['car_info']['blue_discount'];
        unset($info['car_info']['discount']);
        unset($info['car_info']['yellow_discount']);
        unset($info['car_info']['blue_discount']);

        $info['car_info']['banner'] = D('File')->getArrayFilePath(explode(',',$info['car_info']['pictures']));

        //获取汽车购评价
        unset($where);
        $count = M('CarComment')->where(array('car_id'=>$info['car_info']['car_id'],'status'=>1))->count();
        $info['comment_num'] = $count?''.$count:'0';
        $comment_list = M('CarComment')->where(array('car_id'=>$info['car_info']['car_id'],'status'=>1))
            ->field('user_id,exterior,space,controllability,consumption,label_str,pictures,content,create_time')
            ->limit(1)
            ->select();
        foreach($comment_list as $k => $v){
            $comment_list[$k]['comment_star'] = ''.number_format(($v['exterior']+$v['space']+$v['controllability']+$v['consumption'])/4,1);
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
        $info['comment_new'] = $comment_list;


        //获取汽车购的规格
        $attr = M('car_property')->where(array('id'=>$info['car_info']['car_id']))->field('id,attr_name,attr_val')->select();
        $info['attr'] = $attr?$attr:array();
        $info['share_url'] = "http://wjyp.txunda.com";
        $info['share_img'] = D('File')->getOneFilePath($info['car_info']['car_img']);
        $info['share_content'] = $info['car_info']['share_content'];
        $info['car_info']['car_img'] = $info['share_img'];

        //获取环信客服的账号信息
        $config = D('Config')->parseList();
        $customer_service_id = $config['customer_service_id'];
        $customer_service_info = M('User')->where(array('id'=>$customer_service_id))->find();
        $info['easemob_account'] = $customer_service_info['easemob_account']?$customer_service_info['easemob_account']:'';
        $info['server_name'] = $customer_service_info['nickname']?$customer_service_info['nickname']:'无界优品客服';
        if($customer_service_info){
            $path = M('File')->where(array('id'=>$customer_service_info['head_pic']))->getField('path');
            $info['server_head_pic'] =  $path?C('API_URL').$path:C('API_URL').'/Uploads/Member/default.png';
        } else{
            $info['server_head_pic'] = C('API_URL').'/Uploads/Member/default.png';
        }

        //添加足迹信息
        if(!empty($user_id)){
            $data['add_time'] = time();
            $data['type'] = 5;
            $data['user_id'] = $user_id;
            $data['id_val'] = $request['car_id'];
            M('Myfooter')->add($data);
        }
        apiResponse('1','获取成功',$info);
    }

    /**
     * 获取汽车评论
     */
    public function comment($request = array(),$limit = 1){

        $where['status'] = 1;
        $where['type'] = 1;
        $where['id_val'] = $request['car_id'];
        $page = $request['p'] ? "{$request['p']},10" : $limit;
        $count = M('OtherComment')->where($where)->count();
        $comment = M('OtherComment')
            ->field('id AS comment_id,user_id,content,nickname,pictures,all_star,create_time')
            ->where($where)
            ->order('create_time DESC')
            ->page($page)
            ->select();
        if(!$comment){
            return false;
        }
        foreach($comment as $k=>$v){
            $comment[$k]['head_pic'] = D('File')->getOneFilePath(getName('User','head_pic',$v['user_id']));
            $comment[$k]['pictures'] = D('File')->getArrayFilePath(explode(',',$v['pictures']));
            $comment[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
        }
        $info['comment'] = $comment;
        $arr = array();
        $arr[0]['key'] = '精华';
//        $arr[]
        return array('info'=>$info,'count'=>$count);

    }

}