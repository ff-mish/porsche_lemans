<?php

Yii::import("ext.sinaWeibo.SinaWeibo_API");

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
    
    // 转发一个 微博 (repost) 需要知道原始微博的 uuid 便于统计
    $uuid = $request->getPost("uuid", FALSE);
    
    // 评论一个微博 (reply)  需要知道评论人的 screen_name 便于统计 
    $screen_name = $request->getPost("screen_name", FALSE);
    // 评论一个微博 (reply)  需要知道原始微博的 uuid便于统计 
    $screen_name = $request->getPost("uuid", FALSE);
    
    // 自己系统发的一个微博
    $from = $request->getPost("from", "web");
    if ($from == "web") {
      $host = parse_url(Yii::app()->getBaseUrl(TRUE), PHP_URL_HOST);
      $weibo_api = new SinaWeibo_API(WB_AKEY, WB_SKEY, UserAR::token());
      $short_urls = $weibo_api->short_url_shorten("http://". $host);
      $msg = Yii::app()->params["topic"]." ". $msg. " ". $short_urls["urls"][0]["url_short"];
    }
    
    $twitteAr = new TwitteAR();
    $ret = $twitteAr->twittePost($msg);
    
    $this->responseJSON($ret, "success");
  }
}

