<?php
/**
 * 菜单配置列表
 * group  父菜单 title标题名称  icon改组图标 class是否选中 默认为空 url链接地址  is_developer 0都可见 1开发者模式可见
 * child 子菜单 同上
 */
    return array(
        'MENUS' => array(
            array(
                'group' => array('title' => '仪表盘', 'icon' => 'fa fa-dashboard', 'class' => '', 'url' => 'Index/index', 'is_developer' => 0),
                '_child' => array()
            ),
            array(
                'group' => array('title' => '平台交易统计', 'icon' => 'fa fa-user', 'class' => '', 'url' => 'MerchantDetail/index', 'is_developer' => 0),
                '_child' => array()
            ),
            array(
                'group' => array('title' => '会员管理', 'icon' => 'fa fa-bookmark-o', 'class' => '', 'url' => '', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'会员列表','url'=>'User/index','class'=>'','is_developer'=>0),
                    array('title'=>'会员等级','url'=>'UserLevel/index','class'=>'','is_developer'=>0),
                    array('title'=>'会员类型','url'=>'UserRank/index','class'=>'','is_developer'=>0),
                    array('title'=>'会员充值管理','url'=>'UserMoney/index','class'=>'','is_developer'=>0),
                    array('title'=>'会员提现管理','url'=>'UserCash/index','class'=>'','is_developer'=>0),
                    array('title'=>'会员转账管理','url'=>'ChangeMoney/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title' => '商品管理', 'icon' => 'fa fa-shopping-basket', 'class' => '', 'url' => '', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'商品列表','url'=>'Goods/index?status=1','class'=>'','is_developer'=>0),
                    array('title'=>'商品分类','url'=>'GoodsCategory/index','class'=>'','is_developer'=>0),
                    array('title'=>'商品类型','url'=>'GoodsType/index','class'=>'','is_developer'=>0),
                    array('title'=>'商品品牌','url'=>'GoodsBrand/index','class'=>'','is_developer'=>0),
                    array('title'=>'进口国家管理','url'=>'Country/index','class'=>'','is_developer'=>0),
                    array('title'=>'商品评论管理','url'=>'Comment/index','class'=>'','is_developer'=>0),
                    array('title'=>'商品服务管理','url'=>'GoodsServer/index','class'=>'','is_developer'=>0),
                    array('title'=>'商品价格说明','url'=>'GoodsPriceDesc/index','class'=>'','is_developer'=>0),
                )
            ),
//            array(
//                'group' => array('title' => '招商管理', 'icon' => 'fa fa-bookmark-o', 'class' => '', 'url' => '', 'is_developer' => 0),
//                '_child' => array(
////                    array('title'=>'商品列表','url'=>'AttractGoods/index','class'=>'','is_developer'=>0),
////                    array('title'=>'添加商品','url'=>'AttractGoods/add','class'=>'','is_developer'=>0),
//                    array('title'=>'推荐商家管理','url'=>'MerchantRun/index','class'=>'','is_developer'=>0),
//                )
//            ),
            array(
                'group' => array('title' => '供应商管理', 'icon' => 'fa fa-bookmark-o', 'class' => '', 'url' => '', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'商家开户','url'=>'Merchant/add','class'=>'','is_developer'=>0),
                    array('title'=>'商家列表','url'=>'MerchantList/index','class'=>'','is_developer'=>0),
                    array('title'=>'会员推荐商家','url'=>'MerchantRefer/index','class'=>'','is_developer'=>0),
                    array('title'=>'配送方式管理','url'=>'Shipping/index','class'=>'','is_developer'=>0),
                    array('title'=>'举报类型','url'=>'ReportType/index','class'=>'','is_developer'=>0),
                    array('title'=>'举报商家审核','url'=>'MerchantReport/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'红包管理','icon'=>'fa fa-file','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'红包列表','url'=>'Bonus/face','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title' => '优惠管理', 'icon' => 'fa fa-ticket', 'class' => '', 'url' => '', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'积分抽奖','url'=>'OneBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'拼单购','url'=>'GroupBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'比价购','url'=>'Auction/index','class'=>'','is_developer'=>0),
                    array('title'=>'限量购','url'=>'LimitBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'无界预购','url'=>'PreBuy/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title' => '专区管理', 'icon' => 'fa fa-gift', 'class' => '', 'url' => '', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'主题街','url'=>'Theme/index','class'=>'','is_developer'=>0),
                    array('title'=>'票券区','url'=>'TicketBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'无界商店','url'=>'IntegralBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'品牌团','url'=>'BrandBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'中国质造','url'=>'ChinaBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'科技前沿','url'=>'ScienceBuy/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title' => '汽车购管理', 'icon' => 'fa fa-bookmark-o', 'class' => '', 'url' => '', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'车型配置','url'=>'CarStyle/index','class'=>'','is_developer'=>0),
                    array('title'=>'品牌配置','url'=>'CarBrand/index','class'=>'','is_developer'=>0),
                    array('title'=>'汽车列表','url'=>'CarBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'汽车信息录入','url'=>'AddCarBuy/add','class'=>'','is_developer'=>0),
                    array('title'=>'汽车评论管理','url'=>'OtherComment/index?type=1','class'=>'','is_developer'=>0),
                    array('title'=>'汽车评论标签管理','url'=>'CarLabel/index','class'=>'','is_developer'=>0),
                    array('title'=>'汽车购订单管理','url'=>'CarOrder/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title' => '房产购管理', 'icon' => 'fa fa-bookmark-o', 'class' => '', 'url' => '', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'楼盘列表列表','url'=>'HouseBuyList/index','class'=>'','is_developer'=>0),
                    array('title'=>'楼盘信息录入','url'=>'AddHouseBuy/add','class'=>'','is_developer'=>0),
                    array('title'=>'房产评论管理','url'=>'HouseBuy/comment?type=2','class'=>'','is_developer'=>0),
                    array('title'=>'房产评论标签管理','url'=>'HouseLabel/index','class'=>'','is_developer'=>0),
                    array('title'=>'房产购订单管理','url'=>'HouseOrder/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title' => '企业孵化', 'icon' => 'fa fa-at', 'class' => '', 'url' => 'CompanyDevelop/index', 'is_developer' => 0),
                '_child' => array()
            ),
            array(
                'group' => array('title' => '广告管理', 'icon' => 'fa fa-bookmark-o', 'class' => '', 'url' => '', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'广告轮播列表','url'=>'AdsList/index','class'=>'','is_developer'=>0),
                    array('title'=>'广告轮播位置','url'=>'Ads/position','class'=>'','is_developer'=>0),
                )
            ),
//            array(
//                'group' => array('title' => '无界头条', 'icon' => 'fa fa-at', 'class' => '', 'url' => 'Headlines/index', 'is_developer' => 0),
//                '_child' => array()
//            ),
            array(
                'group' => array('title' => '内容管理', 'icon' => 'fa fa-bookmark-o', 'class' => '', 'url' => '', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'书院分类','url'=>'AcademyType/index','class'=>'','is_developer'=>0),
                    array('title'=>'文章列表','url'=>'Academy/index','class'=>'','is_developer'=>0),
                    array('title' => '无界头条', 'class' => '', 'url' => 'Headlines/index', 'is_developer' => 0),
                    array('title' => '帮助中心', 'class' => '', 'url' => 'HelpCenter/index', 'is_developer' => 0),
                )
            ),
//            array(
//                'group' => array('title' => '帮助中心', 'icon' => 'fa fa-retweet', 'class' => '', 'url' => 'HelpCenter/index', 'is_developer' => 0),
//                '_child' => array()
//            ),
            array(
                'group' => array('title'=>'文章管理','icon'=>'fa fa-file','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'文章列表','url'=>'Article/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title' => '意见反馈', 'icon' => 'fa fa-envelope-square', 'class' => '', 'url' => '', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'意见反馈类型','url'=>'FeedbackType/index','class'=>'','is_developer'=>0),
                    array('title'=>'意见反馈记录','url'=>'Feedback/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'信息管理','icon'=>'fa fa-envelope','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'短信模板设置','url'=>'SendTemplate/index','class'=>'','is_developer'=>0),
                    array('title'=>'短信发送记录','url'=>'SendLog/index','class'=>'','is_developer'=>0),
                    array('title'=>'公告管理','url'=>'Announce/index','class'=>'','is_developer'=>0),
                )
            ),

            array(
                'group' => array('title'=>'管理员操作','icon'=>'fa fa-user','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'管理员信息','url'=>'Administrator/index','class'=>'','is_developer'=>0),
                    array('title'=>'部门管理','url'=>'Department/index','class'=>'','is_developer'=>0),
                    array('title'=>'分组权限','url'=>'AuthGroup/index','class'=>'','is_developer'=>0),
                    array('title'=>'行为信息','url'=>'Action/index','class'=>'','is_developer'=>0),
                    array('title'=>'行为日志','url'=>'ActionLog/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'系统设置','icon'=>'fa fa-wrench','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'平台设置','url'=>'ConfigSet/index?config_group=1','class'=>'','is_developer'=>0),
                    array('title'=>'配置管理','url'=>'Config/index','class'=>'','is_developer'=>0),
                    array('title'=>'地区管理','url'=>'Region/index','class'=>'','is_developer'=>0),

                )
            ),
        ),
    );