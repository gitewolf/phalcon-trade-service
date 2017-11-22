<?php
/**
 * Created by PhpStorm.
 * User: jacky
 * Date: 17-6-17
 * Time: 下午4:51
 */

namespace Controllers;



use Services\CouponService;

class CouponplanController extends ControllerBase
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
        $name = $this->request->get('name', 'string', '');
        $type = $this->request->get('type', 'string', '');
        $status = $this->request->get('status','int',0);
        $result = $this->service->listPlanPage($currentPage, $pageSize, $name, $type, $status);
        $this->_RESTful($result);
    }

    public function createAction()
    {
        $planArr = [];
        $planArr['name'] = $this->getRequest('name', 'string');
        $planArr['face_amount'] = $this->getRequest('face_amount', 'int');
        $planArr['max_amount'] = $this->getRequest('max_amount', 'int');
        $planArr['type'] = $this->getRequest('type', 'int');
        $planArr['valid_type'] = $this->getRequest('valid_type', 'int');
        $planArr['valid_period'] = $this->getRequest('valid_period', 'int');
        $planArr['start_time'] = $this->getRequest('start_time', 'int');
        $planArr['end_time'] = $this->getRequest('end_time', 'int');
        $planArr['img_url'] = $this->getRequest('img_url', 'string');
        $planArr['desc'] = $this->getRequest('desc', 'string');
        $planArr['additional_services'] = $this->getRequest('additional_services', 'string');
        $this->checkParamsEmpty([
            $planArr['name'],
            $planArr['face_amount'],
            $planArr['type'],
            $planArr['valid_type'],
        ]);
        $result = $this->service->createPlan($planArr);
        $this->_RESTful($result);
    }



    public function updateAction()
    {
        $id = $this->getRequest('id', 'string', '');
        $planArr = [];
        $planArr['name'] = $this->getRequest('name', 'string');
        $planArr['face_amount'] = $this->getRequest('face_amount', 'int');
        $planArr['max_amount'] = $this->getRequest('max_amount', 'int');
        $planArr['type'] = $this->getRequest('type', 'int');
        $planArr['valid_type'] = $this->getRequest('valid_type', 'int');
        $planArr['valid_period'] = $this->getRequest('valid_period', 'int');
        $planArr['start_time'] = $this->getRequest('start_time', 'int');
        $planArr['end_time'] = $this->getRequest('end_time', 'int');
        $planArr['img_url'] = $this->getRequest('img_url', 'string');
        $planArr['desc'] = $this->getRequest('desc', 'string');
        $planArr['status'] = $this->getRequest('status', 'int');
        $planArr['additional_services'] = $this->getRequest('additional_services', 'string');
        $this->checkParamsEmpty([
            $id,
            $planArr['name'],
            $planArr['face_amount'],
            $planArr['type'],
            $planArr['valid_type'],
        ]);
        $result = $this->service->updatePlan($id, $planArr);

        $this->_RESTful($result);
    }

    public function deleteAction()
    {

        $this->_RESTful(true);
    }




}