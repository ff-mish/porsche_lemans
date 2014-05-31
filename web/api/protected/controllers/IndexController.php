<?php

class IndexController extends Controller {
  
  public $page_name;
  public $classname;
  public $is_start = FALSE;
  
  public function init() {
    parent::init();
    
    $this->layout = "default";
    
    // 判断活动是否已经开始
    $start_data = strtotime(Yii::app()->params["startTime"]);
    $now = time();
    if ($now >= $start_data) {
      $this->is_start = TRUE;
    }
  }
  
  public function beforeAction($action) {
    // 用户是否登录
    $user = UserAR::crtuser();
    if (!$user && $action->id != "index") {
      return $this->redirect("/");
    }
    return parent::beforeAction($action);
  }
  
  public function actionIndex() {
    $params = array(
        "page_name" => "index"
    );
    $this->page_name = $params["page_name"];
    $startTime = strtotime(Yii::app()->params["startTime"]);
    $now = time();
    if ($now >= $startTime) {
      $this->render("index", $params);
    }
    else {
      $this->render("countdown");
    }
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
    $this->classname = "pagebg8";
    
    // 邀请逻辑
    $invited_data = Yii::app()->session["invited_data"];
    $code = $invited_data["code"];
    if ($invited_data && $user && !$user->team && !InviteLogAR::userWasAllowedInvite($user->uuid, $code)) {
      $params["is_invited"] = TRUE;
      // 在这里还要获取一下用户的被邀请的team的名字
      $tid = $invited_data["tid"];
      $team = TeamAR::model()->findByPk($tid);
      if ($team) {
        $params["team_name"] = $team->name;
        $params["team_id"] = $team->tid;
      }
      else {
        $params["team_name"] = "";
        $params["team_id"] = "";
      }
    }
    else {
      $params["is_invited"] = FALSE;
      $params["team_name"] = "";
      $params["team_id"] = "";
    }
    
    // 用户如果是自动加入了小组, 但是又是属于邀请类型用户
    // 这时需要询问用户是否加入当前小组？
    if ($user->status = UserAR::STATUS_AUTO_JOIN) {
      //1. 把用户现在的组拿出来
      $team_now = $user->team;
      if ($team_now) {
        $params["now_team_name"] = $team_now->name;
        $params["now_team_id"] = $team_now->tid;
      }
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
    $this->classname = "pagebg10";
    $this->render("monitoring", $params);
  }
  
  public function actionWinnerprices() {
    $params = array(
        "page_name" => "winnerprices"
    );
    $this->page_name = $params["page_name"];
    $this->classname = "pagebg12";
    
    $this->render("winnerprices");
  }
}

