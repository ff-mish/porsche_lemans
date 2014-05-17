<?php

class TeamAR extends CActiveRecord {
  public $users;
  public $last_post;
  public $score;
  
  const LAST_POST_NUM = 3;
  
  
  public function tableName() {
    return "teams";
  }
  
  public function primaryKey() {
    return "tid";
  }
  
  public static function model($classname = __CLASS__) {
    return parent::model($classname);
  }
  
  public function rules() {
    return array(
        array("name, owner_uid", "required"),
        array("owner_uid", "uniqueColumn"),
        array("name, cdate, udate, score, owner_uid", "safe"),
    );
  }
  
  public function  uniqueColumn($attribute, $params = array()) {
    $value = $this->{$attribute};
    $cond = array("condition" => "$attribute = :value", "params" => array(":value" => $value));
    
    $rows = $this->findAll($cond);
    if ($rows && count($rows) > 0) {
      $this->addError($attribute, "column values must be unique");
    }
  }


  public function relations() {
    return array(
        "creator" => array(self::BELONGS_TO, "UserAR", "owner_uid"), 
    );
  }
  
  public function beforeSave() {
    $this->udate = date("Y-m-d H:i:s");
    if ($this->isNewRecord) {
      $this->cdate = date("Y-m-d H:i:s");
    }
    return TRUE;
  }
  
  public function afterSave() {
    // 自动把队长加入到team 组员里去
    $tid = $this->tid;
    $uid = $this->owner_uid;
    
    $user_team = new UserTeamAR();
    $user_team->uid = $uid;
    $user_team->tid = $tid;
    
    $user_team->save();
    
    return parent::afterSave();
  }
  
  public function afterFind() {
    // 获取最新Post
    $twitteAr = new TwitteAR();
    $this->last_post = $twitteAr->getTeamLastPost($this->tid);
    // 获取 统计记分
    $score = ScoreTeamAR::getTeamScore($this->tid);
    $this->score = $score;
    
    return parent::afterFind();
  }
  
  /**
   * 添加一个新的Team. 每个用户只能创建一个Team.
   * @param type $name
   * @return boolean|\TeamAR
   */
  public static function newteam($name) {
    $user_ar = UserAR::crtuser();
    if (!$user_ar) {
      return FALSE;
    }
    
    $team_ar = new TeamAR();
    $team_ar->name = $name;
    $team_ar->owner_uid = $user_ar->uid;
    
    if ($team_ar->save()) {
      return $team_ar;
    }
    else {
      return $team_ar->getErrors();
    }
    
    return FALSE;
  }
  
  public function loadMembers($tid = FALSE) {
    if (!$tid) {
      $tid = $this->tid;
    }
    
    $cond = array(
        "condition" => "tid=:tid",
        "params" => array(":tid" => $tid),
    );
    $user_teams = UserTeamAR::model()->findAll($cond);
    $users = array();
    foreach ($user_teams as $user_team) {
      $uid = $user_team->uid;
      $user = UserAR::model()->findByPk($uid);
      $users[] = $user;
    }
    $this->users = $users;
    
    return $users;
  }
}

