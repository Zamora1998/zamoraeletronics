-- MySQL dump 10.13  Distrib 5.7.42, for Linux (x86_64)
--
-- Host: localhost    Database: EDMI - Soluciones Empresariales
-- ------------------------------------------------------
-- Server version	5.7.42-0ubuntu0.18.04.1
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;

/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;

/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

/*!40101 SET NAMES utf8 */;

/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;

/*!40103 SET TIME_ZONE='+00:00' */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;

/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accessdetails`
--
DROP TABLE IF EXISTS `accessdetails`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `accessdetails` (
        `access_id` SMALLINT (5) unsigned NOT NULL,
        `language_id` VARCHAR(3) COLLATE utf8mb4_unicode_ci NOT NULL,
        `name` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        `description` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`access_id`, `language_id`),
        KEY `language_id` (`language_id`),
        CONSTRAINT `accessdetails_ibfk_1` FOREIGN KEY (`access_id`) REFERENCES `accesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `accessdetails_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accessdetails`
--
LOCK TABLES `accessdetails` WRITE;

/*!40000 ALTER TABLE `accessdetails` DISABLE KEYS */;

INSERT INTO
    `accessdetails`
VALUES
    (1, 'en', 'Developer', ''),
    (1, 'es', 'Desarrollador', ''),
    (
        2,
        'en',
        'Administrator',
        'Has access to all function of the organizations owned and/or assigned.'
    ),
    (
        2,
        'es',
        'Administrador',
        'Tiene accesso a todas las funcionesde las organizaciones pertenecientes y/o asignadas.'
    );

/*!40000 ALTER TABLE `accessdetails` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `accesses`
--
DROP TABLE IF EXISTS `accesses`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `accesses` (
        `id` SMALLINT (5) unsigned NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accesses`
--
LOCK TABLES `accesses` WRITE;

/*!40000 ALTER TABLE `accesses` DISABLE KEYS */;

INSERT INTO
    `accesses`
VALUES
    (1, 'Dev'),
    (2, 'Admin');

/*!40000 ALTER TABLE `accesses` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `contactaddresses`
--
DROP TABLE IF EXISTS `contactaddresses`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `contactaddresses` (
        `id` INT (10) unsigned NOT NULL AUTO_INCREMENT,
        `contact_id` INT (10) unsigned NOT NULL,
        `contacttype_id` tinyint (3) unsigned NOT NULL,
        `countries_id` CHAR(2) COLLATE utf8mb4_unicode_ci NOT NULL,
        `addresslineone` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `addresslinetwo` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `zip` VARCHAR(45) COLLATE utf8mb4_unicode_ci NOT NULL,
        `city` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        KEY `contact_id` (`contact_id`),
        KEY `contacttype_id` (`contacttype_id`),
        KEY `country_id` (`countries_id`),
        CONSTRAINT `contactaddresses_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `contactaddresses_ibfk_2` FOREIGN KEY (`contacttype_id`) REFERENCES `contacttypes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `contactaddresses_ibfk_3` FOREIGN KEY (`countries_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactaddresses`
--
LOCK TABLES `contactaddresses` WRITE;

/*!40000 ALTER TABLE `contactaddresses` DISABLE KEYS */;

/*!40000 ALTER TABLE `contactaddresses` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `contactcompanies`
--
DROP TABLE IF EXISTS `contactcompanies`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `contactcompanies` (
        `contact_id` INT (10) unsigned NOT NULL,
        `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`contact_id`),
        CONSTRAINT `fk_contactcompanies_contacts1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactcompanies`
--
LOCK TABLES `contactcompanies` WRITE;

/*!40000 ALTER TABLE `contactcompanies` DISABLE KEYS */;

/*!40000 ALTER TABLE `contactcompanies` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `contactemails`
--
DROP TABLE IF EXISTS `contactemails`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `contactemails` (
        `id` INT (10) unsigned NOT NULL AUTO_INCREMENT,
        `contact_id` INT (10) unsigned NOT NULL,
        `contacttype_id` tinyint (5) unsigned NOT NULL,
        `emailaddress` VARCHAR(254) COLLATE utf8mb4_unicode_ci NOT NULL,
        `defaultemail` tinyint (1) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `contact_id` (`contact_id`),
        KEY `contacttype_id` (`contacttype_id`),
        KEY `emailaddress` (`emailaddress`),
        CONSTRAINT `contactemails_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `contactemails_ibfk_2` FOREIGN KEY (`contacttype_id`) REFERENCES `contacttypes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactemails`
--
LOCK TABLES `contactemails` WRITE;

/*!40000 ALTER TABLE `contactemails` DISABLE KEYS */;

/*!40000 ALTER TABLE `contactemails` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `contactpersons`
--
DROP TABLE IF EXISTS `contactpersons`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `contactpersons` (
        `contact_id` INT (10) unsigned NOT NULL,
        `firstname` VARCHAR(120) COLLATE utf8mb4_unicode_ci NOT NULL,
        `lastname` VARCHAR(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `media_id` INT (10) unsigned DEFAULT NULL,
        `title` CHAR(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`contact_id`),
        CONSTRAINT `fk_contactpersons_contacts1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactpersons`
--
LOCK TABLES `contactpersons` WRITE;

/*!40000 ALTER TABLE `contactpersons` DISABLE KEYS */;

/*!40000 ALTER TABLE `contactpersons` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `contactphones`
--
DROP TABLE IF EXISTS `contactphones`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `contactphones` (
        `id` INT (10) unsigned NOT NULL AUTO_INCREMENT,
        `contact_id` INT (10) unsigned NOT NULL,
        `contacttype_id` tinyint (3) unsigned NOT NULL,
        `countrycode` VARCHAR(7) COLLATE utf8mb4_unicode_ci NOT NULL,
        `number` VARCHAR(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `extension` SMALLINT (6) DEFAULT NULL,
        `defaultphone` tinyint (1) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        KEY `contact_id` (`contact_id`),
        KEY `contacttype_id` (`contacttype_id`),
        CONSTRAINT `contactphones_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `contactphones_ibfk_2` FOREIGN KEY (`contacttype_id`) REFERENCES `contacttypes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactphones`
--
LOCK TABLES `contactphones` WRITE;

/*!40000 ALTER TABLE `contactphones` DISABLE KEYS */;

/*!40000 ALTER TABLE `contactphones` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `contacts`
--
DROP TABLE IF EXISTS `contacts`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `contacts` (
        `id` INT (10) unsigned NOT NULL AUTO_INCREMENT,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacts`
--
LOCK TABLES `contacts` WRITE;

/*!40000 ALTER TABLE `contacts` DISABLE KEYS */;

/*!40000 ALTER TABLE `contacts` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `contactsocialmedia`
--
DROP TABLE IF EXISTS `contactsocialmedia`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `contactsocialmedia` (
        `id` INT (10) unsigned NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `contact_id` INT (10) unsigned NOT NULL,
        `socialmedia_id` INT (10) unsigned NOT NULL,
        PRIMARY KEY (`id`),
        KEY `fk_socialmedia_contacts1_idx` (`contact_id`),
        KEY `fk_socialmedia_socialmedias1_idx` (`socialmedia_id`),
        CONSTRAINT `fk_socialmedia_contacts1` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `fk_socialmedia_socialmedia1` FOREIGN KEY (`socialmedia_id`) REFERENCES `socialmedia` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactsocialmedia`
--
LOCK TABLES `contactsocialmedia` WRITE;

/*!40000 ALTER TABLE `contactsocialmedia` DISABLE KEYS */;

/*!40000 ALTER TABLE `contactsocialmedia` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `contacttypes`
--
DROP TABLE IF EXISTS `contacttypes`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `contacttypes` (
        `id` tinyint (4) unsigned NOT NULL AUTO_INCREMENT,
        `label_name` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `position` tinyint (3) unsigned NOT NULL DEFAULT '255',
        PRIMARY KEY (`id`),
        KEY `fk_contracttypes_label1_idx` (`label_name`)
    ) ENGINE = InnoDB AUTO_INCREMENT = 5 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contacttypes`
--
LOCK TABLES `contacttypes` WRITE;

/*!40000 ALTER TABLE `contacttypes` DISABLE KEYS */;

INSERT INTO
    `contacttypes`
VALUES
    (1, 'ctypWork', 1),
    (2, 'ctypMobile', 2),
    (3, 'ctypHome', 3),
    (4, 'ctypFax', 4);

/*!40000 ALTER TABLE `contacttypes` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `countries`
--
DROP TABLE IF EXISTS `countries`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `countries` (
        `id` CHAR(2) COLLATE utf8mb4_unicode_ci NOT NULL,
        `cca3` CHAR(3) COLLATE utf8mb4_unicode_ci NOT NULL,
        `currency_code` VARCHAR(3) COLLATE utf8mb4_unicode_ci NOT NULL,
        `callingcode` VARCHAR(7) COLLATE utf8mb4_unicode_ci NOT NULL,
        `region` VARCHAR(8) COLLATE utf8mb4_unicode_ci NOT NULL,
        `subregion` VARCHAR(25) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countries`
--
LOCK TABLES `countries` WRITE;

/*!40000 ALTER TABLE `countries` DISABLE KEYS */;

INSERT INTO
    `countries`
VALUES
    (
        'AD',
        'AND',
        'EUR',
        '376',
        'Europe',
        'Southern Europe'
    ),
    ('AE', 'ARE', 'AED', '971', 'Asia', 'Western Asia'),
    ('AF', 'AFG', 'AFN', '93', 'Asia', 'Southern Asia'),
    (
        'AG',
        'ATG',
        'XCD',
        '1268',
        'Americas',
        'Caribbean'
    ),
    (
        'AI',
        'AIA',
        'XCD',
        '1264',
        'Americas',
        'Caribbean'
    ),
    (
        'AL',
        'ALB',
        'ALL',
        '355',
        'Europe',
        'Southern Europe'
    ),
    ('AM', 'ARM', 'AMD', '374', 'Asia', 'Western Asia'),
    (
        'AO',
        'AGO',
        'AOA',
        '244',
        'Africa',
        'Middle Africa'
    ),
    ('AQ', 'ATA', '', '', '', ''),
    (
        'AR',
        'ARG',
        'ARS',
        '54',
        'Americas',
        'South America'
    ),
    (
        'AS',
        'ASM',
        'USD',
        '1684',
        'Oceania',
        'Polynesia'
    ),
    (
        'AT',
        'AUT',
        'EUR',
        '43',
        'Europe',
        'Western Europe'
    ),
    (
        'AU',
        'AUS',
        'AUD',
        '61',
        'Oceania',
        'Australia and New Zealand'
    ),
    (
        'AW',
        'ABW',
        'AWG',
        '297',
        'Americas',
        'Caribbean'
    ),
    (
        'AX',
        'ALA',
        'EUR',
        '358',
        'Europe',
        'Northern Europe'
    ),
    ('AZ', 'AZE', 'AZN', '994', 'Asia', 'Western Asia'),
    (
        'BA',
        'BIH',
        'BAM',
        '387',
        'Europe',
        'Southern Europe'
    ),
    (
        'BB',
        'BRB',
        'BBD',
        '1246',
        'Americas',
        'Caribbean'
    ),
    (
        'BD',
        'BGD',
        'BDT',
        '880',
        'Asia',
        'Southern Asia'
    ),
    (
        'BE',
        'BEL',
        'EUR',
        '32',
        'Europe',
        'Western Europe'
    ),
    (
        'BF',
        'BFA',
        'XOF',
        '226',
        'Africa',
        'Western Africa'
    ),
    (
        'BG',
        'BGR',
        'BGN',
        '359',
        'Europe',
        'Eastern Europe'
    ),
    ('BH', 'BHR', 'BHD', '973', 'Asia', 'Western Asia'),
    (
        'BI',
        'BDI',
        'BIF',
        '257',
        'Africa',
        'Eastern Africa'
    ),
    (
        'BJ',
        'BEN',
        'XOF',
        '229',
        'Africa',
        'Western Africa'
    ),
    (
        'BL',
        'BLM',
        'EUR',
        '590',
        'Americas',
        'Caribbean'
    ),
    (
        'BM',
        'BMU',
        'BMD',
        '1441',
        'Americas',
        'Northern America'
    ),
    (
        'BN',
        'BRN',
        'BND',
        '673',
        'Asia',
        'South-Eastern Asia'
    ),
    (
        'BO',
        'BOL',
        'BOB',
        '591',
        'Americas',
        'South America'
    ),
    (
        'BR',
        'BRA',
        'BRL',
        '55',
        'Americas',
        'South America'
    ),
    (
        'BS',
        'BHS',
        'BSD',
        '1242',
        'Americas',
        'Caribbean'
    ),
    (
        'BT',
        'BTN',
        'BTN',
        '975',
        'Asia',
        'Southern Asia'
    ),
    ('BV', 'BVT', 'NOK', '', '', ''),
    (
        'BW',
        'BWA',
        'BWP',
        '267',
        'Africa',
        'Southern Africa'
    ),
    (
        'BY',
        'BLR',
        'BYR',
        '375',
        'Europe',
        'Eastern Europe'
    ),
    (
        'BZ',
        'BLZ',
        'BZD',
        '501',
        'Americas',
        'Central America'
    ),
    (
        'CA',
        'CAN',
        'CAD',
        '1',
        'Americas',
        'Northern America'
    ),
    (
        'CC',
        'CCK',
        'AUD',
        '61',
        'Oceania',
        'Australia and New Zealand'
    ),
    (
        'CD',
        'COD',
        'CDF',
        '243',
        'Africa',
        'Middle Africa'
    ),
    (
        'CF',
        'CAF',
        'XAF',
        '236',
        'Africa',
        'Middle Africa'
    ),
    (
        'CG',
        'COG',
        'XAF',
        '242',
        'Africa',
        'Middle Africa'
    ),
    (
        'CH',
        'CHE',
        'CHE',
        '41',
        'Europe',
        'Western Europe'
    ),
    (
        'CI',
        'CIV',
        'XOF',
        '225',
        'Africa',
        'Western Africa'
    ),
    ('CK', 'COK', 'NZD', '682', 'Oceania', 'Polynesia'),
    (
        'CL',
        'CHL',
        'CLF',
        '56',
        'Americas',
        'South America'
    ),
    (
        'CM',
        'CMR',
        'XAF',
        '237',
        'Africa',
        'Middle Africa'
    ),
    ('CN', 'CHN', 'CNY', '86', 'Asia', 'Eastern Asia'),
    (
        'CO',
        'COL',
        'COP',
        '57',
        'Americas',
        'South America'
    ),
    (
        'CR',
        'CRI',
        'CRC',
        '506',
        'Americas',
        'Central America'
    ),
    ('CU', 'CUB', 'CUC', '53', 'Americas', 'Caribbean'),
    (
        'CV',
        'CPV',
        'CVE',
        '238',
        'Africa',
        'Western Africa'
    ),
    (
        'CW',
        'CUW',
        'ANG',
        '5999',
        'Americas',
        'Caribbean'
    ),
    (
        'CX',
        'CXR',
        'AUD',
        '61',
        'Oceania',
        'Australia and New Zealand'
    ),
    (
        'CY',
        'CYP',
        'EUR',
        '357',
        'Europe',
        'Eastern Europe'
    ),
    (
        'CZ',
        'CZE',
        'CZK',
        '420',
        'Europe',
        'Eastern Europe'
    ),
    (
        'DE',
        'DEU',
        'EUR',
        '49',
        'Europe',
        'Western Europe'
    ),
    (
        'DJ',
        'DJI',
        'DJF',
        '253',
        'Africa',
        'Eastern Africa'
    ),
    (
        'DK',
        'DNK',
        'DKK',
        '45',
        'Europe',
        'Northern Europe'
    ),
    (
        'DM',
        'DMA',
        'XCD',
        '1767',
        'Americas',
        'Caribbean'
    ),
    (
        'DO',
        'DOM',
        'DOP',
        '1809',
        'Americas',
        'Caribbean'
    ),
    (
        'DZ',
        'DZA',
        'DZD',
        '213',
        'Africa',
        'Northern Africa'
    ),
    (
        'EC',
        'ECU',
        'USD',
        '593',
        'Americas',
        'South America'
    ),
    (
        'EE',
        'EST',
        'EUR',
        '372',
        'Europe',
        'Northern Europe'
    ),
    (
        'EG',
        'EGY',
        'EGP',
        '20',
        'Africa',
        'Northern Africa'
    ),
    (
        'EH',
        'ESH',
        'MAD',
        '212',
        'Africa',
        'Northern Africa'
    ),
    (
        'ER',
        'ERI',
        'ERN',
        '291',
        'Africa',
        'Eastern Africa'
    ),
    (
        'ES',
        'ESP',
        'EUR',
        '34',
        'Europe',
        'Southern Europe'
    ),
    (
        'ET',
        'ETH',
        'ETB',
        '251',
        'Africa',
        'Eastern Africa'
    ),
    (
        'FI',
        'FIN',
        'EUR',
        '358',
        'Europe',
        'Northern Europe'
    ),
    ('FJ', 'FJI', 'FJD', '679', 'Oceania', 'Melanesia'),
    (
        'FK',
        'FLK',
        'FKP',
        '500',
        'Americas',
        'South America'
    ),
    (
        'FM',
        'FSM',
        'USD',
        '691',
        'Oceania',
        'Micronesia'
    ),
    (
        'FO',
        'FRO',
        'DKK',
        '298',
        'Europe',
        'Northern Europe'
    ),
    (
        'FR',
        'FRA',
        'EUR',
        '33',
        'Europe',
        'Western Europe'
    ),
    (
        'GA',
        'GAB',
        'XAF',
        '241',
        'Africa',
        'Middle Africa'
    ),
    (
        'GB',
        'GBR',
        'GBP',
        '44',
        'Europe',
        'Northern Europe'
    ),
    (
        'GD',
        'GRD',
        'XCD',
        '1473',
        'Americas',
        'Caribbean'
    ),
    ('GE', 'GEO', 'GEL', '995', 'Asia', 'Western Asia'),
    (
        'GF',
        'GUF',
        'EUR',
        '594',
        'Americas',
        'South America'
    ),
    (
        'GG',
        'GGY',
        'GBP',
        '44',
        'Europe',
        'Northern Europe'
    ),
    (
        'GH',
        'GHA',
        'GHS',
        '233',
        'Africa',
        'Western Africa'
    ),
    (
        'GI',
        'GIB',
        'GIP',
        '350',
        'Europe',
        'Southern Europe'
    ),
    (
        'GL',
        'GRL',
        'DKK',
        '299',
        'Americas',
        'Northern America'
    ),
    (
        'GM',
        'GMB',
        'GMD',
        '220',
        'Africa',
        'Western Africa'
    ),
    (
        'GN',
        'GIN',
        'GNF',
        '224',
        'Africa',
        'Western Africa'
    ),
    (
        'GP',
        'GLP',
        'EUR',
        '590',
        'Americas',
        'Caribbean'
    ),
    (
        'GQ',
        'GNQ',
        'XAF',
        '240',
        'Africa',
        'Middle Africa'
    ),
    (
        'GR',
        'GRC',
        'EUR',
        '30',
        'Europe',
        'Southern Europe'
    ),
    (
        'GS',
        'SGS',
        'GBP',
        '500',
        'Americas',
        'South America'
    ),
    (
        'GT',
        'GTM',
        'GTQ',
        '502',
        'Americas',
        'Central America'
    ),
    (
        'GU',
        'GUM',
        'USD',
        '1671',
        'Oceania',
        'Micronesia'
    ),
    (
        'GW',
        'GNB',
        'XOF',
        '245',
        'Africa',
        'Western Africa'
    ),
    (
        'GY',
        'GUY',
        'GYD',
        '592',
        'Americas',
        'South America'
    ),
    ('HK', 'HKG', 'HKD', '852', 'Asia', 'Eastern Asia'),
    ('HM', 'HMD', 'AUD', '', '', ''),
    (
        'HN',
        'HND',
        'HNL',
        '504',
        'Americas',
        'Central America'
    ),
    (
        'HR',
        'HRV',
        'HRK',
        '385',
        'Europe',
        'Southern Europe'
    ),
    (
        'HT',
        'HTI',
        'HTG',
        '509',
        'Americas',
        'Caribbean'
    ),
    (
        'HU',
        'HUN',
        'HUF',
        '36',
        'Europe',
        'Eastern Europe'
    ),
    (
        'ID',
        'IDN',
        'IDR',
        '62',
        'Asia',
        'South-Eastern Asia'
    ),
    (
        'IE',
        'IRL',
        'EUR',
        '353',
        'Europe',
        'Northern Europe'
    ),
    ('IL', 'ISR', 'ILS', '972', 'Asia', 'Western Asia'),
    (
        'IM',
        'IMN',
        'GBP',
        '44',
        'Europe',
        'Northern Europe'
    ),
    ('IN', 'IND', 'INR', '91', 'Asia', 'Southern Asia'),
    (
        'IO',
        'IOT',
        'USD',
        '246',
        'Africa',
        'Eastern Africa'
    ),
    ('IQ', 'IRQ', 'IQD', '964', 'Asia', 'Western Asia'),
    ('IR', 'IRN', 'IRR', '98', 'Asia', 'Southern Asia'),
    (
        'IS',
        'ISL',
        'ISK',
        '354',
        'Europe',
        'Northern Europe'
    ),
    (
        'IT',
        'ITA',
        'EUR',
        '39',
        'Europe',
        'Southern Europe'
    ),
    (
        'JE',
        'JEY',
        'GBP',
        '44',
        'Europe',
        'Northern Europe'
    ),
    (
        'JM',
        'JAM',
        'JMD',
        '1876',
        'Americas',
        'Caribbean'
    ),
    ('JO', 'JOR', 'JOD', '962', 'Asia', 'Western Asia'),
    ('JP', 'JPN', 'JPY', '81', 'Asia', 'Eastern Asia'),
    (
        'KE',
        'KEN',
        'KES',
        '254',
        'Africa',
        'Eastern Africa'
    ),
    ('KG', 'KGZ', 'KGS', '996', 'Asia', 'Central Asia'),
    (
        'KH',
        'KHM',
        'KHR',
        '855',
        'Asia',
        'South-Eastern Asia'
    ),
    (
        'KI',
        'KIR',
        'AUD',
        '686',
        'Oceania',
        'Micronesia'
    ),
    (
        'KM',
        'COM',
        'KMF',
        '269',
        'Africa',
        'Eastern Africa'
    ),
    (
        'KN',
        'KNA',
        'XCD',
        '1869',
        'Americas',
        'Caribbean'
    ),
    ('KP', 'PRK', 'KPW', '850', 'Asia', 'Eastern Asia'),
    ('KR', 'KOR', 'KRW', '82', 'Asia', 'Eastern Asia'),
    ('KW', 'KWT', 'KWD', '965', 'Asia', 'Western Asia'),
    (
        'KY',
        'CYM',
        'KYD',
        '1345',
        'Americas',
        'Caribbean'
    ),
    ('KZ', 'KAZ', 'KZT', '76', 'Asia', 'Central Asia'),
    (
        'LA',
        'LAO',
        'LAK',
        '856',
        'Asia',
        'South-Eastern Asia'
    ),
    ('LB', 'LBN', 'LBP', '961', 'Asia', 'Western Asia'),
    (
        'LC',
        'LCA',
        'XCD',
        '1758',
        'Americas',
        'Caribbean'
    ),
    (
        'LI',
        'LIE',
        'CHF',
        '423',
        'Europe',
        'Western Europe'
    ),
    ('LK', 'LKA', 'LKR', '94', 'Asia', 'Southern Asia'),
    (
        'LR',
        'LBR',
        'LRD',
        '231',
        'Africa',
        'Western Africa'
    ),
    (
        'LS',
        'LSO',
        'LSL',
        '266',
        'Africa',
        'Southern Africa'
    ),
    (
        'LT',
        'LTU',
        'EUR',
        '370',
        'Europe',
        'Northern Europe'
    ),
    (
        'LU',
        'LUX',
        'EUR',
        '352',
        'Europe',
        'Western Europe'
    ),
    (
        'LV',
        'LVA',
        'EUR',
        '371',
        'Europe',
        'Northern Europe'
    ),
    (
        'LY',
        'LBY',
        'LYD',
        '218',
        'Africa',
        'Northern Africa'
    ),
    (
        'MA',
        'MAR',
        'MAD',
        '212',
        'Africa',
        'Northern Africa'
    ),
    (
        'MC',
        'MCO',
        'EUR',
        '377',
        'Europe',
        'Western Europe'
    ),
    (
        'MD',
        'MDA',
        'MDL',
        '373',
        'Europe',
        'Eastern Europe'
    ),
    (
        'ME',
        'MNE',
        'EUR',
        '382',
        'Europe',
        'Southern Europe'
    ),
    (
        'MF',
        'MAF',
        'EUR',
        '590',
        'Americas',
        'Caribbean'
    ),
    (
        'MG',
        'MDG',
        'MGA',
        '261',
        'Africa',
        'Eastern Africa'
    ),
    (
        'MH',
        'MHL',
        'USD',
        '692',
        'Oceania',
        'Micronesia'
    ),
    (
        'MK',
        'MKD',
        'MKD',
        '389',
        'Europe',
        'Southern Europe'
    ),
    (
        'ML',
        'MLI',
        'XOF',
        '223',
        'Africa',
        'Western Africa'
    ),
    (
        'MM',
        'MMR',
        'MMK',
        '95',
        'Asia',
        'South-Eastern Asia'
    ),
    ('MN', 'MNG', 'MNT', '976', 'Asia', 'Eastern Asia'),
    ('MO', 'MAC', 'MOP', '853', 'Asia', 'Eastern Asia'),
    (
        'MP',
        'MNP',
        'USD',
        '1670',
        'Oceania',
        'Micronesia'
    ),
    (
        'MQ',
        'MTQ',
        'EUR',
        '596',
        'Americas',
        'Caribbean'
    ),
    (
        'MR',
        'MRT',
        'MRO',
        '222',
        'Africa',
        'Western Africa'
    ),
    (
        'MS',
        'MSR',
        'XCD',
        '1664',
        'Americas',
        'Caribbean'
    ),
    (
        'MT',
        'MLT',
        'EUR',
        '356',
        'Europe',
        'Southern Europe'
    ),
    (
        'MU',
        'MUS',
        'MUR',
        '230',
        'Africa',
        'Eastern Africa'
    ),
    (
        'MV',
        'MDV',
        'MVR',
        '960',
        'Asia',
        'Southern Asia'
    ),
    (
        'MW',
        'MWI',
        'MWK',
        '265',
        'Africa',
        'Eastern Africa'
    ),
    (
        'MX',
        'MEX',
        'MXN',
        '52',
        'Americas',
        'Central America'
    ),
    (
        'MY',
        'MYS',
        'MYR',
        '60',
        'Asia',
        'South-Eastern Asia'
    ),
    (
        'MZ',
        'MOZ',
        'MZN',
        '258',
        'Africa',
        'Eastern Africa'
    ),
    (
        'NA',
        'NAM',
        'NAD',
        '264',
        'Africa',
        'Southern Africa'
    ),
    ('NC', 'NCL', 'XPF', '687', 'Oceania', 'Melanesia'),
    (
        'NE',
        'NER',
        'XOF',
        '227',
        'Africa',
        'Western Africa'
    ),
    (
        'NF',
        'NFK',
        'AUD',
        '672',
        'Oceania',
        'Australia and New Zealand'
    ),
    (
        'NG',
        'NGA',
        'NGN',
        '234',
        'Africa',
        'Western Africa'
    ),
    (
        'NI',
        'NIC',
        'NIO',
        '505',
        'Americas',
        'Central America'
    ),
    (
        'NL',
        'NLD',
        'EUR',
        '31',
        'Europe',
        'Western Europe'
    ),
    (
        'NO',
        'NOR',
        'NOK',
        '47',
        'Europe',
        'Northern Europe'
    ),
    (
        'NP',
        'NPL',
        'NPR',
        '977',
        'Asia',
        'Southern Asia'
    ),
    (
        'NR',
        'NRU',
        'AUD',
        '674',
        'Oceania',
        'Micronesia'
    ),
    ('NU', 'NIU', 'NZD', '683', 'Oceania', 'Polynesia'),
    (
        'NZ',
        'NZL',
        'NZD',
        '64',
        'Oceania',
        'Australia and New Zealand'
    ),
    ('OM', 'OMN', 'OMR', '968', 'Asia', 'Western Asia'),
    (
        'PA',
        'PAN',
        'PAB',
        '507',
        'Americas',
        'Central America'
    ),
    (
        'PE',
        'PER',
        'PEN',
        '51',
        'Americas',
        'South America'
    ),
    ('PF', 'PYF', 'XPF', '689', 'Oceania', 'Polynesia'),
    ('PG', 'PNG', 'PGK', '675', 'Oceania', 'Melanesia'),
    (
        'PH',
        'PHL',
        'PHP',
        '63',
        'Asia',
        'South-Eastern Asia'
    ),
    ('PK', 'PAK', 'PKR', '92', 'Asia', 'Southern Asia'),
    (
        'PL',
        'POL',
        'PLN',
        '48',
        'Europe',
        'Eastern Europe'
    ),
    (
        'PM',
        'SPM',
        'EUR',
        '508',
        'Americas',
        'Northern America'
    ),
    ('PN', 'PCN', 'NZD', '64', 'Oceania', 'Polynesia'),
    (
        'PR',
        'PRI',
        'USD',
        '1787',
        'Americas',
        'Caribbean'
    ),
    ('PS', 'PSE', 'ILS', '970', 'Asia', 'Western Asia'),
    (
        'PT',
        'PRT',
        'EUR',
        '351',
        'Europe',
        'Southern Europe'
    ),
    (
        'PW',
        'PLW',
        'USD',
        '680',
        'Oceania',
        'Micronesia'
    ),
    (
        'PY',
        'PRY',
        'PYG',
        '595',
        'Americas',
        'South America'
    ),
    ('QA', 'QAT', 'QAR', '974', 'Asia', 'Western Asia'),
    (
        'RE',
        'REU',
        'EUR',
        '262',
        'Africa',
        'Eastern Africa'
    ),
    (
        'RO',
        'ROU',
        'RON',
        '40',
        'Europe',
        'Eastern Europe'
    ),
    (
        'RS',
        'SRB',
        'RSD',
        '381',
        'Europe',
        'Southern Europe'
    ),
    (
        'RU',
        'RUS',
        'RUB',
        '7',
        'Europe',
        'Eastern Europe'
    ),
    (
        'RW',
        'RWA',
        'RWF',
        '250',
        'Africa',
        'Eastern Africa'
    ),
    ('SA', 'SAU', 'SAR', '966', 'Asia', 'Western Asia'),
    ('SB', 'SLB', 'SBD', '677', 'Oceania', 'Melanesia'),
    (
        'SC',
        'SYC',
        'SCR',
        '248',
        'Africa',
        'Eastern Africa'
    ),
    (
        'SD',
        'SDN',
        'SDG',
        '249',
        'Africa',
        'Northern Africa'
    ),
    (
        'SE',
        'SWE',
        'SEK',
        '46',
        'Europe',
        'Northern Europe'
    ),
    (
        'SG',
        'SGP',
        'SGD',
        '65',
        'Asia',
        'South-Eastern Asia'
    ),
    (
        'SI',
        'SVN',
        'EUR',
        '386',
        'Europe',
        'Southern Europe'
    ),
    (
        'SJ',
        'SJM',
        'NOK',
        '4779',
        'Europe',
        'Northern Europe'
    ),
    (
        'SK',
        'SVK',
        'EUR',
        '421',
        'Europe',
        'Central Europe'
    ),
    (
        'SL',
        'SLE',
        'SLL',
        '232',
        'Africa',
        'Western Africa'
    ),
    (
        'SM',
        'SMR',
        'EUR',
        '378',
        'Europe',
        'Southern Europe'
    ),
    (
        'SN',
        'SEN',
        'XOF',
        '221',
        'Africa',
        'Western Africa'
    ),
    (
        'SO',
        'SOM',
        'SOS',
        '252',
        'Africa',
        'Eastern Africa'
    ),
    (
        'SR',
        'SUR',
        'SRD',
        '597',
        'Americas',
        'South America'
    ),
    (
        'SS',
        'SSD',
        'SSP',
        '211',
        'Africa',
        'Middle Africa'
    ),
    (
        'ST',
        'STP',
        'STD',
        '239',
        'Africa',
        'Middle Africa'
    ),
    (
        'SV',
        'SLV',
        'SVC',
        '503',
        'Americas',
        'Central America'
    ),
    (
        'SX',
        'SXM',
        'ANG',
        '1721',
        'Americas',
        'Caribbean'
    ),
    ('SY', 'SYR', 'SYP', '963', 'Asia', 'Western Asia'),
    (
        'SZ',
        'SWZ',
        'SZL',
        '268',
        'Africa',
        'Southern Africa'
    ),
    (
        'TC',
        'TCA',
        'USD',
        '1649',
        'Americas',
        'Caribbean'
    ),
    (
        'TD',
        'TCD',
        'XAF',
        '235',
        'Africa',
        'Middle Africa'
    ),
    ('TF', 'ATF', 'EUR', '', '', ''),
    (
        'TG',
        'TGO',
        'XOF',
        '228',
        'Africa',
        'Western Africa'
    ),
    (
        'TH',
        'THA',
        'THB',
        '66',
        'Asia',
        'South-Eastern Asia'
    ),
    ('TJ', 'TJK', 'TJS', '992', 'Asia', 'Central Asia'),
    ('TK', 'TKL', 'NZD', '690', 'Oceania', 'Polynesia'),
    (
        'TL',
        'TLS',
        'USD',
        '670',
        'Asia',
        'South-Eastern Asia'
    ),
    ('TM', 'TKM', 'TMT', '993', 'Asia', 'Central Asia'),
    (
        'TN',
        'TUN',
        'TND',
        '216',
        'Africa',
        'Northern Africa'
    ),
    ('TO', 'TON', 'TOP', '676', 'Oceania', 'Polynesia'),
    ('TR', 'TUR', 'TRY', '90', 'Asia', 'Western Asia'),
    (
        'TT',
        'TTO',
        'TTD',
        '1868',
        'Americas',
        'Caribbean'
    ),
    ('TV', 'TUV', 'AUD', '688', 'Oceania', 'Polynesia'),
    ('TW', 'TWN', 'TWD', '886', 'Asia', 'Eastern Asia'),
    (
        'TZ',
        'TZA',
        'TZS',
        '255',
        'Africa',
        'Eastern Africa'
    ),
    (
        'UA',
        'UKR',
        'UAH',
        '380',
        'Europe',
        'Eastern Europe'
    ),
    (
        'UG',
        'UGA',
        'UGX',
        '256',
        'Africa',
        'Eastern Africa'
    ),
    (
        'UM',
        'UMI',
        'USD',
        '',
        'Americas',
        'Northern America'
    ),
    (
        'US',
        'USA',
        'USD',
        '1',
        'Americas',
        'Northern America'
    ),
    (
        'UY',
        'URY',
        'UYI',
        '598',
        'Americas',
        'South America'
    ),
    ('UZ', 'UZB', 'UZS', '998', 'Asia', 'Central Asia'),
    (
        'VA',
        'VAT',
        'EUR',
        '3906698',
        'Europe',
        'Southern Europe'
    ),
    (
        'VC',
        'VCT',
        'XCD',
        '1784',
        'Americas',
        'Caribbean'
    ),
    (
        'VE',
        'VEN',
        'VEF',
        '58',
        'Americas',
        'South America'
    ),
    (
        'VG',
        'VGB',
        'USD',
        '1284',
        'Americas',
        'Caribbean'
    ),
    (
        'VI',
        'VIR',
        'USD',
        '1340',
        'Americas',
        'Caribbean'
    ),
    (
        'VN',
        'VNM',
        'VND',
        '84',
        'Asia',
        'South-Eastern Asia'
    ),
    ('VU', 'VUT', 'VUV', '678', 'Oceania', 'Melanesia'),
    ('WF', 'WLF', 'XPF', '681', 'Oceania', 'Polynesia'),
    ('WS', 'WSM', 'WST', '685', 'Oceania', 'Polynesia'),
    (
        'XK',
        'UNK',
        'EUR',
        '383',
        'Europe',
        'Eastern Europe'
    ),
    ('YE', 'YEM', 'YER', '967', 'Asia', 'Western Asia'),
    (
        'YT',
        'MYT',
        'EUR',
        '262',
        'Africa',
        'Eastern Africa'
    ),
    (
        'ZA',
        'ZAF',
        'ZAR',
        '27',
        'Africa',
        'Southern Africa'
    ),
    (
        'ZM',
        'ZMB',
        'ZMW',
        '260',
        'Africa',
        'Eastern Africa'
    ),
    (
        'ZW',
        'ZWE',
        'ZWL',
        '263',
        'Africa',
        'Eastern Africa'
    );

