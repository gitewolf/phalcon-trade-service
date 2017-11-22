<?php
/**
 * Created by PhpStorm.
 * User: zhq
 * Date: 17-6-17
 * Time: 上午10:47
 */

namespace Services;


use Common\ErrorCode;
use Exceptions\PikException;
use Models\Pro\FundsAccount;
use Models\Pro\FundsDetail;
use Models\Pro\Message;
use Models\Pro\OrderDetail;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;
use Models\Pro\Order;

class FundsService extends BaseService
{

    /** @var FundsAccount $model */
    protected $model;
    /** @var FundsDetail $model */
    protected $detailModel;

    public function __construct()
    {
        parent::__construct();
        $this->model = new FundsAccount();
        $this->detailModel = new FundsDetail();
    }

    /**
     * 保存资金账户信息
     * @param $uid
     */
    public function saveFoundsInfo($uid, $fundsArr){
        $userFunds = $this->getUserFundsInfo($uid);
        if(empty($userFunds)){
            $userFunds = new FundsAccount();
            $userFunds->uid = $uid;
        }
        $userFunds->init($fundsArr);
        return $userFunds->save();
    }

    /**
     *
     * @param $uid
     * @return FundsAccount
     */
    public function getUserFundsInfo($uid){
        $queryParam = ['uid =:uid:'];
        $queryParam['bind'] = ['uid' => $uid];
        return $this->model->findFirst($queryParam);
    }

    /**
     * 个人的资金变更
     * @param $uid
     * @param $bizType  // 业务类型  1: 充值, 2:消费, 3:收入, 4:退款
     * @param $coin
     * @param string $order_id
     * @param string $remark
     * @param bool $inTx  //是否属于上一级事务
     * @return mixed
     * @throws PikException
     */
    public function addUserCoin($uid, $bizType, $coin, $order_id='',$remark='', $inTx = false)
    {
        $userFundsLockKey = 'USER_FUNDS_LOCK_'.$uid;
        try {
            $userFunds = $this->getUserFundsInfo($uid);
            if(empty($userFunds)){
                $this->saveFoundsInfo($uid,[]);
                $userFunds = $this->getUserFundsInfo($uid);
            }
            $transaction = $this->getTransaction();
            $userFunds->setTransaction($transaction);

            $fundsDetail  = new FundsDetail();
            $fundsDetail->setTransaction($transaction);
            $fundsDetail->uid = $uid;
            $fundsDetail->biz_type = $bizType;
            $fundsDetail->fund_type = 1;
            $fundsDetail->order_id = $order_id;
            $fundsDetail->remark = $remark;
            if($bizType == 1){//充值
                $fundsDetail->coin = $coin;
                $fundsDetail->uid = $uid;
                $fundsDetail->coin_balance = $userFunds->coin + $coin;
                $fundsDetail->flow = 'in';
                $userFunds->coin = $userFunds->coin + $coin;
                $userFunds->in_amount = $userFunds->in_amount + $coin;
            }else if ($bizType == 2){ //消费
                $fundsDetail->coin = $coin;
                $fundsDetail->uid = $uid;
                $fundsDetail->coin_balance = $userFunds->coin - $coin;
                $fundsDetail->flow = 'out';
                $userFunds->coin = $userFunds->coin - $coin;
                $userFunds->out_amount = $userFunds->out_amount + $coin;
            }else if ($bizType == 3){ //收入
                $fundsDetail->coin = $coin;
                $fundsDetail->uid = $uid;
                $fundsDetail->coin_balance = $userFunds->coin + $coin;
                $fundsDetail->flow = 'in';
                $userFunds->coin = $userFunds->coin + $coin;
                $userFunds->in_amount = $userFunds->in_amount + $coin;
            }else if ($bizType == 4){ //退款
                $fundsDetail->coin = $coin;
                $fundsDetail->uid = $uid;
                $fundsDetail->coin_balance = $userFunds->coin + $coin;
                $fundsDetail->flow = 'in';
                $userFunds->coin = $userFunds->coin + $coin;
                $userFunds->in_amount = $userFunds->in_amount + $coin;
            }
            if ($fundsDetail->save() == false || $userFunds->save() == false) {
                $transaction->rollback("Cannot save order");
            }
            if(!$inTx){
                $transaction->commit();
            }
            return true;
        } catch (TxFailed $e) {
            throw new PikException(ErrorCode::FUNDS_SAVE_FAILED);
        }
    }



    /**
     * 分页返回资金列表
     * @param $currentPage
     * @param $pageSize
     * @return array
     */
    public function listPage($currentPage, $pageSize)
    {
        $queryParam = [];
        $queryParam['conditions'] = ' 1 = 1 ';
        //分页
        $pageInfo = $this->mysqlPageInfo($currentPage, $pageSize, $queryParam, $this->model);
        $queryParam['columns'] =['id','uid','type','coin','contact_name','contact_mobile','address','post_code','remark','create_time','update_time'];
        $queryParam['order'] = 'update_time DESC';
        $queryParam['limit'] =
            [
                "offset" => ($currentPage - 1) * $pageSize,
                "number" => $pageSize
            ];
        $fundsList = $this->model->find($queryParam)->toArray();
        $result = [
            'page' => $pageInfo,
            'data' => $fundsList
        ];
        return $result;
    }


    /**
     * 分页返回个人资金明细列表
     * @param $currentPage
     * @param $pageSize
     * @return array
     */
    public function listDetailPage($currentPage, $pageSize)
    {
        $queryParam = [];
        $queryParam['conditions'] = ' 1 = 1 ';
        //分页
        $pageInfo = $this->mysqlPageInfo($currentPage, $pageSize, $queryParam, $this->model);
        $queryParam['columns'] =['id','uid','type','coin','contact_name','contact_mobile','address','post_code','remark','create_time','update_time'];
        $queryParam['order'] = 'update_time DESC';
        $queryParam['limit'] =
            [
                "offset" => ($currentPage - 1) * $pageSize,
                "number" => $pageSize
            ];
        $fundsList = $this->model->find($queryParam)->toArray();
        $result = [
            'page' => $pageInfo,
            'data' => $fundsList
        ];
        return $result;
    }

}