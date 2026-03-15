-- Migration: Add gender and contact_number to students table
ALTER TABLE students
ADD COLUMN gender ENUM('Male', 'Female', 'Other') NULL AFTER last_name,
ADD COLUMN contact_number VARCHAR(20) NULL AFTER email;
