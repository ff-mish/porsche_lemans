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
    $saveteam = $request->getPost("saveteam");
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

        if ($saveteam != "true") {
          print "save team";
          continue;
        }

        // 第三，保存用户发的微博
        if ($userAr) {
          $uuid = $status["idstr"];
          $content = $status["text"];
          $thirdpart_date = date("Y-m-d H:m:s", strtotime($status["created_at"]));
          $cond = array("condition" => "uuid=:uuid", "params" => array(":uuid" => $uuid ));
          $found = TwitteAR::model()->find($cond);
          if ($found) {
            $found->cdate = $thirdpart_date;
            $found->save();
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
    $lenght_of_race = 13.6;
    $output = '{"status":0,"message":"success","data":{"twitter":{"name":"Twitter","distance":0,"lap":0,"speed":214,"typeIndex":1,"rankings":0},"weibo":{"name":"Weibo","distance":0,"lap":0,"speed":246,"typeIndex":0,"rankings":2}}}';
    
    $output = json_decode($output, true);

    $twitter = &$output["data"]["twitter"];

    //$start_date_seconds = strtotime("-1 day" ,strtotime(date("Y-m-d 15:00:00")));
    $start_date_seconds = strtotime(date("Y-m-d 15:00:00"));
    $end_date_seconds =  time();
    if ($start_date_seconds > $end_date_seconds) {
      $start_date_seconds = strtotime("-1 day" ,strtotime(date("Y-m-d 15:00:00")));
    }
    $hour = ( $end_date_seconds - $start_date_seconds ) / 3600 ;

    $twitter["distance"] = round($twitter["speed"] * $hour);
    $twitter["lap"] = round($twitter["distance"] / $lenght_of_race);

    $weibo = &$output["data"]["weibo"];
    $weibo["distance"] = round($weibo["speed"] * $hour);
    $weibo["lap"] = round($weibo["distance"] / $lenght_of_race);

    print(json_encode($output));

    return;

    $time_now = time();
    $time_start = strtotime(Yii::app()->params["startTime"]);
    $hour = ceil(($time_now - $time_start) / 3600);
    
    
    // weibo
    // 第一步，把所有的Team 的数据拿出来
    $sql = "select DISTINCT score_team.tid as tid, score_team.* from score_team left join teams on  teams.tid = score_team.tid left join users on users.uid = teams.owner_uid where users.from = '".UserAR::FROM_WEIBO."' ORDER BY average DESC limit 0, 30";
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
    $sql = "select DISTINCT score_team.tid as tid, score_team.* from score_team left join  teams on  teams.tid = score_team.tid left join users on users.uid = teams.owner_uid where users.from = '".UserAR::FROM_TWITTER."' ORDER BY average DESC limit 0, 30";
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
    $twittr_distance = $twitter_speed * $hour;
    
    // 圈数
    $twitter_lap = round($twittr_distance / $lenght_of_race, 0);
    
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
      $time_now = time();
      $time_start = strtotime(Yii::app()->params["startTime"]);
    $hour = ceil(($time_now - $time_start) / 3600);
    
    $user_team_tid = $team->tid;
    
    $lenght_of_race = 13.6;
    $fromes = array(UserAR::FROM_WEIBO, UserAR::FROM_TWITTER);

    $team_total_query = new CDbCriteria();
    $team_total_query->addCondition("status=:status");
    $team_total_query->params[":status"] = 1;
    $total_team = TeamAR::model()->count($team_total_query) + 8000;
      
    // 再把所有的组拿出来
    $teams = array();
    $all_teams_score = array();
    $crt_index = 0;
    $total_weibo = 0;
    $total_twitter = 0;
    
    $teams_in = array();
    // 先把每组速度汇总数据拿出来
    //$sql = "select teams.name as name, score_team.*,  sum(average) as average_total, count(score_team.tid) as team_total from score_team left join  teams on  teams.tid = score_team.tid left join users on users.uid = teams.owner_uid where users.from = '".$from."' group by teams.tid ORDER BY average_total DESC";
    $sql = "select teams.name as name, users.from as user_from, score_team.*,  sum(average) as average_total, count(score_team.tid) as team_total from score_team left join  teams on  teams.tid = score_team.tid left join users on users.uid = teams.owner_uid  where teams.status = 1 group by teams.tid ORDER BY average_total DESC";
    $command = Yii::app()->db->createCommand($sql);
    $results = $command->queryAll();

    foreach ($results as $index => $result) {
      
      if ($result["user_from"] == UserAR::FROM_WEIBO) {
        $total_weibo += 1;
      }
      else {
        $total_twitter += 1;
      }
      
      $total_average = $result["average_total"] + 0.1;
      $total = $result["team_total"];

      // 速度
      $speed = ceil($total_average / $total);

      // 距离
      
      $distance = $speed * $hour;

      // Lap
      $lap = ceil($distance / $lenght_of_race);

      // 组名
      $name = $result["name"];
      $all_teams_score[] = array(
          "distance" => $distance,
          "team" => $name,
          "id" => $result["tid"],
          "speed" => $speed,
          "lap" => $lap,
          "typeIndex" => $result["user_from"] == UserAR::FROM_WEIBO ? 0 : 1
      );
    }
    
    
    // 排序 team score
    usort($all_teams_score, "sort_team_data");
    
    foreach ($all_teams_score as $index => &$team_score) {
      $team_score["ranking"] = $index + 1;
    }
    
    // 当前位置
    foreach ($all_teams_score as $key => $team_score) {
      if ($team->tid == $team_score["id"]) {
        $crt_index = $key + 1;
      }
    }
    
    $crt_team = $all_teams_score[$crt_index];
    
    $request = Yii::app()->getRequest();
    $type = $request->getParam("type", "team");
    
    if ($type == "team") {
      // 再依次拿出前50条 后50条
     $first_index = $crt_index - 50;
     if ($first_index < 0) {
       $first_index = 0;
       $least_num = 50 - $crt_index;
     }
     else {
       $first_index = $crt_index - 50;
       $least_num = 0;
     }
     
     $last_index = $crt_index + 50;
     if ($last_index >= count($all_teams_score)) {
       $last_index = count($all_teams_score) - 1;
       $first_num = $crt_index + 50 - count($all_teams_score) + 1;
     }
     else {
       $first_num = 0;
     }
     
     if ($least_num) {
       $len = $crt_index - $first_index;
       $before = array_splice($all_teams_score, $first_index, $len + 1);
       $last_index = $last_index + $least_num;
     }
     else {
       $len = $crt_index - $first_index;
       $before = array_splice($all_teams_score, $first_index, $len + 1);
     }
     
     $len = $last_index - $crt_index;
     $after = array_splice($all_teams_score, $crt_index, $len + 1);
     
     $ret = array_merge($before, array($crt_team), $after);
     $teamAr = new TeamAR();
     // 获取每个队的 人数总数
     foreach ($ret as &$team) {
       $tid = $team["id"];
       $team["player"] = $teamAr->getMemberCount($tid);
     }
     
     $this->responseJSON($ret, "success", array("twitter_total" => $total_twitter, "weibo_total" => $total_team - $total_twitter, "user_tid"=> $user_team_tid));
    }
    else {
      $ret = array_splice($all_teams_score, 0, 101);
      $teamAr = new TeamAR();
     // 获取每个队的 人数总数
     foreach ($ret as &$team) {
       $tid = $team["id"];
       $team["player"] = $teamAr->getMemberCount($tid);
     }
      $this->responseJSON($ret, "success", array("twitter_total" => $total_twitter, "weibo_total" => $total_team - $total_twitter, "user_tid"=> $user_team_tid));
    }
  }

  public function actionEarth() {
    $sql = "SELECT count(twittes.uid) as total, users.uid as user_uid, users.lat as lat, users.lng as lng, users.from as `from` from twittes left join users on users.uid=twittes.uid  ";
    
    header("Content-Type: application/json; charset=utf8");
    $hour = 24;
    $ret_data = array();
    $start_date = "2014-06-14 21:00:00";
       // 然后补齐数据
    $query = new CDbCriteria();
    $query->addCondition("lat", "", "<>");
    $all_users = UserAR::model()->findAll($query);
    
    for ($i = 1; $i <= 24; $i++) {
      $uids = array();
      $end_date = date("Y-m-d H:i:s", strtotime($start_date) + 60 * 60 * $i);
      $new_sql = $sql. " WHERE users.lat <> '' AND users.lng <> '' AND  twittes.cdate >= '"."$start_date"."' AND twittes.cdate < '". $end_date."' group by twittes.uid";
      
      $rows = Yii::app()->db->createCommand($new_sql)->queryAll();
      
      $new_row = array();
      foreach ($rows as $row) {
        $new_row[$row["user_uid"]][] = (float)($row["lat"]);
        $new_row[$row["user_uid"]][] = (float)($row["lng"]);
        $new_row[$row["user_uid"]][] = $this->getLength($row["total"]);
        $new_row[$row["user_uid"]][] = $row["from"] == "weibo" ? 0: 1;
      }

      foreach ($all_users as $all_user) {
        if (!isset($new_row[$all_user->uid])) {
          $new_row[$all_user->uid][] = (float)round($all_user->lat, 2);
          $new_row[$all_user->uid][] = (float)round($all_user->lng, 2);
          $new_row[$all_user->uid][] = 0;
          $new_row[$all_user->uid][] = $all_user->from == "weibo" ? 0: 1;
        }
      }
      
      ksort($new_row);
      $new_new_row = array();
      $new_row = array_values($new_row);
      foreach ($new_row as $new_row_t) {
        $new_new_row = array_merge($new_new_row, $new_row_t);
      }
      
      $ret_data[] = array((string)($i), $new_new_row);
    }
    
    $this->responseJSON(($ret_data), "succes");
  }
  
  function getLength ($total) {
    $max_twittes = 800;
    $max_length = 1;
    $d = round($max_length / $max_twittes, 5);
    if ($d > 1) {
      $d = 0.9;
    }
    
    return $total * $d;
  }
  
  // 计算每组的数据
  public function actionTeamdata() {
    // 先把当前用户的组
    $user = UserAR::crtuser(TRUE);
    $team = $user->team;
    
    $lenght_of_race = 13.6;
    $fromes = array(UserAR::FROM_WEIBO, UserAR::FROM_TWITTER);
    
    // 再把所有的组拿出来
    $teams = array();
    $all_teams_score = array();
    $crt_index = 0;
    $total_weibo = 0;
    $total_twitter = 0;
    
    $teams_in = array();
    // 先把每组速度汇总数据拿出来
    //$sql = "select teams.name as name, score_team.*,  sum(average) as average_total, count(score_team.tid) as team_total from score_team left join  teams on  teams.tid = score_team.tid left join users on users.uid = teams.owner_uid where users.from = '".$from."' group by teams.tid ORDER BY average_total DESC";
    $sql = "select teams.name as name, users.from as user_from, score_team.*,  sum(average) as average_total, count(score_team.tid) as team_total from score_team left join  teams on  teams.tid = score_team.tid left join users on users.uid = teams.owner_uid group by teams.tid ORDER BY average_total DESC";
    $command = Yii::app()->db->createCommand($sql);
    $results = $command->queryAll();

    foreach ($results as $index => $result) {
      
      if ($result["user_from"] == UserAR::FROM_WEIBO) {
        $total_weibo += 1;
      }
      else {
        $total_twitter += 1;
      }
      
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

      $all_teams_score[] = array(
          "distance" => $distance,
          "rankings" => $ranking,
          "team" => $name,
          "id" => $result["tid"],
          "speed" => $speed,
          "lap" => $lap,
          "typeIndex" => $result["user_from"] == UserAR::FROM_WEIBO ? 0 : 1
      );
    }
    
    // 排序 team score
    usort($all_teams_score, "sort_team_data");
    
    $request = Yii::app()->getRequest();
    $type = $request->getParam("type", "team");
    
    $ret = array_splice($all_teams_score, 0,100);
    
    $this->responseJSON(array(
        "teams" => $ret,
        "twitter_total" => $total_twitter,
        "weibo_total" => $total_weibo
    ), "success");
  }
}

// 
function sort_team_data($a, $b) {
    if ($a["distance"] == $b["distance"]) {
        return 0;
    }
    return ($a["distance"] < $b["distance"]) ? 1 : -1;
}

