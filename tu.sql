-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2020-07-14 18:49:43
-- 服务器版本： 5.7.30-log
-- PHP 版本： 7.3.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `tu`
--

-- --------------------------------------------------------

--
-- 表的结构 `remote_imgs`
--

CREATE TABLE `remote_imgs` (
  `imgmd5` varchar(32) NOT NULL COMMENT '文件md5',
  `imguploadtime` datetime DEFAULT NULL COMMENT '上传时间，10位时间戳',
  `imguploadip` varchar(20) NOT NULL COMMENT '上传IP',
  `imgurl` varchar(200) NOT NULL COMMENT '远程访问URL',
  `repo` varchar(20) NOT NULL,
  `filesize` int(11) NOT NULL DEFAULT '0',
  `filename` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='图片统计表';

-- --------------------------------------------------------

--
-- 表的结构 `repo`
--

CREATE TABLE `repo` (
  `ID` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `filesize` float NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转储表的索引
--

--
-- 表的索引 `remote_imgs`
--
ALTER TABLE `remote_imgs`
  ADD PRIMARY KEY (`imgmd5`);

--
-- 表的索引 `repo`
--
ALTER TABLE `repo`
  ADD PRIMARY KEY (`ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
