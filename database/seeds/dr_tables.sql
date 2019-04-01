
-- Dumping structure for table nhrl_db.dr_calls
DROP TABLE IF EXISTS `dr_calls`;
CREATE TABLE IF NOT EXISTS `dr_calls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sample_id` int(10) unsigned NOT NULL,
  `drug_class` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `drug_class_id` tinyint(3) unsigned DEFAULT NULL,
  `mutations` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dr_calls_sample_id_index` (`sample_id`),
  KEY `dr_calls_drug_class_id_index` (`drug_class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping structure for table nhrl_db.dr_call_drugs
DROP TABLE IF EXISTS `dr_call_drugs`;
CREATE TABLE IF NOT EXISTS `dr_call_drugs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `call_id` int(10) unsigned NOT NULL,
  `short_name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `short_name_id` tinyint(3) unsigned DEFAULT NULL,
  `call` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dr_call_drugs_call_id_index` (`call_id`),
  KEY `dr_call_drugs_short_name_id_index` (`short_name_id`),
  KEY `dr_call_drugs_call_index` (`call`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table nhrl_db.dr_call_drugs: ~240 rows (approximately)
/*!40000 ALTER TABLE `dr_call_drugs` DISABLE KEYS */;
/*!40000 ALTER TABLE `dr_call_drugs` ENABLE KEYS */;

-- Dumping structure for table nhrl_db.dr_extraction_worksheets
DROP TABLE IF EXISTS `dr_extraction_worksheets`;
CREATE TABLE IF NOT EXISTS `dr_extraction_worksheets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lab_id` tinyint(3) unsigned NOT NULL,
  `status_id` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `createdby` int(10) unsigned DEFAULT NULL,
  `date_gel_documentation` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dr_extraction_worksheets_status_id_index` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table nhrl_db.dr_extraction_worksheets: ~1 rows (approximately)
/*!40000 ALTER TABLE `dr_extraction_worksheets` DISABLE KEYS */;
/*!40000 ALTER TABLE `dr_extraction_worksheets` ENABLE KEYS */;

-- Dumping structure for table nhrl_db.dr_genotypes
DROP TABLE IF EXISTS `dr_genotypes`;
CREATE TABLE IF NOT EXISTS `dr_genotypes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sample_id` int(10) unsigned NOT NULL,
  `locus` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `locus_id` smallint(5) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dr_genotypes_sample_id_index` (`sample_id`),
  KEY `dr_genotypes_locus_id_index` (`locus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Dumping structure for table nhrl_db.dr_patients
DROP TABLE IF EXISTS `dr_patients`;
CREATE TABLE IF NOT EXISTS `dr_patients` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned NOT NULL,
  `datereceived` date DEFAULT NULL,
  `result` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `worksheet_id` int(10) unsigned DEFAULT NULL,
  `rcategory` tinyint(3) unsigned NOT NULL,
  `dr_reason_id` tinyint(3) unsigned NOT NULL,
  `status_id` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dr_patients_patient_id_index` (`patient_id`),
  KEY `dr_patients_worksheet_id_index` (`worksheet_id`),
  KEY `dr_patients_rcategory_index` (`rcategory`),
  KEY `dr_patients_dr_reason_id_index` (`dr_reason_id`),
  KEY `dr_patients_status_id_index` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table nhrl_db.dr_patients: ~0 rows (approximately)
/*!40000 ALTER TABLE `dr_patients` DISABLE KEYS */;
/*!40000 ALTER TABLE `dr_patients` ENABLE KEYS */;

-- Dumping structure for table nhrl_db.dr_residues
DROP TABLE IF EXISTS `dr_residues`;
CREATE TABLE IF NOT EXISTS `dr_residues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `genotype_id` int(10) unsigned NOT NULL,
  `residue` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `residue_id` smallint(5) unsigned DEFAULT NULL,
  `position` smallint(5) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dr_residues_genotype_id_index` (`genotype_id`),
  KEY `dr_residues_residue_id_index` (`residue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table nhrl_db.dr_residues: ~6,590 rows (approximately)
/*!40000 ALTER TABLE `dr_residues` DISABLE KEYS */;
/*!40000 ALTER TABLE `dr_residues` ENABLE KEYS */;

