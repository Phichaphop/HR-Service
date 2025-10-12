-- ===================================
-- SQL Mode Fix for TIMESTAMP Issues
-- Run this if you encounter TIMESTAMP errors
-- ===================================

-- Check current SQL mode
SELECT @@sql_mode;

-- Set SQL mode to allow zero dates and NULL timestamps
SET GLOBAL sql_mode = 'NO_ENGINE_SUBSTITUTION';

-- Or use this for more permissive mode
SET GLOBAL sql_mode = '';

-- For session only (temporary)
SET SESSION sql_mode = '';

-- Check again
SELECT @@sql_mode;

-- Note: You may need to restart MySQL service after changing GLOBAL settings