<?php

class ScoreTeamAR extends CActiveRecord {
  public function tableName() {
    return "score_team";
  }
  
  public function primaryKey() {
    return "tsid";
  }
  
  public static function model($classname = __CLASS__) {
    return parent::model($classname);
  }
  
  public function rules() {
    return array(
        array("tsid,tid, cdate, speed,quality, assiduity,impact,average", "safe"),
    );
  }


  public function relations() {
    return array(

    );
  }
  
  public function beforeSave() {
//    $this->udate = date("Y-m-d H:i:s");
    if ($this->isNewRecord) {
      $this->cdate = date("Y-m-d H:i:s");
    }
    return TRUE;
  }

    /**
     * 返回当前用户所属团队的最新积分
     */
    public function getTeamScore($tid)
    {
        $teamScore=$this->find(
            array(
                "condition" => "tid=:tid",
                "params" => array(':tid'=>$tid),
                'order'=>'calculate_time DESC',
            )
        );
        return $teamScore;
    }

    public function afterSave() {
    return parent::afterSave();
  }

}

