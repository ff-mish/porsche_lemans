<?php

/**
 * @author Jackey <jziwenchen@gmail.com>
 * 系统级别的Controller 比如 404  / 错误 
 */
class WebController extends Controller {
  public function init() {
    parent::init();
  }
  
  public function actionError() {
    $error = Yii::app()->errorHandler->error;
    if (!$error) {
      $event = func_get_arg(0);
      if ($event instanceof CExceptionEvent) {
        return $this->responseError($event);
      }
    }
    $this->responseError($error);
  }
  
  // 生成邀请链接时候我们在URL上加了密，所以这里调用接口解密
  public function actionDecryptionURL() {
    $requst = Yii::app()->getRequest();
    $data = $requst->getParam("d");
    
    $this->responseJSON($params);
  }
  
  public function actionWelcome() {
    $request = Yii::app()->getRequest();
    $d = $request->getParam("d");
    if ($d) {
      $userAr = new UserAR();
      $data = $userAr->decryptionInvitedData($d);
      Yii::app()->session["invited_data"] = $data;
    }
    $this->responseJSON("hello, lemans", "");
  }
  
  public function actionInittoken() {
    $this->render("inittoken");
  }
}

