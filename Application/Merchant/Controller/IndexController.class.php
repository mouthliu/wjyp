<?php
namespace Merchant\Controller;

/**
 * Class IndexController
 * @package Merchant\Controller
 * 776,778,777,771,775,773,770,774,772,768,764,769,767,758,762,760,753,757,755,751,756,754,748,752,750
 * 首页控制器
 */
class IndexController extends BaseController {


    public function index() {
        if(empty($_POST['start_time']) &&empty($_POST['end_time'])){
            $start_time = strtotime(date('Y-m-d'))-7*86400;
            $end_time = strtotime(date('Y-m-d'));
        }
        $mid = getMerchantId();
        $this -> visitList($start_time,$end_time);
        $this->assign('goods_num',M('Goods')->where("merchant_id={$mid} AND status != 9")->count());
        $this->assign('order_num',M('Order')->where("merchant_id={$mid} AND order_status != 9")->count());
        $this->assign('visit_num',M('Myfooter')->where("id_val = {$mid} AND type=2")->count());
        $this->display('index');
    }
    // 访问量
    public function visitList($start_time,$end_time)
    {
        for($i = $start_time;$i<=$end_time;$i = $i+86400){
            $xAxis[] = date('Y-m-d',$i);
            $where['add_time'] = array(array('egt',$i),array('elt',$i+86400-1),'AND');
            $where['id_val'] = getMerchantId();
            $where['type'] = 2;
            $yAxis[0]['data'][] = (int)(M('Myfooter')->where($where)->count());
            unset($where);
        }
        $yAxis[0]['name'] = '日访问人数';
        $this->assign('xAxis',json_encode($xAxis));
        $this->assign('yAxis',json_encode($yAxis));
    }
}
