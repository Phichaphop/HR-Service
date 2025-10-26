-- =====================================================
-- HR SERVICE DATABASE SCHEMA
-- Version: 1.0
-- Database: if0_39800794_db
-- Charset: utf8mb4
-- Collation: utf8mb4_unicode_ci
-- =====================================================

-- Set Database Configuration
SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = '';

-- =====================================================
-- STEP 1: DROP EXISTING TABLES (in correct order)
-- =====================================================

-- Request/Transaction Tables
DROP TABLE IF EXISTS satisfaction_ratings;
DROP TABLE IF EXISTS document_submissions;
DROP TABLE IF EXISTS skill_test_requests;
DROP TABLE IF EXISTS supplies_requests;
DROP TABLE IF EXISTS locker_requests;
DROP TABLE IF EXISTS locker_usage_history;
DROP TABLE IF EXISTS shuttle_bus_requests;
DROP TABLE IF EXISTS id_card_requests;
DROP TABLE IF EXISTS certificate_requests;
DROP TABLE IF EXISTS leave_requests;
DROP TABLE IF EXISTS document_storage;

-- Organization & Employee Tables
DROP TABLE IF EXISTS company_info;
DROP TABLE IF EXISTS employees;

-- Locker Management
DROP TABLE IF EXISTS locker_master;

-- Master Data Tables
DROP TABLE IF EXISTS doc_type_master;
DROP TABLE IF EXISTS service_type_master;
DROP TABLE IF EXISTS service_category_master;
DROP TABLE IF EXISTS termination_reason_master;
DROP TABLE IF EXISTS status_master;
DROP TABLE IF EXISTS education_level_master;
DROP TABLE IF EXISTS nationality_master;
DROP TABLE IF EXISTS sex_master;
DROP TABLE IF EXISTS contribution_level_master;
DROP TABLE IF EXISTS customer_zone_master;
DROP TABLE IF EXISTS hiring_type_master;
DROP TABLE IF EXISTS labour_cost_master;
DROP TABLE IF EXISTS position_level_master;
DROP TABLE IF EXISTS position_master;
DROP TABLE IF EXISTS operation_master;
DROP TABLE IF EXISTS section_master;
DROP TABLE IF EXISTS department_master;
DROP TABLE IF EXISTS division_master;
DROP TABLE IF EXISTS function_master;
DROP TABLE IF EXISTS prefix_master;

-- Localization & Roles
DROP TABLE IF EXISTS localization_master;
DROP TABLE IF EXISTS roles;

-- Re-enable Foreign Key Checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- STEP 2: CREATE ROLES TABLE
-- =====================================================

