<?php
/**
 * Created by PhpStorm.
 * User: jacky
 * Date: 17-6-17
 * Time: 上午10:47
 */

namespace Services;


use Common\ErrorCode;
use Exceptions\PikException;
use Models\Pro\CouponPlan;
use Models\Pro\Goods;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;

class GoodsService extends BaseService
{

    /** @var Goods $model */
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Goods();
    }

    /**
     * 新增商品
     */
    public function create($goodsArr)
    {
        $this->model->init($goodsArr);
        $this->model->version = 1;
        if ($this->model->save()) {
            return $this->model;
        } else {
            throw new PikException(ErrorCode::GOODS_SAVE_FAILED);
        }
    }

    public function update($goodsId, $goodsArr)
    {
        $queryParam = ['id =:id:'];
        $queryParam['bind'] = ['id' => $goodsId];
        $goods = Goods::findFirst($queryParam);
        $goods->init($goodsArr);
        if ($goods->save()) {
            return $goods;
        } else {
            throw new PikException(ErrorCode::GOODS_SAVE_FAILED);
        }
    }


    public function delete($goodsId)
    {
        $queryParam = ['id =:id:'];
        $queryParam['bind'] = ['id' => $goodsId];
        $goods = Goods::findFirst($queryParam);
        if (empty($goods) || $goods->status == 2) {
            return true;
        }
        $goods->status = 2;
        if ($goods->save()) {
            return $goods;
        } else {
            throw new PikException(ErrorCode::GOODS_SAVE_FAILED);
        }
    }

    /**
     * 分页返回商品信息列表
     * @param $currentPage
     * @param $pageSize
     * @param string $name
     * @param int $status
     * @param int $type
     * @return array
     */
    public function listPage($currentPage, $pageSize, $name = '', $status = 0, $type = 0)
    {
        $queryParam = [];
        $queryParam['conditions'] = ' 1=1 ';
        if (!empty($name)) {
            $queryParam['conditions'] = 'name LIKE :name: ';
            $queryParam['bind']['name'] = '%' . $name . '%';
        }
        if (!empty($status)) {
            $queryParam['conditions'] = $queryParam['conditions'] . 'and status = :status: ';
            $queryParam['bind']['status'] = $status;
        }
        if (!empty($type)) {
            $queryParam['conditions'] = $queryParam['conditions'] . 'and goods_type = :goods_type: ';
            $queryParam['bind']['goods_type'] = $type;
        }
        //分页
        $pageInfo = $this->mysqlPageInfo($currentPage, $pageSize, $queryParam, $this->model);
        $queryParam['order'] = 'update_time DESC';
        $queryParam['limit'] =
            [
                "offset" => ($currentPage - 1) * $pageSize,
                "number" => $pageSize
            ];
        $goodsList = $this->model->find($queryParam)->toArray();


        $result = [
            'page' => $pageInfo,
            'data' => $goodsList
        ];
        return $result;
    }


    /**
     * 返回某类型的所有商品(所有充值信息)
     * @param int $type
     * @return array
     */
    public function listByType($type)
    {
        $queryParam = [];
        $queryParam['conditions'] = 'status = 1 and goods_type = :goods_type: ';
        $queryParam['bind']['goods_type'] = $type;
        $queryParam['order'] = 'update_time DESC';
        $goodsList = $this->model->find($queryParam)->toArray();
        return $goodsList;
    }

    /**
     * @param $goodsId
     * @return Goods
     */
    public function detail($goodsId)
    {
        $queryParam = ['id =:id:'];
        $queryParam['bind'] = ['id' => $goodsId];
        $goods = Goods::findFirst($queryParam);
        return $goods;
    }


    /**
     * @purpose 下架商品
     * @param $userId
     * @return bool
     * @throws PikException
     */
    public function saleOff($goodsId)
    {
        $plan = CouponPlan::findFirst($goodsId);
        $plan->status = CouponPlan::STATUS_UNABLE;
        return $plan->save();
    }


}