
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ACTIVE` tinyint(1) unsigned NOT NULL COMMENT 'Активность',
  `ACTIVE_FROM` datetime DEFAULT NULL COMMENT 'Начало активности',
  `ACTIVE_TO` datetime DEFAULT NULL COMMENT 'Окончание активности',
  `SORT` mediumint(8) unsigned NOT NULL COMMENT 'Сортировка',
  `CODE` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Символьный код',
  `NAME` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Название',
  `PREVIEW_TEXT` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Анонс',
  `DETAIL_TEXT` mediumtext COLLATE utf8_unicode_ci NOT NULL COMMENT 'Содержание',
  `DETAIL_PICTURE` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Изображения',
  `TAGS` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Теги',
  `DATE_CREATE` datetime NOT NULL COMMENT 'Дата создания',
  `DATE_MODIFY` datetime NOT NULL COMMENT 'Дата изменения',
  PRIMARY KEY (`id`),
  KEY `SORT` (`SORT`),
  KEY `CODE` (`CODE`),
  KEY `ACTIVE_FROM` (`ACTIVE_FROM`),
  KEY `ACTIVE_TO` (`ACTIVE_TO`),
  KEY `ACTIVE` (`ACTIVE`),
  KEY `DATE_CREATE` (`DATE_CREATE`),
  KEY `DATE_MODIFY` (`DATE_MODIFY`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Новости';

INSERT INTO `news` (`id`, `NAME`, `PREVIEW_TEXT`, `DETAIL_TEXT`, `DETAIL_PICTURE`, `DATE_CREATE`, `DATE_MODIFY`, `ACTIVE`, `ACTIVE_FROM`, `ACTIVE_TO`, `SORT`, `CODE`, `TAGS`) VALUES
(1,	'Тест',	'Анонс',	'Описание',	'[\"image1.png\", \"image2.png\", \"image3.png\"]',	'2016-04-15 16:17:42',	'2016-04-15 16:17:42',	1,	NULL,	NULL,	100,	'test',	'some-test, breaking-news');
