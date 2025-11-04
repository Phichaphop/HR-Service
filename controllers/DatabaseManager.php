<?php
/**
 * Database Manager Controller
 * Handles database and table creation/deletion
 * CRITICAL: Super Admin Access Only
 */

require_once __DIR__ . '/../config/db_config.php';

class DatabaseManager {
    
    /**
     * Verify super admin code
     */
    public static function verifySuperAdminCode($code) {
        return $code === SUPER_ADMIN_CODE;
    }
    
    /**
     * Create database
     */
    public static function createDatabase() {
        $conn = new mysqli(DB_SERVER, DB_USER, DB_PASS);
        
        if ($conn->connect_error) {
            return ['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error];
        }
        
        $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        
        if ($conn->query($sql)) {
            $conn->close();
            return ['success' => true, 'message' => 'Database created successfully'];
        } else {
            $error = $conn->error;
            $conn->close();
            return ['success' => false, 'message' => 'Error creating database: ' . $error];
        }
    }
    
    /**
     * Drop database
     */
    public static function dropDatabase() {
        $conn = new mysqli(DB_SERVER, DB_USER, DB_PASS);
        
        if ($conn->connect_error) {
            return ['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error];
        }
        
        $sql = "DROP DATABASE IF EXISTS " . DB_NAME;
        
        if ($conn->query($sql)) {
            $conn->close();
            return ['success' => true, 'message' => 'Database dropped successfully'];
        } else {
            $error = $conn->error;
            $conn->close();
            return ['success' => false, 'message' => 'Error dropping database: ' . $error];
        }
    }
    
    /**
     * Create all tables
     */
    public static function createAllTables() {
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        // Set SQL mode and execution time
        @ini_set('max_execution_time', 300);
        @ini_set('memory_limit', '256M');
        $conn->query("SET sql_mode = ''");
        $conn->query("SET SESSION sql_mode = ''");
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Read SQL schema file
        $sql_file = __DIR__ . '/../db/schema.sql';
        
        if (!file_exists($sql_file)) {
            return ['success' => false, 'message' => 'Schema file not found at: ' . $sql_file];
        }
        
        $sql = file_get_contents($sql_file);
        
        // Execute multi-query
        if ($conn->multi_query($sql)) {
            // Clear all results
            do {
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
            
            // Create triggers separately
            self::createTriggers($conn);
            
            // Re-enable foreign keys
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            
            $conn->close();
            
            // Seed data after table creation
            return self::seedInitialData();
        } else {
            $error = $conn->error;
            $conn->close();
            return ['success' => false, 'message' => 'Error creating tables: ' . $error];
        }
    }
    
    /**
     * Create triggers for auto-calculating age and year_of_service
     */
    private static function createTriggers($conn) {
        // Drop existing triggers if they exist
        $conn->query("DROP TRIGGER IF EXISTS calculate_employee_fields_insert");
        $conn->query("DROP TRIGGER IF EXISTS calculate_employee_fields_update");
        
        // Create INSERT trigger
        $trigger_insert = "
        CREATE TRIGGER calculate_employee_fields_insert
        BEFORE INSERT ON employees
        FOR EACH ROW
        BEGIN
            IF NEW.birthday IS NOT NULL THEN
                SET NEW.age = TIMESTAMPDIFF(YEAR, NEW.birthday, CURDATE());
            END IF;
            
            IF NEW.date_of_hire IS NOT NULL THEN
                SET NEW.year_of_service = TIMESTAMPDIFF(YEAR, NEW.date_of_hire, CURDATE());
            END IF;
        END";
        
        $conn->query($trigger_insert);
        
        // Create UPDATE trigger
        $trigger_update = "
        CREATE TRIGGER calculate_employee_fields_update
        BEFORE UPDATE ON employees
        FOR EACH ROW
        BEGIN
            IF NEW.birthday IS NOT NULL THEN
                SET NEW.age = TIMESTAMPDIFF(YEAR, NEW.birthday, CURDATE());
            END IF;
            
            IF NEW.date_of_hire IS NOT NULL THEN
                SET NEW.year_of_service = TIMESTAMPDIFF(YEAR, NEW.date_of_hire, CURDATE());
            END IF;
        END";
        
        $conn->query($trigger_update);
    }
    
    /**
     * Drop all tables
     */
    public static function dropAllTables() {
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        // Drop triggers first
        $conn->query("DROP TRIGGER IF EXISTS calculate_employee_fields_insert");
        $conn->query("DROP TRIGGER IF EXISTS calculate_employee_fields_update");
        
        // Disable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Get all tables
        $result = $conn->query("SHOW TABLES");
        $tables = [];
        
        while ($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        
        // Drop each table
        foreach ($tables as $table) {
            $conn->query("DROP TABLE IF EXISTS `$table`");
        }
        
        // Enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        $conn->close();
        return ['success' => true, 'message' => 'All tables dropped successfully'];
    }
    
    /**
     * Seed initial data - IMPROVED with better error handling
     */
    private static function seedInitialData() {
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Seed in order
            self::seedRoles($conn);
            self::seedPrefixes($conn);
            self::seedSex($conn);
            self::seedNationality($conn);
            self::seedEducationLevel($conn);
            self::seedStatus($conn);
            self::seedFunctions($conn);
            self::seedDivisions($conn);
            self::seedDepartments($conn);
            self::seedSections($conn);
            self::seedOperations($conn);
            self::seedPositions($conn);
            self::seedPositionLevels($conn);
            self::seedLabourCost($conn);
            self::seedHiringTypes($conn);
            self::seedCustomerZones($conn);
            self::seedContributionLevels($conn);
            self::seedTerminationReasons($conn);
            self::seedServiceCategories($conn);
            self::seedServiceTypes($conn);
            self::seedDocTypes($conn);
            self::seedCertificateTypes($conn);
            self::seedEmployees($conn);
            self::seedCompanyInfo($conn);
            self::seedLocalization($conn);
            self::seedLockers($conn);
            
            $conn->commit();
            $conn->close();
            
            return ['success' => true, 'message' => 'All tables created and data seeded successfully'];
            
        } catch (Exception $e) {
            $conn->rollback();
            $conn->close();
            return ['success' => false, 'message' => 'Error seeding data: ' . $e->getMessage()];
        }
    }
    
    // ===== SEED FUNCTIONS =====
    
    private static function seedRoles($conn) {
        $sql = "INSERT INTO roles (role_name, role_name_th, role_name_en, role_name_my) VALUES 
            ('admin', 'ผู้ดูแลระบบ', 'Administrator', 'စီမံခန့်ခွဲသူ'),
            ('officer', 'เจ้าหน้าที่', 'Officer', 'အရာရှိ'),
            ('employee', 'พนักงาน', 'Employee', 'ဝန်ထမ်း')";
        if (!$conn->query($sql)) throw new Exception("Roles: " . $conn->error);
    }
    
    private static function seedPrefixes($conn) {
        $sql = "INSERT INTO prefix_master (prefix_th, prefix_en, prefix_my) VALUES 
            ('นาย', 'Mr.', 'မစ္စတာ'),
            ('นาง', 'Mrs.', 'မစ္စစ်'),
            ('นางสาว', 'Miss', 'မမ')";
        if (!$conn->query($sql)) throw new Exception("Prefixes: " . $conn->error);
    }
    
    private static function seedSex($conn) {
        $sql = "INSERT INTO sex_master (sex_name_th, sex_name_en, sex_name_my) VALUES 
            ('ชาย', 'Male', 'ကျား'),
            ('หญิง', 'Female', 'မ'),
            ('ไม่ระบุ', 'Not Specified', 'သတ်မှတ်မထား')";
        if (!$conn->query($sql)) throw new Exception("Sex: " . $conn->error);
    }
    
    private static function seedNationality($conn) {
        $sql = "INSERT INTO nationality_master (nationality_th, nationality_en, nationality_my) VALUES 
            ('ไทย', 'Thai', 'ထိုင်း'),
            ('พม่า', 'Myanmar', 'မြန်မာ'),
            ('อื่นๆ', 'Others', 'အခြား')";
        if (!$conn->query($sql)) throw new Exception("Nationality: " . $conn->error);
    }
    
    private static function seedEducationLevel($conn) {
        $sql = "INSERT INTO education_level_master (level_name_th, level_name_en, level_name_my) VALUES 
            ('ประถมศึกษา', 'Primary School', 'မူလတန်း'),
            ('มัธยมศึกษาตอนต้น', 'Lower Secondary', 'အလယ်တန်းအောက်'),
            ('มัธยมศึกษาตอนปลาย', 'Upper Secondary', 'အထက်တန်း'),
            ('ปวช.', 'Vocational Certificate', 'အသက်မွေးပညာ'),
            ('ปวส.', 'High Vocational Certificate', 'အဆင့်မြင့်အသက်မွေးပညာ'),
            ('ปริญญาตรี', 'Bachelor Degree', 'ဘွဲ့ရ'),
            ('ปริญญาโท', 'Master Degree', 'မဟာဘွဲ့'),
            ('ปริญญาเอก', 'Doctoral Degree', 'ပါရဂူဘွဲ့')";
        if (!$conn->query($sql)) throw new Exception("Education: " . $conn->error);
    }
    
    private static function seedStatus($conn) {
        $sql = "INSERT INTO status_master (status_name_th, status_name_en, status_name_my) VALUES 
            ('ทำงานอยู่', 'Active', 'အလုပ်လုပ်နေ'),
            ('ลาออก', 'Resigned', 'နုတ်ထွက်'),
            ('เกษียณ', 'Retired', 'အငြိမ်းစား'),
            ('ถูกไล่ออก', 'Terminated', 'ထုတ်ပယ်'),
            ('พักงาน', 'On Leave', 'ခွင့်ယူ')";
        if (!$conn->query($sql)) throw new Exception("Status: " . $conn->error);
    }
    
    private static function seedFunctions($conn) {
        $sql = "INSERT INTO function_master (function_name_th, function_name_en, function_name_my) VALUES 
            ('Financial', 'Financial', 'ငွေကြေးဆိုင်ရာ'),
            ('Human Resource', 'Human Resource', 'လူ့စွမ်းအား'),
            ('Marketing', 'Marketing', 'စျေးကွက်လုပ်ငန်း'),
            ('Operation', 'Operation', 'လုပ်ငန်းဆောင်ရွက်မှု')";
        if (!$conn->query($sql)) throw new Exception("Functions: " . $conn->error);
    }
    
    private static function seedDivisions($conn) {
        $sql = "INSERT INTO division_master (division_name_th, division_name_en, division_name_my) VALUES 
            ('Finance & Accounting', 'Finance & Accounting', 'ငွေကြေးနှင့်စာရင်းကိုင်'),
            ('Human Resource', 'Human Resource', 'လူ့စွမ်းအား'),
            ('Marketing', 'Marketing', 'စျေးကွက်လုပ်ငန်း'),
            ('Merchandising', 'Merchandising', 'ကုန်ပစ္စည်းချွန်းကျင်း'),
            ('Business Process Improvement', 'Business Process Improvement', 'စီးပွားရေးလုပ်ငန်းစဉ်ကိုတိုးတက်စေခြင်း'),
            ('Information Technology', 'Information Technology', 'သတင်းအချက်အလက်နည်းပညာ'),
            ('Production', 'Production', 'ထုတ်လုပ်ရေး'),
            ('Quality Assurance', 'Quality Assurance', 'အရည်အသွေးသုံးသပ်ချက်')";
        if (!$conn->query($sql)) throw new Exception("Divisions: " . $conn->error);
    }
    
    private static function seedDepartments($conn) {
        $departments = [
            'Accounting', 'HR Business Partner', 'HR People Development',
            'HR Shared Service', 'HR Talent Acquisition', 'Human Resource',
            'Merchandising', 'Procurement', 'Business Process Improvement',
            'Infrastructure', 'Production', 'Quality Assurance'
        ];
        
        $values = [];
        foreach ($departments as $dept) {
            $esc = $conn->real_escape_string($dept);
            $values[] = "('$esc', '$esc', '$esc')";
        }
        $sql = "INSERT INTO department_master (department_name_th, department_name_en, department_name_my) VALUES " . implode(',', $values);
        if (!$conn->query($sql)) throw new Exception("Departments: " . $conn->error);
    }
    
    private static function seedSections($conn) {
        $sections = [
            'Accounting', 'Cost Accounting', 'General Accounting',
            'HR Business Partner', 'People Development', 'Compliance',
            'HR Shared Service', 'Total Reward', 'Talent Acquisition',
            'Human Resource', 'Merchandising', 'Procurement',
            'Automation', 'Infrastructure', 'Production', 'Quality Control'
        ];
        
        $values = [];
        foreach ($sections as $sect) {
            $esc = $conn->real_escape_string($sect);
            $values[] = "('$esc', '$esc', '$esc')";
        }
        $sql = "INSERT INTO section_master (section_name_th, section_name_en, section_name_my) VALUES " . implode(',', $values);
        if (!$conn->query($sql)) throw new Exception("Sections: " . $conn->error);
    }
    
    private static function seedOperations($conn) {
        $operations = [
            'Accounting', 'Purchasing', 'HR Business Partner', 'People Development',
            'Administration', 'Payroll', 'Talent Acquisition', 'Merchandising',
            'Procurement', 'Automation', 'Infrastructure', 'Production',
            'Quality Assurance', 'Maintenance', 'Planning'
        ];
        
        $values = [];
        foreach ($operations as $oper) {
            $esc = $conn->real_escape_string($oper);
            $values[] = "('$esc', '$esc', '$esc')";
        }
        $sql = "INSERT INTO operation_master (operation_name_th, operation_name_en, operation_name_my) VALUES " . implode(',', $values);
        if (!$conn->query($sql)) throw new Exception("Operations: " . $conn->error);
    }
    
    private static function seedPositions($conn) {
        // REDUCED positions list for better performance
        $positions = [
            'Accounting Manager', 'HR Manager', 'Officer', 'Supervisor',
            'Leader', 'Worker', 'Technician', 'Engineer',
            'Clerk', 'Driver', 'Coordinator', 'Specialist'
        ];
        
        $values = [];
        foreach ($positions as $pos) {
            $esc = $conn->real_escape_string($pos);
            $values[] = "('$esc', '$esc', '$esc')";
        }
        $sql = "INSERT INTO position_master (position_name_th, position_name_en, position_name_my) VALUES " . implode(',', $values);
        if (!$conn->query($sql)) throw new Exception("Positions: " . $conn->error);
    }
    
    private static function seedPositionLevels($conn) {
        $sql = "INSERT INTO position_level_master (level_name_th, level_name_en, level_name_my) VALUES 
            ('Worker', 'Worker', 'အလုပ်သမား'),
            ('Officer', 'Officer', 'အရာရှိ'),
            ('Leader', 'Leader', 'ခေါ်ဆိုသူ'),
            ('Supervisor', 'Supervisor', 'ကြီးကြပ်သူ'),
            ('Manager', 'Manager', 'မန်နေဂျာ'),
            ('Director', 'Director', 'ညွှန်ကြားရန်အရှင်')";
        if (!$conn->query($sql)) throw new Exception("Position Levels: " . $conn->error);
    }
    
    private static function seedLabourCost($conn) {
        $sql = "INSERT INTO labour_cost_master (cost_name_th, cost_name_en, cost_name_my) VALUES 
            ('Direct', 'Direct', 'တိုက်ရိုက်'),
            ('Indirect', 'Indirect', 'သွယ်ဝိုက်'),
            ('Support', 'Support', 'ကူညီ'),
            ('Decoration', 'Decoration', 'အလှတရ')";
        if (!$conn->query($sql)) throw new Exception("Labour Cost: " . $conn->error);
    }
    
    private static function seedHiringTypes($conn) {
        $sql = "INSERT INTO hiring_type_master (type_name_th, type_name_en, type_name_my) VALUES 
            ('Daily', 'Daily', 'နေ့စား'),
            ('Monthly', 'Monthly', 'လစာ')";
        if (!$conn->query($sql)) throw new Exception("Hiring Types: " . $conn->error);
    }
    
    private static function seedCustomerZones($conn) {
        $sql = "INSERT INTO customer_zone_master (zone_name_th, zone_name_en, zone_name_my) VALUES 
            ('Zone 1', 'Zone 1', 'ဇုန် ၁'),
            ('Zone 2', 'Zone 2', 'ဇုန် ၂'),
            ('Zone 3', 'Zone 3', 'ဇုန် ၃'),
            ('Zone 4', 'Zone 4', 'ဇုန် ၄')";
        if (!$conn->query($sql)) throw new Exception("Customer Zones: " . $conn->error);
    }
    
    private static function seedContributionLevels($conn) {
        $sql = "INSERT INTO contribution_level_master (level_name_th, level_name_en, level_name_my) VALUES 
            ('C1', 'C1', 'C1'),
            ('C2', 'C2', 'C2'),
            ('C3', 'C3', 'C3'),
            ('C4', 'C4', 'C4')";
        if (!$conn->query($sql)) throw new Exception("Contribution Levels: " . $conn->error);
    }
    
    private static function seedTerminationReasons($conn) {
        $sql = "INSERT INTO termination_reason_master (reason_th, reason_en, reason_my) VALUES 
            ('ลาออกเอง', 'Voluntary Resignation', 'ကိုယ်တိုင်နုတ်ထွက်'),
            ('หมดสัญญา', 'Contract Ended', 'စာချုပ်ကုန်ဆုံး'),
            ('ถูกไล่ออก', 'Termination', 'ထုတ်ပယ်'),
            ('เกษียณอายุ', 'Retirement', 'အငြိမ်းစား'),
            ('ย้ายงาน', 'Job Transfer', 'အလုပ်ပြောင်း')";
        if (!$conn->query($sql)) throw new Exception("Termination Reasons: " . $conn->error);
    }
    
    private static function seedServiceCategories($conn) {
        $sql = "INSERT INTO service_category_master (category_name_th, category_name_en, category_name_my) VALUES 
            ('ใบลา', 'Leave Form', 'ခွင့်စာရွက်'),
            ('เอกสารสแกนนิ้ว', 'Finger Scan', 'လက်ဗွေစကင်း'),
            ('เอกสารบัญชีธนาคาร', 'Bank Account', 'ဘဏ်အကောင့်'),
            ('เอกสารบัตรประชาชน', 'ID Card', 'အသိအမှတ်ပြုလက်မှတ်'),
            ('หนังสือรับรอง', 'Certificate', 'အသိအမှတ်ပြုလက်မှတ်')";
        if (!$conn->query($sql)) throw new Exception("Service Categories: " . $conn->error);
    }
    
    private static function seedServiceTypes($conn) {
        $sql = "INSERT INTO service_type_master (type_name_th, type_name_en, type_name_my) VALUES 
            ('รายบุคคล', 'Individual', 'တစ်ဦးချင်း'),
            ('รายกลุ่ม', 'Group', 'အုပ်စု')";
        if (!$conn->query($sql)) throw new Exception("Service Types: " . $conn->error);
    }
    
    private static function seedDocTypes($conn) {
        $sql = "INSERT INTO doc_type_master (type_name_th, type_name_en, type_name_my) VALUES 
            ('คู่มือพนักงาน', 'Employee Handbook', 'ဝန်ထမ်းလမ်းညွှန်'),
            ('ระเบียบบริษัท', 'Company Regulations', 'ကုမ္ပဏီစည်းမျဉ်း'),
            ('แบบฟอร์ม', 'Forms', 'ပုံစံ'),
            ('ประกาศ', 'Announcements', 'ကြေညာချက်'),
            ('เอกสารอื่นๆ', 'Other Documents', 'အခြားစာရွက်များ')";
        if (!$conn->query($sql)) throw new Exception("Doc Types: " . $conn->error);
    }
    
    private static function seedCertificateTypes($conn) {
        $stmt = $conn->prepare("INSERT INTO certificate_types (type_name_th, type_name_en, type_name_my, template_content) VALUES (?, ?, ?, ?)");
        
        $types = [
            [
                'หนังสือรับรองการทำงาน',
                'Employment Certificate',
                'အလုပ်အကိုင်အတည်ပြုလွှာ',
                'Template for Employment Certificate'
            ],
            [
                'หนังสือรับรองเงินเดือน',
                'Salary Certificate',
                'လစာအတည်ပြုလွှာ',
                'Template for Salary Certificate'
            ],
            [
                'หนังสือรับรองการเป็นพนักงาน',
                'Employee Status Certificate',
                'ဝန်ထမ်းအဆင့်အတည်ပြုလွှာ',
                'Template for Employee Status Certificate'
            ]
        ];
        
        foreach ($types as $type) {
            $stmt->bind_param("ssss", $type[0], $type[1], $type[2], $type[3]);
            if (!$stmt->execute()) {
                throw new Exception("Certificate Types: " . $stmt->error);
            }
        }
        $stmt->close();
    }
    
    private static function seedEmployees($conn) {
        $password_hash = password_hash('password123', PASSWORD_DEFAULT);
        
        $employees = [
            ['90681322', 1, 'Admin User', 'Admin User', 2, 2, 6, 9, 12, 2, 5, 1, 2, 1, 2, 1, 1, '1988-03-15', 6, '089-123-4567', 'Bangkok', '2019-01-15', 1, '90681322', 1],
            ['90681323', 2, 'Officer User', 'Officer User', 2, 2, 6, 9, 12, 3, 2, 1, 2, 1, 3, 2, 1, '1992-07-20', 6, '089-234-5678', 'Bangkok', '2020-05-01', 1, '90681323', 2],
            ['90681324', 1, 'Employee User', 'Employee User', 2, 2, 6, 9, 12, 3, 2, 1, 2, 2, 2, 1, 1, '1995-11-10', 6, '089-345-6789', 'Bangkok', '2021-02-01', 1, '90681324', 3]
        ];
        
        $stmt = $conn->prepare("INSERT INTO employees 
            (employee_id, prefix_id, full_name_th, full_name_en, function_id, division_id, department_id, 
            section_id, operation_id, position_id, position_level_id, labour_cost_id, hiring_type_id, 
            customer_zone_id, contribution_level_id, sex_id, nationality_id, birthday, education_level_id, 
            phone_no, address_province, date_of_hire, status_id, username, password, role_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($employees as $emp) {
            $stmt->bind_param("sissiiiiiiiiiiisissiisssi",
                $emp[0], $emp[1], $emp[2], $emp[3], $emp[4], $emp[5], $emp[6], $emp[7], $emp[8], $emp[9],
                $emp[10], $emp[11], $emp[12], $emp[13], $emp[14], $emp[15], $emp[16], $emp[17],
                $emp[18], $emp[19], $emp[20], $emp[21], $emp[22], $emp[23], $password_hash, $emp[24]
            );
            if (!$stmt->execute()) {
                throw new Exception("Employees: " . $stmt->error);
            }
        }
        $stmt->close();
    }
    
    private static function seedCompanyInfo($conn) {
        $sql = "INSERT INTO company_info (company_name_th, company_name_en, phone, fax, address, representative_name) VALUES 
            ('บริษัท แทร็กซ์ อินเตอร์เทรด จำกัด', 'Trax Intertrade Co., Ltd.', '043-507-089-92', '043-507-091', 
            '61 หมู่ 5 ถนนร้อยเอ็ด-กาฬสินธุ์ ต.จังหาร อ.จังหาร จ.ร้อยเอ็ด 45000', 'นายธีรภัทร์ เสมแก้ว')";
        if (!$conn->query($sql)) throw new Exception("Company Info: " . $conn->error);
    }
    
    private static function seedLocalization($conn) {
        $sql = "INSERT INTO localization_master (key_id, th_text, en_text, my_text, category) VALUES 
            ('app_title', 'ระบบบริการทรัพยากรบุคคล', 'HR Service System', 'လူ့စွမ်းအားဝန်ဆောင်မှုစနစ်', 'general'),
            ('login', 'เข้าสู่ระบบ', 'Login', 'ဝင်ရောက်ရန်', 'auth'),
            ('logout', 'ออกจากระบบ', 'Logout', 'ထွက်ရန်', 'auth'),
            ('dashboard', 'หน้าหลัก', 'Dashboard', 'ပင်မစာမျက်နှာ', 'menu'),
            ('employees', 'พนักงาน', 'Employees', 'ဝန်ထမ်းများ', 'menu')";
        if (!$conn->query($sql)) throw new Exception("Localization: " . $conn->error);
    }
    
    private static function seedLockers($conn) {
        $sql = "INSERT INTO locker_master (locker_number, locker_location, status) VALUES 
            ('L001', 'Floor 1 - Zone A', 'Available'),
            ('L002', 'Floor 1 - Zone A', 'Available'),
            ('L003', 'Floor 1 - Zone B', 'Available'),
            ('L004', 'Floor 2 - Zone A', 'Available'),
            ('L005', 'Floor 2 - Zone B', 'Available')";
        if (!$conn->query($sql)) throw new Exception("Lockers: " . $conn->error);
    }
}
?>