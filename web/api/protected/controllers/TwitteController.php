<?php

class TwitteController extends Controller {
  public function init() {
    return parent::init();
  }
  
  public function actionIndex() {
    $user = UserAR::crtuser();
    if (!$user) {
      return $this->responseError("user not login", ErrorAR::ERROR_NOT_LOGIN);
    }
    $twitteAr = new TwitteAR();
    // 先拿到 Posche 微博
    $porsche_twittes = $twitteAr->getListInLevel(TwitteAR::LEVEL_WEB);
    // 再获取当前用户的微博
    $user_twittes = $twitteAr->getListInLevel(TwitteAR::LEVEL_USER);
    // 再获取当前用户组的微博
    $team_twittes = $twitteAr->getListInLevel(TwitteAR::LEVEL_TEAM);
    // 再获取话题的微博
    $top_twittes = $twitteAr->getListInLevel(TwitteAR::LEVEL_TOPIC);
    
    return $this->responseJSON(array(
        TwitteAR::LEVEL_WEB => $porsche_twittes,
        TwitteAR::LEVEL_USER => $user_twittes,
        TwitteAR::LEVEL_TEAM => $team_twittes,
        TwitteAR::LEVEL_TOPIC => $top_twittes
    ), "success");
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

