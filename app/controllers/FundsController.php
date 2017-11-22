<?php
/**
 * Created by PhpStorm.
 * User: jacky
 * Date: 17-9-17
 * Time: 下午4:51
 */

namespace Controllers;

use Common\ErrorCode;
use Exceptions\PikException;
use Models\Pro\MpUser;
use Models\Pro\Order;
use Services\CouponService;
use Services\FundsService;
use Services\GoodsService;
use Services\OrderService;
use Services\UserServices;

class FundsController extends ControllerBase
{
    /** @var  FundsService $service */
    private $service;

    public function initialize()
    {
        parent::initialize();
        $this->service = $this->fundsService;

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
     * 新建修改用户资金账号
     * @throws \Exceptions\PikException
     */
    public function saveInfoAction()
    {
        $uid = $this->getRequest('uid', 'string');
        $fundsArr = [];
        $fundsArr['pay_passport'] = $this->getRequest('pay_passport', 'string');
        $fundsArr['contact_name'] = $this->getRequest('contact_name', 'string');
        $fundsArr['contact_mobile'] = $this->getRequest('contact_mobile', 'string');
        $fundsArr['address'] = $this->getRequest('address', 'string');
        $fundsArr['post_code'] = $this->getRequest('post_code', 'string');
        $fundsArr['remark'] = $this->getRequest('remark', 'string');
        $this->checkParamsEmpty([
            $uid,
        ]);

        $result = $this->service->saveFoundsInfo($uid, $fundsArr);
        $this->_RESTful($result);
    }


    /**
     * 新建修改用户资金账号
     * @throws \Exceptions\PikException
     */
    public function addUserCoinAction()
    {
        $uid = $this->getRequest('uid', 'string');
        $bizType = $this->getRequest('biz_type', 'string');
        $coin = $this->getRequest('coin', 'string');
        $order_id = $this->getRequest('order_id', 'string');
        $remark = $this->getRequest('remark', 'string');
        $this->checkParamsEmpty([
            $uid, $bizType, $coin
        ]);

        $result = $this->service->addUserCoin($uid, $bizType, $coin, $order_id, $remark);
        $this->_RESTful($result);
    }


    /**
     * 新建修改用户资金账号
     * @throws \Exceptions\PikException
     */
    public function getUserAccountAction()
    {
        $uid = $this->getRequest('uid', 'string');
        $fundsInfo = $this->service->getUserFundsInfo($uid);
        /** @var CouponService $couponService */
        $couponService = $this->couponService;
        $couponInfo = $couponService->getAvailableAmount($uid);

        $exportCoupon = isset($couponInfo['1'])?$couponInfo['1']:0;
        $userAccount = [
            'uid' => $uid,
            'coin' => isset($fundsInfo->coin) ? $fundsInfo->coin : 0,
            'export_coupon' => $exportCoupon
        ];
        $this->_RESTful($userAccount);
    }

}