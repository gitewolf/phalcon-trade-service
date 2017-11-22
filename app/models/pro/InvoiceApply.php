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
 * 寄送发票申请表
 * @property int $id          //主键
 * @property string $uid          //用户Id
 * @property double $amount          //发票金额
 * @property int $scope_time          //开发票截止时间
 * @property int $type  //1: 普通发票, 2:增值发票
 * @property int $status  // 1: 申请, 2 处理中, 3 已发出, 4 结束 -1 取消 -2 拒绝
 * @property string $order_id  //相关订单 逗号隔开
 * @property string $address         //地址
 * @property string $post_code         //邮编
 * @property string $contact_name         //收件人
 * @property string $contact_mobile         //收件人手机号
 * @property string $reson         //拒绝理由
 * @property string $histroy         //记录每步的操作记录
 * @property string $tracking_number         //快递单号
 * @property string $operator   // 操作人
 * @property int $update_time
 * @property int $create_time
 */
class InvoiceApply extends Model
{
    function getSource()
    {
        return "jds_invoice_apply";
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