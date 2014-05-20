<?php

class QuestionController extends Controller {
  
  /**
   * 随机一个问题
   */
  public function actionRandom() {
    $qa_ar = new QAAR();
    $user = UserAR::crtuser();
    if (!$user) {
      $this->responseError("user not login", ErrorAR::ERROR_NOT_LOGIN);
    }
    $question = $qa_ar->randomQuestion($user);
    $this->responseJSON($question, "success");
  }
  
  /**
   * 添加Q&A
   */
  public function actionAdd() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      return $this->responseError("post only", ErrorAR::ERROR_HTTP_VERB_ERROR);
    }
    
    $question = $request->getPost("question");
    $anwser_1 = $request->getPost("answer_1");
    $anwser_2 = $request->getPost("answer_2");
    $anwser_3 = $request->getPost("answer_3");
    $anwser_4 = $request->getPost("answer_4");
    $right = $request->getPost("right");
    
    $qa_ar = new QAAR();
    $ret = $qa_ar->addNewQuestion($question, array($anwser_1, $anwser_2, $anwser_3, $anwser_4), $right);
    
    if ($ret instanceof QAAR) {
      
    }
    else {
      return $this->responseError("save question error", ErrorAR::ERROR_VALIDATE_FAILED, $ret);
    }
  }
  
  /**
   * 获取问题列表
   */
  public function actionList() {
    $requst = Yii::app()->getRequest();
    
    $page =$requst->getParam("page", 1);
    
    $qa_ar = new QAAR();
    $questions = $qa_ar->getListInPage($page);
    
    $this->responseJSON($questions, "success");
  }
  
  public function actionDelete() {
    $requst = Yii::app()->getRequest();
    
    if (!$requst->isPostRequest) {
      return $this->responseError("http error", ErrorAR::ERROR_HTTP_VERB_ERROR);
    }
    
    $qaid = $request->getPost($qaid);
    $qa_ar = new QAAR();
    $qa_ar->deleteByPk($qaid);
    
    return $this->responseJSON(array(), "success");
  }
  
  public function actionUpdate() {
    $requst = Yii::app()->getRequest();
    
    if (!$requst->isPostRequest) {
      return $this->responseError("http error", ErrorAR::ERROR_HTTP_VERB_ERROR);
    }
    
    $qaid = $request->getPost($qaid);
    $question = $request->getPost("question");
    $anwser_1 = $request->getPost("answer_1");
    $anwser_2 = $request->getPost("answer_2");
    $anwser_3 = $request->getPost("answer_3");
    $anwser_4 = $request->getPost("answer_4");
    $right = $request->getPost("right", -1);
    
    $qa_ar = new QAAR();
    $qa_ar->setPrimaryKey($qaid);
    
    $ret = $qa_ar->updateQuestion($question, array($anwser_1, $anwser_2, $anwser_3, $anwser_4), $right);
    
    if ($ret) {
      $this->responseJSON($qa_ar, "success");
    }
    else {
      $this->responseError("unknow error", ErrorAR::ERROR_UNKNOWN);
    }
  }
  
  /**
   * 用户回答问题的接口
   */
  public function actionAnswer() {
    $request = Yii::app()->getRequest();
    
    if (!$request->isPostRequest) {
      return $this->responseError("http error", ErrorAR::ERROR_HTTP_VERB_ERROR);
    }
    
    $qaid = $request->getParam("qaid");
    $answer_id = $request->getParam("answer");
    
    $user_qa_ar = new UserQAAR();
    $user_qa_ar->answer($qaid, $answer_id);
    $data = array(
        "is_right" => $user_qa_ar->is_right,
        "new_achivement" => $user_qa_ar->has_new_achivement
    );
    
    $this->responseJSON($data, "success");
  }
}

