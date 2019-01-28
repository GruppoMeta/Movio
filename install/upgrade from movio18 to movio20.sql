# Disable Foreign Keys Check
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = '';

# Deleted Tables

# Changed Tables

-- changed table `countries_tbl`

ALTER TABLE `countries_tbl`
  ENGINE=InnoDB,
  DEFAULT CHARSET=utf8;

-- changed table `custom_code_mapping_tbl`

ALTER TABLE `custom_code_mapping_tbl`
  ADD KEY `custom_code_mapping_code` (`custom_code_mapping_code`),
  ENGINE=InnoDB;

-- changed table `documents_detail_tbl`

ALTER TABLE `documents_detail_tbl`
  CHANGE COLUMN `document_detail_translated` `document_detail_translated` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `document_detail_status`,
  CHANGE COLUMN `document_detail_isVisible` `document_detail_isVisible` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `document_detail_object`,
  ADD COLUMN `document_detail_note` text AFTER `document_detail_isVisible`,
  ENGINE=InnoDB;

-- changed table `documents_index_datetime_tbl`

ALTER TABLE `documents_index_datetime_tbl`
  DROP INDEX `document_index_datetime_name`;
ALTER TABLE `documents_index_datetime_tbl`
  CHANGE COLUMN `document_index_datetime_name` `document_index_datetime_name` varchar(100) NOT NULL AFTER `document_index_datetime_FK_document_detail_id`,
  ADD KEY `document_index_datetime_name` (`document_index_datetime_name`),
  ADD KEY `document_index_datetime_value` (`document_index_datetime_value`),
  ENGINE=InnoDB;

-- changed table `documents_index_date_tbl`

ALTER TABLE `documents_index_date_tbl`
  DROP INDEX `document_index_date_fk`,
  DROP INDEX `document_index_date_name`;
ALTER TABLE `documents_index_date_tbl`
  CHANGE COLUMN `document_index_date_name` `document_index_date_name` varchar(100) NOT NULL AFTER `document_index_date_FK_document_detail_id`,
  ADD KEY `document_index_date_fk` (`document_index_date_FK_document_detail_id`) USING BTREE,
  ADD KEY `document_index_date_name` (`document_index_date_name`),
  ADD KEY `document_index_date_value` (`document_index_date_value`),
  ENGINE=InnoDB;

-- changed table `documents_index_fulltext_tbl`

ALTER TABLE `documents_index_fulltext_tbl`
  DROP INDEX `document_index_fulltext_FK_document_detail_id`;
ALTER TABLE `documents_index_fulltext_tbl`
  CHANGE COLUMN `document_index_fulltext_name` `document_index_fulltext_name` varchar(100) NOT NULL AFTER `document_index_fulltext_FK_document_detail_id`,
  ADD KEY `document_index_fulltext_fk` (`document_index_fulltext_FK_document_detail_id`) USING BTREE;

-- changed table `documents_index_int_tbl`

ALTER TABLE `documents_index_int_tbl`
  DROP INDEX `document_index_int_name`;
ALTER TABLE `documents_index_int_tbl`
  CHANGE COLUMN `document_index_int_name` `document_index_int_name` varchar(100) NOT NULL AFTER `document_index_int_FK_document_detail_id`,
  ADD KEY `document_index_int_name` (`document_index_int_name`),
  ADD KEY `document_index_int_value` (`document_index_int_value`),
  ENGINE=InnoDB;

-- changed table `documents_index_text_tbl`

ALTER TABLE `documents_index_text_tbl`
  DROP INDEX `document_index_text_name`;
ALTER TABLE `documents_index_text_tbl`
  CHANGE COLUMN `document_index_text_name` `document_index_text_name` varchar(100) NOT NULL AFTER `document_index_text_FK_document_detail_id`,
  ADD KEY `document_index_text_name` (`document_index_text_name`),
  ADD KEY `document_index_text_value` (`document_index_text_value`),
  ENGINE=InnoDB;

-- changed table `documents_index_time_tbl`

ALTER TABLE `documents_index_time_tbl`
  DROP INDEX `document_index_time_name`;
ALTER TABLE `documents_index_time_tbl`
  CHANGE COLUMN `document_index_time_name` `document_index_time_name` varchar(100) NOT NULL AFTER `document_index_time_FK_document_detail_id`,
  ADD KEY `document_index_time_name` (`document_index_time_name`),
  ADD KEY `document_index_time_value` (`document_index_time_value`),
  ENGINE=InnoDB;

-- changed table `documents_tbl`

