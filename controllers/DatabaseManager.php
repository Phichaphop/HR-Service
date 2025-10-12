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
        
        // Fix SQL mode to allow NULL timestamps
        $conn->query("SET sql_mode = ''");
        $conn->query("SET SESSION sql_mode = ''");
        
        // Read SQL schema file
        $sql_file = __DIR__ . '/../db/schema.sql';
        
        if (!file_exists($sql_file)) {
            return ['success' => false, 'message' => 'Schema file not found'];
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
            
            // Create triggers separately (DELIMITER doesn't work in multi_query)
            self::createTriggers($conn);
            
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
     * Seed initial data
     */
    private static function seedInitialData() {
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Seed roles
            $conn->query("INSERT INTO roles (role_name, role_name_th, role_name_en, role_name_my) VALUES 
                ('admin', 'ผู้ดูแลระบบ', 'Administrator', 'စီမံခန့်ခွဲသူ'),
                ('officer', 'เจ้าหน้าที่', 'Officer', 'အရာရှိ'),
                ('employee', 'พนักงาน', 'Employee', 'ဝန်ထမ်း')");
            
            // Seed prefix_master
            $conn->query("INSERT INTO prefix_master (prefix_th, prefix_en, prefix_my) VALUES 
                ('นาย', 'Mr.', 'မစ္စတာ'),
                ('นาง', 'Mrs.', 'မစ္စစ်'),
                ('นางสาว', 'Miss', 'မမ'),
                ('ด.ช.', 'Master', 'မာစတာ'),
                ('ด.ญ.', 'Miss', 'မမ')");
            
            // Seed sex_master
            $conn->query("INSERT INTO sex_master (sex_name_th, sex_name_en, sex_name_my) VALUES 
                ('ชาย', 'Male', 'ကျား'),
                ('หญิง', 'Female', 'မ'),
                ('ไม่ระบุ', 'Not Specified', 'သတ်မှတ်မထား')");
            
            // Seed nationality_master
            $conn->query("INSERT INTO nationality_master (nationality_th, nationality_en, nationality_my) VALUES 
                ('ไทย', 'Thai', 'ထိုင်း'),
                ('พม่า', 'Myanmar', 'မြန်မာ'),
                ('ลาว', 'Laos', 'လာအို'),
                ('กัมพูชา', 'Cambodia', 'ကမ္ဘောဒီးယား'),
                ('เวียดนาม', 'Vietnam', 'ဗီယက်နမ်')");
            
            // Seed education_level_master
            $conn->query("INSERT INTO education_level_master (level_name_th, level_name_en, level_name_my) VALUES 
                ('ประถมศึกษา', 'Primary School', 'မူလတန်း'),
                ('มัธยมศึกษาตอนต้น', 'Lower Secondary', 'အလယ်တန်းအောက်'),
                ('มัธyมศึกษาตอนปลาย', 'Upper Secondary', 'အထက်တန်း'),
                ('ปวช.', 'Vocational Certificate', 'အသက်မွေးပညာ'),
                ('ปวส.', 'High Vocational Certificate', 'အဆင့်မြင့်အသက်မွေးပညာ'),
                ('ปริญญาตรี', 'Bachelor Degree', 'ဘွဲ့ရ'),
                ('ปริญญาโท', 'Master Degree', 'မဟာဘွဲ့'),
                ('ปริญญาเอก', 'Doctoral Degree', 'ပါရဂူဘွဲ့')");
            
            // Seed status_master
            $conn->query("INSERT INTO status_master (status_name_th, status_name_en, status_name_my) VALUES 
                ('ทำงานอยู่', 'Active', 'အလုပ်လုပ်နေ'),
                ('ลาออก', 'Resigned', 'နုတ်ထွက်'),
                ('เกษียณ', 'Retired', 'အငြိမ်းစား'),
                ('ถูกไล่ออก', 'Terminated', 'ထုတ်ပယ်'),
                ('พักงาน', 'On Leave', 'ခွင့်ယူ')");
            
            // Seed function_master
            $conn->query("INSERT INTO function_master (function_name_th, function_name_en, function_name_my) VALUES 
                ('ฝ่ายผลิต', 'Production', 'ထုတ်လုပ်ရေး'),
                ('ฝ่ายคุณภาพ', 'Quality Assurance', 'အရည်အသွေး'),
                ('ฝ่ายบริหาร', 'Administration', 'စီမံခန့်ခွဲရေး'),
                ('ฝ่ายการเงิน', 'Finance', 'ဘဏ္ဍာရေး'),
                ('ฝ่ายทรัพยากรบุคคล', 'Human Resources', 'လူ့စွမ်းအား'),
                ('ฝ่ายจัดซื้อ', 'Purchasing', 'ဝယ်ယူရေး'),
                ('ฝ่ายคลังสินค้า', 'Warehouse', 'သိုလှောင်ရုံ'),
                ('ฝ่ายบำรุงรักษา', 'Maintenance', 'ပြုပြင်ထိန်းသိမ်းရေး')");
            
            // Seed division_master
            $conn->query("INSERT INTO division_master (division_name_th, division_name_en, division_name_my) VALUES 
                ('แผนกตัดเย็บ', 'Sewing Division', 'ချုပ်ရေးဌာန'),
                ('แผนกบรรจุภัณฑ์', 'Packaging Division', 'ထုပ်ပိုးရေးဌာန'),
                ('แผนกควบคุมคุณภาพ', 'QC Division', 'အရည်အသွေးထိန်းချုပ်ရေးဌာန'),
                ('แผนกธุรการ', 'Admin Division', 'စီမံအုပ်ချုပ်ရေးဌာန'),
                ('แผนกบัญชี', 'Accounting Division', 'စာရင်းကိုင်ဌာန')");
            
            // Seed department_master
            $conn->query("INSERT INTO department_master (department_name_th, department_name_en, department_name_my) VALUES 
                ('แผนกจัดส่ง', 'Shipping Department', 'ပို့ဆောင်ရေးဌာန'),
                ('แผนกรับสินค้า', 'Receiving Department', 'လက်ခံရေးဌာန'),
                ('แผนกจัดเก็บ', 'Storage Department', 'သိုလှောင်ရေးဌာန'),
                ('แผนกทรัพยากรบุคคล', 'HR Department', 'လူ့စွမ်းအားဌာန'),
                ('แผนกเงินเดือน', 'Payroll Department', 'လစာဌာန')");
            
            // Seed section_master
            $conn->query("INSERT INTO section_master (section_name_th, section_name_en, section_name_my) VALUES 
                ('แผนกผลิตชิ้นส่วน', 'Parts Production', 'အစိတ်အပိုင်းထုတ်လုပ်ရေး'),
                ('แผนกประกอบ', 'Assembly', 'တပ်ဆင်ရေး'),
                ('แผนกบรรจุหีบห่อ', 'Packing', 'ထုပ်ပိုးရေး'),
                ('แผนกตรวจสอบคุณภาพ', 'Quality Inspection', 'အရည်အသွေးစစ်ဆေးရေး'),
                ('แผนกซ่อมบำรุง', 'Maintenance', 'ပြုပြင်ထိန်းသိမ်းရေး')");
            
            // Seed operation_master
            $conn->query("INSERT INTO operation_master (operation_name_th, operation_name_en, operation_name_my) VALUES 
                ('งานตัด', 'Cutting', 'ဖြတ်တောက်ခြင်း'),
                ('งานเย็บ', 'Sewing', 'ချုပ်ခြင်း'),
                ('งานตรวจสอบ', 'Inspection', 'စစ်ဆေးခြင်း'),
                ('งานบรรจุ', 'Packaging', 'ထုပ်ပိုးခြင်း'),
                ('งานขนส่ง', 'Transportation', 'ပို့ဆောင်ခြင်း')");
            
            // Seed position_master
            $conn->query("INSERT INTO position_master (position_name_th, position_name_en, position_name_my) VALUES 
                ('ผู้จัดการ', 'Manager', 'မန်နေဂျာ'),
                ('หัวหน้างาน', 'Supervisor', 'ကြီးကြပ်ရေးမှူး'),
                ('พนักงานทั่วไป', 'General Worker', 'အလုပ်သမား'),
                ('พนักงานคุณภาพ', 'QC Staff', 'အရည်အသွေးဝန်ထမ်း'),
                ('ช่างเทคนิค', 'Technician', 'နည်းပညာရှင်'),
                ('เจ้าหน้าที่ธุรการ', 'Admin Staff', 'အုပ်ချုပ်ရေးဝန်ထမ်း'),
                ('พนักงานบัญชี', 'Accountant', 'စာရင်းကိုင်'),
                ('พนักงานคลังสินค้า', 'Warehouse Staff', 'သိုလှောင်ရုံဝန်ထမ်း')");
            
            // Seed position_level_master
            $conn->query("INSERT INTO position_level_master (level_name_th, level_name_en, level_name_my) VALUES 
                ('ระดับ 1', 'Level 1', 'အဆင့် ၁'),
                ('ระดับ 2', 'Level 2', 'အဆင့် ၂'),
                ('ระดับ 3', 'Level 3', 'အဆင့် ၃'),
                ('ระดับ 4', 'Level 4', 'အဆင့် ၄'),
                ('ระดับ 5', 'Level 5', 'အဆင့် ၅')");
            
            // Seed labour_cost_master
            $conn->query("INSERT INTO labour_cost_master (cost_name_th, cost_name_en, cost_name_my) VALUES 
                ('ค่าแรงตรง', 'Direct Labor', 'တိုက်ရိုက်အလုပ်သမား'),
                ('ค่าแรงอ้อม', 'Indirect Labor', 'သွယ်ဝိုက်အလုပ်သမား'),
                ('ค่าแรงชั่วคราว', 'Temporary Labor', 'ယာယီအလုပ်သမား')");
            
            // Seed hiring_type_master
            $conn->query("INSERT INTO hiring_type_master (type_name_th, type_name_en, type_name_my) VALUES 
                ('พนักงานประจำ', 'Permanent', 'အမြဲတမ်း'),
                ('พนักงานสัญญาจ้าง', 'Contract', 'စာချုပ်စနစ်'),
                ('พนักงานรายวัน', 'Daily', 'နေ့စား'),
                ('พนักงานชั่วคราว', 'Temporary', 'ယာယီ'),
                ('พนักงานทดลองงาน', 'Probation', 'စမ်းသပ်ကာလ')");
            
            // Seed customer_zone_master
            $conn->query("INSERT INTO customer_zone_master (zone_name_th, zone_name_en, zone_name_my) VALUES 
                ('โซน A', 'Zone A', 'ဇုန် A'),
                ('โซน B', 'Zone B', 'ဇုန် B'),
                ('โซน C', 'Zone C', 'ဇုန် C'),
                ('โซน D', 'Zone D', 'ဇုန် D'),
                ('โซนทั่วไป', 'General Zone', 'ယေဘူယျဇုန်')");
            
            // Seed contribution_level_master
            $conn->query("INSERT INTO contribution_level_master (level_name_th, level_name_en, level_name_my) VALUES 
                ('ระดับสูง', 'High', 'မြင့်'),
                ('ระดับกลาง', 'Medium', 'အလယ်အလတ်'),
                ('ระดับต่ำ', 'Low', 'နိမ့်'),
                ('ยังไม่ประเมิน', 'Not Evaluated', 'မသုံးသပ်ရသေး')");
            
            // Seed termination_reason_master
            $conn->query("INSERT INTO termination_reason_master (reason_th, reason_en, reason_my) VALUES 
                ('ลาออกเอง', 'Voluntary Resignation', 'ကိုယ်တိုင်နုတ်ထွက်'),
                ('หมดสัญญา', 'Contract Ended', 'စာချုပ်ကုန်ဆုံး'),
                ('ถูกไล่ออก', 'Termination', 'ထုတ်ပယ်'),
                ('เกษียณอายุ', 'Retirement', 'အငြိမ်းစား'),
                ('ย้ายงาน', 'Job Transfer', 'အလုပ်ပြောင်း')");
            
            // Seed service_category_master
            $conn->query("INSERT INTO service_category_master (category_name_th, category_name_en, category_name_my) VALUES 
                ('ใบลา', 'Leave Document', 'ခွင့်စာရွက်'),
                ('เอกสารสแกนนิ้วไม่ติด', 'Fingerprint Issue', 'လက်ဗွေပြဿနာ'),
                ('เอกสารบัญชีธนาคาร', 'Bank Document', 'ဘဏ်စာရွက်'),
                ('ใบขออนุญาตออกนอกบริเวรบริษัท', 'Exit Permission', 'ထွက်ခွင့်လျှောက်လွှာ'),
                ('หนังสือรับรอง', 'Certificate', 'အသိအမှတ်ပြုလက်မှတ်'),
                ('เอกสารทั่วไป', 'General Document', 'ယေဘုယျစာရွက်')");
            
            // Seed service_type_master
            $conn->query("INSERT INTO service_type_master (type_name_th, type_name_en, type_name_my) VALUES 
                ('รายบุคคล', 'Individual', 'တစ်ဦးချင်း'),
                ('รายกลุ่ม', 'Group', 'အုပ်စု')");
            
            // Seed doc_type_master
            $conn->query("INSERT INTO doc_type_master (type_name_th, type_name_en, type_name_my) VALUES 
                ('คู่มือพนักงาน', 'Employee Handbook', 'ဝန်ထမ်းလမ်းညွှန်'),
                ('ระเบียบบริษัท', 'Company Regulations', 'ကုမ္ပဏီစည်းမျဉ်း'),
                ('แบบฟอร์ม', 'Forms', 'ပုံစံ'),
                ('ประกาศ', 'Announcements', 'ကြေညာချက်'),
                ('เอกสารอื่นๆ', 'Other Documents', 'အခြားစာရွက်များ')");
            
            // Seed sample employees
            $password_hash = password_hash('password123', PASSWORD_DEFAULT);
            
            // Calculate age and years for seed data
            $employees_data = [
                ['ADM001', 1, 'สมชาย ใจดี', 'Somchai Jaidee', 5, 4, 4, 1, 3, 1, 5, 2, 1, 5, 1, 1, 1, '1985-05-15', 6, '081-234-5678', 'อุดรธานี', '2020-01-01', 1, 'admin', 1],
                ['OFC001', 2, 'สมหญิง รักงาน', 'Somying Rakngaan', 5, 4, 4, 1, 3, 6, 3, 2, 1, 5, 2, 2, 1, '1990-08-20', 6, '082-345-6789', 'อุดรธานี', '2021-03-15', 1, 'officer', 2],
                ['EMP001', 1, 'สมศักดิ์ ขยัน', 'Somsak Kayan', 1, 1, 1, 1, 1, 3, 2, 1, 1, 1, 2, 1, 1, '1995-12-10', 4, '083-456-7890', 'อุดรธานี', '2022-06-01', 1, 'emp001', 3],
                ['EMP002', 3, 'สมหมาย ดีงาม', 'Sommai Deengaam', 1, 1, 1, 2, 2, 3, 2, 1, 1, 1, 2, 2, 1, '1992-03-25', 3, '084-567-8901', 'อุดรธานี', '2022-07-15', 1, 'emp002', 3],
                ['EMP003', 1, 'มานะ ทำงาน', 'Mana Tamngaan', 1, 1, 1, 3, 4, 3, 1, 1, 1, 2, 3, 1, 2, '1998-11-05', 3, '085-678-9012', 'อุดรธานี', '2023-01-10', 1, 'emp003', 3],
                ['EMP004', 2, 'มาลี สวยงาม', 'Malee Suayngaam', 2, 3, 3, 4, 3, 4, 2, 1, 1, 2, 2, 2, 1, '1993-07-18', 5, '086-789-0123', 'อุดรธานี', '2022-09-01', 1, 'emp004', 3],
                ['EMP005', 1, 'วิชัย ฉลาด', 'Wichai Chalat', 8, 5, 5, 5, 3, 5, 3, 2, 1, 3, 1, 1, 1, '1988-09-30', 6, '087-890-1234', 'อุดรธานี', '2021-11-20', 1, 'emp005', 3]
            ];
            
            foreach ($employees_data as $emp) {
                // Calculate age
                $birthday = new DateTime($emp[17]);
                $now = new DateTime();
                $age = $now->diff($birthday)->y;
                
                // Calculate years of service
                $hire_date = new DateTime($emp[21]);
                $years_service = $now->diff($hire_date)->y;
                
                $stmt = $conn->prepare("INSERT INTO employees 
                    (employee_id, prefix_id, full_name_th, full_name_en, function_id, division_id, department_id, 
                    section_id, operation_id, position_id, position_level_id, labour_cost_id, hiring_type_id, 
                    customer_zone_id, contribution_level_id, sex_id, nationality_id, birthday, age, education_level_id, 
                    phone_no, address_province, date_of_hire, year_of_service, status_id, username, password, role_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->bind_param("siisiiiiiiiiiiiississssiissi",
                    $emp[0], $emp[1], $emp[2], $emp[3], $emp[4], $emp[5], $emp[6], $emp[7], $emp[8], $emp[9],
                    $emp[10], $emp[11], $emp[12], $emp[13], $emp[14], $emp[15], $emp[16], $emp[17], $age,
                    $emp[18], $emp[19], $emp[20], $emp[21], $years_service, $emp[22], $emp[23], $password_hash, $emp[24]
                );
                $stmt->execute();
                $stmt->close();
            }
            
            // Seed company info
            $conn->query("INSERT INTO company_info (company_name_th, company_name_en, phone, fax, address, representative_name) VALUES 
                ('บริษัท ตัวอย่าง จำกัด', 'Sample Company Limited', '042-123-456', '042-123-457', '123 ถนนหลัก ตำบลหมากแข้ง อำเภอเมือง จังหวัดอุดรธานี 41000', 'นายสมชาย ใจดี')");
            
            // Seed localization (sample keys)
            $conn->query("INSERT INTO localization_master (key_id, th_text, en_text, my_text, category) VALUES 
                ('app_title', 'ระบบบริการทรัพยากรบุคคล', 'HR Service System', 'လူ့စွမ်းအားဝန်ဆောင်မှုစနစ်', 'general'),
                ('login', 'เข้าสู่ระบบ', 'Login', 'ဝင်ရောက်ရန်', 'auth'),
                ('logout', 'ออกจากระบบ', 'Logout', 'ထွက်ရန်', 'auth'),
                ('username', 'ชื่อผู้ใช้', 'Username', 'အသုံးပြုသူအမည်', 'auth'),
                ('password', 'รหัสผ่าน', 'Password', 'စကားဝှက်', 'auth'),
                ('dashboard', 'หน้าหลัก', 'Dashboard', 'ပင်မစာမျက်နှာ', 'menu'),
                ('employees', 'พนักงาน', 'Employees', 'ဝန်ထမ်းများ', 'menu'),
                ('requests', 'คำขอ', 'Requests', 'တောင်းဆိုချက်များ', 'menu'),
                ('settings', 'ตั้งค่า', 'Settings', 'ဆက်တင်များ', 'menu'),
                ('save', 'บันทึก', 'Save', 'သိမ်းရန်', 'action'),
                ('cancel', 'ยกเลิก', 'Cancel', 'ပယ်ဖျက်ရန်', 'action'),
                ('edit', 'แก้ไข', 'Edit', 'ပြင်ဆင်ရန်', 'action'),
                ('delete', 'ลบ', 'Delete', 'ဖျက်ရန်', 'action'),
                ('submit', 'ส่ง', 'Submit', 'တင်သွင်းရန်', 'action'),
                ('approve', 'อนุมัติ', 'Approve', 'အတည်ပြုရန်', 'action'),
                ('reject', 'ปฏิเสธ', 'Reject', 'ငြင်းပယ်ရန်', 'action')");
            
            // Seed sample lockers
            $conn->query("INSERT INTO locker_master (locker_number, locker_location, status) VALUES 
                ('L001', 'ชั้น 1 - โซน A', 'Available'),
                ('L002', 'ชั้น 1 - โซน A', 'Available'),
                ('L003', 'ชั้น 1 - โซน B', 'Available'),
                ('L004', 'ชั้น 1 - โซน B', 'Available'),
                ('L005', 'ชั้น 2 - โซน A', 'Available'),
                ('L006', 'ชั้น 2 - โซน A', 'Available'),
                ('L007', 'ชั้น 2 - โซน B', 'Available'),
                ('L008', 'ชั้น 2 - โซน B', 'Available'),
                ('L009', 'ชั้น 3 - โซน A', 'Maintenance'),
                ('L010', 'ชั้น 3 - โซน A', 'Available')");
            
            $conn->commit();
            $conn->close();
            
            return ['success' => true, 'message' => 'All tables created and data seeded successfully'];
            
        } catch (Exception $e) {
            $conn->rollback();
            $conn->close();
            return ['success' => false, 'message' => 'Error seeding data: ' . $e->getMessage()];
        }
    }
}
?>