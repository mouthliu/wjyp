<?php
namespace Manager\Controller;

/**
 * Class IndexController
 * @package Manager\Controller
 * 776,778,777,771,775,773,770,774,772,768,764,769,767,758,762,760,753,757,755,751,756,754,748,752,750
 * 首页控制器
 */
class IndexController extends BaseController {


    public function index() {
//        p($_SESSION);
        if(empty($_POST['start_time']) && empty($_POST['end_time'])){
            //从当前日期一周内
            $start_time = strtotime(date('Y-m-d'))-7*86400;
            $end_time = strtotime(date('Y-m-d'));
        }
        $this -> registerList($start_time,$end_time);
        $this->assign('user_num',M('User')->count());
        $this->display('index');
    }
    // 注册
    public function registerList($start_time,$end_time)
    {
        for($i = $start_time;$i<=$end_time;$i = $i+86400){
            $xAxis[] = date('Y-m-d',$i);//x坐标
            $where['create_time'] = array(array('egt',$i),array('elt',$i+86400-1),'AND');
            //统计
            $yAxis[0]['data'][] = (int)(M('User')->where($where)->count());
            //y坐标 一个json数组标表示一个折线图（数据必须是数字型）
            unset($where);
        }
        $yAxis[0]['name'] = '日注册用户';
        $this->assign('xAxis',json_encode($xAxis));
        $this->assign('yAxis',json_encode($yAxis));
    }
}
