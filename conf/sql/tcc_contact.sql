CREATE TABLE tcc_contact (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  id_car int(10) unsigned NOT NULL,
  email varchar(100) NOT NULL,
  subject varchar(80) NOT NULL,
  message varchar(500) NOT NULL,
  reg_time int(10) unsigned NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;