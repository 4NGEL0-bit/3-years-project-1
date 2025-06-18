USE `clinic_db`;

-- IMPORTANT: Before running this, ensure you have HASHED passwords if you intend to use password_verify().
-- For this example, we are inserting plain text passwords as used in the current PHP logic.
-- In a production system, ALWAYS use hashed passwords.

-- Admin User
INSERT INTO `users` (`nom`, `email`, `mot_de_passe`, `role`) VALUES
("Admin User", "admin@clinicsys.com", "admin123", "admin");

-- Doctor User
INSERT INTO `users` (`nom`, `email`, `mot_de_passe`, `role`, `phone`, `address`, `gender`, `date_of_birth`) VALUES
("Dr. Emily Carter", "doctor.carter@clinicsys.com", "doctor123", "doctor", "555-0101", "123 Health St, Medville", "Female", "1980-05-15");

-- Another Doctor User
INSERT INTO `users` (`nom`, `email`, `mot_de_passe`, `role`, `phone`, `address`, `gender`, `date_of_birth`) VALUES
("Dr. Ben Miller", "doctor.miller@clinicsys.com", "doctor456", "doctor", "555-0102", "456 Wellness Ave, Medtown", "Male", "1975-11-20");

-- Nurse User
INSERT INTO `users` (`nom`, `email`, `mot_de_passe`, `role`, `phone`, `address`, `gender`, `date_of_birth`) VALUES
("Nurse Alex Johnson", "nurse.johnson@clinicsys.com", "nurse123", "nurse", "555-0201", "789 Care Rd, Healthburg", "Other", "1990-02-28");

-- Sample Patient (can also be created via registration form)
INSERT INTO `users` (`nom`, `email`, `mot_de_passe`, `role`, `phone`, `address`, `gender`, `date_of_birth`) VALUES
("John Doe", "john.doe@example.com", "patient123", "patient", "555-0301", "10 Patient Ln, Testville", "Male", "1985-07-01");

-- You can add more sample data for appointments, etc., if needed for initial testing.

SELECT "Seed users created successfully." AS status;

