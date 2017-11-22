<?php
/**
 * Created by PhpStorm.
 * User: jacky
 * Date: 17-8-15
 * Time: 下午4:17
 */

namespace Models\Pro;

use Phalcon\Mvc\Model;

/**
 * 资金明细表
 * @property int $id          //资金ID
 * @property string $uid      //用户OID
 * @property int $type      // 账户类型
 * @property int $biz_type      // 业务类型 	1: 充值, 2:消费, 3:收入, 4:退款
 * @property int $fund_type      // 资金类型 	1: 皮币, 2:代金券
 * @property string $flow      // 资金流向 	in 或 out
 * @property double $amonut  //实付金额
 * @property double $coin_balance //皮币余额
 * @property int $order_id     // 关联订单ID
 * @property string $remark  // 备注
 * @property int $update_time
 * @property int $create_time
 */
class FundsDetail extends Model
{
    function getSource()
    {
            return "jds_funds_detail";
    }


    protected function beforeUpdate()
    {
        $this->update_time = time();
    }
    protected function beforeCreate()
    {
        $this->create_time = time();
        $this->update_time = time();
    }
}