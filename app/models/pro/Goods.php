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
 * @property int $id          //商品ID
 * @property int $version          //版本号 每次修改version+1. 并且旧版本会有历史存档
 * @property string $name   //商品名称
 * @property string $product_type   // 产品类型  professional, standard, edu, team
 * @property int $goods_type  // 商品类型 1: 购买产品,2: 消费 3: 充值, 4 : 导出
 * @property int $product_valid_type  //有效期类型
 * @property int $product_valid_period  //有效期
 * @property string $matter_ids  // 素材或素材包. 多个素材时,逗号隔开
 * @property string $coupons  // 代金券计划ID+金额 json串
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
class Goods extends Model
{
    function getSource()
    {
        return "jds_goods";
    }

    public function init($goodsArr)
    {
        $this->name = isset($goodsArr['name']) ? $goodsArr['name'] : '';
        $this->product_type = isset($goodsArr['product_type']) ? $goodsArr['product_type'] : 0;
        $this->goods_type = isset($goodsArr['goods_type']) ? $goodsArr['goods_type'] : 0;
        $this->product_valid_type = isset($goodsArr['product_valid_type']) ? $goodsArr['product_valid_type'] : 0;
        $this->product_valid_period = isset($goodsArr['product_valid_period']) ? $goodsArr['product_valid_period'] : 0;
        $this->matter_ids = isset($goodsArr['matter_ids']) ? $goodsArr['matter_ids'] : '';
        $this->coupons = isset($goodsArr['coupons']) ? $goodsArr['coupons'] : '';
        $this->price = isset($goodsArr['price']) ? $goodsArr['price'] : 0;
        $this->base_price = isset($goodsArr['base_price']) ? $goodsArr['base_price'] : 0;
        $this->recharge_coin = isset($goodsArr['recharge_coin']) ? $goodsArr['recharge_coin'] : 0;
        $this->bonus_coin = isset($goodsArr['bonus_coin']) ? $goodsArr['bonus_coin'] : 0;
        $this->img_url = isset($goodsArr['img_url']) ? $goodsArr['img_url'] : '';
        $this->desc = isset($goodsArr['desc']) ? $goodsArr['desc'] : '';
        $this->additional_services = isset($goodsArr['additional_services']) ? $goodsArr['additional_services'] : '';

    }


    public function beforeUpdate()
    {
        $this->update_time = time();
    }
    public function beforeCreate()
    {
        $this->create_time = time();
        $this->update_time = time();
    }
}