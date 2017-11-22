<?php
/**
 * Description of PikException
 *
 * @author jacky.deng
 */

namespace Exceptions;
use Common\ErrorCode;

class PikException extends \Exception {
    protected $code;
    protected $msg = null;

    function __construct ($err_code, $err_msg = null) {
        $this->code = $err_code;
        if (null != $err_msg) {
            $this->msg = $err_msg;
        }
    }

    public function getErrMsg($code){
        if(empty($this->msg)){
            return isset(ErrorCode::$_errorMsgArr[$code]) ? ErrorCode::$_errorMsgArr[$code] : '';
        }else{
            return $this->msg;
        }
    }
}

?>