/*!40000 ALTER TABLE `countries` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `countrydetails`
--
DROP TABLE IF EXISTS `countrydetails`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `countrydetails` (
        `country_id` CHAR(2) COLLATE utf8mb4_unicode_ci NOT NULL,
        `language_id` VARCHAR(3) COLLATE utf8mb4_unicode_ci NOT NULL,
        `name` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`country_id`, `language_id`),
        KEY `language_id` (`language_id`),
        CONSTRAINT `countrydetails_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `countries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `countrydetails_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `countrydetails`
--
LOCK TABLES `countrydetails` WRITE;

/*!40000 ALTER TABLE `countrydetails` DISABLE KEYS */;

INSERT INTO
    `countrydetails`
VALUES
    ('AD', 'en', 'Andorra'),
    ('AD', 'es', 'Andorra'),
    ('AE', 'en', 'United Arab Emirates'),
    ('AE', 'es', 'Emiratos Árabes Unidos'),
    ('AF', 'en', 'Afghanistan'),
    ('AF', 'es', 'Afganistán'),
    ('AG', 'en', 'Antigua and Barbuda'),
    ('AG', 'es', 'Antigua y Barbuda'),
    ('AI', 'en', 'Anguilla'),
    ('AI', 'es', 'Anguilla'),
    ('AL', 'en', 'Albania'),
    ('AL', 'es', 'Albania'),
    ('AM', 'en', 'Armenia'),
    ('AM', 'es', 'Armenia'),
    ('AO', 'en', 'Angola'),
    ('AO', 'es', 'Angola'),
    ('AQ', 'en', 'Antarctica'),
    ('AQ', 'es', 'Antártida'),
    ('AR', 'en', 'Argentina'),
    ('AR', 'es', 'Argentina'),
    ('AS', 'en', 'American Samoa'),
    ('AS', 'es', 'Samoa Americana'),
    ('AT', 'en', 'Austria'),
    ('AT', 'es', 'Austria'),
    ('AU', 'en', 'Australia'),
    ('AU', 'es', 'Australia'),
    ('AW', 'en', 'Aruba'),
    ('AW', 'es', 'Aruba'),
    ('AX', 'en', 'Åland Islands'),
    ('AX', 'es', 'Alandia'),
    ('AZ', 'en', 'Azerbaijan'),
    ('AZ', 'es', 'Azerbaiyán'),
    ('BA', 'en', 'Bosnia and Herzegovina'),
    ('BA', 'es', 'Bosnia y Herzegovina'),
    ('BB', 'en', 'Barbados'),
    ('BB', 'es', 'Barbados'),
    ('BD', 'en', 'Bangladesh'),
    ('BD', 'es', 'Bangladesh'),
    ('BE', 'en', 'Belgium'),
    ('BE', 'es', 'Bélgica'),
    ('BF', 'en', 'Burkina Faso'),
    ('BF', 'es', 'Burkina Faso'),
    ('BG', 'en', 'Bulgaria'),
    ('BG', 'es', 'Bulgaria'),
    ('BH', 'en', 'Bahrain'),
    ('BH', 'es', 'Bahrein'),
    ('BI', 'en', 'Burundi'),
    ('BI', 'es', 'Burundi'),
    ('BJ', 'en', 'Benin'),
    ('BJ', 'es', 'Benín'),
    ('BL', 'en', 'Saint Barthélemy'),
    ('BL', 'es', 'San Bartolomé'),
    ('BM', 'en', 'Bermuda'),
    ('BM', 'es', 'Bermudas'),
    ('BN', 'en', 'Brunei'),
    ('BN', 'es', 'Brunei'),
    ('BO', 'en', 'Bolivia'),
    ('BO', 'es', 'Bolivia'),
    ('BR', 'en', 'Brazil'),
    ('BR', 'es', 'Brasil'),
    ('BS', 'en', 'Bahamas'),
    ('BS', 'es', 'Bahamas'),
    ('BT', 'en', 'Bhutan'),
    ('BT', 'es', 'Bután'),
    ('BV', 'en', 'Bouvet Island'),
    ('BV', 'es', 'Isla Bouvet'),
    ('BW', 'en', 'Botswana'),
    ('BW', 'es', 'Botswana'),
    ('BY', 'en', 'Belarus'),
    ('BY', 'es', 'Bielorrusia'),
    ('BZ', 'en', 'Belize'),
    ('BZ', 'es', 'Belice'),
    ('CA', 'en', 'Canada'),
    ('CA', 'es', 'Canadá'),
    ('CC', 'en', 'Cocos (Keeling) Islands'),
    ('CC', 'es', 'Islas Cocos o Islas Keeling'),
    ('CD', 'en', 'DR Congo'),
    ('CD', 'es', 'Congo (Rep. Dem.)'),
    ('CF', 'en', 'Central African Republic'),
    ('CF', 'es', 'República Centroafricana'),
    ('CG', 'en', 'Republic of the Congo'),
    ('CG', 'es', 'Congo'),
    ('CH', 'en', 'Switzerland'),
    ('CH', 'es', 'Suiza'),
    ('CI', 'en', 'Ivory Coast'),
    ('CI', 'es', 'Costa de Marfil'),
    ('CK', 'en', 'Cook Islands'),
    ('CK', 'es', 'Islas Cook'),
    ('CL', 'en', 'Chile'),
    ('CL', 'es', 'Chile'),
    ('CM', 'en', 'Cameroon'),
    ('CM', 'es', 'Camerún'),
    ('CN', 'en', 'China'),
    ('CN', 'es', 'China'),
    ('CO', 'en', 'Colombia'),
    ('CO', 'es', 'Colombia'),
    ('CR', 'en', 'Costa Rica'),
    ('CR', 'es', 'Costa Rica'),
    ('CU', 'en', 'Cuba'),
    ('CU', 'es', 'Cuba'),
    ('CV', 'en', 'Cape Verde'),
    ('CV', 'es', 'Cabo Verde'),
    ('CW', 'en', 'Curaçao'),
    ('CW', 'es', 'Curazao'),
    ('CX', 'en', 'Christmas Island'),
    ('CX', 'es', 'Isla de Navidad'),
    ('CY', 'en', 'Cyprus'),
    ('CY', 'es', 'Chipre'),
    ('CZ', 'en', 'Czech Republic'),
    ('CZ', 'es', 'República Checa'),
    ('DE', 'en', 'Germany'),
    ('DE', 'es', 'Alemania'),
    ('DJ', 'en', 'Djibouti'),
    ('DJ', 'es', 'Djibouti'),
    ('DK', 'en', 'Denmark'),
    ('DK', 'es', 'Dinamarca'),
    ('DM', 'en', 'Dominica'),
    ('DM', 'es', 'Dominica'),
    ('DO', 'en', 'Dominican Republic'),
    ('DO', 'es', 'República Dominicana'),
    ('DZ', 'en', 'Algeria'),
    ('DZ', 'es', 'Argelia'),
    ('EC', 'en', 'Ecuador'),
    ('EC', 'es', 'Ecuador'),
    ('EE', 'en', 'Estonia'),
    ('EE', 'es', 'Estonia'),
    ('EG', 'en', 'Egypt'),
    ('EG', 'es', 'Egipto'),
    ('EH', 'en', 'Western Sahara'),
    ('EH', 'es', 'Sahara Occidental'),
    ('ER', 'en', 'Eritrea'),
    ('ER', 'es', 'Eritrea'),
    ('ES', 'en', 'Spain'),
    ('ES', 'es', 'España'),
    ('ET', 'en', 'Ethiopia'),
    ('ET', 'es', 'Etiopía'),
    ('FI', 'en', 'Finland'),
    ('FI', 'es', 'Finlandia'),
    ('FJ', 'en', 'Fiji'),
    ('FJ', 'es', 'Fiyi'),
    ('FK', 'en', 'Falkland Islands'),
    ('FK', 'es', 'Islas Malvinas'),
    ('FM', 'en', 'Micronesia'),
    ('FM', 'es', 'Micronesia'),
    ('FO', 'en', 'Faroe Islands'),
    ('FO', 'es', 'Islas Faroe'),
    ('FR', 'en', 'France'),
    ('FR', 'es', 'Francia'),
    ('GA', 'en', 'Gabon'),
    ('GA', 'es', 'Gabón'),
    ('GB', 'en', 'United Kingdom'),
    ('GB', 'es', 'Reino Unido'),
    ('GD', 'en', 'Grenada'),
    ('GD', 'es', 'Grenada'),
    ('GE', 'en', 'Georgia'),
    ('GE', 'es', 'Georgia'),
    ('GF', 'en', 'French Guiana'),
    ('GF', 'es', 'Guayana Francesa'),
    ('GG', 'en', 'Guernsey'),
    ('GG', 'es', 'Guernsey'),
    ('GH', 'en', 'Ghana'),
    ('GH', 'es', 'Ghana'),
    ('GI', 'en', 'Gibraltar'),
    ('GI', 'es', 'Gibraltar'),
    ('GL', 'en', 'Greenland'),
    ('GL', 'es', 'Groenlandia'),
    ('GM', 'en', 'Gambia'),
    ('GM', 'es', 'Gambia'),
    ('GN', 'en', 'Guinea'),
    ('GN', 'es', 'Guinea'),
    ('GP', 'en', 'Guadeloupe'),
    ('GP', 'es', 'Guadalupe'),
    ('GQ', 'en', 'Equatorial Guinea'),
    ('GQ', 'es', 'Guinea Ecuatorial'),
    ('GR', 'en', 'Greece'),
    ('GR', 'es', 'Grecia'),
    ('GS', 'en', 'South Georgia'),
    (
        'GS',
        'es',
        'Islas Georgias del Sur y Sandwich del Sur'
    ),
    ('GT', 'en', 'Guatemala'),
    ('GT', 'es', 'Guatemala'),
    ('GU', 'en', 'Guam'),
    ('GU', 'es', 'Guam'),
    ('GW', 'en', 'Guinea-Bissau'),
    ('GW', 'es', 'Guinea-Bisáu'),
    ('GY', 'en', 'Guyana'),
    ('GY', 'es', 'Guyana'),
    ('HK', 'en', 'Hong Kong'),
    ('HK', 'es', 'Hong Kong'),
    ('HM', 'en', 'Heard Island and McDonald Islands'),
    ('HM', 'es', 'Islas Heard y McDonald'),
    ('HN', 'en', 'Honduras'),
    ('HN', 'es', 'Honduras'),
    ('HR', 'en', 'Croatia'),
    ('HR', 'es', 'Croacia'),
    ('HT', 'en', 'Haiti'),
    ('HT', 'es', 'Haiti'),
    ('HU', 'en', 'Hungary'),
    ('HU', 'es', 'Hungría'),
    ('ID', 'en', 'Indonesia'),
    ('ID', 'es', 'Indonesia'),
    ('IE', 'en', 'Ireland'),
    ('IE', 'es', 'Irlanda'),
    ('IL', 'en', 'Israel'),
    ('IL', 'es', 'Israel'),
    ('IM', 'en', 'Isle of Man'),
    ('IM', 'es', 'Isla de Man'),
    ('IN', 'en', 'India'),
    ('IN', 'es', 'India'),
    ('IO', 'en', 'British Indian Ocean Territory'),
    (
        'IO',
        'es',
        'Territorio Británico del Océano Índico'
    ),
    ('IQ', 'en', 'Iraq'),
    ('IQ', 'es', 'Irak'),
    ('IR', 'en', 'Iran'),
    ('IR', 'es', 'Iran'),
    ('IS', 'en', 'Iceland'),
    ('IS', 'es', 'Islandia'),
    ('IT', 'en', 'Italy'),
    ('IT', 'es', 'Italia'),
    ('JE', 'en', 'Jersey'),
    ('JE', 'es', 'Jersey'),
    ('JM', 'en', 'Jamaica'),
    ('JM', 'es', 'Jamaica'),
    ('JO', 'en', 'Jordan'),
    ('JO', 'es', 'Jordania'),
    ('JP', 'en', 'Japan'),
    ('JP', 'es', 'Japón'),
    ('KE', 'en', 'Kenya'),
    ('KE', 'es', 'Kenia'),
    ('KG', 'en', 'Kyrgyzstan'),
    ('KG', 'es', 'Kirguizistán'),
    ('KH', 'en', 'Cambodia'),
    ('KH', 'es', 'Camboya'),
    ('KI', 'en', 'Kiribati'),
    ('KI', 'es', 'Kiribati'),
    ('KM', 'en', 'Comoros'),
    ('KM', 'es', 'Comoras'),
    ('KN', 'en', 'Saint Kitts and Nevis'),
    ('KN', 'es', 'San Cristóbal y Nieves'),
    ('KP', 'en', 'North Korea'),
    ('KP', 'es', 'Corea del Norte'),
    ('KR', 'en', 'South Korea'),
    ('KR', 'es', 'Corea del Sur'),
    ('KW', 'en', 'Kuwait'),
    ('KW', 'es', 'Kuwait'),
    ('KY', 'en', 'Cayman Islands'),
    ('KY', 'es', 'Islas Caimán'),
    ('KZ', 'en', 'Kazakhstan'),
    ('KZ', 'es', 'Kazajistán'),
    ('LA', 'en', 'Laos'),
    ('LA', 'es', 'Laos'),
    ('LB', 'en', 'Lebanon'),
    ('LB', 'es', 'Líbano'),
    ('LC', 'en', 'Saint Lucia'),
    ('LC', 'es', 'Santa Lucía'),
    ('LI', 'en', 'Liechtenstein'),
    ('LI', 'es', 'Liechtenstein'),
    ('LK', 'en', 'Sri Lanka'),
    ('LK', 'es', 'Sri Lanka'),
    ('LR', 'en', 'Liberia'),
    ('LR', 'es', 'Liberia'),
    ('LS', 'en', 'Lesotho'),
    ('LS', 'es', 'Lesotho'),
    ('LT', 'en', 'Lithuania'),
    ('LT', 'es', 'Lituania'),
    ('LU', 'en', 'Luxembourg'),
    ('LU', 'es', 'Luxemburgo'),
    ('LV', 'en', 'Latvia'),
    ('LV', 'es', 'Letonia'),
    ('LY', 'en', 'Libya'),
    ('LY', 'es', 'Libia'),
    ('MA', 'en', 'Morocco'),
    ('MA', 'es', 'Marruecos'),
    ('MC', 'en', 'Monaco'),
    ('MC', 'es', 'Mónaco'),
    ('MD', 'en', 'Moldova'),
    ('MD', 'es', 'Moldavia'),
    ('ME', 'en', 'Montenegro'),
    ('ME', 'es', 'Montenegro'),
    ('MF', 'en', 'Saint Martin'),
    ('MF', 'es', 'Saint Martin'),
    ('MG', 'en', 'Madagascar'),
    ('MG', 'es', 'Madagascar'),
    ('MH', 'en', 'Marshall Islands'),
    ('MH', 'es', 'Islas Marshall'),
    ('MK', 'en', 'Macedonia'),
    ('MK', 'es', 'Macedonia'),
    ('ML', 'en', 'Mali'),
    ('ML', 'es', 'Mali'),
    ('MM', 'en', 'Myanmar'),
    ('MM', 'es', 'Myanmar'),
    ('MN', 'en', 'Mongolia'),
    ('MN', 'es', 'Mongolia'),
    ('MO', 'en', 'Macau'),
    ('MO', 'es', 'Macao'),
    ('MP', 'en', 'Northern Mariana Islands'),
    ('MP', 'es', 'Islas Marianas del Norte'),
    ('MQ', 'en', 'Martinique'),
    ('MQ', 'es', 'Martinica'),
    ('MR', 'en', 'Mauritania'),
    ('MR', 'es', 'Mauritania'),
    ('MS', 'en', 'Montserrat'),
    ('MS', 'es', 'Montserrat'),
    ('MT', 'en', 'Malta'),
    ('MT', 'es', 'Malta'),
    ('MU', 'en', 'Mauritius'),
    ('MU', 'es', 'Mauricio'),
    ('MV', 'en', 'Maldives'),
    ('MV', 'es', 'Maldivas'),
    ('MW', 'en', 'Malawi'),
    ('MW', 'es', 'Malawi'),
    ('MX', 'en', 'Mexico'),
    ('MX', 'es', 'México'),
    ('MY', 'en', 'Malaysia'),
    ('MY', 'es', 'Malasia'),
    ('MZ', 'en', 'Mozambique'),
    ('MZ', 'es', 'Mozambique'),
    ('NA', 'en', 'Namibia'),
    ('NA', 'es', 'Namibia'),
    ('NC', 'en', 'New Caledonia'),
    ('NC', 'es', 'Nueva Caledonia'),
    ('NE', 'en', 'Niger'),
    ('NE', 'es', 'Níger'),
    ('NF', 'en', 'Norfolk Island'),
    ('NF', 'es', 'Isla de Norfolk'),
    ('NG', 'en', 'Nigeria'),
    ('NG', 'es', 'Nigeria'),
    ('NI', 'en', 'Nicaragua'),
    ('NI', 'es', 'Nicaragua'),
    ('NL', 'en', 'Netherlands'),
    ('NL', 'es', 'Países Bajos'),
    ('NO', 'en', 'Norway'),
    ('NO', 'es', 'Noruega'),
    ('NP', 'en', 'Nepal'),
    ('NP', 'es', 'Nepal'),
    ('NR', 'en', 'Nauru'),
    ('NR', 'es', 'Nauru'),
    ('NU', 'en', 'Niue'),
    ('NU', 'es', 'Niue'),
    ('NZ', 'en', 'New Zealand'),
    ('NZ', 'es', 'Nueva Zelanda'),
    ('OM', 'en', 'Oman'),
    ('OM', 'es', 'Omán'),
    ('PA', 'en', 'Panama'),
    ('PA', 'es', 'Panamá'),
    ('PE', 'en', 'Peru'),
    ('PE', 'es', 'Perú'),
    ('PF', 'en', 'French Polynesia'),
    ('PF', 'es', 'Polinesia Francesa'),
    ('PG', 'en', 'Papua New Guinea'),
    ('PG', 'es', 'Papúa Nueva Guinea'),
    ('PH', 'en', 'Philippines'),
    ('PH', 'es', 'Filipinas'),
    ('PK', 'en', 'Pakistan'),
    ('PK', 'es', 'Pakistán'),
    ('PL', 'en', 'Poland'),
    ('PL', 'es', 'Polonia'),
    ('PM', 'en', 'Saint Pierre and Miquelon'),
    ('PM', 'es', 'San Pedro y Miquelón'),
    ('PN', 'en', 'Pitcairn Islands'),
    ('PN', 'es', 'Islas Pitcairn'),
    ('PR', 'en', 'Puerto Rico'),
    ('PR', 'es', 'Puerto Rico'),
    ('PS', 'en', 'Palestine'),
    ('PS', 'es', 'Palestina'),
    ('PT', 'en', 'Portugal'),
    ('PT', 'es', 'Portugal'),
    ('PW', 'en', 'Palau'),
    ('PW', 'es', 'Palau'),
    ('PY', 'en', 'Paraguay'),
    ('PY', 'es', 'Paraguay'),
    ('QA', 'en', 'Qatar'),
    ('QA', 'es', 'Catar'),
    ('RE', 'en', 'Réunion'),
    ('RE', 'es', 'Reunión'),
    ('RO', 'en', 'Romania'),
    ('RO', 'es', 'Rumania'),
    ('RS', 'en', 'Serbia'),
    ('RS', 'es', 'Serbia'),
    ('RU', 'en', 'Russia'),
    ('RU', 'es', 'Rusia'),
    ('RW', 'en', 'Rwanda'),
    ('RW', 'es', 'Ruanda'),
    ('SA', 'en', 'Saudi Arabia'),
    ('SA', 'es', 'Arabia Saudí'),
    ('SB', 'en', 'Solomon Islands'),
    ('SB', 'es', 'Islas Salomón'),
    ('SC', 'en', 'Seychelles'),
    ('SC', 'es', 'Seychelles'),
    ('SD', 'en', 'Sudan'),
    ('SD', 'es', 'Sudán'),
    ('SE', 'en', 'Sweden'),
    ('SE', 'es', 'Suecia'),
    ('SG', 'en', 'Singapore'),
    ('SG', 'es', 'Singapur'),
    ('SI', 'en', 'Slovenia'),
    ('SI', 'es', 'Eslovenia'),
    ('SJ', 'en', 'Svalbard and Jan Mayen'),
    ('SJ', 'es', 'Islas Svalbard y Jan Mayen'),
    ('SK', 'en', 'Slovakia'),
    ('SK', 'es', 'República Eslovaca'),
    ('SL', 'en', 'Sierra Leone'),
    ('SL', 'es', 'Sierra Leone'),
    ('SM', 'en', 'San Marino'),
    ('SM', 'es', 'San Marino'),
    ('SN', 'en', 'Senegal'),
    ('SN', 'es', 'Senegal'),
    ('SO', 'en', 'Somalia'),
    ('SO', 'es', 'Somalia'),
    ('SR', 'en', 'Suriname'),
    ('SR', 'es', 'Surinam'),
    ('SS', 'en', 'South Sudan'),
    ('SS', 'es', 'Sudán del Sur'),
    ('ST', 'en', 'São Tomé and Príncipe'),
    ('ST', 'es', 'Santo Tomé y Príncipe'),
    ('SV', 'en', 'El Salvador'),
    ('SV', 'es', 'El Salvador'),
    ('SX', 'en', 'Sint Maarten'),
    ('SX', 'es', 'Sint Maarten'),
    ('SY', 'en', 'Syria'),
    ('SY', 'es', 'Siria'),
    ('SZ', 'en', 'Swaziland'),
    ('SZ', 'es', 'Suazilandia'),
    ('TC', 'en', 'Turks and Caicos Islands'),
    ('TC', 'es', 'Islas Turks y Caicos'),
    ('TD', 'en', 'Chad'),
    ('TD', 'es', 'Chad'),
    ('TF', 'en', 'French Southern and Antarctic Lands'),
    (
        'TF',
        'es',
        'Tierras Australes y Antárticas Francesas'
    ),
    ('TG', 'en', 'Togo'),
    ('TG', 'es', 'Togo'),
    ('TH', 'en', 'Thailand'),
    ('TH', 'es', 'Tailandia'),
    ('TJ', 'en', 'Tajikistan'),
    ('TJ', 'es', 'Tayikistán'),
    ('TK', 'en', 'Tokelau'),
    ('TK', 'es', 'Islas Tokelau'),
    ('TL', 'en', 'Timor-Leste'),
    ('TL', 'es', 'Timor Oriental'),
    ('TM', 'en', 'Turkmenistan'),
    ('TM', 'es', 'Turkmenistán'),
    ('TN', 'en', 'Tunisia'),
    ('TN', 'es', 'Túnez'),
    ('TO', 'en', 'Tonga'),
    ('TO', 'es', 'Tonga'),
    ('TR', 'en', 'Turkey'),
    ('TR', 'es', 'Turquía'),
    ('TT', 'en', 'Trinidad and Tobago'),
    ('TT', 'es', 'Trinidad y Tobago'),
    ('TV', 'en', 'Tuvalu'),
    ('TV', 'es', 'Tuvalu'),
    ('TW', 'en', 'Taiwan'),
    ('TW', 'es', 'Taiwán'),
    ('TZ', 'en', 'Tanzania'),
    ('TZ', 'es', 'Tanzania'),
    ('UA', 'en', 'Ukraine'),
    ('UA', 'es', 'Ucrania'),
    ('UG', 'en', 'Uganda'),
    ('UG', 'es', 'Uganda'),
    (
        'UM',
        'en',
        'United States Minor Outlying Islands'
    ),
    (
        'UM',
        'es',
        'Islas Ultramarinas Menores de Estados Unidos'
    ),
    ('US', 'en', 'United States'),
    ('US', 'es', 'Estados Unidos'),
    ('UY', 'en', 'Uruguay'),
    ('UY', 'es', 'Uruguay'),
    ('UZ', 'en', 'Uzbekistan'),
    ('UZ', 'es', 'Uzbekistán'),
    ('VA', 'en', 'Vatican City'),
    ('VA', 'es', 'Ciudad del Vaticano'),
    ('VC', 'en', 'Saint Vincent and the Grenadines'),
    ('VC', 'es', 'San Vicente y Granadinas'),
    ('VE', 'en', 'Venezuela'),
    ('VE', 'es', 'Venezuela'),
    ('VG', 'en', 'British Virgin Islands'),
    ('VG', 'es', 'Islas Vírgenes del Reino Unido'),
    ('VI', 'en', 'United States Virgin Islands'),
    (
        'VI',
        'es',
        'Islas Vírgenes de los Estados Unidos'
    ),
    ('VN', 'en', 'Vietnam'),
    ('VN', 'es', 'Vietnam'),
    ('VU', 'en', 'Vanuatu'),
    ('VU', 'es', 'Vanuatu'),
    ('WF', 'en', 'Wallis and Futuna'),
    ('WF', 'es', 'Wallis y Futuna'),
    ('WS', 'en', 'Samoa'),
    ('WS', 'es', 'Samoa'),
    ('XK', 'en', 'Kosovo'),
    ('XK', 'es', 'Kosovo'),
    ('YE', 'en', 'Yemen'),
    ('YE', 'es', 'Yemen'),
    ('YT', 'en', 'Mayotte'),
    ('YT', 'es', 'Mayotte'),
    ('ZA', 'en', 'South Africa'),
    ('ZA', 'es', 'República de Sudáfrica'),
    ('ZM', 'en', 'Zambia'),
    ('ZM', 'es', 'Zambia'),
    ('ZW', 'en', 'Zimbabwe'),
    ('ZW', 'es', 'Zimbabue');

