CREATE TABLE tcc_user (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  registration_id varchar(200) NOT NULL,
  reg_time int(10) unsigned NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY registration_id (registration_id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;