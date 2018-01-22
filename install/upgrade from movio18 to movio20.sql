# Disable Foreign Keys Check
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = '';

# Deleted Tables

# Changed Tables

-- changed table `custom_code_mapping_tbl`

SELECT 'Altering table custom_code_mapping_tbl';
ALTER TABLE `custom_code_mapping_tbl`
  CHANGE COLUMN `custom_code_mapping_id` `custom_code_mapping_id` int(10) UNSIGNED NOT NULL FIRST;

-- changed table `documents_detail_tbl`

SELECT 'Altering table documents_detail_tbl';
ALTER TABLE `documents_detail_tbl`
  CHANGE COLUMN `document_detail_id` `document_detail_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `document_detail_FK_document_id` `document_detail_FK_document_id` int(10) UNSIGNED NOT NULL AFTER `document_detail_id`,
  CHANGE COLUMN `document_detail_FK_language_id` `document_detail_FK_language_id` int(10) UNSIGNED NOT NULL AFTER `document_detail_FK_document_id`,
  CHANGE COLUMN `document_detail_FK_user_id` `document_detail_FK_user_id` int(10) UNSIGNED NOT NULL AFTER `document_detail_FK_language_id`,
  CHANGE COLUMN `document_detail_translated` `document_detail_translated` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `document_detail_status`,
  CHANGE COLUMN `document_detail_isVisible` `document_detail_isVisible` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `document_detail_object`;

-- changed table `documents_index_datetime_tbl`

SELECT 'Altering table documents_index_datetime_tbl';
ALTER TABLE `documents_index_datetime_tbl`
  CHANGE COLUMN `document_index_datetime_id` `document_index_datetime_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `document_index_datetime_FK_document_detail_id` `document_index_datetime_FK_document_detail_id` int(10) UNSIGNED NOT NULL AFTER `document_index_datetime_id`;

-- changed table `documents_index_date_tbl`

SELECT 'Altering table documents_index_date_tbl';
ALTER TABLE `documents_index_date_tbl`
  CHANGE COLUMN `document_index_date_id` `document_index_date_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `document_index_date_FK_document_detail_id` `document_index_date_FK_document_detail_id` int(10) UNSIGNED NOT NULL AFTER `document_index_date_id`;

-- changed table `documents_index_fulltext_tbl`

SELECT 'Altering table documents_index_fulltext_tbl';
ALTER TABLE `documents_index_fulltext_tbl`
  CHANGE COLUMN `document_index_fulltext_id` `document_index_fulltext_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `document_index_fulltext_FK_document_detail_id` `document_index_fulltext_FK_document_detail_id` int(10) UNSIGNED NOT NULL AFTER `document_index_fulltext_id`;

-- changed table `documents_index_int_tbl`

SELECT 'Altering table documents_index_int_tbl';
ALTER TABLE `documents_index_int_tbl`
  CHANGE COLUMN `document_index_int_id` `document_index_int_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `document_index_int_FK_document_detail_id` `document_index_int_FK_document_detail_id` int(10) UNSIGNED NOT NULL AFTER `document_index_int_id`;

-- changed table `documents_index_text_tbl`

SELECT 'Altering table documents_index_text_tbl';
ALTER TABLE `documents_index_text_tbl`
  CHANGE COLUMN `document_index_text_id` `document_index_text_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `document_index_text_FK_document_detail_id` `document_index_text_FK_document_detail_id` int(10) UNSIGNED NOT NULL AFTER `document_index_text_id`;

-- changed table `documents_index_time_tbl`

