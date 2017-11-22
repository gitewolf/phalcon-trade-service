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
use Models\Pro\Coupon;
use Models\Pro\CouponPlan;
use Models\Pro\Order;
use Models\Pro\UserCoupon;
use Payment\Common\PayException;
use Phalcon\Http\Client\Exception;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;

class CouponService extends BaseService
{
    /** @var UserCoupon $model */
    protected $model;
    /** @var CouponPlan $planModel */
    protected $planModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new UserCoupon();
        $this->planModel = new CouponPlan();
    }

    /**
     * 添加优惠券计划
     * $username,$type,$organId,$oid,$roleId
     */
    public function createPlan($planArr)
    {
        $this->planModel->init($planArr);
        $this->planModel->status = CouponPlan::STATUS_ENABLE;

        if ($this->planModel->save()) {
            return $this->planModel;
        } else {
            throw new PikException(ErrorCode::COUPON_PLAN_SAVE_FAILED);
        }
    }

    public function updatePlan($planId, $planArr)
    {
        $plan = $this->detailPlan($planId);
        $plan->init($planArr);
        if (isset($planArr['status']) && !empty($planArr['status'])) {
            $plan->status = $planArr['status'];
        }

        if ($plan->save()) {
            return $plan;
        } else {
            throw new PikException(ErrorCode::COUPON_PLAN_SAVE_FAILED);
        }
    }

    /**
     * 分页列表优惠券计划
     * $username,$type,$organId,$oid,$roleId
     */
    public function listPlanPage($currentPage, $pageSize, $name, $type, $status)
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
            $queryParam['conditions'] = $queryParam['conditions'] . 'and type = :type: ';
            $queryParam['bind']['type'] = $type;
        }
        //分页
        $pageInfo = $this->mysqlPageInfo($currentPage, $pageSize, $queryParam, $this->planModel);
        $queryParam['order'] = 'update_time DESC';
        $queryParam['limit'] =
            [
                "offset" => ($currentPage - 1) * $pageSize,
                "number" => $pageSize
            ];
        $goodsList = $this->planModel->find($queryParam)->toArray();


        $result = [
            'page' => $pageInfo,
            'data' => $goodsList
        ];
        return $result;

    }


    /**
     * 分页列表优惠券计划
     * $username,$type,$organId,$oid,$roleId
     */
    public function listPage($currentPage, $pageSize, $name, $type, $status)
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
            $queryParam['conditions'] = $queryParam['conditions'] . 'and type = :type: ';
            $queryParam['bind']['type'] = $type;
        }
        //分页
        $pageInfo = $this->mysqlPageInfo($currentPage, $pageSize, $queryParam, $this->planModel);
        $queryParam['order'] = 'update_time DESC';
        $queryParam['limit'] =
            [
                "offset" => ($currentPage - 1) * $pageSize,
                "number" => $pageSize
            ];
        $goodsList = $this->planModel->find($queryParam)->toArray();


        $result = [
            'page' => $pageInfo,
            'data' => $goodsList
        ];
        return $result;
    }

    /**
     * @purpose 删除优惠券计划
     * @param $userId
     * @return bool
     * @throws PikException
     */
    public function deletePlan($planId)
    {
        $plan = $this->detailPlan($planId);
        $plan->status = CouponPlan::STATUS_UNABLE;
        return $plan->save();
    }

    /**
     * 新增优惠券
     * @param $planId
     * @param string $uid
     * @param int $amount
     * @param bool $inTx
     * @return UserCoupon
     * @throws PikException
     */
    public function createCoupon($planId, $uid = '', $amount = 0, $inTx = false)
    {
        try {
            $plan = $this->detailPlan($planId);
            if (empty($amount)) {
                $amount = $plan->face_amount;
            }
            $this->model->init($plan, $uid);
            $transaction = $this->getTransaction();
            $this->model->setTransaction($transaction);
            $this->model->code = $this->generateCouponCode();
            $this->model->amount = $amount;
            $this->model->balance = $amount;

            if (!$this->model->save()) {
                $transaction->rollback("COUPON SAVE FAILED");
                throw new PikException(ErrorCode::COUPON_PLAN_SAVE_FAILED);
            }
            if (!$inTx) {
                $transaction->commit();
            }
            return $this->model;
        } catch (TxFailed $e) {
            throw new PikException(ErrorCode::COUPON_SAVE_FAILED);
        } catch (PikException $e) {
            throw $e;
        }catch (\Exception $e) {
            throw new PikException(ErrorCode::COUPON_SAVE_FAILED);
        }
    }

    /**
     * 绑定优惠券
     * @param $couponCode
     * @param $uid
     */
    public function bindCoupon($couponCode, $uid)
    {
        $transaction = $this->getTransaction();
        $userCoupon = $this->getByCode($couponCode, true);
        $userCoupon->setTransaction($transaction);
        $plan = $this->detailPlan($userCoupon->plan_id);
        if (!empty($userCoupon->uid)) {
            throw new PikException(ErrorCode::COUPON_HAS_BIND_ALREADY);
        } else if ($userCoupon->status != 1) {
            throw new PikException(ErrorCode::COUPON_HAS_BIND_ALREADY);
        } else if (!empty($userCoupon->limit_time) && $userCoupon->limit_time < time()) {
            throw new PikException(ErrorCode::COUPON_OUT_OF_DATE);
        }
        $userCoupon->uid = $uid;
        $userCoupon->status = 2;
        $userCoupon->bind_time = time();
        $userCoupon->limit_time = $userCoupon->calcLimitTime($plan->valid_type, $plan->valid_period, $plan->end_time);
        if (!$userCoupon->save()) {
            throw new PikException(ErrorCode::COUPON_PLAN_SAVE_FAILED);
        }
        $transaction->commit();

        return $userCoupon;
    }

    /**
 * 扣除金额
 * @param $uid
 * @param $amount
 * @param $type
 * @param bool $inTx
 * @return bool
 * @throws PikException
 */
    public function expense($uid, $amount, $type, $inTx = false)
    {
        $availableAmount = $this->getAvailableAmount($uid);
        if (empty($availableAmount) || !isset($availableAmount[$type]) || $availableAmount[$type] < $amount) {
            throw new PikException(ErrorCode::COUPON_BALANCE_NOT_ENOUGH);
        }
        try {
            $usedCoupon = [];
            $transaction = $this->getTransaction();
            $leftAmount = $amount;
            while ($leftAmount > 0) {
                $coupon = $this->findAvailableCoupon($uid, $type, true);
                $coupon->setTransaction($transaction);
                if (empty($coupon)) {
                    $transaction->rollback("COUPON NOT ENOUGH");
                }
                if ($coupon->balance > $leftAmount){
                    $coupon->balance = $coupon->balance - $leftAmount;
                    $usedAmount = $leftAmount;
                    $leftAmount =0;
                }else{
                    $leftAmount = $leftAmount - $coupon->balance;
                    $usedAmount = $coupon->balance;
                    $coupon->balance = 0;
                    $coupon->status = UserCoupon::STATUS_EXPIRED;
                }
                $usedCoupon[$coupon->code] = $usedAmount;
                if (!$coupon->save()) {
                    $transaction->rollback("COUPON NOT ENOUGH");
                    throw new PikException(ErrorCode::COUPON_PLAN_SAVE_FAILED);
                }
            }
            if (!$inTx) {
                $transaction->commit();
            }
            return $usedCoupon;
        } catch (TxFailed $e) {
            throw new PikException(ErrorCode::COUPON_BALANCE_NOT_ENOUGH);
        }
    }


    /**
     * 获取一张用户可用优惠券
     * @param $uid
     * @param $type
     * @param bool $forUpdate
     * @return UserCoupon
     */
    public function findAvailableCoupon($uid, $type, $forUpdate = false)
    {
        $queryParam['conditions'] = 'uid =:uid: and status = ' . UserCoupon::STATUS_BIND;
        $queryParam['conditions'] .= ' and limit_time > :limit_time:';
        $queryParam['conditions'] .= ' and start_time < :start_time:';
        $queryParam['conditions'] .= ' and type = :type:';
        $queryParam['bind'] = [
            'uid' => $uid,
            'limit_time' => time(),
            'start_time' => time(),
            'type' => $type,
        ];
        $queryParam['order'] = 'id ASC';
        if ($forUpdate) {
            $queryParam['for_update'] = true;
        }
        $coupon = UserCoupon::findFirst($queryParam);
        return $coupon;
    }

    /**
     * 获取用户可用优惠金额
     * @param $uid
     * @return array
     */
    public function getAvailableAmount($uid)
    {

        $queryParam['conditions'] = 'uid =:uid: and status = ' . UserCoupon::STATUS_BIND;
        $queryParam['conditions'] .= ' and limit_time > :limit_time:';
        $queryParam['conditions'] .= ' and start_time < :start_time:';
        $queryParam['bind'] = [
            'uid' => $uid,
            'limit_time' => time(),
            'start_time' => time(),
        ];
//        if(empty($type)){
//            $queryParam['conditions'] .= " and type = :type:";
//            $queryParam['bind']['type'] = $type;
//        }else{
//            $queryParam['group'] = $type;
//        }
        $queryParam["column"] = 'balance';
        $queryParam['group'] = 'type';
        $groupList = UserCoupon::sum($queryParam);
        if (empty($groupList)) {
            return [];
        }
        $availableList = [];
        foreach ($groupList as $group) {
            $availableList[$group['type']] = $group['sumatory'];
        }
        return $availableList;
    }

    /**
     * 获取用户优惠券详情
     * @param $id
     * @param bool $forUpdate
     * @return static
     * @throws PikException
     */
    public function detail($id, $forUpdate = false)
    {
        $queryParam = ['id =:id:'];
        $queryParam['bind'] = ['id' => $id];
        if($forUpdate){
            $queryParam['for_update'] = true;
        }
        $coupon = UserCoupon::findFirst($queryParam);
        if (empty($coupon)) {
            throw new PikException(ErrorCode::COUPON_PLAN_NOT_EXIST);
        }
        return $coupon;
    }

    /**
     * 获取用户优惠券详情
     * @param $couponCode
     * @param $forUpdate
     * @return UserCoupon
     * @throws PikException
     */
    public function getByCode($couponCode, $forUpdate = false)
    {
        $queryParam = ['code =:code:'];
        $queryParam['bind'] = ['code' => $couponCode];
        if ($forUpdate) {
            $queryParam['for_update'] = true;
        }
        $coupon = UserCoupon::findFirst($queryParam);
        if (empty($coupon)) {
            throw new PikException(ErrorCode::COUPON_NOT_EXIST);
        }
        return $coupon;
    }

    /**
     * 获取计划详情
     * @param $planId
     * @return CouponPlan
     * @throws PikException
     */
    public function detailPlan($planId)
    {
        $queryParam = ['id =:id:'];
        $queryParam['bind'] = ['id' => $planId];
        $plan = CouponPlan::findFirst($queryParam);
        if (empty($plan)) {
            throw new PikException(ErrorCode::COUPON_PLAN_NOT_EXIST);
        }
        return $plan;
    }

    /**
     * 生成coupon code
     * @return int
     * @throws PikException
     */
    private function generateCouponCode()
    {
        $time_str = time() - 1000000000;
        $rand = rand(1000000, 9999999);
        $couponCode = $time_str . $rand;
        return strval($couponCode);
    }


    /**
     * 根据订单类型返回可用导出券类型
     * @param $orderType  //类型 1: 充值,2: 导出 3: 购买产品
     * @return int        // 1: 导出券, 2. 代金券
     */
    public function getCouponTypeForOrder($orderType)
    {
        if($orderType == Order::TYPE_EXPORT){
            return CouponPlan::TYPE_EXPORT;
        }else if($orderType == Order::TYPE_BUY){
            return CouponPlan::TYPE_BUY;
        }else{
            return 0;
        }
    }


    /**
     * 退还优惠券
     * @param $uid
     * @param $amount
     * @param $code
     * @param bool $inTx
     * @return bool
     * @throws PikException
     */
    public function restitution($uid, $amount, $code, $inTx = false)
    {
        try {
            $transaction = $this->getTransaction();
            $coupon = $this->getByCode($code, true);
            $coupon->setTransaction($transaction);
            if($coupon->uid != $uid){
                throw new PikException(ErrorCode::COUPON_NOT_EXIST);
            }
            if($coupon->status != UserCoupon::STATUS_BIND){
                $coupon->status = UserCoupon::STATUS_BIND;
            }
            $coupon->balance = $coupon->balance + $amount;
            if($coupon->balance > $coupon->amount){//导出券余额不能超过最大金额
                $coupon->balance = $coupon->amount;
            }
            if (!$coupon->save()) {
                $transaction->rollback("COUPON NOT ENOUGH");
                throw new PikException(ErrorCode::COUPON_PLAN_SAVE_FAILED);
            }
            if (!$inTx) {
                $transaction->commit();
            }
//            return $usedCoupon;
        } catch (PikException $e) {
            throw $e;
        }catch (TxFailed $e) {
            throw new PikException(ErrorCode::COUPON_BALANCE_NOT_ENOUGH);
        }
    }


}