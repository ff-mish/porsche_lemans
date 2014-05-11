<?php

class TestController extends Controller {
  public function actionIndex() {
    $userAr = new UserAR();
    $invited_url = $userAr->generateInvitedURL("200238123", "30209847");
    
    $this->render("index", array("url" => $invited_url));
  }
  
  public function actionAddMedia() {
    $this->render("addmedia");
  }
  
  public function actionTwitte() {
    $this->render("twitte");
  }
}