SELECT 'Altering table documents_index_time_tbl';
ALTER TABLE `documents_index_time_tbl`
  CHANGE COLUMN `document_index_time_id` `document_index_time_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `document_index_time_FK_document_detail_id` `document_index_time_FK_document_detail_id` int(10) UNSIGNED NOT NULL AFTER `document_index_time_id`;

-- changed table `documents_tbl`

SELECT 'Altering table documents_tbl';
ALTER TABLE `documents_tbl`
  CHANGE COLUMN `document_id` `document_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `document_FK_site_id` `document_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `document_creationDate`;

-- changed table `entity_properties_tbl`

SELECT 'Altering table entity_properties_tbl';
ALTER TABLE `entity_properties_tbl`
  CHANGE COLUMN `entity_properties_id` `entity_properties_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `entity_properties_FK_entity_id` `entity_properties_FK_entity_id` int(10) UNSIGNED NOT NULL AFTER `entity_properties_id`,
  CHANGE COLUMN `entity_properties_target_FK_entity_id` `entity_properties_target_FK_entity_id` int(10) UNSIGNED DEFAULT NULL AFTER `entity_properties_type`;

-- changed table `entity_tbl`

SELECT 'Altering table entity_tbl';
ALTER TABLE `entity_tbl`
  CHANGE COLUMN `entity_id` `entity_id` int(10) UNSIGNED NOT NULL FIRST;

-- changed table `joins_tbl`

SELECT 'Altering table joins_tbl';
ALTER TABLE `joins_tbl`
  CHANGE COLUMN `join_id` `join_id` int(1) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `join_FK_source_id` `join_FK_source_id` int(10) UNSIGNED NOT NULL AFTER `join_id`,
  CHANGE COLUMN `join_FK_dest_id` `join_FK_dest_id` int(10) UNSIGNED NOT NULL AFTER `join_FK_source_id`;

-- changed table `languages_tbl`

