<?php
Yii::import('ext.sinaWeibo.SinaWeibo',true);
/**
 * @author Jackey <jziwenchen@gmail.com>
 * 系统级别的Controller 比如 404  / 错误 
 */
class WebController extends Controller {
  public function init() {
    parent::init();
  }
  
  public function actionError() {
    $error = Yii::app()->errorHandler->error;
    if (!$error) {
      $event = func_get_arg(0);
      if ($event instanceof CExceptionEvent) {
        return $this->responseError($event);
      }
    }
    $this->responseError($error);
  }
  
  // 生成邀请链接时候我们在URL上加了密，所以这里调用接口解密
  public function actionDecryptionURL() {
    $requst = Yii::app()->getRequest();
    $d = $requst->getParam("d", FALSE);
    
    if (!$d) {
      return $this->responseError("invalid params", ErrorAR::ERROR_MISSED_REQUIRED_PARAMS);
    }
    $user = UserAR::crtuser();
    if (!$user) {
      //return $this->responseError("user not login", ErrorAR::ERROR_NOT_LOGIN);
    }
    
    if ($d) {
      $userAr = new UserAR();
      $data = $userAr->decryptionInvitedData($d);
      // 还需要判断邀请数据是否有效
      
      Yii::app()->session["invited_data"] = $data;
    }
    
    $this->responseJSON($data, "success");
  }
  
  public function actionWelcome() {
    $request = Yii::app()->getRequest();
    $d = $request->getParam("d");
    if ($d) {
      $userAr = new UserAR();
      $data = $userAr->decryptionInvitedData($d);
      Yii::app()->session["invited_data"] = $data;
    }
    $this->responseJSON("hello, lemans", "");
  }
  
  public function actionInittoken() {
    $this->render("inittoken");
  }
  
  public function actionTime() {
    $time = date("Y-m-d H:i:s");
    $startTime = Yii::app()->params["startTime"];
    
    $this->responseJSON(array("time_now" => $time, "time_start" => $startTime), "success");
  }

  public function actionTranslation() {
    $source = Yii::app()->getComponent("messages");
    $language = Yii::app()->getLanguage();
    $language_file_path = $source->getMessageFile("lemans", $language);

    print $language_file_path;
    die();
  }
  
  // 从Weibo 抓数据后 推送数据到对方接口
  public function actionCronFetchTwitte() {
    $token = SystemAR::get("weibo_token");
    $weibo_api = new SinaWeibo_API(WB_AKEY, WB_SKEY, $token);
    
    $ret = $weibo_api->search_topic(Yii::app()->params["search_weibo_topic"], 50);
    
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
    
    header("content-type:text/html; charset=utf-8");
    print_r($response);
    die();
  }
  
