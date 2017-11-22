<?php
/**
 * Created by PhpStorm.
 * User: jacky
 * Date: 17-6-17
 * Time: 下午4:51
 */

namespace Controllers;

use Common\Constants;
use Common\ErrorCode;
use Exceptions\PikException;
use Models\Pro\MpUser;
use Models\Pro\Order;
use Services\GoodsService;
use Services\OrderService;
use Services\PayService;
use Services\UserServices;

class OrderController extends ControllerBase
{
    /** @var  OrderService $service */
    private $service;

    public function initialize()
    {
        parent::initialize();
        $this->service = $this->orderService;

    }


    public function listPageAction()
    {
        // 当前页
        $currentPage = $this->getRequest('current_page', 'int', 1);
        // 每页显示条数
        $pageSize = $this->getRequest('page_size', 'int', 20);
        $order_id = $this->request->get('order_id', 'string', '');
        $status = $this->request->get('status', 'int', 0);
        $type = $this->request->get('type', 'int', 0);
        $buyer_id = $this->request->get('buyer_id', 'string', '');
        $seller_id = $this->request->get('seller_id', 'string', '');
        $result = $this->service->listPage($currentPage, $pageSize, $buyer_id, $seller_id, $status, $type, $order_id);
        $this->_RESTful($result);
    }

    /**
     * 直接购买一个商品
     * @throws \Exceptions\PikException
     */
    public function createAction()
    {
        $goodsId = $this->getRequest('goods_id', 'string');
        $buyNum = $this->getRequest('buy_num', 'string');

        $orderArr = [];
        $orderGoodsArr = [];
        $orderArr['buyer_id'] = $this->getRequest('buyer_id', 'string');
        $orderArr['buyer_name'] = $this->getRequest('buyer_name', 'string');
        $orderArr['seller_id'] = '100000000';
        $orderArr['seller_name'] = '商城';
        $orderArr['seller_type'] = 1;
        $orderArr['pay_type'] = $this->getRequest('pay_type', 'int', 0);
        $orderArr['type'] = $this->getRequest('type', 'int', 0); //订单类型
        $orderArr['pay_coupon'] = $this->getRequest('pay_coupon', 'double');
        $orderArr['remark'] = $this->getRequest('remark', 'string');
        $orderArr['export_info'] = $this->getRequest('export_info', 'string');

        $this->checkParamsEmpty([
            $goodsId,
            $buyNum,
            $orderArr['buyer_id'],
            $orderArr['type'],
        ]);

        if ($orderArr['type'] == 2) { //导出
            $orderArr['order_money'] = $this->getRequest('order_money', 'double');
            $orderArr['order_base_money'] = $orderArr['order_money'];

            $orderGoodsArr['buy_num'] = $buyNum;
            $orderGoodsArr['buyer_id'] = $orderArr['buyer_id'];
            $orderGoodsArr['seller_id'] = $orderArr['seller_id'];
            $orderGoodsArr['product_type'] = '';
            $orderGoodsArr['goods_type'] = 2;
            $orderGoodsArr['matter_ids'] = '';
            $orderGoodsArr['coupons'] = '';
            $orderGoodsArr['buy_price'] = $orderArr['order_money'];
            $orderGoodsArr['total_price'] = $orderArr['order_money'];
            $orderGoodsArr['additional_services'] = '';
            $orderGoodsArr['goods_id'] = $goodsId;
            $orderGoodsArr['goods_name'] = '导出动画';
        } else {
            /** @var GoodsService $goodsService */
            $goodsService = $this->goodsService;
            $goods = $goodsService->detail($goodsId);
            if (empty($goods)) {
                throw new PikException(ErrorCode::GOODS_NOT_EXIST);
            }
            $orderArr['order_money'] = $goods->price * $buyNum;
            $orderArr['order_money'] = $goods->price * $buyNum;
            if ($goods->base_price == 0) {
                $orderArr['order_base_money'] = $orderArr['order_money'];
            } else {
                $orderArr['order_base_money'] = $goods->base_price * $buyNum;;
            }
            $orderGoodsArr['buy_num'] = $buyNum;
            $orderGoodsArr['buyer_id'] = $orderArr['buyer_id'];
            $orderGoodsArr['seller_id'] = $orderArr['seller_id'];
            $orderGoodsArr['product_type'] = $goods->product_type;
            $orderGoodsArr['goods_type'] = $goods->goods_type;
            $orderGoodsArr['goods_id'] = $goodsId;
            $orderGoodsArr['goods_name'] = $goods->name;
            $orderGoodsArr['matter_ids'] = $goods->matter_ids;
            $orderGoodsArr['coupons'] = $goods->coupons;
            $orderGoodsArr['buy_price'] = $goods->price;
            $orderGoodsArr['total_price'] = $goods->price * $buyNum;
            $orderGoodsArr['additional_services'] = $goods->additional_services;
        }

        $order = $this->service->create($orderArr, [$orderGoodsArr]);
        if ($order->status == Order::STATUS_INIT && $order->pay_type == Order::PAY_TYPE_MONEY) {
            /** @var PayService $payService */
            $payService = $this->payService;

//            $payQrcode = $payService->getAliPayQrcode($order);
            $payWebUrl = $payService->getAliPayWebUrl($order);
            $order = $order->toArray();
//            $order['pay_qrcode'] = $payQrcode;
            $order['pay_ali_url'] = $payWebUrl;
        }
        $this->_RESTful($order);
    }

