<?php

/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController {
  
  public function beforeAction($action) {
    // Custom logic
    return parent::beforeAction($action);
  }

  /**
   * 
   * @param type 错误消息
   * @param type 错误代码
   * @param type 错误额外数据
   */
  public function responseError($message, $error = 500, $ext = array()) {
    $this->_renderjson($this->wrapperDataInRest(NULL, $message, $error, $ext));
  }

  public function randomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
  }

  // 在这里加缓存功能
  public function responseJSON($data, $message, $ext = array()) {
    $request = Yii::app()->getRequest();
    $controllerName = Yii::app()->controller->id;
    $actioName = Yii::app()->controller->action->id;

    $this->_renderjson($this->wrapperDataInRest($data, $message, ErrorAR::ERROR_NO_ERROR, $ext));
  }

  public function wrapperDataInRest($data, $message = '', $error = FALSE, $ext = array()) {
    $json = array(
        "status" => $error,
        "message" => $message,
        "data" => $data
    );

    if (!empty($ext)) {
      $json["ext"] = $ext;
    }

    return $json;
  }

  private function _renderjson($data) {
    header("Content-Type: application/json; charset=UTF-8");
    print CJavaScript::jsonEncode($data);
    die();
  }

  // 辅助方法
  public function isPost() {
    return Yii::app()->getRequest()->isPostRequest;
  }

  public function isPut() {
    return Yii::app()->getRequest()->isPutRequest;
  }

  public function __construct($id, $module = null) {
    parent::__construct($id, $module);
    
    Yii::app()->session["server_start"] = time();

//    $id = Yii::app()->user->getId();
//    // 未登陆情况下 设置一个默认的 useridentity
//    if (!$id) {
//      $userIdentity = new UserIdentity("", "");
//      Yii::app()->user->login($userIdentity);
//    }
  }

  public function init() {
    parent::init();

    //只取前4位，这样只判断最优先的语言。如果取前5位，可能出现en,zh的情况，影响判断。
    $cookies = Yii::app()->request->cookies;
    $lang = $cookies["lang"];
    if ($lang) {
      Yii::app()->language = (string)$lang;
    }
    else {
      $ip = Yii::app()->request->userHostAddress;
      try {
        $content = file_get_contents("http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=".$ip);
        $ip_info = json_decode($content, TRUE);
        if (isset($ip_info["country"]) && $ip_info["country"] == "中国") {
          setcookie("lang", "zh_cn", time() + 3600 * 24, "/");
          Yii::app()->language = "zh_cn";
        }
      }
      catch (Exception $e){
        Yii::app()->language = "en_us";
      }
    }

    
    Yii::app()->attachEventHandler("onError", array($this, "actionError"));
    Yii::app()->attachEventHandler("onException", array($this, "actionError"));

//    // 设置Twitter Token
//    Yii::app()->twitter->user_token = Yii::app()->session["twitter_token"]["oauth_token"];
//    Yii::app()->twitter->user_secret = Yii::app()->session["twitter_token"]["oauth_token_secret"];
  }

  public function actionError($event) {
    $error = Yii::app()->errorHandler->error;
    if (!$error) {
      $event = func_get_arg(0);
      $exception = $event->exception;
      if ($exception->statusCode == "404") {
        $this->render("/error/404");
      }
      else {
        if ($event instanceof CExceptionEvent) {
          return $this->responseError($event);
        }
        else if ($event instanceof CErrorEvent) {
          return $this->responseError($event);
        }
      }
    }
    $this->responseError($error);
  }

}
