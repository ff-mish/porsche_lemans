<?php

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
  
  public function actionInvite() {
    $userAr = new UserAR();
    $userAr->post_invite_tweet();
    $this->responseJSON("", "success");
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
}
