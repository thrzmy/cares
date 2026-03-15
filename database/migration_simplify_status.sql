-- Migration: Simplify student status to only 'pending' and 'admitted'
-- Run this in phpMyAdmin BEFORE deploying the code changes

-- Step 1: Re-tag rejected/waitlisted students as pending
UPDATE students SET status = 'pending' WHERE status IN ('rejected', 'waitlisted');

-- Step 2: Alter the ENUM to only allow pending and admitted
ALTER TABLE students MODIFY COLUMN status ENUM('pending', 'admitted') NOT NULL DEFAULT 'pending';
