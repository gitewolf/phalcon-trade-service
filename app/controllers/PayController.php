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
use Services\OrderService;
use Services\PayService;

class PayController extends ControllerBase
{
    /** @var  PayService $service */
    private $service;

    public function initialize()
    {
        parent::initialize();
        $this->service = $this->payService;
    }


    public function listPageAction()
    {
        // 当前页
        $currentPage = $this->getRequest('current_page', 'int', 1);
        // 每页显示条数
        $pageSize = $this->getRequest('page_size', 'int', 20);
        $result = $this->service->listPage($currentPage, $pageSize);
        $this->_RESTful($result);
    }

    /**
     * 获取阿里支付二维码
     * @throws \Exceptions\PikException
     */
    public function getAliQrcodeAction()
    {
        $orderId = $this->getRequest('order_id', 'string');
        $this->checkParamsEmpty([
            $orderId,
        ]);
        $result = $this->service->getAliPayQrcode($orderId);
        $this->_RESTful($result);
    }

    /**
     * 获取阿里支付链接
     * @throws \Exceptions\PikException
     */
    public function getAliPayWebUrlAction()
    {
        $orderId = $this->getRequest('order_id', 'string');
        $this->checkParamsEmpty([
            $orderId,
        ]);
        $order = $this->orderService->getOrder($orderId);
        if(empty($order)){
            throw new PikException(ErrorCode::ORDER_ID_ERROR);
        }

        $result = $this->service->getAliPayWebUrl($order);
        $this->_RESTful($result);
    }


    /**
     * 获取阿里支付详情
     * @throws \Exceptions\PikException
     */
    public function queryAliTradeDetailAction()
    {
        $orderId = $this->getRequest('order_id', 'string');
        $trade_no = $this->getRequest('trade_no', 'string');
        $this->checkParamsEmpty([
            $orderId,
        ]);

        $result = $this->service->queryAliTradeDetail($orderId, $trade_no);
        $this->_RESTful($result);
    }

    /**
     * 阿里二维码支付成功通知
     * @throws \Exceptions\PikException
     */
    public function aliNotifyAction()
    {
        $orderId = $this->getRequest('out_trade_no', 'string');
        $notifyType = $this->getRequest('notify_type', 'string');
        $receipt_amount = $this->getRequest('receipt_amount', 'string');
        $trade_no = $this->getRequest('trade_no', 'string');
        $this->checkParamsEmpty([
            $orderId,$trade_no
        ]);
        /** @var OrderService $orderService */
        $orderService = $this->orderService;
        $order = $orderService->getOrder($orderId);
        if($order->status == 1){//需要处理订单状态
            try{
                $aliResult = $this->service->queryAliTradeDetail($orderId, $trade_no);
                if(isset($aliResult['is_success']) && $aliResult['is_success'] == 'T'){
                    //订单成功,处理后续请求
                    $orderService->paySucceed($orderId, $aliResult['response']['receipt_amount']);
                }else{
                    $this->logService->error("Wrong ali Notify: ".json_encode($_REQUEST)."\n");
                    $this->_RESTful(false);
                }
            }catch(PikException $e){
                //送到错误处理队列
                $orderService->sendRetryMQ($orderId,Constants::RETRY_TYPE_PAY_SUCCEED);
                $this->logger->error("Wrong ali Notify: " . json_encode($_REQUEST) . "\n");
                throw $e;
            }
        }

        $this->_RESTful(true);
    }


    /**
     * 阿里二维码支付成功通知
     * @throws \Exceptions\PikException
     */
    public function retryPaySucceedAction()
    {
        $orderId = $this->getRequest('order_id', 'string');
        $retry_num = $this->getRequest('retry_num', 'int', 1);
        $this->checkParamsEmpty([
            $orderId
        ]);
        /** @var OrderService $orderService */
        $orderService = $this->orderService;
        $order = $orderService->getOrder($orderId);

        if($order->status == 1){//需要处理订单状态
            try{
                $result = $this->service->queryAliTradeDetail($orderId, '');
                if(isset($result['is_success']) && $result['is_success'] == 'T'){
                    //订单成功,处理后续请求
                    $orderService->paySucceed($orderId, $result['response']['amount']);

                }else{
                    $this->logService->error("Wrong ali Notify: ".json_encode($_REQUEST)."\n");
                    $this->_RESTful(false);
                }
            }catch(PikException $e){
                $orderService->sendRetryMQ($orderId,Constants::RETRY_TYPE_PAY_SUCCEED,$retry_num);
                throw $e;
            }
        }else{
            $this->tradeLogger->info("Order's status of {orderId} has changed to {status}.", ['orderId'=>$orderId, 'status'=>$order->status]);
        }

        $this->_RESTful(true);
    }







}