  // 接受新浪抓过来的微博  － 仅供 代理服务器 接受数据用
  public function actionCronNewTwitte() {
    $request = Yii::app()->getRequest();
    if (!$request->isPostRequest) {
      return $this->responseError("error", 500);
    }
    
    $data = $request->getPost("data");
    if (!$data) {
      return $this->responseError("error", 500);
    }
    
    $statuses = json_decode($data, TRUE);
    
    $from = $request->getPost("from");
    
    if ($from == UserAR::FROM_WEIBO) {
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
          $thirdpart_date = date("Y-m-d H:m:s", strtotime($status["created_at"]));
          $cond = array("condition" => "uuid=:uuid", "params" => array(":uuid" => $uuid ));
          $found = TwitteAR::model()->find($cond);
          $found->cdate = $thirdpart_date;
          $found->save();
          if ($found) {
            print "time: ". date("Y-m-d H:m:s"). ": content [ ". $content . ' ] has existed already'."\r\n";
          }
          else {
            $content = $status["text"];
            $uuid = $status["idstr"];
            $uid = $userAr->uid;
            $type = $userAr->from;

            $twitteAr = new TwitteAR();
            $twitteAr->uid = $uid;
            $twitteAr->content = $content;
            $twitteAr->uuid = $uuid;
            $twitteAr->type = $type;
            $twitteAr->is_from_thirdpart = 1;
            $twitteAr->thirdpart_date  = $thirdpart_date;

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
  
  // 赛道数据
  public function actionRacedata() {
    $time_now = time();
    $time_start = strtotime(Yii::app()->params["startTime"]);
    $hour = ceil(($time_now - $time_start) / 3600);
    
    $lenght_of_race = 13.6;
    // weibo 
    // 第一步，把所有的Team 的数据拿出来
    $sql = "select score_team.* from score_team left join  teams on  teams.tid = score_team.tid left join users on users.uid = teams.owner_uid where users.from = '".UserAR::FROM_WEIBO."'";
    $command = Yii::app()->db->createCommand($sql);
    
    $teamScores = $command->queryAll();
    $total = count($teamScores);
    // 然后计算平均速度
    $total_average = 0.1;
    foreach ($teamScores as $teamScore) {
      $total_average += $teamScore['average'];
    }
    
    // 速度
    $speed = ceil($total_average / $total);
    
    // 距离
    // 速度单位是 KM / Hours
    // 分数是30秒计算一次, 所以总共是30秒
    // 可能还要加上2秒误差
    $distance = $speed * $hour;
    
    // 圈数
    $lap = ceil($distance / $lenght_of_race);
    
    $weibo = array(
        "name"=> Yii::t("lemans" ,"Weibo"),
        "distance" => $distance,
        "lap" => $lap,
        "speed" => $speed,
        "typeIndex" => 0,
    );
    
    // ===================================================================
    // Twitter
    $sql = "select score_team.* from score_team left join  teams on  teams.tid = score_team.tid left join users on users.uid = teams.owner_uid where users.from = '".UserAR::FROM_TWITTER."'";
    $command = Yii::app()->db->createCommand($sql);
    
    $teamScores = $command->queryAll();
    $total = count($teamScores);
    // 然后计算平均速度
    $total_average = 0.1;
    foreach ($teamScores as $teamScore) {
      $total_average += $teamScore['average'];
    }
    
    // 速度 
    $twitter_speed = ceil($total_average / $total);
    
    // 距离
    // 速度单位是 KM / Hours
    // 分数是30秒计算一次, 所以总共是30秒
    // 可能还要加上2秒误差
    $twittr_distance = $speed * $hour;
    
    // 圈数
    $twitter_lap = round($distance / $lenght_of_race, 0);
    
    $twitter = array(
        "name"=> Yii::t("lemans" ,"Twitter"),
        "distance" => $twittr_distance,
        "lap" => $twitter_lap,
        "speed" => $twitter_speed,
        "typeIndex" => 1,
    );
    
    if ($twitter_speed > $speed) {
      $twitter["rankings"] = 1;
      $weibo["rankings"] = 1;
    }
    else {
      $twitter["rankings"] = 0;
      $weibo["rankings"] = 2;
    }
    
    $this->responseJSON(array("twitter" => $twitter, "weibo" => $weibo), "success");
  }
  
  public function actionTeammobiledata() {
    // 先把当前用户的组
    $user = UserAR::crtuser(TRUE);
    $team = $user->team;
    
    $lenght_of_race = 13.6;
    $fromes = array(UserAR::FROM_WEIBO, UserAR::FROM_TWITTER);
    
    // 再把所有的组拿出来
    $teams = array();
    $all_teams_score = array();
    $crt_index = 0;
    foreach ($fromes as $from) {
      $teams_in = array();
      // 先把每组速度汇总数据拿出来
      $sql = "select teams.name as name, score_team.*,  sum(average) as average_total, count(score_team.tid) as team_total from score_team left join  teams on  teams.tid = score_team.tid left join users on users.uid = teams.owner_uid where users.from = '".$from."' group by teams.tid ORDER BY average_total DESC";
      $command = Yii::app()->db->createCommand($sql);
      $results = $command->queryAll();
      
      foreach ($results as $index => $result) {
        $total_average = $result["average_total"] + 0.1;
        $total = $result["team_total"];
        
        // 速度
        $speed = ceil($total_average / $total);
        
        // 距离
        $total_seconds = $total * (30 + 2);
        $hour = $total_seconds / 3600;
        $distance = $speed * $hour;
        
        // Lap
        $lap = ceil($distance / $lenght_of_race);
        
        // 排名
        $ranking = $index + 1;
        
        // 组名
        
        // 当前位置
        $name = $result["name"];
        if ($team->tid == $result["tid"]) {
          $crt_index = count($all_teams_score);
        }
        
//        $teams_in[] = array(
//            "distance" => $distance,
//            "team" => $team,
//            "id" => $result["tid"],
//            "speed" => $speed,
//            "lap" => $lap,
//            "typeIndex" => $from == UserAR::FROM_WEIBO ? 0 : 1
//        );
        $all_teams_score[] = array(
            "distance" => $distance,
            "team" => $name,
            "id" => $result["tid"],
            "speed" => $speed,
            "lap" => $lap,
            "typeIndex" => $from == UserAR::FROM_WEIBO ? 0 : 1
        );
      }
    }
    
    // 再依次拿出前50条 后50条
    $first_index = $crt_index - 50;
    if ($first_index < 0) {
      $first_index = 0;
    } 
    $last_index = $crt_index + 50;
    if ($last_index >= count($all_teams_score)) {
      $last_index = count($all_teams_score) - 1;
    }
    
    $before = array_splice($all_teams_score, $first_index, 50);
    $after = array_splice($all_teams_score, $crt_index, 50);
    
    $this->responseJSON(array("before" => $before, "after" => $after), "success");
    
    
  }
  
  // 计算每组的数据
  public function actionTeamdata() {
    $lenght_of_race = 13.6;
    $fromes = array(UserAR::FROM_WEIBO, UserAR::FROM_TWITTER);
    
    $teams = array();
    foreach ($fromes as $from) {
      $teams_in = array();
      // 先把每组速度汇总数据拿出来
      $sql = "select teams.name as name, score_team.*,  sum(average) as average_total, count(score_team.tid) as team_total from score_team left join  teams on  teams.tid = score_team.tid left join users on users.uid = teams.owner_uid where users.from = '".$from."' group by teams.tid ORDER BY average_total DESC";
      $command = Yii::app()->db->createCommand($sql);
      $results = $command->queryAll();
      
      foreach ($results as $index => $result) {
        $total_average = $result["average_total"] + 0.1;
        $total = $result["team_total"];
        
        // 速度
        $speed = ceil($total_average / $total);
        
        // 距离
        $total_seconds = $total * (30 + 2);
        $hour = $total_seconds / 3600;
        $distance = $speed * $hour;
        
        // Lap
        $lap = ceil($distance / $lenght_of_race);
        
        // 排名
        $ranking = $index + 1;
        
        // 组名
        $team = $result["name"];
        
        $teams_in[] = array(
            "distance" => $distance,
            "team" => $team,
            "id" => $result["tid"],
            "speed" => $speed,
            "lap" => $lap,
            "typeIndex" => $from == UserAR::FROM_WEIBO ? 0 : 1
        );
      }
      $teams[$from] = $teams_in;
    }
    
    // 然后去掉分组
    $total_twitter = count($teams[UserAR::FROM_TWITTER]);
    $total_weibo = count($teams[UserAR::FROM_WEIBO]);
    
    $t_teams = array();
    $t_teams = $teams[UserAR::FROM_TWITTER] + $teams[UserAR::FROM_WEIBO];
    
    usort($t_teams, "sort_team_data");
    
    foreach ($t_teams as $key => &$t_team) {
      $t_team["rankings"] = $key + 1;
    }
    
    $ret_data = array(
        "teams" => $t_teams,
        "twitter_total" => $total_twitter,
        "weibo_total" => $total_weibo
    );
    
    $this->responseJSON($ret_data, "SUCCESS");
  }
}

// 
function sort_team_data($a, $b) {
    if ($a["distance"] == $b["distance"]) {
        return 0;
    }
    return ($a["distance"] < $b["distance"]) ? 1 : -1;
}

