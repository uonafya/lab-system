<?php

namespace App;

use DB;

class MiscCovid extends Common
{


	public static function create_tables(){
		DB::statement("DROP TABLE IF EXISTS `covid_worksheets`;");

		DB::statement("
			CREATE TABLE `covid_worksheets` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`national_worksheet_id` int(10) unsigned DEFAULT NULL,
				`machine_type` tinyint(3) unsigned NOT NULL,
				`lab_id` tinyint(3) unsigned NOT NULL,
				`status_id` tinyint(3) unsigned NOT NULL DEFAULT '1',
				`runby` int(10) unsigned DEFAULT NULL,
				`uploadedby` int(10) unsigned DEFAULT NULL,
				`sortedby` int(10) unsigned DEFAULT NULL,
				`alliquotedby` int(10) unsigned DEFAULT NULL,
				`bulkedby` int(10) unsigned DEFAULT NULL,
				`reviewedby` int(10) unsigned DEFAULT NULL,
				`reviewedby2` int(10) unsigned DEFAULT NULL,
				`createdby` int(10) unsigned DEFAULT NULL,
				`cancelledby` int(10) unsigned DEFAULT NULL,
				-- `hiqcap_no` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
				-- `spekkit_no` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
				-- `rack_no` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
				-- `lot_no` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
				-- `sample_prep_lot_no` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
				-- `bulklysis_lot_no` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
				-- `control_lot_no` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
				-- `calibrator_lot_no` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
				-- `amplification_kit_lot_no` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
				`neg_control_result` tinyint(3) unsigned DEFAULT NULL,
				`pos_control_result` tinyint(3) unsigned DEFAULT NULL,
				`neg_control_interpretation` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
				`pos_control_interpretation` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
				`cdcworksheetno` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
				`kitexpirydate` date DEFAULT NULL,
				`sampleprepexpirydate` date DEFAULT NULL,
				`bulklysisexpirydate` date DEFAULT NULL,
				`controlexpirydate` date DEFAULT NULL,
				`calibratorexpirydate` date DEFAULT NULL,
				`amplificationexpirydate` date DEFAULT NULL,
				`datecut` date DEFAULT NULL,
				`datereviewed` date DEFAULT NULL,
				`datereviewed2` date DEFAULT NULL,
				`dateuploaded` date DEFAULT NULL,
				`datecancelled` date DEFAULT NULL,
				`daterun` date DEFAULT NULL,
				`synched` tinyint(4) NOT NULL DEFAULT '0',
				`datesynched` date DEFAULT NULL,
				`created_at` timestamp NULL DEFAULT NULL,
				`updated_at` timestamp NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `covid_national_worksheet_id_index` (`national_worksheet_id`),
				KEY `covid_status_id_index` (`status_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		");

		DB::statement("DROP TABLE IF EXISTS `covid_samples`;");

		DB::statement("
			CREATE TABLE `covid_samples` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`national_sample_id` int(10) unsigned DEFAULT NULL,
				`lab_id` tinyint(3) unsigned NOT NULL,
				`facility_id` int(10) unsigned NOT NULL,
				`patient` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
				`amrs_location` tinyint(4) DEFAULT NULL,
				`provider_identifier` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
				`order_no` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,

				`sample_type` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,

				`receivedstatus` tinyint(3) unsigned DEFAULT NULL,
				`sample_received_by` int(10) unsigned DEFAULT NULL,

				`dob` date DEFAULT NULL,
				`age` tinyint unsigned DEFAULT NULL,
				`sex` tinyint unsigned DEFAULT NULL,
				`residence` varchar(80) COLLATE utf8_unicode_ci NOT NULL,
				`symptoms_date` date DEFAULT NULL,

				`entered_by` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
				`comments` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
				`labcomment` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
				`parentid` int(10) unsigned DEFAULT '0',
				`rejectedreason` tinyint(3) unsigned DEFAULT NULL,
				`reason_for_repeat` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,

				`interpretation` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
				`result` tinyint(3) unsigned DEFAULT NULL,
				`worksheet_id` int(10) unsigned DEFAULT NULL,
				`run` tinyint(3) unsigned DEFAULT '1',
				`repeatt` tinyint(3) unsigned DEFAULT '0',
				`approvedby` int(10) unsigned DEFAULT NULL,
				`approvedby2` int(10) unsigned DEFAULT NULL,

				`datecollected` date DEFAULT NULL,
				`datereceived` date DEFAULT NULL,
				`datetested` date DEFAULT NULL,
				`datedispatched` date DEFAULT NULL,

				`datemodified` date DEFAULT NULL,
				`dateapproved` date DEFAULT NULL,
				`dateapproved2` date DEFAULT NULL,

				`tat1` tinyint(3) unsigned DEFAULT '0',
				`tat2` tinyint(3) unsigned DEFAULT '0',
				`tat3` tinyint(3) unsigned DEFAULT '0',
				`tat4` tinyint(3) unsigned DEFAULT '0',

				`synched` tinyint(4) DEFAULT '0',
				`datesynched` date DEFAULT NULL,
				`created_at` timestamp NULL DEFAULT NULL,
				`updated_at` timestamp NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `covid_national_sample_id_index` (`national_sample_id`),
				KEY `covid_patient_index` (`patient`),
				KEY `covid_order_no_index` (`order_no`),
				KEY `covid_parentid_index` (`parentid`),
				KEY `covid_worksheet_id_index` (`worksheet_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		");


		DB::statement("DROP TABLE IF EXISTS `covid_travels`;");

		DB::statement("
			CREATE TABLE `covid_travels` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`national_travel_id` int(10) unsigned DEFAULT NULL,
				`sample_id` int(10) unsigned DEFAULT NULL,

				`travel_date` date DEFAULT NULL,
				`city_visited` varchar(50) DEFAULT NULL,
				`duration_visited` smallint unsigned DEFAULT NULL,

				`synched` tinyint(4) NOT NULL DEFAULT '0',
				`datesynched` date DEFAULT NULL,
				`created_at` timestamp NULL DEFAULT NULL,
				`updated_at` timestamp NULL DEFAULT NULL,
				PRIMARY KEY (`id`),
				KEY `covid_national_travel_id_index` (`national_travel_id`),
				KEY `covid_status_id_index` (`status_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
		");


	}
}
