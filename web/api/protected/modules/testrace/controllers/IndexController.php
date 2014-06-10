<?php

class IndexController extends Controller {
  
  public $layout ='/layouts/default';
  
  public $page_name = "";
  
  public $classname = "";
  
  public function init() {
    parent::init();
  }
  
  public function actionIndex() {
    $this->page_name = "race";
    $this->render("index");
  }
  
  public function actionTeam() {
    $this->page_name = "teamrace";
    $this->render("team");
  }
}
