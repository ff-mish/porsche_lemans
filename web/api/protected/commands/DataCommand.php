<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class DataCommand extends CConsoleCommand {
  
  // 用户为10000
  private $max_user = 10000;
  // 每个用户发的最多微博
  private $max_twitte = 50;
  // 每个用户转发的微博
  private $max_retwitte = 20;
  // Q&A 用户回答的 
  private $max_qa_answered = 84;
  
  public function init() {
    //TODO::
  }

  // 造数据,
  public function run() {
    $users = UserAR::model()->findAll();
    foreach ($users as $user) {
      // 用户发微博
      for ($i = 0; $i < $this->max_twitte; $i++) {
        $twitte = $this->generateRandomString();
        $twitteAr = new TwitteAR();
        $twitteAr->uid = $user["uid"];
        $twitteAr->content = $twitte;
        $twitteAr->uuid = time();
        $twitteAr->type = $user->from;
        
        try {
          $twitteAr->save();
          print "SAVED Twitte: [$twitte]\r\n";
        }
        catch(Exception $e) {
          
        }
      }
      
      // Q&A 回答
      
    }
  }
  
  public function generateRandomString($length = 10) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $randomString = '';
      for ($i = 0; $i < $length; $i++) {
          $randomString .= $characters[rand(0, strlen($characters) - 1)];
      }
      return $randomString;
  }
}