    public function detailAction()
    {
        $orderId = $this->getRequest('order_id', 'string');
        $buyerId = $this->getRequest('buyer_id', 'string');
        $sellerId = $this->getRequest('seller_id', 'string');
        $this->checkParamsEmpty($orderId);
        $order = $this->service->getOrder($orderId);
        if (empty($order)) {
            throw new PikException(ErrorCode::ORDER_ID_ERROR);
        }
        if (!empty($buyerId) && $buyerId != $order->buyer_id) {
            throw new PikException(ErrorCode::ORDER_ID_ERROR);
        }
        if (!empty($sellerId) && $sellerId != $order->seller_id) {
            throw new PikException(ErrorCode::ORDER_ID_ERROR);
        }
        /** @var PayService $payService */
        $payService = $this->payService;
        //如果属于待支付状态需要先检查是否已经支付成功
        if ($order->status == Order::STATUS_INIT && $order->pay_type == Order::PAY_TYPE_MONEY) {
            try {
                $aliResult = $payService->queryAliTradeDetail($orderId);
                if (isset($aliResult['is_success']) && $aliResult['is_success'] == 'T') {
                    //订单成功,处理后续请求
                    $order = $this->service->paySucceed($orderId, $aliResult['response']['amount']);
                }
            } catch (PikException $e) {
                //送到错误处理队列
                $this->service->sendRetryMQ($orderId, Constants::RETRY_TYPE_PAY_SUCCEED);
                $this->logger->error("pay Order Failed: " . json_encode($_REQUEST) . "\n");
            }
        }
        if ($order->status == Order::STATUS_INIT && $order->pay_type == Order::PAY_TYPE_MONEY) {
            $payQrcode = $payService->getAliPayQrcode($order);
            $payWebUrl = $payService->getAliPayWebUrl($order);
            $order = $order->toArray();
            $order['pay_qrcode'] = $payQrcode;
            $order['pay_ali_url'] = $payWebUrl;
        }
        $this->_RESTful($order);
    }

    public function updateAction()
    {
        $goodsArr = [];
        $id = $this->getRequest('id', 'int');
        $goodsArr['name'] = $this->getRequest('name', 'string');
        $goodsArr['product_type'] = $this->getRequest('product_type', 'string');
        $goodsArr['product_valid_type'] = $this->getRequest('product_valid_type', 'int');
        $goodsArr['product_valid_period'] = $this->getRequest('product_valid_period', 'int');
        $goodsArr['goods_type'] = $this->getRequest('goods_type', 'int');
        $goodsArr['matter_ids'] = $this->getRequest('matter_ids', 'string');
        $goodsArr['coupons'] = $this->getRequest('coupons', 'string');
        $goodsArr['price'] = $this->getRequest('price', 'double');
        $goodsArr['base_price'] = $this->getRequest('base_price', 'double');
        $goodsArr['img_url'] = $this->getRequest('img_url', 'string');
        $goodsArr['desc'] = $this->getRequest('desc', 'string');
        $goodsArr['additional_services'] = $this->getRequest('additional_services', 'string');
        $this->checkParamsEmpty([
            $id,
            $goodsArr['name'],
            $goodsArr['product_type'],
            $goodsArr['goods_type'],
            $goodsArr['base_price'],
            $goodsArr['price']
        ]);
        $result = $this->service->update($id, $goodsArr);
        $this->_RESTful($result);
    }

    public function cancelAction()
    {
        $orderId = $this->getRequest('order_id', 'int');
        $this->checkParamsEmpty($orderId);
        $result = $this->service->cancel($orderId);
        $this->_RESTful($result);
    }

    public function retryOrderRefundAction()
    {
        $orderId = $this->getRequest('order_id', 'int');
        $tryNum = $this->getRequest('try_num', 'int');
        $this->checkParamsEmpty($orderId);
        $result = $this->service->refundCoupon($orderId, $tryNum);
        $this->_RESTful($result);
    }

    /**
     * 皮币支付订单
     * @throws \Exceptions\PikException
     */
    public function coinPayAction()
    {
        $orderId = $this->getRequest('order_id', 'string');
        $uid = $this->getRequest('uid', 'string');
//        $pwd = $this->getRequest('pwd', 'string');
        $this->checkParamsEmpty([
            $orderId, $uid
        ]);
        $order = $this->service->coinPay($orderId, $uid);
        $this->_RESTful($order);
    }


}