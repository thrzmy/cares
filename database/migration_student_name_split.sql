-- Migration to split student name into first_name and last_name
-- Compatible with MySQL 8+

ALTER TABLE students
ADD COLUMN first_name VARCHAR(75) AFTER application_number,
ADD COLUMN last_name VARCHAR(75) AFTER first_name;

-- Simple split: everything before the last space is first_name, everything after is last_name
-- For names without spaces, put it all in last_name
UPDATE students
SET
  first_name = CASE
    WHEN LOCATE(' ', name) = 0 THEN ''
    ELSE SUBSTRING_INDEX(name, ' ', 1)
  END,
  last_name = CASE
    WHEN LOCATE(' ', name) = 0 THEN name
    ELSE SUBSTRING(name, LOCATE(' ', name) + 1)
  END;

-- Now drop the old column
ALTER TABLE students DROP COLUMN name;
