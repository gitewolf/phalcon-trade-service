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
use Models\Pro\MpOrganization;
use Models\Pro\MpUser;
use Models\Pro\MpUserOrg;
use Phalcon\Mvc\Model\Transaction\Failed as TxFailed;

class UserServices extends BaseService
{

    /**
     * 添加用户
     * $username,$type,$organId,$oid,$roleId
     */
    public function createUser($nickname, $organIds = array(), $oid, $gid, $roleId, $companyId, $account, $remarks,$pingcode=null)
    {
        try {
            $transaction = $this->getTransaction();
            $user = new MpUser();
            $user->setTransaction($transaction);

            $user->name = $nickname;
            $user->company_id = $companyId;
            $user->oid = $oid;
            $user->gid = $gid;
            $user->role_id = $roleId;
            $user->account = $account;
            $user->remarks = $remarks;
            $user->create_time = time();
            $user->update_time = time();
            $user->pingcode = $pingcode;
            if ($user->save() == false) {
                $transaction->rollback("Cannot save user");
                return false;
            }
            $id = $user->getId();

            foreach ($organIds as $org) {
                if (!$this->addUserOrg($id, $org)) {
                    $transaction->rollback("Cannot save userOrg");
                    break;
                }
            }
            $transaction->commit();
            return $user;
        } catch (TxFailed $e) {
            throw new PikException (ErrorCode::CREATE_FAILED);
        }

    }

    public function getProfileByAccount($username)
    {
        $profile = '';
        $email = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        if (preg_match($email, $username)) {
            try {
                $user = $this->getUserinfoByEmail($username);
                $profile = $user[$username];
            } catch (\Exception $e) {
                throw new PikException (ErrorCode::USERNAME_NOT_FOUND);
            }
        } elseif (preg_match("/^1[34578]{1}\d{9}$/", $username)) {
            try {
                $user = $this->getUserinfoByMobile('0086' . $username);
                $profile = $user['0086' . $username];
            } catch (\Exception $e) {
                throw new PikException (ErrorCode::USERNAME_NOT_FOUND);
            }
        } else {
            throw new PikException (ErrorCode::ILLEGAL_USER_NAME);
        }
        return $profile;
    }

    public function checkPikId($oid)
    {
        $params['conditions'] = 'oid = :oid: AND is_delete='.MpUser::STATUS_ENABLE;
        $params['bind']['oid'] = $oid;
        $myUser = MpUser::find($params);
        $myUser = $myUser->toArray();
        if (!empty($myUser)) {
            throw new PikException (ErrorCode::TEAM_MEMBER_ALREADY_EXIST);
        }
    }

    public function updateUser($uid, $username, $organIds = array(), $roleId,$pingcode='')
    {
        try {
            $transaction = $this->getTransaction();
            $user = MpUser::findFirst($uid);
            $user->setTransaction($transaction);
            if (!empty($organIds)) {
                if (!$this->delUserOrg($uid)) {
                    $transaction->rollback("Cannot update user");
                    throw new PikException (ErrorCode::UPDATE_FAILED);
                }
                foreach ($organIds as $org) {
                    if (!$this->addUserOrg($uid, $org)) {
                        $transaction->rollback("Cannot update user");
                        break;
                    }
                }

            }
            if(0 < strlen($pingcode))
            {
                $clientInfo = array(
                    'platform' => 'android',
                    'id' => 0,
                    'version' => '0',
                    'channel' => 'com.jackydeng.com',
                    'deviceId' => '0',
                    'deviceType' => 'mp',
                    'systemVersion' => 'JDS_MP_BG',
                );

                $tokenId = $this->getDi()->get('sdkPassport')->tokenId($clientInfo);
                $pingcodeParams = array(
                    'pingcode'=>$pingcode,
                    'gid'=>$user->gid,
                    'old_pingcode'=>$user->pingcode,
                    "tokenId" => $tokenId['tokenId']
                );
                $this->getDi()->getShared('sdkPassport')->qrInfoUpdate($pingcodeParams);
                $user->pingcode = $pingcode;
            }
            $user->name = $username;
            $user->role_id = $roleId;
            if ($user->save() == false) {
                $transaction->rollback("Cannot update user");
                throw new PikException (ErrorCode::UPDATE_FAILED);
            };
            $transaction->commit();

        } catch (TxFailed $e) {
            throw new PikException (ErrorCode::UPDATE_FAILED);

        }
        return $user;
    }

