<?php

class UserQAAR extends CActiveRecord {
  const ANSWER_WRONG = 0;
  const ANSWER_RIGHT = 1;
  
  public $has_new_achivement;
  
  public function tableName() {
    return "user_question_answer";
  }
  
  public function primaryKey() {
    return "uqaid";
  }
  
  public static function model($classname = __CLASS__) {
    return parent::model($classname);
  }
  
  public function beforeSave() {
    $this->cdate = date("Y-m-d H:i:s");
    
    // 检查用户是否之前已经回答了这个问题
    $qaid = $this->qaid;
    $uid = $this->uid;
    $cond = array("condition" => "qaid=:qaid AND uid=:uid", "params" => array(":uid" => $uid, ":qaid" => $qaid));
    $row = $this->find($cond);
    if ($row) {
      $this->addError("uid", "user answered question before");
      return FALSE;
    }
    
    return parent::beforeSave();
  }
  
  public function afterSave() {
    // TODO:: 用户回答后 判断用户是否回答正确，如果正确，我们则进行加分
    // step 1, 先判断用户是否回答正确
    if ($this->is_right == self::ANSWER_RIGHT) {
      $uid = $this->uid;
      $user = UserAR::model()->findByPk($uid);
      if ($user) {
        $userTeamAr = new UserTeamAR();
        if ($team = $userTeamAr->loadUserTeam($user)) {
          $team = $team->team;
          $old_achivement = $team->achivements_total;
          $members = $team->loadMembers();
          $query = new CDbCriteria();
          $uids = array();
          foreach ($members as $member) {
            $uids[] = $member->uid;
          }
          $query->addCondition("uid in (:uid)");
          $query->addCondition("is_right = 1");
          $query->params[":uid"] = implode(",", $uids);
          $count = self::model()->count($query);
          
          $new_achivement = round($count / 3, 0, PHP_ROUND_HALF_DOWN);
          // 这里说明 有新的盾牌
          if ($old_achivement < $new_achivement ) {
            $this->has_new_achivement = TRUE;
            $team->achivements_total = $new_achivement;
            $team->save();
          }
        }
      }
    }
    // 用户加分后 还要完成对回答问题次数的统计工作
    $questionAr = QAAR::model()->findByPk($this->qaid);
    
    if ($questionAr) {
      $right_times = $questionAr->right_answered;
      $times = $questionAr->answered;
      if ($this->is_right == self::ANSWER_RIGHT) {
        $right_times += 1;
      }
      $times += 1;
      $questionAr->right_answered = $right_times;
      $questionAr->answered = $times;
      $questionAr->save();
    }
    
    // 最后保存  Q&A 回答的时间
    $uid = $this->uid;
    $name = "uid_". $uid.'_question_lasttime';
    
    SystemAR::set($name, time());
    
    return parent::afterSave();
  }
  
  /**
   * 加载用户已经回答过的问题
   * @param type $uid
   * @return type
   */
  public function loadUserAnwseredQuestiones($user) {
    $cond = array(
        "condition" => "uid=:uid",
        "params" => array(":uid" => $user->uid),
    );
    
    $rows = $this->findAll($cond);
    return $rows;
  }
  
  /**
   * 用户回答问题接口
   * @param type $qaid
   * @param type $answer
   */
  public function answer($qaid, $answer) {
    $qa_ar = QAAR::model()->findByPk($qaid);
    if (!$qa_ar) {
      return FALSE;
    }
    else {
      if ($qa_ar->right == $answer) {
        $right = self::ANSWER_RIGHT;
      }
      else {
        $right = self::ANSWER_WRONG;
      }
      $this->qaid = $qaid;
      $this->uid = UserAR::crtuser()->uid;
      $this->is_right = $right;
      $this->answer_id = $answer;
      
      $this->save();
      
      return $this;
    }
  }
}

