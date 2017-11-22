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
 * 用户资金表
 * @property int $id          //资金ID
 * @property string $uid      //用户OID
 * @property int $type      // 账户类型
 * @property int $pay_passport  //支付密码md5存放
 * @property double $coin  //皮币余额
 * @property double $in_amount  //进账总额
 * @property double $out_amount  //出账总额
 * @property double $freeze_coin  //冻结金额
 * @property string $contact_name  // 联系人
 * @property string $contact_mobile  // 联系电话
 * @property string $address  // 联系地址
 * @property string $post_code  // 邮编
 * @property string $remark  // 备注
 * @property int $update_time
 * @property int $create_time
 */
class FundsAccount extends Model
{
    function getSource()
    {
        return "jds_funds_account";
    }

    public function init($fundsArr)
    {
        $this->type = isset($fundsArr['type']) ? $fundsArr['type'] : 1;
        if(isset($fundsArr['pay_passport']) && !empty($fundsArr['pay_passport']) ){
            $this->pay_passport = $fundsArr['pay_passport'];
        }
        $this->contact_name = isset($fundsArr['contact_name']) ? $fundsArr['contact_name'] : '';
        $this->contact_mobile = isset($fundsArr['contact_mobile']) ? $fundsArr['contact_mobile'] : '';
        $this->post_code = isset($fundsArr['post_code']) ? $fundsArr['post_code'] : '';
        $this->address = isset($fundsArr['address']) ? $fundsArr['address'] : '';
        $this->remark = isset($fundsArr['remark']) ? $fundsArr['remark'] : '';

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