<?php
/**
 * Created by PhpStorm.
 * User: jacky
 * Date: 17-6-17
 * Time: 上午10:47
 */

namespace Services;


use Common\Constants;
use Common\ErrorCode;
use Exceptions\PikException;
use Models\Pro\Goods;
use Models\Pro\Message;
use Models\Pro\OrderDetail;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Models\Pro\Order;

class OrderService extends BaseService
{

    /** @var Order $model */
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Order();
    }

    /**
     * 新增订单
     * @param $orderArr
     * @param $orderGoodsArr
     * @return Order
     * @throws PikException
     */
    public function create($orderArr, $orderGoodsArr)
    {
        $transaction = $this->getTransaction();
        try {
            $order = new Order();
            $order->init($orderArr);
            $order->setTransaction($transaction);
            if ($order->type == 1) { //充值类型
                $order->pay_type = 1;
                $order->pay_coupon = 0;
                $order->pay_coin = 0;
                $order->pay_money = $order->order_money;
            } else {
                if ($order->pay_type == Order::PAY_TYPE_MONEY) {
                    $order->pay_money = $order->order_money - $order->pay_coupon;
                    $order->pay_coin = 0;
                } else {
                    $order->pay_coin = $order->order_money - $order->pay_coupon;
                    $order->pay_money = 0;
                }
            }
            $order->status = Order::STATUS_INIT;
            if ($order->pay_coupon > 0) {
                $usedCoupon = $this->preReduceCoupon($order);
                if (!empty($usedCoupon)) {
                    $order->pay_coupon_detail = json_encode($usedCoupon);
                }
                if ($order->pay_coupon == $order->order_money) {
                    //全部用导出券支付成功
                    $order->pay_coin = 0;
                    $order->pay_money = 0;
                    $order->status = Order::STATUS_PAYED;
                    $order->pay_time = time();
                }
            }

            $order->order_id = $this->generateOrderId();
            if ($order->save() == false) {
                $transaction->rollback("Cannot save order");
                throw new PikException(ErrorCode::ORDER_SAVE_FAILED);
            }

            //生成商品订单详情
            foreach ($orderGoodsArr as $orderGoods) {
                $orderDetail = new OrderDetail();
                $orderDetail->setTransaction($transaction);
                $orderDetail->init($orderGoods);
                $orderDetail->status = OrderDetail::STATUS_INIT;
                $orderDetail->order_id = $order->order_id;
                if ($orderDetail->save() == false) {
                    $transaction->rollback("Cannot save order");
                    throw new PikException(ErrorCode::ORDER_SAVE_FAILED);
                }
            }
            $transaction->commit();
            return $order;
        } catch (TxFailed $e) {
            throw new PikException (ErrorCode::CREATE_FAILED);
        }
    }

    /**
     * 支付成功
     * @param $orderId
     * @param $receipt_amount
     * @return Order
     * @throws PikException
     */
    public function paySucceed($orderId, $receipt_amount)
    {
        $transaction = $this->getTransaction();
        /** @var GoodsService $goodsService */
        $goodsService = $this->getShared('goodsService');
        /** @var FundsService $fundsService */
        $fundsService = $this->getShared('fundsService');
        /** @var CouponService $couponService */
        $couponService = $this->getShared('couponService');
        try {
            $orderGoods = $this->getOrderDetails($orderId);
            $order = $this->getOrder($orderId, true);
            if ($order->status != 1) {
                //订单状态错误
                throw new PikException (ErrorCode::ORDER_SAVE_FAILED);
            }
            if ($order->pay_money != $receipt_amount) {
                //订单金额错误
                throw new PikException(ErrorCode::ORDER_MONEY_NOT_MATCH);
            }
            $order->setTransaction($transaction);

            $order->status = Order::STATUS_SEND;
            $order->pay_time = time();
            if ($order->save() == false) {
                $this->logger->error($order->getMessages());
                $transaction->rollback("Cannot save order");
            }
            if ($order->type == Order::TYPE_EXPORT) {
                //通知pro_service 导出订单支付成功
                $this->createExportInfo($order);
            } else {
                foreach ($orderGoods as $orderDetail) {
                    $goodsId = $orderDetail->goods_id;
                    $buy_num = $orderDetail->buy_num;
                    $goods = $goodsService->detail($goodsId);
                    if ($goods->goods_type == 3) { //充值
                        if (!empty($goods->coupons)) {
                            $coupons = $goods->coupons;
                            $coupons = json_decode($coupons, true);
                            foreach ($coupons as $couponPlanId => $amount) {
                                $couponService->createCoupon($couponPlanId, $order->buyer_id, $amount, true);
                            }
                        }
                        if (!empty($goods->recharge_coin)) {
                            $fundsService->addUserCoin($order->buyer_id, 1, $goods->recharge_coin * $buy_num, $orderId, $goods->name . "(充值)", true);
                        }
                        if (!empty($goods->bonus_coin)) {
                            $fundsService->addUserCoin($order->buyer_id, 1, $goods->bonus_coin * $buy_num, $orderId, $goods->name . "(赠送)", true);
                        }
                    }
                }
            }
            $transaction->commit();
            return $order;
        } catch (PikException $e) {
//            $this->logger->info('test');
//            register_shutdown_function(
//                [$transaction, 'rollback'], 'save Order Faild');
            throw $e;
        } catch (TxFailed $e) {
//            var_dump($e);
            throw new PikException (ErrorCode::ORDER_SAVE_FAILED);
        }
    }

    /**
     * 皮币支付
     * @param $orderId
     * @param $uid
     * @param string $pwd
     * @return Order
     * @throws PikException
     */
    public function coinPay($orderId, $uid, $pwd = '')
    {
        $transaction = $this->getTransaction();
        /** @var FundsService $fundsService */
        $fundsService = $this->getShared('fundsService');
        $funds = $fundsService->getUserFundsInfo($uid);
        //暂时不判断支付密码
//        if (!empty($funds->pay_passport) && $funds->pay_passport != $pwd) {
//            throw new PikException (ErrorCode::PAY_PASSPORT_ERROR);
//        }
        try {
            $order = $this->getOrder($orderId, true);
            if (empty($order)) {
                throw new PikException(ErrorCode::ORDER_ID_ERROR);
            }
            if ($order->status == 2 || $order->status == 3) {
                //订单状态属于已经支付则直接返回成功
                return $order;
            } else if ($order->status != 1) {
                //订单状态错误
                throw new PikException (ErrorCode::ORDER_SAVE_FAILED);
            }
            if ($order->buyer_id != $uid) {
                throw new PikException (ErrorCode::ORDER_NOT_MATCH_USER);
            }

            if ($order->pay_coin > $funds->coin) {
                //用户账号余额不足
                throw new PikException(ErrorCode::PAY_COIN_NOT_ENOUGH);
            }
            $fundsService->addUserCoin($order->buyer_id, 2, $order->pay_coin, $orderId, '订单支付', true);
            $order->setTransaction($transaction);
            $order->status = Order::STATUS_PAYED;
            $order->pay_time = time();
            if ($order->save() == false) {
                $this->logger->error($order->getMessages());
                $transaction->rollback("Cannot save order");
            }
            if ($order->type == Order::TYPE_EXPORT) {
                //通知pro_service 导出订单支付成功
//                $this->createExport($order->export_info);
            }
            $transaction->commit();
            return $order;
        } catch (PikException $e) {
            throw $e;
        } catch (TxFailed $e) {
            throw new PikException (ErrorCode::ORDER_SAVE_FAILED);
        }
    }

    /**
     * 取消订单
     * @param $orderId
     * @return bool
     * @throws PikException
     */
    public function cancel($orderId)
    {
        $order = $this->getOrder($orderId);
        if (empty($order)) {
            throw new PikException(ErrorCode::ORDER_NOT_EXIST);
        }
        if ($order->status != Order::STATUS_INIT) {
            throw new PikException(ErrorCode::ORDER_CANNOT_CANCEL);
        }
        $order->status = Order::STATUS_CANCEL;
        if (!empty($order->pay_coupon_detail)) {
            $order->refund_money_status = Order::REFUND_MONEY_STATUS_PROCESS;
        }
        $result = $order->save();
        if ($result && !empty($order->pay_coupon_detail)) {
            register_shutdown_function([$this, 'refundCoupon'], $orderId);
        }
        return $result;
    }

    /**
     * 取消订单后回退对应的导出券
     * @param $orderId
     * @param $retryNum
     * @return bool
     * @throws PikException
     */
    public function refundCoupon($orderId, $retryNum = 0)
    {
        $transaction = $this->getTransaction();
        try {

            $order = $this->getOrder($orderId, true);
            if ($order->status == Order::STATUS_CANCEL && $order->refund_money_status == Order::REFUND_MONEY_STATUS_PROCESS) {
                file_put_contents("/tmp/test_trade.log", "开始回退优惠券 \n", FILE_APPEND);
                $couponDetail = $order->pay_coupon_detail;

                file_put_contents("/tmp/test_trade.log", $couponDetail . " \n", FILE_APPEND);
                $couponDetail = json_decode($couponDetail);
            }
            if (empty($couponDetail)) {
                return true;
            }
            /** @var CouponService $couponService */
            $couponService = $this->getShared('couponService');
            foreach ($couponDetail as $code => $amount) {
                $couponService->restitution($order->buyer_id, $amount, $code, true);
            }
            $order->refund_money_status = Order::REFUND_MONEY_STATUS_FINISHED;
            $order->save();
            $transaction->commit();
            return $order;
        } catch (PikException $e) {
            $this->tradeLogger->error("Refund Coupon Error for Order :{orderId}, error message is {errMsg}.",
                ['orderId' => $orderId, 'errMsg' => $e->getErrMsg($e->getCode())]);
            $this->sendRetryMQ($orderId, Constants::RETRY_TYPE_ORDER_REFUND, $retryNum);
        } catch (TxFailed $e) {
            $this->tradeLogger->error("Refund Coupon Error for Order :{orderId}, error message is {errMsg}.",
                ['orderId' => $orderId, 'errMsg' => $e->getMessage()]);
            $this->sendRetryMQ($orderId, Constants::RETRY_TYPE_ORDER_REFUND, $retryNum);
        }
    }


    /**
     * 导出订单支付成功后生成导出码肌瘤
     * @param Order $order
     * @throws PikException
     */
    public function createExportInfo(Order $order)
    {
        $params = json_decode($order->export_info);
        $export_info['use_coupon'] = $order->pay_coupon;
        $export_info['use_coin'] = empty($order->pay_money) ? $order->pay_money : $order->pay_coin;
        try {
            $createResult = $this->callProService('export/create', $params);
            if (empty($createResult['effective'])) {
                throw new PikException(ErrorCode::ORDER_EXPORT_CREATE_FAILED);
            }
        } catch (\Exception $e) {
            throw new PikException(ErrorCode::ORDER_EXPORT_CREATE_FAILED);
        }

    }

    //改变订单状态
    public function updateStatus($orderId, $status)
    {
        $order = $this->getOrder($orderId);
        if (empty($order)) {
            throw new PikException(ErrorCode::ORDER_NOT_EXIST);
        }
        $order->status = $status;
        return $order->save();
    }


    /**
     * 获取订单信息
     * @param $orderId
     * @return Order
     */
    public function getOrder($orderId, $forUpdate = false)
    {
        $queryParam = ['order_id =:order_id:'];
        $queryParam['bind'] = ['order_id' => $orderId];
        if ($forUpdate) {
            $queryParam['for_update'] = true;
        }
        $order = Order::findFirst($queryParam);
        return $order;
    }


    /**
     * 获取订单商品列表
     * @param $orderId
     * @return \Phalcon\Mvc\Model\ResultsetInterface
     */
    public function getOrderDetails($orderId)
    {
        $queryParam = ['order_id =:order_id:'];
        $queryParam['bind'] = ['order_id' => $orderId];
        $orderGoods = OrderDetail::find($queryParam);
        return $orderGoods;
    }

    /**
     * 分页返回订单列表
     * @param $currentPage
     * @param $pageSize
     * @param int $buyer_id
     * @param int $seller_id
     * @param int $status
     * @param int $type
     * @param string $order_id
     * @return array
     */
    public function listPage($currentPage, $pageSize, $buyer_id = 0, $seller_id = 0, $status = 0, $type = 0, $orderId = '')
    {
        $queryParam = [];
        $queryParam['conditions'] = ' 1 = 1 ';
        if (!empty($status)) {
            $queryParam['conditions'] = $queryParam['conditions'] . 'and status = :status: ';
            $queryParam['bind']['status'] = $status;
        }
        if (!empty($buyer_id)) {
            $queryParam['conditions'] = $queryParam['conditions'] . 'and buyer_id = :buyer_id: ';
            $queryParam['bind']['buyer_id'] = $buyer_id;
        }
        if (!empty($seller_id)) {
            $queryParam['conditions'] = $queryParam['conditions'] . 'and seller_id = :seller_id: ';
            $queryParam['bind']['seller_id'] = $seller_id;
        }
        if (!empty($orderId)) {
            $queryParam['conditions'] = $queryParam['conditions'] . 'and order_id like :order_id: ';
            $queryParam['bind']['order_id'] = '%' . $orderId . '%';
        }
        if (!empty($type)) {
            $queryParam['conditions'] = $queryParam['conditions'] . 'and type = :type: ';
            $queryParam['bind']['type'] = $type;
        } else { //type = 0 的时候返回所有非充值的订单
            $queryParam['conditions'] = $queryParam['conditions'] . 'and type != 1 ';
        }
        //分页
        $pageInfo = $this->mysqlPageInfo($currentPage, $pageSize, $queryParam, $this->model);
        $queryParam['order'] = 'order_id DESC';
        $queryParam['limit'] =
            [
                "offset" => ($currentPage - 1) * $pageSize,
                "number" => $pageSize
            ];
        $orderList = $this->model->find($queryParam)->toArray();
        $result = [
            'page' => $pageInfo,
            'data' => $orderList
        ];
        return $result;
    }


    /**
     * 生成OrderId
     * @return bool
     * @throws PikException
     */
    public function generateOrderId()
    {
        $time_str = date('YmdH', time());
        $time_str = substr($time_str, 2);
        /** @var \Redis $redis */
        $redis = $this->getShared('redis');
        $orderKey = 'ORDER_ID_KEY_' . $time_str;
        $keyNum = $redis->incr($orderKey);
        if ($keyNum == 1) {
            $redis->expire($orderKey, 8640);
        }
        $rand = rand(0, 99999);
        $orderId = intval($time_str) * 100000 + $keyNum;
        $orderId = $orderId . $rand;
        return $orderId;
    }

    /**
     * 生成订单时先预扣除优惠券
     * @param $order
     * @return bool
     * @throws PikException
     */
    protected function preReduceCoupon($order)
    {
        /** @var CouponService $couponService */
        $couponService = $this->getShared('couponService');
        $couponType = $couponService->getCouponTypeForOrder($order->type);
        if (empty($couponType)) {
            throw new PikException(ErrorCode::COUPON_NOT_FOUND);
        }
        $usedCoupon = $couponService->expense($order->buyer_id, $order->pay_coupon, 1, true);
        return $usedCoupon;
    }

    /**
     * 发送到错误处理队列
     * @param $orderId
     * @param $type
     * @param array $param
     * @param int $tryNum
     */
    public function sendRetryMQ($orderId, $type, $tryNum = 0, $param = [])
    {

        /** @var \Redis $redis */
        $redis = $this->getShared('redis');
        $param['order_id'] = $orderId;
        $param['try_num'] = $tryNum + 1;
        $param['action'] = Constants::$mqRetryActions[$type];
        $orderKey = json_encode($param);
        if ($tryNum <= Constants::ORDER_RETRY_MAX_NUM) {
            $orderRetryKey = Constants::MQ_ORDER_RETRY_LIST;
            $tryNum++;
            $retryTime = time() + $this->getCDTime($tryNum);
            $redis->zAdd($orderRetryKey, $retryTime, $orderKey);
        } else {
            //记录到超时处理日志中
            $orderOverRetry = Constants::H_ORDER_RETRY_BEYOND;
            $redis->hset($orderOverRetry, $orderId . '_' . $type, json_encode($param));
        }
    }


    /**
     * 发送到任务处理队列
     * @param $orderId
     * @param $type
     * @param array $param
     */
    public function sendTaskMQ($orderId, $type, $param = [])
    {
        /** @var \Redis $redis */
        $redis = $this->getShared('redis');
        $param['order_id'] = $orderId;
        $param['action'] = Constants::$mqTaskActions[$type];
        $redis->lPush(Constants::MQ_TRADE_TASK_LIST, json_encode($param));
    }


    /**
     * 分页返回订单列表
     */
    public function checkOvertimeOrders()
    {
        $queryParam = [];
        $orderTime = time() - Constants::ORDER_OVERTIME_SECOND;
        $queryParam['conditions'] = ' status = 1 and create_time < :create_time:';
        $queryParam['bind']['create_time'] = $orderTime;

        $orderList = $this->model->find($queryParam);
        foreach ($orderList as $order) {
            $this->sendTaskMQ($order->order_id, Constants::TASK_TYPE_ORDER_CANCEL);
        }
    }


    /**
     * 根据重试次数获取下次重试的时间间隔
     * @param $tryNum
     * @return int
     */
    private function getCDTime($tryNum)
    {
        $cdTime = 0;
        switch ($tryNum) {
            case 1:
                $cdTime = 3;
                break;
            case 2:
                $cdTime = 30;
                break;
            case 3:
                $cdTime = 120;
                break;
            case 4:
                $cdTime = 600;
                break;
            case 5:
                $cdTime = 3600;
                break;
        }
        return $cdTime;
    }

}