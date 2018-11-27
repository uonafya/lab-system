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




