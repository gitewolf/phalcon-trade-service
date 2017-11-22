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
 * 代金券计划
 *
 * @property int $id          //计划ID
 * @property string $name   //代金券名称
 * @property int $status   // 1: 上架, 2 下架(不再销售,但仍可使用), -1. 删除
 * @property int $type   // 1: 导出券, 2. 代金券
 * @property int $start_time   //产品生效时间
 * @property int $end_time   //产品终止时间
 * @property int $face_amount  //面额
 * @property int $valid_type  //有效期类型 1: 天 2:月 3:年
 * @property int $valid_period  //有效期
 * @property int $max_amount  //发行总量 0 代表不限
 * @property int $release_amount  //已发行总量
 * @property string $additional_services  // 额外服务
 * @property string $img_url  // 优惠券图片
 * @property string $desc     //优惠券说明
 * @property int $update_time
 * @property int $create_time
 */

class CouponPlan extends Model
{

    const STATUS_ENABLE = 1;
    const STATUS_UNABLE = 2;

    const TYPE_EXPORT = 1;
    const TYPE_BUY = 2;
    const TYPE_PRODUCTION = 3;

    function getSource()
    {
        return "jds_coupon_plan";
    }


    public function init($planArr)
    {
        $this->name = isset($planArr['name']) ? $planArr['name'] : '';
        $this->type  = isset($planArr['type ']) ? $planArr['type '] : 1;
        $this->status  = isset($planArr['status ']) ? $planArr['status '] : 1;
        $this->face_amount = isset($planArr['face_amount']) ? $planArr['face_amount'] : 0;
        $this->valid_type = isset($planArr['valid_type']) ? $planArr['valid_type'] : 0;
        $this->valid_period = isset($planArr['valid_period']) ? $planArr['valid_period'] : 0;
        $this->start_time  = isset($planArr['start_time']) ? $planArr['start_time'] : 0;
        $this->end_time  = isset($planArr['end_time']) ? $planArr['end_time'] : 0;
        $this->max_amount = isset($planArr['max_amount']) ? $planArr['max_amount'] : 0;
        $this->additional_services = isset($planArr['additional_services']) ? $planArr['additional_services'] : '';
        if( isset($planArr['img_url']) && !empty($planArr['img_url'])){
            $this->img_url =  $planArr['img_url'];
        }
        $this->desc = isset($planArr['desc']) ? $planArr['desc'] : '';

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