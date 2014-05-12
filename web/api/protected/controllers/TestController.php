<?php

class TestController extends Controller {
  public function actionIndex() {
    $userAr = new UserAR();
    $invited_url = $userAr->generateInvitedURL("200238123", "30209847");
    
    $this->render("index", array("url" => $invited_url));
  }
  
  public function actionAddMedia() {
    $this->render("addmedia");
  }
  
  public function actionTwitte() {
    $this->render("twitte");
  }

  public function actionAddSomeThing()
  {
//      （只是把需要做计算的数据拿出来，计算步骤尚且不说）
//计算团队分数
//1：计算团队内所有人员的发帖数量，（注意是所有团队内人员）
//   发帖数量从 twittes  	检索 uid 为团队成员 && 满足时间内  &&  (ref_type && ref_id)为空，表示不是转发	计算平均值
//
//2.高质量转发（只需要检索满足条件的tid，不需要检索成员）
//  从 twittes	检索 tid && (ref_type && ref_id)不为空，表示是转发
//
//3.队伍问题回答（检索所有成员）
//  从 user_question_answer 检索团队成员的总答题数 正确答题数，累加 计算
//
//4.粉丝（检索所有成员）
//  从 users 表 获取 friend 进行累加

        //echo date("Y-m-d H:i:s", time()) ;
        //添加一批新用户
//      $sql='';
//      for($i=1 ; $i<7;$i++)
//      {
//          $type=(intval($i % 2)+1);
//          $time=date("Y-m-d H:i:s", time()-rand(-36000,36000));
//          $rand=rand(100,3000);
//          $sql=$sql . "INSERT INTO `users` (`uid`, `name`, `from`, `cdate`, `friends`) VALUES ('" . $i   ."', '". $i ."', '".$type."', '".$time."', '".$rand."'); ";
//      }
//      echo $sql;

      //为用户分配团队
//      for($i=1;$i<1000;$i++)
//      {
//          $userTeam=new UserTeamAR();
//          $userTeam->uid=$i;
//          $userTeam->tid=rand(1,4);
//          var_dump($userTeam->save(false));
//          $userTeam->setIsNewRecord(true);
//      }

      //为用户新增回答问题
//      for($j=0;$j<20;$j++)
//      {
//        for($i=0;$i<1000;$i++)
//        {
//            $usqa=new UserQAAR();
//            $usqa->is_right=rand(0,1);
//            $usqa->uid=$i;
//            var_dump($usqa->save(false));
//            $usqa->setIsNewRecord(true);
//        }
//      }

      //新增 3000 个不转发的原始帖子
//        for($i=1;$i<3000;$i++)
//        {
//            $twittes=new TwitteAR();
//            $twittes->uid=rand(1,999);
//            $twittes->content=$twittes->tid;
//            $twittes->redirect_count=rand(10,2000);
//            var_dump($twittes->save(false));
//            $twittes->setIsNewRecord(true);
//        }

      //新增转发贴3000个
//         for($i=1;$i<3000;$i++)
//        {
//            $twittes=new TwitteAR();
//            $twittes->uid=rand(1,999);
//            $twittes->content=$twittes->tid;
//            $twittes->redirect_count=rand(10,2000);
//            $twittes->ref_id=rand(1,40);
//            $twittes->ref_type=rand(1,2);
//            var_dump($twittes->save(false));
//            $twittes->setIsNewRecord(true);
//        }
  }

}

