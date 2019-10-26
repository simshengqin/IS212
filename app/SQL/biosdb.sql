-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 25, 2019 at 11:40 AM
-- Server version: 5.7.23
-- PHP Version: 7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `biosdb`
--
CREATE DATABASE IF NOT EXISTS `biosdb` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `biosdb`;

-- --------------------------------------------------------

--
-- Table structure for table `bid`
--

DROP TABLE IF EXISTS `bid`;
CREATE TABLE IF NOT EXISTS `bid` (
  `userid` varchar(128) DEFAULT NULL,
  `amount` int(2) DEFAULT NULL,
  `code` varchar(10) DEFAULT NULL,
  `section` varchar(3) DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bid_status`
--

DROP TABLE IF EXISTS `bid_status`;
CREATE TABLE IF NOT EXISTS `bid_status` (
  `id` int(1) NOT NULL,
  `round` int(1) NOT NULL,
  `status` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Truncate table before insert `bid_status`
--

TRUNCATE TABLE `bid_status`;
--
-- Dumping data for table `bid_status`
--

INSERT INTO `bid_status` (`id`, `round`, `status`) VALUES
(1, 0, 'closed');

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

DROP TABLE IF EXISTS `course`;
CREATE TABLE IF NOT EXISTS `course` (
  `course` varchar(10) NOT NULL,
  `school` varchar(45) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `exam date` date DEFAULT NULL,
  `exam start` time DEFAULT NULL,
  `exam end` time DEFAULT NULL,
  PRIMARY KEY (`course`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `course_completed`
--

DROP TABLE IF EXISTS `course_completed`;
CREATE TABLE IF NOT EXISTS `course_completed` (
  `userid` varchar(128) DEFAULT NULL,
  `code` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `prerequisite`
--

DROP TABLE IF EXISTS `prerequisite`;
CREATE TABLE IF NOT EXISTS `prerequisite` (
  `course` varchar(10) DEFAULT NULL,
  `prerequisite` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `section`
--

DROP TABLE IF EXISTS `section`;
CREATE TABLE IF NOT EXISTS `section` (
  `course` varchar(10) NOT NULL,
  `section` varchar(45) NOT NULL,
  `day` int(11) DEFAULT NULL,
  `start` time DEFAULT NULL,
  `end` time DEFAULT NULL,
  `instructor` varchar(100) DEFAULT NULL,
  `venue` varchar(100) DEFAULT NULL,
  `size` varchar(45) DEFAULT NULL,
  `minbid` float NOT NULL DEFAULT '10',
  PRIMARY KEY (`section`,`course`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `section_student`
--

DROP TABLE IF EXISTS `section_student`;
CREATE TABLE IF NOT EXISTS `section_student` (
  `userid` varchar(128) NOT NULL,
  `course` varchar(10) NOT NULL,
  `section` varchar(45) NOT NULL,
  `amount` float NOT NULL,
  PRIMARY KEY (`userid`,`course`,`section`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

DROP TABLE IF EXISTS `student`;
CREATE TABLE IF NOT EXISTS `student` (
  `userid` varchar(128) NOT NULL,
  `password` text NOT NULL,
  `name` varchar(100) NOT NULL,
  `school` varchar(255) NOT NULL,
  `edollar` float NOT NULL,
  PRIMARY KEY (`userid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
