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
    public function run($args) {
       $this->score();
    }

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