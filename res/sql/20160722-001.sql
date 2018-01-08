CREATE TABLE `queue_jobs` (
  `id` mediumint(20) NOT NULL AUTO_INCREMENT,
  `queue` char(32) NOT NULL DEFAULT 'default',
  `data` mediumtext NOT NULL,
  `started_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `delay_until` datetime DEFAULT NULL,
  `lock_id` binary(16) DEFAULT NULL,
  `attempts` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `queue` (`queue`,`lock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

