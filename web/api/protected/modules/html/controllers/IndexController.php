<?php

class IndexController extends CController {
  
  public $page_name;
  public $is_start = FALSE;
  
  public function init() {
      $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4); //只取前4位，这样只判断最优先的语言。如果取前5位，可能出现en,zh的情况，影响判断。
      if (preg_match("/zh-c/i", $lang))
          Yii::app()->language='zh_cn';
      else if (preg_match("/zh/i", $lang))
          Yii::app()->language='zh_cn';

    $this->layout = "default";
    
    // 判断活动是否已经开始
    $start_data = strtotime(Yii::app()->params["startTime"]);
    $now = time();
    if ($now >= $start_data) {
      $this->is_start = TRUE;
    }
    return parent::init();
  }
  
  public function actionIndex() {
    $params = array(
        "page_name" => "index"
    );
    $this->page_name = $params["page_name"];
    $this->render("index", $params);
  }
  
  public function actionAchieve() {
    $params = array(
        "page_name" => "achieve"
    );
    $this->page_name = $params["page_name"];
    $this->render("achieve", $params);
  }
  
  public function actionCountdown() {
    $params = array(
        "page_name" => "countdown"
    );
    $this->page_name = $params["page_name"];
    $this->render("countdown", $params);
  }
  
  public function actionFuel() {
    $params = array(
        "page_name" => "fuel"
    );
    $this->page_name = $params["page_name"];
    $this->render("fule", $params);
  }
  
  public function actionRace() {
    $params = array(
        "page_name" => "race"
    );
    $this->page_name = $params["page_name"];
    $this->render("race", $params);
  }
  
  public function actionRaceteam() {
    $params = array(
        "page_name" => "raceteam"
    );
    $this->page_name = $params["page_name"];
    $this->render("race", $params);
  }
  
  public function actionStand() {
    $params = array(
        "page_name" => "stand"
    );
    $user = UserAR::crtuser(TRUE);

    if ($user && $user->team) {
      $params["team_name"] = $user->team->name;
      $params["team_owner_uid"] = $user->team->owner_uid;
    }
    else {
      $params["team_name"] = "";
      $params["team_owner_uid"] = "";
    }
    $this->page_name = $params["page_name"];
    
    $invited_data = Yii::app()->session["invited_data"];
    if ($invited_data) {
      $params["is_invited"] = TRUE;
    }
    else {
      $params["is_invited"] = FALSE;
    }
    
    $this->render("stand", $params);
  }
  
  public function actionTeambuild() {
    $params = array(
        "page_name" => "teambuild"
    );
    $this->page_name = $params["page_name"];
    $this->render("teambuild", $params);
  }
  
  public function actionTweet() {
    $params = array(
        "page_name" => "tweet"
    );
    $this->page_name = $params["page_name"];
    $this->render("tweet", $params);
  }
  
  public function actionMonitoring() {
    $params = array(
        "page_name" => "monitoring"
    );
    $this->page_name = $params["page_name"];
    $this->render("monitoring", $params);
  }
}
