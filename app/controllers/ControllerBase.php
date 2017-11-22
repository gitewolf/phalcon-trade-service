<?php

namespace Controllers;

use Common\ErrorCode;
use Curl\Curl;
use Exceptions\PikException;
use Phalcon\Events\Event;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;


class ControllerBase extends Controller
{
    private $service = null;
    /** @var \Phalcon\Logger\Adapter\File $logService  */
    protected $logService;

    public function initialize()
    {
        $this->view->disable(); //禁用视图
        $serviceName = lcfirst(substr(static::class, 12, -10) . 'Service');
        if ($this->getDi()->has($serviceName)) {
            $this->service = $this->{$serviceName};
        }
        $this->logService = $this->tradeLogger;

    }


    /**
     * 捕捉错误异常code 返回错误数据
     */
    public function restfulErrAction()
    {
        $errCode = $this->dispatcher->getParam('errCode');
        $this->_RESTfulError($errCode);
    }


    /**
     * 输出RESTful 错误响应
     * @param $errCode
     * @param null $result
     */
    protected function _RESTfulError($errCode, $result = null, $errMsg = null)
    {
        if (empty($errMsg)) {
            if (isset(ErrorCode::$_errorMsgArr[$errCode])) {
                $errMsg = ErrorCode::$_errorMsgArr[$errCode];
            } else if (!empty($result) && is_string($result)) {
                $errMsg = $result;
            } else {
                $errMsg = '服务异常';
            }
        }

        if (empty($result)) {
            $result = new \ArrayObject();
        }
        $data = [
            'code' => intval($errCode),
            'msg' => $errMsg,
            'result' => $result,
        ];
        $this->_response($data);
    }


    /**
     * 输出RESTful响应
     * @param mixed $result 结果数据集
     * @param string $msg 描述信息
     */
    protected function _RESTful($result, $msg = 'Success')
    {
        $data = [
            'code' => 0,
            'msg' => $msg,
            'result' => $result,
        ];
        $this->_response($data);
    }

    /**
     * 处理异常
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @param $exception
     */
    public function beforeException(Event $event, Dispatcher $dispatcher, $exception)
    {
//        $dispatcher->
        var_dump($event);
        var_dump($exception);
        exit;
    }

    /**
     * 返回用户参数
     * @param $paramName
     * @param null $filters
     * @param null $defaultValue
     * @return float|int|mixed|null|string
     */
    public function getRequest($paramName, $filters = null, $defaultValue = null)
    {
        $param_value = null;
        if ($this->request->isGet()) {
            $param_value = $this->request->getQuery($paramName);
        }

        if ($this->request->isPost()) {
            $param_value = $this->request->getPost($paramName);
        }

        if ($this->request->isDelete()) {
            $param_value = $this->request->get($paramName);

        }
        if ($this->request->isPut()) {
            $param_value = $this->request->getPut($paramName);
        }
        if ($defaultValue !== null) {
            if ($param_value === null) {
                $param_value = $defaultValue;
            }
        }
        if (!empty($filters)) {
            if ($filters == 'int') {
                $param_value = intval($param_value);
            } else if ($filters == 'string') {
                $param_value = strval($param_value);
            } else if ($filters == 'double') {
                $param_value = doubleval($param_value);
            } else if ($filters == 'boolean') {
                $param_value = boolval($param_value);
            }
        }
        return $param_value;
    }


    /**
     * 检查参数不能为空
     * @param $params
     * @return bool
     */
    public function checkParamsEmpty($params)
    {
        if(empty($params)){
            throw new PikException (ErrorCode::PARAM_ERROR);
        }else if (is_array($params)) {
            foreach ($params as $param) {
                if ($param === null || $param === '') {
                    throw new PikException (ErrorCode::PARAM_ERROR);
                }
            }
        } else {
            if ($params === null || $params === '') {
                throw new PikException (ErrorCode::PARAM_ERROR);
            }
        }
        return true;
    }

    /**
     * Execute before the router so we can determine if this is a provate controller, and must be authenticated, or a
     * public controller that is open to all.
     *
     * @param Dispatcher $dispatcher
     * @return boolean
     */
    public function beforeExecuteRoute(Dispatcher $dispatcher)
    {
//        $controllerName = $dispatcher->getControllerName();
//        $methodName = $dispatcher->getControllerName();
//        // Only check permissions on private controllers
//        if (!$this->acl->isPublic($controllerName,$methodName)) {
//
//            // Get the current identity
//            $identity = $this->auth->getIdentity();
//
//            // If there is no identity available the user is redirected to index/index
//            if (!is_array($identity)) {
//
////                $this->flash->notice('You don\'t have access to this module: private');
//                $dispatcher->forward(array(
//                    'controller' => 'user',
//                    'action' => 'login'
//                ));
//                return false;
//            }
//            // Check if the user have permission to the current option
//            $actionName = $dispatcher->getActionName();
//            if ($identity['username'] && !$this->acl->isAllowed($identity['role'], $controllerName, $actionName)) {
//
//                $this->flash->notice('You don\'t have access to this module: ' . $controllerName . ':' . $actionName);
//
//                if ($this->acl->isAllowed($identity['profile'], $controllerName, 'index')) {
//                    $dispatcher->forward(array(
//                        'controller' => $controllerName,
//                        'action' => 'index'
//                    ));
//                } else {
//                    $dispatcher->forward(array(
//                        'controller' => 'user_control',
//                        'action' => 'index'
//                    ));
//                }
//
//                return false;
//            }
//        }
    }

    /**
     * @param $data
     */
    protected function _response($data)
    {
        $encodeOptions = JSON_UNESCAPED_UNICODE + JSON_BIGINT_AS_STRING;
        $res = json_encode($data, $encodeOptions);
        $this->response->setContentType('application/json', 'UTF-8');
        $this->response->setHeader('X-JDS-RESPONSE-FMT', 'application/json');
        $this->response->setContent($res);

        $this->response->send();

        exit;
    }

    /**
     * 获取用户信息
     * @param $userIds
     * @return mixed
     */
    public function getQrUserinfoByGid($userIds)
    {
        /* @var Passport $sdkPassport */
        $sdkPassport = $this->getDI()->getShared('sdkPassport');
        if (is_array($userIds)) {
            $userIds = implode(',', $userIds);
        }
        $params = ['gid' => $userIds, 'with_qrinfo' => 1];
        $profileArr = $sdkPassport->getProfileByGid($params);
        $userList = $this->formatUserArray($profileArr, 'formatQrcode');
        return $userList;
    }


    /**
     * 格式化用户列表信息
     * @param $anim
     * @return array
     */
    protected function formatUserArray($userArr, $callBack = 'formatUser')
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

    protected function formatQrcode($user)
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
                'login_id'=>isset($user['qrInfo']['login_id']) ? $user['qrInfo']['login_id']:null

            ];
        return $user;
    }

}
