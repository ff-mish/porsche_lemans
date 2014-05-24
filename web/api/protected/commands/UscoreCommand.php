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
class UscoreCommand extends CConsoleCommand
{
//    public function run($args) {
//       $this->score();
//    }
  
    public function actionScore() {
      // 每小时发帖数量限制
      $max_twitte_per_hour = 40;
      $users = UserAR::model()->findAll();
      
      foreach ($users as $user) {
        // Speed: 发帖 速度
        // 开始时间
        $start_date = date("Y-m-d H", strtotime(Yii::app()->params["startTime"]));
        // 现在时间
        $now_date = date("Y-m-d H");

        // 相差几个小时？ 
        $time_step = round( (strtotime($now_date.":00:00") - strtotime($start_date.":00:00")) / ( 60 * 60) );

        // 每个小时 我分别计算出来速度，然后再平均
        $speeds = array();
        $s_date = $start_date;
        for ($i = 0; $i < $time_step; $i++) {
          // 构造查询条件
          $t = strtotime($s_date.":00:00") + 60 * 60;
          $n_date = date("Y-m-d H", $t);
          $query = new CDbCriteria();
          $query->addCondition("cdate >= :s_date AND cdate < :n_date");
          $query->params[":s_date"] = $s_date;
          $query->params[":n_date"] = $n_date;
          
          $query->addCondition("uid=:uid");
          $query->params[":uid"] = $user->uid;

          $count = TwitteAR::model()->count($query);
          $s_speed = $count > $max_twitte_per_hour  ? "1" : round($count / $max_twitte_per_hour, 1);
          $speeds[] = $s_speed;

          // 时间轮回
          $s_date = $n_date;
        }

        $speed = round(array_sum($speeds) / $time_step, 1);

        // Impact: 粉丝数
        // 个人只记粉丝数 不记比分
        $impact = $user->friends;
        
        // 保存记录
        $scoreUserAr = new ScoreUserAR();
        $scoreUserAr->uid = $user->uid;
        $scoreUserAr->speed = $speed;
        $scoreUserAr->impact = $impact;
        $scoreUserAr->cdate = date("Y-m-d H:i:s");
        
        print "run score cron job for user: [{$user->name}]"."\r\n";
        $scoreUserAr->save();
      }
      
    }
    
    /**
     * @author  小东
     * @return boolean
     */
    public function score()
    {
        set_time_limit(0);  //临时设置脚本运算为不限时
        $userAll=UserAR::model()->findAll(
            array(
                'select'=>'uid,friends',
            )
        );
        if($userAll)
        {
            $connection=Yii::app()->db;
            foreach($userAll as $key => $value)     //遍历用户计算积分
            {
                $userScore=new ScoreUserAR();
                $user_uid=$value->uid;
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
                    $userScore->speed=round( $speedSum / $speedArrayCount , 3) ;
                }

                $userScore->impact=$value->friends;
                $userScore->uid=$value->uid;
                $userScore->save(false);
                $userScore->setIsNewRecord(true);
            }
        }
    }
}