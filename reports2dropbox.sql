-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 20, 2013 at 08:31 AM
-- Server version: 5.6.12-log
-- PHP Version: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `reports2dropbox`
--
CREATE DATABASE IF NOT EXISTS `reports2dropbox` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `reports2dropbox`;

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `jotform_username` varchar(255) DEFAULT NULL,
  `jotform_email` varchar(255) DEFAULT NULL,
  `dropbox_data` text,
  `first_integration` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1000 ;

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE IF NOT EXISTS `forms` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `uid` varchar(50) DEFAULT NULL,
  `jotform_formid` varchar(50) DEFAULT NULL,
  `submission_count` int(50) NOT NULL DEFAULT '0',
  `last_submission_id` varchar(50) DEFAULT NULL,
  `last_submission_created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2000 ;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE IF NOT EXISTS `reports` (
  `id` int(50) NOT NULL AUTO_INCREMENT,
  `uid` int(50) DEFAULT NULL,
  `fid` int(50) DEFAULT NULL,
  `jotform_rid` varchar(50) DEFAULT NULL,
  `jotform_title` varchar(255) DEFAULT NULL,
  `jotform_url` varchar(255) DEFAULT NULL,
  `filepath` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3000 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
