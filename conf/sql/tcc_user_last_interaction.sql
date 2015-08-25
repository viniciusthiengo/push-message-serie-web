CREATE TABLE `tcc_user_last_interaction` (
  `id_user_from` int(10) unsigned NOT NULL,
  `id_user_to` int(10) unsigned NOT NULL,
  `reg_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id_user_from`,`id_user_to`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1