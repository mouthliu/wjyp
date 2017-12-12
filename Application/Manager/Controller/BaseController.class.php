<?php
namespace Manager\Controller;

use Think\Controller;
use Manager\Model\AuthRuleModel;

/**
 * Class BaseController
 * @package Manager\Controller
 * 控制器父类
 * 其中包含 验证登录
 *          菜单处理
 *          和一些基本的增、删、改、查的方法
 *          好多方法可以集中处理 这里分开 是为了方便权限验证
 */
abstract class BaseController extends Controller
{

    /**
     * @var string
     * 权限验证规则
     */
    protected static $rule = '';

    /**
     * 每个控制器方法执行前 先执行该方法
     */
    protected function _initialize()
    {
        // 获取当前登录的管理员ID
        define('AID', is_login());
        // 还没登录 跳转到登录页面
        if (!AID) {
            $this->redirect('Login/login');
        }
        //读取站点配置  先读取缓存
        $config = S('Config_Cache');

        if (!$config) {
            $config = D('Config')->parseList();
            S('Config_Cache', $config);
        }
        //添加配置到 C函数
        C($config);

        // 是否是超级管理员
        define('IS_ROOT', is_administrator());
        //检查IP地址是否禁止访问
        if (!IS_ROOT && C('ADMIN_ALLOW_IP')) {
            // 检查IP地址访问
            if (!in_array(get_client_ip(), explode(',', C('ADMIN_ALLOW_IP')))) {
                $this->error('403:IP禁止访问');
            }
        }

        //验证规则
        self::$rule = MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;

        //左侧菜单列表
        $this->assign('menus', $this->getMenus());
        $this->assign('bottom', htmlspecialchars_decode(C('CONFIG_COPYRIGHT')));
        $this->assign('breadcrumb',$this->getDreadcrumb());
    }

    /**
     * 获取main菜单
     * Created by ${PRODUCT_NAME}.
     * User: Vernon
     * Date: 2017-
     */
    public function getDreadcrumb(){
        $breadcrumb = array();
        $menus = C('MENUS');
        //处理每个菜单的信息
        foreach ($menus as $key_a => & $menu) {
            //非开发者模式下  删除仅开发者模式下可见的菜单
            if (!C('IS_DEVELOPER') && $menu['group']['is_developer'] == 1) {
                unset($menus[$key_a]);
                continue;
            }
            //该判断主要是 针对没有子菜单的父菜单
            if (substr($menu['group']['url'], 0, strpos($menu['group']['url'], '/')) == CONTROLLER_NAME) {
                $arraymenu = explode('/', $menu['group']['url']);
                if (in_array(CONTROLLER_NAME, $arraymenu)) {
                    $breadcrumb['breadcrumb_url'] = $menu['group']['url'];
                    $breadcrumb['breadcrumb_nav'] = $menu['group']['title'];
                    break;
                }
            }

            //存在子菜单
            if (!empty($menu['_child'])){
                foreach ($menu['_child'] as $key_b => & $child) {
                    //非开发者模式下  删除仅开发者模式下可见的菜单
                    if (!C('IS_DEVELOPER') && $child['is_developer'] == 1) {
                        unset($menu['_child'][$key_b]);
                        continue;
                    }
                    //判断当前控制器在那个菜单路径下 该菜单高亮
                    $explode = explode('/', $child['url']);
                    if (in_array(CONTROLLER_NAME, $explode)){
                        $breadcrumb['breadcrumb_url'] = $menu['group']['url'];
                        $breadcrumb['breadcrumb_nav'] = $menu['group']['title'];
                        $breadcrumb['breadcrumb_title'] =$child['title'];
                        break;
                    }
                }
            }
        }
        return $breadcrumb;
    }

    /**
     * 频道列表页
     */
    function index()
    {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->getList(I('request.'));
        if ($result) {
            $this->assign('page', $result['page']);
            $this->assign('list', $result['list']);
        } else {
            $this->error($Object->getLogicError());
        }
        // 记录当前列表页的cookie
        Cookie('__forward__', $_SERVER['REQUEST_URI']);
        $this->getIndexRelation();
        $this->display('index');
    }

