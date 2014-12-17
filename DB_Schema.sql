-- phpMyAdmin SQL Dump
-- version 3.5.8.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 17, 2014 at 12:14 AM
-- Server version: 5.5.40-36.1-log
-- PHP Version: 5.4.23

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `DB_NAME`
--

-- --------------------------------------------------------

--
-- Table structure for table `tmp_tx_history`
--

DROP TABLE IF EXISTS `tmp_tx_history`;
CREATE TABLE IF NOT EXISTS `tmp_tx_history` (
  `index` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `currency` varchar(5) NOT NULL,
  `btc_amount` double NOT NULL,
  `dollar_amount` double NOT NULL,
  `note` varchar(300) NOT NULL,
  `pub_add` varchar(40) NOT NULL,
  `file_hash` varchar(40) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `pub_add` (`pub_add`),
  KEY `index` (`index`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=50 ;

-- --------------------------------------------------------

--
-- Table structure for table `tx_history`
--

DROP TABLE IF EXISTS `tx_history`;
CREATE TABLE IF NOT EXISTS `tx_history` (
  `index` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `currency` varchar(5) NOT NULL,
  `btc_amount` double NOT NULL,
  `dollar_amount` double NOT NULL,
  `note` varchar(300) NOT NULL,
  `pub_add` varchar(40) NOT NULL,
  `file_hash` varchar(40) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`index`),
  UNIQUE KEY `pub_add` (`pub_add`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Table structure for table `wifkeys`
--

DROP TABLE IF EXISTS `wifkeys`;
CREATE TABLE IF NOT EXISTS `wifkeys` (
  `index` int(11) NOT NULL AUTO_INCREMENT,
  `pub_key` varchar(40) NOT NULL,
  `priv_enc` varchar(300) NOT NULL,
  `file_hash` varchar(40) DEFAULT NULL,
  `gen_date` date NOT NULL,
  `last_balance` double DEFAULT NULL,
  PRIMARY KEY (`index`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=89 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