    /**
     * @purpose 删除用户
     * @param $userId
     * @return bool
     * @throws PikException
     */
    public function deleteUser($userId)
    {
        //$transaction = $this->getTransaction();
        $user = MpUser::findFirst($userId);
//        if (!$this->delUserOrg($userId)) {
//            $transaction->rollback("Cannot delete user");
//            throw new PikException (ErrorCode::DELETE_FAILED);
//
//        }
        if ($user->is_delete == MpUser::STATUS_UNABLE) {
            //$transaction->rollback("Cannot delete user");
            throw new PikException (ErrorCode::DELETE_FAILED);

        }
        //$transaction->commit();
        $user->is_delete = MpUser::STATUS_UNABLE;
        return $user->save();
    }


    /**
     * @purpose 删除组织用户结构关系
     * @param $userId
     * @return bool
     */

    public function delUserOrg($userId)
    {

        $params['conditions'] = ' user_id = :user_id:';
        $params['bind']['user_id'] = $userId;

        $userOrgs = $mpUserOrg = MpUserOrg::find($params);
        $isDelete = true;
        foreach ($userOrgs as $org) {
            if ($org->delete() == false) {
                $isDelete = false;
                break;
            }
        }
        return $isDelete;
    }

    public function originUserOrg($userId)
    {
        $params['conditions'] = ' user_id = :user_id:';
        $params['bind']['user_id'] = $userId;

        $userOrg = MpUserOrg::findFirst($params);
        if(!$userOrg)
        {
            return false;
        }
        $org = MpOrganization::findFirstById($userOrg->org_id);
        if(!$org)
        {
            return false;
        }
        if(!preg_match('/(\d+),$/',$org->parent_ids,$match))
        {
            return false;
        }
        $userOrg->org_id = $match[1];
        return $userOrg->save();
    }

    public function userListCount($orgId, $name, $type, $companyId, $status=0,$modelsManager)
    {
        $userList = $modelsManager->createBuilder()
            ->columns("count(1) as num")
            ->addfrom('Models\Pro\MpUser', 'user')
            ->leftjoin('Models\Pro\MpUserOrg', 'org.user_id = user.id', 'org')
            ->leftjoin('Models\Pro\MpRole', 'role.id = user.role_id', 'role');

        if ($type == 0) {
            $userList->where(" user.role_id = 0 AND user.is_delete = ".$status);

        } else {
            $userList->where(" user.role_id <> 0 AND user.is_delete = ".$status);

        };
        if (!empty($name)) {
            $userList->andwhere('name LIKE :name:', array('name' => '%' . $name . '%'));

        }
        if (!empty($orgId)) {
            $userList->andwhere('org.org_id= :org_id:', array('org_id' => $orgId));
        }
        if (!empty($companyId)) {
            $userList->andwhere('user.company_id= :companyId:', array('companyId' => $companyId));

        }

        $userList->orderBy('user.create_time DESC');

        $userList = $userList->getQuery()->execute();
        return $userList->toArray()[0]['num'];

    }

