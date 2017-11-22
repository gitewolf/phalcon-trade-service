<?php
/**
 * Created by PhpStorm.
 * User: jacky
 * Date: 17-9-17
 * Time: 下午4:51
 */

namespace Controllers;


use Models\Pro\MpUser;
use Services\CouponService;
use Services\UserServices;

class CouponController extends ControllerBase
{
    /** @var  CouponService $service */
    private $service;

    public function initialize()
    {
        parent::initialize();
        $this->service = $this->couponService;

    }


    public function listPageAction()
    {
        // 当前页
        $currentPage = $this->getRequest('current_page', 'int', 1);
        // 每页显示条数
        $pageSize = $this->getRequest('page_size', 'int', 20);
        $code = $this->request->get('code', 'string', '');
        $status = $this->request->get('status','int',0);
        $result = $this->service->listPage($currentPage, $pageSize);
        if(0 < count($result['data']))
        {
            $gidArray = array_column($result['data'],'gid');
            $gidArray = $this->getQrUserinfoByGid($gidArray);
            array_walk($result['data'],function(&$value)use($gidArray){
                if(isset($gidArray[$value['gid']]))
                {
                    $value['userinfo'] = $gidArray[$value['gid']];
                }
            });

        }
        $this->_RESTful($result);
    }

    public function createAction()
    {
        $plan_id = $this->getRequest('plan_id', 'string');
        $amount = $this->getRequest('amount', 'int');
        $uid = $this->getRequest('uid', 'string');
        $this->checkParamsEmpty([
            $plan_id,
        ]);
        $result = $this->service->createCoupon($plan_id, $uid, $amount);
        $this->_RESTful($result);
    }


    public function bindAction()
    {
        $couponCode = $this->getRequest('coupon_code', 'string');
        $uid = $this->getRequest('uid', 'string');
        $this->checkParamsEmpty([
            $couponCode,
            $uid
        ]);
        $result = $this->service->bindCoupon($couponCode, $uid);
        $this->_RESTful($result);
    }


    public function getAvailableAmountAction()
    {
        $uid = $this->getRequest('uid', 'string');
        $type = $this->getRequest('type', 'int');
        $this->checkParamsEmpty([
            $uid,
//            $type
        ]);

        $result = $this->service->getAvailableAmount($uid);
//        $this->service->updateUser($coupon_id, $username, $organIds, $roleId,$pingcode);

        $this->_RESTful($result);
    }
    public function expenseAction()
    {
        $uid = $this->getRequest('uid', 'string');
        $amount = $this->getRequest('amount', 'string');
        $type = $this->getRequest('type', 'string');
        $this->checkParamsEmpty([
            $amount,
            $uid,
            $type
        ]);

        $result = $this->service->expense($uid, $amount,$type);
//        $this->service->updateUser($coupon_id, $username, $organIds, $roleId,$pingcode);
        $this->_RESTful($result);
    }

    public function deleteAction()
    {

        $this->_RESTful(true);
    }




}