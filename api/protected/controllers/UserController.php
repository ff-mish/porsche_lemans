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
  
  public function actionRegister() {
    $this->responseJSON(array("uid" => 1), "success");
  }
}