ALTER TABLE `documents_tbl`
  CHANGE COLUMN `document_FK_site_id` `document_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `document_creationDate`,
  ENGINE=InnoDB;

-- changed table `entity_properties_tbl`

ALTER TABLE `entity_properties_tbl`
  ENGINE=InnoDB;

-- changed table `entity_tbl`

ALTER TABLE `entity_tbl`
  ENGINE=InnoDB;

-- changed table `languages_tbl`

ALTER TABLE `languages_tbl`
  ADD COLUMN `language_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `language_id`,
  CHANGE COLUMN `language_name` `language_name` varchar(100) NOT NULL DEFAULT '' AFTER `language_FK_site_id`,
  CHANGE COLUMN `language_code` `language_code` varchar(10) NOT NULL DEFAULT '' AFTER `language_name`,
  CHANGE COLUMN `language_FK_country_id` `language_FK_country_id` int(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `language_code`,
  CHANGE COLUMN `language_isDefault` `language_isDefault` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `language_FK_country_id`,
  CHANGE COLUMN `language_order` `language_order` int(4) UNSIGNED NOT NULL DEFAULT '0' AFTER `language_isDefault`,
  ADD KEY `language_FK_site_id` (`language_FK_site_id`),
  ADD KEY `language_isDefault` (`language_isDefault`),
  ADD KEY `language_order` (`language_order`),
  ENGINE=InnoDB;

-- changed table `mediadetails_tbl`

ALTER TABLE `mediadetails_tbl`
  CHANGE COLUMN `media_category` `media_category` varchar(255) DEFAULT NULL AFTER `media_title`,
  ENGINE=InnoDB;

-- changed table `media_tbl`

ALTER TABLE `media_tbl`
  CHANGE COLUMN `media_FK_site_id` `media_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `media_id`,
  ENGINE=InnoDB;

-- changed table `menudetails_tbl`

ALTER TABLE `menudetails_tbl`
  ENGINE=InnoDB;

-- changed table `menus_tbl`

ALTER TABLE `menus_tbl`
  CHANGE COLUMN `menu_FK_site_id` `menu_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `menu_id`,
  CHANGE COLUMN `menu_creationDate` `menu_creationDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `menu_hasPreview`,
  CHANGE COLUMN `menu_modificationDate` `menu_modificationDate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `menu_creationDate`,
  ADD COLUMN `menu_extendsPermissions` tinyint(1) NOT NULL DEFAULT '0' AFTER `menu_printPdf`,
  CHANGE COLUMN `menu_cssClass` `menu_cssClass` varchar(255) DEFAULT NULL AFTER `menu_extendsPermissions`,
  ADD KEY `menu_type` (`menu_type`),
  ENGINE=InnoDB;

-- changed table `mobilecodes_tbl`

ALTER TABLE `mobilecodes_tbl`
  ADD KEY `mobilecode_code` (`mobilecode_code`),
  ENGINE=InnoDB;

-- changed table `mobilecontents_tbl`

ALTER TABLE `mobilecontents_tbl`
  ENGINE=InnoDB;

-- changed table `mobilefulltext_tbl`

ALTER TABLE `mobilefulltext_tbl`
  ADD KEY `mobilefulltext_FK_content_id` (`mobilefulltext_FK_content_id`),
  ENGINE=InnoDB;

-- changed table `registry_tbl`

ALTER TABLE `registry_tbl`
  ADD COLUMN `registry_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `registry_id`,
  CHANGE COLUMN `registry_path` `registry_path` varchar(255) NOT NULL DEFAULT '' AFTER `registry_FK_site_id`,
  CHANGE COLUMN `registry_value` `registry_value` text NOT NULL AFTER `registry_path`,
  ADD KEY `registry_FK_site_id` (`registry_FK_site_id`),
  ENGINE=InnoDB;

-- changed table `simple_documents_index_datetime_tbl`

ALTER TABLE `simple_documents_index_datetime_tbl`
  DROP INDEX `simple_document_index_datetime_fk`,
  DROP INDEX `simple_document_index_datetime_name`;
ALTER TABLE `simple_documents_index_datetime_tbl`
  CHANGE COLUMN `simple_document_index_datetime_name` `simple_document_index_datetime_name` varchar(100) NOT NULL AFTER `simple_document_index_datetime_FK_simple_document_id`,
  ADD KEY `simple_document_index_datetime_fk` (`simple_document_index_datetime_FK_simple_document_id`) USING BTREE,
  ADD KEY `simple_document_index_datetime_name` (`simple_document_index_datetime_name`) USING BTREE,
  ADD KEY `simple_document_index_datetime_value` (`simple_document_index_datetime_value`) USING BTREE,
  ENGINE=InnoDB;

-- changed table `simple_documents_index_date_tbl`

ALTER TABLE `simple_documents_index_date_tbl`
  DROP INDEX `simple_document_index_date_name`;
ALTER TABLE `simple_documents_index_date_tbl`
  CHANGE COLUMN `simple_document_index_date_name` `simple_document_index_date_name` varchar(100) NOT NULL AFTER `simple_document_index_date_FK_simple_document_id`,
  ADD KEY `simple_document_index_date_name` (`simple_document_index_date_name`) USING BTREE,
  ADD KEY `simple_document_index_date_value` (`simple_document_index_date_value`),
  ENGINE=InnoDB;

-- changed table `simple_documents_index_fulltext_tbl`

ALTER TABLE `simple_documents_index_fulltext_tbl`
  DROP INDEX `simple_document_index_fulltext_FK_simple_document_detail_id`;
ALTER TABLE `simple_documents_index_fulltext_tbl`
  CHANGE COLUMN `simple_document_index_fulltext_name` `simple_document_index_fulltext_name` varchar(100) NOT NULL AFTER `simple_document_index_fulltext_FK_simple_document_id`,
  ADD KEY `simple_document_index_fulltext_fk` (`simple_document_index_fulltext_FK_simple_document_id`) USING BTREE;

-- changed table `simple_documents_index_int_tbl`

ALTER TABLE `simple_documents_index_int_tbl`
  DROP INDEX `simple_document_index_int_name`;
ALTER TABLE `simple_documents_index_int_tbl`
  CHANGE COLUMN `simple_document_index_int_name` `simple_document_index_int_name` varchar(100) NOT NULL AFTER `simple_document_index_int_FK_simple_document_id`,
  ADD KEY `simple_document_index_int_name` (`simple_document_index_int_name`) USING BTREE,
  ADD KEY `simple_document_index_int_value` (`simple_document_index_int_value`),
  ENGINE=InnoDB;

-- changed table `simple_documents_index_text_tbl`

ALTER TABLE `simple_documents_index_text_tbl`
  DROP INDEX `simple_document_index_text_name`;
ALTER TABLE `simple_documents_index_text_tbl`
  CHANGE COLUMN `simple_document_index_text_name` `simple_document_index_text_name` varchar(100) NOT NULL AFTER `simple_document_index_text_FK_simple_document_id`,
  CHANGE COLUMN `simple_document_index_text_value` `simple_document_index_text_value` varchar(255) DEFAULT NULL AFTER `simple_document_index_text_name`,
  ADD KEY `simple_document_index_text_name` (`simple_document_index_text_name`) USING BTREE,
  ADD KEY `simple_document_index_text_value` (`simple_document_index_text_value`),
  ENGINE=InnoDB;

-- changed table `simple_documents_index_time_tbl`

ALTER TABLE `simple_documents_index_time_tbl`
  DROP INDEX `simple_document_index_time_name`;
ALTER TABLE `simple_documents_index_time_tbl`
  CHANGE COLUMN `simple_document_index_time_name` `simple_document_index_time_name` varchar(100) NOT NULL AFTER `simple_document_index_time_FK_simple_document_id`,
  ADD KEY `simple_document_index_time_name` (`simple_document_index_time_name`) USING BTREE,
  ADD KEY `simple_document_index_time_value` (`simple_document_index_time_value`),
  ENGINE=InnoDB;

-- changed table `simple_documents_tbl`

ALTER TABLE `simple_documents_tbl`
  CHANGE COLUMN `simple_document_FK_site_id` `simple_document_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `simple_document_id`,
  ENGINE=InnoDB;

-- changed table `speakingurls_tbl`

ALTER TABLE `speakingurls_tbl`
  CHANGE COLUMN `speakingurl_id` `speakingurl_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `speakingurl_FK_language_id` `speakingurl_FK_language_id` int(10) UNSIGNED NOT NULL AFTER `speakingurl_id`,
  ADD COLUMN `speakingurl_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `speakingurl_FK_language_id`,
  CHANGE COLUMN `speakingurl_FK` `speakingurl_FK` int(10) UNSIGNED NOT NULL AFTER `speakingurl_FK_site_id`,
  CHANGE COLUMN `speakingurl_option` `speakingurl_option` varchar(255) DEFAULT NULL AFTER `speakingurl_value`,
  ADD KEY `speakingurl_FK_site_id` (`speakingurl_FK_site_id`);

-- changed table `usergroups_tbl`

ALTER TABLE `usergroups_tbl`
  CHANGE COLUMN `usergroup_FK_site_id` `usergroup_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `usergroup_backEndAccess`,
  ENGINE=InnoDB;

-- changed table `userlogs_tbl`

ALTER TABLE `userlogs_tbl`
  DROP INDEX `userlog_FK_user_id`;
ALTER TABLE `userlogs_tbl`
  ADD COLUMN `userlog_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `userlog_FK_user_id`,
  CHANGE COLUMN `userlog_session` `userlog_session` varchar(50) NOT NULL DEFAULT '' AFTER `userlog_FK_site_id`,
  CHANGE COLUMN `userlog_ip` `userlog_ip` varchar(50) NOT NULL DEFAULT '' AFTER `userlog_session`,
  CHANGE COLUMN `userlog_date` `userlog_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `userlog_ip`,
  CHANGE COLUMN `userlog_lastAction` `userlog_lastAction` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `userlog_date`,
  ADD UNIQUE KEY `userlog_FK_user_id` (`userlog_FK_user_id`),
  ADD KEY `userlog_FK_site_id` (`userlog_FK_site_id`),
  ENGINE=InnoDB;

-- changed table `users_tbl`

ALTER TABLE `users_tbl`
  CHANGE COLUMN `user_FK_site_id` `user_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `user_FK_usergroup_id`,
  ADD COLUMN `user_title` varchar(50) DEFAULT NULL AFTER `user_lastName`,
  ADD COLUMN `user_companyName` varchar(255) DEFAULT NULL AFTER `user_title`,
  ADD COLUMN `user_address` varchar(255) DEFAULT NULL AFTER `user_companyName`,
  ADD COLUMN `user_city` varchar(255) DEFAULT NULL AFTER `user_address`,
  ADD COLUMN `user_zip` varchar(20) DEFAULT NULL AFTER `user_city`,
  ADD COLUMN `user_state` varchar(100) DEFAULT NULL AFTER `user_zip`,
  ADD COLUMN `user_country` varchar(100) DEFAULT NULL AFTER `user_state`,
  ADD COLUMN `user_FK_country_id` int(50) DEFAULT '0' AFTER `user_country`,
  ADD COLUMN `user_phone` varchar(100) DEFAULT NULL AFTER `user_FK_country_id`,
  ADD COLUMN `user_phone2` varchar(50) DEFAULT NULL AFTER `user_phone`,
  ADD COLUMN `user_mobile` varchar(50) DEFAULT NULL AFTER `user_phone2`,
  ADD COLUMN `user_fax` varchar(100) DEFAULT NULL AFTER `user_mobile`,
  CHANGE COLUMN `user_email` `user_email` varchar(255) NOT NULL DEFAULT '' AFTER `user_fax`,
  ADD COLUMN `user_www` varchar(255) DEFAULT NULL AFTER `user_email`,
  ADD COLUMN `user_birthday` date NOT NULL DEFAULT '0000-00-00' AFTER `user_www`,
  ADD COLUMN `user_sex` enum('M','F') DEFAULT 'M' AFTER `user_birthday`,
  ADD COLUMN `user_confirmCode` varchar(200) DEFAULT NULL AFTER `user_sex`,
  ADD COLUMN `user_wantNewsletter` tinyint(1) UNSIGNED DEFAULT '1' AFTER `user_confirmCode`,
  ADD COLUMN `user_isInMailinglist` tinyint(1) UNSIGNED DEFAULT '0' AFTER `user_wantNewsletter`,
  ADD COLUMN `user_position` varchar(255) DEFAULT NULL AFTER `user_isInMailinglist`,
  ADD COLUMN `user_department` varchar(255) DEFAULT NULL AFTER `user_position`,
  ADD COLUMN `user_fiscalCode` varchar(32) NOT NULL DEFAULT '' AFTER `user_department`,
  ADD COLUMN `user_vat` varchar(32) NOT NULL DEFAULT '' AFTER `user_fiscalCode`,
  ENGINE=InnoDB;

# New Tables

-- new table `joins_tbl`

CREATE TABLE IF NOT EXISTS `joins_tbl` (
  `join_id` int(1) UNSIGNED NOT NULL AUTO_INCREMENT,
  `join_FK_source_id` int(10) UNSIGNED NOT NULL,
  `join_FK_dest_id` int(10) UNSIGNED NOT NULL,
  `join_objectName` varchar(50) NOT NULL,
  PRIMARY KEY (`join_id`),
  KEY `join_FK_dest_id` (`join_FK_dest_id`),
  KEY `join_FK_source_id` (`join_FK_source_id`),
  KEY `join_objectName` (`join_objectName`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- new table `logs_tbl`

CREATE TABLE IF NOT EXISTS `logs_tbl` (
  `log_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `log_level` varchar(100) NOT NULL DEFAULT '',
  `log_date` datetime NOT NULL,
  `log_ip` varchar(20) DEFAULT NULL,
  `log_session` varchar(50) NOT NULL DEFAULT '',
  `log_group` varchar(50) NOT NULL DEFAULT '',
  `log_message` text NOT NULL,
  `log_FK_user_id` int(10) DEFAULT '0',
  `log_FK_site_id` int(10) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `log_FK_site_id` (`log_FK_site_id`),
  KEY `log_FK_user_id` (`log_FK_user_id`),
  KEY `log_group` (`log_group`),
  KEY `log_level` (`log_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# Disable Foreign Keys Check
SET FOREIGN_KEY_CHECKS = 1;
