# --------------------------------------------------------
# Host:                         10.230.50.11
# Database:                     apidb
# Server version:               5.7.12-enterprise-commercial-advanced-log
# Server OS:                    Linux
# HeidiSQL version:             5.0.0.3272
# Date/time:                    2017-07-24 17:01:55
# --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

# Dumping structure for table apidb.agecategory
CREATE TABLE IF NOT EXISTS `agecategory` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `subID` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `name` (`name`),
  KEY `subID` (`subID`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.agecategory: 12 rows
/*!40000 ALTER TABLE `agecategory` DISABLE KEYS */;
INSERT INTO `agecategory` (`ID`, `name`, `subID`) VALUES (0, 'No Data', '1'), (1, '<5', NULL), (2, '<10', NULL), (3, '<15', NULL), (4, '<18', NULL), (5, '18 +', NULL), (6, 'Less 2', '1'), (7, '2-9', '1'), (8, '10-14', '1'), (9, '15-19', '1'), (10, '20-24', '1'), (11, '25+', '1');
/*!40000 ALTER TABLE `agecategory` ENABLE KEYS */;


# Dumping structure for table apidb.entry_points
CREATE TABLE IF NOT EXISTS `entry_points` (
  `ID` int(14) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.entry_points: 7 rows
/*!40000 ALTER TABLE `entry_points` DISABLE KEYS */;
INSERT INTO `entry_points` (`ID`, `name`) VALUES (4, 'CCC/PSC'), (5, 'Materrnity'), (3, 'MCH/PMTCT'), (7, 'No Data'), (1, 'OPD'), (6, 'Other'), (2, 'Paediatric  Ward');
/*!40000 ALTER TABLE `entry_points` ENABLE KEYS */;


# Dumping structure for table apidb.feedings
CREATE TABLE IF NOT EXISTS `feedings` (
  `ID` int(14) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(100) NOT NULL,
  `Flag` int(14) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `ID` (`ID`),
  KEY `name` (`name`),
  KEY `description` (`description`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.feedings: 9 rows
/*!40000 ALTER TABLE `feedings` DISABLE KEYS */;
INSERT INTO `feedings` (`ID`, `name`, `description`, `Flag`) VALUES (1, 'EBF', 'Exclusive Breast Feeding [ 0 - 6 Months]', 1), (2, 'MBF', 'Mixed Breast Feeding  [ 0 - 6 Months ]', 1), (3, 'NBF', 'Not Breast Feeding [ > 6 Months ]', 1), (4, 'ERF', 'Exclusive Replacement  Feeding  [ 0 - 6 Months]', 1), (5, 'None', 'No Data', 1), (6, 'BF', 'Breast Feeding [ > 6 Months ]', 1), (7, 'NBF', 'Not Breast Feeding [ < 6 Months ]', 1), (8, 'NBF', 'Not Breast Feeding', 2), (9, 'BF', 'Breast Feeding', 2);
/*!40000 ALTER TABLE `feedings` ENABLE KEYS */;


# Dumping structure for table apidb.gender
CREATE TABLE IF NOT EXISTS `gender` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.gender: 3 rows
/*!40000 ALTER TABLE `gender` DISABLE KEYS */;
INSERT INTO `gender` (`ID`, `name`) VALUES (0, 'No Data'), (1, 'M'), (2, 'F');
/*!40000 ALTER TABLE `gender` ENABLE KEYS */;


# Dumping structure for table apidb.hei_categories
CREATE TABLE IF NOT EXISTS `hei_categories` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `active` int(10) DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.hei_categories: 6 rows
/*!40000 ALTER TABLE `hei_categories` DISABLE KEYS */;
INSERT INTO `hei_categories` (`ID`, `name`, `active`) VALUES (1, 'Enrolled', 1), (2, 'Lost to Follow Up', 1), (3, 'Dead', 1), (4, 'Adult Sample', 1), (5, 'Transferred Out', 1), (6, 'Other', 1);
/*!40000 ALTER TABLE `hei_categories` ENABLE KEYS */;


# Dumping structure for table apidb.hei_validation
CREATE TABLE IF NOT EXISTS `hei_validation` (
  `ID` int(14) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `desc` varchar(30) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.hei_validation: 5 rows
/*!40000 ALTER TABLE `hei_validation` DISABLE KEYS */;
INSERT INTO `hei_validation` (`ID`, `name`, `desc`) VALUES (1, 'CP', 'Confirmed Positive'), (2, 'A', 'Adult'), (3, 'VL', 'Viral Load'), (4, 'RT', 'Repeat test'), (5, 'UF', 'Unknown Facility');
/*!40000 ALTER TABLE `hei_validation` ENABLE KEYS */;


# Dumping structure for table apidb.labs
CREATE TABLE IF NOT EXISTS `labs` (
  `ID` int(14) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(32) DEFAULT NULL,
  `labname` varchar(50) DEFAULT NULL,
  `labdesc` varchar(50) DEFAULT NULL,
  `lablocation` varchar(50) DEFAULT NULL,
  `labtel1` varchar(32) DEFAULT NULL,
  `labtel2` varchar(32) DEFAULT NULL,
  `taqman` int(1) DEFAULT '1',
  `abbott` int(1) DEFAULT '1',
  PRIMARY KEY (`ID`),
  KEY `name` (`name`),
  KEY `labname` (`labname`),
  KEY `labdesc` (`labdesc`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.labs: 9 rows
/*!40000 ALTER TABLE `labs` DISABLE KEYS */;
INSERT INTO `labs` (`ID`, `name`, `email`, `labname`, `labdesc`, `lablocation`, `labtel1`, `labtel2`, `taqman`, `abbott`) VALUES (1, 'KEMRI CVR HIV-P3 Lab, Nairobi', 'eid-nairobi@googlegroups.com', 'KEMRI Nairobi ', 'KEMRI CVR HIV-P3 Lab', 'KEMRI HQ, Mbagathi Road, Nairobi', '020 2722541 Ext: 2256/2290 ', '0725793260 / 0725796842', 1, 1), (2, 'KEMRI CDC HIV/R Lab,  Kisumu', 'eid-kisumu-kisian@googlegroups.c', 'Kisumu Lab', 'CDC HIV/R Lab', 'Kisumu-Busia Road, Kisumu', '057 2053017/8 ', ' 0722204614', 1, 1), (3, 'KEMRI ALUPE HIV Laboratory', 'eid-alupe@googlegroups.com', 'Busia Lab', 'KEMRI Alupe Lab', 'Busia - Malaba Rd, Busia', ' (055) 22410', ' 0726156679', 1, 1), (4, 'KEMRI/Walter Reed CRC Lab, Kericho', 'eid-kericho@googlegroups.com', 'Kericho Lab', 'WRP CRC Lab', 'Hospital Road, Kericho', ' 052 30388/21064', '0716430261', 1, 1), (5, 'AMPATH Care Lab, Eldoret', 'eid-ampath@googlegroups.com', 'Eldoret Lab', 'AMPATH Care Lab Eldoret', 'Moi Referral Hospital, Eldoret', NULL, NULL, 1, 1), (6, 'Coast Provincial General Hospital Molecular Lab', 'eid-cpgh@googlegroups.com', 'Coast Lab', 'CPGH Molecular Lab', 'Coast Provincial General Hospital', ' 0722207868 Ext. Lab', '0720594408 / 0733657392', 1, 1), (7, 'National HIV Reference Laboratory, Nairobi', NULL, 'NHRL Nairobi', 'NHRL Nairobi', NULL, NULL, NULL, 1, 1), (8, 'Nyumbani Diagnostic Lab', NULL, 'Nyumbani Lab ', 'Nyumbani Lab Nairobi', NULL, NULL, NULL, 0, 1), (9, 'Kenyatta National Hospial Lab, Nairobi', NULL, 'KNH Nairobi', 'KNH Nairobi', NULL, NULL, NULL, 1, 1);
/*!40000 ALTER TABLE `labs` ENABLE KEYS */;


# Dumping structure for table apidb.pcrtype
CREATE TABLE IF NOT EXISTS `pcrtype` (
  `ID` int(14) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping data for table apidb.pcrtype: 4 rows
/*!40000 ALTER TABLE `pcrtype` DISABLE KEYS */;
INSERT INTO `pcrtype` (`ID`, `name`) VALUES (1, '1 &nbsp;Initial PCR'), (2, '2 &nbsp;Repeat PCR'), (3, '3 &nbsp;Confirmatory PCR'), (4, '4 &nbsp;Discrepant PCR(tie breaker)');
/*!40000 ALTER TABLE `pcrtype` ENABLE KEYS */;


# Dumping structure for table apidb.platforms
CREATE TABLE IF NOT EXISTS `platforms` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.platforms: 3 rows
/*!40000 ALTER TABLE `platforms` DISABLE KEYS */;
INSERT INTO `platforms` (`ID`, `name`) VALUES (1, 'Abbott'), (2, 'Roche'), (3, 'Hologic');
/*!40000 ALTER TABLE `platforms` ENABLE KEYS */;


# Dumping structure for table apidb.prophylaxis
CREATE TABLE IF NOT EXISTS `prophylaxis` (
  `ID` int(14) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `ptype` int(14) NOT NULL,
  `flag` int(14) NOT NULL DEFAULT '1',
  `rank` int(14) DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `ID` (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.prophylaxis: 24 rows
/*!40000 ALTER TABLE `prophylaxis` DISABLE KEYS */;
INSERT INTO `prophylaxis` (`ID`, `name`, `ptype`, `flag`, `rank`) VALUES (1, 'SdNVP Only', 1, 0, NULL), (2, 'Interrupted HAART (HAART until end of BF)', 1, 0, NULL), (3, 'AZT (From 14wks or later) + sdNVP + 3TC + AZT+3TC for 7 days', 1, 0, NULL), (4, 'HAART', 1, 0, NULL), (5, 'None', 1, 1, 10), (6, 'PM1X : Other Regimens', 1, 1, 9), (7, 'No Data', 1, 1, 11), (8, 'SdNVP Only', 2, 0, NULL), (9, 'Sd NVP+AZT+3TC', 2, 0, NULL), (10, 'NVP for 6 weeks (Mother on HAART or not BF)', 2, 0, NULL), (11, 'NVP during BF', 2, 0, NULL), (12, 'Others', 2, 1, 4), (13, 'None', 2, 1, 3), (14, 'No Data', 2, 1, 5), (15, 'NVP for 12 Wks', 2, 1, 1), (16, 'NVP+AZT', 2, 1, 2), (17, 'PM3 :AZT+3TC+NVP', 1, 1, 1), (18, 'PM4 :AZT+3TC+EFV', 1, 1, 2), (19, 'PM5 :AZT+3TC+LPV/r', 1, 1, 3), (20, 'PM6 :TDF+3TC+NVP', 1, 1, 4), (21, 'PM7 :TDF+3TC+LPV/r', 1, 1, 5), (22, 'PM9 :TDF+3TC+EFV', 1, 1, 6), (23, 'PM10:AZT+3TC+ATV/r', 1, 1, 7), (24, 'PM11:TDF+3TC+ATV/r', 1, 1, 8);
/*!40000 ALTER TABLE `prophylaxis` ENABLE KEYS */;


# Dumping structure for table apidb.prophylaxistypes
CREATE TABLE IF NOT EXISTS `prophylaxistypes` (
  `ID` int(14) NOT NULL AUTO_INCREMENT,
  `Name` varchar(200) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.prophylaxistypes: 2 rows
/*!40000 ALTER TABLE `prophylaxistypes` DISABLE KEYS */;
INSERT INTO `prophylaxistypes` (`ID`, `Name`) VALUES (1, 'Mother Intervention'), (2, 'Infant Prophylaxis');
/*!40000 ALTER TABLE `prophylaxistypes` ENABLE KEYS */;


# Dumping structure for table apidb.receivedstatus
CREATE TABLE IF NOT EXISTS `receivedstatus` (
  `ID` int(14) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID` (`ID`,`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.receivedstatus: 3 rows
/*!40000 ALTER TABLE `receivedstatus` DISABLE KEYS */;
INSERT INTO `receivedstatus` (`ID`, `Name`) VALUES (1, 'Accepted'), (2, 'Rejected'), (3, 'Repeat');
/*!40000 ALTER TABLE `receivedstatus` ENABLE KEYS */;


# Dumping structure for table apidb.rejectedreasons
CREATE TABLE IF NOT EXISTS `rejectedreasons` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `Name` varchar(50) DEFAULT NULL,
  `alias` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1 COMMENT='eid rejected reasons';

# Dumping data for table apidb.rejectedreasons: 17 rows
/*!40000 ALTER TABLE `rejectedreasons` DISABLE KEYS */;
INSERT INTO `rejectedreasons` (`ID`, `Name`, `alias`) VALUES (1, 'Serum rings', NULL), (2, 'Clotted samples', NULL), (3, 'Samples packaged together', NULL), (4, 'Small Spots', NULL), (5, 'Poor Drying', NULL), (6, 'Other', NULL), (7, 'Over Age ( Adult )', NULL), (8, 'Collected on expired filter paper', NULL), (9, 'Sample collected on Humidity Indicator', NULL), (10, 'Under Aged', NULL), (11, 'Over Age ( Child )', NULL), (12, 'DBS not sent with the request form', NULL), (13, 'Patient ID error / labeling', NULL), (14, 'Over Saturation', NULL), (15, 'Scratched DBS Spots', NULL), (16, 'Double Entry', NULL), (17, 'Insufficient sample volume', NULL);
/*!40000 ALTER TABLE `rejectedreasons` ENABLE KEYS */;


# Dumping structure for table apidb.results
CREATE TABLE IF NOT EXISTS `results` (
  `ID` int(14) NOT NULL AUTO_INCREMENT,
  `Name` varchar(100) NOT NULL,
  `alias` varchar(100) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `ID` (`ID`),
  KEY `ID_2` (`ID`,`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.results: 5 rows
/*!40000 ALTER TABLE `results` DISABLE KEYS */;
INSERT INTO `results` (`ID`, `Name`, `alias`) VALUES (1, 'Negative', 'NEG'), (2, 'Positive', 'POS'), (3, 'Failed', ''), (4, 'Unknown', ''), (5, 'Collect New Sample', '');
/*!40000 ALTER TABLE `results` ENABLE KEYS */;


# Dumping structure for table apidb.testtype
CREATE TABLE IF NOT EXISTS `testtype` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.testtype: 2 rows
/*!40000 ALTER TABLE `testtype` DISABLE KEYS */;
INSERT INTO `testtype` (`ID`, `name`) VALUES (1, 'EID'), (2, 'VL');
/*!40000 ALTER TABLE `testtype` ENABLE KEYS */;


# Dumping structure for table apidb.viraljustifications
CREATE TABLE IF NOT EXISTS `viraljustifications` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `displaylabel` varchar(100) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `flag` int(50) DEFAULT '1',
  `rank` int(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.viraljustifications: 9 rows
/*!40000 ALTER TABLE `viraljustifications` DISABLE KEYS */;
INSERT INTO `viraljustifications` (`id`, `displaylabel`, `name`, `flag`, `rank`) VALUES (1, '1 &nbsp;Routine VL', 'Routine VL', 1, 1), (2, '2 &nbsp;Confirmation of Treatment Failure (Repeat VL)', 'Confirmation of Treatment Failure (Repeat VL)', 1, 2), (3, '3 &nbsp;Clinical Failure', 'Clinical Failure', 1, 3), (5, '4 &nbsp;Single Drug Substitution', 'Single Drug Substitution', 1, 4), (6, '8 &nbsp;Pregnant Mother', 'Pregnant Mother', 1, 8), (7, '6 &nbsp;Other', 'Other', 1, 6), (8, '7 &nbsp;No Data', 'No Data', 1, 7), (9, '9 &nbsp;Breast Feeding Mothers', 'Breast Feeding Mothers', 1, 9), (10, '5 &nbsp;Baseline', 'Baseline', 1, 5);
/*!40000 ALTER TABLE `viraljustifications` ENABLE KEYS */;


# Dumping structure for table apidb.viralprophylaxis
CREATE TABLE IF NOT EXISTS `viralprophylaxis` (
  `ID` int(50) NOT NULL AUTO_INCREMENT,
  `displaylabel` varchar(50) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` varchar(100) NOT NULL,
  `line` int(100) NOT NULL,
  `ptype` int(14) NOT NULL DEFAULT '2',
  `category` int(14) NOT NULL DEFAULT '3',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.viralprophylaxis: 19 rows
/*!40000 ALTER TABLE `viralprophylaxis` DISABLE KEYS */;
INSERT INTO `viralprophylaxis` (`ID`, `displaylabel`, `name`, `description`, `line`, `ptype`, `category`) VALUES (1, '4 &nbsp;AZT+3TC+NVP', 'AZT+3TC+NVP', '', 0, 0, 4), (2, '5 &nbsp;AZT+3TC+EFV', 'AZT+3TC+EFV', '', 0, 0, 5), (3, '2 &nbsp;TDF+3TC+NVP', 'TDF+3TC+NVP', '', 0, 0, 2), (4, '1 &nbsp;TDF+3TC+EFV', 'TDF+3TC+EFV', '', 0, 0, 1), (5, '10 &nbsp;AZT+3TC+LPVr', 'AZT+3TC+LPVr', '', 0, 0, 10), (6, '17 &nbsp;AZT+3TC+ABC', 'AZT+3TC+ABC', '', 0, 0, 17), (7, '18 &nbsp;TDF+3TC+LPVr', 'TDF+3TC+LPVr', '', 0, 0, 18), (8, '14 &nbsp;AZT+3TC+ATVr', 'AZT+3TC+ATVr', '', 0, 0, 14), (9, '11 &nbsp;TDF+3TC+ATVr', 'TDF+3TC+ATVr', '', 0, 0, 11), (10, '13 &nbsp;ABC+3TC+ATVr', 'ABC+3TC+ATVr', '', 0, 0, 13), (11, '6 &nbsp;ABC+3TC+NVP', 'ABC+3TC+NVP', '', 0, 0, 6), (12, '7 &nbsp;ABC+3TC+EFV', 'ABC+3TC+EFV', '', 0, 0, 7), (13, '9 &nbsp;ABC+3TC+LPVr', 'ABC+3TC+LPVr', '', 0, 0, 9), (14, '16 &nbsp;Other', 'Other', '', 0, 0, 16), (15, '19 &nbsp;None', 'None', '', 0, 0, 19), (16, '20 &nbsp;No Data', 'No Data', '', 0, 0, 20), (17, '3 &nbsp;TDF+3TC+DTG', 'TDT+3TC+DTG', '', 0, 0, 3), (18, '8 &nbsp;ABC+3TC+DTG', 'ABC+3TC+DTG', '', 0, 0, 8), (19, '15 &nbsp;AZT+3TC+DRV/r', 'AZT+3TC+DRV/r', '', 0, 0, 15);
/*!40000 ALTER TABLE `viralprophylaxis` ENABLE KEYS */;


# Dumping structure for table apidb.viralrejectedreasons
CREATE TABLE IF NOT EXISTS `viralrejectedreasons` (
  `ID` int(10) NOT NULL,
  `Name` varchar(200) DEFAULT NULL,
  `alias` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

# Dumping data for table apidb.viralrejectedreasons: 15 rows
/*!40000 ALTER TABLE `viralrejectedreasons` DISABLE KEYS */;
INSERT INTO `viralrejectedreasons` (`ID`, `Name`, `alias`) VALUES (1, 'Improper Collection Technique [Clotted Haemolyzed / Short Draw Lipemic ]', 'Improper Collection Technique '), (2, 'Incorrect Container / Tube Received', 'Incorrect Container/Tube Received'), (3, 'Patient ID error / No Specimen Label', 'Patient ID error / No Specimen Label'), (4, 'Requisition & Specimen do not match', 'Requisition & Specimen do not match'), (5, 'Delayed Delivery of Specimen', 'Delayed Delivery of Specimen'), (10, 'Improperly Packaged Specimens / Shipment [ Leaking specimen / breaking / high temperature ]', 'Improperly Packaged Specimens'), (11, 'Sample Missing on Requisition Form', 'Sample Missing on Requisition Form'), (12, 'Sample \'not due for re-collection\' not 3 months', 'Sample \'not due for re-collection\' '), (13, 'Patient Died', 'Patient Died'), (14, 'Insufficient  sample volume', 'Insufficient  sample volume'), (15, 'No Request form accompanying the sample', 'No Request form '), (16, 'Collected on expired Filter paper', 'Collected on expired Filter paper'), (17, 'Missing Sample ( Physical Sample Missing)', 'Missing Sample'), (18, 'Duplicate Entry', 'Duplicate Entry'), (19, 'Other', 'Other');
/*!40000 ALTER TABLE `viralrejectedreasons` ENABLE KEYS */;


# Dumping structure for table apidb.viralsampletype
CREATE TABLE IF NOT EXISTS `viralsampletype` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `sampletype` int(14) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `typecode` int(100) DEFAULT NULL,
  `flag` int(10) DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.viralsampletype: 4 rows
/*!40000 ALTER TABLE `viralsampletype` DISABLE KEYS */;
INSERT INTO `viralsampletype` (`ID`, `sampletype`, `name`, `alias`, `typecode`, `flag`) VALUES (1, 1, 'Frozen Plasma', 'PLASMA', 2, 1), (2, 3, 'Venous Blood  (EDTA )', NULL, 2, 1), (4, 2, 'DBS Venous', 'DBS', 1, 1), (3, 2, 'DBS Capillary ( infants)', NULL, 1, 1);
/*!40000 ALTER TABLE `viralsampletype` ENABLE KEYS */;


# Dumping structure for table apidb.viralsampletypedetails
CREATE TABLE IF NOT EXISTS `viralsampletypedetails` (
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `flag` int(10) DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

# Dumping data for table apidb.viralsampletypedetails: 3 rows
/*!40000 ALTER TABLE `viralsampletypedetails` DISABLE KEYS */;
INSERT INTO `viralsampletypedetails` (`ID`, `name`, `flag`) VALUES (1, 'Frozen Plasma', 1), (2, 'DBS', 1), (3, 'EDTA', 1);
/*!40000 ALTER TABLE `viralsampletypedetails` ENABLE KEYS */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

CREATE OR REPLACE
VIEW view_facilitys AS
SELECT facilitys.id,facilitys.facilitycode,facilitys.longitude,facilitys.latitude,facilitys.email, facilitys.district,
districts.name as subcounty,facilitys.name,facilitys.partner,partners.name as partnerdesc,
facilitys.smsprinter,facilitys.flag,facilitys.lab,districts.county,countys.name as countydesc ,
districts.province 
from facilitys,districts,countys,partners 
where facilitys.district=districts.ID and districts.county=countys.id 
and facilitys.partner=partners.ID and facilitys.Flag=1;
​

