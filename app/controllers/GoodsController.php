<?php
/**
 * Created by PhpStorm.
 * User: jacky
 * Date: 17-9-17
 * Time: 下午4:51
 */

namespace Controllers;

use Common\ErrorCode;
use Models\Pro\MpUser;
use Services\GoodsService;
use Services\UserServices;

class GoodsController extends ControllerBase
{
    /** @var  GoodsService $service */
    private $service;

    public function initialize()
    {
        parent::initialize();
        $this->service = $this->goodsService;

    }


    public function listPageAction()
    {
        // 当前页
        $currentPage = $this->getRequest('current_page', 'int', 1);
        // 每页显示条数
        $pageSize = $this->getRequest('page_size', 'int', 20);
        $name = $this->request->get('name', 'string', '');
        $status = $this->request->get('status', 'int', 0);
        $type = $this->request->get('goods_type', 'int', 0);
        $result = $this->service->listPage($currentPage, $pageSize, $name, $status, $type);
        $this->_RESTful($result);
    }

    public function listByTypeAction()
    {
        $type = $this->request->get('goods_type', 'int', 0);
        $result = $this->service->listByType( $type);
        $this->_RESTful($result);
    }

    public function createAction()
    {
        $goodsArr = [];
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
            $goodsArr['name'],
            $goodsArr['product_type'],
            $goodsArr['goods_type'],
            $goodsArr['base_price'],
            $goodsArr['price']
        ]);
        $result = $this->service->create($goodsArr);
        $this->_RESTful($result);
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

    public function deleteAction()
    {
        $id = $this->getRequest('id', 'int');
        $this->checkParamsEmpty($id);
        $result = $this->service->delete($id);
        $this->_RESTful($result);
    }


}