SELECT 'Altering table languages_tbl';
ALTER TABLE `languages_tbl`
  CHANGE COLUMN `language_id` `language_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `language_FK_site_id` `language_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `language_id`,
  CHANGE COLUMN `language_FK_country_id` `language_FK_country_id` int(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `language_code`,
  CHANGE COLUMN `language_isDefault` `language_isDefault` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `language_FK_country_id`,
  CHANGE COLUMN `language_order` `language_order` int(4) UNSIGNED NOT NULL DEFAULT '0' AFTER `language_isDefault`;

-- changed table `logs_tbl`

SELECT 'Altering table logs_tbl';
ALTER TABLE `logs_tbl`
  CHANGE COLUMN `log_id` `log_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `log_FK_site_id` `log_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `log_FK_user_id`;

-- changed table `mediadetails_tbl`

SELECT 'Altering table mediadetails_tbl';
ALTER TABLE `mediadetails_tbl`
  CHANGE COLUMN `mediadetail_id` `mediadetail_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `mediadetail_FK_media_id` `mediadetail_FK_media_id` int(10) UNSIGNED NOT NULL AFTER `mediadetail_id`,
  CHANGE COLUMN `media_FK_language_id` `media_FK_language_id` int(10) UNSIGNED NOT NULL AFTER `mediadetail_FK_media_id`,
  CHANGE COLUMN `media_FK_user_id` `media_FK_user_id` int(10) UNSIGNED NOT NULL AFTER `media_FK_language_id`;

-- changed table `media_tbl`

SELECT 'Altering table media_tbl';
ALTER TABLE `media_tbl`
  CHANGE COLUMN `media_id` `media_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `media_FK_site_id` `media_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `media_id`,
  CHANGE COLUMN `media_size` `media_size` int(4) UNSIGNED NOT NULL DEFAULT '0' AFTER `media_fileName`;

-- changed table `menudetails_tbl`

SELECT 'Altering table menudetails_tbl';
ALTER TABLE `menudetails_tbl`
  CHANGE COLUMN `menudetail_id` `menudetail_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `menudetail_FK_menu_id` `menudetail_FK_menu_id` int(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `menudetail_id`,
  CHANGE COLUMN `menudetail_FK_language_id` `menudetail_FK_language_id` int(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `menudetail_FK_menu_id`,
  CHANGE COLUMN `menudetail_isVisible` `menudetail_isVisible` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `menudetail_coverage`;

-- changed table `menus_tbl`

SELECT 'Altering table menus_tbl';
ALTER TABLE `menus_tbl`
  CHANGE COLUMN `menu_id` `menu_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `menu_FK_site_id` `menu_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `menu_id`,
  CHANGE COLUMN `menu_parentId` `menu_parentId` int(10) UNSIGNED DEFAULT '0' AFTER `menu_FK_site_id`,
  CHANGE COLUMN `menu_order` `menu_order` int(4) UNSIGNED DEFAULT '0' AFTER `menu_pageType`,
  CHANGE COLUMN `menu_hasPreview` `menu_hasPreview` tinyint(1) UNSIGNED DEFAULT '1' AFTER `menu_order`,
  CHANGE COLUMN `menu_isLocked` `menu_isLocked` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' AFTER `menu_url`;

-- changed table `mobilecodes_tbl`

SELECT 'Altering table mobilecodes_tbl';
ALTER TABLE `mobilecodes_tbl`
  CHANGE COLUMN `mobilecode_id` `mobilecode_id` int(10) UNSIGNED NOT NULL FIRST;

-- changed table `mobilecontents_tbl`

SELECT 'Altering table mobilecontents_tbl';
ALTER TABLE `mobilecontents_tbl`
  CHANGE COLUMN `content_id` `content_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `content_menuId` `content_menuId` int(10) UNSIGNED NOT NULL AFTER `content_id`,
  CHANGE COLUMN `content_documentId` `content_documentId` int(10) UNSIGNED NOT NULL AFTER `content_menuId`,
  CHANGE COLUMN `content_parent` `content_parent` int(10) UNSIGNED DEFAULT '0' AFTER `content_pageType`;

-- changed table `mobilefulltext_tbl`

SELECT 'Altering table mobilefulltext_tbl';
ALTER TABLE `mobilefulltext_tbl`
  CHANGE COLUMN `mobilefulltext_id` `mobilefulltext_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `mobilefulltext_FK_content_id` `mobilefulltext_FK_content_id` int(10) UNSIGNED NOT NULL AFTER `mobilefulltext_id`;

-- changed table `registry_tbl`

SELECT 'Altering table registry_tbl';
ALTER TABLE `registry_tbl`
  CHANGE COLUMN `registry_FK_site_id` `registry_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `registry_id`;

-- changed table `simple_documents_index_datetime_tbl`

SELECT 'Altering table simple_documents_index_datetime_tbl';
ALTER TABLE `simple_documents_index_datetime_tbl`
  CHANGE COLUMN `simple_document_index_datetime_id` `simple_document_index_datetime_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `simple_document_index_datetime_FK_simple_document_id` `simple_document_index_datetime_FK_simple_document_id` int(10) UNSIGNED NOT NULL AFTER `simple_document_index_datetime_id`;

-- changed table `simple_documents_index_date_tbl`

SELECT 'Altering table simple_documents_index_date_tbl';
ALTER TABLE `simple_documents_index_date_tbl`
  CHANGE COLUMN `simple_document_index_date_id` `simple_document_index_date_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `simple_document_index_date_FK_simple_document_id` `simple_document_index_date_FK_simple_document_id` int(10) UNSIGNED NOT NULL AFTER `simple_document_index_date_id`;

-- changed table `simple_documents_index_fulltext_tbl`

SELECT 'Altering table simple_documents_index_fulltext_tbl';
ALTER TABLE `simple_documents_index_fulltext_tbl`
  CHANGE COLUMN `simple_document_index_fulltext_id` `simple_document_index_fulltext_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `simple_document_index_fulltext_FK_simple_document_id` `simple_document_index_fulltext_FK_simple_document_id` int(10) UNSIGNED NOT NULL AFTER `simple_document_index_fulltext_id`;

-- changed table `simple_documents_index_int_tbl`

SELECT 'Altering table simple_documents_index_int_tbl';
ALTER TABLE `simple_documents_index_int_tbl`
  CHANGE COLUMN `simple_document_index_int_id` `simple_document_index_int_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `simple_document_index_int_FK_simple_document_id` `simple_document_index_int_FK_simple_document_id` int(10) UNSIGNED NOT NULL AFTER `simple_document_index_int_id`;

-- changed table `simple_documents_index_text_tbl`

SELECT 'Altering table simple_documents_index_text_tbl';
ALTER TABLE `simple_documents_index_text_tbl`
  CHANGE COLUMN `simple_document_index_text_id` `simple_document_index_text_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `simple_document_index_text_FK_simple_document_id` `simple_document_index_text_FK_simple_document_id` int(10) UNSIGNED NOT NULL AFTER `simple_document_index_text_id`;

-- changed table `simple_documents_index_time_tbl`

SELECT 'Altering table simple_documents_index_time_tbl';
ALTER TABLE `simple_documents_index_time_tbl`
  CHANGE COLUMN `simple_document_index_time_id` `simple_document_index_time_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `simple_document_index_time_FK_simple_document_id` `simple_document_index_time_FK_simple_document_id` int(10) UNSIGNED NOT NULL AFTER `simple_document_index_time_id`;

-- changed table `simple_documents_tbl`

SELECT 'Altering table simple_documents_tbl';
ALTER TABLE `simple_documents_tbl`
  CHANGE COLUMN `simple_document_id` `simple_document_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `simple_document_FK_site_id` `simple_document_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `simple_document_id`;

-- changed table `speakingurls_tbl`

SELECT 'Altering table speakingurls_tbl';
ALTER TABLE `speakingurls_tbl`
  CHANGE COLUMN `speakingurl_id` `speakingurl_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `speakingurl_FK_language_id` `speakingurl_FK_language_id` int(10) UNSIGNED NOT NULL AFTER `speakingurl_id`,
  CHANGE COLUMN `speakingurl_FK_site_id` `speakingurl_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `speakingurl_FK_language_id`,
  CHANGE COLUMN `speakingurl_FK` `speakingurl_FK` int(10) UNSIGNED NOT NULL AFTER `speakingurl_FK_site_id`;

-- changed table `usergroups_tbl`

SELECT 'Altering table usergroups_tbl';
ALTER TABLE `usergroups_tbl`
  CHANGE COLUMN `usergroup_FK_site_id` `usergroup_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `usergroup_backEndAccess`;

-- changed table `userlogs_tbl`

SELECT 'Altering table userlogs_tbl';
ALTER TABLE `userlogs_tbl`
  CHANGE COLUMN `userlog_id` `userlog_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `userlog_FK_user_id` `userlog_FK_user_id` int(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `userlog_id`,
  CHANGE COLUMN `userlog_FK_site_id` `userlog_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `userlog_FK_user_id`;

-- changed table `users_tbl`

SELECT 'Altering table users_tbl';
ALTER TABLE `users_tbl`
  DROP COLUMN `user_extid`;
ALTER TABLE `users_tbl`
  CHANGE COLUMN `user_id` `user_id` int(10) UNSIGNED NOT NULL FIRST,
  CHANGE COLUMN `user_FK_usergroup_id` `user_FK_usergroup_id` int(10) UNSIGNED NOT NULL DEFAULT '2' AFTER `user_id`,
  CHANGE COLUMN `user_FK_site_id` `user_FK_site_id` int(10) UNSIGNED DEFAULT NULL AFTER `user_FK_usergroup_id`,
  CHANGE COLUMN `user_isActive` `user_isActive` tinyint(1) UNSIGNED NOT NULL DEFAULT '1' AFTER `user_dateCreation`,
  CHANGE COLUMN `user_wantNewsletter` `user_wantNewsletter` tinyint(1) UNSIGNED DEFAULT '1' AFTER `user_confirmCode`,
  CHANGE COLUMN `user_isInMailinglist` `user_isInMailinglist` tinyint(1) UNSIGNED DEFAULT '0' AFTER `user_wantNewsletter`,
  CHANGE COLUMN `user_fiscalCode` `user_fiscalCode` varchar(32) NOT NULL DEFAULT '' AFTER `user_department`,
  CHANGE COLUMN `user_vat` `user_vat` varchar(32) NOT NULL DEFAULT '' AFTER `user_fiscalCode`;

# New Tables

# Disable Foreign Keys Check
SET FOREIGN_KEY_CHECKS = 1;