    /**
     * 分页返回用户列表
     * @return array
     */
    public function listPage($currentPage, $pageSize, $orgId, $name, $type, $companyId,$isDelete=0,$modelsManager)
    {
        $userList = $modelsManager->createBuilder()
            ->columns("org.org_id,user.id , user.name ,user.gid,user.pingcode,user.remarks,user.account,user.company_id,user.oid,role.id as role_id,role.name as role_name,role.type as admin_type")
            ->addfrom('Models\Pro\MpUser', 'user')
            ->leftjoin('Models\Pro\MpUserOrg', 'org.user_id = user.id', 'org')
            ->leftjoin('Models\Pro\MpRole', 'role.id = user.role_id', 'role');

        if ($type == 0) {
            $userList->where(" user.role_id = 0 AND user.is_delete = ".$isDelete);

        }else {
            $userList->where(" user.role_id <> 0 AND user.is_delete = ".$isDelete);

        };
        if (!empty($name)) {
            $userList->andwhere('user.name LIKE :name:', array('name' => '%' . $name . '%'));

        }
        if (!empty($orgId)) {
            $userList->andwhere('org.org_id= :org_id:', array('org_id' => $orgId));
        }
        if (!empty($companyId)) {
            $userList->andwhere('user.company_id= :companyId:', array('companyId' => $companyId));

        }

        $userList->groupBy('user.id ');

        $userList->orderBy('user.create_time DESC');
        $userList->limit($pageSize, ($currentPage - 1) * $pageSize);

        $userList = $userList->getQuery()->execute();

        $total = $this->userListCount($orgId, $name, $type, $companyId, $isDelete,$modelsManager);
        $pageInfo = $this->generatePageInfo($currentPage, $pageSize, $total);

        $result = [
            'page' => $pageInfo,
            'data' => $userList->toArray()
        ];


        return $result;
    }


    public function addUserOrg($id, $org)
    {
        $userOrg = new MpUserOrg();
        $userOrg->user_id = $id;
        $userOrg->org_id = $org;
        if ($userOrg->save() == false) {
            return false;
        }
        return true;
    }

    /**
     * @purpose 通过用户id 查看详情
     * @param $oid
     * @return mixed
     */
    public function getUserProfleByGid($gid)
    {
        $user = new MpUser();
        return $user::findFirstByGid($gid);
    }

    /**
     * @purpose 通过状态正常id 查看详情
     * @param $oid
     * @return mixed
     */
    public function getValidUserProfleByGid($gid)
    {
        $user = MpUser::findFirst(array(
            'conditions'=>'gid='.$gid.' AND is_delete='.MpUser::STATUS_ENABLE
        ));
        return $user;
    }


    /**
     * @param $userId
     * @return array
     */
    public function getUserProfle($userId)
    {
        return MpUser::findFirst($userId);
    }

    /**
     * 根据Oid 获取用户信息
     * @param $oid
     * @return array
     */
    public function getUserByOid($oid)
    {
        $params['conditions'] = ' oid = :oid: ';
        $params['bind']['oid'] = $oid;
        return MpUser::findFirst($params);
    }

    /**
     * @purpose 获取用户组id
     * @param $uid
     * @return array
     */
    public function getUserOrgs($uid)
    {
        $params['conditions'] = ' user_id = :uid: ';
        $params['bind']['uid'] = $uid;
        $userOrgs = MpUserOrg::find($params);
        $orgList = [];
        foreach ($userOrgs as $user) {
            $orgList[] = $user->org_id;
        }
        return $orgList;
    }



    public function findOrgDataByOids(Array $oids, $currentPage, $pageSize)
    {
        if (empty($oids))
        {
            return array();
        }
        $queryParam = array(
            'has_course = 1 AND id IN ({id:array})',
            'bind' => [
                'id' => $oids
            ]);
        $result = $this->findOrganizationData($queryParam, $currentPage, $pageSize);
        return $result;
    }

    public function findOrgChildDataByOids(Array $oids, $currentPage, $pageSize)
    {
        if (!isset($oids)) {
            return array();
        }
        $str = 'has_course = 1 AND ( id IN ({id:array})';
        foreach ($oids as $oid) {
            $str .= " OR  parent_ids LIKE '%,{$oid},%' ";
        }
        $str .= ')';
        $queryParam = array(
            'conditions' => $str,
            'bind' => [
                'id' => $oids
            ]
        );
        $result = $this->findOrganizationData($queryParam, $currentPage, $pageSize);
        return $result;

    }

