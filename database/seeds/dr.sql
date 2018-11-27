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
	(5, 1110, 'processing'),
	(6, 1120, 'action_required');

DROP TABLE IF EXISTS `calls`;
CREATE TABLE  `calls` (
  `id` TINYINT unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


INSERT INTO `calls` (`id`, `name`) VALUES
	(1, 'S'),
	(2, 'LC');




