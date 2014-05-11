<?php

class TeamAR extends CActiveRecord {
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
}