/*!40000 ALTER TABLE `countrydetails` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `labeldetails`
--
DROP TABLE IF EXISTS `labeldetails`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `labeldetails` (
        `label_id` INT (10) unsigned NOT NULL,
        `language_id` VARCHAR(3) COLLATE utf8mb4_unicode_ci NOT NULL,
        `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`label_id`, `language_id`),
        KEY `language_id` (`language_id`),
        CONSTRAINT `labeldetails_ibfk_1` FOREIGN KEY (`label_id`) REFERENCES `labels` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `labeldetails_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `labeldetails`
--
LOCK TABLES `labeldetails` WRITE;

/*!40000 ALTER TABLE `labeldetails` DISABLE KEYS */;

INSERT INTO
    `labeldetails`
VALUES
    (1, 'en', 'Add'),
    (1, 'es', 'Añadir'),
    (2, 'en', 'Address'),
    (2, 'es', 'Dirección'),
    (3, 'en', 'Cancel'),
    (3, 'es', 'Cancelar'),
    (4, 'en', 'City'),
    (4, 'es', 'Ciudad'),
    (5, 'en', 'Comany'),
    (5, 'es', 'Compañía'),
    (6, 'en', 'Country'),
    (6, 'es', 'País'),
    (7, 'en', 'Delete'),
    (7, 'es', 'Eliminar'),
    (8, 'en', 'Email'),
    (8, 'es', 'Correo Electrónico'),
    (9, 'en', 'Name'),
    (9, 'es', 'Nombre'),
    (10, 'en', 'Phone'),
    (10, 'es', 'Telefono'),
    (11, 'en', 'Search'),
    (11, 'es', 'Buscar'),
    (12, 'en', 'Select'),
    (12, 'es', 'Seleccionar'),
    (13, 'en', 'Social network'),
    (13, 'es', 'Red social'),
    (14, 'en', 'Zip Code'),
    (14, 'es', 'Código Postal'),
    (15, 'en', 'Fax'),
    (15, 'es', 'Fax'),
    (16, 'en', 'Home'),
    (16, 'es', 'Casa'),
    (17, 'en', 'Mobile'),
    (17, 'es', 'Móvil'),
    (18, 'en', 'Work'),
    (18, 'es', 'Trabajo'),
    (22, 'en', 'Skype'),
    (22, 'es', 'Skype'),
    (23, 'en', 'Facebook'),
    (23, 'es', 'Facebook'),
    (24, 'en', 'Twitter'),
    (24, 'es', 'Twitter'),
    (25, 'en', 'Web page'),
    (25, 'es', 'Página web'),
    (26, 'en', 'Instagram'),
    (26, 'es', 'Instagram'),
    (27, 'en', 'Linkedin'),
    (27, 'es', 'Linkedin'),
    (28, 'en', 'WhatsApp'),
    (28, 'es', 'WhatsApp'),
    (29, 'en', 'Home'),
    (29, 'es', 'Principal'),
    (30, 'en', 'Features'),
    (30, 'es', 'Características'),
    (31, 'en', 'Pricing'),
    (31, 'es', 'Precios'),
    (32, 'en', 'FAQs'),
    (32, 'es', 'Preguntas'),
    (33, 'en', 'About'),
    (33, 'es', 'Acerca'),
    (34, 'en', 'Dashboard'),
    (34, 'es', 'Panel'),
    (35, 'en', 'Organizations'),
    (35, 'es', 'Organizaciones'),
    (36, 'en', 'Events'),
    (36, 'es', 'Eventos'),
    (37, 'en', 'Contacts'),
    (37, 'es', 'Contactos'),
    (38, 'en', 'Sign in'),
    (38, 'es', 'Ingresar'),
    (39, 'en', 'Sign up'),
    (39, 'es', 'Inscribir'),
    (40, 'en', 'Sign out'),
    (40, 'es', 'Cerrar sesión'),
    (41, 'en', 'Actions'),
    (41, 'es', 'Acciones'),
    (42, 'en', 'Name'),
    (42, 'es', 'Nombre'),
    (43, 'en', 'Organization name'),
    (43, 'es', 'Nombre organización'),
    (44, 'en', 'Create'),
    (44, 'es', 'Crear'),
    (45, 'en', 'Aproved'),
    (45, 'es', 'Aprobado'),
    (46, 'en', 'Denied'),
    (46, 'es', 'Denegado'),
    (47, 'en', 'Groups'),
    (47, 'es', 'Grupos'),
    (48, 'en', 'Posts'),
    (48, 'es', 'Puestos'),
    (49, 'en', 'New'),
    (49, 'es', 'Nuevo'),
    (50, 'en', 'Edit'),
    (50, 'es', 'Modificar'),
    (51, 'en', 'Delete'),
    (51, 'es', 'Eliminar'),
    (52, 'en', 'Enabled'),
    (52, 'es', 'Habilitado'),
    (53, 'en', 'Status'),
    (53, 'es', 'Estado'),
    (54, 'en', 'Start'),
    (54, 'es', 'Inicio'),
    (55, 'en', 'End'),
    (55, 'es', 'Final'),
    (56, 'en', 'Owner'),
    (56, 'es', 'Dueño'),
    (57, 'en', 'Save'),
    (57, 'es', 'Guardar'),
    (58, 'en', 'Cancel'),
    (58, 'es', 'Cancelar'),
    (59, 'en', 'Preparing'),
    (59, 'es', 'En preparación'),
    (60, 'en', 'Ready'),
    (60, 'es', 'Listo'),
    (61, 'en', 'Running'),
    (61, 'es', 'Corriendo'),
    (62, 'en', 'Completed'),
    (62, 'es', 'Concluido'),
    (67, 'en', 'Deliverables'),
    (67, 'es', 'Entregables'),
    (68, 'en', 'Accesses'),
    (68, 'es', 'Accesos'),
    (69, 'en', 'Transfer'),
    (69, 'es', 'Transferir'),
    (70, 'en', 'Edit group'),
    (70, 'es', 'Modificar grupo'),
    (71, 'en', 'New group'),
    (71, 'es', 'Grupo nuevo'),
    (72, 'en', 'New event'),
    (72, 'es', 'Evento nuevo'),
    (73, 'en', 'Edit event'),
    (73, 'es', 'Modificar evento'),
    (74, 'en', 'Yes'),
    (74, 'es', 'Si'),
    (75, 'en', 'No'),
    (75, 'es', 'No'),
    (76, 'en', 'Are you sure you want to delete?'),
    (76, 'es', '¿Esta seguro/a que quiere eliminar?'),
    (77, 'en', 'Successfuly deleted.'),
    (77, 'es', 'Eliminado con éxito.'),
    (78, 'en', 'There was an error while deleting.'),
    (78, 'es', 'Hubo un error al eliminar.'),
    (79, 'en', 'Successfuly created.'),
    (79, 'es', 'Creado con éxito.'),
    (80, 'en', 'There was an error while creating.'),
    (80, 'es', 'Hubo un error al crear.'),
    (81, 'en', 'Successfuly updated.'),
    (81, 'es', 'Modificado con éxito.'),
    (82, 'en', 'There was an error while updating.'),
    (82, 'es', 'Hubo un error al modificar.'),
    (83, 'en', 'Link'),
    (83, 'es', 'Enlace'),
    (84, 'en', 'Copy link'),
    (84, 'es', 'Copiar enlace'),
    (85, 'en', 'New Deliverable'),
    (85, 'es', 'Entregable nuevo'),
    (86, 'en', 'Edit deliverable'),
    (86, 'es', 'Midificar entregable'),
    (87, 'en', 'Quantity'),
    (87, 'es', 'Cantidad'),
    (88, 'en', 'Visitors'),
    (88, 'es', 'Visitantes'),
    (89, 'en', 'Company'),
    (89, 'es', 'Compañia'),
    (90, 'en', 'Email'),
    (90, 'es', 'Corréo'),
    (91, 'en', 'Phone'),
    (91, 'es', 'Teléfono'),
    (92, 'en', 'Group'),
    (92, 'es', 'Grupo');

/*!40000 ALTER TABLE `labeldetails` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `labels`
--
DROP TABLE IF EXISTS `labels`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `labels` (
        `id` INT (10) unsigned NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE = InnoDB AUTO_INCREMENT = 93 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `labels`
--
LOCK TABLES `labels` WRITE;

/*!40000 ALTER TABLE `labels` DISABLE KEYS */;

INSERT INTO
    `labels`
VALUES
    (68, 'btnAccesses'),
    (58, 'btnCancel'),
    (84, 'btnCopyLink'),
    (44, 'btnCreate'),
    (51, 'btnDelete'),
    (50, 'btnEdit'),
    (49, 'btnNew'),
    (75, 'btnNo'),
    (57, 'btnSave'),
    (69, 'btnTransfer'),
    (74, 'btnYes'),
    (15, 'ctypFax'),
    (16, 'ctypHome'),
    (17, 'ctypMobile'),
    (18, 'ctypWork'),
    (62, 'evtCompleted'),
    (59, 'evtPreparing'),
    (60, 'evtReady'),
    (61, 'evtRunning'),
    (1, 'lblAdd'),
    (2, 'lblAddress'),
    (3, 'lblCancel'),
    (4, 'lblCity'),
    (5, 'lblCompany'),
    (6, 'lblCountry'),
    (7, 'lblDelete'),
    (86, 'lblEditDeliverable'),
    (73, 'lblEditEvent'),
    (70, 'lblEditGroup'),
    (8, 'lblEmail'),
    (9, 'lblName'),
    (85, 'lblNewDeliverable'),
    (72, 'lblNewEvent'),
    (71, 'lblNewGroup'),
    (43, 'lblOrganizationName'),
    (10, 'lblPhone'),
    (11, 'lblSearch'),
    (12, 'lblSelect'),
    (38, 'lblSignIn'),
    (39, 'lblSignUp'),
    (13, 'lblSocialNerwork'),
    (14, 'lblZip'),
    (33, 'navAbout'),
    (37, 'navContacts'),
    (34, 'navDashboard'),
    (67, 'navDeliverables'),
    (36, 'navEvents'),
    (32, 'navFAQs'),
    (30, 'navFeatures'),
    (47, 'navGroups'),
    (29, 'navHome'),
    (35, 'navOrganizations'),
    (48, 'navPosts'),
    (31, 'navPricing'),
    (40, 'navSignOut'),
    (88, 'navVisitors'),
    (80, 'nteCreateError'),
    (79, 'nteCreateSuccess'),
    (78, 'nteDeleteError'),
    (77, 'nteDeleteSuccess'),
    (76, 'nteDeleteWarn'),
    (82, 'nteUpdateError'),
    (81, 'nteUpdateSuccess'),
    (45, 'ntyAproved'),
    (46, 'ntyDenied'),
    (23, 'socFacebook'),
    (26, 'socInstagram'),
    (27, 'socLinkedin'),
    (22, 'socSkype'),
    (24, 'socTwitter'),
    (25, 'socWebPage'),
    (28, 'socWhatsApp'),
    (41, 'tblActions'),
    (89, 'tblCompany'),
    (90, 'tblEmail'),
    (52, 'tblEnabled'),
    (55, 'tblEnd'),
    (92, 'tblGroup'),
    (83, 'tblLink'),
    (42, 'tblName'),
    (56, 'tblOwner'),
    (91, 'tblPhone'),
    (87, 'tblQuantity'),
    (54, 'tblStart'),
    (53, 'tblStatus');

/*!40000 ALTER TABLE `labels` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `languages`
--
DROP TABLE IF EXISTS `languages`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `languages` (
        `id` VARCHAR(3) COLLATE utf8mb4_unicode_ci NOT NULL,
        `name` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        `name_en` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        `flag` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `enabled` tinyint (1) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--
LOCK TABLES `languages` WRITE;

/*!40000 ALTER TABLE `languages` DISABLE KEYS */;

INSERT INTO
    `languages`
