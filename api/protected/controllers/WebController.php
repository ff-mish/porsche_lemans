<?php

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
  
  public function actionWelcome() {
    $this->responseJSON("hello, lemans", "");
  }
}

