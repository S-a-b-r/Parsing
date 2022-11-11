-- Adminer 4.8.1 MySQL 5.5.5-10.5.11-MariaDB dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `parsed_data`;
CREATE TABLE `parsed_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `trade_num` varchar(20) NOT NULL,
  `lot_num` int(11) NOT NULL,
  `lot_href` varchar(100) NOT NULL,
  `cost` float NOT NULL,
  `trade_datetime` datetime NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `parsed_data` (`id`, `trade_num`, `lot_num`, `lot_href`, `cost`, `trade_datetime`, `status`) VALUES
(1,	'0014007',	1,	'http://www.arbitat.ru/public/auctions/view/14008/',	352000,	'2022-12-09 13:00:00',	'Прием заявок'),
(2,	'0014007',	2,	'http://www.arbitat.ru/public/auctions/view/14008/',	610000,	'2022-12-09 13:00:00',	'Прием заявок'),
(3,	'0014002',	1,	'http://www.arbitat.ru/public/auctions/view/14004/',	981000,	'2022-12-08 14:00:00',	'Прием заявок');

-- 2022-11-11 13:38:32