VALUES
    ('af', 'Afrikaans', 'Afrikaans', NULL, 0),
    ('agq', 'Aghem', 'Aghem', NULL, 0),
    ('ak', 'Akan', 'Akan', NULL, 0),
    ('am', 'አማርኛ', 'Amharic', NULL, 0),
    ('ar', 'العربية', 'Arabic', NULL, 0),
    ('as', 'অসমীয়া', 'Assamese', NULL, 0),
    ('asa', 'Kipare', 'Asu', NULL, 0),
    ('ast', 'asturianu', 'Asturian', NULL, 0),
    ('az', 'azərbaycan', 'Azerbaijani', NULL, 0),
    ('bas', 'Ɓàsàa', 'Basaa', NULL, 0),
    ('be', 'беларуская', 'Belarusian', NULL, 0),
    ('bem', 'Ichibemba', 'Bemba', NULL, 0),
    ('bez', 'Hibena', 'Bena', NULL, 0),
    ('bg', 'български', 'Bulgarian', NULL, 0),
    ('bm', 'bamanakan', 'Bambara', NULL, 0),
    ('bn', 'বাংলা', 'Bangla', NULL, 0),
    ('bo', 'བོད་སྐད་', 'Tibetan', NULL, 0),
    ('br', 'brezhoneg', 'Breton', NULL, 0),
    ('brx', 'बड़ो', 'Bodo', NULL, 0),
    ('bs', 'bosanski', 'Bosnian', NULL, 0),
    ('ca', 'català', 'Catalan', NULL, 0),
    (
        'ccp',
        '????????????????????????',
        'Chakma',
        NULL,
        0
    ),
    ('ce', 'нохчийн', 'Chechen', NULL, 0),
    ('ceb', 'Binisaya', 'Cebuano', NULL, 0),
    ('cgg', 'Rukiga', 'Chiga', NULL, 0),
    ('chr', 'ᏣᎳᎩ', 'Cherokee', NULL, 0),
    (
        'ckb',
        'کوردیی ناوەندی',
        'Central Kurdish',
        NULL,
        0
    ),
    ('cs', 'čeština', 'Czech', NULL, 0),
    ('cy', 'Cymraeg', 'Welsh', NULL, 0),
    ('da', 'dansk', 'Danish', NULL, 0),
    ('dav', 'Kitaita', 'Taita', NULL, 0),
    ('de', 'Deutsch', 'German', NULL, 0),
    ('dje', 'Zarmaciine', 'Zarma', NULL, 0),
    ('doi', 'डोगरी', 'Dogri', NULL, 0),
    ('dsb', 'dolnoserbšćina', 'Lower Sorbian', NULL, 0),
    ('dua', 'duálá', 'Duala', NULL, 0),
    ('dyo', 'joola', 'Jola-Fonyi', NULL, 0),
    ('dz', 'རྫོང་ཁ', 'Dzongkha', NULL, 0),
    ('ebu', 'Kĩembu', 'Embu', NULL, 0),
    ('ee', 'Eʋegbe', 'Ewe', NULL, 0),
    ('el', 'Ελληνικά', 'Greek', NULL, 0),
    (
        'en',
        'English',
        'English',
        '/assets/images/flags/United-Kingdom.png',
        1
    ),
    ('eo', 'esperanto', 'Esperanto', NULL, 0),
    (
        'es',
        'español',
        'Spanish',
        '/assets/images/flags/spanish.png',
        1
    ),
    ('et', 'eesti', 'Estonian', NULL, 0),
    ('eu', 'euskara', 'Basque', NULL, 0),
    ('ewo', 'ewondo', 'Ewondo', NULL, 0),
    ('fa', 'فارسی', 'Persian', NULL, 0),
    ('ff', 'Pulaar', 'Fulah', NULL, 0),
    ('fi', 'suomi', 'Finnish', NULL, 0),
    ('fil', 'Filipino', 'Filipino', NULL, 0),
    ('fo', 'føroyskt', 'Faroese', NULL, 0),
    ('fr', 'français', 'French', NULL, 0),
    ('fur', 'furlan', 'Friulian', NULL, 0),
    ('fy', 'Frysk', 'Western Frisian', NULL, 0),
    ('ga', 'Gaeilge', 'Irish', NULL, 0),
    ('gd', 'Gàidhlig', 'Scottish Gaelic', NULL, 0),
    ('gl', 'galego', 'Galician', NULL, 0),
    (
        'gsw',
        'Schwiizertüütsch',
        'Swiss German',
        NULL,
        0
    ),
    ('gu', 'ગુજરાતી', 'Gujarati', NULL, 0),
    ('guz', 'Ekegusii', 'Gusii', NULL, 0),
    ('gv', 'Gaelg', 'Manx', NULL, 0),
    ('ha', 'Hausa', 'Hausa', NULL, 0),
    ('haw', 'ʻŌlelo Hawaiʻi', 'Hawaiian', NULL, 0),
    ('he', 'עברית', 'Hebrew', NULL, 0),
    ('hi', 'हिन्दी', 'Hindi', NULL, 0),
    ('hr', 'hrvatski', 'Croatian', NULL, 0),
    (
        'hsb',
        'hornjoserbšćina',
        'Upper Sorbian',
        NULL,
        0
    ),
    ('hu', 'magyar', 'Hungarian', NULL, 0),
    ('hy', 'հայերեն', 'Armenian', NULL, 0),
    ('ia', 'interlingua', 'Interlingua', NULL, 0),
    ('id', 'Indonesia', 'Indonesian', NULL, 0),
    ('ig', 'Igbo', 'Igbo', NULL, 0),
    ('ii', 'ꆈꌠꉙ', 'Sichuan Yi', NULL, 0),
    ('is', 'íslenska', 'Icelandic', NULL, 0),
    ('it', 'italiano', 'Italian', NULL, 0),
    ('ja', '日本語', 'Japanese', NULL, 0),
    ('jgo', 'Ndaꞌa', 'Ngomba', NULL, 0),
    ('jmc', 'Kimachame', 'Machame', NULL, 0),
    ('jv', 'Jawa', 'Javanese', NULL, 0),
    ('ka', 'ქართული', 'Georgian', NULL, 0),
    ('kab', 'Taqbaylit', 'Kabyle', NULL, 0),
    ('kam', 'Kikamba', 'Kamba', NULL, 0),
    ('kde', 'Chimakonde', 'Makonde', NULL, 0),
    ('kea', 'kabuverdianu', 'Kabuverdianu', NULL, 0),
    ('khq', 'Koyra ciini', 'Koyra Chiini', NULL, 0),
    ('ki', 'Gikuyu', 'Kikuyu', NULL, 0),
    ('kk', 'қазақ тілі', 'Kazakh', NULL, 0),
    ('kkj', 'kakɔ', 'Kako', NULL, 0),
    ('kl', 'kalaallisut', 'Kalaallisut', NULL, 0),
    ('kln', 'Kalenjin', 'Kalenjin', NULL, 0),
    ('km', 'ខ្មែរ', 'Khmer', NULL, 0),
    ('kn', 'ಕನ್ನಡ', 'Kannada', NULL, 0),
    ('ko', '한국어', 'Korean', NULL, 0),
    ('kok', 'कोंकणी', 'Konkani', NULL, 0),
    ('ks', 'کٲشُر', 'Kashmiri', NULL, 0),
    ('ksb', 'Kishambaa', 'Shambala', NULL, 0),
    ('ksf', 'rikpa', 'Bafia', NULL, 0),
    ('ksh', 'Kölsch', 'Colognian', NULL, 0),
    ('ku', 'kurdî', 'Kurdish', NULL, 0),
    ('kw', 'kernewek', 'Cornish', NULL, 0),
    ('ky', 'кыргызча', 'Kyrgyz', NULL, 0),
    ('lag', 'Kɨlaangi', 'Langi', NULL, 0),
    ('lb', 'Lëtzebuergesch', 'Luxembourgish', NULL, 0),
    ('lg', 'Luganda', 'Ganda', NULL, 0),
    ('lkt', 'Lakȟólʼiyapi', 'Lakota', NULL, 0),
    ('ln', 'lingála', 'Lingala', NULL, 0),
    ('lo', 'ລາວ', 'Lao', NULL, 0),
    ('lrc', 'لۊری شومالی', 'Northern Luri', NULL, 0),
    ('lt', 'lietuvių', 'Lithuanian', NULL, 0),
    ('lu', 'Tshiluba', 'Luba-Katanga', NULL, 0),
    ('luo', 'Dholuo', 'Luo', NULL, 0),
    ('luy', 'Luluhia', 'Luyia', NULL, 0),
    ('lv', 'latviešu', 'Latvian', NULL, 0),
    ('mai', 'मैथिली', 'Maithili', NULL, 0),
    ('mas', 'Maa', 'Masai', NULL, 0),
    ('mer', 'Kĩmĩrũ', 'Meru', NULL, 0),
    ('mfe', 'kreol morisien', 'Morisyen', NULL, 0),
    ('mg', 'Malagasy', 'Malagasy', NULL, 0),
    ('mgh', 'Makua', 'Makhuwa-Meetto', NULL, 0),
    ('mgo', 'metaʼ', 'Metaʼ', NULL, 0),
    ('mi', 'te reo Māori', 'Maori', NULL, 0),
    ('mk', 'македонски', 'Macedonian', NULL, 0),
    ('ml', 'മലയാളം', 'Malayalam', NULL, 0),
    ('mn', 'монгол', 'Mongolian', NULL, 0),
    ('mni', 'মৈতৈলোন্', 'Manipuri', NULL, 0),
    ('mr', 'मराठी', 'Marathi', NULL, 0),
    ('ms', 'Melayu', 'Malay', NULL, 0),
    ('mt', 'Malti', 'Maltese', NULL, 0),
    ('mua', 'MUNDAŊ', 'Mundang', NULL, 0),
    ('my', 'မြန်မာ', 'Burmese', NULL, 0),
    ('mzn', 'مازرونی', 'Mazanderani', NULL, 0),
    ('naq', 'Khoekhoegowab', 'Nama', NULL, 0),
    ('nb', 'norsk bokmål', 'Norwegian Bokmål', NULL, 0),
    ('nd', 'isiNdebele', 'North Ndebele', NULL, 0),
    ('ne', 'नेपाली', 'Nepali', NULL, 0),
    ('nl', 'Nederlands', 'Dutch', NULL, 0),
    ('nmg', 'nmg', 'Kwasio', NULL, 0),
    (
        'nn',
        'norsk nynorsk',
        'Norwegian Nynorsk',
        NULL,
        0
    ),
    ('nnh', 'Shwóŋò ngiembɔɔn', 'Ngiemboon', NULL, 0),
    ('no', 'norsk', 'Norwegian', NULL, 0),
    ('nus', 'Thok Nath', 'Nuer', NULL, 0),
    ('nyn', 'Runyankore', 'Nyankole', NULL, 0),
    ('om', 'Oromoo', 'Oromo', NULL, 0),
    ('or', 'ଓଡ଼ିଆ', 'Odia', NULL, 0),
    ('os', 'ирон', 'Ossetic', NULL, 0),
    ('pa', 'ਪੰਜਾਬੀ', 'Punjabi', NULL, 0),
    (
        'pcm',
        'Naijíriá Píjin',
        'Nigerian Pidgin',
        NULL,
        0
    ),
    ('pl', 'polski', 'Polish', NULL, 0),
    ('ps', 'پښتو', 'Pashto', NULL, 0),
    ('pt', 'português', 'Portuguese', NULL, 0),
    ('qu', 'Runasimi', 'Quechua', NULL, 0),
    ('rm', 'rumantsch', 'Romansh', NULL, 0),
    ('rn', 'Ikirundi', 'Rundi', NULL, 0),
    ('ro', 'română', 'Romanian', NULL, 0),
    ('rof', 'Kihorombo', 'Rombo', NULL, 0),
    ('ru', 'русский', 'Russian', NULL, 0),
    ('rw', 'Kinyarwanda', 'Kinyarwanda', NULL, 0),
    ('rwk', 'Kiruwa', 'Rwa', NULL, 0),
    ('sa', 'संस्कृत भाषा', 'Sanskrit', NULL, 0),
    ('sah', 'саха тыла', 'Sakha', NULL, 0),
    ('saq', 'Kisampur', 'Samburu', NULL, 0),
    ('sat', 'ᱥᱟᱱᱛᱟᱲᱤ', 'Santali', NULL, 0),
    ('sbp', 'Ishisangu', 'Sangu', NULL, 0),
    ('sd', 'سنڌي', 'Sindhi', NULL, 0),
    ('se', 'davvisámegiella', 'Northern Sami', NULL, 0),
    ('seh', 'sena', 'Sena', NULL, 0),
    (
        'ses',
        'Koyraboro senni',
        'Koyraboro Senni',
        NULL,
        0
    ),
    ('sg', 'Sängö', 'Sango', NULL, 0),
    ('shi', 'ⵜⴰⵛⵍⵃⵉⵜ', 'Tachelhit', NULL, 0),
    ('si', 'සිංහල', 'Sinhala', NULL, 0),
    ('sk', 'slovenčina', 'Slovak', NULL, 0),
    ('sl', 'slovenščina', 'Slovenian', NULL, 0),
    ('smn', 'anarâškielâ', 'Inari Sami', NULL, 0),
    ('sn', 'chiShona', 'Shona', NULL, 0),
    ('so', 'Soomaali', 'Somali', NULL, 0),
    ('sq', 'shqip', 'Albanian', NULL, 0),
    ('sr', 'српски', 'Serbian', NULL, 0),
    ('su', 'Basa Sunda', 'Sundanese', NULL, 0),
    ('sv', 'svenska', 'Swedish', NULL, 0),
    ('sw', 'Kiswahili', 'Swahili', NULL, 0),
    ('ta', 'தமிழ்', 'Tamil', NULL, 0),
    ('te', 'తెలుగు', 'Telugu', NULL, 0),
    ('teo', 'Kiteso', 'Teso', NULL, 0),
    ('tg', 'тоҷикӣ', 'Tajik', NULL, 0),
    ('th', 'ไทย', 'Thai', NULL, 0),
    ('ti', 'ትግር', 'Tigrinya', NULL, 0),
    ('tk', 'türkmen dili', 'Turkmen', NULL, 0),
    ('to', 'lea fakatonga', 'Tongan', NULL, 0),
    ('tr', 'Türkçe', 'Turkish', NULL, 0),
    ('tt', 'татар', 'Tatar', NULL, 0),
    ('twq', 'Tasawaq senni', 'Tasawaq', NULL, 0),
    (
        'tzm',
        'Tamaziɣt n laṭlaṣ',
        'Central Atlas Tamazight',
        NULL,
        0
    ),
    ('ug', 'ئۇيغۇرچە', 'Uyghur', NULL, 0),
    ('uk', 'українська', 'Ukrainian', NULL, 0),
    ('ur', 'اردو', 'Urdu', NULL, 0),
    ('uz', 'o‘zbek', 'Uzbek', NULL, 0),
    ('vai', 'ꕙꔤ', 'Vai', NULL, 0),
    ('vi', 'Tiếng Việt', 'Vietnamese', NULL, 0),
    ('vun', 'Kyivunjo', 'Vunjo', NULL, 0),
    ('wae', 'Walser', 'Walser', NULL, 0),
    ('wo', 'Wolof', 'Wolof', NULL, 0),
    ('xh', 'isiXhosa', 'Xhosa', NULL, 0),
    ('xog', 'Olusoga', 'Soga', NULL, 0),
    ('yav', 'nuasue', 'Yangben', NULL, 0),
    ('yi', 'ייִדיש', 'Yiddish', NULL, 0),
    ('yo', 'Èdè Yorùbá', 'Yoruba', NULL, 0),
    ('yue', '粵語', 'Cantonese', NULL, 0),
    (
        'zgh',
        'ⵜⴰⵎⴰⵣⵉⵖⵜ',
        'Standard Moroccan Tamazight',
        NULL,
        0
    ),
    ('zh', '中文', 'Chinese', NULL, 0),
    ('zu', 'isiZulu', 'Zulu', NULL, 0);

/*!40000 ALTER TABLE `languages` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `locales`
--
DROP TABLE IF EXISTS `locales`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `locales` (
        `id` VARCHAR(15) COLLATE utf8mb4_unicode_ci NOT NULL,
        `language_id` VARCHAR(3) COLLATE utf8mb4_unicode_ci NOT NULL,
        `name` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        `name_en` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        `flag` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `enabled` tinyint (1) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        KEY `language_id` (`language_id`),
        CONSTRAINT `locales_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `locales`
--
LOCK TABLES `locales` WRITE;

/*!40000 ALTER TABLE `locales` DISABLE KEYS */;

INSERT INTO
    `locales`
