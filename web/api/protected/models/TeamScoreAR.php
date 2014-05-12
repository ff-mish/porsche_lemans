<?php

class TeamScoreAR extends CActiveRecord {
  public function tableName() {
    return "teams_score";
  }
  
  public function primaryKey() {
    return "tsid";
  }
  
  public static function model($classname = __CLASS__) {
    return parent::model($classname);
  }
  
  public function rules() {
    return array(
        array("tsid, cdate, udate, score, tid", "safe"),
    );
  }


  public function relations() {
    return array(

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
    return parent::afterSave();
  }

}

