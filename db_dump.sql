/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-12.2.2-MariaDB, for osx10.21 (arm64)
--
-- Host: localhost    Database: webiusportaal
-- ------------------------------------------------------
-- Server version	12.2.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `app_settings` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_app_settings` (`website_id`,`setting_key`),
  CONSTRAINT `fk_app_settings_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `assortiment_categorieen`
--

DROP TABLE IF EXISTS `assortiment_categorieen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `assortiment_categorieen` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `titel` varchar(255) NOT NULL,
  `beschrijving` text NOT NULL,
  `afbeelding` varchar(255) DEFAULT NULL,
  `badge_tekst` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_assortiment_categorieen_website` (`website_id`),
  CONSTRAINT `fk_assortiment_categorieen_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assortiment_categorieen`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `assortiment_categorieen` WRITE;
/*!40000 ALTER TABLE `assortiment_categorieen` DISABLE KEYS */;
INSERT INTO `assortiment_categorieen` VALUES
('fbe70632-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Boeken & Tijdschriften','Van de nieuwste thrillers uit de Primera Top 10 tot een wandvullend assortiment aan tijdschriften. Wij hebben voor elke lezer een passend verhaal.','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/assortiment/1773333994_boeken.jpeg','Wekelijks Nieuw',3,'2026-08-24 18:16:53'),
('fbe7545c-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Wenskaarten','Voor elk moment de juiste woorden. Met duizenden kaarten van o.a. Hallmark en Paperclip vind je bij ons de grootste collectie van Goor en omstreken.','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/assortiment/1773334471_WhatsApp Image 2026-03-12 at 17.52.20.jpeg','Grootste Keuze',2,'2026-08-24 18:16:53'),
('fbe77108-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Nederlandse Loterij','Alle kansspelen in onze winkel','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/assortiment/1771968533_nederlandseloterij.jpg','Loterij',4,'2026-08-24 18:16:53'),
('fbe78d50-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Gifts','We hebben een ruim assortiment cadeau-artikelen, voor elke gelegenheid en voor elke portemonee. We hebben een basis aan vaste artikelen en natuurlijk ook per seizoen wat actuele gifts.','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/assortiment/1773334024_WhatsApp Image 2026-03-12 at 14.49.39 (10).jpeg','Ruime Keus',1,'2026-08-24 18:16:53'),
('fbe7bbfe-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Telecom','Apple- en Samsung telefoonaccessoires','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/assortiment/1773333984_telecom.jpeg','Authorised Reseller',5,'2026-08-24 18:16:53'),
('fbe7d56c-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Kantoor Artikelen','Kantoor Artikelen, Batterijen en inktcardriges','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/assortiment/1773334374_kantoorartikelen.jpeg','Alles voor uw Kantoor',6,'2026-08-24 18:16:53');
/*!40000 ALTER TABLE `assortiment_categorieen` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `assortiment_merken`
--

DROP TABLE IF EXISTS `assortiment_merken`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `assortiment_merken` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `category_id` char(36) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `logo_path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_assortiment_merken_website` (`website_id`),
  KEY `idx_assortiment_merken_category` (`category_id`),
  CONSTRAINT `fk_assortiment_merken_category` FOREIGN KEY (`category_id`) REFERENCES `assortiment_categorieen` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assortiment_merken_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assortiment_merken`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `assortiment_merken` WRITE;
/*!40000 ALTER TABLE `assortiment_merken` DISABLE KEYS */;
INSERT INTO `assortiment_merken` VALUES
('fbe81b58-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe70632-9fe7-11f1-b21d-06bd6669bc40','Betapress','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772113865_betapress.png',1),
('fbe824fe-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe70632-9fe7-11f1-b21d-06bd6669bc40','Aldipress','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772113924_aldipress.png',2),
('fbe831c4-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe70632-9fe7-11f1-b21d-06bd6669bc40','Book & Service','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772114028_book_en_service.png',3),
('fbe83e3a-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe7545c-9fe7-11f1-b21d-06bd6669bc40','Hallmark','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772113711_hallmark.jpg',1),
('fbe84d3a-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe7545c-9fe7-11f1-b21d-06bd6669bc40','Artige','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772113755_artige.png',2),
('fbe85a00-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe77108-9fe7-11f1-b21d-06bd6669bc40','Staatsloterij','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772054189_staatsloterij.webp',1),
('fbe869d2-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe77108-9fe7-11f1-b21d-06bd6669bc40','Lotto','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772054200_lotto.jpeg',2),
('fbe873aa-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe77108-9fe7-11f1-b21d-06bd6669bc40','Eurojackpot','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772054213_eurojackpot.webp',3),
('fbe889bc-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe77108-9fe7-11f1-b21d-06bd6669bc40','TOTO','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772054228_toto.jpeg',4),
('fbe89826-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe77108-9fe7-11f1-b21d-06bd6669bc40','Luckyday','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772054254_luckyday.png',5),
('fbe8a898-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe77108-9fe7-11f1-b21d-06bd6669bc40','Krasloten','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772054267_krasloten_placeholder.jpg',6),
('fbe8b6f8-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe77108-9fe7-11f1-b21d-06bd6669bc40','Miljoenenspel','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772054287_miljoenenspel.png',7),
('fbe8c666-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','Sen & Zo','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772025871_Senenzo_logo_2021_removebg_preview.png',1),
('fbe8d836-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','Van Vliet','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772025974_vanvliet.jpg',2),
('fbe8eb6e-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','Ashleigh & Burwood','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772026355_asleigh.png',3),
('fbe8fdac-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','100% leuk','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772026373_100leuk.png',4),
('fbe90e96-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','Nouka','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772026382_nouka.png',5),
('fbe922fa-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','Miko gift creators','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772026486_miko.png',6),
('fbe95680-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','999 games','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772026496_999.jpg',7),
('fbe985e2-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','Boosterbox','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772026655_boosterbox.jpg',8),
('fbe9ab44-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','Top Model','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772026730_topmodel.png',9),
('fbe9c21e-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','Voor Jou Chocolade','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772026817_voorjou.jpg',10),
('fbe9f3b0-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','Robotime','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772036557_robotime.jpg',11),
('fbea054e-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','Woodart','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1772036567_Woodart_Logo.png',12),
('fbea15de-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe78d50-9fe7-11f1-b21d-06bd6669bc40','woezel en pip','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1773326731_woezel.png',13),
('fbea24b6-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe7bbfe-9fe7-11f1-b21d-06bd6669bc40','Samsung','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1773326886_samsung.jpg',1),
('fbea3a00-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fbe7bbfe-9fe7-11f1-b21d-06bd6669bc40','Apple','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/merken/1773326895_apple.jpg',2);
/*!40000 ALTER TABLE `assortiment_merken` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `cadeaukaarten`
--

DROP TABLE IF EXISTS `cadeaukaarten`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cadeaukaarten` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `kaart_naam` varchar(100) NOT NULL,
  `geschikte_winkels` text NOT NULL,
  `tags` text NOT NULL,
  `afbeelding` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cadeaukaarten_website` (`website_id`),
  CONSTRAINT `fk_cadeaukaarten_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cadeaukaarten`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cadeaukaarten` WRITE;
/*!40000 ALTER TABLE `cadeaukaarten` DISABLE KEYS */;
INSERT INTO `cadeaukaarten` VALUES
('fbea5e72-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Primera Keuze Cadeaukaart','primera, Primera, Debandijk','alles, keuze, warenhuis, cadeau, divers, populair, primera, tip','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1770323017_primerakeuze.webp',1,'2026-02-04 21:24:20'),
('fbeaeef0-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','VVV Cadeaukaart','Blokker, Bruna, Karwei, Kwantum, Leen Bakker, Prenatal, Lucardi, Shoeby, Rituals, Kruidvat','shoppen, klussen, baby, sieraden, warenhuis, vvv, lokaal, online','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257825_vvv_cadeaukaart_og_image_1200x627.jpg',2,'2026-02-04 21:24:20'),
('fbeb1006-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Bol.com Cadeaukaart','Miljoenen artikelen op Bol.com','elektronica, boeken, speelgoed, wonen, alles, online, bol','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771258588_Bol.com_cadeaukaart_OG_image_1200x627.jpg',3,'2026-02-04 21:24:20'),
('fbeb4904-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','HEMA Cadeaubon','Alle HEMA filialen en online','wonen, kleding, make-up, school, huishouden, nederlands, hema','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771256118_672b7ad6cea79.png',4,'2026-02-04 21:24:20'),
('fbeb5ba6-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Zalando Cadeaubon','Zalando webshop','mode, kleding, schoenen, tassen, fashion, online, zalando','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771255864_zalando_og_image_1200x627.jpg',5,'2026-02-04 21:24:20'),
('fbec77de-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','H&M Cadeaukaart','H&M winkels en online','mode, kleding, fashion, trends, goedkoop, hm','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771256144_2030471268.png',7,'2026-02-04 21:24:20'),
('fbef010c-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','ICI PARIS XL','ICI PARIS XL parfumerie','beauty, parfum, make-up, verzorging, luxe, iciparis','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771251183_Iciparisxlgiftcard_OG_image_1200x627.jpg',6,'2026-02-04 21:24:20'),
('fbefb016-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Rituals Cadeaukaart','Rituals Cosmetics winkels en online','beauty, ontspanning, huis, geuren, verzorging, wellness, rituals','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771251163_logo_rituals.png',8,'2026-02-04 21:24:20'),
('fbf05d04-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Boekenbon','Primera, Bruna, AKO, Libris en lokale boekhandels','lezen, boeken, studie, romans, literatuur, hobby, boekenbon','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771248455_boekenbon_afbeelding_1.png',9,'2026-02-04 21:24:20'),
('fbf10f56-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Nationale Bioscoopbon','Pathé, Vue, Kinepolis en alle aangesloten lokale bioscopen','film, bioscoop, uitje, popcorn, avondje weg, bioscoopbon','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771256396_Nationale_Bioscoopbon_OG_image_1200x627.jpg',10,'2026-02-04 21:24:20'),
('fbfb0790-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Podium Cadeaukaart','Theaters, Concertgebouwen, Musicals, Carré, AFAS Live','theater, concert, muziek, cultuur, uitje, musical, podium','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257021_podium_cadeaukaart.jpg',11,'2026-02-04 21:24:20'),
('fbfb6294-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Decathlon Cadeaukaart','Decathlon winkels en online','sport, fitness, kamperen, outdoor, voetbal, fietsen, decathlon','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257227_decathlon_egift_1_4457492_regular.jpg',12,'2026-02-04 21:24:20'),
('fbfb72ac-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Karwei Cadeaukaart','Karwei bouwmarkten','klussen, bouwmarkt, wonen, verf, tuin, gereedschap, karwei','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771259035_square_3.jpeg',13,'2026-02-04 21:24:20'),
('fbfb7f0e-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Intratuin Cadeaukaart','Intratuin tuincentra','tuin, planten, bloemen, wonen, kerst, buiten, intratuin','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1770323044_intratuin.webp',14,'2026-02-04 21:24:20'),
('fbfbb410-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Adidas','Adidas winkels en online shop','sport, schoenen, kleding, fitness, voetbal, sneakers','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257080_E_GIFT_CARD_Zwart_GC2784_HM1.tiff.jpg',19,'2026-02-04 21:24:20'),
('fbfbc1ee-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Babycadeaubon','Prénatal, Babypark, Baby-Dump, Noppies','baby, kraamcadeau, zwanger, speelgoed, kleding','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771256208_babycadeaubon_blauw_3.jpg',15,'2026-02-04 21:24:20'),
('fbfbe4e4-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Beauty Cadeau','ICI PARIS XL, Rituals, Holland & Barrett, Douglas','verzorging, beauty, parfum, ontspanning, wellness','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771249509_cadeaukaart_BeautyCadeau.jpg',16,'2026-02-04 21:24:20'),
('fbfbf038-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Bloemen Cadeau','Fleurop en lokale bloemisten','bloemen, planten, fleurop, cadeau, bedankje','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771258939_4257676a13aa72de793916d7b778bab_1698206756662.png',17,'2026-02-04 21:24:20'),
('fbfbfc7c-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Coolblue','Coolblue winkels en webshop','elektronica, witgoed, laptops, telefonie, gadgets','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257434_download.jpeg',18,'2026-02-04 21:24:20'),
('fbfc05a0-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','DE Cadeaukaart','Blokker, Cookandpan.nl, Koffievoordeel.nl','koffie, huishouden, blokker, wonen, douwe egberts','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771258025_67175a35bb891.png',20,'2026-02-04 21:24:20'),
('fbfc2166-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Dille & Kamille','Dille & Kamille winkels en online','wonen, keuken, tuin, hobbys, koken, duurzaam','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257272_dille_kamille_1500px_0.jpg',21,'2026-02-04 21:24:20'),
('fbfc5758-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Diner Cadeau','5000+ restaurants in Nederland','eten, diner, uitgaan, horeca, culinair','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771256082_download.jpg',22,'2026-02-04 21:24:20'),
('fbfc72a6-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Douglas','Douglas parfumerie','beauty, parfum, make-up, verzorging, luxe','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771251625_douglas_1500px_1.jpg',23,'2026-02-04 21:24:20'),
('fbfcba72-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Efteling','Efteling toegangskaarten en verblijf','pretpark, uitje, familie, kinderen, sprookjes','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771248927_public.png',24,'2026-02-04 21:24:20'),
('fbfcdac0-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Etos','Etos drogisterijen','verzorging, gezondheid, make-up, baby, drogist','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771250995_Etos_cadeaukaart_0.jpg',25,'2026-02-04 21:24:20'),
('fbfd58ce-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','fashioncheque','H&M, Rituals, C&A, Foot Locker, My Jewellery, Bristol','mode, kleding, schoenen, fashion, winkelen','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771256433_fas_25035_roze_uitsnede_landscape_regulier_1.jpg',26,'2026-02-04 21:24:20'),
('fbfd9eec-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Fleurop','Alle Fleurop bloemisten','bloemen, boeket, planten, bedankje, fleurop','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257800_bloemenbon.jpg',27,'2026-02-04 21:24:20'),
('fbfe6ca0-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Fletcher Hotels','100+ Fletcher Hotels in Nederland','overnachten, hotel, weekendje weg, vakantie, fletcher','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771250573_Fletcher_cadeaukaart_OG_image_1200x627.jpg',47,'2026-02-04 21:24:20'),
('fbfe8334-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Gall & Gall','Gall & Gall slijterijen','drank, wijn, whisky, bier, borrel, slijter','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257317_gall_gall_1500px_0.jpg',28,'2026-02-04 21:24:20'),
('fbfe918a-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Huis & Tuin Cadeau','Praxis, Karwei, Intratuin, Trendhopper, Leen Bakker, dille & kamille,','wonen, tuin, klussen, interieur, planten','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1770939646_images.jpeg',29,'2026-02-04 21:24:20'),
('fbfeaa08-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Hunkemöller','Hunkemöller winkels en online','lingerie, nachtmode, mode, vrouwen','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257057_U5JFjuwDgvOhswtSey6191BII0ciGQ0uPP3XNw2i.png',30,'2026-02-04 21:24:20'),
('fbfebade-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','IKEA','IKEA woonwarenhuizen','wonen, meubels, accessoires, inrichting, bedden','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257204_ALIKEANLD_fp01.png',31,'2026-02-04 21:24:20'),
('fbfec3ee-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Jewelcard','Lucardi, Siebel en lokale juweliers','sieraden, horloges, juwelier, goud, zilver','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771258825_jewelcard_og_image_1200x627.jpg',32,'2026-02-04 21:24:20'),
('fbff09f8-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','KidsCadeau','Intertoys, Pathé, Efteling, Beekse Bergen','kinderen, speelgoed, uitje, verjaardag','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771249476_kids_cadeau_gift_card_4463946_regular_1.jpg',33,'2026-02-04 21:24:20'),
('fbff233e-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Klus Cadeau','Gamma, Karwei, Praxis, Hornbach','klussen, gereedschap, bouwmarkt, verbouwen','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771258734_kluscadeau_og_image_1200x627_1.jpg',34,'2026-02-04 21:24:20'),
('fbff3090-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','MediaMarkt','MediaMarkt winkels en online','elektronica, tv, witgoed, gaming, telefonie','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257399_mediamarkt_cadeaukaart_50.png',35,'2026-02-04 21:24:20'),
('fbff8392-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','My Jewellery','My Jewellery boutiques en online','mode, sieraden, kleding, vrouwen, accessoires','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771255892_product_images_mjgiftcard_roze.jpg',36,'2026-02-04 21:24:20'),
('fbff9382-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Nike','Nike winkels en online shop','sport, schoenen, sneakers, kleding, fitness','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257249_public_2.png',37,'2026-02-04 21:24:20'),
('fbfffce6-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','OFM. (Only for Men)','OFM winkels en online','mode, kleding, mannen, pakken, schoenen','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771251207_only_for_men_digitale_cadeaubon_4464684_regular.jpg',38,'2026-02-04 21:24:20'),
('fc000b28-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','One4all Restaurantbon','Restaurant','keuze, warenhuis, alles, cadeau, populair, restaurant, eten','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771178997_5016dbd01c2f3fd2098ede14c27e0_1732709302710.png',39,'2026-02-04 21:24:20'),
('fc0015be-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Pathé (Thuis)','Pathé bioscopen of Pathé Thuis films','film, bioscoop, streaming, thuis, popcorn','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771178979_giftcard.307bfde.png',40,'2026-02-04 21:24:20'),
('fc001e60-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Prénatal','Prénatal babywinkels','baby, zwanger, kleding, kraamcadeau, kinderwagen','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771178954_prenatal_1500px_0.jpg',41,'2026-02-04 21:24:20'),
('fc00291e-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Roblox','Roblox platform (Robux)','gaming, kinderen, online, robux, games','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771156107_383609cd00d90bc77dd09683bdc450a8_roblox_gift_card_442.png',42,'2026-02-04 21:24:20'),
('fc00371a-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Social Deal','Eten, uitjes en wellness op Social Deal','korting, uitje, eten, restaurant, wellness','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771156083_6797d243cbbda9ecafde1c21b17e50_1723165572200.png',43,'2026-02-04 21:24:20'),
('fc004746-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Thuisbezorgd.nl','Online eten bestellen','eten, bezorgen, pizza, snack, online','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771156063_thuisbezorgd_OG_image_1200x627_1.jpg',44,'2026-02-04 21:24:20'),
('fc00574a-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Ticketmaster','Concerten, sport en evenementen','tickets, muziek, concert, uitje, evenement','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771155577_Screenshot_20260215_123905_Samsung_Internet.jpg',45,'2026-02-04 21:24:20'),
('fc006c6c-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','vtwonen','vtwonen shop en aangesloten winkels','wonen, interieur, design, accessoires','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771155565_Screenshot_20260215_123733_Samsung_Internet.jpg',46,'2026-02-04 21:24:20'),
('fc0079d2-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Wehkamp','Wehkamp webshop','mode, wonen, elektronica, online, warenhuis','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1770322971_wehkamp.png',48,'2026-02-04 21:24:20'),
('fc0087a6-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','LEESCADEAUKAART','Primera,Bruna,Ako','tijdschriften,boeken,ebooks','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771248628_lees_cadeaukaart_11.jpg',49,'2026-02-16 13:30:03'),
('fc00d152-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','BLADCADEAU','Online','tijdschriften','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771248908_bladcadeau_e_gift_4444072_regular.jpg',50,'2026-02-16 13:32:01'),
('fc01878c-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','KIDS OR TEENS','Intertoys,Balorig, en diverse kledingwinkels','speelgoed,kleding','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771249440_Kids_Teen_Cadeau_OG_image_1200x627.jpg',51,'2026-02-16 13:43:34'),
('fc01a5c8-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','LEGO','Lego','speelgoed','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771249666_Lego_giftcard.jpg',52,'2026-02-16 13:47:34'),
('fc024aa0-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','WEEKENDJE WEG','Parken,hotels','vakantie','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771250545_weekendje_weg_cadeaukaart_pas_3.jpg',53,'2026-02-16 14:02:10'),
('fc026ddc-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','FESTIVALCADEAUKAART','Festivals','uitgaan, ontspanning','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771250694_Festival_cadeaukaart_1.jpg',54,'2026-02-16 14:04:41'),
('fc02f41e-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','BEAUTY EN FASHION ','oa douglas, hunkemoller','beauty en fashion','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771250869_Beauty_Fashion_OG_image_1200x627_0.jpg',55,'2026-02-16 14:07:35'),
('fc031cfa-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','BEAUTY EN MORE','oa douglas, hunkemoller','beauty en fashion','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771250971_Beauty___More_Cadeau_2.jpg',56,'2026-02-16 14:09:08'),
('fc032f88-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','HOTEL GIFTCARD','Hotels en Parken','Uitje','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771251116_hotelgiftcard_2.jpg',57,'2026-02-16 14:11:30'),
('fc033db6-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','THERMEN','Thermen','ontspanning','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771251524_Thermencadeau_1.jpg',58,'2026-02-16 14:18:30'),
('fc034d42-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','REIS CADEAU','Hotels, Parken','ontspanning','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771251599_cadeaukaart_Reis_Cadeau.jpg',59,'2026-02-16 14:19:47'),
('fc035c88-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','PRIMARK','Primark','fashion','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771251672_primark_1500px_0.jpg',61,'2026-02-16 14:21:01'),
('fc038712-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','PINK GELLAC','Pink Gellac','beauty ','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771251734_6a3f8d4d08c1a6d49f7756cfb527f83c.png',60,'2026-02-16 14:22:01'),
('fc03d9a6-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','SAUNA EN WELLNESS','Sauna en Thermen','dagje uit','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771255715_download.png',62,'2026-02-16 15:28:11'),
('fc03f3d2-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','SAUNA CADEAUBON','Sauna','ontspanning','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771255835_Design_2019.png',63,'2026-02-16 15:30:18'),
('fc042082-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','JEWELCARD','Juwelier','Sieraden','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771256048_Jewelcard.jpg',64,'2026-02-16 15:32:10'),
('fc0438c4-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','SHOEBY CADEAUKAART','Shoeby','fashion','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771256028_shoeby_og_image_1200x627.jpg',65,'2026-02-16 15:33:25'),
('fc04724e-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','PRENATAL','Prenatal','baby, kraamcadeau, zwanger, speelgoed, kleding','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771256267_prenatal_1500px_0_1_.jpg',66,'2026-02-16 15:37:31'),
('fc048f0e-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','KLEERTJES.COM','Kleertjes.com','fashion','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771256365_Kleertjes_cadeaukaart_0.jpg',67,'2026-02-16 15:39:09'),
('fc04ad86-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','HOLLAND CASINO','Holland Casino','avondje uit','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771256971_hc_try_out_25_v3.png',68,'2026-02-16 15:49:15'),
('fc04f96c-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','PETSPLACE','Petsplace','dieren,voer','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257497_square_1.jpeg',69,'2026-02-16 15:58:02'),
('fc050d58-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','HOLLAND&BARRETT','Holland&barrett winkels','gezondheid,beauty','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257643_holland_and_barrett_og_image_1200x627.jpg',70,'2026-02-16 16:00:28'),
('fc0593a4-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','NATIONALE TUINBON','Div Tuincentra','huis,tuin','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257772_Nationaletuinbon_OG_image_1200x627.jpg',71,'2026-02-16 16:02:34'),
('fc05a9e8-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','WINKELCHEQUE','Div kledingwinkels ','fashion en beauty','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771257964_download_1.jpeg',72,'2026-02-16 16:05:33'),
('fc05bc1c-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','ONE4ALL MULTI CADEAUKAART','Van Kledingzaak tot Bouwmarkt','fashion,beauty,huis,tuin','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771258171_Cadeau_Multichoice_Card_1.png',73,'2026-02-16 16:09:15'),
('fc05d724-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','ONE4ALLWOONCADEAU','Van Wehkamp tot Karwei','fashion, wonen,beauty','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1771258281_4a3002f40e7bb79804f7498e192be1e.png',74,'2026-02-16 16:11:07'),
('fc0628f0-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','De Donatiebon','greenpeace, warchild, goede doelen, kwf, aidsfonds, cliniclowns','goededoelen, doneren','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/kaarten/1776436894_dedonatiebon.webp',75,'2026-04-17 14:41:34');
/*!40000 ALTER TABLE `cadeaukaarten` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` char(36) NOT NULL,
  `website_id` char(36) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fk_categories_website` (`website_id`),
  CONSTRAINT `fk_categories_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
('1bf2d2ce-7079-11f1-bc9f-06bd6669bc40',NULL,'Sigaretten',1);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `contact_messages`
--

DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact_messages` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `status` enum('nieuw','gelezen','beantwoord') DEFAULT 'nieuw',
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`),
  CONSTRAINT `fk_contact_messages_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact_messages`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `contact_messages` WRITE;
/*!40000 ALTER TABLE `contact_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact_messages` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `custom_field_definitions`
--

DROP TABLE IF EXISTS `custom_field_definitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_field_definitions` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `field_key` varchar(100) NOT NULL,
  `label` varchar(150) NOT NULL,
  `field_type` varchar(20) NOT NULL DEFAULT 'text',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cfd_website_entity` (`website_id`,`entity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_field_definitions`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `custom_field_definitions` WRITE;
/*!40000 ALTER TABLE `custom_field_definitions` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_field_definitions` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `custom_field_values`
--

DROP TABLE IF EXISTS `custom_field_values`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_field_values` (
  `id` char(36) NOT NULL,
  `field_id` char(36) NOT NULL,
  `entity_id` char(36) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cfv_field` (`field_id`),
  KEY `idx_cfv_entity` (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_field_values`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `custom_field_values` WRITE;
/*!40000 ALTER TABLE `custom_field_values` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_field_values` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`),
  CONSTRAINT `fk_faqs_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
INSERT INTO `faqs` VALUES
('267cec5a-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','Maken jullie pasfoto\'s?','Ja! Wij maken officiële pasfoto\'s voor paspoort, ID-kaart en rijbewijs (ook RDW digitaal verlengen). Geen afspraak nodig.','2026-08-30 12:34:31'),
('267cec96-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','PostNL Pakket ophalen?','Je kunt bij ons terecht voor het versturen en ophalen van PostNL pakketten. Vergeet je legitimatiebewijs niet mee te nemen!','2026-08-30 12:34:31'),
('267ceca0-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','Zijn jullie op zondag open?','Nee, op zondag is onze winkel gesloten. Bekijk onze actuele openingstijden hierboven voor de rest van de week.','2026-08-30 12:34:31');
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `footer`
--

DROP TABLE IF EXISTS `footer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `footer` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `sleutel` varchar(50) NOT NULL,
  `waarde` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_footer` (`website_id`,`sleutel`),
  CONSTRAINT `fk_footer_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `footer`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `footer` WRITE;
/*!40000 ALTER TABLE `footer` DISABLE KEYS */;
INSERT INTO `footer` VALUES
('d798b43e-a3d8-11f1-b679-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','contact_telefoon','0547 26 11 44'),
('d798f868-a3d8-11f1-b679-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','contact_email','debandijk@primeranet.nl'),
('d799158c-a3d8-11f1-b679-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','contact_regio',''),
('f2f825b6-a3d8-11f1-b679-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','bedrijf_kvk','06072701'),
('f2f859d2-a3d8-11f1-b679-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','bedrijf_btw','');
/*!40000 ALTER TABLE `footer` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `footer_links`
--

DROP TABLE IF EXISTS `footer_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `footer_links` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `kolom` varchar(20) NOT NULL,
  `label` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `icon` varchar(30) NOT NULL DEFAULT 'link',
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_footer_links_website` (`website_id`,`kolom`),
  CONSTRAINT `fk_footer_links_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `footer_links`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `footer_links` WRITE;
/*!40000 ALTER TABLE `footer_links` DISABLE KEYS */;
INSERT INTO `footer_links` VALUES
('4b45a7d6-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','services','Pasfoto\s','services/pasfotos.php','camera',1,'2026-08-31 19:58:11'),
('4b45a7e0-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','services','PostNL','services/postnl.php','package',2,'2026-08-31 19:58:11'),
('4b45a7ea-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','services','RDW','services/rdw.php','check-circle',3,'2026-08-31 19:58:11'),
('4b45a7eb-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','services','Geldmaat','services/geldmaat.php','cash',4,'2026-08-31 19:58:11'),
('4b45a7f4-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','navigatie','Home','index.php','home',1,'2026-08-31 19:58:11'),
('4b45a7f5-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','navigatie','Openingstijden','index.php#openingstijden','clock',2,'2026-08-31 19:58:11'),
('4b45a7f6-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','navigatie','Nieuws','#nieuws','newspaper',3,'2026-08-31 19:58:11'),
('4b45a7fe-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','navigatie','Contact','contact.php','chat',4,'2026-08-31 19:58:11'),
('4b45a7ff-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','navigatie','Over Ons','about.php','people',5,'2026-08-31 19:58:11');
/*!40000 ALTER TABLE `footer_links` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `geschiedenis`
--

DROP TABLE IF EXISTS `geschiedenis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `geschiedenis` (
  `website_id` char(36) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Onze Geschiedenis',
  `content` text NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`website_id`),
  CONSTRAINT `fk_geschiedenis_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `geschiedenis`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `geschiedenis` WRITE;
/*!40000 ALTER TABLE `geschiedenis` DISABLE KEYS */;
INSERT INTO `geschiedenis` VALUES
('775d64f8-99a0-11f1-bc37-06bd6669bc40','Onze Geschiedenis','<h3>1 april 1994 — De Start op Nummer 94</h3><p>Op deze dag opende René, samen met Gerri, de deur van hun winkel \'De Bandijk\' aan de Grotestraat 94. De focus lag op tabak, snoep en kado\'s. René was 27 jaar en startte de winkel met een bijzondere motivatie: er was bij hem Retinitis pigmentosa ontdekt. Om niet afhankelijk te worden van een uitkering, besloot hij met volle gedrevenheid zelf aan de bak te gaan. Binnen een jaar bleek echter dat het pand moest wijken voor de bouw van het nieuwe gemeentehuis en politiebureau.</p><p><img src=\"historie/94.jpg\" alt=\"1994\"></p><h3>1995 — Grotestraat 67</h3><p>Er werd snel een nieuwe plek gevonden op nummer 67. Ondanks de beperkte oppervlakte van 35m², groeide de klantenkring snel. De Bandijk onderscheidde zich door een enorme diversiteit in tabaksproducten. Na 7 jaar meldde het postkantoor zich: zij zochten een ondernemer om hun diensten over te nemen. Dit vroeg om een groter pand en extra hulp. René Kamphuis (schoonzoon van Gerri) werd gevraagd om bij in de zaak te komen om deze nieuwe uitdaging aan te gaan.</p><p><img src=\"historie/67.jpg\" alt=\"1995\"></p><h3>28 nov 2002 — De stap naar Primera</h3><p>Aan de Grotestraat 135 openden René V. en René K. de deuren van hun nieuwe gemakswinkel met postkantoor. Er werd gekozen voor een samenwerking met de Primera-keten. Gerri bleef werkzaam in de zaak, nu ondersteund door dochter Cindy en nieuwe personeelsleden Ine en Geja. Niet veel later kwam ook de eerste zaterdagkracht Daniëlle het team versterken. Primera De Bandijk was hiermee officieel een feit op de huidige locatie.</p><p><img src=\"historie/vooraanzicht_2002.jpg\" alt=\"2002\"></p><h3>2012 — Post &amp; Bankzaken</h3><p>Toen Postkantoren BV werd opgeheven, veranderde het postgedeelte in TPGpost en werden GIRO-klanten ondergebracht bij de ING Bank. Er werd een speciaal ING-servicepunt ingericht en de postzaken verhuisden naar de centrale winkelbalie. Dit zorgde voor een nieuwe dynamiek in de winkel, waarbij klanten voor verschillende diensten gezellig samen in de rij stonden te wachten.</p><h3>2014 — Grote Verbouwing</h3><p>Twee jaar na de kleine ingrepen was het tijd voor een grote verandering. De winkel ging een week dicht voor een complete metamorfose: een grotere balie, een extra kassa en een gloednieuwe vloer. Op 31 augustus werd de heropening groots gevierd met alle genodigden en klanten, waarmee de winkel weer helemaal klaar was voor de toekomst.</p><h3>2018 — Overdracht</h3><p>Door de toenemende drukte en zijn verslechterende zicht, besloot René Versteegen na jaren van trouwe dienst zijn plek in de VOF over te dragen. Sinds augustus 2018 zijn René en Cindy de eigenaren van Primera De Bandijk. Inmiddels helpt ook hun oudste zoon Bas regelmatig mee, waardoor de familiekracht in de winkel gewaarborgd blijft.</p><h3>2023 - Heden — Nieuwe Mogelijkheden</h3><p>Na het beëindigen van de samenwerking met de ING kreeg de winkel opnieuw een functionele update. De hoek van het oude servicepunt werd volledig verbouwd en getransformeerd tot een moderne ruimte voor het maken van pasfoto\'s. Hiermee blijft de winkel zich continu aanpassen aan de behoeften van de inwoners van Goor.</p><p><img src=\"historie/nieuwe_indeling.jpg\" alt=\"Heden\"></p>','2026-08-30 11:54:30');
/*!40000 ALTER TABLE `geschiedenis` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `legals`
--

DROP TABLE IF EXISTS `legals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `legals` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`),
  CONSTRAINT `fk_legals_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `legals`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `legals` WRITE;
/*!40000 ALTER TABLE `legals` DISABLE KEYS */;
INSERT INTO `legals` VALUES
('5fda356a-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','voorwaarden','','','2026-08-17 19:53:53'),
('5fda3e2a-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','privacy','','','2026-08-17 19:53:53'),
('5fda44ce-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','cookies','','','2026-08-17 19:53:53');
/*!40000 ALTER TABLE `legals` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `login_attempts`
--

DROP TABLE IF EXISTS `login_attempts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 0,
  `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_login_attempts_username` (`username`),
  KEY `idx_login_attempts_ip` (`ip_address`),
  KEY `idx_login_attempts_attempted_at` (`attempted_at`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_attempts`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `login_attempts` WRITE;
/*!40000 ALTER TABLE `login_attempts` DISABLE KEYS */;
INSERT INTO `login_attempts` VALUES
(1,'sebastiaankamphuis0508@gmail.com','::1',0,'2026-08-17 19:53:35'),
(2,'Admin','::1',1,'2026-08-17 19:53:40'),
(6,'webius','::1',1,'2026-08-17 20:00:21'),
(7,'admin','::1',1,'2026-08-18 19:38:21'),
(8,'admin','::1',1,'2026-08-19 20:09:09'),
(10,'admin','::1',1,'2026-08-24 15:12:46'),
(11,'info@webius.nl','::1',0,'2026-08-24 13:16:05'),
(12,'info@webius.nl','::1',0,'2026-08-24 13:16:12'),
(13,'info@webius.nl','::1',0,'2026-08-24 13:16:39'),
(14,'info@webius.nl','::1',0,'2026-08-24 13:16:46'),
(15,'info@webius.nl','::1',0,'2026-08-24 13:17:55'),
(16,'info@webius.nl','::1',0,'2026-08-24 15:19:38'),
(17,'Webius','::1',1,'2026-08-24 15:19:55'),
(18,'sebastiaan','::1',1,'2026-08-24 15:34:21'),
(20,'admin','::1',1,'2026-08-24 17:39:41'),
(21,'admin','::1',1,'2026-08-24 18:13:02'),
(22,'admin','::1',1,'2026-08-24 18:42:06'),
(23,'admin','::1',1,'2026-08-24 18:47:33'),
(26,'sebastiaan','::1',1,'2026-08-24 21:13:59'),
(30,'admin','::1',1,'2026-08-25 17:17:36'),
(31,'admin','::1',1,'2026-08-29 17:26:07'),
(32,'admin','::1',1,'2026-08-30 11:52:12'),
(33,'remembertest','127.0.0.1',1,'2026-08-30 12:18:13'),
(34,'admin','::1',1,'2026-08-30 12:23:32'),
(35,'contenttest','127.0.0.1',1,'2026-08-30 12:39:47'),
(36,'supertest','127.0.0.1',1,'2026-08-30 12:49:23'),
(37,'edittarget','127.0.0.1',1,'2026-08-30 12:49:42'),
(39,'admin','::1',1,'2026-08-31 20:00:38'),
(40,'admin','::1',1,'2026-09-01 18:01:53'),
(41,'admin','::1',1,'2026-09-01 18:50:38'),
(42,'sebastiaan','::1',1,'2026-09-01 19:17:18');
/*!40000 ALTER TABLE `login_attempts` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `nieuws`
--

DROP TABLE IF EXISTS `nieuws`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nieuws` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_paths` text DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_nieuws_website_status_created` (`website_id`,`status`,`created_at`),
  CONSTRAINT `fk_nieuws_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nieuws`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `nieuws` WRITE;
/*!40000 ALTER TABLE `nieuws` DISABLE KEYS */;
INSERT INTO `nieuws` VALUES
('fc065d8e-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','Iets Nieuws! ','Vanaf half Augustus krijgen wij een nieuw product in de winkel..\r\nHebben jullie al een idee??\r\nHint:\r\n- het komt 3 meter breed te staan\r\nTot het zover is moeten we ruimte creëren en zullen we artikelen gaan uitverkopen. Dus het loont om af en toe Even naar Primera te gaan.','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/legacy/nieuws/1783538790_0_primera_nieuws.jpg','published','2026-07-08 19:26:30');
/*!40000 ALTER TABLE `nieuws` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `opening_hours`
--

DROP TABLE IF EXISTS `opening_hours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `opening_hours` (
  `website_id` char(36) NOT NULL,
  `day_of_week` tinyint(4) NOT NULL,
  `opens` time DEFAULT NULL,
  `closes` time DEFAULT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT 0,
  `note` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`website_id`,`day_of_week`),
  CONSTRAINT `fk_opening_hours_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `opening_hours`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `opening_hours` WRITE;
/*!40000 ALTER TABLE `opening_hours` DISABLE KEYS */;
INSERT INTO `opening_hours` VALUES
('775d64f8-99a0-11f1-bc37-06bd6669bc40',0,NULL,NULL,1,NULL),
('775d64f8-99a0-11f1-bc37-06bd6669bc40',1,'08:00:00','18:00:00',0,NULL),
('775d64f8-99a0-11f1-bc37-06bd6669bc40',2,'08:00:00','18:00:00',0,NULL),
('775d64f8-99a0-11f1-bc37-06bd6669bc40',3,'08:00:00','18:00:00',0,NULL),
('775d64f8-99a0-11f1-bc37-06bd6669bc40',4,'08:00:00','18:00:00',0,NULL),
('775d64f8-99a0-11f1-bc37-06bd6669bc40',5,'08:00:00','20:00:00',0,'Koopavond'),
('775d64f8-99a0-11f1-bc37-06bd6669bc40',6,'08:00:00','17:00:00',0,NULL);
/*!40000 ALTER TABLE `opening_hours` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `portfolio`
--

DROP TABLE IF EXISTS `portfolio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `portfolio` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`),
  CONSTRAINT `fk_portfolio_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `portfolio`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `portfolio` WRITE;
/*!40000 ALTER TABLE `portfolio` DISABLE KEYS */;
/*!40000 ALTER TABLE `portfolio` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `prijsvraag_instellingen`
--

DROP TABLE IF EXISTS `prijsvraag_instellingen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `prijsvraag_instellingen` (
  `website_id` char(36) NOT NULL,
  `huidige_vraag` text NOT NULL,
  `huidige_prijs` varchar(255) NOT NULL,
  `afbeelding_url` varchar(255) DEFAULT NULL,
  `is_actief` tinyint(1) NOT NULL DEFAULT 0,
  `toon_antwoorden` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`website_id`),
  CONSTRAINT `fk_prijsvraag_instellingen_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prijsvraag_instellingen`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `prijsvraag_instellingen` WRITE;
/*!40000 ALTER TABLE `prijsvraag_instellingen` DISABLE KEYS */;
INSERT INTO `prijsvraag_instellingen` VALUES
('775d64f8-99a0-11f1-bc37-06bd6669bc40','Raad de laatste 2 cijfers van het lot hierboven. En maak kans op een heel koningsdaglot\r\n\r\nMeedoen kan t/m 24 april 2026 12:00\r\n\r\nSpeelbewust 18+','Heel Koningsdaglot Staatsloterij t.w.v. 20,-',NULL,1,0,'2026-09-01 19:25:09');
/*!40000 ALTER TABLE `prijsvraag_instellingen` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `prijsvraag_inzendingen`
--

DROP TABLE IF EXISTS `prijsvraag_inzendingen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `prijsvraag_inzendingen` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `voornaam` varchar(50) NOT NULL,
  `achternaam` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefoon` varchar(20) DEFAULT NULL,
  `antwoord` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_inzending_website_email` (`website_id`,`email`),
  KEY `idx_prijsvraag_inzendingen_website` (`website_id`),
  CONSTRAINT `fk_inzendingen_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prijsvraag_inzendingen`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `prijsvraag_inzendingen` WRITE;
/*!40000 ALTER TABLE `prijsvraag_inzendingen` DISABLE KEYS */;
INSERT INTO `prijsvraag_inzendingen` VALUES
('599c1fca-a3cf-11f1-b679-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','jantje','van der veen','jantjevdveen@ikbenhallo.nl','0612345678','00','2026-08-29 17:30:38'),
('eeabd890-a63a-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','pietje','de vries','pdevries@hoi.nl','0162983982193890','00','2026-09-01 19:25:46');
/*!40000 ALTER TABLE `prijsvraag_inzendingen` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `category_id` char(36) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `show_on_homepage` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `fk_products_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `remember_tokens`
--

DROP TABLE IF EXISTS `remember_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` char(36) NOT NULL,
  `selector` varchar(24) NOT NULL,
  `validator_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_remember_selector` (`selector`),
  KEY `idx_remember_user` (`user_id`),
  CONSTRAINT `fk_remember_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remember_tokens`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `remember_tokens` WRITE;
/*!40000 ALTER TABLE `remember_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `remember_tokens` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `review_text` text NOT NULL,
  `stars` int(11) DEFAULT 5,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`),
  CONSTRAINT `fk_reviews_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `services` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`),
  CONSTRAINT `fk_services_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `services`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `services` WRITE;
/*!40000 ALTER TABLE `services` DISABLE KEYS */;
/*!40000 ALTER TABLE `services` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `site_content`
--

DROP TABLE IF EXISTS `site_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `site_content` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `page` varchar(50) NOT NULL,
  `section_key` varchar(50) NOT NULL,
  `content_text` text NOT NULL,
  `type` enum('text','textarea','image','number','boolean') NOT NULL DEFAULT 'text',
  `label` varchar(150) DEFAULT NULL,
  `group_name` varchar(50) DEFAULT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_site_content` (`website_id`,`page`,`section_key`),
  CONSTRAINT `fk_site_content_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `site_content`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `site_content` WRITE;
/*!40000 ALTER TABLE `site_content` DISABLE KEYS */;
INSERT INTO `site_content` VALUES
('01e1f81f-0de9-4ce4-b334-953fc406f12b','775d64f8-99a0-11f1-bc37-06bd6669bc40','contact','contact_info_title','Contactgegevens','text','Contact info titel','info',0,'2026-08-29 18:15:44'),
('155542ec-4b91-4508-be1a-cf542905910b','775d64f8-99a0-11f1-bc37-06bd6669bc40','algemeen','social_tiktok','https://tiktok.com/@jouwpagina','text','TikTok URL','social',0,'2026-08-29 18:15:44'),
('20d22b6e-8c17-4aa1-ba21-f8a82947b88d','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','hero_title','Primera de Bandijk','text','Hero titel','hero',1,'2026-09-01 18:51:02'),
('267c545c-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','assortiment','hero_title','Ons assortiment','text','Titel bovenaan','hero',1,'2026-08-30 12:34:31'),
('267c5b78-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','assortiment','hero_title','Ons assortiment','text','Titel bovenaan','hero',1,'2026-09-01 18:51:02'),
('267c5c0e-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','assortiment','hero_text','Van de dagelijkse krant tot dat ene speciale cadeau — de vertrouwde kwaliteit van De Bandijk.','textarea','Introductietekst','hero',1,'2026-08-30 12:34:31'),
('267c5c68-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','assortiment','hero_text','Van de dagelijkse krant tot dat ene speciale cadeau — de vertrouwde kwaliteit van De Bandijk.','textarea','Introductietekst','hero',1,'2026-09-01 18:51:02'),
('267c5cb8-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','assortiment','tabak_title','Tabak & rookwaren','text','Titel wettelijke tekst','wettelijk',1,'2026-08-30 12:34:31'),
('267c5d08-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','assortiment','tabak_title','Tabak & rookwaren','text','Titel wettelijke tekst','wettelijk',1,'2026-09-01 18:51:02'),
('267c5d58-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','assortiment','tabak_text','Wij verkopen geen tabaksproducten aan personen onder de 18 jaar. Ben je jonger dan 25 jaar? Laat dan uit eigen beweging je legitimatiebewijs zien. Zonder geldig ID-bewijs mogen wij de verkoop niet voltooien.','textarea','Wettelijke tabak/rookwaren-tekst','wettelijk',1,'2026-08-30 12:34:31'),
('267c5ede-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','assortiment','tabak_text','Wij verkopen geen tabaksproducten aan personen onder de 18 jaar. Ben je jonger dan 25 jaar? Laat dan uit eigen beweging je legitimatiebewijs zien. Zonder geldig ID-bewijs mogen wij de verkoop niet voltooien.','textarea','Wettelijke tabak/rookwaren-tekst','wettelijk',1,'2026-09-01 18:51:02'),
('267c5f2e-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','cadeaukaarten','hero_title','Cadeaukaart zoeker','text','Titel bovenaan','hero',1,'2026-08-30 12:34:31'),
('267c6014-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','cadeaukaarten','hero_title','Cadeaukaart zoeker','text','Titel bovenaan','hero',1,'2026-09-01 18:51:02'),
('267c606e-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','cadeaukaarten','hero_text','Voor elke winkel of hobby een kaart. Zoek direct in ons assortiment van +100 kaarten.','textarea','Introductietekst','hero',1,'2026-08-30 12:34:31'),
('267c60b4-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','cadeaukaarten','hero_text','Voor elke winkel of hobby een kaart. Zoek direct in ons assortiment van +70 kaarten.','textarea','Introductietekst','hero',1,'2026-09-01 18:51:02'),
('267c63e8-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','cadeaukaarten','waarschuwing_text','Let op: Deze tool is momenteel in ontwikkeling. De getoonde gegevens en voorraden kunnen afwijken van de werkelijkheid. Kom naar de winkel voor het actuele aanbod.','textarea','\"In ontwikkeling\"-melding (zet uit zodra klaar)','waarschuwing',1,'2026-08-30 12:34:31'),
('267c6654-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','cadeaukaarten','waarschuwing_text','Let op: Deze tool is momenteel in ontwikkeling. De getoonde gegevens en voorraden kunnen afwijken van de werkelijkheid. Kom naar de winkel voor het actuele aanbod.','textarea','\"In ontwikkeling\"-melding (zet uit zodra klaar)','waarschuwing',0,'2026-09-01 18:04:37'),
('267c6708-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','nieuws','hero_title','Nieuws & acties','text','Titel bovenaan','hero',1,'2026-08-30 12:34:31'),
('267c6762-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','nieuws','hero_title','Nieuws & acties','text','Titel bovenaan','hero',1,'2026-09-01 18:51:02'),
('267c67b2-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','nieuws','hero_text','Ontdek de laatste nieuwtjes, winacties en lokale updates van Primera de Bandijk in Goor.','textarea','Introductietekst','hero',1,'2026-08-30 12:34:31'),
('267cb154-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','nieuws','hero_text','Ontdek de laatste nieuwtjes, winacties en lokale updates van Primera de Bandijk in Goor.','textarea','Introductietekst','hero',1,'2026-09-01 18:51:02'),
('267cb1e0-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','prijsvraag','hero_title','Doe mee & win!','text','Titel bovenaan','hero',1,'2026-08-30 12:34:31'),
('267cb230-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','prijsvraag','hero_title','Doe mee & win!','text','Titel bovenaan','hero',1,'2026-09-01 18:51:02'),
('267cb28a-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','prijsvraag','hero_text','Beantwoord de vraag hieronder en wie weet ben jij onze volgende gelukkige winnaar.','textarea','Introductietekst','hero',1,'2026-08-30 12:34:31'),
('267cb2da-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','prijsvraag','hero_text','Beantwoord de vraag hieronder en wie weet ben jij onze volgende gelukkige winnaar.','textarea','Introductietekst','hero',1,'2026-09-01 18:51:02'),
('267cb456-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','prijsvraag','geen_vraag_text','Op dit moment bereiden we een nieuwe winactie voor. Houd onze website of Facebook in de gaten!','textarea','Tekst als er geen actieve prijsvraag is','overig',1,'2026-08-30 12:34:31'),
('267cb4ba-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','prijsvraag','geen_vraag_text','Op dit moment bereiden we een nieuwe winactie voor. Houd onze website of Facebook in de gaten!','textarea','Tekst als er geen actieve prijsvraag is','overig',1,'2026-09-01 18:51:02'),
('267cb50a-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','prijsvraag','voorwaarden_text','Ik ga akkoord met de actievoorwaarden en geef toestemming om op Facebook en/of onze website te verschijnen met een foto (bij winst).','textarea','Tekst bij het akkoord-vakje','overig',1,'2026-08-30 12:34:31'),
('267cb564-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','prijsvraag','voorwaarden_text','Ik ga akkoord met de actievoorwaarden en geef toestemming om op Facebook en/of onze website te verschijnen met een foto (bij winst).','textarea','Tekst bij het akkoord-vakje','overig',1,'2026-09-01 18:51:02'),
('267cb5b4-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','geschiedenis','cta_title','Bezoek onze winkel in Goor','text','Titel van het CTA-blok','cta',1,'2026-08-30 12:34:31'),
('267cb604-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','geschiedenis','cta_title','Bezoek onze winkel in Goor','text','Titel van het CTA-blok','cta',1,'2026-09-01 18:51:02'),
('267cb64a-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','geschiedenis','cta_text','Al meer dan 30 jaar een vertrouwd gezicht aan de Grotestraat.','textarea','Tekst van het CTA-blok','cta',1,'2026-08-30 12:34:31'),
('267cb690-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','geschiedenis','cta_text','Al meer dan 30 jaar een vertrouwd gezicht aan de Grotestraat.','textarea','Tekst van het CTA-blok','cta',1,'2026-09-01 18:51:02'),
('267cb6d6-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','diensten_title','Alles onder één dak','text','Titel diensten-sectie','diensten',1,'2026-08-30 12:34:31'),
('267cb726-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','diensten_title','Alles onder één dak','text','Titel diensten-sectie','diensten',1,'2026-09-01 18:51:02'),
('267cb76c-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','diensten_text','Van officiële overheidsdiensten tot uw dagelijkse boodschap.','textarea','Introtekst diensten-sectie','diensten',1,'2026-08-30 12:34:31'),
('267cb7f8-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','diensten_text','Van officiële overheidsdiensten tot uw dagelijkse boodschap.','textarea','Introtekst diensten-sectie','diensten',1,'2026-09-01 18:51:02'),
('267cb848-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','tegel_postnl_title','PostNL Punt','text','Tegel 1 - Titel','diensten',1,'2026-08-30 12:38:26'),
('267cb898-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','tegel_postnl_title','PostNL Punt','text','Tegel 1 - Titel','diensten',1,'2026-09-01 18:51:02'),
('267cb8de-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','tegel_postnl_desc','Snel pakketten versturen of ophalen.','textarea','Tegel 1 - Beschrijving','diensten',1,'2026-08-30 12:38:26'),
('267cb92e-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','tegel_postnl_desc','Snel pakketten versturen of ophalen.','textarea','Tegel 1 - Beschrijving','diensten',1,'2026-09-01 18:51:02'),
('267cb974-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','tegel_geldmaat_title','Geldmaat','text','Tegel 2 - Titel','diensten',1,'2026-08-30 12:38:26'),
('267cb9c4-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','tegel_geldmaat_title','Geldmaat','text','Tegel 2 - Titel','diensten',1,'2026-09-01 18:51:02'),
('267cba0a-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','tegel_geldmaat_desc','Veilig contant geld opnemen.','textarea','Tegel 2 - Beschrijving','diensten',1,'2026-08-30 12:38:26'),
('267cba50-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','tegel_geldmaat_desc','Veilig contant geld opnemen.','textarea','Tegel 2 - Beschrijving','diensten',1,'2026-09-01 18:51:02'),
('267cbaa0-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','tegel_rdw_title','RDW Diensten','text','Tegel 3 - Titel','diensten',1,'2026-08-30 12:38:26'),
('267cbae6-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','tegel_rdw_title','RDW Diensten','text','Tegel 3 - Titel','diensten',1,'2026-09-01 18:51:02'),
('267cbb36-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','tegel_rdw_desc','Overschrijven en rijbewijs verlengen.','textarea','Tegel 3 - Beschrijving','diensten',1,'2026-08-30 12:38:26'),
('267cbb7c-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','tegel_rdw_desc','Overschrijven en rijbewijs verlengen.','textarea','Tegel 3 - Beschrijving','diensten',1,'2026-09-01 18:51:02'),
('267cbbcc-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','tegel_cadeaukaarten_title','Cadeaukaarten','text','Tegel 4 - Titel (alleen zichtbaar als module aan staat)','diensten',1,'2026-08-30 12:38:26'),
('267cbc1c-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','tegel_cadeaukaarten_title','Cadeaukaarten','text','Tegel 4 - Titel (alleen zichtbaar als module aan staat)','diensten',1,'2026-09-01 18:51:02'),
('267cbc62-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','tegel_cadeaukaarten_desc','100+ merken, direct leverbaar.','textarea','Tegel 4 - Beschrijving','diensten',1,'2026-08-30 12:38:26'),
('267cbcb2-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','tegel_cadeaukaarten_desc','100+ merken, direct leverbaar.','textarea','Tegel 4 - Beschrijving','diensten',1,'2026-09-01 18:51:02'),
('267cbd0c-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','tegel_pasfotos_title','Pasfoto\'s','text','Tegel 5 - Titel','diensten',1,'2026-08-30 12:38:26'),
('267cbd52-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','tegel_pasfotos_title','Pasfoto\'s','text','Tegel 5 - Titel','diensten',1,'2026-09-01 18:51:02'),
('267cbd98-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','tegel_pasfotos_desc','Officieel erkend, ook RDW. Direct klaar.','textarea','Tegel 5 - Beschrijving','diensten',1,'2026-08-30 12:38:26'),
('267cbdde-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','tegel_pasfotos_desc','Officieel erkend, ook RDW. Direct klaar.','textarea','Tegel 5 - Beschrijving','diensten',1,'2026-09-01 18:51:02'),
('267cbe2e-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','tegel_assortiment_title','Assortiment','text','Tegel 6 - Titel (alleen zichtbaar als module aan staat)','diensten',1,'2026-08-30 12:38:26'),
('267cc388-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','tegel_assortiment_title','Assortiment','text','Tegel 6 - Titel (alleen zichtbaar als module aan staat)','diensten',1,'2026-09-01 18:51:02'),
('267cc3e2-a46f-11f1-8a1e-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','tegel_assortiment_desc','Tabak, snoep, boeken en meer.','textarea','Tegel 6 - Beschrijving','diensten',1,'2026-08-30 12:38:26'),
('267cc432-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','tegel_assortiment_desc','Tabak, snoep, boeken en meer.','textarea','Tegel 6 - Beschrijving','diensten',1,'2026-09-01 18:51:02'),
('2d417237-6183-4a17-bcf1-a82006582c43','775d64f8-99a0-11f1-bc37-06bd6669bc40','contact','contact_hero_text','Heeft u een vraag over onze services of wilt u de voorraad van een artikel checken?','textarea','Contact hero tekst','hero',0,'2026-08-29 18:15:44'),
('2e8c678b-e1bc-4b3d-86a3-efe0942806d7','775d64f8-99a0-11f1-bc37-06bd6669bc40','contact','contact_plaats','Goor','text','Plaats','info',0,'2026-08-29 18:15:44'),
('34ba4490-a634-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','pasfotos','meta_title','Pasfoto laten maken Goor | RDW Erkend | Primera de Bandijk','text','Titel in de browsertab','seo',1,'2026-09-01 18:51:02'),
('34ba4e90-a634-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','pasfotos','hero_title','Pasfoto\'s klaar terwijl u wacht','text','Grote Hoofdtitel','hero',1,'2026-09-01 18:51:02'),
('34ba4f80-a634-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','pasfotos','hero_text','Nieuw paspoort, ID-kaart of rijbewijs nodig? Wij maken officiële pasfoto\'s die voldoen aan alle wettelijke eisen. Direct klaar, zonder afspraak!','textarea','Korte Introductie','hero',1,'2026-09-01 18:51:02'),
('34ba5048-a634-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','postnl','meta_title','PostNL Pakketpunt Goor | Pakket ophalen & versturen | Primera de Bandijk','text','Titel in de browsertab','seo',1,'2026-09-01 18:51:02'),
('34ba50d4-a634-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','postnl','hero_title','PostNL Servicepunt','text','Grote Hoofdtitel','hero',1,'2026-09-01 18:51:02'),
('34ba5160-a634-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','postnl','hero_text','Pakket versturen, ophalen of een brief posten? Bij Primera de Bandijk regelt u uw postzaken snel en efficiënt, met de vertrouwde service van uw buurtwinkel.','textarea','Korte Introductie','hero',1,'2026-09-01 18:51:02'),
('34ba5890-a634-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','rdw','meta_title','RDW Kenteken Overschrijven Goor | Primera de Bandijk','text','Titel in de browsertab','seo',1,'2026-09-01 18:51:02'),
('34ba59b2-a634-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','rdw','hero_title','RDW Diensten','text','Grote Hoofdtitel','hero',1,'2026-09-01 18:51:02'),
('34ba5d7c-a634-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','rdw','hero_text','Een voertuig gekocht, verkocht of wilt u uw rijbewijs verlengen? Kom langs en vraag naar de mogelijkheden.','textarea','Korte Introductie','hero',1,'2026-09-01 18:51:02'),
('34ba5e26-a634-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','geldmaat','meta_title','Geldmaat Locatie Goor | Geld opnemen & storten bij Primera de Bandijk','text','Titel in de browsertab','seo',1,'2026-09-01 18:51:02'),
('34ba5eb2-a634-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','geldmaat','hero_title','Geldmaat Locatie','text','Grote Hoofdtitel','hero',1,'2026-09-01 18:51:02'),
('34ba5f34-a634-11f1-aa02-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','geldmaat','hero_text','Veilig, droog en discreet bankzaken regelen? Bij Primera de Bandijk vindt u een Geldmaat-automaat voor uw dagelijkse contante transacties.','textarea','Korte Introductie','hero',1,'2026-09-01 18:51:02'),
('3b203950-18e6-4c71-889e-023da89f199f','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','about_text','','textarea','Over tekst','about',0,'2026-09-01 18:51:02'),
('4107e476-4b11-495c-9302-dab4ad5c279a','775d64f8-99a0-11f1-bc37-06bd6669bc40','algemeen','contact_regio','Hof van Twente - Goor','text','Regio','contact',0,'2026-08-29 18:15:44'),
('4b460b0e-a576-11f1-84c8-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','site_naam','De Bandijk','text','Merknaam (footer-kop)','branding',1,'2026-08-31 19:58:11'),
('4b461090-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','algemeen','site_naam','De Bandijk','text','Merknaam (footer-kop)','branding',1,'2026-09-01 18:51:02'),
('4b461130-a576-11f1-84c8-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','meta_title','Primera de Bandijk | Handig in de buurt','text','Titel in de browsertab','seo',1,'2026-08-31 19:58:11'),
('4b4611f8-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','meta_title','Primera de Bandijk | Handig in de buurt','text','Titel in de browsertab','seo',1,'2026-09-01 18:51:02'),
('4b461266-a576-11f1-84c8-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','about','meta_title','Over Primera de Bandijk | Het verhaal van onze winkel in Goor','text','Titel in de browsertab','seo',1,'2026-08-31 19:58:11'),
('4b4612d4-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','about','meta_title','Over Primera de Bandijk | Het verhaal van onze winkel in Goor','text','Titel in de browsertab','seo',1,'2026-09-01 18:51:02'),
('4b461338-a576-11f1-84c8-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','contact','meta_title','Contact & Openingstijden | Primera de Bandijk Goor','text','Titel in de browsertab','seo',1,'2026-08-31 19:58:11'),
('4b46139c-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','contact','meta_title','Contact & Openingstijden | Primera de Bandijk Goor','text','Titel in de browsertab','seo',1,'2026-09-01 18:51:02'),
('4b461400-a576-11f1-84c8-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','assortiment','meta_title','Ons Assortiment | Primera de Bandijk Goor','text','Titel in de browsertab','seo',1,'2026-08-31 19:58:11'),
('4b461464-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','assortiment','meta_title','Ons Assortiment | Primera de Bandijk Goor','text','Titel in de browsertab','seo',1,'2026-09-01 18:51:02'),
('4b4614c8-a576-11f1-84c8-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','cadeaukaarten','meta_title','Cadeaukaart Zoeker Goor | +100 kaarten bij Primera de Bandijk','text','Titel in de browsertab','seo',1,'2026-08-31 19:58:11'),
('4b46152c-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','cadeaukaarten','meta_title','Cadeaukaart Zoeker Goor | +100 kaarten bij Primera de Bandijk','text','Titel in de browsertab','seo',1,'2026-09-01 18:51:02'),
('4b461590-a576-11f1-84c8-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','nieuws','meta_title','Nieuws, Acties & Winacties | Primera de Bandijk Goor','text','Titel in de browsertab','seo',1,'2026-08-31 19:58:11'),
('4b4615f4-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','nieuws','meta_title','Nieuws, Acties & Winacties | Primera de Bandijk Goor','text','Titel in de browsertab','seo',1,'2026-09-01 18:51:02'),
('4b461658-a576-11f1-84c8-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','prijsvraag','meta_title','Prijsvraag | Primera de Bandijk','text','Titel in de browsertab','seo',1,'2026-08-31 19:58:11'),
('4b46172a-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','prijsvraag','meta_title','Prijsvraag | Primera de Bandijk','text','Titel in de browsertab','seo',1,'2026-09-01 18:51:02'),
('4b4617ac-a576-11f1-84c8-06bd6669bc41','5fd921b6-9a75-11f1-b88b-06bd6669bc40','geschiedenis','meta_title','Onze Geschiedenis | 30+ jaar Primera de Bandijk Goor','text','Titel in de browsertab','seo',1,'2026-08-31 19:58:11'),
('4b461810-a576-11f1-84c8-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','geschiedenis','meta_title','Onze Geschiedenis | 30+ jaar Primera de Bandijk Goor','text','Titel in de browsertab','seo',1,'2026-09-01 18:51:02'),
('5fd93e3a-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','contact','contact_info_title','','text','Contact info titel','info',1,'2026-08-17 20:01:18'),
('5fd94498-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','social_tiktok','','text','TikTok URL','social',1,'2026-08-17 20:00:34'),
('5fd94d62-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','hero_title','Webius','text','Hero titel','hero',1,'2026-08-17 20:00:34'),
('5fd95690-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','contact','contact_hero_text','','textarea','Contact hero tekst','hero',1,'2026-08-17 20:01:18'),
('5fd970e4-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','contact','contact_plaats','','text','Plaats','info',1,'2026-08-17 20:01:18'),
('5fd97a58-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','about_text','','textarea','Over tekst','about',1,'2026-08-17 20:00:34'),
('5fd982dc-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','contact_regio','','text','Regio','contact',1,'2026-08-17 20:01:18'),
('5fd98ba6-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','social_tiktok_actief','','boolean','TikTok actief','social',1,'2026-08-17 20:00:34'),
('5fd9904c-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','hero_image','','image','Hero afbeelding','hero',1,'2026-09-01 19:10:25'),
('5fd996be-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','contact_email','','text','E-mailadres','contact',1,'2026-08-17 20:01:18'),
('5fd99d08-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','contact','contact_adres','','text','Adres','info',1,'2026-08-17 20:01:18'),
('5fd9a14a-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','about_3','','text','Kenmerk 3','about',1,'2026-08-17 20:00:34'),
('5fd9a5dc-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','about_title','','text','Over titel','about',1,'2026-08-17 20:00:34'),
('5fd9aa3c-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','social_facebook','','text','Facebook URL','social',1,'2026-08-17 20:00:34'),
('5fd9aea6-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','contact','contact_info_text','','textarea','Contact info tekst','info',1,'2026-08-17 20:01:18'),
('5fd9b3d8-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','about_image_1','','image','Over afbeelding 1','about',1,'2026-09-01 19:10:25'),
('5fd9bb9e-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','bedrijf_kvk','','text','KVK-nummer','bedrijfsgegevens',1,'2026-08-17 20:00:34'),
('5fd9c382-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','bedrijf_btw','','text','BTW-nummer','bedrijfsgegevens',1,'2026-08-17 20:00:34'),
('5fd9cb02-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','contact','contact_telefoon','','text','Telefoonnummer','info',1,'2026-08-17 20:01:18'),
('5fd9d264-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','social_instagram_actief','','boolean','Instagram actief','social',1,'2026-08-17 20:00:34'),
('5fd9d9bc-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','contact_telefoon','','text','Telefoonnummer','contact',1,'2026-08-17 20:01:18'),
('5fd9e132-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','hero_tile','','text','Pagina Titel','Titel',1,'2026-08-17 20:00:34'),
('5fd9e876-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','social_facebook_actief','','boolean','Facebook actief','social',1,'2026-08-17 20:00:34'),
('5fd9eeb6-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','hero_text','','textarea','Hero tekst','hero',1,'2026-08-17 20:00:34'),
('5fd9f460-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','contact','contact_postcode','','text','Postcode','info',1,'2026-08-17 20:01:18'),
('5fd9fa0a-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','footer_text','','textarea','Footer tekst','footer',1,'2026-08-17 20:00:34'),
('5fd9ffa0-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','contact','contact_email','','text','E-mailadres','info',1,'2026-08-17 20:01:18'),
('5fda057c-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','contact','contact_hero_title','','text','Contact hero titel','hero',1,'2026-08-17 20:01:18'),
('5fda0b58-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','about_1','','text','Kenmerk 1','about',1,'2026-08-17 20:00:34'),
('5fda1120-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','algemeen','social_instagram','','text','Instagram URL','social',1,'2026-08-17 20:00:34'),
('5fda16fc-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','about_2','','text','Kenmerk 2','about',1,'2026-08-17 20:00:34'),
('5fda1d50-9a75-11f1-b88b-06bd6669bc40','5fd921b6-9a75-11f1-b88b-06bd6669bc40','home','about_image_2','','image','Over afbeelding 2','about',1,'2026-09-01 19:10:25'),
('606a78e1-3761-4094-9b76-f511d0cec2e3','775d64f8-99a0-11f1-bc37-06bd6669bc40','algemeen','social_tiktok_actief','1','boolean','TikTok actief','social',0,'2026-08-29 18:15:44'),
('68fabfb7-b34c-4339-9f45-9ac189eb1f88','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','hero_image','uploads/775d64f8-99a0-11f1-bc37-06bd6669bc40/19780761d7505a4af8db9afcf2bf9550.webp','image','Hero afbeelding','hero',1,'2026-09-01 19:16:13'),
('7327931f-2e40-4f50-8f2d-c5a293adc0ba','775d64f8-99a0-11f1-bc37-06bd6669bc40','contact','contact_adres','Grotestraat 135','text','Adres','info',0,'2026-08-29 18:15:44'),
('80437335-1091-40e6-96f5-953e0ec4111b','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','about_title','','text','Over titel','about',0,'2026-09-01 18:51:02'),
('8d58dd39-99f8-4d91-8298-4ebaca0b6473','775d64f8-99a0-11f1-bc37-06bd6669bc40','algemeen','social_facebook','https://www.facebook.com/BandijkGoor/?locale=nl_NL','text','Facebook URL','social',1,'2026-09-01 18:51:02'),
('9cc1ac24-ab0e-4e3b-84d3-6eea62ef3306','775d64f8-99a0-11f1-bc37-06bd6669bc40','contact','contact_info_text','Vul het formulier in of neem direct contact op via onderstaande gegevens. Ik probeer altijd binnen 24 uur te reageren op je bericht.','textarea','Contact info tekst','info',0,'2026-08-29 18:15:44'),
('a26f94a1-ee89-4755-9576-406a0476205d','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','about_image_1','','image','Over afbeelding 1','about',1,'2026-09-01 19:10:25'),
('a2b3dbbc-bcd7-40df-b1d3-cb29c3e356d8','775d64f8-99a0-11f1-bc37-06bd6669bc40','algemeen','bedrijf_kvk','12345678','text','KVK-nummer','bedrijfsgegevens',0,'2026-08-29 18:15:44'),
('ab8169e4-6b0e-4a27-a9bd-2cfb41ecc0bd','775d64f8-99a0-11f1-bc37-06bd6669bc40','algemeen','bedrijf_btw','NL123456789B01','text','BTW-nummer','bedrijfsgegevens',0,'2026-08-29 18:15:44'),
('b05f44fa-2665-44d0-8de8-a04831af3258','775d64f8-99a0-11f1-bc37-06bd6669bc40','algemeen','social_instagram_actief','1','boolean','Instagram actief','social',0,'2026-08-29 18:15:44'),
('b2dc8b86-3309-4213-96f9-973f97f26bd9','775d64f8-99a0-11f1-bc37-06bd6669bc40','algemeen','contact_telefoon','0547 26 11 44','text','Telefoonnummer','contact',0,'2026-08-29 18:15:44'),
('b6ff0076-99aa-11f1-bc37-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','hero_tile','Primera de Bandijk','text','Pagina Titel','Titel',0,'2026-08-29 18:15:44'),
('bc24b5fe-cdf8-4e2a-85cf-c39439428eb6','775d64f8-99a0-11f1-bc37-06bd6669bc40','algemeen','social_facebook_actief','1','boolean','Facebook actief','social',1,'2026-09-01 18:51:02'),
('cee03419-9fb5-46b4-8b75-b28be058e184','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','hero_text','Van officiële RDW pasfoto\'s tot PostNL services en de nieuwste boeken. Alles onder één dak bij De Bandijk in Goor.','textarea','Hero tekst','hero',0,'2026-08-29 18:15:44'),
('d08e44d3-b7b4-40a1-bba1-cdfe62430aca','775d64f8-99a0-11f1-bc37-06bd6669bc40','contact','contact_postcode','7471 BN','text','Postcode','info',0,'2026-08-29 18:15:44'),
('da926d79-2d9f-40a1-8352-fba21c9fb489','775d64f8-99a0-11f1-bc37-06bd6669bc40','algemeen','footer_text','Exclusieve haarverzorging en Beauty, gewoon bij jou thuis. Ervaar luxe, persoonlijke aandacht en ultiem vakmanschap in je eigen vertrouwde omgeving.','textarea','Footer tekst','footer',0,'2026-08-29 18:15:44'),
('e2044156-a3d1-11f1-b679-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','about','about_hero_title','Het verhaal achter De Bandijk','text','Titel bovenaan de Over Ons-pagina','hero',0,'2026-08-29 18:15:44'),
('e204446c-a3d1-11f1-b679-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','about','about_intro_text','Bij Primera De Bandijk geloven we in de kracht van persoonlijk contact en ouderwetse service in een moderne jas. Ontdek het verhaal en de mensen achter uw favoriete gemakswinkel in Goor.','textarea','Introtekst naast de foto','hero',0,'2026-08-29 18:15:44'),
('e2044520-a3d1-11f1-b679-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','about','about_story_title','Gewoon gezellig bij De Bandijk','text','Titel van het verhaal','story',0,'2026-08-29 18:15:44'),
('e2044584-a3d1-11f1-b679-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','about','about_story_1','Al drie decennia lang draait het bij ons om meer dan alleen de verkoop. René en Gerri legden in \'94 de basis, en die passie hebben ze overgedragen aan de volgende generatie. Dat René ondanks zijn oogziekte altijd vol gas is blijven geven, tekent de sfeer van onze zaak: we kijken naar wat wél kan.','textarea','Verhaal - alinea 1','story',0,'2026-08-29 18:15:44'),
('e2044638-a3d1-11f1-b679-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','about','about_story_2','Inmiddels hebben René en Cindy het stokje van de \'oude garde\' overgenomen. Samen met ons team zijn we uitgegroeid tot de plek in Goor waar je niet alleen komt voor je zaken, maar ook voor een goed humeur.','textarea','Verhaal - alinea 2 (uitgelicht)','story',0,'2026-08-29 18:15:44'),
('e20446a6-a3d1-11f1-b679-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','about','about_story_3','Of je nu binnenstapt voor een officiële pasfoto, je geluk beproeft met een staatslot of even snel een pakketje wegbrengt: we kennen onze klanten. Geen poespas, maar gewoon goede service en een praatje. Dat is De Bandijk!','textarea','Verhaal - alinea 3','story',0,'2026-08-29 18:15:44'),
('eb74b456-d950-43f8-9d50-d3ed8324dea2','775d64f8-99a0-11f1-bc37-06bd6669bc40','contact','contact_email','debandijk@primeranet.nl','text','E-mailadres','info',0,'2026-08-29 18:15:44'),
('edd843b5-28c2-4b44-8597-23b65e9efc47','775d64f8-99a0-11f1-bc37-06bd6669bc40','contact','contact_hero_title','Neem contact op','text','Contact hero titel','hero',0,'2026-08-29 18:15:44'),
('f952102a-8df8-4be1-9057-e781c1b3e22c','775d64f8-99a0-11f1-bc37-06bd6669bc40','algemeen','social_instagram','https://instagram.com/jouwpagina','text','Instagram URL','social',0,'2026-08-29 18:15:44'),
('ffd7dc65-0815-484c-8f93-3f37cf9d8e8f','775d64f8-99a0-11f1-bc37-06bd6669bc40','home','about_image_2','uploads/1781122080_Schermafbeelding 2026-05-26 om 19.12.39.png','image','Over afbeelding 2','about',1,'2026-09-01 19:10:25');
/*!40000 ALTER TABLE `site_content` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `team_members`
--

DROP TABLE IF EXISTS `team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `team_members` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `naam` varchar(150) NOT NULL,
  `functie` varchar(150) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_team_members_website` (`website_id`),
  CONSTRAINT `fk_team_members_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `team_members`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `team_members` WRITE;
/*!40000 ALTER TABLE `team_members` DISABLE KEYS */;
INSERT INTO `team_members` VALUES
('268087e8-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','Cindy','Eigenaar',1,'2026-08-30 12:34:31'),
('268087fc-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','René','Eigenaar',2,'2026-08-30 12:34:31'),
('26808806-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','Lysanne','Medewerker',3,'2026-08-30 12:34:31'),
('26808807-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','Wendy','Medewerker',4,'2026-08-30 12:34:31'),
('26808808-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','Bas','Medewerker',5,'2026-08-30 12:34:31'),
('26808810-a46f-11f1-8a1e-06bd6669bc41','775d64f8-99a0-11f1-bc37-06bd6669bc40','Iris','Medewerker',6,'2026-08-30 12:34:31');
/*!40000 ALTER TABLE `team_members` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `website_id` char(36) DEFAULT NULL,
  `role` enum('super_admin','client') NOT NULL DEFAULT 'client',
  `username` varchar(50) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_username` (`username`),
  KEY `fk_users_website` (`website_id`),
  CONSTRAINT `fk_users_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
('675b5d6f-0670-45f9-95a0-0a0645e5e285','775d64f8-99a0-11f1-bc37-06bd6669bc40','client','sebastiaan','Sebastiaan','Kamphuis','sebastiaanbaskamphuis@gmail.com','$2y$12$whOKyGaVr5EUDJfsIBhfyusiPBZrW7AYKEkIbyUtWAnFYqoIvmIGe',1,NULL,NULL,'2026-09-01 19:17:18','2026-08-24 15:33:48'),
('723a7fd0-fa9f-4359-af83-4e54f35fbf4c','5fd921b6-9a75-11f1-b88b-06bd6669bc40','client','Webius',NULL,NULL,'info@webius.nl','$2y$12$ghBPQfvpglhjcDf8tFpwme06bEmUneVkvC.gyWiq2YkQ7i0bHLOyS',1,NULL,NULL,'2026-08-24 15:19:55','2026-08-17 19:58:45'),
('bfab240b-12ac-420b-9eec-d65c0cd06d5c',NULL,'super_admin','admin',NULL,NULL,'sebastiaankamphuis0508@gmail.com','$2y$12$KdUNtAuBZUdaUZWVA1pmwufROvNda8Nptf63nal3ailaO5IbhWGhC',1,NULL,NULL,'2026-09-01 18:50:38','2026-06-24 18:31:03'),
('fc07165c-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','client','Cindy',NULL,NULL,'cindy@primeradebandijk.nl.placeholder-update-me','$2y$12$KlKs1AhbDsqzPoG6EfDdD.nP/eqzWnHeLlmES.EujB0eGLx8VWdOe',1,NULL,NULL,NULL,'2026-08-24 18:16:53'),
('fc072bce-9fe7-11f1-b21d-06bd6669bc40','775d64f8-99a0-11f1-bc37-06bd6669bc40','client','Rene',NULL,NULL,'rene@primeradebandijk.nl.placeholder-update-me','$2y$12$Uiw7W5HyMwSdRMZyXnFngOEyZROswjmvczV.8kllXNPEZzIv1Mhx6',1,NULL,NULL,NULL,'2026-08-24 18:16:53');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `usps`
--

DROP TABLE IF EXISTS `usps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usps` (
  `id` char(36) NOT NULL,
  `website_id` char(36) NOT NULL,
  `icon` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `website_id` (`website_id`),
  CONSTRAINT `fk_usps_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usps`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `usps` WRITE;
/*!40000 ALTER TABLE `usps` DISABLE KEYS */;
/*!40000 ALTER TABLE `usps` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `website_modules`
--

DROP TABLE IF EXISTS `website_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `website_modules` (
  `website_id` char(36) NOT NULL,
  `module_key` varchar(50) NOT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`website_id`,`module_key`),
  CONSTRAINT `fk_website_modules_website` FOREIGN KEY (`website_id`) REFERENCES `websites` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `website_modules`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `website_modules` WRITE;
/*!40000 ALTER TABLE `website_modules` DISABLE KEYS */;
INSERT INTO `website_modules` VALUES
('5fd921b6-9a75-11f1-b88b-06bd6669bc40','assortiment',0,'2026-08-24 18:05:28'),
('5fd921b6-9a75-11f1-b88b-06bd6669bc40','cadeaukaarten',0,'2026-08-24 18:05:28'),
('5fd921b6-9a75-11f1-b88b-06bd6669bc40','geschiedenis',0,'2026-08-24 18:05:28'),
('5fd921b6-9a75-11f1-b88b-06bd6669bc40','nieuws',0,'2026-08-24 18:05:28'),
('5fd921b6-9a75-11f1-b88b-06bd6669bc40','prijsvraag',0,'2026-08-24 18:05:28'),
('775d64f8-99a0-11f1-bc37-06bd6669bc40','assortiment',1,'2026-08-24 18:05:28'),
('775d64f8-99a0-11f1-bc37-06bd6669bc40','cadeaukaarten',1,'2026-08-24 18:05:28'),
('775d64f8-99a0-11f1-bc37-06bd6669bc40','geschiedenis',1,'2026-08-24 18:05:28'),
('775d64f8-99a0-11f1-bc37-06bd6669bc40','nieuws',1,'2026-08-24 18:05:28'),
('775d64f8-99a0-11f1-bc37-06bd6669bc40','prijsvraag',1,'2026-08-24 18:33:59');
/*!40000 ALTER TABLE `website_modules` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `websites`
--

DROP TABLE IF EXISTS `websites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `websites` (
  `id` char(36) NOT NULL,
  `domain_name` varchar(255) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `template_key` varchar(50) NOT NULL DEFAULT 'default',
  `logo_path` varchar(255) DEFAULT NULL,
  `color_primary` varchar(7) DEFAULT NULL,
  `color_secondary` varchar(7) DEFAULT NULL,
  `color_accent` varchar(7) DEFAULT NULL,
  `font_family` varchar(100) DEFAULT 'Outfit',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_websites_domain` (`domain_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `websites`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `websites` WRITE;
/*!40000 ALTER TABLE `websites` DISABLE KEYS */;
INSERT INTO `websites` VALUES
('5fd921b6-9a75-11f1-b88b-06bd6669bc40','webius.nl','Webius','default',NULL,'#00c7fc','#232323','#1f2937','Outfit',1,'2026-08-17 19:53:53'),
('775d64f8-99a0-11f1-bc37-06bd6669bc40','primeradebandijk.nl','Primera de Bandijk','default',NULL,'#0042aa','#fffb00','#0433ff','Outfit',1,'2026-08-16 18:29:51');
/*!40000 ALTER TABLE `websites` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-09-03 18:58:37