VALUES
    ('af', 'af', 'Afrikaans', 'Afrikaans', NULL, 0),
    (
        'af_NA',
        'af',
        'Afrikaans (Namibië)',
        'Afrikaans (Namibia)',
        NULL,
        0
    ),
    (
        'af_ZA',
        'af',
        'Afrikaans (Suid-Afrika)',
        'Afrikaans (South Africa)',
        NULL,
        0
    ),
    ('agq', 'agq', 'Aghem', 'Aghem', NULL, 0),
    (
        'agq_CM',
        'agq',
        'Aghem (Kàmàlûŋ)',
        'Aghem (Cameroon)',
        NULL,
        0
    ),
    ('ak', 'ak', 'Akan', 'Akan', NULL, 0),
    (
        'ak_GH',
        'ak',
        'Akan (Gaana)',
        'Akan (Ghana)',
        NULL,
        0
    ),
    ('am', 'am', 'አማርኛ', 'Amharic', NULL, 0),
    (
        'am_ET',
        'am',
        'አማርኛ (ኢትዮጵያ)',
        'Amharic (Ethiopia)',
        NULL,
        0
    ),
    ('ar', 'ar', 'العربية', 'Arabic', NULL, 0),
    (
        'ar_001',
        'ar',
        'العربية (العالم)',
        'Arabic (world)',
        NULL,
        0
    ),
    (
        'ar_AE',
        'ar',
        'العربية (الإمارات العربية المتحدة)',
        'Arabic (United Arab Emirates)',
        NULL,
        0
    ),
    (
        'ar_BH',
        'ar',
        'العربية (البحرين)',
        'Arabic (Bahrain)',
        NULL,
        0
    ),
    (
        'ar_DJ',
        'ar',
        'العربية (جيبوتي)',
        'Arabic (Djibouti)',
        NULL,
        0
    ),
    (
        'ar_DZ',
        'ar',
        'العربية (الجزائر)',
        'Arabic (Algeria)',
        NULL,
        0
    ),
    (
        'ar_EG',
        'ar',
        'العربية (مصر)',
        'Arabic (Egypt)',
        NULL,
        0
    ),
    (
        'ar_EH',
        'ar',
        'العربية (الصحراء الغربية)',
        'Arabic (Western Sahara)',
        NULL,
        0
    ),
    (
        'ar_ER',
        'ar',
        'العربية (إريتريا)',
        'Arabic (Eritrea)',
        NULL,
        0
    ),
    (
        'ar_IL',
        'ar',
        'العربية (إسرائيل)',
        'Arabic (Israel)',
        NULL,
        0
    ),
    (
        'ar_IQ',
        'ar',
        'العربية (العراق)',
        'Arabic (Iraq)',
        NULL,
        0
    ),
    (
        'ar_JO',
        'ar',
        'العربية (الأردن)',
        'Arabic (Jordan)',
        NULL,
        0
    ),
    (
        'ar_KM',
        'ar',
        'العربية (جزر القمر)',
        'Arabic (Comoros)',
        NULL,
        0
    ),
    (
        'ar_KW',
        'ar',
        'العربية (الكويت)',
        'Arabic (Kuwait)',
        NULL,
        0
    ),
    (
        'ar_LB',
        'ar',
        'العربية (لبنان)',
        'Arabic (Lebanon)',
        NULL,
        0
    ),
    (
        'ar_LY',
        'ar',
        'العربية (ليبيا)',
        'Arabic (Libya)',
        NULL,
        0
    ),
    (
        'ar_MA',
        'ar',
        'العربية (المغرب)',
        'Arabic (Morocco)',
        NULL,
        0
    ),
    (
        'ar_MR',
        'ar',
        'العربية (موريتانيا)',
        'Arabic (Mauritania)',
        NULL,
        0
    ),
    (
        'ar_OM',
        'ar',
        'العربية (عُمان)',
        'Arabic (Oman)',
        NULL,
        0
    ),
    (
        'ar_PS',
        'ar',
        'العربية (الأراضي الفلسطينية)',
        'Arabic (Palestinian Territories)',
        NULL,
        0
    ),
    (
        'ar_QA',
        'ar',
        'العربية (قطر)',
        'Arabic (Qatar)',
        NULL,
        0
    ),
    (
        'ar_SA',
        'ar',
        'العربية (المملكة العربية السعودية)',
        'Arabic (Saudi Arabia)',
        NULL,
        0
    ),
    (
        'ar_SD',
        'ar',
        'العربية (السودان)',
        'Arabic (Sudan)',
        NULL,
        0
    ),
    (
        'ar_SO',
        'ar',
        'العربية (الصومال)',
        'Arabic (Somalia)',
        NULL,
        0
    ),
    (
        'ar_SS',
        'ar',
        'العربية (جنوب السودان)',
        'Arabic (South Sudan)',
        NULL,
        0
    ),
    (
        'ar_SY',
        'ar',
        'العربية (سوريا)',
        'Arabic (Syria)',
        NULL,
        0
    ),
    (
        'ar_TD',
        'ar',
        'العربية (تشاد)',
        'Arabic (Chad)',
        NULL,
        0
    ),
    (
        'ar_TN',
        'ar',
        'العربية (تونس)',
        'Arabic (Tunisia)',
        NULL,
        0
    ),
    (
        'ar_YE',
        'ar',
        'العربية (اليمن)',
        'Arabic (Yemen)',
        NULL,
        0
    ),
    ('as', 'as', 'অসমীয়া', 'Assamese', NULL, 0),
    (
        'as_IN',
        'as',
        'অসমীয়া (ভাৰত)',
        'Assamese (India)',
        NULL,
        0
    ),
    ('asa', 'asa', 'Kipare', 'Asu', NULL, 0),
    (
        'asa_TZ',
        'asa',
        'Kipare (Tadhania)',
        'Asu (Tanzania)',
        NULL,
        0
    ),
    ('ast', 'ast', 'asturianu', 'Asturian', NULL, 0),
    (
        'ast_ES',
        'ast',
        'asturianu (España)',
        'Asturian (Spain)',
        NULL,
        0
    ),
    ('az', 'az', 'azərbaycan', 'Azerbaijani', NULL, 0),
    (
        'az_Cyrl',
        'az',
        'азәрбајҹан (Кирил)',
        'Azerbaijani (Cyrillic)',
        NULL,
        0
    ),
    (
        'az_Cyrl_AZ',
        'az',
        'азәрбајҹан (Кирил, Азәрбајҹан)',
        'Azerbaijani (Cyrillic, Azerbaijan)',
        NULL,
        0
    ),
    (
        'az_Latn',
        'az',
        'azərbaycan (latın)',
        'Azerbaijani (Latin)',
        NULL,
        0
    ),
    (
        'az_Latn_AZ',
        'az',
        'azərbaycan (latın, Azərbaycan)',
        'Azerbaijani (Latin, Azerbaijan)',
        NULL,
        0
    ),
    ('bas', 'bas', 'Ɓàsàa', 'Basaa', NULL, 0),
    (
        'bas_CM',
        'bas',
        'Ɓàsàa (Kàmɛ̀rûn)',
        'Basaa (Cameroon)',
        NULL,
        0
    ),
    ('be', 'be', 'беларуская', 'Belarusian', NULL, 0),
    (
        'be_BY',
        'be',
        'беларуская (Беларусь)',
        'Belarusian (Belarus)',
        NULL,
        0
    ),
    ('bem', 'bem', 'Ichibemba', 'Bemba', NULL, 0),
    (
        'bem_ZM',
        'bem',
        'Ichibemba (Zambia)',
        'Bemba (Zambia)',
        NULL,
        0
    ),
    ('bez', 'bez', 'Hibena', 'Bena', NULL, 0),
    (
        'bez_TZ',
        'bez',
        'Hibena (Hutanzania)',
        'Bena (Tanzania)',
        NULL,
        0
    ),
    ('bg', 'bg', 'български', 'Bulgarian', NULL, 0),
    (
        'bg_BG',
        'bg',
        'български (България)',
        'Bulgarian (Bulgaria)',
        NULL,
        0
    ),
    ('bm', 'bm', 'bamanakan', 'Bambara', NULL, 0),
    (
        'bm_ML',
        'bm',
        'bamanakan (Mali)',
        'Bambara (Mali)',
        NULL,
        0
    ),
    ('bn', 'bn', 'বাংলা', 'Bangla', NULL, 0),
    (
        'bn_BD',
        'bn',
        'বাংলা (বাংলাদেশ)',
        'Bangla (Bangladesh)',
        NULL,
        0
    ),
    (
        'bn_IN',
        'bn',
        'বাংলা (ভারত)',
        'Bangla (India)',
        NULL,
        0
    ),
    ('bo', 'bo', 'བོད་སྐད་', 'Tibetan', NULL, 0),
    (
        'bo_CN',
        'bo',
        'བོད་སྐད་ (རྒྱ་ནག)',
        'Tibetan (China)',
        NULL,
        0
    ),
    (
        'bo_IN',
        'bo',
        'བོད་སྐད་ (རྒྱ་གར་)',
        'Tibetan (India)',
        NULL,
        0
    ),
    ('br', 'br', 'brezhoneg', 'Breton', NULL, 0),
    (
        'br_FR',
        'br',
        'brezhoneg (Frañs)',
        'Breton (France)',
        NULL,
        0
    ),
    ('brx', 'brx', 'बड़ो', 'Bodo', NULL, 0),
    (
        'brx_IN',
        'brx',
        'बड़ो (भारत)',
        'Bodo (India)',
        NULL,
        0
    ),
    ('bs', 'bs', 'bosanski', 'Bosnian', NULL, 0),
    (
        'bs_Cyrl',
        'bs',
        'босански (ћирилица)',
        'Bosnian (Cyrillic)',
        NULL,
        0
    ),
    (
        'bs_Cyrl_BA',
        'bs',
        'босански (ћирилица, Босна и Херцеговина)',
        'Bosnian (Cyrillic, Bosnia & Herzegovina)',
        NULL,
        0
    ),
    (
        'bs_Latn',
        'bs',
        'bosanski (latinica)',
        'Bosnian (Latin)',
        NULL,
        0
    ),
    (
        'bs_Latn_BA',
        'bs',
        'bosanski (latinica, Bosna i Hercegovina)',
        'Bosnian (Latin, Bosnia & Herzegovina)',
        NULL,
        0
    ),
    ('ca', 'ca', 'català', 'Catalan', NULL, 0),
    (
        'ca_AD',
        'ca',
        'català (Andorra)',
        'Catalan (Andorra)',
        NULL,
        0
    ),
    (
        'ca_ES',
        'ca',
        'català (Espanya)',
        'Catalan (Spain)',
        NULL,
        0
    ),
    (
        'ca_FR',
        'ca',
        'català (França)',
        'Catalan (France)',
        NULL,
        0
    ),
    (
        'ca_IT',
        'ca',
        'català (Itàlia)',
        'Catalan (Italy)',
        NULL,
        0
    ),
    (
        'ccp',
        'ccp',
        '????????????????????????',
        'Chakma',
        NULL,
        0
    ),
    (
        'ccp_BD',
        'ccp',
        '???????????????????????? (????????????????????????',
        'Chakma (Bangladesh)',
        NULL,
        0
    ),
    (
        'ccp_IN',
        'ccp',
        '???????????????????????? (????????????????????)',
        'Chakma (India)',
        NULL,
        0
    ),
    ('ce', 'ce', 'нохчийн', 'Chechen', NULL, 0),
    (
        'ce_RU',
        'ce',
        'нохчийн (Росси)',
        'Chechen (Russia)',
        NULL,
        0
    ),
    ('ceb', 'ceb', 'Binisaya', 'Cebuano', NULL, 0),
    (
        'ceb_PH',
        'ceb',
        'Binisaya (Pilipinas)',
        'Cebuano (Philippines)',
        NULL,
        0
    ),
    ('cgg', 'cgg', 'Rukiga', 'Chiga', NULL, 0),
    (
        'cgg_UG',
        'cgg',
        'Rukiga (Uganda)',
        'Chiga (Uganda)',
        NULL,
        0
    ),
    ('chr', 'chr', 'ᏣᎳᎩ', 'Cherokee', NULL, 0),
    (
        'chr_US',
        'chr',
        'ᏣᎳᎩ (ᏌᏊ ᎢᏳᎾᎵᏍᏔᏅ ᏍᎦᏚᎩ)',
        'Cherokee (United States)',
        NULL,
        0
    ),
    (
        'ckb',
        'ckb',
        'کوردیی ناوەندی',
        'Central Kurdish',
        NULL,
        0
    ),
    (
        'ckb_IQ',
        'ckb',
        'کوردیی ناوەندی (عێراق)',
        'Central Kurdish (Iraq)',
        NULL,
        0
    ),
    (
        'ckb_IR',
        'ckb',
        'کوردیی ناوەندی (ئێران)',
        'Central Kurdish (Iran)',
        NULL,
        0
    ),
    ('cs', 'cs', 'čeština', 'Czech', NULL, 0),
    (
        'cs_CZ',
        'cs',
        'čeština (Česko)',
        'Czech (Czechia)',
        NULL,
        0
    ),
    ('cy', 'cy', 'Cymraeg', 'Welsh', NULL, 0),
    (
        'cy_GB',
        'cy',
        'Cymraeg (Y Deyrnas Unedig)',
        'Welsh (United Kingdom)',
        NULL,
        0
    ),
    ('da', 'da', 'dansk', 'Danish', NULL, 0),
    (
        'da_DK',
        'da',
        'dansk (Danmark)',
        'Danish (Denmark)',
        NULL,
        0
    ),
    (
        'da_GL',
        'da',
        'dansk (Grønland)',
        'Danish (Greenland)',
        NULL,
        0
    ),
    ('dav', 'dav', 'Kitaita', 'Taita', NULL, 0),
    (
        'dav_KE',
        'dav',
        'Kitaita (Kenya)',
        'Taita (Kenya)',
        NULL,
        0
    ),
    ('de', 'de', 'Deutsch', 'German', NULL, 1),
    (
        'de_AT',
        'de',
        'Deutsch (Österreich)',
        'German (Austria)',
        NULL,
        1
    ),
    (
        'de_BE',
        'de',
        'Deutsch (Belgien)',
        'German (Belgium)',
        NULL,
        0
    ),
    (
        'de_CH',
        'de',
        'Deutsch (Schweiz)',
        'German (Switzerland)',
        NULL,
        1
    ),
    (
        'de_DE',
        'de',
        'Deutsch (Deutschland)',
        'German (Germany)',
        NULL,
        1
    ),
    (
        'de_IT',
        'de',
        'Deutsch (Italien)',
        'German (Italy)',
        NULL,
        0
    ),
    (
        'de_LI',
        'de',
        'Deutsch (Liechtenstein)',
        'German (Liechtenstein)',
        NULL,
        0
    ),
    (
        'de_LU',
        'de',
        'Deutsch (Luxemburg)',
        'German (Luxembourg)',
        NULL,
        0
    ),
    ('dje', 'dje', 'Zarmaciine', 'Zarma', NULL, 0),
    (
        'dje_NE',
        'dje',
        'Zarmaciine (Nižer)',
        'Zarma (Niger)',
        NULL,
        0
    ),
    ('doi', 'doi', 'डोगरी', 'Dogri', NULL, 0),
    (
        'doi_IN',
        'doi',
        'डोगरी (भारत)',
        'Dogri (India)',
        NULL,
        0
    ),
    (
        'dsb',
        'dsb',
        'dolnoserbšćina',
        'Lower Sorbian',
        NULL,
        0
    ),
    (
        'dsb_DE',
        'dsb',
        'dolnoserbšćina (Nimska)',
        'Lower Sorbian (Germany)',
        NULL,
        0
    ),
    ('dua', 'dua', 'duálá', 'Duala', NULL, 0),
    (
        'dua_CM',
        'dua',
        'duálá (Cameroun)',
        'Duala (Cameroon)',
        NULL,
        0
    ),
    ('dyo', 'dyo', 'joola', 'Jola-Fonyi', NULL, 0),
    (
        'dyo_SN',
        'dyo',
        'joola (Senegal)',
        'Jola-Fonyi (Senegal)',
        NULL,
        0
    ),
    ('dz', 'dz', 'རྫོང་ཁ', 'Dzongkha', NULL, 0),
    (
        'dz_BT',
        'dz',
        'རྫོང་ཁ། (འབྲུག།)',
        'Dzongkha (Bhutan)',
        NULL,
        0
    ),
    ('ebu', 'ebu', 'Kĩembu', 'Embu', NULL, 0),
    (
        'ebu_KE',
        'ebu',
        'Kĩembu (Kenya)',
        'Embu (Kenya)',
        NULL,
        0
    ),
    ('ee', 'ee', 'Eʋegbe', 'Ewe', NULL, 0),
    (
        'ee_GH',
        'ee',
        'Eʋegbe (Ghana nutome)',
        'Ewe (Ghana)',
        NULL,
        0
    ),
    (
        'ee_TG',
        'ee',
        'Eʋegbe (Togo nutome)',
        'Ewe (Togo)',
        NULL,
        0
    ),
    ('el', 'el', 'Ελληνικά', 'Greek', NULL, 0),
    (
        'el_CY',
        'el',
        'Ελληνικά (Κύπρος)',
        'Greek (Cyprus)',
        NULL,
        0
    ),
    (
        'el_GR',
        'el',
        'Ελληνικά (Ελλάδα)',
        'Greek (Greece)',
        NULL,
        0
    ),
    ('en', 'en', 'English', 'English', NULL, 1),
    (
        'en_001',
        'en',
        'English (world)',
        'English (world)',
        NULL,
        0
    ),
    (
        'en_150',
        'en',
        'English (Europe)',
        'English (Europe)',
        NULL,
        0
    ),
    (
        'en_AE',
        'en',
        'English (United Arab Emirates)',
        'English (United Arab Emirates)',
        NULL,
        0
    ),
    (
        'en_AG',
        'en',
        'English (Antigua & Barbuda)',
        'English (Antigua & Barbuda)',
        NULL,
        0
    ),
    (
        'en_AI',
        'en',
        'English (Anguilla)',
        'English (Anguilla)',
        NULL,
        0
    ),
    (
        'en_AS',
        'en',
        'English (American Samoa)',
        'English (American Samoa)',
        NULL,
        0
    ),
    (
        'en_AT',
        'en',
        'English (Austria)',
        'English (Austria)',
        NULL,
        0
    ),
    (
        'en_AU',
        'en',
        'English (Australia)',
        'English (Australia)',
        NULL,
        1
    ),
    (
        'en_BB',
        'en',
        'English (Barbados)',
        'English (Barbados)',
        NULL,
        0
    ),
    (
        'en_BE',
        'en',
        'English (Belgium)',
        'English (Belgium)',
        NULL,
        0
    ),
    (
        'en_BI',
        'en',
        'English (Burundi)',
        'English (Burundi)',
        NULL,
        0
    ),
    (
        'en_BM',
        'en',
        'English (Bermuda)',
        'English (Bermuda)',
        NULL,
        0
    ),
    (
        'en_BS',
        'en',
        'English (Bahamas)',
        'English (Bahamas)',
        NULL,
        0
    ),
    (
        'en_BW',
        'en',
        'English (Botswana)',
        'English (Botswana)',
        NULL,
        0
    ),
    (
        'en_BZ',
        'en',
        'English (Belize)',
        'English (Belize)',
        NULL,
        0
    ),
    (
        'en_CA',
        'en',
        'English (Canada)',
        'English (Canada)',
        NULL,
        0
    ),
    (
        'en_CC',
        'en',
        'English (Cocos [Keeling] Islands)',
        'English (Cocos [Keeling] Islands)',
        NULL,
        0
    ),
    (
        'en_CH',
        'en',
        'English (Switzerland)',
        'English (Switzerland)',
        NULL,
        0
    ),
    (
        'en_CK',
        'en',
        'English (Cook Islands)',
        'English (Cook Islands)',
        NULL,
        0
    ),
    (
        'en_CM',
        'en',
        'English (Cameroon)',
        'English (Cameroon)',
        NULL,
        0
    ),
    (
        'en_CX',
        'en',
        'English (Christmas Island)',
        'English (Christmas Island)',
        NULL,
        0
    ),
    (
        'en_CY',
        'en',
        'English (Cyprus)',
        'English (Cyprus)',
        NULL,
        0
    ),
    (
        'en_DE',
        'en',
        'English (Germany)',
        'English (Germany)',
        NULL,
        0
    ),
    (
        'en_DG',
        'en',
        'English (Diego Garcia)',
        'English (Diego Garcia)',
        NULL,
        0
    ),
    (
        'en_DK',
        'en',
        'English (Denmark)',
        'English (Denmark)',
        NULL,
        0
    ),
    (
        'en_DM',
        'en',
        'English (Dominica)',
        'English (Dominica)',
        NULL,
        0
    ),
    (
        'en_ER',
        'en',
        'English (Eritrea)',
        'English (Eritrea)',
        NULL,
        0
    ),
    (
        'en_FI',
        'en',
        'English (Finland)',
        'English (Finland)',
        NULL,
        0
    ),
    (
        'en_FJ',
        'en',
        'English (Fiji)',
        'English (Fiji)',
        NULL,
        0
    ),
    (
        'en_FK',
        'en',
        'English (Falkland Islands)',
        'English (Falkland Islands)',
        NULL,
        0
    ),
    (
        'en_FM',
        'en',
        'English (Micronesia)',
        'English (Micronesia)',
        NULL,
        0
    ),
    (
        'en_GB',
        'en',
        'English (United Kingdom)',
        'English (United Kingdom)',
        NULL,
        1
    ),
    (
        'en_GD',
        'en',
        'English (Grenada)',
        'English (Grenada)',
        NULL,
        0
    ),
    (
        'en_GG',
        'en',
        'English (Guernsey)',
        'English (Guernsey)',
        NULL,
        0
    ),
    (
        'en_GH',
        'en',
        'English (Ghana)',
        'English (Ghana)',
        NULL,
        0
    ),
    (
        'en_GI',
        'en',
        'English (Gibraltar)',
        'English (Gibraltar)',
        NULL,
        0
    ),
    (
        'en_GM',
        'en',
        'English (Gambia)',
        'English (Gambia)',
        NULL,
        0
    ),
    (
        'en_GU',
        'en',
        'English (Guam)',
        'English (Guam)',
        NULL,
        0
    ),
    (
        'en_GY',
        'en',
        'English (Guyana)',
        'English (Guyana)',
        NULL,
        0
    ),
    (
        'en_HK',
        'en',
        'English (Hong Kong SAR China)',
        'English (Hong Kong SAR China)',
        NULL,
        0
    ),
    (
        'en_IE',
        'en',
        'English (Ireland)',
        'English (Ireland)',
        NULL,
        0
    ),
    (
        'en_IL',
        'en',
        'English (Israel)',
        'English (Israel)',
        NULL,
        0
    ),
    (
        'en_IM',
        'en',
        'English (Isle of Man)',
        'English (Isle of Man)',
        NULL,
        0
    ),
    (
        'en_IN',
        'en',
        'English (India)',
        'English (India)',
        NULL,
        0
    ),
    (
        'en_IO',
        'en',
        'English (British Indian Ocean Territory)',
        'English (British Indian Ocean Territory)',
        NULL,
        0
    ),
    (
        'en_JE',
        'en',
        'English (Jersey)',
        'English (Jersey)',
        NULL,
        0
    ),
    (
        'en_JM',
        'en',
        'English (Jamaica)',
        'English (Jamaica)',
        NULL,
        0
    ),
    (
        'en_KE',
        'en',
        'English (Kenya)',
        'English (Kenya)',
        NULL,
        0
    ),
    (
        'en_KI',
        'en',
        'English (Kiribati)',
        'English (Kiribati)',
        NULL,
        0
    ),
    (
        'en_KN',
        'en',
        'English (St. Kitts & Nevis)',
        'English (St. Kitts & Nevis)',
        NULL,
        0
    ),
    (
        'en_KY',
        'en',
        'English (Cayman Islands)',
        'English (Cayman Islands)',
        NULL,
        0
    ),
    (
        'en_LC',
        'en',
        'English (St. Lucia)',
        'English (St. Lucia)',
        NULL,
        0
    ),
    (
        'en_LR',
        'en',
        'English (Liberia)',
        'English (Liberia)',
        NULL,
        0
    ),
    (
        'en_LS',
        'en',
        'English (Lesotho)',
        'English (Lesotho)',
        NULL,
        0
    ),
    (
        'en_MG',
        'en',
        'English (Madagascar)',
        'English (Madagascar)',
        NULL,
        0
    ),
    (
        'en_MH',
        'en',
        'English (Marshall Islands)',
        'English (Marshall Islands)',
        NULL,
        0
    ),
    (
        'en_MO',
        'en',
        'English (Macao SAR China)',
        'English (Macao SAR China)',
        NULL,
        0
    ),
    (
        'en_MP',
        'en',
        'English (Northern Mariana Islands)',
        'English (Northern Mariana Islands)',
        NULL,
        0
    ),
    (
        'en_MS',
        'en',
        'English (Montserrat)',
        'English (Montserrat)',
        NULL,
        0
    ),
    (
        'en_MT',
        'en',
        'English (Malta)',
        'English (Malta)',
        NULL,
        0
    ),
    (
        'en_MU',
        'en',
        'English (Mauritius)',
        'English (Mauritius)',
        NULL,
        0
    ),
    (
        'en_MW',
        'en',
        'English (Malawi)',
        'English (Malawi)',
        NULL,
        0
    ),
    (
        'en_MY',
        'en',
        'English (Malaysia)',
        'English (Malaysia)',
        NULL,
        0
    ),
    (
        'en_NA',
        'en',
        'English (Namibia)',
        'English (Namibia)',
        NULL,
        0
    ),
    (
        'en_NF',
        'en',
        'English (Norfolk Island)',
        'English (Norfolk Island)',
        NULL,
        0
    ),
    (
        'en_NG',
        'en',
        'English (Nigeria)',
        'English (Nigeria)',
        NULL,
        0
    ),
    (
        'en_NL',
        'en',
        'English (Netherlands)',
        'English (Netherlands)',
        NULL,
        0
    ),
    (
        'en_NR',
        'en',
        'English (Nauru)',
        'English (Nauru)',
        NULL,
        0
    ),
    (
        'en_NU',
        'en',
        'English (Niue)',
        'English (Niue)',
        NULL,
        0
    ),
    (
        'en_NZ',
        'en',
        'English (New Zealand)',
        'English (New Zealand)',
        NULL,
        1
    ),
    (
        'en_PG',
        'en',
        'English (Papua New Guinea)',
        'English (Papua New Guinea)',
        NULL,
        0
    ),
    (
        'en_PH',
        'en',
        'English (Philippines)',
        'English (Philippines)',
        NULL,
        0
    ),
    (
        'en_PK',
        'en',
        'English (Pakistan)',
        'English (Pakistan)',
        NULL,
        0
    ),
    (
        'en_PN',
        'en',
        'English (Pitcairn Islands)',
        'English (Pitcairn Islands)',
        NULL,
        0
    ),
    (
        'en_PR',
        'en',
        'English (Puerto Rico)',
        'English (Puerto Rico)',
        NULL,
        0
    ),
    (
        'en_PW',
        'en',
        'English (Palau)',
        'English (Palau)',
        NULL,
        0
    ),
    (
        'en_RW',
        'en',
        'English (Rwanda)',
        'English (Rwanda)',
        NULL,
        0
    ),
    (
        'en_SB',
        'en',
        'English (Solomon Islands)',
        'English (Solomon Islands)',
        NULL,
        0
    ),
    (
        'en_SC',
        'en',
        'English (Seychelles)',
        'English (Seychelles)',
        NULL,
        0
    ),
    (
        'en_SD',
        'en',
        'English (Sudan)',
        'English (Sudan)',
        NULL,
        0
    ),
    (
        'en_SE',
        'en',
        'English (Sweden)',
        'English (Sweden)',
        NULL,
        0
    ),
    (
        'en_SG',
        'en',
        'English (Singapore)',
        'English (Singapore)',
        NULL,
        0
    ),
    (
        'en_SH',
        'en',
        'English (St. Helena)',
        'English (St. Helena)',
        NULL,
        0
    ),
    (
        'en_SI',
        'en',
        'English (Slovenia)',
        'English (Slovenia)',
        NULL,
        0
    ),
    (
        'en_SL',
        'en',
        'English (Sierra Leone)',
        'English (Sierra Leone)',
        NULL,
        0
    ),
    (
        'en_SS',
        'en',
        'English (South Sudan)',
        'English (South Sudan)',
        NULL,
        0
    ),
    (
        'en_SX',
        'en',
        'English (Sint Maarten)',
        'English (Sint Maarten)',
        NULL,
        0
    ),
    (
        'en_SZ',
        'en',
        'English (Eswatini)',
        'English (Eswatini)',
        NULL,
        0
    ),
    (
        'en_TC',
        'en',
        'English (Turks & Caicos Islands)',
        'English (Turks & Caicos Islands)',
        NULL,
        0
    ),
    (
        'en_TK',
        'en',
        'English (Tokelau)',
        'English (Tokelau)',
        NULL,
        0
    ),
    (
        'en_TO',
        'en',
        'English (Tonga)',
        'English (Tonga)',
        NULL,
        0
    ),
    (
        'en_TT',
        'en',
        'English (Trinidad & Tobago)',
        'English (Trinidad & Tobago)',
        NULL,
        0
    ),
    (
        'en_TV',
        'en',
        'English (Tuvalu)',
        'English (Tuvalu)',
        NULL,
        0
    ),
    (
        'en_TZ',
        'en',
        'English (Tanzania)',
        'English (Tanzania)',
        NULL,
        0
    ),
    (
        'en_UG',
        'en',
        'English (Uganda)',
        'English (Uganda)',
        NULL,
        0
    ),
    (
        'en_UM',
        'en',
        'English (U.S. Outlying Islands)',
        'English (U.S. Outlying Islands)',
        NULL,
        0
    ),
    (
        'en_US',
        'en',
        'English (United States)',
        'English (United States)',
        NULL,
        1
    ),
    (
        'en_US_POSIX',
        'en',
        'English (United States, Computer)',
        'English (United States, Computer)',
        NULL,
        0
    ),
    (
        'en_VC',
        'en',
        'English (St. Vincent & Grenadines)',
        'English (St. Vincent & Grenadines)',
        NULL,
        0
    ),
    (
        'en_VG',
        'en',
        'English (British Virgin Islands)',
        'English (British Virgin Islands)',
        NULL,
        0
    ),
    (
        'en_VI',
        'en',
        'English (U.S. Virgin Islands)',
        'English (U.S. Virgin Islands)',
        NULL,
        0
    ),
    (
        'en_VU',
        'en',
        'English (Vanuatu)',
        'English (Vanuatu)',
        NULL,
        0
    ),
    (
        'en_WS',
        'en',
        'English (Samoa)',
        'English (Samoa)',
        NULL,
        0
    ),
    (
        'en_ZA',
        'en',
        'English (South Africa)',
        'English (South Africa)',
        NULL,
        0
    ),
    (
        'en_ZM',
        'en',
        'English (Zambia)',
        'English (Zambia)',
        NULL,
        0
    ),
    (
        'en_ZW',
        'en',
        'English (Zimbabwe)',
        'English (Zimbabwe)',
        NULL,
        0
    ),
    ('eo', 'eo', 'esperanto', 'Esperanto', NULL, 0),
    (
        'eo_001',
        'eo',
        'esperanto (Mondo)',
        'Esperanto (world)',
        NULL,
        0
    ),
    ('es', 'es', 'español', 'Spanish', NULL, 1),
    (
        'es_419',
        'es',
        'español (Latinoamérica)',
        'Spanish (Latin America)',
        NULL,
        0
    ),
    (
        'es_AR',
        'es',
        'español (Argentina)',
        'Spanish (Argentina)',
        NULL,
        1
    ),
    (
        'es_BO',
        'es',
        'español (Bolivia)',
        'Spanish (Bolivia)',
        NULL,
        1
    ),
    (
        'es_BR',
        'es',
        'español (Brasil)',
        'Spanish (Brazil)',
        NULL,
        1
    ),
    (
        'es_BZ',
        'es',
        'español (Belice)',
        'Spanish (Belize)',
        NULL,
        1
    ),
    (
        'es_CL',
        'es',
        'español (Chile)',
        'Spanish (Chile)',
        NULL,
        1
    ),
    (
        'es_CO',
        'es',
        'español (Colombia)',
        'Spanish (Colombia)',
        NULL,
        1
    ),
    (
        'es_CR',
        'es',
        'español (Costa Rica)',
        'Spanish (Costa Rica)',
        NULL,
        1
    ),
    (
        'es_CU',
        'es',
        'español (Cuba)',
        'Spanish (Cuba)',
        NULL,
        1
    ),
    (
        'es_DO',
        'es',
        'español (República Dominicana)',
        'Spanish (Dominican Republic)',
        NULL,
        1
    ),
    (
        'es_EA',
        'es',
        'español (Ceuta y Melilla)',
        'Spanish (Ceuta & Melilla)',
        NULL,
        0
    ),
    (
        'es_EC',
        'es',
        'español (Ecuador)',
        'Spanish (Ecuador)',
        NULL,
        1
    ),
    (
        'es_ES',
        'es',
        'español (España)',
        'Spanish (Spain)',
        NULL,
        1
    ),
    (
        'es_GQ',
        'es',
        'español (Guinea Ecuatorial)',
        'Spanish (Equatorial Guinea)',
        NULL,
        1
    ),
    (
        'es_GT',
        'es',
        'español (Guatemala)',
        'Spanish (Guatemala)',
        NULL,
        1
    ),
    (
        'es_HN',
        'es',
        'español (Honduras)',
        'Spanish (Honduras)',
        NULL,
        1
    ),
    (
        'es_IC',
        'es',
        'español (Canarias)',
        'Spanish (Canary Islands)',
        NULL,
        0
    ),
    (
        'es_MX',
        'es',
        'español (México)',
        'Spanish (Mexico)',
        NULL,
        1
    ),
    (
        'es_NI',
        'es',
        'español (Nicaragua)',
        'Spanish (Nicaragua)',
        NULL,
        1
    ),
    (
        'es_PA',
        'es',
        'español (Panamá)',
        'Spanish (Panama)',
        NULL,
        1
    ),
    (
        'es_PE',
        'es',
        'español (Perú)',
        'Spanish (Peru)',
        NULL,
        1
    ),
    (
        'es_PH',
        'es',
        'español (Filipinas)',
        'Spanish (Philippines)',
        NULL,
        0
    ),
    (
        'es_PR',
        'es',
        'español (Puerto Rico)',
        'Spanish (Puerto Rico)',
        NULL,
        1
    ),
    (
        'es_PY',
        'es',
        'español (Paraguay)',
        'Spanish (Paraguay)',
        NULL,
        1
    ),
    (
        'es_SV',
        'es',
        'español (El Salvador)',
        'Spanish (El Salvador)',
        NULL,
        1
    ),
    (
        'es_US',
        'es',
        'español (Estados Unidos)',
        'Spanish (United States)',
        NULL,
        0
    ),
    (
        'es_UY',
        'es',
        'español (Uruguay)',
        'Spanish (Uruguay)',
        NULL,
        1
    ),
    (
        'es_VE',
        'es',
        'español (Venezuela)',
        'Spanish (Venezuela)',
        NULL,
        1
    ),
    ('et', 'et', 'eesti', 'Estonian', NULL, 0),
    (
        'et_EE',
        'et',
        'eesti (Eesti)',
        'Estonian (Estonia)',
        NULL,
        0
    ),
    ('eu', 'eu', 'euskara', 'Basque', NULL, 0),
    (
        'eu_ES',
        'eu',
        'euskara (Espainia)',
        'Basque (Spain)',
        NULL,
        0
    ),
    ('ewo', 'ewo', 'ewondo', 'Ewondo', NULL, 0),
    (
        'ewo_CM',
        'ewo',
        'ewondo (Kamərún)',
        'Ewondo (Cameroon)',
        NULL,
        0
    ),
    ('fa', 'fa', 'فارسی', 'Persian', NULL, 0),
    (
        'fa_AF',
        'fa',
        'فارسی (افغانستان)',
        'Persian (Afghanistan)',
        NULL,
        0
    ),
    (
        'fa_IR',
        'fa',
        'فارسی (ایران)',
        'Persian (Iran)',
        NULL,
        0
    ),
    ('ff', 'ff', 'Pulaar', 'Fulah', NULL, 0),
    (
        'ff_Adlm',
        'ff',
        '???????????????????? (????????????????????)',
        'Fulah (Adlam)',
        NULL,
        0
    ),
    (
        'ff_Adlm_BF',
        'ff',
        '???????????????????? (????????????????????⹁ ??????',
        'Fulah (Adlam, Burkina Faso)',
        NULL,
        0
    ),
    (
        'ff_Adlm_CM',
        'ff',
        '???????????????????? (????????????????????⹁ ??????',
        'Fulah (Adlam, Cameroon)',
        NULL,
        0
    ),
    (
        'ff_Adlm_GH',
        'ff',
        '???????????????????? (????????????????????⹁ ??????',
        'Fulah (Adlam, Ghana)',
        NULL,
        0
    ),
    (
        'ff_Adlm_GM',
        'ff',
        '???????????????????? (????????????????????⹁ ??????',
        'Fulah (Adlam, Gambia)',
        NULL,
        0
    ),
    (
        'ff_Adlm_GN',
        'ff',
        '???????????????????? (????????????????????⹁ ??????',
        'Fulah (Adlam, Guinea)',
        NULL,
        0
    ),
    (
        'ff_Adlm_GW',
        'ff',
        '???????????????????? (????????????????????⹁ ??????',
        'Fulah (Adlam, Guinea-Bissau)',
        NULL,
        0
    ),
    (
        'ff_Adlm_LR',
        'ff',
        '???????????????????? (????????????????????⹁ ??????',
        'Fulah (Adlam, Liberia)',
        NULL,
        0
    ),
    (
        'ff_Adlm_MR',
        'ff',
        '???????????????????? (????????????????????⹁ ??????',
        'Fulah (Adlam, Mauritania)',
        NULL,
        0
    ),
    (
        'ff_Adlm_NE',
        'ff',
        '???????????????????? (????????????????????⹁ ??????',
        'Fulah (Adlam, Niger)',
        NULL,
        0
    ),
    (
        'ff_Adlm_NG',
        'ff',
        '???????????????????? (????????????????????⹁ ??????',
        'Fulah (Adlam, Nigeria)',
        NULL,
        0
    ),
    (
        'ff_Adlm_SL',
        'ff',
        '???????????????????? (????????????????????⹁ ??????',
        'Fulah (Adlam, Sierra Leone)',
        NULL,
        0
    ),
    (
        'ff_Adlm_SN',
        'ff',
        '???????????????????? (????????????????????⹁ ??????',
        'Fulah (Adlam, Senegal)',
        NULL,
        0
    ),
    (
        'ff_Latn',
        'ff',
        'Pulaar (Latn)',
        'Fulah (Latin)',
        NULL,
        0
    ),
    (
        'ff_Latn_BF',
        'ff',
        'Pulaar (Latn, Burkibaa Faaso)',
        'Fulah (Latin, Burkina Faso)',
        NULL,
        0
    ),
    (
        'ff_Latn_CM',
        'ff',
        'Pulaar (Latn, Kameruun)',
        'Fulah (Latin, Cameroon)',
        NULL,
        0
    ),
    (
        'ff_Latn_GH',
        'ff',
        'Pulaar (Latn, Ganaa)',
        'Fulah (Latin, Ghana)',
        NULL,
        0
    ),
    (
        'ff_Latn_GM',
        'ff',
        'Pulaar (Latn, Gammbi)',
        'Fulah (Latin, Gambia)',
        NULL,
        0
    ),
    (
        'ff_Latn_GN',
        'ff',
        'Pulaar (Latn, Gine)',
        'Fulah (Latin, Guinea)',
        NULL,
        0
    ),
    (
        'ff_Latn_GW',
        'ff',
        'Pulaar (Latn, Gine-Bisaawo)',
        'Fulah (Latin, Guinea-Bissau)',
        NULL,
        0
    ),
    (
        'ff_Latn_LR',
        'ff',
        'Pulaar (Latn, Liberiyaa)',
        'Fulah (Latin, Liberia)',
        NULL,
        0
    ),
    (
        'ff_Latn_MR',
        'ff',
        'Pulaar (Latn, Muritani)',
        'Fulah (Latin, Mauritania)',
        NULL,
        0
    ),
    (
        'ff_Latn_NE',
        'ff',
        'Pulaar (Latn, Nijeer)',
        'Fulah (Latin, Niger)',
        NULL,
        0
    ),
    (
        'ff_Latn_NG',
        'ff',
        'Pulaar (Latn, Nijeriyaa)',
        'Fulah (Latin, Nigeria)',
        NULL,
        0
    ),
    (
        'ff_Latn_SL',
        'ff',
        'Pulaar (Latn, Seraa liyon)',
        'Fulah (Latin, Sierra Leone)',
        NULL,
        0
    ),
    (
        'ff_Latn_SN',
        'ff',
        'Pulaar (Latn, Senegaal)',
        'Fulah (Latin, Senegal)',
        NULL,
        0
    ),
    ('fi', 'fi', 'suomi', 'Finnish', NULL, 0),
    (
        'fi_FI',
        'fi',
        'suomi (Suomi)',
        'Finnish (Finland)',
        NULL,
        0
    ),
    ('fil', 'fil', 'Filipino', 'Filipino', NULL, 0),
    (
        'fil_PH',
        'fil',
        'Filipino (Pilipinas)',
        'Filipino (Philippines)',
        NULL,
        0
    ),
    ('fo', 'fo', 'føroyskt', 'Faroese', NULL, 0),
    (
        'fo_DK',
        'fo',
        'føroyskt (Danmark)',
        'Faroese (Denmark)',
        NULL,
        0
    ),
    (
        'fo_FO',
        'fo',
        'føroyskt (Føroyar)',
        'Faroese (Faroe Islands)',
        NULL,
        0
    ),
    ('fr', 'fr', 'français', 'French', NULL, 0),
    (
        'fr_BE',
        'fr',
        'français (Belgique)',
        'French (Belgium)',
        NULL,
        0
    ),
    (
        'fr_BF',
        'fr',
        'français (Burkina Faso)',
        'French (Burkina Faso)',
        NULL,
        0
    ),
    (
        'fr_BI',
        'fr',
        'français (Burundi)',
        'French (Burundi)',
        NULL,
        0
    ),
    (
        'fr_BJ',
        'fr',
        'français (Bénin)',
        'French (Benin)',
        NULL,
        0
    ),
    (
        'fr_BL',
        'fr',
        'français (Saint-Barthélemy)',
        'French (St. Barthélemy)',
        NULL,
        0
    ),
    (
        'fr_CA',
        'fr',
        'français (Canada)',
        'French (Canada)',
        NULL,
        0
    ),
    (
        'fr_CD',
        'fr',
        'français (Congo-Kinshasa)',
        'French (Congo - Kinshasa)',
        NULL,
        0
    ),
    (
        'fr_CF',
        'fr',
        'français (République centrafricaine)',
        'French (Central African Republic)',
        NULL,
        0
    ),
    (
        'fr_CG',
        'fr',
        'français (Congo-Brazzaville)',
        'French (Congo - Brazzaville)',
        NULL,
        0
    ),
    (
        'fr_CH',
        'fr',
        'français (Suisse)',
        'French (Switzerland)',
        NULL,
        0
    ),
    (
        'fr_CI',
        'fr',
        'français (Côte d’Ivoire)',
        'French (Côte d’Ivoire)',
        NULL,
        0
    ),
    (
        'fr_CM',
        'fr',
        'français (Cameroun)',
        'French (Cameroon)',
        NULL,
        0
    ),
    (
        'fr_DJ',
        'fr',
        'français (Djibouti)',
        'French (Djibouti)',
        NULL,
        0
    ),
    (
        'fr_DZ',
        'fr',
        'français (Algérie)',
        'French (Algeria)',
        NULL,
        0
    ),
    (
        'fr_FR',
        'fr',
        'français (France)',
        'French (France)',
        NULL,
        0
    ),
    (
        'fr_GA',
        'fr',
        'français (Gabon)',
        'French (Gabon)',
        NULL,
        0
    ),
    (
        'fr_GF',
        'fr',
        'français (Guyane française)',
        'French (French Guiana)',
        NULL,
        0
    ),
    (
        'fr_GN',
        'fr',
        'français (Guinée)',
        'French (Guinea)',
        NULL,
        0
    ),
    (
        'fr_GP',
        'fr',
        'français (Guadeloupe)',
        'French (Guadeloupe)',
        NULL,
        0
    ),
    (
        'fr_GQ',
        'fr',
        'français (Guinée équatoriale)',
        'French (Equatorial Guinea)',
        NULL,
        0
    ),
    (
        'fr_HT',
        'fr',
        'français (Haïti)',
        'French (Haiti)',
        NULL,
        0
    ),
    (
        'fr_KM',
        'fr',
        'français (Comores)',
        'French (Comoros)',
        NULL,
        0
    ),
    (
        'fr_LU',
        'fr',
        'français (Luxembourg)',
        'French (Luxembourg)',
        NULL,
        0
    ),
    (
        'fr_MA',
        'fr',
        'français (Maroc)',
        'French (Morocco)',
        NULL,
        0
    ),
    (
        'fr_MC',
        'fr',
        'français (Monaco)',
        'French (Monaco)',
        NULL,
        0
    ),
    (
        'fr_MF',
        'fr',
        'français (Saint-Martin)',
        'French (St. Martin)',
        NULL,
        0
    ),
    (
        'fr_MG',
        'fr',
        'français (Madagascar)',
        'French (Madagascar)',
        NULL,
        0
    ),
    (
        'fr_ML',
        'fr',
        'français (Mali)',
        'French (Mali)',
        NULL,
        0
    ),
    (
        'fr_MQ',
        'fr',
        'français (Martinique)',
        'French (Martinique)',
        NULL,
        0
    ),
    (
        'fr_MR',
        'fr',
        'français (Mauritanie)',
        'French (Mauritania)',
        NULL,
        0
    ),
    (
        'fr_MU',
        'fr',
        'français (Maurice)',
        'French (Mauritius)',
        NULL,
        0
    ),
    (
        'fr_NC',
        'fr',
        'français (Nouvelle-Calédonie)',
        'French (New Caledonia)',
        NULL,
        0
    ),
    (
        'fr_NE',
        'fr',
        'français (Niger)',
        'French (Niger)',
        NULL,
        0
    ),
    (
        'fr_PF',
        'fr',
        'français (Polynésie française)',
        'French (French Polynesia)',
        NULL,
        0
    ),
    (
        'fr_PM',
        'fr',
        'français (Saint-Pierre-et-Miquelon)',
        'French (St. Pierre & Miquelon)',
        NULL,
        0
    ),
    (
        'fr_RE',
        'fr',
        'français (La Réunion)',
        'French (Réunion)',
        NULL,
        0
    ),
    (
        'fr_RW',
        'fr',
        'français (Rwanda)',
        'French (Rwanda)',
        NULL,
        0
    ),
    (
        'fr_SC',
        'fr',
        'français (Seychelles)',
        'French (Seychelles)',
        NULL,
        0
    ),
    (
        'fr_SN',
        'fr',
        'français (Sénégal)',
        'French (Senegal)',
        NULL,
        0
    ),
    (
        'fr_SY',
        'fr',
        'français (Syrie)',
        'French (Syria)',
        NULL,
        0
    ),
    (
        'fr_TD',
        'fr',
        'français (Tchad)',
        'French (Chad)',
        NULL,
        0
    ),
    (
        'fr_TG',
        'fr',
        'français (Togo)',
        'French (Togo)',
        NULL,
        0
    ),
    (
        'fr_TN',
        'fr',
        'français (Tunisie)',
        'French (Tunisia)',
        NULL,
        0
    ),
    (
        'fr_VU',
        'fr',
        'français (Vanuatu)',
        'French (Vanuatu)',
        NULL,
        0
    ),
    (
        'fr_WF',
        'fr',
        'français (Wallis-et-Futuna)',
        'French (Wallis & Futuna)',
        NULL,
        0
    ),
    (
        'fr_YT',
        'fr',
        'français (Mayotte)',
        'French (Mayotte)',
        NULL,
        0
    ),
    ('fur', 'fur', 'furlan', 'Friulian', NULL, 0),
    (
        'fur_IT',
        'fur',
        'furlan (Italie)',
        'Friulian (Italy)',
        NULL,
        0
    ),
    ('fy', 'fy', 'Frysk', 'Western Frisian', NULL, 0),
    (
        'fy_NL',
        'fy',
        'Frysk (Nederlân)',
        'Western Frisian (Netherlands)',
        NULL,
        0
    ),
    ('ga', 'ga', 'Gaeilge', 'Irish', NULL, 0),
    (
        'ga_GB',
        'ga',
        'Gaeilge (an Ríocht Aontaithe)',
        'Irish (United Kingdom)',
        NULL,
        0
    ),
    (
        'ga_IE',
        'ga',
        'Gaeilge (Éire)',
        'Irish (Ireland)',
        NULL,
        0
    ),
    (
        'gd',
        'gd',
        'Gàidhlig',
        'Scottish Gaelic',
        NULL,
        0
    ),
    (
        'gd_GB',
        'gd',
        'Gàidhlig (An Rìoghachd Aonaichte)',
        'Scottish Gaelic (United Kingdom)',
        NULL,
        0
    ),
    ('gl', 'gl', 'galego', 'Galician', NULL, 0),
    (
        'gl_ES',
        'gl',
        'galego (España)',
        'Galician (Spain)',
        NULL,
        0
    ),
    (
        'gsw',
        'gsw',
        'Schwiizertüütsch',
        'Swiss German',
        NULL,
        0
    ),
    (
        'gsw_CH',
        'gsw',
        'Schwiizertüütsch (Schwiiz)',
        'Swiss German (Switzerland)',
        NULL,
        0
    ),
    (
        'gsw_FR',
        'gsw',
        'Schwiizertüütsch (Frankriich)',
        'Swiss German (France)',
        NULL,
        0
    ),
    (
        'gsw_LI',
        'gsw',
        'Schwiizertüütsch (Liächteschtäi)',
        'Swiss German (Liechtenstein)',
        NULL,
        0
    ),
    ('gu', 'gu', 'ગુજરાતી', 'Gujarati', NULL, 0),
    (
        'gu_IN',
        'gu',
        'ગુજરાતી (ભારત)',
        'Gujarati (India)',
        NULL,
        0
    ),
    ('guz', 'guz', 'Ekegusii', 'Gusii', NULL, 0),
    (
        'guz_KE',
        'guz',
        'Ekegusii (Kenya)',
        'Gusii (Kenya)',
        NULL,
        0
    ),
    ('gv', 'gv', 'Gaelg', 'Manx', NULL, 0),
    (
        'gv_IM',
        'gv',
        'Gaelg (Ellan Vannin)',
        'Manx (Isle of Man)',
        NULL,
        0
    ),
    ('ha', 'ha', 'Hausa', 'Hausa', NULL, 0),
    (
        'ha_GH',
        'ha',
        'Hausa (Gana)',
        'Hausa (Ghana)',
        NULL,
        0
    ),
    (
        'ha_NE',
        'ha',
        'Hausa (Nijar)',
        'Hausa (Niger)',
        NULL,
        0
    ),
    (
        'ha_NG',
        'ha',
        'Hausa (Najeriya)',
        'Hausa (Nigeria)',
        NULL,
        0
    ),
    (
        'haw',
        'haw',
        'ʻŌlelo Hawaiʻi',
        'Hawaiian',
        NULL,
        0
    ),
    (
        'haw_US',
        'haw',
        'ʻŌlelo Hawaiʻi (ʻAmelika Hui Pū ʻIa)',
        'Hawaiian (United States)',
        NULL,
        0
    ),
    ('he', 'he', 'עברית', 'Hebrew', NULL, 0),
    (
        'he_IL',
        'he',
        'עברית (ישראל)',
        'Hebrew (Israel)',
        NULL,
        0
    ),
    ('hi', 'hi', 'हिन्दी', 'Hindi', NULL, 0),
    (
        'hi_IN',
        'hi',
        'हिन्दी (भारत)',
        'Hindi (India)',
        NULL,
        0
    ),
    ('hr', 'hr', 'hrvatski', 'Croatian', NULL, 0),
    (
        'hr_BA',
        'hr',
        'hrvatski (Bosna i Hercegovina)',
        'Croatian (Bosnia & Herzegovina)',
        NULL,
        0
    ),
    (
        'hr_HR',
        'hr',
        'hrvatski (Hrvatska)',
        'Croatian (Croatia)',
        NULL,
        0
    ),
    (
        'hsb',
        'hsb',
        'hornjoserbšćina',
        'Upper Sorbian',
        NULL,
        0
    ),
    (
        'hsb_DE',
        'hsb',
        'hornjoserbšćina (Němska)',
        'Upper Sorbian (Germany)',
        NULL,
        0
    ),
    ('hu', 'hu', 'magyar', 'Hungarian', NULL, 0),
    (
        'hu_HU',
        'hu',
        'magyar (Magyarország)',
        'Hungarian (Hungary)',
        NULL,
        0
    ),
    ('hy', 'hy', 'հայերեն', 'Armenian', NULL, 0),
    (
        'hy_AM',
        'hy',
        'հայերեն (Հայաստան)',
        'Armenian (Armenia)',
        NULL,
        0
    ),
    ('ia', 'ia', 'interlingua', 'Interlingua', NULL, 0),
    (
        'ia_001',
        'ia',
        'interlingua (Mundo)',
        'Interlingua (world)',
        NULL,
        0
    ),
    ('id', 'id', 'Indonesia', 'Indonesian', NULL, 0),
    (
        'id_ID',
        'id',
        'Indonesia (Indonesia)',
        'Indonesian (Indonesia)',
        NULL,
        0
    ),
    ('ig', 'ig', 'Igbo', 'Igbo', NULL, 0),
    (
        'ig_NG',
        'ig',
        'Igbo (Naịjịrịa)',
        'Igbo (Nigeria)',
        NULL,
        0
    ),
    ('ii', 'ii', 'ꆈꌠꉙ', 'Sichuan Yi', NULL, 0),
    (
        'ii_CN',
        'ii',
        'ꆈꌠꉙ (ꍏꇩ)',
        'Sichuan Yi (China)',
        NULL,
        0
    ),
    ('is', 'is', 'íslenska', 'Icelandic', NULL, 0),
    (
        'is_IS',
        'is',
        'íslenska (Ísland)',
        'Icelandic (Iceland)',
        NULL,
        0
    ),
    ('it', 'it', 'italiano', 'Italian', NULL, 0),
    (
        'it_CH',
        'it',
        'italiano (Svizzera)',
        'Italian (Switzerland)',
        NULL,
        0
    ),
    (
        'it_IT',
        'it',
        'italiano (Italia)',
        'Italian (Italy)',
        NULL,
        0
    ),
    (
        'it_SM',
        'it',
        'italiano (San Marino)',
        'Italian (San Marino)',
        NULL,
        0
    ),
    (
        'it_VA',
        'it',
        'italiano (Città del Vaticano)',
        'Italian (Vatican City)',
        NULL,
        0
    ),
    ('ja', 'ja', '日本語', 'Japanese', NULL, 0),
    (
        'ja_JP',
        'ja',
        '日本語 (日本)',
        'Japanese (Japan)',
        NULL,
        0
    ),
    ('jgo', 'jgo', 'Ndaꞌa', 'Ngomba', NULL, 0),
    (
        'jgo_CM',
        'jgo',
        'Ndaꞌa (Kamɛlûn)',
        'Ngomba (Cameroon)',
        NULL,
        0
    ),
    ('jmc', 'jmc', 'Kimachame', 'Machame', NULL, 0),
    (
        'jmc_TZ',
        'jmc',
        'Kimachame (Tanzania)',
        'Machame (Tanzania)',
        NULL,
        0
    ),
    ('jv', 'jv', 'Jawa', 'Javanese', NULL, 0),
    (
        'jv_ID',
        'jv',
        'Jawa (Indonésia)',
        'Javanese (Indonesia)',
        NULL,
        0
    ),
    ('ka', 'ka', 'ქართული', 'Georgian', NULL, 0),
    (
        'ka_GE',
        'ka',
        'ქართული (საქართველო)',
        'Georgian (Georgia)',
        NULL,
        0
    ),
    ('kab', 'kab', 'Taqbaylit', 'Kabyle', NULL, 0),
    (
        'kab_DZ',
        'kab',
        'Taqbaylit (Lezzayer)',
        'Kabyle (Algeria)',
        NULL,
        0
    ),
    ('kam', 'kam', 'Kikamba', 'Kamba', NULL, 0),
    (
        'kam_KE',
        'kam',
        'Kikamba (Kenya)',
        'Kamba (Kenya)',
        NULL,
        0
    ),
    ('kde', 'kde', 'Chimakonde', 'Makonde', NULL, 0),
    (
        'kde_TZ',
        'kde',
        'Chimakonde (Tanzania)',
        'Makonde (Tanzania)',
        NULL,
        0
    ),
    (
        'kea',
        'kea',
        'kabuverdianu',
        'Kabuverdianu',
        NULL,
        0
    ),
    (
        'kea_CV',
        'kea',
        'kabuverdianu (Kabu Verdi)',
        'Kabuverdianu (Cape Verde)',
        NULL,
        0
    ),
    (
        'khq',
        'khq',
        'Koyra ciini',
        'Koyra Chiini',
        NULL,
        0
    ),
    (
        'khq_ML',
        'khq',
        'Koyra ciini (Maali)',
        'Koyra Chiini (Mali)',
        NULL,
        0
    ),
    ('ki', 'ki', 'Gikuyu', 'Kikuyu', NULL, 0),
    (
        'ki_KE',
        'ki',
        'Gikuyu (Kenya)',
        'Kikuyu (Kenya)',
        NULL,
        0
    ),
    ('kk', 'kk', 'қазақ тілі', 'Kazakh', NULL, 0),
    (
        'kk_KZ',
        'kk',
        'қазақ тілі (Қазақстан)',
        'Kazakh (Kazakhstan)',
        NULL,
        0
    ),
    ('kkj', 'kkj', 'kakɔ', 'Kako', NULL, 0),
    (
        'kkj_CM',
        'kkj',
        'kakɔ (Kamɛrun)',
        'Kako (Cameroon)',
        NULL,
        0
    ),
    ('kl', 'kl', 'kalaallisut', 'Kalaallisut', NULL, 0),
    (
        'kl_GL',
        'kl',
        'kalaallisut (Kalaallit Nunaat)',
        'Kalaallisut (Greenland)',
        NULL,
        0
    ),
    ('kln', 'kln', 'Kalenjin', 'Kalenjin', NULL, 0),
    (
        'kln_KE',
        'kln',
        'Kalenjin (Emetab Kenya)',
        'Kalenjin (Kenya)',
        NULL,
        0
    ),
    ('km', 'km', 'ខ្មែរ', 'Khmer', NULL, 0),
    (
        'km_KH',
        'km',
        'ខ្មែរ (កម្ពុជា)',
        'Khmer (Cambodia)',
        NULL,
        0
    ),
    ('kn', 'kn', 'ಕನ್ನಡ', 'Kannada', NULL, 0),
    (
        'kn_IN',
        'kn',
        'ಕನ್ನಡ (ಭಾರತ)',
        'Kannada (India)',
        NULL,
        0
    ),
    ('ko', 'ko', '한국어', 'Korean', NULL, 0),
    (
        'ko_KP',
        'ko',
        '한국어(조선민주주의인민공화국)',
        'Korean (North Korea)',
        NULL,
        0
    ),
    (
        'ko_KR',
        'ko',
        '한국어(대한민국)',
        'Korean (South Korea)',
        NULL,
        0
    ),
    ('kok', 'kok', 'कोंकणी', 'Konkani', NULL, 0),
    (
        'kok_IN',
        'kok',
        'कोंकणी (भारत)',
        'Konkani (India)',
        NULL,
        0
    ),
    ('ks', 'ks', 'کٲشُر', 'Kashmiri', NULL, 0),
    (
        'ks_Arab',
        'ks',
        'کٲشُر (اَربی)',
        'Kashmiri (Arabic)',
        NULL,
        0
    ),
    (
        'ks_Arab_IN',
        'ks',
        'کٲشُر (اَربی, ہِندوستان)',
        'Kashmiri (Arabic, India)',
        NULL,
        0
    ),
    ('ksb', 'ksb', 'Kishambaa', 'Shambala', NULL, 0),
    (
        'ksb_TZ',
        'ksb',
        'Kishambaa (Tanzania)',
        'Shambala (Tanzania)',
        NULL,
        0
    ),
    ('ksf', 'ksf', 'rikpa', 'Bafia', NULL, 0),
    (
        'ksf_CM',
        'ksf',
        'rikpa (kamɛrún)',
        'Bafia (Cameroon)',
        NULL,
        0
    ),
    ('ksh', 'ksh', 'Kölsch', 'Colognian', NULL, 0),
    (
        'ksh_DE',
        'ksh',
        'Kölsch en Doütschland',
        'Colognian (Germany)',
        NULL,
        0
    ),
    ('ku', 'ku', 'kurdî', 'Kurdish', NULL, 0),
    (
        'ku_TR',
        'ku',
        'kurdî (Tirkiye)',
        'Kurdish (Turkey)',
        NULL,
        0
    ),
    ('kw', 'kw', 'kernewek', 'Cornish', NULL, 0),
    (
        'kw_GB',
        'kw',
        'kernewek (Rywvaneth Unys)',
        'Cornish (United Kingdom)',
        NULL,
        0
    ),
    ('ky', 'ky', 'кыргызча', 'Kyrgyz', NULL, 0),
    (
        'ky_KG',
        'ky',
        'кыргызча (Кыргызстан)',
        'Kyrgyz (Kyrgyzstan)',
        NULL,
        0
    ),
    ('lag', 'lag', 'Kɨlaangi', 'Langi', NULL, 0),
    (
        'lag_TZ',
        'lag',
        'Kɨlaangi (Taansanía)',
        'Langi (Tanzania)',
        NULL,
        0
    ),
    (
        'lb',
        'lb',
        'Lëtzebuergesch',
        'Luxembourgish',
        NULL,
        0
    ),
    (
        'lb_LU',
        'lb',
        'Lëtzebuergesch (Lëtzebuerg)',
        'Luxembourgish (Luxembourg)',
        NULL,
        0
    ),
    ('lg', 'lg', 'Luganda', 'Ganda', NULL, 0),
    (
        'lg_UG',
        'lg',
        'Luganda (Yuganda)',
        'Ganda (Uganda)',
        NULL,
        0
    ),
    ('lkt', 'lkt', 'Lakȟólʼiyapi', 'Lakota', NULL, 0),
    (
        'lkt_US',
        'lkt',
        'Lakȟólʼiyapi (Mílahaŋska Tȟamákȟočhe)',
        'Lakota (United States)',
        NULL,
        0
    ),
    ('ln', 'ln', 'lingála', 'Lingala', NULL, 0),
    (
        'ln_AO',
        'ln',
        'lingála (Angóla)',
        'Lingala (Angola)',
        NULL,
        0
    ),
    (
        'ln_CD',
        'ln',
        'lingála (Republíki ya Kongó Demokratíki)',
        'Lingala (Congo - Kinshasa)',
        NULL,
        0
    ),
    (
        'ln_CF',
        'ln',
        'lingála (Repibiki ya Afríka ya Káti)',
        'Lingala (Central African Republic)',
        NULL,
        0
    ),
    (
        'ln_CG',
        'ln',
        'lingála (Kongo)',
        'Lingala (Congo - Brazzaville)',
        NULL,
        0
    ),
    ('lo', 'lo', 'ລາວ', 'Lao', NULL, 0),
    ('lo_LA', 'lo', 'ລາວ (ລາວ)', 'Lao (Laos)', NULL, 0),
    (
        'lrc',
        'lrc',
        'لۊری شومالی',
        'Northern Luri',
        NULL,
        0
    ),
    (
        'lrc_IQ',
        'lrc',
        'لۊری شومالی (IQ)',
        'Northern Luri (Iraq)',
        NULL,
        0
    ),
    (
        'lrc_IR',
        'lrc',
        'لۊری شومالی (IR)',
        'Northern Luri (Iran)',
        NULL,
        0
    ),
    ('lt', 'lt', 'lietuvių', 'Lithuanian', NULL, 0),
    (
        'lt_LT',
        'lt',
        'lietuvių (Lietuva)',
        'Lithuanian (Lithuania)',
        NULL,
        0
    ),
    ('lu', 'lu', 'Tshiluba', 'Luba-Katanga', NULL, 0),
    (
        'lu_CD',
        'lu',
        'Tshiluba (Ditunga wa Kongu)',
        'Luba-Katanga (Congo - Kinshasa)',
        NULL,
        0
    ),
    ('luo', 'luo', 'Dholuo', 'Luo', NULL, 0),
    (
        'luo_KE',
        'luo',
        'Dholuo (Kenya)',
        'Luo (Kenya)',
        NULL,
        0
    ),
    ('luy', 'luy', 'Luluhia', 'Luyia', NULL, 0),
    (
        'luy_KE',
        'luy',
        'Luluhia (Kenya)',
        'Luyia (Kenya)',
        NULL,
        0
    ),
    ('lv', 'lv', 'latviešu', 'Latvian', NULL, 0),
    (
        'lv_LV',
        'lv',
        'latviešu (Latvija)',
        'Latvian (Latvia)',
        NULL,
        0
    ),
    ('mai', 'mai', 'मैथिली', 'Maithili', NULL, 0),
    (
        'mai_IN',
        'mai',
        'मैथिली (भारत)',
        'Maithili (India)',
        NULL,
        0
    ),
    ('mas', 'mas', 'Maa', 'Masai', NULL, 0),
    (
        'mas_KE',
        'mas',
        'Maa (Kenya)',
        'Masai (Kenya)',
        NULL,
        0
    ),
    (
        'mas_TZ',
        'mas',
        'Maa (Tansania)',
        'Masai (Tanzania)',
        NULL,
        0
    ),
    ('mer', 'mer', 'Kĩmĩrũ', 'Meru', NULL, 0),
    (
        'mer_KE',
        'mer',
        'Kĩmĩrũ (Kenya)',
        'Meru (Kenya)',
        NULL,
        0
    ),
    (
        'mfe',
        'mfe',
        'kreol morisien',
        'Morisyen',
        NULL,
        0
    ),
    (
        'mfe_MU',
        'mfe',
        'kreol morisien (Moris)',
        'Morisyen (Mauritius)',
        NULL,
        0
    ),
    ('mg', 'mg', 'Malagasy', 'Malagasy', NULL, 0),
    (
        'mg_MG',
        'mg',
        'Malagasy (Madagasikara)',
        'Malagasy (Madagascar)',
        NULL,
        0
    ),
    ('mgh', 'mgh', 'Makua', 'Makhuwa-Meetto', NULL, 0),
    (
        'mgh_MZ',
        'mgh',
        'Makua (Umozambiki)',
        'Makhuwa-Meetto (Mozambique)',
        NULL,
        0
    ),
    ('mgo', 'mgo', 'metaʼ', 'Metaʼ', NULL, 0),
    (
        'mgo_CM',
        'mgo',
        'metaʼ (Kamalun)',
        'Metaʼ (Cameroon)',
        NULL,
        0
    ),
    ('mi', 'mi', 'te reo Māori', 'Maori', NULL, 0),
    (
        'mi_NZ',
        'mi',
        'te reo Māori (Aotearoa)',
        'Maori (New Zealand)',
        NULL,
        0
    ),
    ('mk', 'mk', 'македонски', 'Macedonian', NULL, 0),
    (
        'mk_MK',
        'mk',
        'македонски (Северна Македонија)',
        'Macedonian (North Macedonia)',
        NULL,
        0
    ),
    ('ml', 'ml', 'മലയാളം', 'Malayalam', NULL, 0),
    (
        'ml_IN',
        'ml',
        'മലയാളം (ഇന്ത്യ)',
        'Malayalam (India)',
        NULL,
        0
    ),
    ('mn', 'mn', 'монгол', 'Mongolian', NULL, 0),
    (
        'mn_MN',
        'mn',
        'монгол (Монгол)',
        'Mongolian (Mongolia)',
        NULL,
        0
    ),
    ('mni', 'mni', 'মৈতৈলোন্', 'Manipuri', NULL, 0),
    (
        'mni_Beng',
        'mni',
        'মৈতৈলোন্ (বাংলা)',
        'Manipuri (Bangla)',
        NULL,
        0
    ),
    (
        'mni_Beng_IN',
        'mni',
        'মৈতৈলোন্ (বাংলা, ইন্দিয়া)',
        'Manipuri (Bangla, India)',
        NULL,
        0
    ),
    ('mr', 'mr', 'मराठी', 'Marathi', NULL, 0),
    (
        'mr_IN',
        'mr',
        'मराठी (भारत)',
        'Marathi (India)',
        NULL,
        0
    ),
    ('ms', 'ms', 'Melayu', 'Malay', NULL, 0),
    (
        'ms_BN',
        'ms',
        'Melayu (Brunei)',
        'Malay (Brunei)',
        NULL,
        0
    ),
    (
        'ms_ID',
        'ms',
        'Melayu (Indonesia)',
        'Malay (Indonesia)',
        NULL,
        0
    ),
    (
        'ms_MY',
        'ms',
        'Melayu (Malaysia)',
        'Malay (Malaysia)',
        NULL,
        0
    ),
    (
        'ms_SG',
        'ms',
        'Melayu (Singapura)',
        'Malay (Singapore)',
        NULL,
        0
    ),
    ('mt', 'mt', 'Malti', 'Maltese', NULL, 0),
    (
        'mt_MT',
        'mt',
        'Malti (Malta)',
        'Maltese (Malta)',
        NULL,
        0
    ),
    ('mua', 'mua', 'MUNDAŊ', 'Mundang', NULL, 0),
    (
        'mua_CM',
        'mua',
        'MUNDAŊ (kameruŋ)',
        'Mundang (Cameroon)',
        NULL,
        0
    ),
    ('my', 'my', 'မြန်မာ', 'Burmese', NULL, 0),
    (
        'my_MM',
        'my',
        'မြန်မာ (မြန်မာ)',
        'Burmese (Myanmar [Burma])',
        NULL,
        0
    ),
    ('mzn', 'mzn', 'مازرونی', 'Mazanderani', NULL, 0),
    (
        'mzn_IR',
        'mzn',
        'مازرونی (ایران)',
        'Mazanderani (Iran)',
        NULL,
        0
    ),
    ('naq', 'naq', 'Khoekhoegowab', 'Nama', NULL, 0),
    (
        'naq_NA',
        'naq',
        'Khoekhoegowab (Namibiab)',
        'Nama (Namibia)',
        NULL,
        0
    ),
    (
        'nb',
        'nb',
        'norsk bokmål',
        'Norwegian Bokmål',
        NULL,
        0
    ),
    (
        'nb_NO',
        'nb',
        'norsk bokmål (Norge)',
        'Norwegian Bokmål (Norway)',
        NULL,
        0
    ),
    (
        'nb_SJ',
        'nb',
        'norsk bokmål (Svalbard og Jan Mayen)',
        'Norwegian Bokmål (Svalbard & Jan Mayen)',
        NULL,
        0
    ),
    (
        'nd',
        'nd',
        'isiNdebele',
        'North Ndebele',
        NULL,
        0
    ),
    (
        'nd_ZW',
        'nd',
        'isiNdebele (Zimbabwe)',
        'North Ndebele (Zimbabwe)',
        NULL,
        0
    ),
    ('ne', 'ne', 'नेपाली', 'Nepali', NULL, 0),
    (
        'ne_IN',
        'ne',
        'नेपाली (भारत)',
        'Nepali (India)',
        NULL,
        0
    ),
    (
        'ne_NP',
        'ne',
        'नेपाली (नेपाल)',
        'Nepali (Nepal)',
        NULL,
        0
    ),
    ('nl', 'nl', 'Nederlands', 'Dutch', NULL, 0),
    (
        'nl_AW',
        'nl',
        'Nederlands (Aruba)',
        'Dutch (Aruba)',
        NULL,
        0
    ),
    (
        'nl_BE',
        'nl',
        'Nederlands (België)',
        'Dutch (Belgium)',
        NULL,
        0
    ),
    (
        'nl_BQ',
        'nl',
        'Nederlands (Caribisch Nederland)',
        'Dutch (Caribbean Netherlands)',
        NULL,
        0
    ),
    (
        'nl_CW',
        'nl',
        'Nederlands (Curaçao)',
        'Dutch (Curaçao)',
        NULL,
        0
    ),
    (
        'nl_NL',
        'nl',
        'Nederlands (Nederland)',
        'Dutch (Netherlands)',
        NULL,
        0
    ),
    (
        'nl_SR',
        'nl',
        'Nederlands (Suriname)',
        'Dutch (Suriname)',
        NULL,
        0
    ),
    (
        'nl_SX',
        'nl',
        'Nederlands (Sint-Maarten)',
        'Dutch (Sint Maarten)',
        NULL,
        0
    ),
    ('nmg', 'nmg', 'nmg', 'Kwasio', NULL, 0),
    (
        'nmg_CM',
        'nmg',
        'nmg (Kamerun)',
        'Kwasio (Cameroon)',
        NULL,
        0
    ),
    (
        'nn',
        'nn',
        'norsk nynorsk',
        'Norwegian Nynorsk',
        NULL,
        0
    ),
    (
        'nn_NO',
        'nn',
        'norsk nynorsk (Noreg)',
        'Norwegian Nynorsk (Norway)',
        NULL,
        0
    ),
    (
        'nnh',
        'nnh',
        'Shwóŋò ngiembɔɔn',
        'Ngiemboon',
        NULL,
        0
    ),
    (
        'nnh_CM',
        'nnh',
        'Shwóŋò ngiembɔɔn (Kàmalûm)',
        'Ngiemboon (Cameroon)',
        NULL,
        0
    ),
    ('no', 'no', 'norsk', 'Norwegian', NULL, 0),
    ('nus', 'nus', 'Thok Nath', 'Nuer', NULL, 0),
    (
        'nus_SS',
        'nus',
        'Thok Nath (SS)',
        'Nuer (South Sudan)',
        NULL,
        0
    ),
    ('nyn', 'nyn', 'Runyankore', 'Nyankole', NULL, 0),
    (
        'nyn_UG',
        'nyn',
        'Runyankore (Uganda)',
        'Nyankole (Uganda)',
        NULL,
        0
    ),
    ('om', 'om', 'Oromoo', 'Oromo', NULL, 0),
    (
        'om_ET',
        'om',
        'Oromoo (Itoophiyaa)',
        'Oromo (Ethiopia)',
        NULL,
        0
    ),
    (
        'om_KE',
        'om',
        'Oromoo (Keeniyaa)',
        'Oromo (Kenya)',
        NULL,
        0
    ),
    ('or', 'or', 'ଓଡ଼ିଆ', 'Odia', NULL, 0),
    (
        'or_IN',
        'or',
        'ଓଡ଼ିଆ (ଭାରତ)',
        'Odia (India)',
        NULL,
        0
    ),
    ('os', 'os', 'ирон', 'Ossetic', NULL, 0),
    (
        'os_GE',
        'os',
        'ирон (Гуырдзыстон)',
        'Ossetic (Georgia)',
        NULL,
        0
    ),
    (
        'os_RU',
        'os',
        'ирон (Уӕрӕсе)',
        'Ossetic (Russia)',
        NULL,
        0
    ),
    ('pa', 'pa', 'ਪੰਜਾਬੀ', 'Punjabi', NULL, 0),
    (
        'pa_Arab',
        'pa',
        'پنجابی (عربی)',
        'Punjabi (Arabic)',
        NULL,
        0
    ),
    (
        'pa_Arab_PK',
        'pa',
        'پنجابی (عربی, پاکستان)',
        'Punjabi (Arabic, Pakistan)',
        NULL,
        0
    ),
    (
        'pa_Guru',
        'pa',
        'ਪੰਜਾਬੀ (ਗੁਰਮੁਖੀ)',
        'Punjabi (Gurmukhi)',
        NULL,
        0
    ),
    (
        'pa_Guru_IN',
        'pa',
        'ਪੰਜਾਬੀ (ਗੁਰਮੁਖੀ, ਭਾਰਤ)',
        'Punjabi (Gurmukhi, India)',
        NULL,
        0
    ),
    (
        'pcm',
        'pcm',
        'Naijíriá Píjin',
        'Nigerian Pidgin',
        NULL,
        0
    ),
    (
        'pcm_NG',
        'pcm',
        'Naijíriá Píjin (Naijíria)',
        'Nigerian Pidgin (Nigeria)',
        NULL,
        0
    ),
    ('pl', 'pl', 'polski', 'Polish', NULL, 0),
    (
        'pl_PL',
        'pl',
        'polski (Polska)',
        'Polish (Poland)',
        NULL,
        0
    ),
    ('ps', 'ps', 'پښتو', 'Pashto', NULL, 0),
    (
        'ps_AF',
        'ps',
        'پښتو (افغانستان)',
        'Pashto (Afghanistan)',
        NULL,
        0
    ),
    (
        'ps_PK',
        'ps',
        'پښتو (پاکستان)',
        'Pashto (Pakistan)',
        NULL,
        0
    ),
    ('pt', 'pt', 'português', 'Portuguese', NULL, 0),
    (
        'pt_AO',
        'pt',
        'português (Angola)',
        'Portuguese (Angola)',
        NULL,
        0
    ),
    (
        'pt_BR',
        'pt',
        'português (Brasil)',
        'Portuguese (Brazil)',
        NULL,
        0
    ),
    (
        'pt_CH',
        'pt',
        'português (Suíça)',
        'Portuguese (Switzerland)',
        NULL,
        0
    ),
    (
        'pt_CV',
        'pt',
        'português (Cabo Verde)',
        'Portuguese (Cape Verde)',
        NULL,
        0
    ),
    (
        'pt_GQ',
        'pt',
        'português (Guiné Equatorial)',
        'Portuguese (Equatorial Guinea)',
        NULL,
        0
    ),
    (
        'pt_GW',
        'pt',
        'português (Guiné-Bissau)',
        'Portuguese (Guinea-Bissau)',
        NULL,
        0
    ),
    (
        'pt_LU',
        'pt',
        'português (Luxemburgo)',
        'Portuguese (Luxembourg)',
        NULL,
        0
    ),
    (
        'pt_MO',
        'pt',
        'português (Macau, RAE da China)',
        'Portuguese (Macao SAR China)',
        NULL,
        0
    ),
    (
        'pt_MZ',
        'pt',
        'português (Moçambique)',
        'Portuguese (Mozambique)',
        NULL,
        0
    ),
    (
        'pt_PT',
        'pt',
        'português (Portugal)',
        'Portuguese (Portugal)',
        NULL,
        0
    ),
    (
        'pt_ST',
        'pt',
        'português (São Tomé e Príncipe)',
        'Portuguese (São Tomé & Príncipe)',
        NULL,
        0
    ),
    (
        'pt_TL',
        'pt',
        'português (Timor-Leste)',
        'Portuguese (Timor-Leste)',
        NULL,
        0
    ),
    ('qu', 'qu', 'Runasimi', 'Quechua', NULL, 0),
    (
        'qu_BO',
        'qu',
        'Runasimi (Bolivia)',
        'Quechua (Bolivia)',
        NULL,
        0
    ),
    (
        'qu_EC',
        'qu',
        'Runasimi (Ecuador)',
        'Quechua (Ecuador)',
        NULL,
        0
    ),
    (
        'qu_PE',
        'qu',
        'Runasimi (Perú)',
        'Quechua (Peru)',
        NULL,
        0
    ),
    ('rm', 'rm', 'rumantsch', 'Romansh', NULL, 0),
    (
        'rm_CH',
        'rm',
        'rumantsch (Svizra)',
        'Romansh (Switzerland)',
        NULL,
        0
    ),
    ('rn', 'rn', 'Ikirundi', 'Rundi', NULL, 0),
    (
        'rn_BI',
        'rn',
        'Ikirundi (Uburundi)',
        'Rundi (Burundi)',
        NULL,
        0
    ),
    ('ro', 'ro', 'română', 'Romanian', NULL, 0),
    (
        'ro_MD',
        'ro',
        'română (Republica Moldova)',
        'Romanian (Moldova)',
        NULL,
        0
    ),
    (
        'ro_RO',
        'ro',
        'română (România)',
        'Romanian (Romania)',
        NULL,
        0
    ),
    ('rof', 'rof', 'Kihorombo', 'Rombo', NULL, 0),
    (
        'rof_TZ',
        'rof',
        'Kihorombo (Tanzania)',
        'Rombo (Tanzania)',
        NULL,
        0
    ),
    ('ru', 'ru', 'русский', 'Russian', NULL, 0),
    (
        'ru_BY',
        'ru',
        'русский (Беларусь)',
        'Russian (Belarus)',
        NULL,
        0
    ),
    (
        'ru_KG',
        'ru',
        'русский (Киргизия)',
        'Russian (Kyrgyzstan)',
        NULL,
        0
    ),
    (
        'ru_KZ',
        'ru',
        'русский (Казахстан)',
        'Russian (Kazakhstan)',
        NULL,
        0
    ),
    (
        'ru_MD',
        'ru',
        'русский (Молдова)',
        'Russian (Moldova)',
        NULL,
        0
    ),
    (
        'ru_RU',
        'ru',
        'русский (Россия)',
        'Russian (Russia)',
        NULL,
        0
    ),
    (
        'ru_UA',
        'ru',
        'русский (Украина)',
        'Russian (Ukraine)',
        NULL,
        0
    ),
    ('rw', 'rw', 'Kinyarwanda', 'Kinyarwanda', NULL, 0),
    (
        'rw_RW',
        'rw',
        'Kinyarwanda (U Rwanda)',
        'Kinyarwanda (Rwanda)',
        NULL,
        0
    ),
    ('rwk', 'rwk', 'Kiruwa', 'Rwa', NULL, 0),
    (
        'rwk_TZ',
        'rwk',
        'Kiruwa (Tanzania)',
        'Rwa (Tanzania)',
        NULL,
        0
    ),
    ('sa', 'sa', 'संस्कृत भाषा', 'Sanskrit', NULL, 0),
    (
        'sa_IN',
        'sa',
        'संस्कृत भाषा (भारतः)',
        'Sanskrit (India)',
        NULL,
        0
    ),
    ('sah', 'sah', 'саха тыла', 'Sakha', NULL, 0),
    (
        'sah_RU',
        'sah',
        'саха тыла (Арассыыйа)',
        'Sakha (Russia)',
        NULL,
        0
    ),
    ('saq', 'saq', 'Kisampur', 'Samburu', NULL, 0),
    (
        'saq_KE',
        'saq',
        'Kisampur (Kenya)',
        'Samburu (Kenya)',
        NULL,
        0
    ),
    ('sat', 'sat', 'ᱥᱟᱱᱛᱟᱲᱤ', 'Santali', NULL, 0),
    (
        'sat_Olck',
        'sat',
        'ᱥᱟᱱᱛᱟᱲᱤ (ᱚᱞ ᱪᱤᱠᱤ)',
        'Santali (Ol Chiki)',
        NULL,
        0
    ),
    (
        'sat_Olck_IN',
        'sat',
        'ᱥᱟᱱᱛᱟᱲᱤ (ᱚᱞ ᱪᱤᱠᱤ, ᱤᱱᱰᱤᱭᱟ)',
        'Santali (Ol Chiki, India)',
        NULL,
        0
    ),
    ('sbp', 'sbp', 'Ishisangu', 'Sangu', NULL, 0),
    (
        'sbp_TZ',
        'sbp',
        'Ishisangu (Tansaniya)',
        'Sangu (Tanzania)',
        NULL,
        0
    ),
    ('sd', 'sd', 'سنڌي', 'Sindhi', NULL, 0),
    (
        'sd_Arab',
        'sd',
        'سنڌي (عربي)',
        'Sindhi (Arabic)',
        NULL,
        0
    ),
    (
        'sd_Arab_PK',
        'sd',
        'سنڌي (عربي, پاڪستان)',
        'Sindhi (Arabic, Pakistan)',
        NULL,
        0
    ),
    (
        'sd_Deva',
        'sd',
        'सिन्धी (देवनागिरी)',
        'Sindhi (Devanagari)',
        NULL,
        0
    ),
    (
        'sd_Deva_IN',
        'sd',
        'सिन्धी (देवनागिरी, भारत)',
        'Sindhi (Devanagari, India)',
        NULL,
        0
    ),
    (
        'se',
        'se',
        'davvisámegiella',
        'Northern Sami',
        NULL,
        0
    ),
    (
        'se_FI',
        'se',
        'davvisámegiella (Suopma)',
        'Northern Sami (Finland)',
        NULL,
        0
    ),
    (
        'se_NO',
        'se',
        'davvisámegiella (Norga)',
        'Northern Sami (Norway)',
        NULL,
        0
    ),
    (
        'se_SE',
        'se',
        'davvisámegiella (Ruoŧŧa)',
        'Northern Sami (Sweden)',
        NULL,
        0
    ),
    ('seh', 'seh', 'sena', 'Sena', NULL, 0),
    (
        'seh_MZ',
        'seh',
        'sena (Moçambique)',
        'Sena (Mozambique)',
        NULL,
        0
    ),
    (
        'ses',
        'ses',
        'Koyraboro senni',
        'Koyraboro Senni',
        NULL,
        0
    ),
    (
        'ses_ML',
        'ses',
        'Koyraboro senni (Maali)',
        'Koyraboro Senni (Mali)',
        NULL,
        0
    ),
    ('sg', 'sg', 'Sängö', 'Sango', NULL, 0),
    (
        'sg_CF',
        'sg',
        'Sängö (Ködörösêse tî Bêafrîka)',
        'Sango (Central African Republic)',
        NULL,
        0
    ),
    ('shi', 'shi', 'ⵜⴰⵛⵍⵃⵉⵜ', 'Tachelhit', NULL, 0),
    (
        'shi_Latn',
        'shi',
        'Tashelḥiyt (Latn)',
        'Tachelhit (Latin)',
        NULL,
        0
    ),
    (
        'shi_Latn_MA',
        'shi',
        'Tashelḥiyt (Latn, lmɣrib)',
        'Tachelhit (Latin, Morocco)',
        NULL,
        0
    ),
    (
        'shi_Tfng',
        'shi',
        'ⵜⴰⵛⵍⵃⵉⵜ (Tfng)',
        'Tachelhit (Tifinagh)',
        NULL,
        0
    ),
    (
        'shi_Tfng_MA',
        'shi',
        'ⵜⴰⵛⵍⵃⵉⵜ (Tfng, ⵍⵎⵖⵔⵉⴱ)',
        'Tachelhit (Tifinagh, Morocco)',
        NULL,
        0
    ),
    ('si', 'si', 'සිංහල', 'Sinhala', NULL, 0),
    (
        'si_LK',
        'si',
        'සිංහල (ශ්‍රී ලංකාව)',
        'Sinhala (Sri Lanka)',
        NULL,
        0
    ),
    ('sk', 'sk', 'slovenčina', 'Slovak', NULL, 0),
    (
        'sk_SK',
        'sk',
        'slovenčina (Slovensko)',
        'Slovak (Slovakia)',
        NULL,
        0
    ),
    ('sl', 'sl', 'slovenščina', 'Slovenian', NULL, 0),
    (
        'sl_SI',
        'sl',
        'slovenščina (Slovenija)',
        'Slovenian (Slovenia)',
        NULL,
        0
    ),
    (
        'smn',
        'smn',
        'anarâškielâ',
        'Inari Sami',
        NULL,
        0
    ),
    (
        'smn_FI',
        'smn',
        'anarâškielâ (Suomâ)',
        'Inari Sami (Finland)',
        NULL,
        0
    ),
    ('sn', 'sn', 'chiShona', 'Shona', NULL, 0),
    (
        'sn_ZW',
        'sn',
        'chiShona (Zimbabwe)',
        'Shona (Zimbabwe)',
        NULL,
        0
    ),
    ('so', 'so', 'Soomaali', 'Somali', NULL, 0),
    (
        'so_DJ',
        'so',
        'Soomaali (Jabuuti)',
        'Somali (Djibouti)',
        NULL,
        0
    ),
    (
        'so_ET',
        'so',
        'Soomaali (Itoobiya)',
        'Somali (Ethiopia)',
        NULL,
        0
    ),
    (
        'so_KE',
        'so',
        'Soomaali (Kenya)',
        'Somali (Kenya)',
        NULL,
        0
    ),
    (
        'so_SO',
        'so',
        'Soomaali (Soomaaliya)',
        'Somali (Somalia)',
        NULL,
        0
    ),
    ('sq', 'sq', 'shqip', 'Albanian', NULL, 0),
    (
        'sq_AL',
        'sq',
        'shqip (Shqipëri)',
        'Albanian (Albania)',
        NULL,
        0
    ),
    (
        'sq_MK',
        'sq',
        'shqip (Maqedonia e Veriut)',
        'Albanian (North Macedonia)',
        NULL,
        0
    ),
    (
        'sq_XK',
        'sq',
        'shqip (Kosovë)',
        'Albanian (Kosovo)',
        NULL,
        0
    ),
    ('sr', 'sr', 'српски', 'Serbian', NULL, 0),
    (
        'sr_Cyrl',
        'sr',
        'српски (ћирилица)',
        'Serbian (Cyrillic)',
        NULL,
        0
    ),
    (
        'sr_Cyrl_BA',
        'sr',
        'српски (ћирилица, Босна и Херцеговина)',
        'Serbian (Cyrillic, Bosnia & Herzegovina)',
        NULL,
        0
    ),
    (
        'sr_Cyrl_ME',
        'sr',
        'српски (ћирилица, Црна Гора)',
        'Serbian (Cyrillic, Montenegro)',
        NULL,
        0
    ),
    (
        'sr_Cyrl_RS',
        'sr',
        'српски (ћирилица, Србија)',
        'Serbian (Cyrillic, Serbia)',
        NULL,
        0
    ),
    (
        'sr_Cyrl_XK',
        'sr',
        'српски (ћирилица, Косово)',
        'Serbian (Cyrillic, Kosovo)',
        NULL,
        0
    ),
    (
        'sr_Latn',
        'sr',
        'srpski (latinica)',
        'Serbian (Latin)',
        NULL,
        0
    ),
    (
        'sr_Latn_BA',
        'sr',
        'srpski (latinica, Bosna i Hercegovina)',
        'Serbian (Latin, Bosnia & Herzegovina)',
        NULL,
        0
    ),
    (
        'sr_Latn_ME',
        'sr',
        'srpski (latinica, Crna Gora)',
        'Serbian (Latin, Montenegro)',
        NULL,
        0
    ),
    (
        'sr_Latn_RS',
        'sr',
        'srpski (latinica, Srbija)',
        'Serbian (Latin, Serbia)',
        NULL,
        0
    ),
    (
        'sr_Latn_XK',
        'sr',
        'srpski (latinica, Kosovo)',
        'Serbian (Latin, Kosovo)',
        NULL,
        0
    ),
    ('su', 'su', 'Basa Sunda', 'Sundanese', NULL, 0),
    (
        'su_Latn',
        'su',
        'Basa Sunda (Latin)',
        'Sundanese (Latin)',
        NULL,
        0
    ),
    (
        'su_Latn_ID',
        'su',
        'Basa Sunda (Latin, ID)',
        'Sundanese (Latin, Indonesia)',
        NULL,
        0
    ),
    ('sv', 'sv', 'svenska', 'Swedish', NULL, 0),
    (
        'sv_AX',
        'sv',
        'svenska (Åland)',
        'Swedish (Åland Islands)',
        NULL,
        0
    ),
    (
        'sv_FI',
        'sv',
        'svenska (Finland)',
        'Swedish (Finland)',
        NULL,
        0
    ),
    (
        'sv_SE',
        'sv',
        'svenska (Sverige)',
        'Swedish (Sweden)',
        NULL,
        0
    ),
    ('sw', 'sw', 'Kiswahili', 'Swahili', NULL, 0),
    (
        'sw_CD',
        'sw',
        'Kiswahili (Jamhuri ya Kidemokrasia ya Kongo)',
        'Swahili (Congo - Kinshasa)',
        NULL,
        0
    ),
    (
        'sw_KE',
        'sw',
        'Kiswahili (Kenya)',
        'Swahili (Kenya)',
        NULL,
        0
    ),
    (
        'sw_TZ',
        'sw',
        'Kiswahili (Tanzania)',
        'Swahili (Tanzania)',
        NULL,
        0
    ),
    (
        'sw_UG',
        'sw',
        'Kiswahili (Uganda)',
        'Swahili (Uganda)',
        NULL,
        0
    ),
    ('ta', 'ta', 'தமிழ்', 'Tamil', NULL, 0),
    (
        'ta_IN',
        'ta',
        'தமிழ் (இந்தியா)',
        'Tamil (India)',
        NULL,
        0
    ),
    (
        'ta_LK',
        'ta',
        'தமிழ் (இலங்கை)',
        'Tamil (Sri Lanka)',
        NULL,
        0
    ),
    (
        'ta_MY',
        'ta',
        'தமிழ் (மலேசியா)',
        'Tamil (Malaysia)',
        NULL,
        0
    ),
    (
        'ta_SG',
        'ta',
        'தமிழ் (சிங்கப்பூர்)',
        'Tamil (Singapore)',
        NULL,
        0
    ),
    ('te', 'te', 'తెలుగు', 'Telugu', NULL, 0),
    (
        'te_IN',
        'te',
        'తెలుగు (భారతదేశం)',
        'Telugu (India)',
        NULL,
        0
    ),
    ('teo', 'teo', 'Kiteso', 'Teso', NULL, 0),
    (
        'teo_KE',
        'teo',
        'Kiteso (Kenia)',
        'Teso (Kenya)',
        NULL,
        0
    ),
    (
        'teo_UG',
        'teo',
        'Kiteso (Uganda)',
        'Teso (Uganda)',
        NULL,
        0
    ),
    ('tg', 'tg', 'тоҷикӣ', 'Tajik', NULL, 0),
    (
        'tg_TJ',
        'tg',
        'тоҷикӣ (Тоҷикистон)',
        'Tajik (Tajikistan)',
        NULL,
        0
    ),
    ('th', 'th', 'ไทย', 'Thai', NULL, 0),
    (
        'th_TH',
        'th',
        'ไทย (ไทย)',
        'Thai (Thailand)',
        NULL,
        0
    ),
    ('ti', 'ti', 'ትግር', 'Tigrinya', NULL, 0),
    (
        'ti_ER',
        'ti',
        'ትግር (ኤርትራ)',
        'Tigrinya (Eritrea)',
        NULL,
        0
    ),
    (
        'ti_ET',
        'ti',
        'ትግር (ኢትዮጵያ)',
        'Tigrinya (Ethiopia)',
        NULL,
        0
    ),
    ('tk', 'tk', 'türkmen dili', 'Turkmen', NULL, 0),
    (
        'tk_TM',
        'tk',
        'türkmen dili (Türkmenistan)',
        'Turkmen (Turkmenistan)',
        NULL,
        0
    ),
    ('to', 'to', 'lea fakatonga', 'Tongan', NULL, 0),
    (
        'to_TO',
        'to',
        'lea fakatonga (Tonga)',
        'Tongan (Tonga)',
        NULL,
        0
    ),
    ('tr', 'tr', 'Türkçe', 'Turkish', NULL, 0),
    (
        'tr_CY',
        'tr',
        'Türkçe (Kıbrıs)',
        'Turkish (Cyprus)',
        NULL,
        0
    ),
    (
        'tr_TR',
        'tr',
        'Türkçe (Türkiye)',
        'Turkish (Turkey)',
        NULL,
        0
    ),
    ('tt', 'tt', 'татар', 'Tatar', NULL, 0),
    (
        'tt_RU',
        'tt',
        'татар (Россия)',
        'Tatar (Russia)',
        NULL,
        0
    ),
    ('twq', 'twq', 'Tasawaq senni', 'Tasawaq', NULL, 0),
    (
        'twq_NE',
        'twq',
        'Tasawaq senni (Nižer)',
        'Tasawaq (Niger)',
        NULL,
        0
    ),
    (
        'tzm',
        'tzm',
        'Tamaziɣt n laṭlaṣ',
        'Central Atlas Tamazight',
        NULL,
        0
    ),
    (
        'tzm_MA',
        'tzm',
        'Tamaziɣt n laṭlaṣ (Meṛṛuk)',
        'Central Atlas Tamazight (Morocco)',
        NULL,
        0
    ),
    ('ug', 'ug', 'ئۇيغۇرچە', 'Uyghur', NULL, 0),
    (
        'ug_CN',
        'ug',
        'ئۇيغۇرچە (جۇڭگو)',
        'Uyghur (China)',
        NULL,
        0
    ),
    ('uk', 'uk', 'українська', 'Ukrainian', NULL, 0),
    (
        'uk_UA',
        'uk',
        'українська (Україна)',
        'Ukrainian (Ukraine)',
        NULL,
        0
    ),
    ('ur', 'ur', 'اردو', 'Urdu', NULL, 0),
    (
        'ur_IN',
        'ur',
        'اردو (بھارت)',
        'Urdu (India)',
        NULL,
        0
    ),
    (
        'ur_PK',
        'ur',
        'اردو (پاکستان)',
        'Urdu (Pakistan)',
        NULL,
        0
    ),
    ('uz', 'uz', 'o‘zbek', 'Uzbek', NULL, 0),
    (
        'uz_Arab',
        'uz',
        'اوزبیک (عربی)',
        'Uzbek (Arabic)',
        NULL,
        0
    ),
    (
        'uz_Arab_AF',
        'uz',
        'اوزبیک (عربی, افغانستان)',
        'Uzbek (Arabic, Afghanistan)',
        NULL,
        0
    ),
    (
        'uz_Cyrl',
        'uz',
        'ўзбекча (Кирил)',
        'Uzbek (Cyrillic)',
        NULL,
        0
    ),
    (
        'uz_Cyrl_UZ',
        'uz',
        'ўзбекча (Кирил, Ўзбекистон)',
        'Uzbek (Cyrillic, Uzbekistan)',
        NULL,
        0
    ),
    (
        'uz_Latn',
        'uz',
        'o‘zbek (lotin)',
        'Uzbek (Latin)',
        NULL,
        0
    ),
    (
        'uz_Latn_UZ',
        'uz',
        'o‘zbek (lotin, Oʻzbekiston)',
        'Uzbek (Latin, Uzbekistan)',
        NULL,
        0
    ),
    ('vai', 'vai', 'ꕙꔤ', 'Vai', NULL, 0),
    (
        'vai_Latn',
        'vai',
        'Vai (Latn)',
        'Vai (Latin)',
        NULL,
        0
    ),
    (
        'vai_Latn_LR',
        'vai',
        'Vai (Latn, Laibhiya)',
        'Vai (Latin, Liberia)',
        NULL,
        0
    ),
    (
        'vai_Vaii',
        'vai',
        'ꕙꔤ (Vaii)',
        'Vai (Vai)',
        NULL,
        0
    ),
    (
        'vai_Vaii_LR',
        'vai',
        'ꕙꔤ (Vaii, ꕞꔤꔫꕩ)',
        'Vai (Vai, Liberia)',
        NULL,
        0
    ),
    ('vi', 'vi', 'Tiếng Việt', 'Vietnamese', NULL, 0),
    (
        'vi_VN',
        'vi',
        'Tiếng Việt (Việt Nam)',
        'Vietnamese (Vietnam)',
        NULL,
        0
    ),
    ('vun', 'vun', 'Kyivunjo', 'Vunjo', NULL, 0),
    (
        'vun_TZ',
        'vun',
        'Kyivunjo (Tanzania)',
        'Vunjo (Tanzania)',
        NULL,
        0
    ),
    ('wae', 'wae', 'Walser', 'Walser', NULL, 0),
    (
        'wae_CH',
        'wae',
        'Walser (Schwiz)',
        'Walser (Switzerland)',
        NULL,
        0
    ),
    ('wo', 'wo', 'Wolof', 'Wolof', NULL, 0),
    (
        'wo_SN',
        'wo',
        'Wolof (Senegaal)',
        'Wolof (Senegal)',
        NULL,
        0
    ),
    ('xh', 'xh', 'isiXhosa', 'Xhosa', NULL, 0),
    (
        'xh_ZA',
        'xh',
        'isiXhosa (eMzantsi Afrika)',
        'Xhosa (South Africa)',
        NULL,
        0
    ),
    ('xog', 'xog', 'Olusoga', 'Soga', NULL, 0),
    (
        'xog_UG',
        'xog',
        'Olusoga (Yuganda)',
        'Soga (Uganda)',
        NULL,
        0
    ),
    ('yav', 'yav', 'nuasue', 'Yangben', NULL, 0),
    (
        'yav_CM',
        'yav',
        'nuasue (Kemelún)',
        'Yangben (Cameroon)',
        NULL,
        0
    ),
    ('yi', 'yi', 'ייִדיש', 'Yiddish', NULL, 0),
    (
        'yi_001',
        'yi',
        'ייִדיש (וועלט)',
        'Yiddish (world)',
        NULL,
        0
    ),
    ('yo', 'yo', 'Èdè Yorùbá', 'Yoruba', NULL, 0),
    (
        'yo_BJ',
        'yo',
        'Èdè Yorùbá (Bɛ̀nɛ̀)',
        'Yoruba (Benin)',
        NULL,
        0
    ),
    (
        'yo_NG',
        'yo',
        'Èdè Yorùbá (Nàìjíríà)',
        'Yoruba (Nigeria)',
        NULL,
        0
    ),
    ('yue', 'yue', '粵語', 'Cantonese', NULL, 0),
    (
        'yue_Hans',
        'yue',
        '粤语 (简体)',
        'Cantonese (Simplified)',
        NULL,
        0
    ),
    (
        'yue_Hans_CN',
        'yue',
        '粤语 (简体，中华人民共和国)',
        'Cantonese (Simplified, China)',
        NULL,
        0
    ),
    (
        'yue_Hant',
        'yue',
        '粵語 (繁體)',
        'Cantonese (Traditional)',
        NULL,
        0
    ),
    (
        'yue_Hant_HK',
        'yue',
        '粵語 (繁體，中華人民共和國香港特別行政區)',
        'Cantonese (Traditional, Hong Kong SAR China)',
        NULL,
        0
    ),
    (
        'zgh',
        'zgh',
        'ⵜⴰⵎⴰⵣⵉⵖⵜ',
        'Standard Moroccan Tamazight',
        NULL,
        0
    ),
    (
        'zgh_MA',
        'zgh',
        'ⵜⴰⵎⴰⵣⵉⵖⵜ (ⵍⵎⵖⵔⵉⴱ)',
        'Standard Moroccan Tamazight (Morocco)',
        NULL,
        0
    ),
    ('zh', 'zh', '中文', 'Chinese', NULL, 0),
    (
        'zh_Hans',
        'zh',
        '中文（简体）',
        'Chinese (Simplified)',
        NULL,
        0
    ),
    (
        'zh_Hans_CN',
        'zh',
        '中文（简体，中国）',
        'Chinese (Simplified, China)',
        NULL,
        0
    ),
    (
        'zh_Hans_HK',
        'zh',
        '中文（简体，中国香港特别行政区）',
        'Chinese (Simplified, Hong Kong SAR China)',
        NULL,
        0
    ),
    (
        'zh_Hans_MO',
        'zh',
        '中文（简体，中国澳门特别行政区）',
        'Chinese (Simplified, Macao SAR China)',
        NULL,
        0
    ),
    (
        'zh_Hans_SG',
        'zh',
        '中文（简体，新加坡）',
        'Chinese (Simplified, Singapore)',
        NULL,
        0
    ),
    (
        'zh_Hant',
        'zh',
        '中文（繁體）',
        'Chinese (Traditional)',
        NULL,
        0
    ),
    (
        'zh_Hant_HK',
        'zh',
        '中文（繁體字，中國香港特別行政區）',
        'Chinese (Traditional, Hong Kong SAR China)',
        NULL,
        0
    ),
    (
        'zh_Hant_MO',
        'zh',
        '中文（繁體字，中國澳門特別行政區）',
        'Chinese (Traditional, Macao SAR China)',
        NULL,
        0
    ),
    (
        'zh_Hant_TW',
        'zh',
        '中文（繁體，台灣）',
        'Chinese (Traditional, Taiwan)',
        NULL,
        0
    ),
    ('zu', 'zu', 'isiZulu', 'Zulu', NULL, 0),
    (
        'zu_ZA',
        'zu',
        'isiZulu (iNingizimu Afrika)',
        'Zulu (South Africa)',
        NULL,
        0
    );

