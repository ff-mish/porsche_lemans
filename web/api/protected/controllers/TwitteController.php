<?php

class TwitteController extends Controller {
  public function init() {
    return parent::init();
  }
  
  public function actionPost() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      return $this->responseError("http error", ErrorAR::ERROR_HTTP_VERB_ERROR);
    }
    $msg = $request->getPost("msg");
    
    $twitteAr = new TwitteAR();
    $ret = $twitteAr->twittePost($msg);
    
    $this->responseJSON($ret, "success");
  }
  
  public function actionList() {
    $request = Yii::app()->getRequest();
    
    $num = $request->getParam("num", 10);
    $level = $request->getParam("level", TwitteAR::LEVEL_USER);
    $twittes = array();
    
    // Web 的微博从Cron JOB 抓取
    if ($level == TwitteAR::LEVEL_WEB) {
      //TODO::
    }
    else {
      $twitteAr = new TwitteAR();
      $twittes = $twitteAr->getListInLevel($level, $num);
    }
    
    // 返回数据
    $data = array();
    foreach ($twittes as $twitte) {
      $item = $twitte->attributes;
      $item["user"] = $twitte->user;
      $data[] = $item;
    }
    
    $this->responseJSON($data, "sucess");
  }
}

