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
use Services\MessageService;
use Services\UserServices;

class MessageController extends ControllerBase
{
    /** @var  MessageService $service */
    private $service;

    public function initialize()
    {
        parent::initialize();
        $this->service = $this->messageService;

    }


    public function listPageAction()
    {
        // 当前页
        $currentPage = $this->getRequest('current_page', 'int', 1);
        // 每页显示条数
        $pageSize = $this->getRequest('page_size', 'int', 20);
        $uid = $this->request->get('uid', 'string', '');
        $type = $this->request->get('type', 'int', 0);
        $result = $this->service->listPage($currentPage, $pageSize,$uid, $type);
        $this->_RESTful($result);
    }

    public function createAction()
    {
        $uid = $this->getRequest('uid', 'string');
        $type= $this->getRequest('type', 'int');
        $mass_type = $this->getRequest('mass_type', 'int');
        $title = $this->getRequest('title', 'string');
        $content = $this->getRequest('content', 'string');
        $this->checkParamsEmpty([
            $type,
            $title,
            $content,
        ]);
        $result = $this->service->create($type, $title, $content, $uid, $mass_type);
        $this->_RESTful($result);
    }


    public function readAction()
    {
        $id = $this->getRequest('id', 'int');
        $uid = $this->getRequest('uid', 'string');
        $this->checkParamsEmpty([
            $id,
            $uid,
        ]);
        $result = $this->service->read($id, $uid);
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