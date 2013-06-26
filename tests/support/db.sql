
DROP DATABASE IF EXISTS `dbtest`;

CREATE DATABASE `dbtest` DEFAULT CHARACTER SET utf8;

USE `dbtest`;

CREATE TABLE `foo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `ext` char(2) NOT NULL DEFAULT 'tv',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

INSERT INTO `foo` VALUES (1,'Foo','tv'),(2,'Bar','qq'),(3,'Zoo','tv'),(4,'Man','mu'),(5,'Ops','rs');