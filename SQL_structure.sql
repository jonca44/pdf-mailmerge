-- phpMyAdmin SQL Dump
-- version 4.0.10.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 17, 2015 at 09:27 PM
-- Server version: 5.1.73-cll
-- PHP Version: 5.4.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `mymailme_app`
--
CREATE DATABASE IF NOT EXISTS `mymailme_app` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `mymailme_app`;

-- --------------------------------------------------------

--
-- Table structure for table `backgrounds`
--

DROP TABLE IF EXISTS `backgrounds`;
CREATE TABLE IF NOT EXISTS `backgrounds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL COMMENT 'user id of the owner of this background collection.',
  `name` varchar(128) NOT NULL,
  `data_path` text NOT NULL COMMENT 'the location of the file that holds the data for this data-source.',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Holds details about background collections (From PDF, DocX o' AUTO_INCREMENT=1287 ;

-- --------------------------------------------------------

--
-- Table structure for table `backgrounds_pages`
--

DROP TABLE IF EXISTS `backgrounds_pages`;
CREATE TABLE IF NOT EXISTS `backgrounds_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `background_id` int(11) NOT NULL,
  `background_pg_num` int(11) NOT NULL,
  `file_name` varchar(128) NOT NULL COMMENT 'filename of this background''s image.',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1313 ;

-- --------------------------------------------------------

--
-- Table structure for table `curl_requests`
--

DROP TABLE IF EXISTS `curl_requests`;
CREATE TABLE IF NOT EXISTS `curl_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_id` int(11) NOT NULL,
  `data` text NOT NULL,
  `actioned_at` datetime DEFAULT NULL COMMENT 'the time when our cron job processed this request.',
  `result` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25401 ;

-- --------------------------------------------------------

--
-- Table structure for table `datasources`
--

