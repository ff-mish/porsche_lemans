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
    public function run($args) {
        //获取所有团队
       $this->score();
    }

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