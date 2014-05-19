<?php

class TeamAR extends CActiveRecord {
  public $users;
  public $last_post;
  public $score;
  
  const LAST_POST_NUM = 3;
  
  const STATUS_OFFLINE = 0;
  const STATUS_ONLINE = 1;
  
  
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
        array("name, cdate, udate, score, owner_uid, achivements_total, status", "safe"),
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
    // 这里要判断下， 当新添加一个team 之前
    // 我们需要判断用户是否属于一个Team 了 
    // 如果是我们不能让它添加一个team
    $_uid = $this->owner_uid;
    $_tid = $this->tid;
    $query = new CDbCriteria();
    $query->addCondition("uid=:uid")
            ->addCondition("tid=:tid");
    $query->params = array(":tid" => $_tid, ":uid" => $_uid);
    if (UserTeamAR::model()->find($query)) {
      return $this->addError("owner_uid", "user has joined team");
    }
    
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
  
  /**
   * 查找owner 的所在team
   * owner 可能有多个team, 我们选择一个有效的team (有组员的team为有效team)
   * 如果没有有效的Team 我们选择一个最新的一个team
   */
  public static function loadTeamByOwner($uid) {
    // 先确定用户所在组
    $query = new CDbCriteria();
    $query->addCondition("uid=:uid");
    $query->params[":uid"] = $uid;
    
    $userTeam = UserTeamAR::model()->find($query);
    
    if ($userTeam) {
      $tid = $userTeam->tid;
      $team = TeamAR::model()->findByPk($tid);
      // 如果用户加入的小组 自己正好是team owner 则直接返回
      if ($team->owner_uid == $uid) {
        return $team;
      }
      else {
        //如果用户不是owner 则说明用户之前退出过team, 我们要找回这个team
        $query = new CDbCriteria();
        $query->addCondition("owner_uid=:uid");
        $query->order="cdate DESC";
        $query->params[":uid"] = $uid;
        $all_teams = TeamAR::model()->findAll($query);
        // 遍历之前用户所有创建的team 找出一个有效的team
        foreach ($all_teams as $team) {
          $tid = $team->tid;
          $query_is_valid = new CDbCriteria();
          $query_is_valid->addCondition("tid=:tid");
          $query_is_valid->params[":tid"] = $tid;
          $count = UserTeamAR::model()->count($query_is_valid);
          if ($count) {
            return $team;
          }
        }
        if (count($all_teams)) {
          // 如果没有找到任何一个team 则返回最新创建的team
          return $all_teams[0];
        }
        return $all_teams;
      }
      return FALSE;
    }
    // 用户不在任何组，说明用户已经退出了组
    else {
      return FALSE;
    }
  }
}

