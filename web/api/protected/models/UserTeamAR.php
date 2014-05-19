<?php

class UserTeamAR extends CActiveRecord {
  public function tableName() {
    return "user_teams";
  }
  
  public function primaryKey() {
    return "utid";
  }
  
  public function rules() {
    return array(
        array("uid, tid", "required"),
        array("uid, tid", 'safe'),
    );
  }
  
  public static function model($classname = __CLASS__) {
    return parent::model($classname);
  }
  
  public function relations() {
    return array(
        "user" => array(self::BELONGS_TO, "UserAR", "uid"),
        "team" => array(self::BELONGS_TO, "TeamAR", "tid"),
    );
  }
  
  public function beforeSave() {
    $this->udate = date("Y-m-d H:i:s");
    if ($this->isNewRecord) {
      $this->cdate = date("Y-m-d H:i:s");
    }
    
    $uid = $this->uid;
    $tid = $this->tid;
    // 避免重复加组
    $query = new CDbCriteria();
    $query->addCondition("uid=:uid");
    $query->addCondition("tid=:tid");
    $query->params = array(":uid" => $uid, ":tid" => $tid);
    if ($this->find($query)) {
      $this->addError("uid", "User has joined team");
      return FALSE;
    }
    
    return TRUE;
  }
  
  /**
   * 
   * @param type $user
   * @return TeamAR
   */
  public function loadUserTeam($user) {
    $cond = array(
        "condition" => $this->getTableAlias().".uid=:uid",
        "params" => array(":uid" => $user->uid),
    );
    $row = $this->with("user", "team")->find($cond);
    
    if ($row) {
      $team = $row->team;
    }
    
    return $row;
  }

  public function leaveTeam($uid) {
    $query = new CDbCriteria();
    $query->addCondition("uid=:uid");
    $query->params[":uid"] = $uid;
    $userTeam = $this->find($query);
    if ($userTeam) {
      return $userTeam->delete();
    }
    
    return TRUE;
  }
}


