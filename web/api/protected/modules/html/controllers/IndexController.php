<?php

class IndexController extends CController {
  
  public $page_name;
  
  public function init() {
      $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4); //只取前4位，这样只判断最优先的语言。如果取前5位，可能出现en,zh的情况，影响判断。
      if (preg_match("/zh-c/i", $lang))
          Yii::app()->language='zh_cn';
      else if (preg_match("/zh/i", $lang))
          Yii::app()->language='zh_cn';

    $this->layout = "default";
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
  
  public function actionFule() {
    $params = array(
        "page_name" => "fule"
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
    $this->page_name = $params["page_name"];
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
}