/*!40000 ALTER TABLE `locales` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `maileraccounts`
--
DROP TABLE IF EXISTS `maileraccounts`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `maileraccounts` (
        `id` SMALLINT (5) unsigned NOT NULL AUTO_INCREMENT,
        `mailer` VARCHAR(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'smtp',
        `smtpauth` VARCHAR(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'true',
        `port` SMALLINT (5) unsigned NOT NULL,
        `smtpsecure` VARCHAR(45) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tls',
        `username` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `password` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `host` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `debug` tinyint (1) unsigned NOT NULL DEFAULT '0',
        UNIQUE KEY `id` (`id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maileraccounts`
--
LOCK TABLES `maileraccounts` WRITE;

/*!40000 ALTER TABLE `maileraccounts` DISABLE KEYS */;

/*!40000 ALTER TABLE `maileraccounts` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `maileraddresses`
--
DROP TABLE IF EXISTS `maileraddresses`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `maileraddresses` (
        `id` INT (10) unsigned NOT NULL AUTO_INCREMENT,
        `mailerqueue_id` INT (10) unsigned NOT NULL,
        `maileraddresstype_id` tinyint (3) unsigned NOT NULL,
        `address` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `mailer_id` (`mailerqueue_id`),
        KEY `mailertype_id` (`maileraddresstype_id`),
        CONSTRAINT `maileraddresses_ibfk_2` FOREIGN KEY (`maileraddresstype_id`) REFERENCES `maileradresstypes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `maileraddresses_ibfk_3` FOREIGN KEY (`mailerqueue_id`) REFERENCES `mailerqueue` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maileraddresses`
--
LOCK TABLES `maileraddresses` WRITE;

/*!40000 ALTER TABLE `maileraddresses` DISABLE KEYS */;

