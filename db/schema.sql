-- ===================================
-- HR SERVICE DATABASE SCHEMA
-- This file contains all table creation statements
-- NO DELIMITER - NO TRIGGERS (created separately)
-- ===================================

-- Disable foreign key checks for clean installation
SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = '';

-- Drop existing tables if they exist
DROP TABLE IF EXISTS document_delivery;
DROP TABLE IF EXISTS document_submissions;
DROP TABLE IF EXISTS skill_test_requests;
DROP TABLE IF EXISTS supplies_requests;
DROP TABLE IF EXISTS locker_requests;
DROP TABLE IF EXISTS shuttle_bus_requests;
DROP TABLE IF EXISTS id_card_requests;
DROP TABLE IF EXISTS certificate_requests;
DROP TABLE IF EXISTS leave_requests;
DROP TABLE IF EXISTS online_documents;
DROP TABLE IF EXISTS locker_usage_history;
DROP TABLE IF EXISTS locker_master;
DROP TABLE IF EXISTS company_info;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS certificate_types;
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
DROP TABLE IF EXISTS localization_master;
DROP TABLE IF EXISTS roles;

-- Enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- ===================================
-- MASTER DATA TABLES
-- ===================================

CREATE TABLE roles (
    role_id INT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(50) NOT NULL UNIQUE,
    role_name_th VARCHAR(100),
    role_name_en VARCHAR(100),
    role_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE localization_master (
    key_id VARCHAR(100) PRIMARY KEY,
    th_text TEXT,
    en_text TEXT,
    my_text TEXT,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE prefix_master (
    prefix_id INT PRIMARY KEY AUTO_INCREMENT,
    prefix_th VARCHAR(50),
    prefix_en VARCHAR(50),
    prefix_my VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE function_master (
    function_id INT PRIMARY KEY AUTO_INCREMENT,
    function_name_th VARCHAR(100),
    function_name_en VARCHAR(100),
    function_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE division_master (
    division_id INT PRIMARY KEY AUTO_INCREMENT,
    division_name_th VARCHAR(100),
    division_name_en VARCHAR(100),
    division_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE department_master (
    department_id INT PRIMARY KEY AUTO_INCREMENT,
    department_name_th VARCHAR(100),
    department_name_en VARCHAR(100),
    department_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE section_master (
    section_id INT PRIMARY KEY AUTO_INCREMENT,
    section_name_th VARCHAR(100),
    section_name_en VARCHAR(100),
    section_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE operation_master (
    operation_id INT PRIMARY KEY AUTO_INCREMENT,
    operation_name_th VARCHAR(100),
    operation_name_en VARCHAR(100),
    operation_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE position_master (
    position_id INT PRIMARY KEY AUTO_INCREMENT,
    position_name_th VARCHAR(100),
    position_name_en VARCHAR(100),
    position_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE position_level_master (
    level_id INT PRIMARY KEY AUTO_INCREMENT,
    level_name_th VARCHAR(50),
    level_name_en VARCHAR(50),
    level_name_my VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE labour_cost_master (
    labour_cost_id INT PRIMARY KEY AUTO_INCREMENT,
    cost_name_th VARCHAR(100),
    cost_name_en VARCHAR(100),
    cost_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE hiring_type_master (
    hiring_type_id INT PRIMARY KEY AUTO_INCREMENT,
    type_name_th VARCHAR(100),
    type_name_en VARCHAR(100),
    type_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE customer_zone_master (
    zone_id INT PRIMARY KEY AUTO_INCREMENT,
    zone_name_th VARCHAR(100),
    zone_name_en VARCHAR(100),
    zone_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contribution_level_master (
    contribution_id INT PRIMARY KEY AUTO_INCREMENT,
    level_name_th VARCHAR(100),
    level_name_en VARCHAR(100),
    level_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE sex_master (
    sex_id INT PRIMARY KEY AUTO_INCREMENT,
    sex_name_th VARCHAR(50),
    sex_name_en VARCHAR(50),
    sex_name_my VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE nationality_master (
    nationality_id INT PRIMARY KEY AUTO_INCREMENT,
    nationality_th VARCHAR(100),
    nationality_en VARCHAR(100),
    nationality_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE education_level_master (
    education_id INT PRIMARY KEY AUTO_INCREMENT,
    level_name_th VARCHAR(100),
    level_name_en VARCHAR(100),
    level_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE status_master (
    status_id INT PRIMARY KEY AUTO_INCREMENT,
    status_name_th VARCHAR(50),
    status_name_en VARCHAR(50),
    status_name_my VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE termination_reason_master (
    reason_id INT PRIMARY KEY AUTO_INCREMENT,
    reason_th VARCHAR(200),
    reason_en VARCHAR(200),
    reason_my VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE service_category_master (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name_th VARCHAR(100),
    category_name_en VARCHAR(100),
    category_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE service_type_master (
    type_id INT PRIMARY KEY AUTO_INCREMENT,
    type_name_th VARCHAR(100),
    type_name_en VARCHAR(100),
    type_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE doc_type_master (
    doc_type_id INT PRIMARY KEY AUTO_INCREMENT,
    type_name_th VARCHAR(100),
    type_name_en VARCHAR(100),
    type_name_my VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- CERTIFICATE TYPES (ต้องสร้างก่อน certificate_requests)
-- ===================================

CREATE TABLE certificate_types (
    cert_type_id INT PRIMARY KEY AUTO_INCREMENT,
    type_name_th VARCHAR(200) NOT NULL,
    type_name_en VARCHAR(200),
    type_name_my VARCHAR(200),
    template_content TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- EMPLOYEE & COMPANY DATA
-- ===================================

CREATE TABLE employees (
    employee_id VARCHAR(8) PRIMARY KEY,
    prefix_id INT,
    full_name_th VARCHAR(200),
    full_name_en VARCHAR(200),
    function_id INT,
    division_id INT,
    department_id INT,
    section_id INT,
    operation_id INT,
    position_id INT,
    position_level_id INT,
    labour_cost_id INT,
    hiring_type_id INT,
    customer_zone_id INT,
    contribution_level_id INT,
    sex_id INT,
    nationality_id INT,
    birthday DATE,
    age INT,
    education_level_id INT,
    phone_no VARCHAR(20),
    address_village VARCHAR(200),
    address_subdistrict VARCHAR(100),
    address_district VARCHAR(100),
    address_province VARCHAR(100),
    date_of_hire DATE,
    year_of_service INT,
    date_of_termination DATE,
    month_of_termination VARCHAR(20),
    status_id INT,
    reason_for_termination_id INT,
    suggestion TEXT,
    remark TEXT,
    username VARCHAR(100) UNIQUE,
    password VARCHAR(255),
    role_id INT,
    profile_pic_path VARCHAR(255),
    theme_color VARCHAR(20) DEFAULT 'blue',
    language_preference VARCHAR(5) DEFAULT 'th',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (prefix_id) REFERENCES prefix_master(prefix_id),
    FOREIGN KEY (function_id) REFERENCES function_master(function_id),
    FOREIGN KEY (division_id) REFERENCES division_master(division_id),
    FOREIGN KEY (department_id) REFERENCES department_master(department_id),
    FOREIGN KEY (section_id) REFERENCES section_master(section_id),
    FOREIGN KEY (operation_id) REFERENCES operation_master(operation_id),
    FOREIGN KEY (position_id) REFERENCES position_master(position_id),
    FOREIGN KEY (position_level_id) REFERENCES position_level_master(level_id),
    FOREIGN KEY (labour_cost_id) REFERENCES labour_cost_master(labour_cost_id),
    FOREIGN KEY (hiring_type_id) REFERENCES hiring_type_master(hiring_type_id),
    FOREIGN KEY (customer_zone_id) REFERENCES customer_zone_master(zone_id),
    FOREIGN KEY (contribution_level_id) REFERENCES contribution_level_master(contribution_id),
    FOREIGN KEY (sex_id) REFERENCES sex_master(sex_id),
    FOREIGN KEY (nationality_id) REFERENCES nationality_master(nationality_id),
    FOREIGN KEY (education_level_id) REFERENCES education_level_master(education_id),
    FOREIGN KEY (status_id) REFERENCES status_master(status_id),
    FOREIGN KEY (reason_for_termination_id) REFERENCES termination_reason_master(reason_id),
    FOREIGN KEY (role_id) REFERENCES roles(role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE company_info (
    company_id INT PRIMARY KEY AUTO_INCREMENT,
    company_name_th VARCHAR(200),
    company_name_en VARCHAR(200),
    company_name_my VARCHAR(200),
    phone VARCHAR(20),
    fax VARCHAR(20),
    address TEXT,
    representative_name VARCHAR(200),
    company_logo_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- LOCKER MANAGEMENT
-- ===================================

CREATE TABLE locker_master (
    locker_id INT PRIMARY KEY AUTO_INCREMENT,
    locker_number VARCHAR(20) UNIQUE NOT NULL,
    locker_location VARCHAR(100),
    status ENUM('Available', 'Occupied', 'Maintenance') DEFAULT 'Available',
    current_owner_id VARCHAR(8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_owner_id) REFERENCES employees(employee_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE locker_usage_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    locker_id INT,
    employee_id VARCHAR(8),
    assigned_date TIMESTAMP NULL DEFAULT NULL,
    returned_date TIMESTAMP NULL DEFAULT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (locker_id) REFERENCES locker_master(locker_id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- ONLINE DOCUMENT STORAGE
-- ===================================

CREATE TABLE online_documents (
    doc_id INT PRIMARY KEY AUTO_INCREMENT,
    file_name_custom VARCHAR(255),
    file_path VARCHAR(500),
    doc_type_id INT,
    upload_by VARCHAR(8),
    upload_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (doc_type_id) REFERENCES doc_type_master(doc_type_id),
    FOREIGN KEY (upload_by) REFERENCES employees(employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- HR SERVICE WORKFLOWS
-- ===================================

CREATE TABLE leave_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8),
    leave_type VARCHAR(100),
    start_date DATE,
    end_date DATE,
    total_days INT,
    reason TEXT,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(8),
    handler_remarks TEXT,
    satisfaction_score INT CHECK (satisfaction_score BETWEEN 1 AND 5),
    satisfaction_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE certificate_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    certificate_no VARCHAR(50),
    cert_type_id INT,
    employee_id VARCHAR(8),
    employee_name VARCHAR(200),
    position VARCHAR(200),
    division VARCHAR(200),
    date_of_hire DATE,
    hiring_type VARCHAR(100),
    base_salary DECIMAL(10,2),
    purpose TEXT,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(8),
    handler_remarks TEXT,
    satisfaction_score INT,
    satisfaction_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (cert_type_id) REFERENCES certificate_types(cert_type_id),
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE id_card_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8),
    reason VARCHAR(200),
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(8),
    handler_remarks TEXT,
    satisfaction_score INT,
    satisfaction_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE shuttle_bus_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8),
    route VARCHAR(200),
    pickup_location VARCHAR(200),
    start_date DATE,
    reason TEXT,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(8),
    handler_remarks TEXT,
    satisfaction_score INT,
    satisfaction_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE locker_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8),
    reason TEXT,
    assigned_locker_id INT,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(8),
    handler_remarks TEXT,
    satisfaction_score INT,
    satisfaction_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (assigned_locker_id) REFERENCES locker_master(locker_id),
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE supplies_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8),
    request_type ENUM('Office Supplies', 'Work Equipment', 'Uniform', 'Safety Equipment'),
    items_list TEXT,
    quantity INT,
    reason TEXT,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(8),
    handler_remarks TEXT,
    satisfaction_score INT,
    satisfaction_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE skill_test_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8),
    skill_name VARCHAR(200),
    test_date DATE,
    reason TEXT,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(8),
    handler_remarks TEXT,
    satisfaction_score INT,
    satisfaction_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE document_submissions (
    submission_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8),
    employee_name VARCHAR(200),
    position VARCHAR(200),
    position_level VARCHAR(100),
    section VARCHAR(100),
    service_category_id INT,
    service_type_id INT,
    submission_date TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(8),
    handler_remarks TEXT,
    satisfaction_score INT CHECK (satisfaction_score BETWEEN 1 AND 5),
    satisfaction_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (service_category_id) REFERENCES service_category_master(category_id),
    FOREIGN KEY (service_type_id) REFERENCES service_type_master(type_id),
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- DOCUMENT DELIVERY (ระบบลงชื่อส่งเอกสาร)
-- ===================================

CREATE TABLE document_delivery (
    delivery_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(8) NOT NULL,
    delivery_type ENUM('ส่ง', 'รับ') DEFAULT 'ส่ง',
    service_type ENUM('คนเดียว', 'กลุ่ม') DEFAULT 'คนเดียว',
    document_category_id INT,
    remarks TEXT,
    satisfaction_score INT CHECK (satisfaction_score BETWEEN 1 AND 5),
    delivery_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (document_category_id) REFERENCES service_category_master(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- INDEXES FOR PERFORMANCE
-- ===================================

CREATE INDEX idx_employee_username ON employees(username);
CREATE INDEX idx_employee_role ON employees(role_id);
CREATE INDEX idx_employee_status ON employees(status_id);
CREATE INDEX idx_locker_status ON locker_master(status);
CREATE INDEX idx_leave_status ON leave_requests(status);
CREATE INDEX idx_cert_status ON certificate_requests(status);
CREATE INDEX idx_cert_type ON certificate_requests(cert_type_id);
CREATE INDEX idx_doc_type ON online_documents(doc_type_id);
CREATE INDEX idx_doc_submit_status ON document_submissions(status);
CREATE INDEX idx_doc_delivery_date ON document_delivery(delivery_date);
CREATE INDEX idx_doc_delivery_emp ON document_delivery(employee_id);

-- ===================================
-- COMPLAINT SYSTEM (Anonymous Complaints)
-- ระบบร้องเรียนแบบไม่เปิดเผยตัวตน
-- UPDATED: Removed icon_class field
-- ===================================

-- Drop existing tables if they exist
DROP TABLE IF EXISTS complaint_activity_log;
DROP TABLE IF EXISTS complaint_complainer_audit;
DROP TABLE IF EXISTS complaints;
DROP TABLE IF EXISTS complaint_category_master;

-- ===================================
-- 1. COMPLAINT CATEGORY MASTER (ประเภทการร้องเรียน)
-- ===================================
CREATE TABLE IF NOT EXISTS complaint_category_master (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name_th VARCHAR(200) NOT NULL,
    category_name_en VARCHAR(200) NOT NULL,
    category_name_my VARCHAR(200) NOT NULL,
    description_th TEXT,
    description_en TEXT,
    description_my TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_active (is_active),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- 2. COMPLAINTS TABLE (คำร้องเรียน - ANONYMOUS)
-- ===================================
CREATE TABLE IF NOT EXISTS complaints (
    complaint_id INT PRIMARY KEY AUTO_INCREMENT,
    
    -- ✅ ANONYMIZATION: Hashed employee ID
    -- Officer/Admin cannot know who filed the complaint
    complainer_id_hash VARCHAR(255) NOT NULL COMMENT 'SHA256 hash of employee_id',
    
    -- ✅ COMPLAINT DATA
    category_id INT NOT NULL,
    subject VARCHAR(300) NOT NULL,
    description LONGTEXT NOT NULL,
    
    -- ✅ STATUS WORKFLOW
    status ENUM('New', 'In Progress', 'Under Review', 'Resolved', 'Closed', 'Dismissed') DEFAULT 'New',
    
    -- ✅ HANDLER INFORMATION
    assigned_to_officer_id VARCHAR(8) DEFAULT NULL,
    assigned_date TIMESTAMP NULL DEFAULT NULL,
    
    -- ✅ RESPONSE & REMARKS
    officer_response LONGTEXT,
    officer_remarks TEXT,
    response_date TIMESTAMP NULL DEFAULT NULL,
    
    -- ✅ ATTACHMENTS
    attachment_path VARCHAR(500),
    
    -- ✅ RATING (Optional - when resolved)
    rating INT DEFAULT NULL CHECK (rating BETWEEN 1 AND 5),
    rating_comment TEXT,
    rated_at TIMESTAMP NULL DEFAULT NULL,
    
    -- ✅ TIMESTAMPS
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL DEFAULT NULL,
    
    -- Foreign Keys
    FOREIGN KEY (category_id) REFERENCES complaint_category_master(category_id),
    FOREIGN KEY (assigned_to_officer_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    
    -- INDICES
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_category (category_id),
    INDEX idx_assigned (assigned_to_officer_id),
    INDEX idx_hash (complainer_id_hash)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- 3. COMPLAINT COMPLAINER AUDIT (เก็บข้อมูลจริง - ADMIN ONLY)
-- ===================================
CREATE TABLE IF NOT EXISTS complaint_complainer_audit (
    audit_id INT PRIMARY KEY AUTO_INCREMENT,
    complaint_id INT NOT NULL,
    complainer_id_plain VARCHAR(8) NOT NULL COMMENT 'Plain employee_id - ADMIN ONLY',
    complainer_id_hash VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    browser_agent TEXT,
    accessed_by_admin_id VARCHAR(8),
    accessed_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (complainer_id_plain) REFERENCES employees(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (accessed_by_admin_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    
    INDEX idx_complaint (complaint_id),
    INDEX idx_accessed_by (accessed_by_admin_id)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- 4. COMPLAINT ACTIVITY LOG (บันทึกกิจกรรม)
-- ===================================
CREATE TABLE IF NOT EXISTS complaint_activity_log (
    log_id INT PRIMARY KEY AUTO_INCREMENT,
    complaint_id INT NOT NULL,
    action VARCHAR(100),
    action_by_officer_id VARCHAR(8),
    old_status VARCHAR(50),
    new_status VARCHAR(50),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (complaint_id) REFERENCES complaints(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (action_by_officer_id) REFERENCES employees(employee_id) ON DELETE SET NULL,
    
    INDEX idx_complaint (complaint_id),
    INDEX idx_officer (action_by_officer_id),
    INDEX idx_created (created_at)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================
-- SEED DATA: Default Complaint Categories
-- ===================================
INSERT INTO complaint_category_master 
    (category_name_th, category_name_en, category_name_my, description_th, description_en, description_my, is_active) 
VALUES
    ('การล่วงละเมิดทางเพศ', 'Sexual Harassment', 'လိင်ပိုင်းဆိုင်ရာ နှောင့်ယှက်ခြင်း', 
     'การกระทำที่ไม่เหมาะสมทางเพศในที่ทำงาน', 'Inappropriate sexual behavior in workplace', 
     'အလုပ်ခွင်တွင် မသင့်လျော်သော လိင်ပိုင်းဆိုင်ရာ အပြုအမူ', 1),
    
    ('การใช้อำนาจในทางที่ผิด', 'Abuse of Power', 'အာဏာ အလွဲသုံးစား', 
     'การใช้ตำแหน่งหน้าที่ในทางที่ไม่เหมาะสม', 'Misuse of authority or position', 
     'ရာထူး သို့မဟုတ် အာဏာကို မသင့်လျော်စွာ အသုံးပြုခြင်း', 1),
    
    ('การเลือกปฏิบัติ', 'Discrimination', 'ခွဲခြားဆက်ဆံမှု', 
     'การปฏิบัติที่ไม่เป็นธรรมต่อบุคคลหรือกลุ่มบุคคล', 'Unfair treatment based on personal characteristics', 
     'ကိုယ်ရေးကိုယ်တာ လက္ခဏာများကို အခြေခံ၍ မတရားသော ဆက်ဆံမှု', 1),
    
    ('สภาพแวดล้อมการทำงาน', 'Work Environment', 'အလုပ်ပတ်ဝန်းကျင်', 
     'ปัญหาเกี่ยวกับความปลอดภัย สุขอนามัย หรือสภาพแวดล้อมในการทำงาน', 
     'Safety, health, or environmental concerns', 
     'ဘေးကင်းရေး၊ ကျန်းမာရေး သို့မဟုတ် ပတ်ဝန်းကျင် စိုးရိမ်ပူပန်မှုများ', 1),
    
    ('ความขัดแย้งระหว่างเพื่อนร่วมงาน', 'Workplace Conflict', 'လုပ်ဖော်ကိုင်ဖက်များကြား ပဋိပက္ခ', 
     'ความขัดแย้งหรือปัญหาระหว่างพนักงานด้วยกัน', 'Conflicts or disputes between employees', 
     'ဝန်ထမ်းများကြား ပဋိပက္ခများ သို့မဟုတ် အငြင်းပွားမှုများ', 1),
    
    ('ค่าตอบแทนและสวัสดิการ', 'Compensation & Benefits', 'လစာနှင့် အကျိုးခံစားခွင့်များ', 
     'ปัญหาเกี่ยวกับเงินเดือน โบนัส หรือสวัสดิการ', 'Issues regarding salary, bonus, or benefits', 
     'လစာ၊ ဆုကြေး သို့မဟုတ် အကျိုးခံစားခွင့်များနှင့် ပတ်သက်သော ပြဿနာများ', 1),
    
    ('การทุจริตและความไม่ซื่อสัตย์', 'Fraud & Dishonesty', 'လိမ်လည်မှုနှင့် ရိုးသားမှုမရှိခြင်း', 
     'การกระทำทุจริต การโกงหรือการให้ข้อมูลเท็จ', 'Fraudulent activities or dishonest behavior', 
     'လိမ်လည်လှည့်ဖြားမှု လုပ်ရပ်များ သို့မဟုတ် ရိုးသားမှုမရှိသော အပြုအမူ', 1),
    
    ('การละเมิดนโยบายบริษัท', 'Policy Violation', 'ကုမ္ပဏီမူဝါဒ ချိုးဖောက်မှု', 
     'การฝ่าฝืนกฎระเบียบหรือนโยบายของบริษัท', 'Violation of company policies or regulations', 
     'ကုမ္ပဏီမူဝါဒများ သို့မဟုတ် စည်းမျဉ်းများကို ချိုးဖောက်ခြင်း', 1),
    
    ('อื่นๆ', 'Other', 'အခြား', 
     'เรื่องร้องเรียนอื่นๆ ที่ไม่อยู่ในหมวดหมู่ข้างต้น', 'Other complaints not listed above', 
     'အထက်ဖော်ပြပါ စာရင်းတွင် မပါဝင်သော အခြားတိုင်ကြားချက်များ', 1);

-- ===================================
-- INDICES FOR PERFORMANCE
-- ===================================
CREATE INDEX idx_complaint_status ON complaints(status);
CREATE INDEX idx_complaint_category ON complaints(category_id);
CREATE INDEX idx_complaint_created ON complaints(created_at);
CREATE INDEX idx_complaint_assigned ON complaints(assigned_to_officer_id);