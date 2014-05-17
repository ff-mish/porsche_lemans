<?php

class ScoreUserAR extends CActiveRecord {
  public function tableName() {
    return "score_user";
  }
  
  public function primaryKey() {
    return "suid";
  }
  
  public static function model($classname = __CLASS__) {
    return parent::model($classname);
  }
  
  public function rules() {
    return array(
        array("suid,uid, cdate, speed,quality, assiduity,impact,average", "safe"),
    );
  }


  public function relations() {
    return array(

    );
  }

    /**
     * 返回当前用户当前积分
     */
    public static function getUserScore($uid)
    {
        $userScore=self::model()->find(
            array(
                "condition" => "uid=:uid",
                "params" => array(':uid'=>$uid),
                'order'=>'cdate DESC',
            )
        );
        return $userScore;
    }
  
  public function beforeSave() {
//    $this->udate = date("Y-m-d H:i:s");
    if ($this->isNewRecord) {
      $this->cdate = date("Y-m-d H:i:s");
    }
    return TRUE;
  }
  
  public function afterSave() {
    return parent::afterSave();
  }

}

