-- MySQL dump 10.13  Distrib 8.0.44, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: klimatici_db
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `brand_id` int NOT NULL,
  `model_name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int DEFAULT '0',
  `energy_class` varchar(10) DEFAULT NULL,
  `btu_power` int DEFAULT NULL,
  `warranty_months` int DEFAULT '24',
  `main_image_url` varchar(500) DEFAULT NULL,
  `features` text,
  `weight_kg` decimal(5,2) DEFAULT NULL,
  `dimensions` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`product_id`),
  KEY `idx_products_brand` (`brand_id`),
  KEY `idx_products_price` (`price`),
  KEY `idx_products_energy` (`energy_class`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`brand_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'CS-TZ25ZKEW Etherea','Инверторен климатик с най-висок енергиен клас и елегантен дизайн. Тихо работещ с технология Nanoe-X за пречистване на въздуха. Идеален за спални и дневни.',1299.00,15,'A+++',9000,60,'https://panashop.ua/image/cache/catalog/shared_photo/cs-z20zkew-cu-z20zke-white-700x700.jpg','Wi-Fi управление, Nanoe-X технология, Интелигентен сензор, Автоматично почистване',9.50,'80x29x23 см',1,'2026-02-07 11:20:22','2026-03-12 14:08:35'),(2,1,'CS-Z35ZKEW Etherea','Мощен инверторен климатик 12000 BTU с премиум характеристики. Подходящ за по-големи помещения до 35 кв.м.',1499.00,12,'A+++',12000,60,'https://www.antonovclima.com/wp-content/uploads/klimatik-panasonic-cs-z35xkew-etherea-r32-wifi.png','Wi-Fi управление, Nanoe-X технология, Икономичен режим, Нощен режим',11.00,'90x30x24 см',1,'2026-02-07 11:20:22','2026-03-12 14:10:32'),(3,1,'CS-Z50ZKEW Nordic','Флагман модел за работа при екстремно ниски температури до -35°C. Мощност 18000 BTU.',2199.00,8,'A++',18000,60,'https://ivalsclima.bg/images/thumbs/0000933_invertoren-klimatik-panasonic-cs-z50zkewcu-z50zke-etherea-18000-btu-klas-aa_550.webp','Работа до -35°C, Двойна изолация, Автоматично размразяване, Turbo режим',15.50,'100x34x26 см',1,'2026-02-07 11:20:22','2026-03-12 14:11:26'),(4,2,'FTXM25R Perfera','Интелигентен климатик с 3D въздушен поток и Flash Streamer технология. Тих и ефективен за помещения до 25 кв.м.',1450.00,18,'A+++',9000,36,'https://www.eracon.bg/image/cache/catalog/Daikin/FTXM/FTXM-R-1000x1000.jpg','Flash Streamer, 3D въздушен поток, Wi-Fi control, Intelligent Eye сензор',9.00,'79x28x23 см',1,'2026-02-07 11:20:22','2026-03-12 14:14:02'),(5,2,'FTXM35R Perfera','По-мощна версия на Perfera серията с 12000 BTU. Отлична енергийна ефективност и тихо работене.',1650.00,14,'A+++',12000,36,'https://www.daikin-bg.com/image/cache/catalog/16335730808-1000x1000.jpg','Flash Streamer, Comfort Mode, Онлайн контрол, Coanda ефект',10.50,'89x29x24 см',1,'2026-02-07 11:20:22','2026-03-12 14:17:21'),(6,2,'FTXP50M Comfora','Мощен климатик за големи пространства. Енергоспестяващ с отлични характеристики.',1899.00,10,'A++',18000,36,'https://daricclima.bg/ufiles/articles/1/2023/05/klimatik-daikin-komfora-ftxp50m-rxp50m_8597.jpg','Икономичен режим, Таймер 24ч, Турбо режим, R32 фреон',14.00,'98x33x25 см',1,'2026-02-07 11:20:22','2026-03-12 14:19:38'),(7,2,'FTXTP71L Premium','Професионален климатик за много големи помещения. 24000 BTU мощност.',2599.00,5,'A++',24000,36,'https://www.saturnsales.co.uk/images/super/Daikin_Standard.jpg','Професионална серия, Мощно охлаждане, Дистанционно управление',18.50,'110x36x28 см',1,'2026-02-07 11:20:22','2026-03-12 14:20:38'),(8,3,'MSZ-LN25VGW Diamond','Премиум климатик с уникален дизайн и най-съвременни технологии. Клас A+++ и изключително тихо работене.',1799.00,10,'A+++',9000,60,'https://www.estetik-klima.com/image/cache/catalog/products/hiperinvertoren-klimatik-mitsubishi-electric-msz-25ln-vgw-muz-ln25-vg-1200x1200.jpg','Plasma Quad Plus, Wi-Fi, 3D i-see Sensor, Двойно филтриране',10.00,'89x30x23 см',1,'2026-02-07 11:20:22','2026-03-12 14:32:09'),(9,3,'MSZ-LN35VGW Diamond','Мощност 12000 BTU с всички предимства на Diamond серията. Подходящ за средни до големи помещения.',1999.00,9,'A+++',12000,60,'https://klimatizacie-bratislava.eu/wp-content/uploads/2023/06/Mitsubishi-DIAMOND-pearl.jpg','i-see Sensor, Plasma филтър, MELCloud контрол, Нощен режим',11.50,'99x31x24 см',1,'2026-02-07 11:20:22','2026-03-12 14:27:20'),(10,3,'MSZ-AP25VGK','Компактен и ефективен модел със стандартни функции на достъпна цена.',1199.00,20,'A++',9000,36,'https://s13emagst.akamaized.net/products/32049/32048745/images/res_e36077f420d0e29337db5c071add8fd3.jpg','Компактен дизайн, Енергоспестяващ, Таймер, Auto режим',8.50,'78x28x22 см',1,'2026-02-07 11:20:22','2026-03-12 14:33:03'),(11,4,'Climate 5000 RAC','Немско качество и надеждност. Инверторна технология за максимална ефективност.',1350.00,12,'A++',9000,24,'https://rodopiklima.bg/wp-content/uploads/2025/02/invertoren-klimatik-toyotomi-utn12aputg12ap-umi-12000-btu-copy-image_61522c98dbd27_1280x1280.jpeg','Германска технология, Тихо работене, Автоматичен режим, LED дисплей',9.50,'80x29x23 см',1,'2026-02-07 11:20:22','2026-03-12 14:50:13'),(12,4,'Climate 5000i RAC','Подобрена версия с Wi-Fi управление и интелигентни функции.',1550.00,10,'A++',12000,24,'https://i.citrus.world/imgcache/size_800/uploads/shop/2/5/252a386c000227beb1f536fb8970d25c.jpg','Wi-Fi контрол, Smart сензори, Еко режим, Самодиагностика',11.00,'85x30x24 cm',1,'2026-02-07 11:20:22','2026-03-12 14:57:16'),(13,5,'Artcool Gallery AG09','Уникален дизайн с възможност за персонализация. Модерен и стилен.',1699.00,8,'A+++',9000,24,'https://s13emagst.akamaized.net/products/1686/1685458/images/res_89dd47d2f2af0efbead27d4dc074cba4.jpg','Artcool дизайн, Променяема рамка, Wi-Fi, Dual Cool технология',10.00,'84x30x21 см',1,'2026-02-07 11:20:22','2026-03-12 14:58:21'),(14,5,'Standard Plus PC12','Класически модел с добро съотношение цена-качество. Надежден и ефективен.',999.00,25,'A++',12000,24,'https://respectclima.bg/image/cache/catalog/LG/allmag_572771338-600x600.jpg','Gold Fin защита, Автоматично почистване, 4-посочен въздушен поток',10.50,'84x29x23 см',1,'2026-02-07 11:20:22','2026-03-12 14:59:24'),(15,5,'Dual Cool S4-W18','Мощен климатик с бързо охлаждане. Dual Inverter компресор за висока ефективност.',1899.00,12,'A++',18000,24,'https://praga.com.ar/wp-content/uploads/2022/08/D_728960-MLA41133399848_032020-F.jpg','Dual Inverter, Бързо охлаждане, 10-годишна гаранция на компресора',15.00,'95x33x25 см',1,'2026-02-07 11:20:22','2026-03-12 15:00:47'),(16,6,'Fairy GWH09ACC','Икономичен модел с добри характеристики. Отличен избор за ограничен бюджет.',799.00,30,'A++',9000,24,'https://megaelectronics.bg/wp-content/uploads/2021/05/02201824591106932813.jpg','Икономичен, Компактен дизайн, Таймер 24ч, I Feel функция',8.00,'71x25x19 см',1,'2026-02-07 11:20:22','2026-03-12 15:01:57'),(17,6,'Amber GWH12YD','Модел от средна ценова категория с Wi-Fi управление.',1099.00,18,'A++',12000,24,'https://ekomanai.lt/wp-content/uploads/2023/11/greeamb.jpeg','Wi-Fi Ready, G10 инвертор, Катехинов филтър, Тих режим',9.50,'80x28x22 см',1,'2026-02-07 11:20:22','2026-03-12 15:04:43'),(18,6,'U-Crown GWH18UC','Мощен и ефективен климатик за големи помещения. Отлично съотношение цена-качество.',1599.00,14,'A++',18000,24,'https://hemeltron.ee/images/virtuemart/product/Gree_U_Crown_GWH_56387afc124e1.jpg','G10 инвертор, Turbo режим, Самодиагностика, Здрав въздух',13.50,'92x32x24 см',1,'2026-02-07 11:20:22','2026-03-12 15:06:32'),(19,2,'FTXJ25MW Emura','Дизайнерски климатик с минималистичен стил. Най-тихият климатик на Daikin.',2299.00,6,'A+++',9000,36,'https://www.antonovclima.com/wp-content/uploads/klimatik-daikin-ftxj25mw-rxj25m-emura-r32.jpg','Най-тих климатик, Премиум дизайн, Онлайн управление, 2-зонов сензор',10.50,'79x30x19 см',1,'2026-02-07 11:20:22','2026-03-12 15:17:26'),(20,1,'CS-Z25ZKEW Nordic','Среден модел от Nordic серията. Работа при ниски температури до -30°C.',1599.00,10,'A++',9000,60,'https://ivalsclima.bg/images/thumbs/0000928_hiperinvertoren-klimatik-panasonic-cs-z25zkewcu-z25zke-etherea-9000-btu-klas-aa_550.webp','Работа до -30°C, Автоматично размразяване, Интелигентен сензор',10.00,'85x30x24 см',1,'2026-02-07 11:20:22','2026-03-12 15:18:24');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-03-28  9:21:55
