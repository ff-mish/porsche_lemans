<?php

class IndexController extends Controller {
  
  public $layout ='/layouts/default';
  
  public $user = "admin";
  public $password = "pass_ord20#4";
  
  public $allow_videos = array("video/mp4");
  
  public $allow_images = array("image/png", "image/jpeg", "image/jpg", "image/gif");
  
  public $allow_teaser_image = array("image/png", "image/jpeg");
  
  public function isLogin() {
    return Yii::app()->session["admin_login"] == TRUE;
  }
  
  public function init() {
    parent::init();
  }
  
  // Action 之前需要判断登录情况
  public function beforeAction($action) {
    // 没有登录情况下访问其他页面 要强制重定向到登录页面
    if ($action->id != "index" && !$this->isLogin()) {
      $this->redirect(array("index"));
    }
    // 用户已经登录情况下 又到后台管理页面 则自动重定向到 qa 页面
    else if ($action->id == "index" && $this->isLogin()) {
      $this->redirect(array("qa"));
    }
    return parent::beforeAction($action);
  }
  
  public function actionIndex() {
    $request = Yii::app()->getRequest();
    if (!$request->isPostRequest) {
      $this->render("index");
    }
    else {
      $user = $request->getPost("user");
      $password = $request->getPost("password");
      if (!$user || !$password) {
        $this->render("index");
      }
      else {
        if ($user == $this->user && $password == $this->password) {
          Yii::app()->session["admin_login"] = TRUE;
          $this->redirect(array("qa"));
        }
        else {
          $this->render("index", array("error" => Yii::t("lemans", "wrong password or user name")));
        }
      }
    }
  }
  
  // QA 列表
  public function actionQa() {
    $request = Yii::app()->getRequest();
    
    if ($request->isPostRequest) {
      $question = $request->getPost("question");
      $anwsers = $request->getPost('q');
      $right = $request->getPost("right");
      $qaid = $request->getPost("qaid");
      
      $start = 65;
      $code = ord($right);
      $right = $code - 64;
      if (!($right > 0 && $right < 5)) {
        $error = array("right" => "正确答案必须从A, B , C , D 选择");
        return $this->responseError("error", 500, $error);
      }
      
      $qaAr = new QAAR();
      // 编辑
      if ($qaid) {
        $qaAr = QAAR::model()->findByPk($qaid);
        $qaAr->updateQuestion($question, $anwsers, $right);
        $this->responseJSON(array(), "success");
      }
      // 新增
      else {
        $ret = $qaAr->addNewQuestion($question, $anwsers, $right);
        if ($ret) {
          $this->responseJSON(array(), "success");
        }
        else {
          $errors = $qaAr->getErrors();
          return $this->responseError("error", 500, $errors);
        }
      }
    }
    else {
      // 默认按照回答数排序
      $query = new CDbCriteria();
      $query->order = "answered DESC";
      $qa_list = QAAR::model()->findAll($query);

      $this->render("qa", array("list" => $qa_list));
    }
  }
  
  public function actionTeam() {
    $request = Yii::app()->getRequest();
    $page = $request->getParam("page", 1);
    $limit = 10;
    $offset = ($page - 1 ) * $limit;
    $total_sql = "select count(*) as count from (select user_team.*, teams.name as name, teams.cdate as cdate, teams.owner_uid as owner_uid, teams.achivements_total as achivements_total from (select max(cdate) as max_date, score_team.tid as tid, score_team.speed as speed, score_team.assiduity as assiduity, score_team.impact as impact, score_team.average as average, score_team.quality as quality from score_team group by tid ) as user_team left join teams on teams.tid=user_team.tid) as team_data  where team_data.name is not NULL";
    $sql = "select * from (select user_team.*, teams.name as name, teams.cdate as cdate, teams.owner_uid as owner_uid, teams.achivements_total as achivements_total from (select max(cdate) as max_date, score_team.tid as tid, score_team.speed as speed, score_team.assiduity as assiduity, score_team.impact as impact, score_team.average as average, score_team.quality as quality from score_team group by tid ) as user_team left join teams on teams.tid=user_team.tid) as team_data  where team_data.name is not NULL";
    
    $sql .= " limit $offset, $limit";
    $total_command = Yii::app()->db->createCommand($total_sql);
    $total = $total_command->queryAll();
    $total = $total[0]["count"];
    
    $page_total = $total % $limit == 0 ? intval($total / $limit) : intval($total / $limit) + 1;
    
    $command = Yii::app()->db->createCommand($sql);
    $results = $command->queryAll();
    
    $this->render("team", array("teams" => $results));
  }
  
