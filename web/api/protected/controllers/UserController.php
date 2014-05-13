<?php
Yii::import('ext.sinaWeibo.SinaWeibo_API',true);
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserController
 *
 * @author jackeychen
 */
class UserController extends Controller{
  public function init() {
    parent::init();
  }
  
  /**
   * 邀请好友接口.
   * 接受 POST 方法
   */
  public function actionInvite() {
    $request = Yii::app()->getRequest();
    
    // 参数检查
    $msg = $request->getPost("msg");
    if (!$msg) {
      $this->responseError("params invalid", ErrorAR::ERROR_MISSED_REQUIRED_PARAMS, array("msg" => "required"));
    }
    
    $userAr = new UserAR();
    $ret = $userAr->post_invite_tweet($msg);
    if ($ret) {
      $this->responseJSON(array(), "success");
    }
    else {
      $this->responseError("invite friend failed", ErrorAR::ERROR_INVITE);
    }
  }
  
  /**
   * 用户加入一个Team
   * 注意参数不是一个 team id 而是队长的uid 后台可以根据队长uid 来获取Team id
   */
  public function actionJoinTeam() {
    $request = Yii::app()->getRequest();
    $team_owner_uid = $request->getParam("uid");
    if ($team_owner_uid) {
      $user_ar = UserAR::crtuser();
      if ($user_ar) {
        $user_ar->user_join_team($team_owner_uid);
      }
    }
  }
  
  public function actionBuildTeam() {
    $request = Yii::app()->getRequest();
    $name = $request->getParam("name");
    if ($name) {
      $team_ar = TeamAR::newteam($name);
      if ($team_ar instanceof TeamAR) {
        $this->responseJSON($team_ar->attributes, "build team success");
      }
      else {
        $this->responseError("validate failed", ErrorAR::ERROR_VALIDATE_FAILED, $team_ar);
      }
    }
    else {
      $this->responseError("team name is required", ErrorAR::ERROR_MISSED_REQUIRED_PARAMS, array("name" => "required"));
    }
  }
  
  public function actionFriendssuggestion() {
    $request = Yii::app()->getRequest();
    $q = $request->getParam("q");
    $token = UserAR::token();
    if ($token) {
      $weibo_api = new SinaWeibo_API(WB_AKEY, WB_SKEY, UserAR::token());
      $users = $weibo_api->search_at_users($q);
      $this->responseJSON($users, "success");
    }
    else {
      $this->responseError("user is not login", ErrorAR::ERROR_NOT_LOGIN);
    }
    $this->responseJSON(array(), "HELLO");
  }
  
  public function actionIndex() {
    $user = UserAR::crtuser(TRUE);
    if (!$user) {
      $this->responseError("user is not login", ErrorAR::ERROR_NOT_LOGIN);
    }
    
    $data = array(
        "user" => $user,
    );
    
    if ($user->team) {
      $data += array(
        "team" => $user->team,
        "last_post" => $user->team->last_post,
      );
    }
    else {
      $data += array(
          "team" => NULL,
          "last_post" => array()
      );
    }
    
    // Get Last Twitte
    
    $this->responseJSON($data, "success");
  }
}
