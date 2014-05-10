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
    return TRUE;
  }
}