  public function actionFuel() {
    $request = Yii::app()->getRequest();
    // 添加新的 Fuel
    if ($request->isPostRequest) {
      // media 文件
      $media_file = CUploadedFile::getInstanceByName("media");
      $media_type = $media_file->getType();
      if (in_array($media_type, $this->allow_videos) && in_array($media_type, $this->allow_images)) {
        return $this->responseError("file error", 500, array("media" => "文件不符合要求"));
      }
      
      $is_image = TRUE;
      if (in_array($media_type, $this->allow_videos)) {
        $is_image = FALSE;
      }
      
      // 还有Teaser Image
      if (!$is_image) {
        $teaser_image_file = CUploadedFile::getInstanceByName("teaser_image");
        if (!$teaser_image_file) {
          return $this->responseError("file error", 500, array("teaser_image" => "文件是必须的"));
        }
        $teaser_image_type = $teaser_image_file->getType();
        if (!in_array($teaser_image_type, $this->allow_teaser_image)) {
          return $this->responseError("file error", 500, array("teaser_image" => "文件不符合要求"));
        }
      }
        
      $title = $request->getPost("title");
      $description = $request->getPost("description");

      if ($is_image) {
        $type = MediaAR::MEDIA_IMAGE;
      }
      else {
        $type = MediaAR::MEDIA_VIDEO;
      }

      $new_name = MediaAR::new_uri($media_type, $type);
      $save_to = Yii::app()->params["uploadedPath"].'/'. $new_name;
      $media_file->saveAs($save_to);

      $uri = str_replace(realpath(Yii::app()->basePath.'/../'), "", $save_to);

      $media_ar = new MediaAR();
      $media_ar->saveNew($uri, $type, "", "", $title, $description);

      // 视频上传后 需要保存视频的 预览图
      if ($type == MediaAR::MEDIA_VIDEO) {
        $new_name = MediaAR::new_uri($teaser_image_type, MediaAR::MEDIA_IMAGE);
        $save_to = Yii::app()->params["uploadedPath"].'/'. $new_name;
        $teaser_image_file->saveAs($save_to);
        $uri = str_replace(realpath(Yii::app()->basePath.'/../'), "", $save_to);
        $media_ar->teaser_image = $uri;
        $media_ar->save();
      }

      $this->responseJSON($media_ar, "success");
    }
    // 查看列表
    else {
      $page = $request->getParam("page", 1);
      $type = $request->getParam("type", "image");
      
      $mediaAr = new MediaAR();
      $medias = $mediaAr->getMedias($type, $page);
      $this->render("fuel", array("medias" => $medias));
    }
  }
  
  public function actionEmail() {
    $request = Yii::app()->getRequest();
    $page = $request->getParam("page", 1);
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    $sql = "select user_mails.mail,users.from, users.name, user_mails.cdate from user_mails left join users on users.uid=user_mails.uid";
    
    // 分页数据
    $total_sql = "select count(*) as count from user_mails left join users on users.uid=user_mails.uid";
    $total = Yii::app()->db->createCommand($total_sql)->queryAll();
    $total = $total[0]["count"];
    $page_total = $total % $limit == 0 ? intval($total / $limit) : intval($total / $limit) + 1;
    
    // 查询列表
    $sql .= " limit $offset, $limit";
    $results = Yii::app()->db->createCommand($sql)->queryAll();
    
    $this->render("email", array("emails" => $results));
  }
  
  public function getUserTeamInfo($tid) {
    $sql = "select users.from, teams_data.tid as tid, teams_data.location as location ,group_concat(users.name) as names from user_teams left join users on users.uid=user_teams.uid left join (select teams.tid, users.location from teams left join users on users.uid=teams.owner_uid) as teams_data on teams_data.tid=user_teams.tid WHERE teams_data.tid = :tid group by user_teams.tid";
    
    $command = Yii::app()->db->createCommand($sql);
    $results = $command->queryAll(true, array(":tid" => $tid));
    
    if ($results) {
      return $results[0];
    }
  }
  
  public function getTeamQuestionCount($tid) {
    return "100";
  }
}
