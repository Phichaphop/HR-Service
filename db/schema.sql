-- ===================================
-- HR SERVICE DATABASE SCHEMA
-- This file contains all table creation statements
-- NO DELIMITER - NO TRIGGERS (created separately)
-- ===================================

-- Disable foreign key checks for clean installation
SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = '';

-- Drop existing tables if they exist
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
    theme_mode VARCHAR(10) DEFAULT 'light',
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
    current_owner_id VARCHAR(6),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (current_owner_id) REFERENCES employees(employee_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE locker_usage_history (
    history_id INT PRIMARY KEY AUTO_INCREMENT,
    locker_id INT,
    employee_id VARCHAR(6),
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
    upload_by VARCHAR(6),
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
    employee_id VARCHAR(6),
    leave_type VARCHAR(100),
    start_date DATE,
    end_date DATE,
    total_days INT,
    reason TEXT,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(6),
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
    employee_id VARCHAR(6),
    employee_name VARCHAR(200),
    position VARCHAR(200),
    division VARCHAR(200),
    date_of_hire DATE,
    hiring_type VARCHAR(100),
    base_salary DECIMAL(10,2),
    purpose TEXT,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(6),
    handler_remarks TEXT,
    satisfaction_score INT,
    satisfaction_feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (handler_id) REFERENCES employees(employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE id_card_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    employee_id VARCHAR(6),
    reason VARCHAR(200),
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(6),
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
    employee_id VARCHAR(6),
    route VARCHAR(200),
    pickup_location VARCHAR(200),
    start_date DATE,
    reason TEXT,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(6),
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
    employee_id VARCHAR(6),
    reason TEXT,
    assigned_locker_id INT,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(6),
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
    employee_id VARCHAR(6),
    request_type ENUM('Office Supplies', 'Work Equipment', 'Uniform', 'Safety Equipment'),
    items_list TEXT,
    quantity INT,
    reason TEXT,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(6),
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
    employee_id VARCHAR(6),
    skill_name VARCHAR(200),
    test_date DATE,
    reason TEXT,
    status ENUM('New', 'In Progress', 'Complete', 'Cancelled') DEFAULT 'New',
    handler_id VARCHAR(6),
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
    handler_id VARCHAR(6),
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
-- INDEXES FOR PERFORMANCE
-- ===================================

CREATE INDEX idx_employee_username ON employees(username);
CREATE INDEX idx_employee_role ON employees(role_id);
CREATE INDEX idx_employee_status ON employees(status_id);
CREATE INDEX idx_locker_status ON locker_master(status);
CREATE INDEX idx_leave_status ON leave_requests(status);
CREATE INDEX idx_cert_status ON certificate_requests(status);
CREATE INDEX idx_doc_type ON online_documents(doc_type_id);
CREATE INDEX idx_doc_submit_status ON document_submissions(status);

-- ===================================
-- เพิ่มตารางประเภทหนังสือรับรอง
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

-- แก้ไขตาราง certificate_requests เพื่อเพิ่มประเภท
ALTER TABLE certificate_requests 
ADD COLUMN cert_type_id INT AFTER certificate_no,
ADD FOREIGN KEY (cert_type_id) REFERENCES certificate_types(cert_type_id);

-- ข้อมูลตัวอย่างประเภทหนังสือรับรอง
INSERT INTO certificate_types (type_name_th, type_name_en, type_name_my, template_content) VALUES
('หนังสือรับรองการทำงาน', 'Employment Certificate', 'အလုပ်အကိုင်အတည်ပြုလွှာ', 
'ขอรับรองว่า {employee_name} รหัสพนักงาน {employee_id} ดำรงตำแหน่ง {position} สังกัด {division} ได้เข้าทำงานกับบริษัทฯ ตั้งแต่วันที่ {date_of_hire} จนถึงปัจจุบัน โดยมีฐานเงินเดือน {base_salary} บาทต่อเดือน'),

('หนังสือรับรองเงินเดือน', 'Salary Certificate', 'လစာအတည်ပြုလွှာ', 
'ขอรับรองว่า {employee_name} รหัสพนักงาน {employee_id} ปัจจุบันทำงานในตำแหน่ง {position} มีฐานเงินเดือน {base_salary} บาทต่อเดือน'),

('หนังสือรับรองการเป็นพนักงาน', 'Employee Status Certificate', 'ဝန်ထမ်းအဆင့်အတည်ပြုလွှာ',
'ขอรับรองว่า {employee_name} รหัสพนักงาน {employee_id} เป็นพนักงานของบริษัทฯ ในตำแหน่ง {position} สังกัด {division}');

-- สร้างตาราง document_delivery สำหรับระบบลงชื่อส่งเอกสาร
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
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (document_category_id) REFERENCES service_category_master(category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;