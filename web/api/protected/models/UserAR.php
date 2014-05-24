<?php

Yii::import("ext.sinaWeibo.SinaWeibo");
Yii::import("ext.sinaWeibo.SinaWeibo_API");

class UserAR extends CActiveRecord {
  const FROM_WEIBO = "weibo";
  const FROM_TWITTER = "twitter";
  
  const STATUS_AUTO_JOIN = 2;
  const STATUS_ENABLED = 1;
  const STATUS_DISABLED = 0;
  
  const STEP_AT_GROUP = 2;
  /**
   *
   * @var SinaWeibo
   */
  public $weibo;
  
  public $team;
  
  public $score;
  
  public function __init() {
    $this->weibo = new SinaWeibo(WB_AKEY, WB_SKEY);
    return parent::init();
  }
  public static function model($classname = __CLASS__) {
    return parent::model($classname);
  }
  
  public function primaryKey() {
    return "uid";
  }
  
  public function tableName() {
    return "users";
  }
  
  public function relations() {
    return array();
  }
  
  public function rules() {
    return array(
        array("uuid, name, from", "required"),
        array("uid, cdate, udate, lat, lng, invited_by, profile_msg, avatar, score, status, friends, location, allowed_invite", "safe"),
    );
  }
  
  /**
   * 从微博获取用户详细信息
   * @param type $uid
   */
  public static function get_weibo_user($uid = 0) {
    $access_token = Yii::app()->session["weibo_token"];
    if ($access_token) {
      $token = $access_token["access_token"];
      $uid = $access_token["uid"];
    }
    else {
      $token = FALSE;
    }
    
    if ($token && $uid) {
      $weibo_api = new SinaWeibo_API(WB_AKEY, WB_SKEY, $token);
      $user = $weibo_api->show_user_by_id($uid);
    }
    else {
      return FALSE;
    }

    return $user;
  }
  
  public static  function get_twitter_user() {
    $access_token = Yii::app()->session["twitter_token"];
    if ($access_token) {
      $uid = $access_token["user_id"];
      $twitter_user = Yii::app()->twitter->user_show($uid);
      return $twitter_user;
    }
    else {
      return FALSE;
    }
  }


  /**
   * 根据User 的第三方uuid (比如idstr 加载用户)
   * @param type $uuid
   * @param type $from
   * @return UserAR
   */
  public function load_user_by_uuid($uuid, $from = "") {
    if (!$from) {
       $from = Yii::app()->session["from"];
    }
    $conds = array(
        "condition" => 'uuid=:uuid AND `from`=:from',
        "params" => array(":uuid" => $uuid, ":from" => $from)
    );
    $users = $this->findAll($conds);
    if ($users) {
      return $users[0];
    }
    else {
      return FALSE;
    }
  }
  
  /**
   * 加密邀请数据
   * @param type $uid 邀请人
   * @param type $invited_uid 被邀请人
   */
  public function generateInvitedURL($uid, $invited_uid = "", $code = "") {
    $team = TeamAR::loadTeamByOwner($uid);
    if ($team) {
      $tid = $team->tid;
    }
    else {
      $tid = 0;
    }
    // 没有tid 就不要去邀请了
    if (!$tid) {
      return FALSE;
    }
    $data = array(
        "uid" => $uid,
        "invited_uid" => $invited_uid,
        "tid" => $tid,
        "time" => time(),
        "code" => $code
    );
    $str = serialize($data);
    $encrpted_str = $this->encrypt_decrypt("e", $str);
    $request = Yii::app()->getRequest();
    $base_url = $request->getBaseUrl(TRUE);
    $host = parse_url($base_url, PHP_URL_HOST);
    $schema = parse_url($base_url, PHP_URL_SCHEME);
    $base_host = $schema."://".$host;
    return $base_host. '?d='. base64_encode($encrpted_str);
  }
  
  
  /**
   * 解密请求数据
   * @param type $str
   * @return boolean
   */
  public function decryptionInvitedData($str) {
    $str = base64_decode($str);
    $decryption_str = $this->encrypt_decrypt("d", $str);
    if ($decryption_str) {
      $data = unserialize($decryption_str);
      return $data;
    }
    return FALSE;
  }
  
