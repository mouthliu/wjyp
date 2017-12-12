<?php

namespace Merchant\Controller;

/**
 * Class CategoriesController Categories
 * User mr.zhou
 * @package Merchant\Controller
 * 商品分类
 */
class CategoriesController extends BaseController {

	/**
	 * 添加
	 */
	function add() {
		$this->checkRule(self::$rule);
		if(!IS_POST) {
			$this->assign('cate',M('Categories')->where(array('depth'=>1))->field('id,name')->select());
			$this->getAddRelation();
			$this->display('update');
		} else {
			$Object = D(CONTROLLER_NAME,'Logic');
			$post = I('post.');
			$post['depth'] = 1;
			$result = $Object->update($post);
			if($result) {
				$this->success($Object->getLogicSuccess(), Cookie('__forward__'));
			} else {
				$this->error($Object->getLogicError());
			}
		}
	}

	/**
	 * 修改
	 */
	function update() {
		$this->checkRule(self::$rule);
		if(!IS_POST) {
			if ($_GET['id']) {
				$Object = D(CONTROLLER_NAME,'Logic');
				$row = $Object->findRow(I('get.'));
				if ($row) {
					$this->getUpdateRelation();
					$this->assign('row', $row);
					$this->assign('cate',M('Categories')->where(array('depth'=>1))->field('id,name')->select());
				} else {
					$this->error($Object->getLogicError());
				}
			}
			$this->display('update');
		} else {
			$Object = D(CONTROLLER_NAME,'Logic');
			$post = I('post.');
			if($post['p_id'] == 0){
				$post['depth'] = 1;
			}else{
				$post['depth'] = 2;
			}
			$result = $Object->update($post);
			if($result) {
				$this->success($Object->getLogicSuccess(), Cookie('__forward__'));
			} else {
				$this->error($Object->getLogicError());
			}
		}
	}


}
