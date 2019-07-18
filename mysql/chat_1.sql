-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2019-07-18 22:15:39
-- 服务器版本： 5.5.62-log
-- PHP 版本： 7.3.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `chat_1`
--

-- --------------------------------------------------------

--
-- 表的结构 `chat_logs`
--

CREATE TABLE `chat_logs` (
  `id` bigint(20) NOT NULL,
  `session_id` int(11) NOT NULL,
  `time` bigint(20) NOT NULL,
  `msg` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `chat_logs`
--

INSERT INTO `chat_logs` (`id`, `session_id`, `time`, `msg`) VALUES
(1, 59, 1563459333, '啊'),
(2, 0, 1563459338, '亚麻得啊'),
(3, 0, 1563459339, '谁有看黄片的网址');

-- --------------------------------------------------------

--
-- 表的结构 `session`
--

CREATE TABLE `session` (
  `id` int(11) NOT NULL,
  `socket_id` bigint(20) NOT NULL,
  `session_key` varchar(16) NOT NULL,
  `name` varchar(10) NOT NULL,
  `online` int(1) NOT NULL COMMENT '1在线0掉线'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `session`
--

INSERT INTO `session` (`id`, `socket_id`, `session_key`, `name`, `online`) VALUES
(1, 57, '1f8e2063d03bc5eb', '测试', 1);

--
-- 转储表的索引
--

--
-- 表的索引 `chat_logs`
--
ALTER TABLE `chat_logs`
  ADD PRIMARY KEY (`id`);

--
-- 表的索引 `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `session_key` (`session_key`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `chat_logs`
--
ALTER TABLE `chat_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- 使用表AUTO_INCREMENT `session`
--
ALTER TABLE `session`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
