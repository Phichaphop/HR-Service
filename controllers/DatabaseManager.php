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
        
        $sql = "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        
        if ($conn->query($sql)) {
            $conn->close();
            return ['success' => true, 'message' => '✅ Database created successfully'];
        } else {
            $error = $conn->error;
            $conn->close();
            return ['success' => false, 'message' => '❌ Error creating database: ' . $error];
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
        
        $sql = "DROP DATABASE IF EXISTS `" . DB_NAME . "`";
        
        if ($conn->query($sql)) {
            $conn->close();
            return ['success' => true, 'message' => '✅ Database dropped successfully'];
        } else {
            $error = $conn->error;
            $conn->close();
            return ['success' => false, 'message' => '❌ Error dropping database: ' . $error];
        }
    }
    
    /**
     * Create all tables
     */
    public static function createAllTables() {
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => '❌ Database connection failed'];
        }
        
        try {
            // Fix SQL mode to allow NULL timestamps
            $conn->query("SET sql_mode = ''");
            $conn->query("SET SESSION sql_mode = ''");
            $conn->query("SET FOREIGN_KEY_CHECKS = 0");
            
            // Read SQL schema file
            $sql_file = __DIR__ . '/../db/schema.sql';
            
            if (!file_exists($sql_file)) {
                $conn->close();
                return ['success' => false, 'message' => '❌ Schema file not found at: ' . $sql_file];
            }
            
            $sql = file_get_contents($sql_file);
            
            // Split queries by semicolon but handle multiline properly
            $queries = array_filter(
                array_map('trim', preg_split('/;(?![^\'`]*[\'`])/m', $sql)),
                function($query) {
                    return !empty($query) && strpos($query, '--') !== 0;
                }
            );
            
            $error_queries = [];
            
            // Execute each query individually
            foreach ($queries as $index => $query) {
                $query = trim($query);
                
                // Skip empty queries and comments
                if (empty($query) || substr($query, 0, 2) === '--') {
                    continue;
                }
                
                // Execute query
                if (!$conn->query($query)) {
                    $error_queries[] = [
                        'query_num' => $index,
                        'error' => $conn->error
                    ];
                    // Log but continue (some CREATE IF NOT EXISTS might fail)
                    error_log("SQL Error: " . $conn->error);
                }
            }
            
            // Re-enable foreign key checks
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            
            // Check if critical tables were created
            $critical_tables = ['roles', 'employees', 'localization_master'];
            $tables_ok = true;
            
            foreach ($critical_tables as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                if (!$result || $result->num_rows === 0) {
                    $tables_ok = false;
                    break;
                }
            }
            
            if (!$tables_ok) {
                $conn->close();
                $error_msg = !empty($error_queries) 
                    ? '❌ Error creating tables: ' . $error_queries[0]['error']
                    : '❌ Critical tables not found after creation';
                return ['success' => false, 'message' => $error_msg];
            }
            
            // Seed initial data
            $seed_result = self::seedInitialData($conn);
            
            $conn->close();
            
            return [
                'success' => true,
                'message' => '✅ All tables created successfully and seeded with initial data'
            ];
            
        } catch (Exception $e) {
            $conn->close();
            return ['success' => false, 'message' => '❌ Exception: ' . $e->getMessage()];
        }
    }
    
    /**
     * Drop all tables
     */
    public static function dropAllTables() {
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => '❌ Database connection failed'];
        }
        
        try {
            // Disable foreign key checks
            $conn->query("SET FOREIGN_KEY_CHECKS = 0");
            
            // Get all tables
            $result = $conn->query("SHOW TABLES");
            
            if (!$result) {
                $conn->close();
                return ['success' => false, 'message' => '❌ Error getting table list: ' . $conn->error];
            }
            
            $dropped_count = 0;
            while ($row = $result->fetch_array()) {
                $table = $row[0];
                if ($conn->query("DROP TABLE IF EXISTS `$table`")) {
                    $dropped_count++;
                } else {
                    error_log("Failed to drop table: $table - " . $conn->error);
                }
            }
            
            // Re-enable foreign key checks
            $conn->query("SET FOREIGN_KEY_CHECKS = 1");
            
            $conn->close();
            
            return [
                'success' => true,
                'message' => "✅ $dropped_count tables dropped successfully"
            ];
            
        } catch (Exception $e) {
            $conn->close();
            return ['success' => false, 'message' => '❌ Exception: ' . $e->getMessage()];
        }
    }
    
    /**
     * Seed initial data
     */
    private static function seedInitialData($conn = null) {
        if ($conn === null) {
            $conn = getDbConnection();
            if (!$conn) {
                return ['success' => false, 'message' => 'Database connection failed'];
            }
        }
        
        try {
            $queries = [
                // Insert Roles
                "INSERT IGNORE INTO roles (role_name, role_name_th, role_name_en, role_name_my) VALUES 
                ('admin', 'ผู้ดูแลระบบ', 'Administrator', 'စနစ်စီမံခန့်ခွဲသူ'),
                ('officer', 'เจ้าหน้าที่', 'Officer', 'အရာရှိ'),
                ('employee', 'พนักงาน', 'Employee', 'အလုပ်သမား')",
                
                // Insert Prefix Master
                "INSERT IGNORE INTO prefix_master (prefix_th, prefix_en, prefix_my) VALUES 
                ('นาย', 'Mr.', 'သူ'),
                ('นาง', 'Mrs.', 'သူမ'),
                ('นางสาว', 'Ms.', 'အစ်ကို'),
                ('ดร.', 'Dr.', 'ဒေါက်တာ')",
                
                // Insert Sex Master
                "INSERT IGNORE INTO sex_master (sex_name_th, sex_name_en, sex_name_my) VALUES 
                ('ชาย', 'Male', 'အထီး'),
                ('หญิง', 'Female', 'အမ')",
                
                // Insert Status Master
                "INSERT IGNORE INTO status_master (status_name_th, status_name_en, status_name_my) VALUES 
                ('ทำงาน', 'Active', 'လုပ်ဆောင်နေ'),
                ('ลาออก', 'Resigned', '辞職'),
                ('เกษียณ', 'Retired', 'အငြိမ်း'),
                ('ปลดออก', 'Terminated', 'ပိတ်ဆိုင်း')",
                
                // Insert Education Level Master
                "INSERT IGNORE INTO education_level_master (level_name_th, level_name_en, level_name_my) VALUES 
                ('ประมาณน้อย', 'Below Average', 'အောက်မြှောက်'),
                ('ประมาณปกติ', 'Average', 'ပုံမှန်'),
                ('ประมาณสูง', 'Above Average', 'အထက်မြှောက်'),
                ('สูงมาก', 'Excellent', 'လွန်ကဆန်း')",
                
                // Insert Hiring Type Master
                "INSERT IGNORE INTO hiring_type_master (type_name_th, type_name_en, type_name_my) VALUES 
                ('ประจำ', 'Permanent', 'အစ္စလည်'),
                ('สัญญา', 'Contract', 'စာချုပ်'),
                ('ชั่วคราว', 'Temporary', '한시')",
                
                // Insert Function Master
                "INSERT IGNORE INTO function_master (function_name_th, function_name_en, function_name_my) VALUES 
                ('บริหาร', 'Management', 'စီမံခန့်ခွဲမှု'),
                ('ขายและการตลาด', 'Sales & Marketing', 'ရောင်းချမှုနှင့်စျေးကွက်ဆွေ'),
                ('ผลิตการ', 'Production', 'ထုတ်လုပ်မှု')",
                
                // Insert Nationality Master
                "INSERT IGNORE INTO nationality_master (nationality_th, nationality_en, nationality_my) VALUES 
                ('ไทย', 'Thai', 'ထိုင်းပြည်'),
                ('ลาว', 'Lao', 'လာအို'),
                ('พม่า', 'Myanmar', 'မြန်မာ')",
                
                // Insert Service Category Master
                "INSERT IGNORE INTO service_category_master (category_name_th, category_name_en, category_name_my) VALUES 
                ('ใบลา', 'Leave Certificate', 'ခွင့်ပြုချက်'),
                ('หนังสือรับรอง', 'Certificate', 'လက်မှတ်'),
                ('บัตรพนักงาน', 'ID Card', 'ခွင့်ပြုပत်'),
                ('รถรับส่ง', 'Shuttle Bus', 'ကားစီးခွင့်')",
                
                // Insert Service Type Master
                "INSERT IGNORE INTO service_type_master (type_name_th, type_name_en, type_name_my) VALUES 
                ('ส่วนตัว', 'Individual', 'ပုဂ္ဂလိက'),
                ('กลุ่ม', 'Group', 'အုပ်စုအဖြစ်')",
                
                // Insert Document Type Master
                "INSERT IGNORE INTO doc_type_master (type_name_th, type_name_en, type_name_my) VALUES 
                ('เอกสารบริษัท', 'Company Documents', 'ကုမ္ပဏီစာရွက်စာတမ်း'),
                ('เอกสารการเงิน', 'Financial Documents', 'ငွေစာရွက်စာတမ်း'),
                ('เอกสารพนักงาน', 'Employee Documents', 'အလုပ်သမားစာရွက်စာတမ်း'),
                ('เอกสารปฏิบัติการ', 'Operational Documents', 'လုပ်ဆောင်ချက်စာရွက်စာတမ်း'),
                ('ข้อตกลงและสัญญา', 'Agreements & Contracts', 'သဘောတူညီချက်နှင့်စာချုပ်'),
                ('แบบฟอร์ม', 'Forms', 'ဖွင့်စာ'),
                ('รายงาน', 'Reports', 'အစီရင်ခံစာများ'),
                ('ใบรับรอง', 'Certificates', 'လက်မှတ်များ')",
                
                // Insert Company Info (Sample)
                "INSERT IGNORE INTO company_info (company_name, company_phone, company_fax, company_address, representative_name, company_logo_path) VALUES 
                ('บริษัท ตัวอย่าง จำกัด', '+66-2-xxx-xxxx', '+66-2-xxx-xxxx', 'เซ็นทราล เวิลด์ กรุงเทพฯ', 'นายสมชาย', '/uploads/company/logo.png')",
                
                // Insert Sample Master Data (more complete)
                "INSERT IGNORE INTO division_master (division_name_th, division_name_en, division_name_my) VALUES 
                ('ฝ่ายบริหาร', 'Administration', 'စီမံခန့်ခွဲမှုဌာန'),
                ('ฝ่ายขาย', 'Sales', 'ရောင်းချမှုဌာန'),
                ('ฝ่ายผลิตการ', 'Production', 'ထုတ်လုပ်ခြင်းဌာန')",
                
                "INSERT IGNORE INTO department_master (department_name_th, department_name_en, department_name_my) VALUES 
                ('แผนกบุคคล', 'Human Resources', 'လူရေးဌာန'),
                ('แผนกบัญชี', 'Accounting', 'စာရင်းကိုက်မှုဌာန'),
                ('แผนกวิศวกรรม', 'Engineering', 'အင်ဂျင်နီယာဌာန')",
                
                "INSERT IGNORE INTO section_master (section_name_th, section_name_en, section_name_my) VALUES 
                ('ส่วนเอกสาร', 'Documentation', 'စာရွက်စာတမ်းဌာန'),
                ('ส่วนเงินเดือน', 'Payroll', 'လစာဌာန'),
                ('ส่วนการศึกษา', 'Training', 'လေ့ကျင့်မှုဌာန')",
                
                "INSERT IGNORE INTO operation_master (operation_name_th, operation_name_en, operation_name_my) VALUES 
                ('ปฏิบัติการวันธรรมดา', 'Regular Operations', 'ပုံမှန်လုပ်ဆောင်ချက်'),
                ('ปฏิบัติการพิเศษ', 'Special Operations', 'အထူးလုပ်ဆောင်ချက်')",
                
                "INSERT IGNORE INTO position_master (position_name_th, position_name_en, position_name_my) VALUES 
                ('ผู้จัดการ', 'Manager', 'မန်နေဂျာ'),
                ('พนักงานระดับกลาง', 'Staff', 'कर्मचारी'),
                ('บุคลากรสนับสนุน', 'Support Staff', 'ကူညီမှုकर्मचारी')",
                
                "INSERT IGNORE INTO position_level_master (level_name_th, level_name_en, level_name_my) VALUES 
                ('อาวุโส', 'Senior', 'အကြီးတန်း'),
                ('ปกติ', 'Regular', 'ပုံမှန်'),
                ('จูเนียร์', 'Junior', 'ငယ်တန်း')",
                
                "INSERT IGNORE INTO labour_cost_master (cost_name_th, cost_name_en, cost_name_my) VALUES 
                ('ค่าแรง', 'Labor Cost', 'အလုပ်賃金'),
                ('เบี้ยประกันสังคม', 'Social Insurance', 'လူမှုကဆိုင်ရာ保険')",
                
                "INSERT IGNORE INTO customer_zone_master (zone_name_th, zone_name_en, zone_name_my) VALUES 
                ('เขตกรุงเทพ', 'Bangkok Zone', 'ရန်ကုန်လုံးခြင်း'),
                ('เขตจังหวัร', 'Provincial Zone', 'အခြားမြို့')",
                
                "INSERT IGNORE INTO contribution_level_master (level_name_th, level_name_en, level_name_my) VALUES 
                ('ระดับ 1', 'Level 1', 'အဆင့် 1'),
                ('ระดับ 2', 'Level 2', 'အဆင့် 2'),
                ('ระดับ 3', 'Level 3', 'အဆင့် 3')",
                
                "INSERT IGNORE INTO termination_reason_master (reason_th, reason_en, reason_my) VALUES 
                ('ลาออกเพื่อเหตุผลส่วนตัว', 'Personal Reason', 'ပုဂ္ဂលိကအကြောင်းအရာ'),
                ('ลาออกเพื่อการศึกษา', 'Further Education', 'ပညာသင်ခန်းစာများ'),
                ('ปลดออกเนื่องจากระเบียบวินัย', 'Disciplinary Termination', 'စည်းကမ်းချိုးဖောက်မှု')"
            ];
            
            // Execute seed queries
            foreach ($queries as $index => $query) {
                if (!$conn->query($query)) {
                    error_log("Seed Error at query $index: " . $conn->error);
                    // Continue even if seed fails
                }
            }
            
            return ['success' => true, 'message' => 'Initial data seeded'];
            
        } catch (Exception $e) {
            error_log("Seed Exception: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error seeding data: ' . $e->getMessage()];
        }
    }
}
?>