CREATE TABLE roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE COMMENT 'Role code (employee, officer, admin)',
    role_name_th VARCHAR(100) COMMENT 'Role name in Thai',
    role_name_en VARCHAR(100) COMMENT 'Role name in English',
    role_name_my VARCHAR(100) COMMENT 'Role name in Myanmar',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role_name (role_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 3: CREATE LOCALIZATION MASTER TABLE
-- =====================================================

CREATE TABLE localization_master (
    key_id VARCHAR(100) PRIMARY KEY,
    th_text TEXT COMMENT 'Thai text',
    en_text TEXT COMMENT 'English text',
    my_text TEXT COMMENT 'Myanmar text',
    category VARCHAR(50) COMMENT 'Text category',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 4: CREATE MASTER DATA TABLES (PREFIX)
-- =====================================================

CREATE TABLE prefix_master (
    prefix_id INT PRIMARY KEY AUTO_INCREMENT,
    prefix_th VARCHAR(50) NOT NULL,
    prefix_en VARCHAR(50),
    prefix_my VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 5: CREATE MASTER DATA TABLES (ORGANIZATION)
-- =====================================================

CREATE TABLE function_master (
    function_id INT PRIMARY KEY AUTO_INCREMENT,
    function_name_th VARCHAR(100) NOT NULL,
    function_name_en VARCHAR(100),
    function_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE division_master (
    division_id INT PRIMARY KEY AUTO_INCREMENT,
    division_name_th VARCHAR(100) NOT NULL,
    division_name_en VARCHAR(100),
    division_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE department_master (
    department_id INT PRIMARY KEY AUTO_INCREMENT,
    department_name_th VARCHAR(100) NOT NULL,
    department_name_en VARCHAR(100),
    department_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE section_master (
    section_id INT PRIMARY KEY AUTO_INCREMENT,
    section_name_th VARCHAR(100) NOT NULL,
    section_name_en VARCHAR(100),
    section_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE operation_master (
    operation_id INT PRIMARY KEY AUTO_INCREMENT,
    operation_name_th VARCHAR(100) NOT NULL,
    operation_name_en VARCHAR(100),
    operation_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 6: CREATE MASTER DATA TABLES (POSITION)
-- =====================================================

CREATE TABLE position_master (
    position_id INT PRIMARY KEY AUTO_INCREMENT,
    position_name_th VARCHAR(100) NOT NULL,
    position_name_en VARCHAR(100),
    position_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE position_level_master (
    level_id INT PRIMARY KEY AUTO_INCREMENT,
    level_name_th VARCHAR(100) NOT NULL,
    level_name_en VARCHAR(100),
    level_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 7: CREATE MASTER DATA TABLES (EMPLOYEE ATTRIBUTES)
-- =====================================================

CREATE TABLE labour_cost_master (
    labour_cost_id INT PRIMARY KEY AUTO_INCREMENT,
    cost_name_th VARCHAR(100) NOT NULL,
    cost_name_en VARCHAR(100),
    cost_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE hiring_type_master (
    hiring_type_id INT PRIMARY KEY AUTO_INCREMENT,
    type_name_th VARCHAR(100) NOT NULL,
    type_name_en VARCHAR(100),
    type_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE customer_zone_master (
    zone_id INT PRIMARY KEY AUTO_INCREMENT,
    zone_name_th VARCHAR(100) NOT NULL,
    zone_name_en VARCHAR(100),
    zone_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contribution_level_master (
    contribution_id INT PRIMARY KEY AUTO_INCREMENT,
    level_name_th VARCHAR(100) NOT NULL,
    level_name_en VARCHAR(100),
    level_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sex_master (
    sex_id INT PRIMARY KEY AUTO_INCREMENT,
    sex_name_th VARCHAR(50) NOT NULL,
    sex_name_en VARCHAR(50),
    sex_name_my VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE nationality_master (
    nationality_id INT PRIMARY KEY AUTO_INCREMENT,
    nationality_th VARCHAR(100) NOT NULL,
    nationality_en VARCHAR(100),
    nationality_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE education_level_master (
    education_id INT PRIMARY KEY AUTO_INCREMENT,
    level_name_th VARCHAR(100) NOT NULL,
    level_name_en VARCHAR(100),
    level_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE status_master (
    status_id INT PRIMARY KEY AUTO_INCREMENT,
    status_name_th VARCHAR(100) NOT NULL,
    status_name_en VARCHAR(100),
    status_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE termination_reason_master (
    reason_id INT PRIMARY KEY AUTO_INCREMENT,
    reason_th VARCHAR(100) NOT NULL,
    reason_en VARCHAR(100),
    reason_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 8: CREATE MASTER DATA TABLES (SERVICES)
-- =====================================================

CREATE TABLE service_category_master (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name_th VARCHAR(100) NOT NULL,
    category_name_en VARCHAR(100),
    category_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE service_type_master (
    type_id INT PRIMARY KEY AUTO_INCREMENT,
    type_name_th VARCHAR(100) NOT NULL,
    type_name_en VARCHAR(100),
    type_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE doc_type_master (
    doc_type_id INT PRIMARY KEY AUTO_INCREMENT,
    type_name_th VARCHAR(100) NOT NULL,
    type_name_en VARCHAR(100),
    type_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 9: CREATE COMPANY INFO TABLE
-- =====================================================

CREATE TABLE company_info (
    company_id INT PRIMARY KEY AUTO_INCREMENT,
    company_name VARCHAR(255) NOT NULL,
    company_phone VARCHAR(20),
    company_fax VARCHAR(20),
    company_address TEXT,
    representative_name VARCHAR(200),
    company_logo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 10: CREATE EMPLOYEES TABLE (MAIN)
-- =====================================================

CREATE TABLE employees (
    employee_id VARCHAR(8) PRIMARY KEY COMMENT 'Employee ID (e.g. 90681322)',
    prefix_id INT COMMENT 'Title/Prefix',
    full_name_th VARCHAR(200),
    full_name_en VARCHAR(200),
    function_id INT COMMENT 'Function',
    division_id INT COMMENT 'Division',
    department_id INT COMMENT 'Department',
    section_id INT COMMENT 'Section',
    operation_id INT COMMENT 'Operation',
    position_id INT COMMENT 'Position',
    position_level_id INT COMMENT 'Position Level',
    labour_cost_id INT COMMENT 'Labour Cost Type',
    hiring_type_id INT COMMENT 'Hiring Type',
    customer_zone_id INT COMMENT 'Customer Zone',
    contribution_level_id INT COMMENT 'Contribution Level',
    sex_id INT COMMENT 'Gender',
    nationality_id INT COMMENT 'Nationality',
    birthday DATE,
    age INT GENERATED ALWAYS AS (YEAR(CURDATE()) - YEAR(birthday)) STORED COMMENT 'Auto-calculated',
    education_level_id INT,
    phone_no VARCHAR(20),
    address_village VARCHAR(200),
    address_subdistrict VARCHAR(100),
    address_district VARCHAR(100),
    address_province VARCHAR(100),
    date_of_hire DATE,
    year_of_service INT GENERATED ALWAYS AS (YEAR(CURDATE()) - YEAR(date_of_hire)) STORED COMMENT 'Auto-calculated',
    date_of_termination DATE,
    month_of_termination VARCHAR(20),
    status_id INT,
    reason_for_termination_id INT,
    suggestion TEXT,
    remark TEXT,
    username VARCHAR(100) UNIQUE,
    password VARCHAR(255) COMMENT 'Password hash (bcrypt)',
    role_id INT,
    profile_pic_path VARCHAR(255),
    theme_mode VARCHAR(10) DEFAULT 'light',
    language_preference VARCHAR(5) DEFAULT 'th',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign Keys
    FOREIGN KEY (prefix_id) REFERENCES prefix_master(prefix_id) ON DELETE SET NULL,
    FOREIGN KEY (function_id) REFERENCES function_master(function_id) ON DELETE SET NULL,
    FOREIGN KEY (division_id) REFERENCES division_master(division_id) ON DELETE SET NULL,
    FOREIGN KEY (department_id) REFERENCES department_master(department_id) ON DELETE SET NULL,
    FOREIGN KEY (section_id) REFERENCES section_master(section_id) ON DELETE SET NULL,
    FOREIGN KEY (operation_id) REFERENCES operation_master(operation_id) ON DELETE SET NULL,
    FOREIGN KEY (position_id) REFERENCES position_master(position_id) ON DELETE SET NULL,
    FOREIGN KEY (position_level_id) REFERENCES position_level_master(level_id) ON DELETE SET NULL,
    FOREIGN KEY (labour_cost_id) REFERENCES labour_cost_master(labour_cost_id) ON DELETE SET NULL,
    FOREIGN KEY (hiring_type_id) REFERENCES hiring_type_master(hiring_type_id) ON DELETE SET NULL,
    FOREIGN KEY (customer_zone_id) REFERENCES customer_zone_master(zone_id) ON DELETE SET NULL,
    FOREIGN KEY (contribution_level_id) REFERENCES contribution_level_master(contribution_id) ON DELETE SET NULL,
    FOREIGN KEY (sex_id) REFERENCES sex_master(sex_id) ON DELETE SET NULL,
    FOREIGN KEY (nationality_id) REFERENCES nationality_master(nationality_id) ON DELETE SET NULL,
    FOREIGN KEY (education_level_id) REFERENCES education_level_master(education_id) ON DELETE SET NULL,
    FOREIGN KEY (status_id) REFERENCES status_master(status_id) ON DELETE SET NULL,
    FOREIGN KEY (reason_for_termination_id) REFERENCES termination_reason_master(reason_id) ON DELETE SET NULL,
    FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE SET NULL,
    
    -- Indexes
    INDEX idx_employee_id (employee_id),
    INDEX idx_status (status_id),
    INDEX idx_division (division_id),
    INDEX idx_department (department_id),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 11: CREATE LOCKER MANAGEMENT TABLES
-- =====================================================

CREATE TABLE locker_master (
    locker_id INT PRIMARY KEY AUTO_INCREMENT,
    locker_number VARCHAR(50) NOT NULL UNIQUE,
    locker_status VARCHAR(20) DEFAULT 'available' COMMENT 'available, occupied, maintenance',
    locker_location VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_locker_status (locker_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 12: CREATE REQUEST TABLES (A-H)
-- =====================================================

-- A. Leave Requests
CREATE TABLE leave_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8) NOT NULL,
    request_type VARCHAR(50) DEFAULT 'leave' COMMENT 'Request type identifier',
    leave_type VARCHAR(100),
    leave_start_date DATE,
    leave_end_date DATE,
    reason TEXT,
    handler_id VARCHAR(8) COMMENT 'Officer handling the request',
    request_status VARCHAR(20) DEFAULT 'new' COMMENT 'new, in_progress, complete, cancelled',
    handler_remarks TEXT,
    satisfaction_score INT DEFAULT NULL COMMENT 'Satisfaction rating 1-5',
    feedback_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    INDEX idx_status (request_status),
    INDEX idx_created_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- B. Certificate Requests
CREATE TABLE certificate_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8) NOT NULL,
    request_type VARCHAR(50) DEFAULT 'certificate',
    certificate_no VARCHAR(100),
    certificate_format VARCHAR(20),
    handler_id VARCHAR(8),
    request_status VARCHAR(20) DEFAULT 'new',
    handler_remarks TEXT,
    satisfaction_score INT DEFAULT NULL,
    feedback_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    INDEX idx_status (request_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- C. ID Card Requests
CREATE TABLE id_card_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8) NOT NULL,
    request_type VARCHAR(50) DEFAULT 'id_card',
    card_type VARCHAR(50),
    handler_id VARCHAR(8),
    request_status VARCHAR(20) DEFAULT 'new',
    handler_remarks TEXT,
    satisfaction_score INT DEFAULT NULL,
    feedback_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    INDEX idx_status (request_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- D. Shuttle Bus Requests
CREATE TABLE shuttle_bus_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8) NOT NULL,
    request_type VARCHAR(50) DEFAULT 'shuttle_bus',
    bus_route VARCHAR(100),
    request_date DATE,
    handler_id VARCHAR(8),
    request_status VARCHAR(20) DEFAULT 'new',
    handler_remarks TEXT,
    satisfaction_score INT DEFAULT NULL,
    feedback_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    INDEX idx_status (request_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- E. Locker Usage Requests
CREATE TABLE locker_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8) NOT NULL,
    request_type VARCHAR(50) DEFAULT 'locker',
    locker_id INT,
    handler_id VARCHAR(8),
    request_status VARCHAR(20) DEFAULT 'new',
    handler_remarks TEXT,
    satisfaction_score INT DEFAULT NULL,
    feedback_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (locker_id) REFERENCES locker_master(locker_id) ON DELETE SET NULL,
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    INDEX idx_status (request_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Locker Usage History (for tracking)
CREATE TABLE locker_usage_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    locker_id INT NOT NULL,
    employee_id VARCHAR(8) NOT NULL,
    usage_start_date DATE,
    usage_end_date DATE,
    return_date DATE,
    status_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (locker_id) REFERENCES locker_master(locker_id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    INDEX idx_locker (locker_id),
    INDEX idx_employee (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- F. Supplies/Uniform Requests
CREATE TABLE supplies_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8) NOT NULL,
    request_type VARCHAR(50) DEFAULT 'supplies',
    supply_category VARCHAR(100) COMMENT 'Office Supplies, Equipment, Uniform, Safety',
    supply_details TEXT COMMENT 'Item list/description',
    quantity INT,
    handler_id VARCHAR(8),
    request_status VARCHAR(20) DEFAULT 'new',
    handler_remarks TEXT,
    satisfaction_score INT DEFAULT NULL,
    feedback_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    INDEX idx_status (request_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- G. Skill Test Requests
CREATE TABLE skill_test_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8) NOT NULL,
    request_type VARCHAR(50) DEFAULT 'skill_test',
    test_skill VARCHAR(100),
    test_date DATE,
    handler_id VARCHAR(8),
    request_status VARCHAR(20) DEFAULT 'new',
    handler_remarks TEXT,
    satisfaction_score INT DEFAULT NULL,
    feedback_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    INDEX idx_status (request_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- H. Document Submission
CREATE TABLE document_submissions (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8) NOT NULL,
    request_type VARCHAR(50) DEFAULT 'document_submission',
    service_category INT COMMENT 'Category from service_category_master',
    service_type INT COMMENT 'Type from service_type_master',
    submitted_documents TEXT COMMENT 'List of documents submitted',
    handler_id VARCHAR(8),
    request_status VARCHAR(20) DEFAULT 'new',
    handler_remarks TEXT,
    satisfaction_score INT DEFAULT NULL,
    feedback_text TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (service_category) REFERENCES service_category_master(category_id) ON DELETE SET NULL,
    FOREIGN KEY (service_type) REFERENCES service_type_master(type_id) ON DELETE SET NULL,
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    INDEX idx_status (request_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 13: CREATE DOCUMENT STORAGE TABLE
-- =====================================================

CREATE TABLE document_storage (
    doc_id INT PRIMARY KEY AUTO_INCREMENT COMMENT 'Document ID',
    file_name VARCHAR(255) NOT NULL COMMENT 'Document name given by user',
    file_path VARCHAR(500) NOT NULL COMMENT 'File path in system',
    doc_type INT COMMENT 'Document type FK',
    upload_by VARCHAR(8) NOT NULL COMMENT 'Employee ID who uploaded',
    upload_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Upload date',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doc_type) REFERENCES doc_type_master(doc_type_id) ON DELETE SET NULL,
    FOREIGN KEY (upload_by) REFERENCES employees(employee_id) ON DELETE CASCADE,
    INDEX idx_doc_type (doc_type),
    INDEX idx_upload_by (upload_by),
    INDEX idx_upload_date (upload_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 14: CREATE SATISFACTION RATINGS TABLE
-- =====================================================

CREATE TABLE satisfaction_ratings (
    rating_id INT PRIMARY KEY AUTO_INCREMENT,
    request_id INT COMMENT 'Associated request ID',
    employee_id VARCHAR(8) NOT NULL,
    satisfaction_score INT NOT NULL COMMENT '1-5 rating',
    feedback_text TEXT COMMENT 'Feedback/suggestion',
    rating_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE,
    INDEX idx_employee (employee_id),
    INDEX idx_score (satisfaction_score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- STEP 15: INSERT INITIAL DATA
-- =====================================================

-- Insert Roles
INSERT INTO roles (role_name, role_name_th, role_name_en, role_name_my) VALUES
('admin', 'ผู้ดูแลระบบ', 'Administrator', 'စနစ်စီမံခန့်ခွဲသူ'),
('officer', 'เจ้าหน้าที่', 'Officer', 'အရာရှိ'),
('employee', 'พนักงาน', 'Employee', 'အလုပ်သမား');

-- Insert Prefix Master
INSERT INTO prefix_master (prefix_th, prefix_en, prefix_my) VALUES
('นาย', 'Mr.', 'သူ'),
('นาง', 'Mrs.', 'သူမ'),
('นางสาว', 'Ms.', 'အစ်ကို'),
('ดร.', 'Dr.', 'ဒေါက်တာ');

-- Insert Sex Master
INSERT INTO sex_master (sex_name_th, sex_name_en, sex_name_my) VALUES
('ชาย', 'Male', 'အထီး'),
('หญิง', 'Female', 'အမ');

-- Insert Status Master
INSERT INTO status_master (status_name_th, status_name_en, status_name_my) VALUES
('ทำงาน', 'Active', 'လုပ်ဆောင်နေ'),
('ลาออก', 'Resigned', '辞職'),
('เกษียณ', 'Retired', 'အငြိမ်း'),
('ปลดออก', 'Terminated', 'ပိတ်ဆိုင်း');

-- Insert Education Level Master
INSERT INTO education_level_master (level_name_th, level_name_en, level_name_my) VALUES
('ประมาณน้อย', 'Below Average', 'အောက်မြှောက်'),
('ประมาณปกติ', 'Average', 'ပုံမှန်'),
('ประมาณสูง', 'Above Average', 'အထက်မြှောက်'),
('สูงมาก', 'Excellent', 'လွန်ကဆန်း');

-- Insert Hiring Type Master
INSERT INTO hiring_type_master (type_name_th, type_name_en, type_name_my) VALUES
('ประจำ', 'Permanent', 'အစ္စလည်'),
('สัญญา', 'Contract', 'စာချုပ်'),
('ชั่วคราว', 'Temporary', '잠시');

-- Insert Function Master
INSERT INTO function_master (function_name_th, function_name_en, function_name_my) VALUES
('บริหาร', 'Management', 'စီမံခန့်ခွဲမှု'),
('ขายและการตลาด', 'Sales & Marketing', 'ရောင်းချမှုနှင့်စျေးကွက်ဆွေ'),
('ผลิตการ', 'Production', 'ထုတ်လုပ်မှု');

-- Insert Nationality Master
INSERT INTO nationality_master (nationality_th, nationality_en, nationality_my) VALUES
('ไทย', 'Thai', 'ထိုင်းပြည်'),
('ลาว', 'Lao', 'လာအို'),
('缅甸', 'Myanmar', 'မြန်မာ');

-- Insert Service Category Master
INSERT INTO service_category_master (category_name_th, category_name_en, category_name_my) VALUES
('ใบลา', 'Leave Certificate', 'ခွင့်ပြုချက်'),
('หนังสือรับรอง', 'Certificate', 'လက်မှတ်'),
('บัตรพนักงาน', 'ID Card', 'ခွင့်ပြုပత်'),
('รถรับส่ง', 'Shuttle Bus', 'ကားစီးခွင့်');

-- Insert Service Type Master
INSERT INTO service_type_master (type_name_th, type_name_en, type_name_my) VALUES
('ส่วนตัว', 'Individual', 'ပုဂ္ဂလိက'),
('กลุ่ม', 'Group', 'အုပ်စုအဖြစ်');

-- Insert Document Type Master
INSERT INTO doc_type_master (type_name_th, type_name_en, type_name_my) VALUES
('เอกสารบริษัท', 'Company Documents', 'ကုမ္ပဏီစာရွက်စာတမ်း'),
('เอกสารการเงิน', 'Financial Documents', 'ငွေစာရွက်စာတမ်း'),
('เอกสารพนักงาน', 'Employee Documents', 'အလုပ်သမားစာရွက်စာတမ်း'),
('เอกสารปฏิบัติการ', 'Operational Documents', 'လုပ်ဆောင်ချက်စာရွက်စာတမ်း'),
('ข้อตกลงและสัญญา', 'Agreements & Contracts', 'သဘောတူညီချက်နှင့်စာချုပ်'),
('แบบฟอร์ม', 'Forms', 'ဖွင့်စာ'),
('รายงาน', 'Reports', 'အစီရင်ခံစာများ'),
('ใบรับรอง', 'Certificates', 'လက်မှတ်များ');

-- Insert Company Info (Sample)
INSERT INTO company_info (company_name, company_phone, company_fax, company_address, representative_name, company_logo_path) VALUES
('บริษัท ตัวอย่าง จำกัด', '+66-2-xxx-xxxx', '+66-2-xxx-xxxx', 'เซ็นทราล เวิลด์ กรุงเทพฯ', 'นายสมชาย', '/uploads/company/logo.png');

-- =====================================================
-- STEP 16: CREATE INDEXES FOR PERFORMANCE
-- =====================================================

CREATE INDEX idx_employee_status ON employees(status_id);
CREATE INDEX idx_employee_division ON employees(division_id);
CREATE INDEX idx_employee_department ON employees(department_id);
CREATE INDEX idx_employee_position ON employees(position_id);
CREATE INDEX idx_employee_role ON employees(role_id);

CREATE INDEX idx_leave_employee ON leave_requests(employee_id);
CREATE INDEX idx_leave_status ON leave_requests(request_status);
CREATE INDEX idx_leave_date ON leave_requests(created_at);

CREATE INDEX idx_certificate_employee ON certificate_requests(employee_id);
CREATE INDEX idx_certificate_status ON certificate_requests(request_status);

CREATE INDEX idx_idcard_employee ON id_card_requests(employee_id);
CREATE INDEX idx_idcard_status ON id_card_requests(request_status);

CREATE INDEX idx_locker_status ON locker_requests(request_status);
CREATE INDEX idx_locker_employee ON locker_requests(employee_id);

CREATE INDEX idx_document_storage_type ON document_storage(doc_type);
CREATE INDEX idx_document_storage_date ON document_storage(upload_at);

-- =====================================================
-- FINAL: Enable Foreign Key Checks
-- =====================================================

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- INSTALLATION COMPLETE
-- =====================================================
/*

สรุปตาราง:

ROLES & LOCALIZATION:
- roles (3 roles)
- localization_master

MASTER DATA (20 ตาราง):
- prefix_master
- function_master
- division_master
- department_master
- section_master
- operation_master
- position_master
- position_level_master
- labour_cost_master
- hiring_type_master
- customer_zone_master
- contribution_level_master
- sex_master
- nationality_master
- education_level_master
- status_master
- termination_reason_master
- service_category_master
- service_type_master
- doc_type_master

MAIN DATA:
- company_info
- employees
- locker_master

REQUESTS (8 ประเภท):
- leave_requests (A)
- certificate_requests (B)
- id_card_requests (C)
- shuttle_bus_requests (D)
- locker_requests (E)
- supplies_requests (F)
- skill_test_requests (G)
- document_submissions (H)

SUPPORT TABLES:
- locker_usage_history
- document_storage
- satisfaction_ratings

รวม 34 ตาราง

การใช้งาน:
1. mysql -u root -p if0_39800794_db < schema.sql
2. ระบบพร้อมใช้ทันที

*/