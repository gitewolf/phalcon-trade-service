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
 * 商品表
 * @property int $id          //商品ID id+version 联合主键
 * @property int $version          //版本号 每次修改version+1. 并且旧版本会有历史存档
 * @property string $name   //商品名称
 * @property string $product_type   // 产品类型 professional, standard, edu, team
 * @property int $goods_type  //商品类型 1: 购买产品,2: 消费 3: 充值
 * @property int $product_valid_type  //有效期类型
 * @property int $product_valid_period  //有效期
 * @property string $matter_ids  // 素材或素材包. 多个素材时,逗号隔开
 * @property string $coupons  // 代金券计划ID+金额
 * @property int $status   // 1: 上架, 2 下架(不再销售,但仍可使用)
 * @property double $price   //售价
 * @property double $base_price   //原价
 * @property int $recharge_coin   // 充值皮币
 * @property int $bonus_coin   //额外赠送皮币
 * @property string $img_url  // 商品图片
 * @property string $desc     //商品说明
 * @property string $additional_services  // 额外服务
 * @property int $update_time
 * @property int $create_time
 */
class Goods_History extends Model
{
    function getSource()
    {
        return "jds_goods_history";
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