-- MySQL dump 10.13  Distrib 5.6.16, for osx10.8 (x86_64)
--
-- Host: localhost    Database: lemans
-- ------------------------------------------------------
-- Server version	5.6.16

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `invite_log`
--

DROP TABLE IF EXISTS `invite_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `invite_log` (
  `ilid` int(11) NOT NULL AUTO_INCREMENT,
  `invitor` int(11) DEFAULT NULL COMMENT '邀请者',
  `invited_idstr` varchar(200) DEFAULT NULL COMMENT '被邀请者的第三方ID Str',
  `invite_code` varchar(200) DEFAULT NULL COMMENT '邀请码',
  `tid` int(11) DEFAULT NULL COMMENT '邀请的TEAM ID ',
  `status` int(11) DEFAULT NULL COMMENT '接受邀请的状态 (可能是接受邀请了，没有接受邀请)',
  `cdate` datetime DEFAULT NULL,
  PRIMARY KEY (`ilid`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media` (
  `mid` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(10) DEFAULT NULL,
  `uri` varchar(200) DEFAULT NULL,
  `cdate` datetime DEFAULT NULL,
  `udate` datetime DEFAULT NULL,
  `uid` int(11) DEFAULT NULL,
  `media_link` varchar(255) DEFAULT NULL,
  `teaser_image` varchar(255) DEFAULT NULL COMMENT '视频的缩略图',
  `title` text,
  `description` text,
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `question_anwser`
--

DROP TABLE IF EXISTS `question_anwser`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `question_anwser` (
  `qaid` int(11) NOT NULL AUTO_INCREMENT,
  `question` text,
  `answer1` text,
  `answer2` text,
  `answer3` text,
  `answer4` text,
  `right` tinyint(4) DEFAULT NULL,
  `answered` int(11) DEFAULT NULL,
  `right_answered` int(11) DEFAULT NULL,
  `cdate` datetime DEFAULT NULL,
  `udate` datetime DEFAULT NULL,
  `uid` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`qaid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `score_team`
--

DROP TABLE IF EXISTS `score_team`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `score_team` (
  `tsid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '团队积分记录',
  `tid` int(11) unsigned NOT NULL COMMENT '对应团队id',
  `cdate` datetime NOT NULL,
  `speed` varchar(100) DEFAULT NULL,
  `quality` varchar(100) DEFAULT NULL,
  `assiduity` varchar(100) DEFAULT NULL,
  `impact` varchar(100) DEFAULT NULL,
  `average` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`tsid`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='保存团队积分表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `score_user`
--

DROP TABLE IF EXISTS `score_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `score_user` (
  `suid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `speed` varchar(100) DEFAULT NULL,
  `quality` varchar(100) DEFAULT NULL,
  `assiduity` varchar(100) DEFAULT NULL,
  `impact` varchar(100) DEFAULT NULL,
  `average` varchar(100) DEFAULT NULL,
  `cdate` datetime DEFAULT NULL,
  PRIMARY KEY (`suid`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `id` char(32) NOT NULL,
  `expire` int(11) DEFAULT NULL,
  `data` longblob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statistics`
--

DROP TABLE IF EXISTS `statistics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `value` int(11) DEFAULT NULL,
  `cdate` datetime DEFAULT NULL,
  `udate` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index2` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `system`
--

DROP TABLE IF EXISTS `system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system` (
  `sid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `tid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `cdate` datetime DEFAULT NULL,
  `udate` datetime DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `owner_uid` int(11) DEFAULT '0' COMMENT '创建者uid',
  `achivements_total` int(11) DEFAULT '0' COMMENT '盾牌个数',
  `status` int(11) DEFAULT '1' COMMENT '组的状态',
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `twittes`
--

DROP TABLE IF EXISTS `twittes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `twittes` (
  `tid` int(11) NOT NULL AUTO_INCREMENT COMMENT '发帖id',
  `uid` int(11) unsigned DEFAULT NULL COMMENT '用户id',
  `content` longtext COMMENT '内容',
  `cdate` datetime DEFAULT NULL COMMENT '创建时间',
  `udate` datetime DEFAULT NULL COMMENT '修改时间',
  `uuid` varchar(45) DEFAULT NULL,
  `redirect_count` int(11) DEFAULT NULL,
  `ref_user_acount` int(11) DEFAULT NULL,
  `type` varchar(45) DEFAULT NULL COMMENT '帖子类型(微博/twitter)',
  `owned_type` varchar(45) DEFAULT NULL,
  `ref_type` varchar(10) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `is_from_thirdpart` varchar(45) DEFAULT NULL COMMENT '是否来自第三方平台(weibo/twitter)',
  `thirdpart_ref_media` varchar(255) DEFAULT NULL,
  `redirect_uuid` varchar(45) DEFAULT NULL COMMENT '转发微博的 uuid',
  PRIMARY KEY (`tid`)
) ENGINE=InnoDB AUTO_INCREMENT=8473 DEFAULT CHARSET=utf8 COMMENT='发帖表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_mails`
--

DROP TABLE IF EXISTS `user_mails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_mails` (
  `umid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `mail` varchar(200) DEFAULT NULL,
  `cdate` datetime DEFAULT NULL,
  PRIMARY KEY (`umid`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_question_answer`
--

DROP TABLE IF EXISTS `user_question_answer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_question_answer` (
  `uqaid` int(11) NOT NULL AUTO_INCREMENT,
  `qaid` int(11) DEFAULT NULL,
  `answer_id` tinyint(4) DEFAULT NULL,
  `is_right` tinyint(1) DEFAULT NULL,
  `cdate` varchar(45) DEFAULT NULL,
  `uid` int(11) DEFAULT '0',
  PRIMARY KEY (`uqaid`)
) ENGINE=InnoDB AUTO_INCREMENT=20012 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_teams`
--

DROP TABLE IF EXISTS `user_teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_teams` (
  `utid` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `tid` int(11) DEFAULT NULL,
  `cdate` datetime DEFAULT NULL,
  `udate` datetime DEFAULT NULL,
  PRIMARY KEY (`utid`)
) ENGINE=InnoDB AUTO_INCREMENT=1029 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) DEFAULT NULL,
  `from` varchar(45) DEFAULT NULL,
  `cdate` datetime DEFAULT NULL,
  `udate` datetime DEFAULT NULL,
  `uuid` varchar(200) DEFAULT NULL,
  `lat` varchar(45) DEFAULT NULL,
  `lng` varchar(45) DEFAULT NULL,
  `invited_by` varchar(200) DEFAULT NULL,
  `profile_msg` blob,
  `avatar` varchar(255) DEFAULT NULL,
  `score` int(11) DEFAULT NULL,
  `status` int(10) DEFAULT '1',
  `friends` int(10) DEFAULT '0',
  `location` varchar(100) DEFAULT '',
  `allowed_invite` int(1) DEFAULT '-1',
  PRIMARY KEY (`uid`),
  UNIQUE KEY `name_from` (`name`,`from`),
  KEY `uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=101761 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-05-20 15:09:32
