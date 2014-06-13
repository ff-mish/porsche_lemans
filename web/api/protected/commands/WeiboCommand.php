<?php
Yii::import("ext.sinaWeibo.SinaWeibo_API");

class WeiboCommand extends CConsoleCommand {
  
  /**
   * @var SinaWeibo_API
   */
  private $weibo_api;
  
  private $token;
  
  public function init() {
    $this->token = SystemAR::get("weibo_token");
    $this->weibo_api = new SinaWeibo_API(WB_AKEY, WB_SKEY, $this->token);
    
    $servers_params = array(
        "REMOTE_ADDR" => "127.0.0.1",
    );
    foreach ($servers_params as $key => $param) {
      $_SERVER[$key] = $param;
    }
  }
  
  public function actionUpdateaccount() {
    $weibo_api = $this->weibo_api;
    $query = new CDbCriteria();
    $query->addCondition(UserAR::model()->getTableAlias().".from=:from");
    $from = UserAR::FROM_WEIBO;
    $query->params[":from"] = $from;
    
    $users = UserAR::model()->findAll($query);
    foreach ($users as $user) {
      $ret = $weibo_api->show_user_by_id($user->uuid);
      $friends = $ret["followers_count"];
      $user->friends = $friends;
      $user->save();
      
      print "User: [".$user->name."] has updated. \r\n";
    }
  }
  
  public function actionOfficeweibo($args) {
    $weibo_api = $this->weibo_api;
    
    $ret = $weibo_api->user_timeline_by_name(Yii::app()->params["porsche_weibo_name"], 1);
    if (!isset($ret["statuses"])) {
      return print_r($ret);
    }
    
    $self = array_shift($args);
    if ($self == "self") {
      $this->saveStatuses($ret["statuses"]);
    }
    else {
      $service_url = Yii::app()->params["service_url"];
      $url = $service_url."/api/web/cronnewtwitte";

      $ch = curl_init($url);

      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, array("from" => UserAR::FROM_WEIBO, "self" => "self", "data" => (json_encode($ret["statuses"]))));
      //curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
      curl_setopt($ch, CURLOPT_USERPWD, "lemans:porschelemans.");
      $response = curl_exec($ch);

      print_r($response);
      
      $service_url = "http://www.letustestit.eu";
      $url = $service_url."/api/web/cronnewtwitte";

      $ch = curl_init($url);

      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, array("from" => UserAR::FROM_WEIBO, "self" => "self", "data" => (json_encode($ret["statuses"]))));
      //curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
      curl_setopt($ch, CURLOPT_USERPWD, "lemans:porschelemans.");
      $response = curl_exec($ch);

      print_r($response);

      // 直接返回
      return ;
    }
  }
  
  public function actionSearchtag() {
    $weibo_api = $this->weibo_api;
    
    $page = 4;
    for ($i = 0; $i < $page; $i++) {
      $ret = $weibo_api->search_topic(Yii::app()->params["search_weibo_topic"], 50);

      // 在这里我们不做保存工作了，直接发送到远程的接入服务器 (正式服务器)
      if (!isset($ret["statuses"])) {
        return print_r($ret);
      }

      $service_url = Yii::app()->params["service_url"];
      $url = $service_url."/api/web/cronnewtwitte";

      $ch = curl_init($url);

      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, array("from" => UserAR::FROM_WEIBO, "data" => (json_encode($ret["statuses"]))));
      //curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
      curl_setopt($ch, CURLOPT_USERPWD, "lemans:porschelemans.");
      $response = curl_exec($ch);

      print_r($response);
      
      $service_url = "http://www.letustestit.eu";
      $url = $service_url."/api/web/cronnewtwitte";

      $ch = curl_init($url);

      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, array("from" => UserAR::FROM_WEIBO, "self" => "self", "data" => (json_encode($ret["statuses"]))));
      //curl_setopt($ch, CURLOPT_FOLLOWLOCATION  ,1);
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER  ,1);
      curl_setopt($ch, CURLOPT_USERPWD, "lemans:porschelemans.");
      $response = curl_exec($ch);

      print_r($response);
    }
  }
  
  /**
   * 保存状态
   */
  private function saveStatuses($statuses) {
    // 我们这里要2件事情，
    // 第一，判断发微博的人是否已经存在我们系统，如果不存在，则自动保存在我们系统
    $isExist = FALSE;
    foreach ($statuses as $status) {
      $weibo_user = $status["user"];
      $weibo_uid = $weibo_user["idstr"];
      $weibo_name = $weibo_user["screen_name"];
      $location = $weibo_user["location"];
      $friends = $weibo_user["followers_count"];
      $from = UserAR::FROM_WEIBO;
      $profile_msg = $weibo_user["description"];
      $avatar = $weibo_user["profile_image_url"];
      
      // 查找数据库， 调查用户是否已经被自动存入
      $cond = array("condition" => "uuid=:uuid AND `from`=:from", 
          "params" => array(":uuid" => $weibo_uid, ":from" => $from));
      $userAr = UserAR::model()->find($cond);
      if ($userAr) {
        print "time: ". date("Y-m-d H:m:s"). ': user [ '. $weibo_name.' ] has existed already';
      }
      else {
        $userAr = new UserAR();
        $userAr->uuid = $weibo_uid;
        $userAr->location = $location;
        $userAr->avatar = $avatar;
        $userAr->name = $weibo_name;
        $userAr->from = $from;
        $userAr->profile_msg = $profile_msg;
        $userAr->friends = $friends;
        $userAr->status = UserAR::STATUS_AUTO_JOIN;
        
        $userAr->save();
        
        $isExist = TRUE;
        
        print "time: ". date("Y-m-d H:m:s"). ": user [ ". $weibo_name. " ] being to insert system.\r\n";
      }
      
      // 第二, 查找用户的组 然后有可能自动建组
      if ($userAr) {
        $userTeamAr  = new UserTeamAR();
        $userTeam = $userTeamAr->loadUserTeam($userAr);
        // 用户如果没有组，则我们自动建组
        if (!$userTeam) {
          TeamAR::newteam(Yii::t("lemans", "New Team"), $userAr);
        }
      }
      
      // 第三，保存用户发的微博
      if ($userAr) {
        $uuid = $status["idstr"];
        $content = $status["text"];

        $cond = array("condition" => "uuid=:uuid", "params" => array(":uuid" => $uuid ));
        $found = TwitteAR::model()->find($cond);
        if ($found) {
          print "time: ". date("Y-m-d H:m:s"). ": content [ ". $content . ' ] has existed already'."\r\n";
        }
        else {
          $content = $status["text"];
          $uid = $userAr->uid;
          $type = $userAr->from;

          $twitteAr = new TwitteAR();
          $twitteAr->uid = $uid;
          $twitteAr->content = $content;
          $twitteAr->uuid = $uuid;
          $twitteAr->type = $type;
          $twitteAr->is_from_thirdpart = 0;
          
          // entities media
          if (isset($status["original_pic"])) {
            $twitteAr->thirdpart_ref_media = $status["original_pic"];
          }
          try {
            $twitteAr->save();
          }
          catch (Exception $e) {
            continue;
          }

          print "time: ". date("Y-m-d H:m:s"). ": content [ ". $content . ' ] being to insert system.'. "\r\n";
        }
      }
      else {
        print "time: ". date("Y-m-d H:m:s"). "unknow error\r\n";
      }
    }
  }
}
