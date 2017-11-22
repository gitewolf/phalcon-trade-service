<?php

class OrderTask extends \Phalcon\CLI\Task
{
    /** @var  \Services\ResourceService $resourceService */
    private $orderService;

    public function mainAction()
    {
        echo "\nThis is the ExportTask and the default action \n";
    }


    /**
     * 检查是否有过期的订单需要取消订单
     * @param array $params
     */
    public function checkOvertimeOrderAction(array $params = []){
        //类别不能为空
        $this->orderService = $this->getDI()->getShared('orderService');
        $this->orderService->checkOvertimeOrders();
    }


}
