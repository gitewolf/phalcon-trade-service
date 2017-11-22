<?php
/**
 * Created by PhpStorm.
 * User: ewolf
 * Date: 2017/3/13
 * Time: 11:05
 */

//用户相关service
$di->set('userService', function () {
    return new \Services\UserServices();
});

//商品相关service
$di->set('goodsService', function () {
    return new \Services\GoodsService();
});
//优惠券相关service
$di->set('couponService', function () {
    return new \Services\CouponService();
});
//消息service
$di->set('orderService', function () {
    return new \Services\OrderService();
});
//消息service
$di->set('messageService', function () {
    return new \Services\MessageService();
});
//资金账号service
$di->set('fundsService', function () {
    return new \Services\FundsService();
});
//支付service
$di->set('payService', function () {
    return new \Services\PayService();
});
