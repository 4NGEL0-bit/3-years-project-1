USE `clinic_db`;

-- SQL Schema for Clinic Appointment System

-- Users Table: Stores information about all users (patients, doctors, nurses, admins)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nom` VARCHAR(255) NOT NULL COMMENT 'Full name of the user',
  `email` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Email address, used for login',
  `mot_de_passe` VARCHAR(255) NOT NULL COMMENT 'Hashed password',
  `phone` VARCHAR(50) DEFAULT NULL COMMENT 'Phone number',
  `address` TEXT DEFAULT NULL COMMENT 'Physical address',
  `gender` ENUM('Male', 'Female', 'Other') DEFAULT NULL,
  `date_of_birth` DATE DEFAULT NULL,
  `role` ENUM('patient', 'doctor', 'nurse', 'admin') NOT NULL COMMENT 'Role of the user in the system',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Appointments Table: Stores information about scheduled appointments
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `patient_id` INT NOT NULL COMMENT 'Foreign key referencing users.id (patient)',
  `doctor_id` INT NOT NULL COMMENT 'Foreign key referencing users.id (doctor)',
  `appointment_date` DATE NOT NULL,
  `appointment_time` TIME NOT NULL,
  `reason_for_visit` TEXT DEFAULT NULL COMMENT 'Brief reason for the appointment, provided by patient',
  `status` ENUM('scheduled', 'completed', 'cancelled', 'checked-in', 'ready_for_doctor') NOT NULL DEFAULT 'scheduled',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Medical Notes Table: Stores diagnosis and notes from doctors for each appointment
CREATE TABLE IF NOT EXISTS `medical_notes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `appointment_id` INT NOT NULL UNIQUE COMMENT 'Foreign key referencing appointments.id',
  `patient_id` INT NOT NULL COMMENT 'Foreign key referencing users.id (patient)',
  `doctor_id` INT NOT NULL COMMENT 'Foreign key referencing users.id (doctor)',
  `diagnosis_text` TEXT DEFAULT NULL COMMENT 'Doctor’s diagnosis',
  `recommendations` TEXT DEFAULT NULL COMMENT 'Doctor’s recommendations or prescriptions',
  `next_visit_date` DATE DEFAULT NULL COMMENT 'Recommended date for next visit',
  `referral_details` TEXT DEFAULT NULL COMMENT 'Details if referred to another specialist/clinic',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments Table: Stores information about payments and invoices
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `appointment_id` INT NOT NULL COMMENT 'Foreign key referencing appointments.id',
  `patient_id` INT NOT NULL COMMENT 'Foreign key referencing users.id (patient)',
  `amount` DECIMAL(10, 2) NOT NULL COMMENT 'Amount of the payment',
  `payment_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `payment_method` VARCHAR(100) DEFAULT NULL COMMENT 'e.g., Credit Card, Cash, Insurance',
  `status` ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
  `transaction_id` VARCHAR(255) DEFAULT NULL UNIQUE COMMENT 'Optional transaction ID from payment gateway',
  `invoice_details` TEXT DEFAULT NULL COMMENT 'Details of the invoice',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Referrals Table (Optional, as per user's list)
CREATE TABLE IF NOT EXISTS `referrals` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `appointment_id` INT NULL COMMENT 'Original appointment leading to referral',
  `patient_id` INT NOT NULL,
  `referring_doctor_id` INT NOT NULL,
  `referred_to_doctor_name` VARCHAR(255) DEFAULT NULL,
  `referred_to_clinic_name` VARCHAR(255) DEFAULT NULL,
  `reason_for_referral` TEXT DEFAULT NULL,
  `referral_date` DATE NOT NULL,
  `status` ENUM('pending', 'accepted', 'declined') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE SET NULL, -- Set null if original appointment is deleted
  FOREIGN KEY (`patient_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`referring_doctor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Clinic Settings Table: Stores global settings for the clinic
CREATE TABLE IF NOT EXISTS `clinic_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_name` VARCHAR(255) NOT NULL UNIQUE COMMENT 'e.g., clinic_name, working_hours_start, consultation_fee',
  `setting_value` TEXT DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example Clinic Settings Data (can be inserted by admin via UI)
-- INSERT INTO `clinic_settings` (`setting_name`, `setting_value`, `description`) VALUES
-- ("clinic_name", "My Health Clinic", "The official name of the clinic"),
-- ("working_hours_start", "09:00:00", "Clinic opening time"),
-- ("working_hours_end", "18:00:00", "Clinic closing time"),
-- ("default_consultation_fee", "75.00", "Standard consultation fee");

-- Adding some basic indexes for performance
ALTER TABLE `users` ADD INDEX `idx_user_email` (`email`);
ALTER TABLE `users` ADD INDEX `idx_user_role` (`role`);
ALTER TABLE `appointments` ADD INDEX `idx_appointment_patient` (`patient_id`);
ALTER TABLE `appointments` ADD INDEX `idx_appointment_doctor` (`doctor_id`);
ALTER TABLE `appointments` ADD INDEX `idx_appointment_date_time` (`appointment_date`, `appointment_time`);
ALTER TABLE `medical_notes` ADD INDEX `idx_medical_notes_patient` (`patient_id`);
ALTER TABLE `medical_notes` ADD INDEX `idx_medical_notes_doctor` (`doctor_id`);
ALTER TABLE `payments` ADD INDEX `idx_payment_patient` (`patient_id`);

