-- ===================================
-- HR SERVICE DATABASE TRIGGERS
-- Auto-calculate age and year_of_service
-- ===================================

-- Use this file in phpMyAdmin or MySQL Workbench
-- DO NOT use in PHP multi_query

USE db_hr_service;

-- Drop existing triggers
DROP TRIGGER IF EXISTS calculate_employee_fields_insert;
DROP TRIGGER IF EXISTS calculate_employee_fields_update;

-- ===================================
-- TRIGGER 1: Calculate on INSERT
-- ===================================

DELIMITER $$

CREATE TRIGGER calculate_employee_fields_insert
BEFORE INSERT ON employees
FOR EACH ROW
BEGIN
    -- Calculate age from birthday
    IF NEW.birthday IS NOT NULL THEN
        SET NEW.age = TIMESTAMPDIFF(YEAR, NEW.birthday, CURDATE());
    END IF;
    
    -- Calculate years of service from hire date
    IF NEW.date_of_hire IS NOT NULL THEN
        SET NEW.year_of_service = TIMESTAMPDIFF(YEAR, NEW.date_of_hire, CURDATE());
    END IF;
END$$

DELIMITER ;

-- ===================================
-- TRIGGER 2: Calculate on UPDATE
-- ===================================

DELIMITER $$

CREATE TRIGGER calculate_employee_fields_update
BEFORE UPDATE ON employees
FOR EACH ROW
BEGIN
    -- Calculate age from birthday
    IF NEW.birthday IS NOT NULL THEN
        SET NEW.age = TIMESTAMPDIFF(YEAR, NEW.birthday, CURDATE());
    END IF;
    
    -- Calculate years of service from hire date
    IF NEW.date_of_hire IS NOT NULL THEN
        SET NEW.year_of_service = TIMESTAMPDIFF(YEAR, NEW.date_of_hire, CURDATE());
    END IF;
END$$

DELIMITER ;

-- ===================================
-- VERIFY TRIGGERS
-- ===================================

SHOW TRIGGERS FROM db_hr_service WHERE `Table` = 'employees';

-- Test the triggers
-- INSERT INTO employees (employee_id, birthday, date_of_hire, ...) VALUES (...);
-- The age and year_of_service should be calculated automatically!