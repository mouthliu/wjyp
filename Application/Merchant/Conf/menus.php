<?php
/**
 * 菜单配置列表
 * group  父菜单 title标题名称  icon改组图标 class是否选中 默认为空 url链接地址  is_developer 0都可见  1开发者模式可见
 * child 子菜单 同上
 */
    return array(
        'MENUS' => array(
            array(
                'group' => array('title' => '仪表盘', 'icon' => 'fa fa-tachometer', 'class' => '', 'url' => 'Index/index', 'is_developer' => 0),
                '_child' => array()
            ),
            array(
                'group' => array('title' => '商品管理', 'icon' => 'fa fa-shopping-basket', 'class' => '', 'url' => '', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'新增商品','url'=>'Goods/add','class'=>'','is_developer'=>0),
                    array('title'=>'商品列表','url'=>'GoodsManage/index?status=0','class'=>'','is_developer'=>0),
                    array('title'=>'评论管理','url'=>'Comment/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'财务管理','icon'=>'fa fa-bar-chart','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'平台交易统计','url'=>'MerchantDetail/index','class'=>'','is_developer'=>0),
                    array('title'=>'账户统计','url'=>'AccountStat/update','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'订单管理','icon'=>'fa fa-calculator','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'普通订单管理','url'=>'Order/index','class'=>'','is_developer'=>0),
                    array('title'=>'拼单购订单管理','url'=>'GroupBuyOrder/index','class'=>'','is_developer'=>0),

                    array('title'=>'房产购订单管理','url'=>'HouseOrder/index','class'=>'','is_developer'=>0),
                    array('title'=>'汽车购订单管理','url'=>'CarOrder/index','class'=>'','is_developer'=>0),
                    array('title'=>'退款/退货管理','url'=>'BackApply/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title' => '活动管理', 'icon' => 'fa fa-group', 'class' => '', 'url' => '', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'限量购','url'=>'LimitBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'积分抽奖','url'=>'OneBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'拼单购','url'=>'GroupBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'比价购','url'=>'Auction/index','class'=>'','is_developer'=>0),
                    array('title'=>'无界预购','url'=>'PreBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'搭配购','url'=>'CheapGroup/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'专区管理','icon'=>'fa fa-gift','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'主题街商品','url'=>'Theme/index','class'=>'','is_developer'=>0),
                    array('title'=>'票券区商品','url'=>'TicketBuy/index','class'=>'','is_developer'=>0),
                    array('title'=>'无界商品商店','url'=>'IntegralBuy/index','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title' => '优惠管理', 'icon' => 'fa fa-ticket', 'class' => '', 'url' => 'Promotion/index', 'is_developer' => 0),
                '_child' => array(
                    array('title'=>'优惠券列表','url'=>'Ticket/index','class'=>'','is_developer'=>0),
                    array('title'=>'发布优惠券','url'=>'AddTicket/add','class'=>'','is_developer'=>0),
                    array('title'=>'广告红包列表','url'=>'Bonus/faceIndex','class'=>'','is_developer'=>0),
                )
            ),
            array(
                'group' => array('title'=>'店铺管理','icon'=>'fa fa-wrench','class'=>'','url'=>'javascript:void(0);','is_developer'=>0),
                '_child' => array(
                    array('title'=>'店铺信息','url'=>'Merchant/index','class'=>'','is_developer'=>0),
                    array('title'=>'地址库管理','url'=>'MerchantAddress/index','class'=>'','is_developer'=>0),
                    array('title'=>'运费模板','url'=>'MerchantTemplate/index','class'=>'','is_developer'=>0),
                    array('title'=>'部门管理','url'=>'MerchantDepartment/index','class'=>'','is_developer'=>0),
                    array('title'=>'绑定用户账号','url'=>'MerchantBindUser/index','class'=>'','is_developer'=>0),
                    array('title'=>'资质信息','url'=>'License/license','class'=>'','is_developer'=>0),
                    array('title'=>'口号设置','url'=>'Slogan/slogan','class'=>'','is_developer'=>0),
                    array('title'=>'修改密码','url'=>'Repass/repass','class'=>'','is_developer'=>0),
                    array('title'=>'退换货地址','url'=>'BackAddress/backAddress','class'=>'','is_developer'=>0),
                    array('title'=>'配送方式','url'=>'Shipping/index','class'=>'','is_developer'=>0),
                    array('title'=>'店铺首页推荐','url'=>'MerchantIndex/index','class'=>'','is_developer'=>0),
                    array('title'=>'店内公告管理','url'=>'Announce/index','class'=>'','is_developer'=>0),
                )
            ),

        ),
    );