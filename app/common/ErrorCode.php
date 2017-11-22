<?php

/**
 * Description of ErrorCode
 *
 * @author jacky.deng
 */
namespace Common;

class ErrorCode
{
    //系统接口错误，判断数据是否已读接口
    //接口相关异常10000-10999
    //用户相关异常20000-20999
    //社区相关异常30000-30999
    //素材相关异常40000-40999
    //专业版相关异常50000-50999
    //财务相关异常80000-80999
    //系统相关异常90000-90999

    //接口参数错误
    const PARAM_ERROR = 10000;
    //接口参数太长
    const PARAM_TOO_LONG_ERROR = 10001;
    //接口不存在
    const API_ERROR = 10100;
    //客户端版本不存在
    const VERSION_ERROR = 10200;
    //需要强制更新
    const VERSION_OUT_DATE = 10201;


    //用户相关异常20000
    //---------用户相关----------

    //用户名密码不匹配
    const LOGIN_FAILED = 20002;
    //用户登录失效
    const USER_NEED_LOGIN = 20001;
    //用户不存在
    const USER_NOT_EXISTS = 20003;
    //邮箱不可用
    const EMAIL_ILLEGAL_USE = 20004;
    //手机不可用
    const MOBILE_ILLEGAL_USE = 20005;

    const USER_DELETE_SELF = 20006;

    //用户名不存在
    const USERNAME_NOT_FOUND = 20006;
    //用户不能登录后台
    const USER_NOT_MANAGER = 20007;
    //用户已经存在
    const USER_DUPLICATED = 20100;
    //用户邮箱已经存在
    const EMAIL_DUPLICATED = 20101;
    //用户名不合法
    const ILLEGAL_USER_NAME = 20102;
    //用户名太长
    const USER_NAME_TOO_LONG = 20103;
    //用户签名太长
    const USER_SIGNATURE_TOO_LONG = 20104;
    //用户已经绑定邮箱
    const EMAIL_BIND_DUPLICATED = 20105;

    //用户被禁用
    const USER_IS_FORBIDDEN = 20204;
    //用户信息需要更新
    const USER_INFO_NEED_UPDATE = 20205;

    const VERIFY_CAPTCHA_FAILED = 20301;
    //验证失败
    const  VERIFY_MOBILE_FAILED = 20302;

    const CREATE_FAILED = 30000;
    const DELETE_FAILED = 30001;
    const UPDATE_FAILED = 30002;


    //80000 支付相关

    //商品
    const  GOODS_NOT_EXIST = 80101;      //该商品不存在
    const  GOODS_SAVE_FAILED = 80102;   //商品保存失败
    //消息
    const  MESSAGE_NOT_EXIST = 51001;      //该消息不存在
    const  MESSAGE_SAVE_FAILED = 51002;   //该消息保存失败

    //订单
    const  ORDER_NOT_EXIST = 80201;      //订单不存在
    const  ORDER_SAVE_FAILED = 80202;   //订单保存失败
    const  ORDER_CANNOT_TO_PAY = 80203;   //订单不能进行支付
    const  ORDER_MONEY_NOT_MATCH = 80204;   //订单金额不匹配
    const  ORDER_NOT_MATCH_USER = 80205;   //订单与支付用户不匹配
    const  PAY_PASSPORT_ERROR = 80206;   //支付密码错误
    const  PAY_COIN_NOT_ENOUGH= 80207;   //用户账号余额不足
    const  ORDER_CANNOT_CANCEL= 80208;   //该订单不可取消
    const  ORDER_RESTITUTE_FAILED= 80209;   //订单复原失败
    const  ORDER_ID_ERROR = 80210;      //订单号错误
    const  ORDER_GOODS_SEND_FAILED = 80211;      //订单对应产品发送失败
    const  ORDER_EXPORT_CREATE_FAILED = 80212;      //订单对应导出产品发送失败



    const  FUNDS_SAVE_FAILED = 80221;   //资金变更失败


    //优惠券
    const  COUPON_PLAN_NOT_EXIST = 80301;      //优惠券计划不存在
    const  COUPON_PLAN_SAVE_FAILED = 80302;   //优惠券计划保存失败
    const  COUPON_NOT_EXIST = 80311;      //优惠券不存在
    const  COUPON_SAVE_FAILED = 80312;   //优惠券保存失败
    const  COUPON_HAS_BIND_ALREADY = 80313;   //优惠券已经被人绑定
    const  COUPON_OUT_OF_DATE = 80314;   //优惠券已经过期
    const  COUPON_BALANCE_NOT_ENOUGH = 80315;   //优惠券余额不足
    const  COUPON_NOT_FOUND = 80316;   //没有可用的优惠券

