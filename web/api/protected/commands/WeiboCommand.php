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
  
  public function actionSearchtag() {
    $weibo_api = $this->weibo_api;
    
    $ret = $weibo_api->public_timeline();
    $statuses = $ret["statuses"];
    
    // 我们这里要2件事情，
    // 第一，判断发微博的人是否已经存在我们系统，如果不存在，则自动保存在我们系统
    $isExist = FALSE;
    foreach ($statuses as $status) {
      $weibo_user = $status["user"];
      $weibo_uid = $weibo_user["idstr"];
      $weibo_name = $weibo_user["screen_name"];
      $location = $weibo_user["location"];
      $friends = $weibo_user["friends_count"];
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
      // 第二，保存用户发的微博
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
          $twitteAr->is_from_thirdpart = 1;
          
          // entities media
          if (isset($status["original_pic"])) {
            $twitteAr->thirdpart_ref_media = $status["original_pic"];
          }
          
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
