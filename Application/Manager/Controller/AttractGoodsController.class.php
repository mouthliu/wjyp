<?php
namespace Manager\Controller;

/**
 * Class ArticleController
 * @package Manager\Controller
 * 文章咨询 控制器
 */
class AttractGoodsController extends BaseController {
    /**
     * 添加数据
     */
    public function getAddRelation(){

        $merchant = M('merchant')->where(array('a_id'=>getMerchantId()))->field('id as merchant_id,merchant_name')->select();
        
        $this->assign('a_id',getMerchantId());
//        p($merchant);
        $this->assign('merchant',$merchant);
    }
    /**
     * 编辑数据
     */
    public function getUpdateRelation(){
        $merchant = M('merchant')->field('id as merchant_id,merchant_name')->select();
        $this->assign('merchant',$merchant);
    }

    /**
     * 领导审核
     */
    public function leadOpinion(){
        $row = M('attract_goods')->where(array('id'=>$_GET['attract_id']))->find();
        if(IS_POST){
            //通过
            if($_POST['save'] == 1){
                $save['lead_status'] = 2;
                $save['update_time'] = time();
                $save['lead_opinion'] = $_POST['lead_opinion'];
                $data = M('attract_goods')->where(array('id'=>$_POST['attract_id']))->save($save);
                if($data){
                    $this->success('审核成功',U("AttractGoods/index"));
                }else{
                    $this->error('审核失败,请重试',U("AttractGoods/index"));
                }
                die;
            }else{
//                拒绝
                $save['lead_status'] = 3;
                $save['update_time'] = time();
                $save['lead_opinion'] = $_POST['lead_opinion'];
                $data = M('attract_goods')->where(array('id'=>$_POST['attract_id']))->save($save);
                if($data){
                    $this->success('拒绝审核成功',U("AttractGoods/index"));
                }else{
                    $this->error('拒绝审核失败,请重试',U("AttractGoods/index"));
                }
                die;
            }

        }
        $this->assign('row',$row);
        $this->display('leadOpinion');
    }
}
