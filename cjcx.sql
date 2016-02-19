-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2016 年 02 月 19 日 06:48
-- 服务器版本: 5.6.12-log
-- PHP 版本: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `cjcx`
--
CREATE DATABASE IF NOT EXISTS `cjcx` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `cjcx`;

-- --------------------------------------------------------

--
-- 表的结构 `acquirelog`
--

CREATE TABLE IF NOT EXISTS `acquirelog` (
  `studentid` varchar(11) NOT NULL,
  `result` text NOT NULL,
  `addtime` int(10) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`studentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `avggrade`
--

CREATE TABLE IF NOT EXISTS `avggrade` (
  `studentid` varchar(11) NOT NULL,
  `semester` tinyint(1) NOT NULL,
  `AvgGrade` float NOT NULL,
  `GradePoint` float NOT NULL,
  `ClassRank` int(2) NOT NULL,
  `UpdateDate` int(10) NOT NULL,
  `schoolYear` varchar(9) NOT NULL,
  UNIQUE KEY `studentid` (`studentid`,`schoolYear`,`semester`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='平均分存储表';

-- --------------------------------------------------------

--
-- 表的结构 `company`
--

CREATE TABLE IF NOT EXISTS `company` (
  `company` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `companyname` varchar(255) NOT NULL,
  `companytype` varchar(50) NOT NULL,
  `companysize` varchar(8) NOT NULL,
  `companyaddress` varchar(10) NOT NULL,
  PRIMARY KEY (`company`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='公司表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `course`
--

CREATE TABLE IF NOT EXISTS `course` (
  `name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `id` int(11) NOT NULL,
  `type` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `credit` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `schoolname` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `feedback`
--

CREATE TABLE IF NOT EXISTS `feedback` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `pid` int(8) DEFAULT '0',
  `truename` varchar(10) NOT NULL,
  `contect` varchar(50) NOT NULL,
  `content` varchar(300) NOT NULL,
  `addtime` int(10) NOT NULL,
  `reply` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='反馈已经' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `grade`
--

CREATE TABLE IF NOT EXISTS `grade` (
  `studentid` bigint(11) NOT NULL,
  `courseid` int(8) NOT NULL,
  `coursename` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `grade` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `makeup` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `isAgain` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `schoolYear` varchar(9) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `mark` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `semester` tinyint(1) NOT NULL,
  `addtime` int(10) NOT NULL,
  PRIMARY KEY (`studentid`,`courseid`,`schoolYear`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `graduates`
--

CREATE TABLE IF NOT EXISTS `graduates` (
  `Studentid` varchar(11) NOT NULL,
  `LastCompany` int(10) NOT NULL,
  `EnterDate` date NOT NULL,
  `Post` int(10) NOT NULL,
  `Websites` varchar(255) NOT NULL,
  `QQ` float NOT NULL,
  `Weibo` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `UpdateTime` int(10) NOT NULL,
  `Mark` text NOT NULL,
  `Top` int(1) NOT NULL DEFAULT '0',
  `Fans` int(10) NOT NULL,
  `Photo` varchar(255) NOT NULL,
  UNIQUE KEY `Studentid` (`Studentid`),
  KEY `LastCompany` (`LastCompany`),
  KEY `Post` (`Post`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='优秀毕业生';

-- --------------------------------------------------------

--
-- 表的结构 `post`
--

CREATE TABLE IF NOT EXISTS `post` (
  `PostID` int(10) NOT NULL AUTO_INCREMENT,
  `PostName` varchar(50) NOT NULL,
  `PostType` varchar(20) NOT NULL,
  `Description` text NOT NULL,
  PRIMARY KEY (`PostID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='职位描述' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `student`
--

CREATE TABLE IF NOT EXISTS `student` (
  `studentid` varchar(11) NOT NULL,
  `truename` varchar(30) NOT NULL,
  `beginsyear` varchar(10) NOT NULL,
  `birth` varchar(10) NOT NULL,
  `password` varchar(40) NOT NULL,
  `wjcpassword` varchar(40) NOT NULL,
  `telephone` varchar(11) DEFAULT NULL,
  `cardno` varchar(18) NOT NULL,
  `classname` varchar(255) NOT NULL,
  `schoolname` varchar(255) NOT NULL,
  `sex` int(1) NOT NULL,
  `depname` varchar(255) NOT NULL,
  `garde` int(4) NOT NULL,
  `level` varchar(10) NOT NULL,
  `addtime` int(10) NOT NULL,
  `source` varchar(20) NOT NULL COMMENT '来源：WEB|Android|IOS|wechat',
  `mark` varchar(255) NOT NULL COMMENT '错误数据收集',
  PRIMARY KEY (`studentid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
