<?php

Yii::import("ext.yii-twitter-OAuth.STwitter");

class TwitterCommand extends CConsoleCommand {
  
  public function init() {
    $_SESSION["access_token"] = SystemAR::get("twitter_token");
  }
  
  public function actionUpdateaccount() {
    $query = new CDbCriteria();
    $query->addCondition(UserAR::model()->getTableAlias().".from=:from");
    $from = UserAR::FROM_WEIBO;
    $query->params[":from"] = $from;
    
    $users = UserAR::model()->findAll($query);
    foreach ($users as $user) {
      $ret = Yii::app()->twitter->user_show($user->uuid);
      $friends = $ret["followers_count"];
      $user->friends = $friends;
      $user->save();
      
      print "User: [".$user->name."] has updated. \r\n";
    }
  }
  
  public function actionOfficeweibo() {
    $timelines = Yii::app()->twitter->user_timeline_by_name(Yii::app()->params["porsche_twitter_name"]);
    
    $statuses = $timelines;
    foreach ($statuses as $status) {
      $user = $status["user"];
      $id_str = $user["id_str"];
      $from = UserAR::FROM_TWITTER;
      $twitter_name = $user["screen_name"];
      $location = $user["location"];
      $friends = $user["followers_count"];
      $profile_msg = $user["description"];
      $avatar = $user["profile_image_url"];
      
      // 查找数据库， 调查用户是否已经被自动存入
      $cond = array("condition" => "uuid=:uuid AND `from`=:from", 
          "params" => array(":uuid" => $id_str, ":from" => $from));
      $userAr = UserAR::model()->find($cond);
      if ($userAr) {
        print "time: ". date("Y-m-d H:m:s"). ': user [ '. $twitter_name.' ] has existed already';
      }
      else {
        $userAr = new UserAR();
        $userAr->uuid = $id_str;
        $userAr->location = $location;
        $userAr->avatar = $avatar;
        $userAr->name = $twitter_name;
        $userAr->from = $from;
        $userAr->profile_msg = $profile_msg;
        $userAr->friends = $friends;
        $userAr->status = UserAR::STATUS_AUTO_JOIN;
        
        $userAr->save();
        
        print "time: ". date("Y-m-d H:m:s"). ": user [ ". $twitter_name. " ] being to insert system.\r\n";
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
        $uuid = $status["id_str"];
        $content = $status["text"];
        $cond = array("condition" => "uuid=:uuid", "params" => array(":uuid" => $uuid ));
        $found = TwitteAR::model()->find($cond);
        if ($found) {
          print "time: ". date("Y-m-d H:m:s"). ": content [ ". $content . ' ] has existed already uuid: ['.$uuid.']'."\r\n";
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
          $entities = $status["entities"];
          $ref_medias = array();
          if (isset($entities["media"]) && $entities["media"]) {
            foreach ($entities["media"] as $media) {
              $ref_medias[] = $media["media_url"];
            }
          }
          $twitteAr->thirdpart_ref_media = implode("||", $ref_medias);

          $twitteAr->save();

          print "time: ". date("Y-m-d H:m:s"). ": content [ ". $content . ' ] being to insert system.'. "\r\n";
        }
      }
      else {
        print "time: ". date("Y-m-d H:m:s"). "unknow error\r\n";
      }
    }
  }
  
  public function actionSearchtag() {
    // 搜索的关键词
    $keyword = '#'.Yii::app()->params["search_twitter_topic"];
    $timelines = Yii::app()->twitter->search_topic($keyword, array("result_type" => "rencent", "count" => 100));
    if (!isset($timelines["statuses"])) {
      return print_r($timelines);
    }
    $statuses = $timelines["statuses"];
    foreach ($statuses as $status) {
      $user = $status["user"];
      $id_str = $user["id_str"];
      $from = UserAR::FROM_TWITTER;
      $twitter_name = $user["screen_name"];
      $location = $user["location"];
      $friends = $user["followers_count"];
      $profile_msg = $user["description"];
      $avatar = $user["profile_image_url"];
      
      // 查找数据库， 调查用户是否已经被自动存入
      $cond = array("condition" => "uuid=:uuid AND `from`=:from", 
          "params" => array(":uuid" => $id_str, ":from" => $from));
      $userAr = UserAR::model()->find($cond);
      if ($userAr) {
        print "time: ". date("Y-m-d H:m:s"). ': user [ '. $twitter_name.' ] has existed already';
      }
      else {
        $userAr = new UserAR();
        $userAr->uuid = $id_str;
        $userAr->location = $location;
        $userAr->avatar = $avatar;
        $userAr->name = $twitter_name;
        $userAr->from = $from;
        $userAr->profile_msg = $profile_msg;
        $userAr->friends = $friends;
        $userAr->status = UserAR::STATUS_AUTO_JOIN;
        
        $userAr->save();
        
        print "time: ". date("Y-m-d H:m:s"). ": user [ ". $twitter_name. " ] being to insert system.\r\n";
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
        $uuid = $id_str;
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
          $twitteAr->is_from_thirdpart = 1;
          
          // entities media
          $entities = $status["entities"];
          $ref_medias = array();
          if (isset($entities["media"]) && $entities["media"]) {
            foreach ($entities["media"] as $media) {
              $ref_medias[] = $media["media_url"];
            }
          }
          $twitteAr->thirdpart_ref_media = implode("||", $ref_medias);

          $twitteAr->save();

          print "time: ". date("Y-m-d H:m:s"). ": content [ ". $content . ' ] being to insert system.'. "\r\n";
        }
      }
      else {
        print "time: ". date("Y-m-d H:m:s"). "unknow error\r\n";
      }
    }
  }
}