/*!40000 ALTER TABLE `maileraddresses` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `maileradresstypes`
--
DROP TABLE IF EXISTS `maileradresstypes`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `maileradresstypes` (
        `id` tinyint (3) unsigned NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(45) COLLATE utf8mb4_unicode_ci NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB AUTO_INCREMENT = 5 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maileradresstypes`
--
LOCK TABLES `maileradresstypes` WRITE;

/*!40000 ALTER TABLE `maileradresstypes` DISABLE KEYS */;

INSERT INTO
    `maileradresstypes`
VALUES
    (1, 'ReplyTo'),
    (2, 'To'),
    (3, 'CC'),
    (4, 'BCC');

/*!40000 ALTER TABLE `maileradresstypes` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `mailerqueue`
--
DROP TABLE IF EXISTS `mailerqueue`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `mailerqueue` (
        `id` INT (10) unsigned NOT NULL AUTO_INCREMENT,
        `fromname` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `subject` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
        `altbody` text COLLATE utf8mb4_unicode_ci NOT NULL,
        `maileraccount_id` SMALLINT (5) unsigned NOT NULL,
        `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `sent` TIMESTAMP NULL DEFAULT NULL,
        `attempts` tinyint (3) unsigned NOT NULL DEFAULT '0',
        `message` text COLLATE utf8mb4_unicode_ci,
        PRIMARY KEY (`id`),
        UNIQUE KEY `id` (`id`),
        KEY `maileraccount_id` (`maileraccount_id`),
        CONSTRAINT `mailerqueue_ibfk_1` FOREIGN KEY (`maileraccount_id`) REFERENCES `maileraccounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mailerqueue`
--
LOCK TABLES `mailerqueue` WRITE;

/*!40000 ALTER TABLE `mailerqueue` DISABLE KEYS */;

