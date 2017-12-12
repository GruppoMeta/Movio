ALTER TABLE `speakingurls_tbl` CHANGE `speakingurl_id` `speakingurl_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `logs_tbl` CHANGE `log_id` `log_id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `users_tbl` DROP `user_extid`;
