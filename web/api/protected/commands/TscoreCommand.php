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
class TScoreCommand extends CConsoleCommand
{
    public function run($args) {
        //获取所有团队
        print_r($args);
    }
}