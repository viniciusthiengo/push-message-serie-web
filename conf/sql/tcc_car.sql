CREATE TABLE tcc_car (
  id int(10) unsigned NOT NULL AUTO_INCREMENT,
  category tinyint(4) NOT NULL,
  model char(30) NOT NULL,
  brand varchar(30) NOT NULL,
  url_photo varchar(100) DEFAULT NULL,
  description varchar(700) DEFAULT NULL,
  tel varchar(18) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY image_url (url_photo)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;