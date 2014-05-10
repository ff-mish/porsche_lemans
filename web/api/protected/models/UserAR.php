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
  private $weibo;
  
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
        array("uid, cdate, udate, lat, lng, invited_by, profile_msg, avatar, score, status, friends", "safe"),
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
  public function generateInvitedURL($uid, $invited_uid = "") {
    $data = array(
        "uid" => $uid,
        "invited_uid" => $invited_uid,
        "time" => time()
    );
    $str = serialize($data);
    $encrpted_str = $this->encrypt_decrypt("e", $str);
    $request = Yii::app()->getRequest();
    $base_url = $request->getBaseUrl(TRUE);
    return $base_url. '?d='. base64_encode($encrpted_str);
  }
  
  
  /**
   * 解密强求数据
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
        $user = $this->load_user_by_uuid($uuid);
        // 用户已经注册到了我们系统了
        if ($user && $user->status == self::STATUS_ENABLED) {
          Yii::app()->session["user"] = $user;
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
              "name" => $weibo_user["screen_name"],
              "from" => self::FROM_WEIBO,
              "uuid" => $weibo_user["idstr"],
              "invited_by" => $inviter,
              // TODO:: 是否需要实现用户个人说明功能？
              "profile_msg" => "", 
              "avatar" => $weibo_user["avatar_large"],
              "friends" => $weibo_user["friends_count"],
              "status" => self::STATUS_ENABLED
          );
          foreach ($attributes as $name => $value) {
            $new_userar->{$name} = $value;
          }
          if ($new_userar->save()) {
            Yii::app()->session["user"] = $new_userar;
          }
          
          // Step2, 用户保存成功后，需要把用户自动分组，分组是根据 Invite uid 来分配的.
          // 如果用户有邀请者，则将用户加入到组中去
          if ($inviter) {
            $this->user_join_team($inviter);
          }
          // 否则用户自己创建一个组
          else {
            //TODO:: 用户登录成功后 有没有必要自动生成一个组???
          }
        }
      }
    }
    // 从Twitter 登录
    else if ($from == self::FROM_TWITTER) {
      // TODO:: 从 Twitter 登录 处理
    }
  }
  
  /**
   * 返回当前登录的用户
   * @return UserAR
   */
  public static function crtuser() {
    return Yii::app()->session["user"];
  }
  
  /**
   * 发送一条邀请微博
   * @param type $weibo
   * @return boolean
   */
  public function post_invite_tweet($msg) {
    // 发一个微博邀请 （其实是一个微博 用@用户名方式）
    if (Yii::app()->session["from"] == self::FROM_WEIBO) {
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
      $short_url = $weibo_api->short_url_shorten($this->generateInvitedURL("20120302", ""));
      $url = $short_url["urls"][0]["url_short"];
      $ret = $weibo_api->update($msg.' '. $url);
      
      return $ret;
    }
    // 发一个Twitter 邀请
    else {
      
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
    $teamAr = TeamAR::model()->find("owner_uid = :owner_uid", array(":owner_uid" => $uid));
    if ($teamAr) {
      $team_user_ar = new UserTeamAR();
      $team_user_ar->uid = $this->uid;
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
}

