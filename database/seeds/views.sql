CREATE OR REPLACE VIEW samples_view AS
          (
            SELECT s.*, b.high_priority, b.datereceived, b.datedispatched, b.site_entry, b.lab_id, f.facilitycode, b.facility_id, 
            p.patient, p.sex, p.dob, p.mother_id, p.entry_point
            
            FROM samples s
              JOIN batches b ON b.id=s.batch_id
              LEFT JOIN facilitys f ON f.id=b.facility_id
              JOIN patients p ON p.id=s.patient_id

          ); 

CREATE OR REPLACE VIEW viralsamples_view AS
          (
            SELECT s.*, b.high_priority, b.datereceived, b.datedispatched, b.site_entry, b.lab_id, f.facilitycode, b.facility_id, 
            p.patient, p.patient_name, p.initiation_date, p.sex, p.dob
            
            FROM viralsamples s
              JOIN viralbatches b ON b.id=s.batch_id
              LEFT JOIN facilitys f ON f.id=b.facility_id
              JOIN viralpatients p ON p.id=s.patient_id

          );