    /**
     * 添加
     */
    function add()
    {
        $this->checkRule(self::$rule);
        if (!IS_POST) {
            $this->getAddRelation();
            $this->display('update');
        } else {
            $Object = D(CONTROLLER_NAME, 'Logic');

            $result = $Object->update(I('post.'));

            if ($result) {
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }

    /**
     * 修改
     */
    function update()
    {
        $this->checkRule(self::$rule);

        if (!IS_POST) {
            if ($_GET['id']) {
                $Object = D(CONTROLLER_NAME, 'Logic');
                $row = $Object->findRow(I('get.'));
                if ($row) {
                    $this->getUpdateRelation();
                    $this->assign('row', $row);
                } else {
                    $this->error($Object->getLogicError());
                }
            }
            $this->display('update');
        } else {
//            p($_POST);
            $Object = D(CONTROLLER_NAME, 'Logic');
            $result = $Object->update(I('post.'));
//            p($result);die;
            $request = I('post.');
            if ($result) {
                if($request['status']){
                    $this->afterProcess(array('model'=>$request['model'],'id'=>$request['id'],'field'=>'status','value'=>$request['status']));
                }
                $this->success($Object->getLogicSuccess(), Cookie('__forward__'));
            } else {
                $this->error($Object->getLogicError());
            }
        }
    }

    /**
     * 添加时 获取相关系数据
     * 例：添加文章时 要获取文章分类列表，添加管理员获取组列表等
     */
    protected function getAddRelation()
    {
    }

    /**
     * 修改时 获取相关系数据
     * 例：添加文章时 要获取文章分类列表，添加管理员获取组列表等
     */
    protected function getUpdateRelation()
    {
    }

    /**
     * 频道列表页相关系数据
     */
    protected function getIndexRelation()
    {
    }

    /**
     * 禁用操作
     */
    function forbid()
    {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->setStatus(I('request.'));
        if ($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }

    /**
     * 启用操作
     */
    function resume()
    {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->setStatus(I('request.'));
        if ($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }

    /**
     * 移除 彻底删除
     */
    function remove()
    {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->remove(I('request.'));
        if ($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }

    /**
     * 删除 假删除 状态置9
     */
    function delete()
    {
        $this->checkRule(self::$rule);
        $Object = D(CONTROLLER_NAME, 'Logic');
        $result = $Object->setStatus(I('request.'));
        if ($result) {
            $this->success($Object->getLogicSuccess());
        } else {
            $this->error($Object->getLogicError());
        }
    }


    /**
     * @param string $rule 检测的规则
     * @param int $type 检验类型
     * @param string $mode 验证模式
     * @return bool
     * 权限检测
     */
    final protected function checkRule($rule, $type = AuthRuleModel::RULE_URL, $mode = 'url')
    {
        if (!$this->accessControl($rule)) {
            //超级管理员允许访问任何页面
            if (!IS_ROOT) {
                static $Auth = null;
                if (!$Auth) {
                    //初始化权限检测类
                    $Auth = new \Think\Auth();
                }
                //检测权限
                if (!$Auth->check($rule, AID, $type, $mode)) {
                    $this->error('权限不足！');
                    exit;
                }
            }
        }
    }

    /**
     * @param string $rule 检测的规则
     * action访问控制,在 **登陆成功** 后执行的第一项权限检测任务
     * @return boolean|null  返回值必须使用 `===` 进行判断
     *   返回 **false**, 不允许任何人访问(超管除外)
     *   返回 **true**, 允许任何管理员访问
     */
    final protected function accessControl($rule = '')
    {
        //管理员允许访问任何页面
        if (IS_ROOT) {
            return true;
        }
        //允许所有人访问的方法列表
        $allow = C('ALLOW_VISIT');
        //不允许访问的方法列表（除超管外)
        $deny = C('DENY_VISIT');
        //判断当前访问 是否在禁止访问的列表中
        if (!empty($deny) && in_array_case($rule, $deny)) {
            //非超管禁止访问deny中的方法
            $this->error('权限不足！');
            exit;
        }
        //判断当前访问 是否在允许访问的列表中
        if (!empty($allow) && in_array_case($rule, $allow)) {
            return true;
        }
        //需要继续检测
        return false;
    }

    /**
     * @return mixed
     * 处理菜单列表 加入高亮状态
     */
    final protected function getMenus()
    {
        $menus = C('MENUS');
//         dump($menus);
        //处理每个菜单的信息
        foreach ($menus as $key_a => & $menu) {
            //非开发者模式下  删除仅开发者模式下可见的菜单
            if (!C('IS_DEVELOPER') && $menu['group']['is_developer'] == 1) {
                unset($menus[$key_a]);
                continue;
            }
            //该判断主要是 针对没有子菜单的父菜单
            if (substr($menu['group']['url'], 0, strpos($menu['group']['url'], '/')) == CONTROLLER_NAME) {
                $arraymenu = explode('/', $menu['group']['url']);
                if (in_array(CONTROLLER_NAME, $arraymenu)) {
                    $menu['group']['class'] = 'active';
                }
            }
            //存在子菜单
            if (!empty($menu['_child'])) {
                foreach ($menu['_child'] as $key_b => & $child) {
                    //非开发者模式下  删除仅开发者模式下可见的菜单
                    if (!C('IS_DEVELOPER') && $child['is_developer'] == 1) {
                        unset($menu['_child'][$key_b]);
                        continue;
                    }
                    //判断当前控制器在那个菜单路径下 该菜单高亮
                    $explode = explode('/', $child['url']);
                    if (in_array(CONTROLLER_NAME, $explode)) {
//                        if(in_array(ACTION_NAME,$explode)){
                        //子菜单高亮
                        $child['class'] = 'active';
                        //其父菜单高亮
//                        }
                        $menu['group']['class'] = 'active';
                    }
                }
            }
        }

        return $menus;
    }

    //面包屑导航
    //abstract protected function breadcrumbNav();

    /**
     * @param string $model 模型
     * @param int $id 数据ID
     * @param string $field 修改的字段名称
     * @param string $value 修改为的值
     * 列表中 针对某个字段的修改
     */
    function singleEdit($model = '', $id = 0, $field = '', $value = '')
    {
        $r = D($model)->where(array('id' => $id))->data(array($field => $value))->save();
        S($model . '_Cache', null);
        $r ? $this->afterProcess(array('model'=>$model,'id'=>$id,'field'=>$field,'value'=>$value)) : '';
        $r ? $this->success('修改成功') : $this->error('修改失败');
    }

    /**
     * @param string $model 模型名称
     * @param int $id 数据ID
     * @param string $field 修改的字段名称
     * @param string $flag 标记 1显示 0隐藏
     * 开关操作
     */
    function onOff($model = '', $id = 0, $field = '', $flag = '')
    {
        if ($flag == 1) {
            $data[$field] = 0;
        } else {
            $data[$field] = 1;
        }
        $r = D($model)->where(array('id' => $id))->data($data)->save();
        S($model . '_Cache', null);
        $r ? $this->success('修改成功') : $this->error('修改失败');
    }

    /**
     * [TwoCode 验证码]
     * @param  string $value [验证码数字]
     */
    public function TwoCode($value = '00000000')
    {
        vendor("phpqrcode.phpqrcode");
        $Submit = new \Vendor\phpqrcode\QRcode();
        $data1 = $value;
        session('verify', $value);
        //dump($data);
        // 纠错级别：L、M、Q、H
        $level = 'L';
        // 点的大小：1到10,用于手机端4就可以了
        $size = 5;
        // 下面注释了把二维码图片保存到本地的代码,如果要保存图片,用$fileName替换第二个参数false
        // $path           = "Uploads/Qrcode/";
        // 生成的文件名
//        $fileName       = $path.time().rand(10000,99999).$size.'.png';
//        $data['qrcode'] = $fileName;
        $Submit->png($data1, $fileName = false, $level, $size);
    }

    public function read($filename,$encode='utf-8')
    {
        Vendor('PHPExcel.PHPExcel');  //导入thinkphp 中第三方插件库
        vendor('PHPExcel.IOFactory');
        $reader = \PHPExcel_IOFactory::createReader('Excel5');
        $excel = $reader->load($filename);
        $objWorksheet = $excel->getActiveSheet();
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        $highestColumnIndex = \PHPExcel_Cell::columnIndexFromString($highestColumn);
        $excelData = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $excelData[$row][] = (string)$objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
            }
        }
        return $excelData;
    }

    //修改字段执行后操作
    function afterProcess($array = array())
    {
        $modArr = array('GroupBuy', 'LimitBuy', 'PreBuy', 'Theme', 'OneBuy', 'Auction');
        if (!in_array($array['model'], $modArr)) {
            return false;
        }
//        if ($array['field'] == 'status' && $array['value'] != 2) {
//            //删除
//            $goods_id = getName($array['model'], 'goods_id', $array['id']);
//            D('Goods')->where("id={$goods_id}")->save(array(cc_format($array['model']) . '_id' => 0));
//        } elseif ($array['field'] == 'status' && $array['value'] == 2) {
//            //申请成功
//            $goods_id = getName($array['model'], 'goods_id', $array['id']);
//            D('Goods')->where("id={$goods_id}")->save(array(cc_format($array['model']) . '_id' => $array['id']));
//        }
    }

    //修改某个字段的值
    function setField(){
        $id = $_POST['id']?$_POST['id']:die('参数不足');
        $model = $_POST['model']?$_POST['model']:die('参数不足');
        $field = $_POST['field']?$_POST['field']:die('参数不足');
        $value = isset($_POST['value'])?$_POST['value']:die('参数不足');
        $data[$field] = $value;
        $res = D($model)->where("id={$id}")->save($data);
        echo $res ? '1' : '0';
    }
    //设置是否推荐
    function setRecommend(){
        $id = $_POST['id']?$_POST['id']:die('参数不足');
        $model = $_POST['model']?$_POST['model']:die('参数不足');
        $data['is_recommend'] = isset($_POST['is_recommend'])?$_POST['is_recommend']: die('error');
        $res = D($model)->where("id={$id}")->save($data);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
    /**
     * OSS图片上传
     * 将上传过的图片再上传到OSS服务器,获取到路径保存在file表中
     */
    function ossUpload(){

    }


}
