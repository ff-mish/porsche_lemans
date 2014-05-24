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
    public static function getTeamScore($tid)
    {
        $teamScore = self::model()->find (
            array(
                "condition" => "tid=:tid",
                "params" => array(':tid'=>$tid),
                'order'=>'cdate DESC',
            )
        );
        return $teamScore;
    }

    public function afterSave() {
      return parent::afterSave();
    }
    
    /**
     * 计算分数的排名位置
     * 算法： SQL 选择出比它大的分数，然后 count() 操作就可以获得排名位置
     */
    public function getScorePosition() {
      $average = $this->average;
      $sql = "SELECT count(*) as count from (SELECT max(cdate), average "
              . "FROM ".$this->tableName()." GROUP BY tid) as average_score "
              . "WHERE average_score.average > ".$average;
      
      $command = Yii::app()->db->createCommand($sql);
      $row = $command->query();
      
      return $row["count"];
    }

}

