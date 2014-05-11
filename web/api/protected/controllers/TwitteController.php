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
}