-- Dumping structure for table nhrl_db.dr_results
DROP TABLE IF EXISTS `dr_results`;
CREATE TABLE IF NOT EXISTS `dr_results` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sample_id` int(10) unsigned NOT NULL,
  `dr_primer_id` tinyint(3) unsigned NOT NULL,
  `result_file` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `intepreted_result` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dr_results_sample_id_index` (`sample_id`),
  KEY `dr_results_dr_primer_id_index` (`dr_primer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table nhrl_db.dr_results: ~0 rows (approximately)
/*!40000 ALTER TABLE `dr_results` DISABLE KEYS */;
/*!40000 ALTER TABLE `dr_results` ENABLE KEYS */;

-- Dumping structure for table nhrl_db.dr_samples
DROP TABLE IF EXISTS `dr_samples`;
CREATE TABLE IF NOT EXISTS `dr_samples` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned NOT NULL,
  `facility_id` int(10) unsigned NOT NULL,
  `lab_id` tinyint(3) unsigned NOT NULL,
  `control` tinyint(3) unsigned DEFAULT '0',
  `exatype_id` bigint(20) unsigned DEFAULT NULL,
  `prev_prophylaxis` tinyint(3) unsigned DEFAULT NULL,
  `prophylaxis` tinyint(3) unsigned DEFAULT NULL,
  `receivedstatus` tinyint(3) unsigned DEFAULT NULL,
  `rejectedreason` tinyint(3) unsigned DEFAULT NULL,
  `sample_type` tinyint(3) unsigned DEFAULT NULL,
  `age` tinyint(3) unsigned DEFAULT NULL,
  `clinical_indications` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `has_opportunistic_infections` tinyint(1) NOT NULL DEFAULT '0',
  `opportunistic_infections` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `has_tb` tinyint(1) NOT NULL DEFAULT '0',
  `tb_treatment_phase_id` tinyint(3) unsigned DEFAULT NULL,
  `has_arv_toxicity` tinyint(1) NOT NULL DEFAULT '0',
  `arv_toxicities` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cd4_result` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `has_missed_pills` tinyint(1) NOT NULL DEFAULT '0',
  `missed_pills` smallint(5) unsigned DEFAULT NULL,
  `has_missed_visits` tinyint(1) NOT NULL DEFAULT '0',
  `missed_visits` smallint(5) unsigned DEFAULT NULL,
  `has_missed_pills_because_missed_visits` tinyint(1) NOT NULL DEFAULT '0',
  `other_medications` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `repeatt` tinyint(1) NOT NULL DEFAULT '0',
  `run` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `parentid` int(10) unsigned DEFAULT '0',
  `collect_new_sample` tinyint(1) NOT NULL DEFAULT '0',
  `date_prev_regimen` date DEFAULT NULL,
  `date_current_regimen` date DEFAULT NULL,
  `extraction_worksheet_id` int(10) unsigned DEFAULT NULL,
  `worksheet_id` int(10) unsigned DEFAULT NULL,
  `datecollected` date DEFAULT NULL,
  `datereceived` date DEFAULT NULL,
  `datetested` date DEFAULT NULL,
  `datedispatched` date DEFAULT NULL,
  `approvedby` int(10) unsigned DEFAULT NULL,
  `approvedby2` int(10) unsigned DEFAULT NULL,
  `dateapproved` date DEFAULT NULL,
  `dateapproved2` date DEFAULT NULL,
  `dr_reason_id` tinyint(3) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `received_by` int(10) unsigned DEFAULT NULL,
  `passed_gel_documentation` tinyint(1) DEFAULT NULL,
  `status_id` tinyint(3) unsigned DEFAULT NULL,
  `qc_pass` tinyint(1) DEFAULT '0',
  `qc_stop_codon_pass` tinyint(1) DEFAULT '0',
  `qc_plate_contamination_pass` tinyint(1) DEFAULT '0',
  `qc_frameshift_codon_pass` tinyint(1) DEFAULT '0',
  `qc_distance_to_sample` smallint(5) unsigned DEFAULT NULL,
  `qc_distance_from_sample` smallint(5) unsigned DEFAULT NULL,
  `qc_distance_difference` double(4,3) unsigned DEFAULT NULL,
  `qc_distance_strain_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `qc_distance_compare_to_name` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `qc_distance_sample_name` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `has_errors` tinyint(1) NOT NULL DEFAULT '0',
  `has_warnings` tinyint(1) NOT NULL DEFAULT '0',
  `has_mutations` tinyint(1) NOT NULL DEFAULT '0',
  `pending_manual_intervention` tinyint(1) NOT NULL DEFAULT '0',
  `had_manual_intervention` tinyint(1) NOT NULL DEFAULT '0',
  `assembled_sequence` text COLLATE utf8_unicode_ci,
  `chromatogram_url` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `exatype_version` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `algorithm` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `synched` tinyint(4) DEFAULT '0',
  `datesynched` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dr_samples_patient_id_index` (`patient_id`),
  KEY `dr_samples_facility_id_index` (`facility_id`),
  KEY `dr_samples_lab_id_index` (`lab_id`),
  KEY `dr_samples_exatype_id_index` (`exatype_id`),
  KEY `dr_samples_parentid_index` (`parentid`),
  KEY `dr_samples_extraction_worksheet_id_index` (`extraction_worksheet_id`),
  KEY `dr_samples_worksheet_id_index` (`worksheet_id`),
  KEY `dr_samples_dr_reason_id_index` (`dr_reason_id`),
  KEY `dr_samples_user_id_index` (`user_id`),
  KEY `dr_samples_status_id_index` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Dumping structure for table nhrl_db.dr_suggestions
DROP TABLE IF EXISTS `dr_suggestions`;
CREATE TABLE IF NOT EXISTS `dr_suggestions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` int(10) unsigned NOT NULL,
  `datereceived` date DEFAULT NULL,
  `result` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `worksheet_id` int(10) unsigned DEFAULT NULL,
  `rcategory` tinyint(3) unsigned NOT NULL,
  `dr_reason_id` tinyint(3) unsigned NOT NULL,
  `status_id` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dr_suggestions_patient_id_index` (`patient_id`),
  KEY `dr_suggestions_worksheet_id_index` (`worksheet_id`),
  KEY `dr_suggestions_rcategory_index` (`rcategory`),
  KEY `dr_suggestions_dr_reason_id_index` (`dr_reason_id`),
  KEY `dr_suggestions_status_id_index` (`status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table nhrl_db.dr_suggestions: ~0 rows (approximately)
/*!40000 ALTER TABLE `dr_suggestions` DISABLE KEYS */;
/*!40000 ALTER TABLE `dr_suggestions` ENABLE KEYS */;

-- Dumping structure for table nhrl_db.dr_warnings
DROP TABLE IF EXISTS `dr_warnings`;
CREATE TABLE IF NOT EXISTS `dr_warnings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sample_id` int(10) unsigned NOT NULL,
  `warning_id` int(10) unsigned NOT NULL,
  `system` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `detail` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dr_warnings_sample_id_index` (`sample_id`),
  KEY `dr_warnings_warning_id_index` (`warning_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table nhrl_db.dr_warnings: ~24 rows (approximately)
/*!40000 ALTER TABLE `dr_warnings` DISABLE KEYS */;
/*!40000 ALTER TABLE `dr_warnings` ENABLE KEYS */;

-- Dumping structure for table nhrl_db.dr_worksheets
DROP TABLE IF EXISTS `dr_worksheets`;
CREATE TABLE IF NOT EXISTS `dr_worksheets` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lab_id` tinyint(3) unsigned NOT NULL,
  `plate_id` bigint(20) unsigned DEFAULT NULL,
  `extraction_worksheet_id` int(10) unsigned DEFAULT NULL,
  `status_id` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `exatype_status_id` tinyint(3) unsigned NOT NULL DEFAULT '4',
  `daterun` date DEFAULT NULL,
  `dateuploaded` date DEFAULT NULL,
  `datecancelled` date DEFAULT NULL,
  `datereviewed` date DEFAULT NULL,
  `datereviewed2` date DEFAULT NULL,
  `createdby` int(10) unsigned DEFAULT NULL,
  `uploadedby` int(10) unsigned DEFAULT NULL,
  `cancelledby` int(10) unsigned DEFAULT NULL,
  `reviewedby` int(10) unsigned DEFAULT NULL,
  `reviewedby2` int(10) unsigned DEFAULT NULL,
  `time_sent_to_sanger` datetime DEFAULT NULL,
  `qc_pass` tinyint(1) NOT NULL DEFAULT '0',
  `qc_run` tinyint(1) NOT NULL DEFAULT '0',
  `qc_distance_pass` int(10) unsigned NOT NULL DEFAULT '0',
  `plate_controls_pass` tinyint(1) NOT NULL DEFAULT '0',
  `has_errors` tinyint(1) NOT NULL DEFAULT '0',
  `has_warnings` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dr_worksheets_plate_id_index` (`plate_id`),
  KEY `dr_worksheets_extraction_worksheet_id_index` (`extraction_worksheet_id`),
  KEY `dr_worksheets_status_id_index` (`status_id`),
  KEY `dr_worksheets_exatype_status_id_index` (`exatype_status_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table nhrl_db.dr_worksheets: ~1 rows (approximately)
/*!40000 ALTER TABLE `dr_worksheets` DISABLE KEYS */;
/*!40000 ALTER TABLE `dr_worksheets` ENABLE KEYS */;

-- Dumping structure for table nhrl_db.dr_worksheet_warnings
DROP TABLE IF EXISTS `dr_worksheet_warnings`;
CREATE TABLE IF NOT EXISTS `dr_worksheet_warnings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `worksheet_id` int(10) unsigned NOT NULL,
  `warning_id` int(10) unsigned NOT NULL,
  `system` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `detail` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dr_worksheet_warnings_worksheet_id_index` (`worksheet_id`),
  KEY `dr_worksheet_warnings_warning_id_index` (`warning_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Dumping data for table nhrl_db.dr_worksheet_warnings: ~0 rows (approximately)
/*!40000 ALTER TABLE `dr_worksheet_warnings` DISABLE KEYS */;
/*!40000 ALTER TABLE `dr_worksheet_warnings` ENABLE KEYS */;


CREATE OR REPLACE VIEW dr_samples_view AS
(
  SELECT s.*, f.facilitycode, f.name as facilityname,
  p.national_patient_id, p.patient, p.initiation_date, p.sex, p.dob, p.patient_name, p.patient_phone_no, p.preferred_language

  FROM dr_samples s
    LEFT JOIN viralpatients p ON p.id=s.patient_id
    LEFT JOIN facilitys f ON f.id=s.facility_id

);