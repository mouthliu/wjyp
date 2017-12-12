<?php
namespace Manager\Controller;

/**
 * Class ArticleController
 * @package Manager\Controller
 * 协议相关 控制器
 */
class ContractController extends BaseController {

    public function getIndexRelation(){
        $where['p_id'] = array('neq',0);
        $p_id = M('contract')->where($where)->getField('p_id',true);

        $this->assign('p_id',$p_id);
        $this->assign('merchant_id',$_GET['merchant_id']);
    }

    public function getAddRelation(){
        $srarus = M('contract')->where(array('id'=>$_GET['p_id']))->getField('status');
        if($srarus !=1){
            $this->error('该协议未审核或审核未通过!');return false;
        }
        $agreement_name = M('contract')->where(array('id'=>$_GET['p_id']))->getField('agreement_name');
        $this->assign('agreement_name',$agreement_name);
        $this->assign('merchant_id',$_GET['merchant_id']);
        $this->assign('p_id',$_GET['p_id']);
    }
    public function getUpdateRelation(){
        $agreement_name = M('contract')->where(array('id'=>$_GET['p_id']))->getField('agreement_name');
        $this->assign('agreement_name',$agreement_name);
        $this->assign('merchant_id',$_GET['merchant_id']);
        $this->assign('p_id',$_GET['p_id']);
    }
    /**
     * 添加补充协议
     * User: Vernon
     * @param:
     * Date: 2017-11-16
     */

    public function adds(){
        $Object = D(CONTROLLER_NAME, 'Logic');
        $row = $Object->findRow(I('get.'));
        $this->assign('row',$row);
        $adjust = M('adjustment')->select();
        $this->assign('adjust',$adjust);
        $goods = M('goods')->where(array('merchant_id'=>$row['merchant_id']))->field('id as goods_id,goods_name,goods_code')->select();
        $this->assign('goods',$goods);
        $this->display('add');
    }

    /**
     * 重写添加
     */
    public function doAdd(){
        if(IS_POST){
            $add['agreement_number'] = $_POST['agreement_number'];
            $add['agreement_name'] = $_POST['agreement_name'];
            $add['create_time'] = time();
            $add['contract_id'] = $_POST['contract_id'];
            switch ($_POST['types']){
                //费用单
                case 1:
                    $add['serve_sum'] = $_POST['serve_sum'];
                    $add['festival_sum'] = $_POST['festival_sum'];
                    $add['deposit_sum'] = $_POST['deposit_sum'];
                    $add['name'] = $_POST['name1'];
                    $add['create_time'] = $_POST['create_time1'];
                    $add['contract'] = $_POST['contracts1'];
                    $add['create_time'] = strtotime($_POST['create_time1']);
                    $data = D('Fee')->create($add);
                    if($data){
                        D('Fee')->data($data)->add();
                        $this->success('添加成功',U('Merchant/index'));
                    }else{
                        $this->error(D('Fee')->getError());
                    }
            break;
                //价格单
                case 2:
                    $add['goods_logo'] =$_POST['contract2'];
                    $add['name'] = $_POST['name2'];
                    $add['goods_opinion'] = $_POST['goods_opinion'];
                    $add['create_time'] = strtotime($_POST['create_time2']);
                    $add['contract'] = $_POST['contract2'];
                    $data = D('Price')->create($add);
                    if($data){
                        D('Price')->data($data)->add();
                        $this->success('添加成功',U('Merchant/index'));
                    }else{
                        $this->error(D('Price')->getError());
                    }
            break;
                //调价单
                case 3:
                    $add['type'] = $_POST['type'];
                    $add['contract'] = $_POST['contract3'];
                    if($_POST['type'] == 2){
                        if(!empty($_POST['start_time'])){
                            $add['start_time'] =strtotime($_POST['start_time']);
                        }else{
                            $this->error('请选择开始日期');
                        }
                        if(!empty($_POST['end_time'])){
                            $add['end_time'] =strtotime($_POST['end_time']);
                        }else{
                            $this->error('请选择结束日期');
                        }
                    }
                    $add['create_time'] = strtotime($_POST['create_time3']);
                    $add['adjustment_opinion'] = $_POST['adjustment_opinion'];
                    $add['name'] = $_POST['name3'];
                    $data = D('Adjustment')->create($add);
                    if($data){
                        D('Adjustment')->data($data)->add();
                        $this->success('添加成功',U('Merchant/index'));
                    }else{
                        $this->error(D('AdjustmentPrice')->getError());
                    }
            break;
                default:
            }
        }
    }
    /**
     * ajax请求获取商品信息
     */
    public function ajaxGoods(){
        if(IS_POST){
            $data = M('goods')->where(array('id'=>$_POST['goods_id']))->field('id as goods_id,market_price,shop_price,settlement_price,goods_code')->find();
            $this->ajaxReturn($data,'JSON');
        }
    }
}
