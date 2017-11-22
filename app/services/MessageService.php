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
use Models\Pro\Message;
use Models\Pro\MessageStatus;

class MessageService extends BaseService
{

    /** @var Message $model */
    protected $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Message();
    }

    /**
     * 新增消息
     */
    public function create($type, $title, $content, $uid = '', $mass_type = 0)
    {
        $msg = new Message();
        $msg->type = $type;
        $msg->title = $title;
        $msg->content = $content;
        $msg->uid = $uid;
        $msg->mass_type = $mass_type;

        if ($type == Message::TYPE_SYSTEM && empty($uid)){
            throw new PikException (ErrorCode::PARAM_ERROR);
        }
        if ($msg->save()) {
            return $msg;
        } else {
            throw new PikException(ErrorCode::MESSAGE_SAVE_FAILED);
        }
    }

    public function read($id, $uid)
    {
        $queryParam = ['id =:id:'];
        $queryParam['bind'] = ['id' => $id];
        $msg = Message::findFirst($queryParam);
        if($msg->type == Message::TYPE_SYSTEM){
            $msg->is_read = 1;
            $msg->save();
        }else{
            $queryParam = ['msg_id =:msg_id: and uid = :uid:'];
            $queryParam['bind'] = ['msg_id' => $id, 'uid' =>$uid];
            $msgStatus = MessageStatus::findFirst($queryParam);
            if(empty($msgStatus)){
                $msgStatus = new MessageStatus();
                $msgStatus->msg_id = $id;
                $msgStatus->uid = $uid;
                $msgStatus->save();
            }
        }
        return true;
    }


    //删除消息
    public function delete($msgId)
    {
        $queryParam = ['id =:id:'];
        $queryParam['bind'] = ['id' => $msgId];
        $msg = Message::findFirst($queryParam);
        if (empty($msg) || $msg->is_delete == 1) {
            return true;
        }
        $msg->is_delete = 1;
        return $msg->save();
    }

    /**
     * 分页返回消息列表
     * @param $currentPage
     * @param $pageSize
     * @param $uid
     * @param int $type
     * @return array
     */
    public function listPage($currentPage, $pageSize, $uid, $type = 0)
    {
        $queryParam = [];
        $queryParam['conditions'] = ' is_delete = 0 ';
        if ($type == Message::TYPE_MASS) {
            $queryParam['conditions'] = $queryParam['conditions'] . 'and type = :type: ';
            $queryParam['bind']['type'] = $type;
        }else if($type == Message::TYPE_SYSTEM){
            $queryParam['conditions'] = $queryParam['conditions'] . 'and type = :type: and uid = :uid: ';
            $queryParam['bind']['type'] = $type;
            $queryParam['bind']['uid'] = $uid;
        }else{
            $queryParam['conditions'] = $queryParam['conditions'] . 'and (type = 1 and uid = :uid:  or type = 2 )';
            $queryParam['bind']['uid'] = $uid;
        }
        //分页
        $pageInfo = $this->mysqlPageInfo($currentPage, $pageSize, $queryParam, $this->model);
        $queryParam['order'] = 'create_time DESC';
        $queryParam['limit'] =
            [
                "offset" => ($currentPage - 1) * $pageSize,
                "number" => $pageSize
            ];
        $msgList = $this->model->find($queryParam)->toArray();
        //todo 还需要判断公告消息是否已读
        $result = [
            'page' => $pageInfo,
            'data' => $msgList
        ];
        return $result;
    }


}