    const  PAY_CHARGE_GET_QR_ERROR = 80001; // 获取二维码支付失败
    const  PAY_CHARGE_GET_URL_ERROR = 80002; // 获取支付地址失败

    //系统异常
    const SYSTEM_ERROR = 90000;
    //Service异常
    const INVALID_SERVICE_ERROR = 90001;
    //推送消息发送异常
    const PUSH_MESSAGE_SEND_ERROR = 90002;
    //数据类型异常
    const INVALID_TYPE_ERROR = 90003;


    //错误信息
    static $_errorMsgArr = array(
        self::PARAM_ERROR => '接口参数错误',
        self::PARAM_TOO_LONG_ERROR => '接口参数太长',
        self::API_ERROR => '接口不存在',
        self::VERSION_ERROR => '客户端版本不存在',
//20000
        self::LOGIN_FAILED => '用户名密码不匹配',
        self::USER_NOT_MANAGER => '用户不是管理员',
        self::USER_NEED_LOGIN => '用户需要登录',
        self::USERNAME_NOT_FOUND => '用户名不存在',
        self::USER_DUPLICATED => '用户已经存在',
        self::EMAIL_DUPLICATED => '邮箱已经存在',
        self::ILLEGAL_USER_NAME => '用户名不合法',
        self::USER_NAME_TOO_LONG => '用户名太长',
        self::USER_SIGNATURE_TOO_LONG => '用户签名太长',
        self::EMAIL_BIND_DUPLICATED => '用户邮箱已经绑定',
        self::USER_NOT_EXISTS => '用户不存在',
        self::USER_IS_FORBIDDEN => '用户被禁用',
        self::USER_INFO_NEED_UPDATE => '用户信息需要更新',
        self::CREATE_FAILED => '添加失败',
        self::UPDATE_FAILED => '更新失败',
        self::DELETE_FAILED => '删除失败',

        //80000 支付相关
        self::PAY_CHARGE_GET_QR_ERROR => '获取二维码支付失败',
        self::PAY_CHARGE_GET_URL_ERROR => '获取支付地址失败',

        self::GOODS_NOT_EXIST => '该商品不存在',
        self::GOODS_SAVE_FAILED => '商品保存失败',

        self::MESSAGE_NOT_EXIST => '该消息不存在',
        self::MESSAGE_SAVE_FAILED => '该消息保存失败',

        self::ORDER_NOT_EXIST => '订单不存在',
        self::ORDER_SAVE_FAILED => '订单保存失败',
        self::ORDER_CANNOT_TO_PAY => '订单不能进行支付',
        self::ORDER_MONEY_NOT_MATCH => '订单金额不匹配',
        self::ORDER_NOT_MATCH_USER => '订单与支付用户不匹配',
        self::PAY_PASSPORT_ERROR => '支付密码错误',
        self::PAY_COIN_NOT_ENOUGH => '用户账号余额不足',
        self::ORDER_CANNOT_CANCEL => '该订单不可取消',
        self::ORDER_RESTITUTE_FAILED => '订单复原失败',
        self::ORDER_ID_ERROR => '订单号错误',
        self::ORDER_GOODS_SEND_FAILED => '订单对应产品发送失败',
        self::ORDER_EXPORT_CREATE_FAILED => '订单对应导出产品发送失败',


        self::COUPON_PLAN_NOT_EXIST => '优惠券计划不存在',
        self::COUPON_PLAN_SAVE_FAILED => '优惠券计划保存失败',
        self::COUPON_NOT_EXIST => '优惠券不存在',
        self::COUPON_SAVE_FAILED => '优惠券保存失败',
        self::COUPON_HAS_BIND_ALREADY => '优惠券已经被人绑定',
        self::COUPON_OUT_OF_DATE => '优惠券已经过期',
        self::COUPON_BALANCE_NOT_ENOUGH => '优惠券余额不足',
        self::COUPON_NOT_FOUND => '没有可用的优惠券',

        self::FUNDS_SAVE_FAILED => '资金变更失败',


        //90000
        self::SYSTEM_ERROR => '系统异常',
        self::INVALID_SERVICE_ERROR => '服务service没有找到',
        self::PUSH_MESSAGE_SEND_ERROR => '推送消息发送异常',
        self::INVALID_TYPE_ERROR => '类型错误'

    );
}