/*!40000 ALTER TABLE `mailerqueue` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `passwordresets`
--
DROP TABLE IF EXISTS `passwordresets`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `passwordresets` (
        `user_id` SMALLINT (5) unsigned NOT NULL,
        `uuid` VARCHAR(36) COLLATE utf8mb4_unicode_ci NOT NULL,
        `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `attempts` tinyint (3) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`user_id`),
        UNIQUE KEY `uuid` (`uuid`),
        CONSTRAINT `passwordresets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `passwordresets`
--
LOCK TABLES `passwordresets` WRITE;

/*!40000 ALTER TABLE `passwordresets` DISABLE KEYS */;

/*!40000 ALTER TABLE `passwordresets` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `route_accesses`
--
DROP TABLE IF EXISTS `route_accesses`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `route_accesses` (
        `route_id` SMALLINT (5) unsigned NOT NULL,
        `access_id` SMALLINT (5) unsigned DEFAULT NULL,
        `param` VARCHAR(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `required` tinyint (1) unsigned NOT NULL,
        UNIQUE KEY `menu_id` (`route_id`, `access_id`),
        KEY `access_id` (`access_id`),
        CONSTRAINT `route_accesses_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `route_accesses_ibfk_2` FOREIGN KEY (`access_id`) REFERENCES `accesses` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `route_accesses`
--
LOCK TABLES `route_accesses` WRITE;

/*!40000 ALTER TABLE `route_accesses` DISABLE KEYS */;

INSERT INTO
    `route_accesses`
VALUES
    (9, 1, NULL, 1),
    (7, 1, NULL, 1);

/*!40000 ALTER TABLE `route_accesses` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `routecategories`
--
DROP TABLE IF EXISTS `routecategories`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `routecategories` (
        `id` SMALLINT (5) unsigned NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        `enabled` tinyint (3) unsigned NOT NULL DEFAULT '1',
        `lpos` SMALLINT (5) unsigned DEFAULT NULL,
        `rpos` SMALLINT (5) unsigned DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `lpos` (`lpos`),
        KEY `rpos` (`rpos`)
    ) ENGINE = InnoDB AUTO_INCREMENT = 5 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `routecategories`
--
LOCK TABLES `routecategories` WRITE;

/*!40000 ALTER TABLE `routecategories` DISABLE KEYS */;

INSERT INTO
    `routecategories`
VALUES
    (0, '/', 1, 1, 10),
    (1, 'Development', 1, 2, 3),
    (2, 'Configuration', 1, 6, 7),
    (3, 'Contacts', 1, 8, 9),
    (4, 'System', 1, 4, 5);

/*!40000 ALTER TABLE `routecategories` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `routes`
--
DROP TABLE IF EXISTS `routes`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `routes` (
        `id` SMALLINT (5) unsigned NOT NULL AUTO_INCREMENT,
        `label_name` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `type` tinyint (3) unsigned NOT NULL DEFAULT '0',
        `icon` VARCHAR(25) COLLATE utf8mb4_unicode_ci DEFAULT '',
        `position` tinyint (3) unsigned DEFAULT NULL,
        `url` VARCHAR(2047) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `file` VARCHAR(2047) COLLATE utf8mb4_unicode_ci NOT NULL,
        `method` VARCHAR(6) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'get, post, put, patch, delete or any',
        `ispublic` tinyint (1) unsigned NOT NULL DEFAULT '0',
        `isalluser` tinyint (1) unsigned NOT NULL DEFAULT '0',
        `routecategory_id` SMALLINT (5) unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        KEY `label_name` (`label_name`),
        KEY `menucategory_id` (`routecategory_id`),
        CONSTRAINT `routes_ibfk_1` FOREIGN KEY (`routecategory_id`) REFERENCES `routecategories` (`id`) ON UPDATE CASCADE
    ) ENGINE = InnoDB AUTO_INCREMENT = 18 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `routes`
--
LOCK TABLES `routes` WRITE;

/*!40000 ALTER TABLE `routes` DISABLE KEYS */;

INSERT INTO
    `routes`
VALUES
    (
        1,
        'navHome',
        1,
        'fal fa-home',
        0,
        '/',
        'pub/main/view/index.php',
        'get',
        1,
        0,
        0
    ),
    (
        2,
        'navFeatures',
        1,
        'fal fa-stars',
        1,
        '/#features',
        'pub/main/view/index.php',
        'get',
        1,
        0,
        0
    ),
    (
        3,
        'navPricing',
        1,
        'fal fa-money-bill',
        2,
        '/#pricing',
        'pub/main/view/index.php',
        'get',
        1,
        0,
        0
    ),
    (
        4,
        'navFAQs',
        1,
        'fal fa-question-circle',
        3,
        '/#faq',
        'pub/main/view/index.php',
        'get',
        1,
        0,
        0
    ),
    (
        5,
        'navAbout',
        1,
        'fal fa-info-square',
        4,
        '/#about',
        'pub/main/view/index.php',
        'get',
        1,
        0,
        0
    ),
    (
        6,
        'navSignOut',
        2,
        'fal fa-sign-out',
        3,
        '/signout',
        'view/signout.php',
        'get',
        1,
        1,
        0
    ),
    (
        7,
        'navDashboard',
        1,
        'fal fa-tachometer-alt',
        0,
        '/main',
        'usr/main/view/main.php',
        'get',
        0,
        1,
        0
    ),
    (
        8,
        'mailSender',
        0,
        '',
        NULL,
        '/mailer',
        'mailer/job/mailSender.php',
        'get',
        1,
        0,
        4
    ),
    (
        9,
        'info',
        0,
        '',
        NULL,
        '/info',
        'view/info.php',
        'get',
        0,
        0,
        0
    ),
    (
        10,
        'navContacts',
        1,
        'fal fa-address-book',
        6,
        'javascript:searchContact();',
        '',
        '',
        0,
        1,
        0
    ),
    (
        11,
        'ctrlContactSearch',
        0,
        '',
        NULL,
        '/contacts/controller/ctrlContactSearch.php',
        'usr/contacts/controller/ctrlContactSearch.php',
        'post',
        0,
        1,
        3
    ),
    (
        12,
        'ctrlContactView',
        0,
        '',
        NULL,
        '/contacts/controller/ctrlContactView.php',
        'usr/contacts/controller/ctrlContactView.php',
        'post',
        0,
        1,
        3
    ),
    (
        13,
        'ctrlContactEditForm',
        0,
        '',
        NULL,
        '/contacts/controller/ctrlContactEditForm.php',
        'usr/contacts/controller/ctrlContactEditForm.php',
        'post',
        0,
        1,
        3
    ),
    (
        14,
        'ctrlContactEdit',
        0,
        '',
        NULL,
        '/contacts/controller/ctrlContactEdit.php',
        'usr/contacts/controller/ctrlContactEdit.php',
        'post',
        0,
        1,
        3
    ),
    (
        15,
        'captchaImage',
        0,
        '',
        NULL,
        '/captcha/image',
        'assets/php/captcha/ctrlImage.php',
        'get',
        1,
        0,
        0
    ),
    (
        16,
        'ctrlUsers',
        0,
        '',
        NULL,
        '/controller/users',
        'controller/ctrlUsers.php',
        'post',
        1,
        0,
        0
    ),
    (
        17,
        'ctrlAuth',
        0,
        '',
        NULL,
        '/controller/auth',
        'controller/ctrlAuth.php',
        'post',
        1,
        0,
        0
    );

/*!40000 ALTER TABLE `routes` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `sessiondata`
--
DROP TABLE IF EXISTS `sessiondata`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `sessiondata` (
        `session_id` VARCHAR(32) NOT NULL DEFAULT '',
        `hash` VARCHAR(32) NOT NULL DEFAULT '',
        `session_data` BLOB NOT NULL,
        `session_expire` INT (11) NOT NULL DEFAULT '0',
        PRIMARY KEY (`session_id`)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessiondata`
--
LOCK TABLES `sessiondata` WRITE;

/*!40000 ALTER TABLE `sessiondata` DISABLE KEYS */;

INSERT INTO
    `sessiondata`
VALUES
    (
        '3gsr29gs5e13224pa5cmkqa4kd',
        '771c96bf955c1725837c0b055bc212ed',
        _binary 'captcha_code|s:6:\"ab6fa9\";id|N;language_id|N;first|N;last|N;email|N;token|s:0:\"\";',
        1684958745
    ),
    (
        'i9ac1b4he0o1qn2cjb0j0u1nfc',
        'b8a6b267e3972131bc0afd336b4b391b',
        _binary 'captcha_code|s:6:\"ad5759\";id|s:1:\"1\";language_id|s:2:\"en\";first|s:7:\"Patrick\";last|s:10:\"Di Martino\";email|s:20:\"patrick@aratours.com\";token|s:36:\"d8422980-fb3f-11ed-bdd0-6cf049a5a4da\";',
        1685059514
    ),
    (
        'p702pi8o1lci5v9jl81e09nbrr',
        'b8a6b267e3972131bc0afd336b4b391b',
        '',
        1684901399
    ),
    (
        'rv1glqpl643uam3pb4p7kl64qs',
        'b8a6b267e3972131bc0afd336b4b391b',
        _binary 'id|s:1:\"1\";language_id|s:2:\"en\";first|s:7:\"Patrick\";last|s:10:\"Di Martino\";email|s:24:\"patrick@wolfpacklabs.com\";token|s:36:\"62058413-f9cf-11ed-bdd0-6cf049a5a4da\";captcha_code|s:6:\"f15df9\";',
        1684901261
    );

/*!40000 ALTER TABLE `sessiondata` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `socialmedia`
--
DROP TABLE IF EXISTS `socialmedia`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `socialmedia` (
        `id` INT (10) unsigned NOT NULL AUTO_INCREMENT,
        `label_name` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `icon` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB AUTO_INCREMENT = 8 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `socialmedia`
--
LOCK TABLES `socialmedia` WRITE;

/*!40000 ALTER TABLE `socialmedia` DISABLE KEYS */;

INSERT INTO
    `socialmedia`
VALUES
    (1, 'socSkype', 'fab fa-skype'),
    (2, 'socFacebook', 'fab fa-facebook-square'),
    (3, 'socTwitter', 'fab fa-twitter-square'),
    (4, 'socWebPage', 'fal fa-globe'),
    (5, 'socInstagram', 'fab fa-instagram'),
    (6, 'socLinkedin', 'fab fa-linkedin'),
    (7, 'socWhatsApp', 'fab fa-whatsapp');

/*!40000 ALTER TABLE `socialmedia` ENABLE KEYS */;

UNLOCK TABLES;

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users`;

/*!40101 SET @saved_cs_client     = @@character_set_client */;

/*!40101 SET character_set_client = utf8 */;

CREATE TABLE
    `users` (
        `id` SMALLINT (5) unsigned NOT NULL AUTO_INCREMENT,
        `email` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        `first` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        `last` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
        `pass` VARCHAR(255) COLLATE utf8mb4_unicode_ci NOT NULL,
        `enabled` tinyint (1) NOT NULL DEFAULT '1',
        `uuid` VARCHAR(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        `language_id` VARCHAR(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `email_UNIQUE` (`email`),
        KEY `language_id` (`language_id`),
        CONSTRAINT `users_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `languages` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--
LOCK TABLES `users` WRITE;

/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO
    `users`
VALUES
    (
        1,
        'administrador@test.com',
        'Administrador',
        'Test',
        '$2y$10$MN/65/l.QE6vzkXwDcwOJephhEYxG2wnrk6wTsd5GuDlXxQqDJZgi',
        1,
        'f27447a8-262f-11f0-b532-b42e99caefae',
        'en'
    );

/*!40000 ALTER TABLE `users` ENABLE KEYS */;

UNLOCK TABLES;

/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;

/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;

/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;

/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-05-25 15:10:21