/*
SQLyog Ultimate v11.11 (64 bit)
MySQL - 8.0.39-0ubuntu0.22.04.1 : Database - shopzit
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`shopzit` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `shopzit`;

/*Table structure for table `product_variation` */

DROP TABLE IF EXISTS `product_variation`;

CREATE TABLE `product_variation` (
  `variation_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `attribute1` varchar(255) DEFAULT NULL,
  `attribute2` varchar(255) DEFAULT NULL,
  `attribute3` varchar(255) DEFAULT NULL,
  `unit1` varchar(255) DEFAULT NULL,
  `unit2` varchar(255) DEFAULT NULL,
  `unit3` varchar(255) DEFAULT NULL,
  `option1` varchar(255) DEFAULT NULL,
  `option2` varchar(255) DEFAULT NULL,
  `option3` varchar(255) DEFAULT NULL,
  `product_variation_image` varchar(100) DEFAULT NULL,
  `price` float DEFAULT NULL,
  `sale_price` float DEFAULT NULL,
  `discount_percentage` double(8,2) NOT NULL DEFAULT '0.00',
  `receive_back_price` float DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `current_stock` int DEFAULT NULL,
  `maxquantitylimit` int DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `visibility` int DEFAULT NULL,
  `check_box_price1` int DEFAULT NULL,
  `check_box_price2` int DEFAULT NULL,
  `check_box_quantity1` int DEFAULT NULL,
  `check_box_quantity2` int DEFAULT NULL,
  `check_box_sku1` int DEFAULT NULL,
  `check_box_sku2` int DEFAULT NULL,
  `sales` int DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`variation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7492 DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
