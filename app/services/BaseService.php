<?php

namespace Services;

use Curl\Curl;
use Phalcon\Di;
use Phalcon\Di\Service;
use Phalcon\mvc\Collection;
use Phalcon\Mvc\Model\Transaction\Manager as TxManager;
use Exceptions\PikException;


class BaseService
{
    private static $di;
    private static $transaction;

    /** @var \Phalcon\Logger\Adapter\File $logger */
    public $logger;
    /** @var \Phalcon\Logger\Adapter\File $logger */
    public $tradeLogger;

    public function __construct()
    {
        $this->logger = $this->getShared("logger");
        $this->tradeLogger = $this->getShared("tradeLogger");
    }

    public function getDi()
    {
        if (self::$di == null) {
            self::$di = Di::getDefault();
        }
        return self::$di;
    }

    public function getTransaction()
    {
        if (self::$transaction == null) {
            $manager = new TxManager();
            $transaction = $manager->get();
            self::$transaction = $transaction;
        }
        return self::$transaction;
    }

    function convertObject2Array($stdObject, $fieldArray = [])
    {
        $objectArray = is_object($stdObject) ? get_object_vars($stdObject) : $stdObject;
        $result = [];
        foreach ($objectArray as $key => $value) {
            if ($key == '_id') {
                $result[$key] = strval($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function getShared($serviceName, $parameters = null)
    {
        $service = $this->getDi()->getShared($serviceName, $parameters);
        return $service;
    }

    public function getService($serviceName, $parameters = null)
    {
        $service = $this->getDi()->get($serviceName, $parameters);
        return $service;
    }

    /**
     * 获取用户信息
     * @param $userIds
     * @return mixed
     */
    public function getUserinfoByGid($userIds)
    {
        /* @var Passport $sdkPassport */
        $sdkPassport = $this->getShared('sdkPassport');
        if (is_array($userIds)) {
            $userIds = implode(',', $userIds);
        }
        $params = ['gid' => $userIds];
        $profileArr = $sdkPassport->getProfileByGid($params);
        $userList = $this->formatUserArray($profileArr);
        return $userList;
    }


    /**
     * 获取用户信息
     * @param $oids
     * @return mixed
     */
    public function getUserinfoByOid($oids)
    {
        /* @var Passport $sdkPassport */
        $sdkPassport = $this->getShared('sdkPassport');
        if (is_array($oids)) {
            $oids = implode(',', $oids);
        }
        $params = ['oid' => $oids];
        $profileArr = $sdkPassport->getProfileByOid($params);

        $userList = $this->formatUserArray($profileArr);
        return $userList;
    }
    /**
     * 获取用户信息
     * @param
     * @return mixed
     */
    public function getUserinfoByEmail($email)
    {
        /* @var Passport $sdkPassport */
        $sdkPassport = $this->getShared('sdkPassport');

        $params = ['email' => $email];
        $profileArr = $sdkPassport->getProfileByEmail($params);

        $userList = $this->formatUserArray($profileArr);
        return $userList;
    }

    /**
     * 获取用户信息
     * @param
     * @return mixed
     */
    public function getUserinfoByMobile($mobile)
    {
        /* @var Passport $sdkPassport */
        $sdkPassport = $this->getShared('sdkPassport');

        $params = ['mobile' => $mobile];
        $profileArr = $sdkPassport->getProfileByMobile($params);

        $userList = $this->formatUserArray($profileArr);
        return $userList;
    }

    public function getFrontUserInfoByGid($userIds)
    {
        /* @var Passport $sdkPassport */
        $sdkPassport = $this->getShared('sdkPassport');
        if (is_array($userIds)) {
            $userIds = implode(',', $userIds);
        }
        $params = ['gid' => $userIds];
        $profileArr = $sdkPassport->getProfileByGid($params);
        $userList = $this->formatUserArray($profileArr,'fromatFrontUser');
        return $userList;
    }
    /**
     * 构造Mongo分页信息
     * @param $currentPage
     * @param $pageSize
     * @param $queryParam
     * @param Collection $collection
     * @return array
     */
    protected function mongoPageInfo($currentPage, $pageSize, $queryParam, Collection $collection)
    {
        // 总条数

        $total = $collection::count([$queryParam]);
        $page = $this->generatePageInfo($currentPage, $pageSize, $total);

        return $page;
    }

    /**
     * 构造Mongo分页信息
     * @param $currentPage
     * @param $pageSize
     * @param $query
     * @param Collection $collection
     * @return array
     */
    protected function mysqlPageInfo($currentPage, $pageSize, $query, \Phalcon\Mvc\Model $model)
    {
        // 总条数
        $total = $model::count($query);
        // 计算总页数
        $page = $this->generatePageInfo($currentPage, $pageSize, $total);
        return $page;
    }

    /**
     * @purpose ssdbPageInfo
     * @param $currentPage
     * @param $pageSize
     * @param $count
     * @return array
     */
    public function ssdbPageInfo($currentPage, $pageSize, $count)
    {
        $total = intval($count);
        if (0 > $total) {
            $total = 0;
        }
        // 计算总页数
        $page = $this->generatePageInfo($currentPage, $pageSize, $total);
        return $page;
    }

    /**
     * @param $currentPage
     * @param $pageSize
     * @param $total
     * @return array
     */
    protected function generatePageInfo($currentPage, $pageSize, $total)
    {
        if ($total < 0) {
            $total = 0;
        }
        // 计算总页数
        $totalPage = ceil($total / $pageSize);

        $currentPage = $currentPage > $totalPage ? $totalPage : $currentPage;
        $currentPage = $currentPage < 1 ? 1 : $currentPage;
        $page = [
            'total' => $total,
            'currentPage' => $currentPage,
            'prev' => $currentPage == 1 ? 1 : $currentPage - 1,
            'next' => ($currentPage + 1) > $totalPage ? $totalPage : $currentPage + 1,
            'totalPage' => $totalPage,
        ];
        return $page;
    }

    /*
    * @purpose 获取用户详情
    */
    protected function getUsersProfle($uids)
    {
        if (!is_array($uids)) {
            $uids = [$uids];
        }

        $params = [
            'gid' => implode(',', $uids)
        ];

        return (new Passport())->getProfileByGid($params);
    }


    /**
     * 格式化用户列表信息
     * @param $anim
     * @return array
     */
    protected function formatUserArray($userArr,$callBack='formatUser')
    {
        $userList = [];
        foreach ($userArr as $key => $user) {
            if (empty($user) || !isset($user['baseInfo'])) {
                continue;
            }
            $userInfo = $this->{$callBack}($user);
            $userList[$key] = $userInfo;
        }
        return $userList;
    }

    /**
     * 格式化用户信息
     * @param $anim
     * @return array
     */
    protected function formatUser($user)
    {
        $user =
            [
                'gid' => $user['baseInfo']['gid'],
                'oid' => $user['oid'],
                'mobile' => $user['baseInfo']['mobile'],
                'nickname' => $user['baseInfo']['nickname'],
                'company' => $user['extendInfo']['company'],
                'role_type' => $user['baseInfo']['role_type'],
                'official_intro' => $user['baseInfo']['official_intro'],
                'avatar' => $user['avatarInfo']['middle'],
                'baseInfo'=>$user['baseInfo']
            ];
        return $user;
    }

    protected function fromatFrontUser($user)
    {
        $user =
            [
                'gid' => $user['baseInfo']['gid'],
                'sex' => $user['extendInfo']['sex'],
                'nickname' => $user['baseInfo']['nickname'],
                'avatar' => $user['avatarInfo'],
                'intro'=>$user['extendInfo']['intro']
            ];
        return $user;
    }

    /**
     * @param $id
     * @param string $model
     * @return Model
     */
    protected function getByPk($id, $model = '')
    {
        $queryParam = ['id =:id:'];
        $queryParam['bind'] = ['id' => $id];
        if (empty($model)) {
            $model = $this->model;
        }
        $detail = $model::findFirst($queryParam);
        return $detail;
    }


    /**
     * @purpose 云数据地址域转换
     * @param $url
     * @return string
     */
    public function yunStorageFormart($url)
    {
        if (isset($url{0}) && '{' != $url{0}) {
            return $url;
        }
        $yunStorageUrl = preg_split('/(?<=\})(?=\/)/', $url, 2);
        $yunStorageConfig = $this->getDi()->get('config')->yunStorage;
        if (!isset($yunStorageUrl[1]) || !isset($yunStorageConfig[$yunStorageUrl[0]])) {
            return $url;
        }
        return JDS_HTTP_SCHEME . $yunStorageConfig[$yunStorageUrl[0]] . $yunStorageUrl[1];
    }


    /**
     *
     * @param $serviceApi
     * @param $params
     * @param $method
     * @return bool
     */
    public function callProService($serviceApi, $params, $method = "POST")
    {
        try {
            $url = $this->getDi()->get('config')->pro_service->url . $serviceApi;
            $response = $this->call($url, $params, $method);

            if (is_array($response)) {
                if ($response['code'] == 0) {
                    $result = $response['result'];
                    return $result;
                } else {
                    throw new PikException($response['code'],$response['msg']);
                }
            } else {
                var_dump($url, $params, $response);
            }

        } catch (\Exception $e) {
            var_dump($e);
            throw new PikException($e->getCode(),$e->getMessage());
        }
        return false;
    }

    /**
     * curl实现请求
     */
    private function call($url, $request, $method)
    {
        $curl = new Curl();
        //调用service 时传入对应的APP_ID
        $curl->setHeader('appId', 'com.jackydeng.pro');
        $curl->$method($url, $request);
        if ($curl->error) {
            var_dump($curl->errorCode, $curl->errorMessage);
            return $curl->response;
            //echo 'Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            //将多层对象 转成数组
            $response = json_decode(json_encode($curl->response), true);
            return $response;
        }
    }
}
