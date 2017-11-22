<?php

/**
 * Description of PikException
 *
 * @author jacky.deng
 */
namespace Common;

class Constants
{
    //主题包括的资源类型
    static $matterTypes = ['material', 'action', 'scene', 'prop', 'bgm', 'bubble'];
    //在主题页面中每种类型对应的显示数量
    static  $matterTypeMaxNum = [
        'material' => 8,
        'action' => 8,
        'scene' => 4,
        'prop' => 8,
        'bgm' => 8,
        'bubble' => 8
    ];
    //在主题页面中每种类型对应的名称
    static $matterTypeName = [
        'material' => '形象',
        'action' => '动作',
        'scene' => '场景',
        'prop' => '道具',
        'bgm' => '背景音',
        'bubble' => '气泡'
    ];

    const RETRY_TYPE_PAY_SUCCEED = 1;
    const RETRY_TYPE_ORDER_REFUND = 2;
    const RETRY_TYPE_SEND_AWARDS = 3;
    //需要重试的任务列表
    static $mqRetryActions = [
        self::RETRY_TYPE_PAY_SUCCEED => 'pay/retryPaySucceed',
        self::RETRY_TYPE_ORDER_REFUND => 'order/retryOrderRefund',
        self::RETRY_TYPE_SEND_AWARDS => 'order/retrySendRewards',
    ];

    const MQ_ORDER_RETRY_LIST = 'MQ_ORDER_RETRY_LIST';
    const H_ORDER_RETRY_BEYOND = 'H_ORDER_RETRY_BEYOND';


    const MQ_TRADE_TASK_LIST = 'MQ_TRADE_TASK_LIST';
    const TASK_TYPE_ORDER_CANCEL = 1;
    //需要重试的任务列表
    static $mqTaskActions = [
        self::TASK_TYPE_ORDER_CANCEL => 'order/cancel',
    ];

    //订单重试最大次数
    const ORDER_RETRY_MAX_NUM = 6;
    //订单等待支付过期时间 1个小时
    const ORDER_OVERTIME_SECOND = 3600;

    const MQ_ORDER_PAY_CHECK_LIST = 'MQ_ORDER_PAY_CHECK_LIST';

    const H_MQ_ORDER_BEYOND_RETRY = 'H_MQ_ORDER_BEYOND_RETRY';



}
