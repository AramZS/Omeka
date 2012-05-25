CREATE TABLE IF NOT EXISTS `%PREFIX%search_texts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `record_name` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `record_id` int(10) unsigned NOT NULL,
  `public` tinyint(1) NOT NULL,
  `text` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `record_name` (`record_name`,`record_id`),
  FULLTEXT KEY `text` (`text`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;