  function encrypt_decrypt($action, $string) {
     $output = false;

     $key = 'xxx _ yeh #! _ held';

     // initialization vector 
     $iv = md5(md5($key));

     if( $action == 'e' ) {
         $output = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, $iv);
         $output = base64_encode($output);
     }
     else if( $action == 'd' ){
         $output = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, $iv);
         $output = rtrim($output, "");
     }
     return $output;
  }
  
  public function login() {
    $from = Yii::app()->session["from"];
    if ($from == self::FROM_WEIBO) {
      $weibo_user = $this->get_weibo_user();
      if ($weibo_user) {
        // 获取uuid
        $uuid = $weibo_user["idstr"];
        $screen_name = $weibo_user["screen_name"];
        $avatar = $weibo_user["avatar_large"];
        $friends = $weibo_user["friends_count"];
      }
    }
    // 从Twitter 登录
    else if ($from == self::FROM_TWITTER) {
      // TODO:: 从 Twitter 登录 处理
      $twitter_user = self::get_twitter_user();
      
      // 检查Twitter 用户是否已经注册系统
      $uuid = $twitter_user->id_str;
      $screen_name = $twitter_user->screen_name;
      $avatar = $twitter_user->profile_image_url;
      $friends = $twitter_user->friends_count;
    }
    
    // 把用户从数据库加载进来
    
    $user = $this->load_user_by_uuid($uuid);
    // 用户已经注册到了我们系统了
    if ($user && $user->status == self::STATUS_ENABLED) {
      Yii::app()->session["user"] = $user;
    // 如果用户已经登录授权过了， 但是又没有建造一个team
      $userteamAr = new UserTeamAR();
      $team = $userteamAr->loadUserTeam($user);
      if (!$team && !Yii::app()->session["invited_data"]) {
        // TODO:: 我们让他去team build 页面
        // 测试代码， 我们要删除掉
        TeamAR::newteam("new team");
      }
      else {
        //
      }
    }
    // 用户是自动被加入到系统的， 这次还是属于第一次授权
    elseif ($user && $user->status == self::STATUS_AUTO_JOIN) {
      // TODO:: 是否需要前端继续授权给用户建立组和邀请用户?
      Yii::app()->session["user"] = $user;
    }
    else if (!$user) {
      // Step1, 用户如果第一次授权系统，我们要保存用户
      $new_userar = new UserAR();
      $invited_data = Yii::app()->session["invited_data"];
      if ($invited_data) {
        $inviter = $invited_data["uid"];
      }
      else {
        $inviter = 0;
      }
      $attributes = array(
          "name" => $screen_name,
          "from" => $from,
          "uuid" => $uuid,
          "invited_by" => $inviter,
          // TODO:: 是否需要实现用户个人说明功能？
          "profile_msg" => "", 
          "avatar" => $avatar,
          "friends" => $friends,
          "status" => self::STATUS_ENABLED
      );
      // 等于0 说明没有邀请者，我们就设置为 接受邀请了
      if ($inviter === 0) {
        $attributes["allowed_invite"] = 1;
      }
      foreach ($attributes as $name => $value) {
        $new_userar->{$name} = $value;
      }
      if ($new_userar->save()) {
        Yii::app()->session["user"] = $new_userar;
      }

      // Step2, 用户保存成功后，需要把用户自动分组，分组是根据 Invite uid 来分配的.
      // 如果用户有邀请者，则将用户加入到组中去
      if ($inviter) {
        // 这里分情况处理
        $invited_uid = $invited_data["invited_uid"];
        if (in_array($uuid, $invited_uid)) {
            // 取消自动加入小组
        }
        // 如果用户没有邀请者 我们自动生成一个team
        else {
          TeamAR::newteam("new team");
        }
      }
      // 否则用户自己创建一个组
      else {
        //TODO:: 用户登录成功后 有没有必要自动生成一个组???
          TeamAR::newteam("new team");
      }
    }
  }
  
  /**
   * 返回当前登录的用户
   * @return UserAR
   */
  public static function crtuser($relation = FALSE) {
    $user =  Yii::app()->session["user"];
    
    // 再获取Team
    if ($user && $relation == TRUE) {
      
      $team_ar = new UserTeamAR();
      $team_user = $team_ar->loadUserTeam($user);

      if ($team_user) {
        $user->team = $team_user->team;
        if ($user->team) {
          $user->team->loadMembers();
        }
      }
      else {
        $user->team = NULL;
      }
    }
    
    return $user;
  }
  
  /**
   * 发送一条邀请微博
   * @param type $weibo
   * @return boolean
   */
  public function post_invite_tweet($msg) {
    // 发一个微博邀请 （其实是一个微博 用@用户名方式）
    $user = UserAR::crtuser();
    if ($user->from == self::FROM_WEIBO) {
      $access_token = Yii::app()->session["weibo_token"];
      if ($access_token) {
        $token = $access_token["access_token"];
        $uid = $access_token["uid"];
      }
      else {
        $token = FALSE;
      }
      if (!$token) {
        return FALSE;
      }
      $weibo_api = new SinaWeibo_API(WB_AKEY, WB_SKEY, $token);
      // 获取邀请者的资料
      $invited_user = array();
      $weibo_users = UserAR::getAtScreenNameFromMsg($msg);
      foreach ($weibo_users as $weibo_user) {
        $invited_user[] = $weibo_user["idstr"];
      }
      $user = $this->load_user_by_uuid($uid);
      $code = InviteLogAR::newInviteCode();
      $short_url = $weibo_api->short_url_shorten($this->generateInvitedURL($user->uid, $invited_user, $code));
      $url = $short_url["urls"][0]["url_short"];
      $ret = $weibo_api->update($msg.' '. $url);
      
      // 发送邀请后，我们把邀请数据保存在数据库
      $team = TeamAR::loadTeamByOwner($user->uid);
      if ($team) {
        InviteLogAR::logInvite($user->uid, $invited_user, $team->tid, $code);
      }
      
      return $ret;
    }
    // 发一个Twitter 邀请
    else {
      $invited_user = array();
      try {
        $twitter_users = UserAR::getAtScreenNameFromMsg($msg);
      }
      catch (Exception $e) {
        return FALSE;
      }
      foreach ($twitter_users as $twitter_user) {
        $invited_user[] = $twitter_user->id_str;
      }
      
      $uuid = $user->uuid;
      $user = $this->load_user_by_uuid($uuid);
      $code = InviteLogAR::newInviteCode();
      $url = $this->generateInvitedURL($user->uid, $invited_user, $code);
      $short_url = Yii::app()->shorturl->shorten($url);
      // Status 应该超过有  140 个  char 
      $ret = Yii::app()->twitter->status_update($msg. ' '. $short_url);
      
      // 发送邀请后，我们把邀请数据保存在数据库
      $team_ar = new UserTeamAR();
      $team_user = $team_ar->loadUserTeam($user);
      $team = $team_user->team;
      if ($team) {
        InviteLogAR::logInvite($user->uid, $invited_user, $team->tid, $code);
      }
      
      return $ret;
      
    }
  }
  
  public function beforeSave() {
    
    // 自动添加时间
    if ($this->uid) {
      $this->udate = date("Y-m-d H:i:s");
    }
    else {
      $this->udate = $this->cdate = date("Y-m-d H:i:s");
    }
    
    return TRUE;
  }
  
  // 返回微博登录链接
  public static function weibo_login_url() {
    $weibo = new SinaWeibo(WB_AKEY, WB_SKEY);
    return $weibo->getAuthorizeURL(WB_CALLBACK_URL);
  }
  
  public static  function twitter_login_url() {
    return Yii::app()->twitter->signUrl;
  }


  /**
   * 用户创建组
   */
  public function user_create_team($name) {
    $teamAr = new TeamAR();
    $teamAr->name = $name;
    $teamAr->owner_uid = $this->uid;
    
    if ($teamAr->save()) {
      return $teamAr;
    }
    return FALSE;
  }
  
  /**
   * 用户加入某个小组
   * @param type $uid 队长的 uid
   */
  public function user_join_team($uid) {
    $query = new CDbCriteria();
    $query->addCondition("owner_uid=:owner_uid");
    $query->params[":owner_uid"] = $uid;
    $teamAr = TeamAR::model()->find($query);
    if ($teamAr) {
      $user = self::crtuser();
      $team_user_ar = new UserTeamAR();
      $team_user_ar->uid = $user->uid;
      $team_user_ar->tid = $teamAr->tid;
      if ($team_user_ar->save()) {
        return $team_user_ar;
      }
    }
    return FALSE;
  }
  
  /**
   * @
   */
  public static function token() {
    $user = Yii::app()->session["user"];
    if ($user) {
      if ($user->from == self::FROM_WEIBO) {
        $access_token = Yii::app()->session["weibo_token"];
        if ($access_token) {
          $token = $access_token["access_token"];
          $uid = $access_token["uid"];
        }
        else {
          $token = FALSE;
        }
        
        return $token;
      }
      else {
        
      }
    }
  }
  
  public function leaveTeam($uid = FALSE) {
    if (!$uid) {
      $uid = $this->uid;
    }
    
    $userTeamAr = new UserTeamAR();
    return $userTeamAr->leaveTeam($uid);
  }
  
  /**
   * 从一段字符串中返回  @用户 
   */
  public static function getAtScreenNameFromMsg($msg) {
    $user = UserAR::crtuser();
    $matches = array();
    header("Content-Type: text/html; charset=utf-8");
    preg_match_all("/@([\p{L}\p{Mn}\w_0-9]+)/u", $msg, $matches);
    
    $weibo_users = array();

    if ($matches && count($matches) > 1) {
        $screenNames = $matches[1];
        if ($user->from == self::FROM_WEIBO) {
            $weibo_api = new SinaWeibo_API(WB_AKEY, WB_SKEY, UserAR::token());
            // 获取用户高级接口后 开启这个方法
            //$weibo_users = $weibo_api->users_show_batch_by_name($screenNames);
            $weibo_users = array();
            foreach ($screenNames as $screenName) {
              $ret = $weibo_api->show_user_by_name($screenName);
              $weibo_users[] = $ret;
            }
        }
        else {
            foreach ($screenNames as $screenName) {
              $ret = Yii::app()->twitter->user_show_with_screename($screenName);
              $weibo_users[] = $ret;
            }
        }
    }
    return $weibo_users;
  }
  
  public function afterFind() {
    // 获取 统计记分
    $score = ScoreUserAR::getUserScore($this->uid);
    $this->score = $score;
    return parent::afterFind();
  }
  
  public function logout() {
    Yii::app()->session["user"] = NULL;
    
    return TRUE;
  }
}

