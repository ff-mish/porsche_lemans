<?php

class QAAR extends CActiveRecord {
  // 每页的“问题” 个数
  const PAGE_ITEMS = 10;
  public function tableName() {
    return "question_anwser";
  }
  
  public function primaryKey() {
    return "qaid";
  }
  
  public function rules() {
    return array(
        array("right", "numerical"),
        array("question,answer1, answer2,answer3, answer4,right, answered, right_answered, cdate, udate, uid", "safe"),
    );
  }
  
  public  function beforeSave() {
    if ($this->isNewRecord) {
      $this->cdate = date("Y-m-d H:i:s");
    }
    $this->udate = date("Y-m-d H:i:s");
    $this->uid = 0;
    return parent::beforeSave();
  }

  public static function model($classname = __CLASS__) {
    return parent::model($classname);
  }
  
  public function randomQuestion($user) {
    // Step1, 先拿出用户最后一次回答问题的时间
    if ($user) {
      $name = "uid_". $user->uid.'_question_lasttime';
      $last_time = SystemAR::get($name, 0);
      if (!$last_time) {
        $last_time = time();
      }
      // Step2, 判断用户是否已经满足弹出条件
      // TODO:: 客户端应该判断这个条件
      
      // Step3, 拿出用户没有回答过的Question
      $user_qa_ar = new UserQAAR();
      $answered_questions = $user_qa_ar->loadUserAnwseredQuestiones($user);
      $ids = array();
      foreach ($answered_questions as $row) {
        $ids[] = $row->qaid;
      }
      $cond = array(
          "condition" => "qaid not in (:ids)",
          "params" => array(":ids" => implode(",", $ids)),
          "order" => "RAND()"
      );
      
      $row = $this->find($cond);
      if ($row) {
        return $row;
      }
    }
  }
  
  /**
   * 添加一条新的问题
   * @param type $question
   * @param type $anwsers
   * @param type $right
   */
  public function addNewQuestion($question, $anwsers, $right) {
    $this->question = $question;
    foreach ($anwsers as $key => $answer) {
      $this->{"answer". (intval($key) + 1)} = $answer;
    }
    $this->right = $right;
    
    return $this->save();
  }
  
  public function getListInPage($page) {
    $query = new CDbCriteria();
    $offset = ( $page - 1 ) * self::PAGE_ITEMS;
    $query->select = "*";
    $query->limit = self::PAGE_ITEMS;
    $query->offset = $offset;
    
    $rows = $this->findAll($query);
    
    return $rows;
  }
  
  public function deleteByPk($pk , $condition = '', $params = array()) {
    // TODO:: 删除前 是否确认删除用户对应的数据？？
    return parent::deleteByPk($pk);
  }
  
  public function updateQuestion($question, $anwsers, $right) {
    if ($question) {
      $this->question = $question;
    }
    foreach ($anwsers as $key => $answer) {
      if ($answer) {
        $this->{"answer". (intval($key) + 1)} = $answer;
      }
    }
    if ($right != -1) {
      $this->right = $right;
    }
    
    return $this->update();
  }
}

