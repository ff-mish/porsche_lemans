<?php
/**
 * Created by PhpStorm.
 * User: drogjh
 * Date: 14-5-13
 * Time: 上午3:50
 */

/**
 * 自动化执行 命令行模式
 */
class TscoreCommand extends CConsoleCommand
{
  
    public function actionScore() {
      $max_twitte_fuel = 100;
      $max_friends = 20000;
      $max_twitte_per_hour = 40;
      
      // 第一步: 先拿出所有的组
      $teams = TeamAR::model()->findAll();
      
      // Speed: 发帖 速度
      $team_speeds = array();
      foreach ($teams as $team) {
        // 然后拿出组里的所有用户
        $users = $team->loadMembers();
        if (!count($users)) {
          continue;
        }
        $team_speeds = array();
        foreach ($users as $user) {
          // 分别计算出每个用户的speed
          // 开始时间
          $start_date = date("Y-m-d H", strtotime(Yii::app()->params["startTime"]));
          // 现在时间
          $now_date = date("Y-m-d H");

          // 相差几个小时？ 
          $time_step = round( (strtotime($now_date.":00:00") - strtotime($start_date.":00:00")) / ( 60 * 60) , 3);

          // 每个小时 我分别计算出来速度，然后再平均
          $speeds = array();
          $s_date = $start_date;
          for ($i = 0; $i < $time_step+1; $i++) {
            // 构造查询条件
            $t = strtotime($s_date.":00:00") + 60 * 60;
            $n_date = date("Y-m-d H:00:00", $t);
            $query = new CDbCriteria();
            $query->addCondition("cdate >= :s_date AND cdate < :n_date");
            $query->params[":s_date"] = $s_date.":00:00";
            $query->params[":n_date"] = $n_date;

            $query->addCondition("uid=:uid");
            $query->params[":uid"] = $user->uid;
            
            
            $count = TwitteAR::model()->count($query);
            $s_speed = $count > $max_twitte_per_hour  ? "1" : round($count / $max_twitte_per_hour, 3);
            $speeds[] = $s_speed;

            // 时间轮回
            $s_date = date("Y-m-d H", strtotime($n_date));
          }

          // 到这里就得到整个用户的
          $speed = round(array_sum($speeds) / $time_step, 3);

          // 然后放入到组里面去
          $team_speeds[] = $speed;
        }

        // 最后对组里面的速度进行平均值计算
        $team_speed = round(array_sum($team_speeds) / count($users), 3);

        // Quality: 转发数积分
        // 只需要查询出所有的转发数目即可
        $uids = array();
        foreach ($users as $user) {
          $uids[] = $user->uid;
        }
        
        $query = new CDbCriteria();
        $query->addCondition("uid in (:uid)");
        $query->addCondition("ref_id IS NOT NULL AND ref_type IS NOT NULL");
        $query->params[":uid"] = implode(",", $uids);
        
        $count = TwitteAR::model()->count($query);
        if ($count >= $max_twitte_fuel) {
          $quality = 1;
        }
        else {
          $quality = round($count / $max_twitte_fuel, 3);
        }
        
        // Assiduity: Q&A 准确率
        // 回答数
        $query_all = new CDbCriteria();
        $query_all->addCondition("uid in (:uid)");
        $query_all->params[":uid"] = implode(",", $uids);
        $all = UserQAAR::model()->count($query_all);
        
        // 正确数
        $query_right = new CDbCriteria();
        $query_right->addCondition("uid in (:uid)");
        $query_right->params[":uid"] = implode(",", $uids);
        $query_right->addCondition("is_right = 1");
        $right = UserQAAR::model()->count($query_right);
        
        if (!$all) {
          $assiduity = 0;
        }
        else {
          $assiduity = round($right / $all, 3);
        }
        
        
        // Impact: 粉丝比例
        $sql = "SELECT sum(friends) as sum FROM users WHERE uid in (:uid)";
        $command = Yii::app()->db->createCommand($sql);
        $command->bindParam(":uid", implode(",", $uids));
        
        $row = $command->query()->read();
        $impact = round($row["sum"] / $max_friends, 3);
        
        // 总分
        // [Impact + 2 (Speed + Quality + Assiduity) ] / 7 * 246
        $average = ($impact + 2 * ($speed + $quality + $assiduity)) / 7 * 246;
        
        // 4个元素计算完毕，我们保存
        $scoreTeamAr = new ScoreTeamAR();
        $scoreTeamAr->tid = $team->tid;
        $scoreTeamAr->cdate = date("Y-m-d H:i:s");
        $scoreTeamAr->speed = $team_speed;
        $scoreTeamAr->quality = $quality;
        $scoreTeamAr->assiduity = $assiduity;
        $scoreTeamAr->impact = $impact;
        $scoreTeamAr->average = round($average, 3);
        
        print "run score cron job for team: [{$team->name}]"."\r\n";
        $scoreTeamAr->save();
        
      }
      
      
    }
    
