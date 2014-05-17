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
    $msg .= ' '.Yii::app()->params["topic"];
    
    // 转发一个 微博 (repost)
    $uuid = $request->getPost("uuid", FALSE);
    
    // 评论一个微博 (reply)
    $screen_name = $request->getPost("screen_name", FALSE);
    
    $twitteAr = new TwitteAR();
    $ret = $twitteAr->twittePost($msg);
    
    $this->responseJSON($ret, "success");
  }
}

