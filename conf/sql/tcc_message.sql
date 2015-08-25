CREATE TABLE `tcc_message` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user_from` int(10) unsigned NOT NULL,
  `id_user_to` int(10) unsigned NOT NULL,
  `message` varchar(1000) NOT NULL,
  `reg_time` int(10) unsigned NOT NULL,
  `was_read` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1