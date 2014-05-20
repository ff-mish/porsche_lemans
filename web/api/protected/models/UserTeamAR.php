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
    
    //避免一个组超过3个人
    $query = new CDbCriteria();
    $query->addCondition("tid=:tid");
    $query->params[":tid"] = $tid;
    $count = self::model()->count($query);
    if ($count >= 3) {
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
  
  /**
   * 用户退组后进行一些逻辑处理
   * @return type
   */
  public function afterDelete() {
    // 用户退组 如果组里面没有任何成员，我们则把组状态修改掉
    $tid = $this->tid;
    $team = TeamAR::model()->findByPk($tid);
    if ($team) {
      $members = $team->loadMembers();
      // 发现没有组员了，则改变状态吧
      if (count($members) <= 0) {
        $team->offlineIt();
      }
    }
    return parent::beforeDelete();
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


