CREATE OR REPLACE VIEW samples_view AS
          (
            SELECT samples.*, CONCAT(u.surname,' ',u.oname) AS creator_name,
            
            FROM samples s
              JOIN batches b ON b.id=s.batch_id
              JOIN facilitys f ON f.id=b.facility_id
              JOIN labs l ON l.id=b.lab_id
              LEFT JOIN users u ON u.id=b.user_id
              LEFT JOIN users rec ON rec.id=b.received_by
          );