    /**
     * @author  小东
     * @return boolean
     */
    public function score()
    {
        set_time_limit(0);  //临时设置脚本运算为不限时

        $allTeams = TeamAR::model()->findAll(
            array(
                'select'=>'tid,owner_uid',
            )
        );
        if(!$allTeams)          //没有查找到团队
            return false;

        //遍历团队
        foreach($allTeams as $key => $value)
        {
            $team_id = $value->tid;
            $teamScore=new ScoreTeamAR();      //记录团队此次计分
            $teamScore->tid=$team_id;

            //计算团队问题回答答对率 Assiduity ，检索结果已经是按照团队总数了，直接除就好
            $connection=Yii::app()->db;
            $rightSql='SELECT is_right,COUNT(*) AS count  FROM user_question_answer WHERE uid IN (SELECT uid FROM user_teams WHERE tid = :tid) AND is_right = 1';
            $command=$connection->createCommand($rightSql);
            $command->bindParam(":tid",$team_id);
            $rightCount=$command->queryRow();
            $rightCount=$rightCount['count'];

            $errorSql='SELECT is_right,COUNT(*) AS count  FROM user_question_answer WHERE uid IN (SELECT uid FROM user_teams WHERE tid = :tid)';
            $command=$connection->createCommand($errorSql);
            $command->bindParam(":tid",$team_id);
            $allCount=$command->queryRow();
            $allCount=$allCount['count'];

            //得到当前团队的  assiduity 百分比
            $teamScore->assiduity=round( $rightCount/$allCount , 3);

            //获取粉丝总数
            $connection->active=false;      //断开连接，
            $connection->active=true;       //重新连接 ，防止数据库断开

            $friendSql="SELECT Sum(friends) AS count FROM users WHERE uid IN (SELECT uid FROM user_teams WHERE tid = :tid)";
            $command=$connection->createCommand($friendSql);
            $command->bindParam(":tid",$team_id);
            $friendCount=$command->queryRow();
            $friendCount=$friendCount['count'];

            //粉丝百分比
            $teamScore->impact= round( $friendCount / 20000 , 3) > 1 ? 1 : round( $friendCount / 20000 , 3) ;

            //获取 Quality 百分比
            $connection->active=false;      //断开连接，
            $connection->active=true;       //重新连接 ，防止数据库断开

            $qualitySql="SELECT COUNT(*) AS count FROM twittes WHERE uid IN (SELECT uid FROM user_teams WHERE tid = :tid) AND ref_type IS NOT NULL AND ref_id IS NOT NULL";
            $command=$connection->createCommand($qualitySql);
            $command->bindParam(":tid", $team_id);
            $qualityCount=$command->queryRow();
            $qualityCount=$qualityCount['count'];

            //质量百分比
            $teamScore->quality= round( $qualityCount / 100 , 3) > 1 ? 1 : round( $qualityCount / 100 , 3) ;

            //Speed 百分比
            $connection->active=false;      //断开连接，
            $connection->active=true;       //重新连接 ，防止数据库断开

            //获取团队内的所有成员信息
            $allTeamsUsers=UserTeamAR::model()->findAll(
                array(
                    'select'=>'uid',
                    'condition'=>'tid = :tid ',
                    'params'=>array(':tid'=> $team_id),
                )
            );

            $userSpeedNum=0;
            $userSpeedSum=0.000;
            //遍历获取当前用户的speed
            foreach($allTeamsUsers as $ke => $val)
            {
                $user_uid = $val->uid;
                $cdate= Yii::app()->params['startTime'] ?  Yii::app()->params['startTime']  : '1970-10-10';
                $speedSql="SELECT uid,DATE_FORMAT( cdate,'%H') AS hour, COUNT( * ) AS count
                                              FROM twittes WHERE uid = :uid  AND ref_type IS NULL AND ref_id IS NULL AND cdate > :cdate GROUP BY hour";
                $command=$connection->createCommand($speedSql);
                $command->bindParam(":uid", $user_uid);
                $command->bindParam(":cdate",$cdate);
                $speedCountArray=$command->queryAll();

                $speedSum=0.000;
                $speedArrayCount=count($speedCountArray);           //用户在活动时间按照小时是否有发送的个数
                if($speedArrayCount > 0)
                {
                    foreach($speedCountArray as $k => $v)
                    {
                        $temp=($v['count'] / 40) >1 ?  1 : ($v['count'] / 40);
                        $speedSum = $speedSum + $temp;
                    }
                    $userSpeedNum++;
                    $userSpeedSum=$userSpeedSum + ($speedSum / $speedArrayCount);
                }
            }

            $teamScore->average =  round( $userSpeedSum / $userSpeedNum , 3) > 1 ? 1 : round( $userSpeedSum / $userSpeedNum  , 3);

            $teamScore->speed=($teamScore->impact + 2* ($teamScore->average + $teamScore->quality + $teamScore->assiduity) / 7)* 246 ;

            $teamScore->save(false);
            $teamScore->setIsNewRecord(true);
        }
    }
}