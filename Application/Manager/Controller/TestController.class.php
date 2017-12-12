<?php

namespace Manager\Controller;

/**
 * Class AdministratorController
 * @package Manager\Controller
 * 管理员控制器
 */
class TestController extends BaseController {

   function index(){
       echo getAttrGroupId1(4,72);
   }
}
