DROP TABLE IF EXISTS `regimen_classes`;
-- Dumping structure for table apidb.age_bands
CREATE TABLE IF NOT EXISTS `regimen_classes` (
  `id`  TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(25) COLLATE utf8mb4_unicode_ci NULL,
  `drug_class` varchar(25) COLLATE utf8mb4_unicode_ci NULL,
  `short_name` varchar(25) COLLATE utf8mb4_unicode_ci NULL,
  `call` varchar(25) COLLATE utf8mb4_unicode_ci NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `dr_plate_statuses`;
CREATE TABLE  `dr_plate_statuses` (
  `id` TINYINT unsigned NOT NULL AUTO_INCREMENT,
  `other_id` INT unsigned NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


INSERT INTO `dr_plate_statuses` (`id`, `other_id`, `name`) VALUES
	(1, 2100, 'completed'),
	(2, 2101, 'error'),
	(3, 2102, 'failed'),
	(4, 2110, 'pending'),
	(5, 2120, 'action_required');


DROP TABLE IF EXISTS `dr_sample_statuses`;
CREATE TABLE  `dr_sample_statuses` (
  `id` TINYINT unsigned NOT NULL AUTO_INCREMENT,
  `other_id` INT unsigned NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


INSERT INTO `dr_sample_statuses` (`id`, `other_id`, `name`) VALUES
	(1, 1100, 'completed'),
	(2, 1101, 'error'),
	(3, 1102, 'failed'),
	(4, 1110, 'pending'),
	(5, 1111, 'processing'),
	(6, 1120, 'action_required');

DROP TABLE IF EXISTS `calls`;
CREATE TABLE  `calls` (
  `id` TINYINT unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


INSERT INTO `calls` (`id`, `name`) VALUES
	(1, 'S'),
	(2, 'LC');


DROP TABLE IF EXISTS `dr_warning_codes`;
CREATE TABLE  `dr_warning_codes` (
  `id` TINYINT unsigned NOT NULL AUTO_INCREMENT,
  `error` TINYINT unsigned NOT NULL DEFAULT 0,
  `name` varchar(30) DEFAULT NULL,
  `description` varchar(80) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


INSERT INTO `dr_warning_codes` (`id`, `error`, `name`, `description`) VALUES
	(1, 1, 'ErrRecall', 'Error in the recall step'),
	(2, 1, 'ErrRecallSampleFailed', 'Sample failed by recall'),
	(3, 1, 'ErrRecallUserFailed', 'User manually marked the sample as failed'),
	(4, 1, 'ErrAlign', 'Error in the alignment step'),
	(5, 1, 'ErrDrugCall', 'Error in the drug class step'),
	(6, 1, 'ErrNegativeControl', 'Negative control failed'),
	(7, 1, 'ErrPositiveControl', 'Positive control failed'),
	(8, 1, 'ErrUnknownSampleType', 'Unknown sample type'),
	(9, 1, 'ErrPlateControlFailed', 'At least one of the plate’s control samples failed'),
	(10, 1, 'ErrPlateAllSamplesFailed', 'All the samples for a plate failed'),
	(11, 1, 'ErrOther', 'Other error'),
	(12, 0, 'WrnRecallBasecall', 'Warning from recall'),
	(13, 0, 'WrnRecallUserApproved', 'The user manually approved a sample flagged by recall'),
	(14, 0, 'WrnNoPositiveControl', 'The plate had no positive control'),
	(15, 0, 'WrnNoNegativeControl', 'The plate had no negative control'),
	(16, 0, 'WrnRecallManualReviewNeeded', 'Manual review is required by recall');




DROP TABLE IF EXISTS `dr_viralprophylaxis`;
CREATE TABLE IF NOT EXISTS `dr_viralprophylaxis` (
  `id` TINYINT unsigned NOT NULL AUTO_INCREMENT,
  `displaylabel` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  'regimen1' varchar(10) NULL,
  `regimen1_class_id` TINYINT unsigned NULL,
  'regimen2' varchar(10) NULL,
  `regimen2_class_id` TINYINT unsigned NULL,
  'regimen3' varchar(10) NULL,
  `regimen3_class_id` TINYINT unsigned NULL,
  `description` varchar(100) NULL,
  `line` TINYINT unsigned NOT NULL,
  `ptype` TINYINT unsigned NOT NULL DEFAULT '2',
  `category` TINYINT unsigned NOT NULL DEFAULT '3',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;


INSERT INTO `dr_viralprophylaxis` (`id`, `displaylabel`, `name`, 'regimen1', `regimen1_class_id`, 'regimen2', `regimen2_class_id` 'regimen3', `regimen3_class_id`, `line`, `ptype`, `category`) VALUES
	(1, '4 &nbsp;AZT+3TC+NVP', 'AZT+3TC+NVP', 'AZT', 12, '3TC', 10, 'NVP', 8, 0, 0, 4),
	(2, '5 &nbsp;AZT+3TC+EFV', 'AZT+3TC+EFV', 'AZT', 12, '3TC', 10, 'EFV', 6, 0, 0, 5),
	(3, '2 &nbsp;TDF+3TC+NVP', 'TDF+3TC+NVP', 'TDF', 16, '3TC', 10, 'NVP', 8, 0, 0, 2),
	(4, '1 &nbsp;TDF+3TC+EFV', 'TDF+3TC+EFV', 'TDF', 16, '3TC', 10, 'EFV', 6, 0, 0, 1),
	(5, '10 &nbsp;AZT+3TC+LPVr', 'AZT+3TC+LPVr', 'AZT', 12, '3TC', 10, '', 0, 0, 10),
	(6, '17 &nbsp;AZT+3TC+ABC', 'AZT+3TC+ABC', 'AZT', 12, '3TC', 10, 'ABC', 11, 0, 0, 17),
	(7, '18 &nbsp;TDF+3TC+LPVr', 'TDF+3TC+LPVr', 'TDF', 16, '3TC', 10, '', 0, 0, 18),
	(8, '14 &nbsp;AZT+3TC+ATVr', 'AZT+3TC+ATVr', 'AZT', 12, '3TC', 10, '', 0, 0, 14),
	(9, '11 &nbsp;TDF+3TC+ATVr', 'TDF+3TC+ATVr', 'TDF', 16, '3TC', 10, '', 0, 0, 11),
	(10, '13 &nbsp;ABC+3TC+ATVr', 'ABC+3TC+ATVr', 'ABC', 11, '3TC', 10, '', 0, 0, 13),
	(11, '6 &nbsp;ABC+3TC+NVP', 'ABC+3TC+NVP', 'ABC', 11, '3TC', 10, 'NVP', 8, 0, 0, 6),
	(12, '7 &nbsp;ABC+3TC+EFV', 'ABC+3TC+EFV', 'ABC', 11, '3TC', 10, 'EFV', 6, 0, 0, 7),
	(13, '9 &nbsp;ABC+3TC+LPVr', 'ABC+3TC+LPVr', 'ABC', 11, '3TC', 10, '', 0, 0, 9),
	(14, '16 &nbsp;Other', 'Other', '', 0, 0, 16),
	(15, '19 &nbsp;None', 'None', '', 0, 0, 19),
	(16, '20 &nbsp;No Data', 'No Data', '', 0, 0, 20),
	(17, '3 &nbsp;TDF+3TC+DTG', 'TLD', 'TDF', 16, '3TC', 10, 'DTG', 2, 0, 0, 3),
	(18, '8 &nbsp;ABC+3TC+DTG', 'ABC+3TC+DTG', 'ABC', 11, '3TC', 10, 'DTG', 2, 0, 0, 8),
	(19, '15 &nbsp;AZT+3TC+DRV/r', 'AZT+3TC+DRV/r', 'AZT', 12, '3TC', 10, '', 0, 0, 15);



DROP TABLE IF EXISTS `regimen_classes`;
CREATE TABLE `regimen_classes` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `drug_class` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_name` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `call` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `regimen_classes` VALUES 
	(1,NULL,'INSTI','BIC','LC'),
	(2,NULL,'INSTI','DTG','LC'),
	(3,NULL,'INSTI','EVG','LC'),
	(4,NULL,'INSTI','RAL','LC'),
	(5,NULL,'NNRTI','DOR','I'),
	(6,NULL,'NNRTI','EFV','R'),
	(7,NULL,'NNRTI','ETR','I'),
	(8,NULL,'NNRTI','NVP','R'),
	(9,NULL,'NNRTI','RPV','I'),
	(10,NULL,'NRTI','3TC','R'),
	(11,NULL,'NRTI','ABC','R'),
	(12,NULL,'NRTI','AZT','S'),
	(13,NULL,'NRTI','D4T','R'),
	(14,NULL,'NRTI','DDI','R'),
	(15,NULL,'NRTI','FTC','R'),
	(16,NULL,'NRTI','TDF','I'),
	(17,NULL,'PI','ATV/r','I'),
	(18,NULL,'PI','DRV/r','I'),
	(19,NULL,'PI','FPV/r','R'),
	(20,NULL,'PI','IDV/r','I'),
	(21,NULL,'PI','LPV/r','I'),
	(22,NULL,'PI','NFV','R'),
	(23,NULL,'PI','SQV/r','R'),
	(24,NULL,'PI','TPV/r','I');