DROP TABLE IF EXISTS `datasources`;
CREATE TABLE IF NOT EXISTS `datasources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `data_path` text NOT NULL COMMENT 'the location of the file that holds the data for this data-source.',
  `file_name` varchar(128) NOT NULL,
  `headers` text COMMENT 'Holds a json array of the CSV headers for easy reference',
  `lines` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='datasources used by documents.' AUTO_INCREMENT=1071 ;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `datasource_id` int(11) DEFAULT NULL COMMENT 'Links a document to its datasource',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='table to hold details about a user''s document' AUTO_INCREMENT=842 ;

-- --------------------------------------------------------

--
-- Table structure for table `documents_pages`
--

DROP TABLE IF EXISTS `documents_pages`;
CREATE TABLE IF NOT EXISTS `documents_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `document_id` int(11) NOT NULL,
  `document_pg_num` int(11) NOT NULL,
  `background_id` int(11) DEFAULT NULL COMMENT 'the background this page belongs to',
  `background_pg_id` int(11) DEFAULT NULL COMMENT 'the pg identifier of the background this page has.',
  `preset` char(2) DEFAULT 'a4' COMMENT 'text representation of the preset page size. Null if using a custom size.',
  `width` int(11) NOT NULL DEFAULT '210' COMMENT 'absolute width ofthe page in mm. Defaults to A4',
  `height` int(11) NOT NULL DEFAULT '297' COMMENT 'absolute height of the page in mm. Defaults to A4',
  `selected_measurement_unit` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 = mm, 1 = inches (Only used for cosmetic calculations)',
  `variables` mediumtext COMMENT 'Allows upto 16mb of storage.',
  PRIMARY KEY (`id`),
  KEY `document_id` (`document_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='holds information about pages that are associated with docum' AUTO_INCREMENT=2241 ;

-- --------------------------------------------------------

--
-- Table structure for table `generated_documents`
--

DROP TABLE IF EXISTS `generated_documents`;
CREATE TABLE IF NOT EXISTS `generated_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `generation_datasource_id` int(11) DEFAULT NULL,
  `file_path` text NOT NULL,
  `pages` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=532 ;

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(16) CHARACTER SET latin1 NOT NULL,
  `user_agent` varchar(255) CHARACTER SET latin1 NOT NULL,
  `username` varchar(255) CHARACTER SET latin1 NOT NULL,
  `successful_login` tinyint(1) NOT NULL,
  `successful_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `successful_user_id` (`successful_user_id`,`successful_login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=347 ;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `ses_id` char(36) COLLATE utf8_unicode_ci NOT NULL COMMENT 'unique GUID',
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `start_time` int(11) unsigned NOT NULL,
  `last_time` int(11) unsigned NOT NULL,
  `user_ip` char(16) CHARACTER SET latin1 NOT NULL,
  `logged_in` tinyint(4) unsigned NOT NULL DEFAULT '0',
  `user_id` char(32) CHARACTER SET latin1 DEFAULT NULL,
  `user_agent` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`ses_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

DROP TABLE IF EXISTS `subscription_plans`;
CREATE TABLE IF NOT EXISTS `subscription_plans` (
  `plan_name` varchar(16) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `monthly_price` decimal(10,2) NOT NULL COMMENT 'price is in dollars',
  `included_pages` int(10) unsigned NOT NULL,
  `price_per_extra_page` decimal(6,5) NOT NULL COMMENT 'price is in dollars',
  PRIMARY KEY (`plan_name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `username` varchar(64) NOT NULL,
  `password_hash` varchar(64) NOT NULL,
  `file_directory` char(36) NOT NULL COMMENT 'the name of the directory that holds their files.',
  `account_enabled` tinyint(4) NOT NULL DEFAULT '1',
  `email` varchar(64) NOT NULL,
  `tutorial_position` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Indicates how far through the tutorial the user got.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  UNIQUE KEY `unique_username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Table of user data' AUTO_INCREMENT=560 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_notices`
--

DROP TABLE IF EXISTS `user_notices`;
CREATE TABLE IF NOT EXISTS `user_notices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(16) NOT NULL,
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1087 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_statistics`
--

DROP TABLE IF EXISTS `user_statistics`;
CREATE TABLE IF NOT EXISTS `user_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `pages_made_total` int(11) NOT NULL DEFAULT '0',
  `billing_cycle_logins` int(11) NOT NULL DEFAULT '0',
  `billing_cycle_documents` int(11) NOT NULL DEFAULT '0',
  `billing_cycle_pages` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=547 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_subscriptions`
--

DROP TABLE IF EXISTS `user_subscriptions`;
CREATE TABLE IF NOT EXISTS `user_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` datetime DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `chargebee_id` char(36) NOT NULL,
  `subscription_plan_id` varchar(24) DEFAULT NULL,
  `subscription_plan_quantity` int(11) DEFAULT NULL,
  `subscription_status` varchar(16) DEFAULT NULL,
  `subscription_trial_start` int(11) DEFAULT NULL,
  `subscription_trial_end` int(11) DEFAULT NULL,
  `subscription_created_at` int(11) DEFAULT NULL,
  `subscription_due_invoices_count` int(11) DEFAULT NULL,
  `subscription_activated_at` int(11) DEFAULT NULL,
  `subscription_current_term_start` int(11) DEFAULT NULL,
  `subscription_current_term_end` int(11) DEFAULT NULL,
  `customer_first_name` varchar(32) DEFAULT NULL,
  `customer_last_name` varchar(32) DEFAULT NULL,
  `customer_email` varchar(256) DEFAULT NULL,
  `customer_created_at` int(11) DEFAULT NULL,
  `card_status` varchar(24) DEFAULT NULL,
  `card_first_name` varchar(32) DEFAULT NULL,
  `card_last_name` varchar(32) DEFAULT NULL,
  `card_card_type` varchar(16) DEFAULT NULL,
  `card_expiry_month` int(11) DEFAULT NULL,
  `card_expiry_year` int(11) DEFAULT NULL,
  `card_billing_addr1` varchar(128) DEFAULT NULL,
  `card_billing_addr2` varchar(128) DEFAULT NULL,
  `card_billing_city` varchar(32) DEFAULT NULL,
  `card_billing_state` varchar(24) DEFAULT NULL,
  `card_billing_country` varchar(24) DEFAULT NULL,
  `card_billing_zip` int(11) DEFAULT NULL,
  `card_masked_number` varchar(24) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_index` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='Links a user to their details as stored in chargebee' AUTO_INCREMENT=547 ;