    private function findOrganizationData($queryParam, $currentPage, $pageSize)
    {
        $result = array(
            "has_more" => false,
            "current_page" => 1,
            "data" => array(),
            'count'=>0
        );
        $count = MpOrganization::count($queryParam);
        $pageInfo = $this->generatePageInfo($currentPage, $pageSize, $count);
        $result['count'] = $count;
        $result['current_page'] = $currentPage;
        if ($pageInfo['currentPage'] < $pageInfo['totalPage']) {
            $result['has_more'] = true;
        }

        if (0 < $count) {
            $queryParam['order'] = 'id ASC';
            $queryParam['limit'] =
                [
                    "offset" => ($currentPage - 1) * $pageSize,
                    "number" => $pageSize
                ];
            $queryParam['columns'] = array('id as org_id', 'name');
            $data = MpOrganization::find($queryParam)->toArray();
            $model = $this->getDi()->getService('modelsManager')->resolve();
            if (isset($data[0])) {
                array_walk($data, function (&$value) use ($model) {
                    $memberCount = $model->createBuilder()
                        ->columns("count(1) as count")
                        ->addfrom('Models\Pro\MpUserOrg', 'o')
                        ->leftjoin('Models\Pro\MpUser', 'm.id = o.user_id', 'm')
                        ->where('o.org_id=' . $value['org_id'] . ' AND m.role_id=0 ')
                        ->getQuery()
                        ->execute()->toArray()[0];
                    $value['member_count'] = $memberCount['count'];
                });
            }
            $result['data'] = $data;
        }
        return $result;

    }


    public function checkUserPermission($adminId, $organIds)
    {

        $adminOrgs = $this->getUserOrgs($adminId);
        if (empty($adminOrgs)) {
            throw new PikException (ErrorCode::USER_HAVE_NOT_OPERATION_PERMISSION);

        }
        $params['conditions'] = '1=1';
        $params['conditions'] .= ' and id IN ({organIds:array})';
        $params['bind']['organIds'] = $organIds;

        $mpOrgs = MpOrganization::find($params);
        if (empty($mpOrgs)) {
            throw new PikException (ErrorCode::USER_HAVE_NOT_OPERATION_PERMISSION);

        }
        $organPids = [];
        foreach ($mpOrgs as $org) {
            $pids = $org->parent_ids . $org->id;
            $pids = explode(',', trim($pids, ","));
            foreach ($pids as $id) {
                $organPids[$id] = 0;
            }
        }
        $organPids = array_keys($organPids);

        $intersection = array_intersect($organPids, $adminOrgs);

        if (!$intersection) {
            throw new PikException (ErrorCode::USER_HAVE_NOT_OPERATION_PERMISSION);

        }
    }

    public function findProfileByMpUserId(Array $ids)
    {
        $queryParams = array(
            'conditions'=> 'id in  ({ids:array}) ',
            'bind'=>array(
                'ids'=>$ids
            )
        );
        $arr = MpUser::find($queryParams)->toArray();
        $newArr = array();
        if(isset($arr[0]))
        {
            $newArr = array_reduce($arr,function(&$newArr,$val){
                $newArr[$val['id']] = $val;
                return $newArr;
            });
        }
        return $newArr;
    }

    public function findValidProfileByMpMobile(Array $mobile)
    {
        $queryParams = array(
            'conditions' => 'account in  ({accounts:array}) AND is_delete ='. MpUser::STATUS_ENABLE,
            'bind' => array(
                'accounts' => $mobile
            )
        );
        $arr = MpUser::find($queryParams)->toArray();
        $newArr = array();
        if (isset($arr[0])) {
            $newArr = array_reduce($arr, function (&$newArr, $val) {
                $newArr[$val['id']] = $val;
                return $newArr;
            });
        }
        return $newArr;
    }

}