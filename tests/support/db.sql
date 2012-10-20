
/*!40000 DROP DATABASE IF EXISTS `dbtest`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `dbtest` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `dbtest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `foo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `ext` char(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
INSERT INTO `foo` VALUES (1,'Foo','tv'),(2,'Bar','qq'),(3,'Zoo','tv'),(4,'Man','mu'),(5,'Ops','rs');