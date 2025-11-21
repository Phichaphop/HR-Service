<?php

/**
 * Database Manager Controller
 * Handles database and table creation/deletion
 * CRITICAL: Super Admin Access Only
 */

require_once __DIR__ . '/../config/db_config.php';

class DatabaseManager
{

    /**
     * Verify super admin code
     */
    public static function verifySuperAdminCode($code)
    {
        return $code === SUPER_ADMIN_CODE;
    }

    /**
     * Create database
     */
    public static function createDatabase()
    {
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
    public static function dropDatabase()
    {
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
    public static function createAllTables()
    {
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
    private static function createTriggers($conn)
    {
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
    public static function dropAllTables()
    {
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
     * UPDATED: Now also calls DatabaseSeeder for additional data
     */
    private static function seedInitialData()
    {
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
                ('อื่นๆ', 'Other', 'အခြား')");

            // Seed sex_master
            $conn->query("INSERT INTO sex_master (sex_name_th, sex_name_en, sex_name_my) VALUES 
                ('ชาย', 'Male', 'ကျား'),
                ('หญิง', 'Female', 'မ'),
                ('ไม่ระบุ', 'Not Specified', 'သတ်မှတ်မထား')");

            // Seed nationality_master
            $conn->query("INSERT INTO nationality_master (nationality_th, nationality_en, nationality_my) VALUES 
                ('ไทย', 'Thai', 'ထိုင်း'),
                ('พม่า', 'Myanmar', 'မြန်မာ'),
                ('อื่นๆ', 'Other', 'အခြား')");

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
                ('Human Resource', 'Human Resource', 'Human Resource'),
                ('Financial', 'Financial', 'Financial'),
                ('Marketing', 'Marketing', 'Marketing'),
                ('Operation', 'Operation', 'Operation')");

            // Seed division_master
            $conn->query("INSERT INTO division_master (division_name_th, division_name_en, division_name_my) VALUES 
                ('Human Resource', 'Human Resource', 'Human Resource'),
                ('Finance & Accounting', 'Finance & Accounting', 'Finance & Accounting'),
                ('Marketing', 'Marketing', 'Marketing'),
                ('Merchandising', 'Merchandising', 'Merchandising'),
                ('Business Process Improvement', 'Business Process Improvement', 'Business Process Improvement'),
                ('Information Technology', 'Information Technology', 'Information Technology'),
                ('Production', 'Production', 'Production'),
                ('Quality Assurance', 'Quality Assurance', 'Quality Assurance')");

            // Seed department_master
            $conn->query("INSERT INTO department_master (department_name_th, department_name_en, department_name_my) VALUES 
                ('Human Resource', 'Human Resource', 'Human Resource'),
                ('HR Business Partner', 'HR Business Partner', 'HR Business Partner'),
                ('HR People Development & Employee Engagement', 'HR People Development & Employee Engagement', 'HR People Development & Employee Engagement'),
                ('HR Shared Services', 'HR Shared Services', 'HR Shared Services'),
                ('HR Talent Acquisition', 'HR Talent Acquisition', 'HR Talent Acquisition'),
                ('Accounting', 'Accounting', 'Accounting'),
                ('Merchandising (NB)', 'Merchandising (NB)', 'Merchandising (NB)'),
                ('Merchandising (Puma)', 'Merchandising (Puma)', 'Merchandising (Puma)'),
                ('Procurement', 'Procurement', 'Procurement'),
                ('Merchandising (Adidas)', 'Merchandising (Adidas)', 'Merchandising (Adidas)'),
                ('Business Process Improvement', 'Business Process Improvement', 'Business Process Improvement'),
                ('Infrastructure', 'Infrastructure', 'Infrastructure'),
                ('CMD', 'CMD', 'CMD'),
                ('Cutting', 'Cutting', 'Cutting'),
                ('Factory Management', 'Factory Management', 'Factory Management'),
                ('Industrial Engineer', 'Industrial Engineer', 'Industrial Engineer'),
                ('Planning', 'Planning', 'Planning'),
                ('Printing', 'Printing', 'Printing'),
                ('Production', 'Production', 'Production'),
                ('Production Sewing', 'Production Sewing', 'Production Sewing'),
                ('Warehouse', 'Warehouse', 'Warehouse'),
                ('QA/QC', 'QA/QC', 'QA/QC'),
                ('QA/QC (Adidas)', 'QA/QC (Adidas)', 'QA/QC (Adidas)'),
                ('QA/QC (Non-Adidas)', 'QA/QC (Non-Adidas)', 'QA/QC (Non-Adidas)')");

            // Seed section_master
            $conn->query("INSERT INTO section_master (section_name_th, section_name_en, section_name_my) VALUES 
                ('Human Resource', 'Human Resource', 'Human Resource'),
                ('HR Business Partner', 'HR Business Partner', 'HR Business Partner'),
                ('People Development', 'People Development', 'People Development'),
                ('People Development & Employee Engagement', 'People Development & Employee Engagement', 'People Development & Employee Engagement'),
                ('Compliance', 'Compliance', 'Compliance'),
                ('HR Shared Service', 'HR Shared Service', 'HR Shared Service'),
                ('Total Reward', 'Total Reward', 'Total Reward'),
                ('Talent Acquisition', 'Talent Acquisition', 'Talent Acquisition'),
                ('Human Resource', 'Human Resource', 'Human Resource'),
                ('Merchandising (NB)', 'Merchandising (NB)', 'Merchandising (NB)'),
                ('Merchandising Development (NB)', 'Merchandising Development (NB)', 'Merchandising Development (NB)'),
                ('Merchandising (Puma)', 'Merchandising (Puma)', 'Merchandising (Puma)'),
                ('Merchandising Production (Puma)', 'Merchandising Production (Puma)', 'Merchandising Production (Puma)'),
                ('Procurement', 'Procurement', 'Procurement'),
                ('GTS Merchandising (Adidas)', 'GTS Merchandising (Adidas)', 'GTS Merchandising (Adidas)'),
                ('Merchandising (Adidas)', 'Merchandising (Adidas)', 'Merchandising (Adidas)'),
                ('Merchandising ILA Labeling (Adidas)', 'Merchandising ILA Labeling (Adidas)', 'Merchandising ILA Labeling (Adidas)'),
                ('Merchandising Production (Adidas)', 'Merchandising Production (Adidas)', 'Merchandising Production (Adidas)'),
                ('Automation & Innovation', 'Automation & Innovation', 'Automation & Innovation'),
                ('Business Process Improvement', 'Business Process Improvement', 'Business Process Improvement'),
                ('Upstream', 'Upstream', 'Upstream'),
                ('Infrastructure', 'Infrastructure', 'Infrastructure'),
                ('Sewing Mechanic (Adidas)', 'Sewing Mechanic (Adidas)', 'Sewing Mechanic (Adidas)'),
                ('Sewing Mechanic (Non-Adidas)', 'Sewing Mechanic (Non-Adidas)', 'Sewing Mechanic (Non-Adidas)'),
                ('CMD', 'CMD', 'CMD'),
                ('CMD Stock Mechanic', 'CMD Stock Mechanic', 'CMD Stock Mechanic'),
                ('Maintenance', 'Maintenance', 'Maintenance'),
                ('Maintenance Engineer Specialist / TPM', 'Maintenance Engineer Specialist / TPM', 'Maintenance Engineer Specialist / TPM'),
                ('Upstream Mechanic', 'Upstream Mechanic', 'Upstream Mechanic'),
                ('Cutting', 'Cutting', 'Cutting'),
                ('Planning', 'Planning', 'Planning'),
                ('Factory Management', 'Factory Management', 'Factory Management'),
                ('IE Sewing Adidas', 'IE Sewing Adidas', 'IE Sewing Adidas'),
                ('IE Sewing Non-Adidas', 'IE Sewing Non-Adidas', 'IE Sewing Non-Adidas'),
                ('Industrial Engineer', 'Industrial Engineer', 'Industrial Engineer'),
                ('Industrial Engineer Adidas', 'Industrial Engineer Adidas', 'Industrial Engineer Adidas'),
                ('Industrial Engineer Non-Adidas', 'Industrial Engineer Non-Adidas', 'Industrial Engineer Non-Adidas'),
                ('Planning', 'Planning', 'Planning'),
                ('Planning (Adidas)', 'Planning (Adidas)', 'Planning (Adidas)'),
                ('Planning (Non-Adidas)', 'Planning (Non-Adidas)', 'Planning (Non-Adidas)'),
                ('Auto Printing', 'Auto Printing', 'Auto Printing'),
                ('Digital Printing', 'Digital Printing', 'Digital Printing'),
                ('Printing', 'Printing', 'Printing'),
                ('Printing Development', 'Printing Development', 'Printing Development'),
                ('Bonding', 'Bonding', 'Bonding'),
                ('Embroidery', 'Embroidery', 'Embroidery'),
                ('Embroidery (Adidas)', 'Embroidery (Adidas)', 'Embroidery (Adidas)'),
                ('Embroidery (Non-Adidas)', 'Embroidery (Non-Adidas)', 'Embroidery (Non-Adidas)'),
                ('Embroidery Develop', 'Embroidery Develop', 'Embroidery Develop'),
                ('Finish Goods', 'Finish Goods', 'Finish Goods'),
                ('Finish Goods (Adidas)', 'Finish Goods (Adidas)', 'Finish Goods (Adidas)'),
                ('Finish Goods (Non-Adidas)', 'Finish Goods (Non-Adidas)', 'Finish Goods (Non-Adidas)'),
                ('Heat Transfer', 'Heat Transfer', 'Heat Transfer'),
                ('Heat Transfer & Bonding', 'Heat Transfer & Bonding', 'Heat Transfer & Bonding'),
                ('Marker', 'Marker', 'Marker'),
                ('Pattern', 'Pattern', 'Pattern'),
                ('PPA', 'PPA', 'PPA'),
                ('Sample Room', 'Sample Room', 'Sample Room'),
                ('Supermarket (Chaiyaphum) Non-Adidas', 'Supermarket (Chaiyaphum) Non-Adidas', 'Supermarket (Chaiyaphum) Non-Adidas'),
                ('Supermarket (Roi-et) Adidas', 'Supermarket (Roi-et) Adidas', 'Supermarket (Roi-et) Adidas'),
                ('Supermarket (Roi-et) Non-Adidas', 'Supermarket (Roi-et) Non-Adidas', 'Supermarket (Roi-et) Non-Adidas'),
                ('Technical Sewing', 'Technical Sewing', 'Technical Sewing'),
                ('Technical Sewing (Adidas)', 'Technical Sewing (Adidas)', 'Technical Sewing (Adidas)'),
                ('Technical Sewing (Non-Adidas)', 'Technical Sewing (Non-Adidas)', 'Technical Sewing (Non-Adidas)'),
                ('Template', 'Template', 'Template'),
                ('Production Sewing', 'Production Sewing', 'Production Sewing'),
                ('Sewing', 'Sewing', 'Sewing'),
                ('Accessory Store', 'Accessory Store', 'Accessory Store'),
                ('Fabric Store', 'Fabric Store', 'Fabric Store'),
                ('Packing Store', 'Packing Store', 'Packing Store'),
                ('QA Raw Material', 'QA Raw Material', 'QA Raw Material'),
                ('Warehouse', 'Warehouse', 'Warehouse'),
                ('Lab', 'Lab', 'Lab'),
                ('QC Cutting (Non-Adidas)', 'QC Cutting (Non-Adidas)', 'QC Cutting (Non-Adidas)'),
                ('QC Upstream', 'QC Upstream', 'QC Upstream'),
                ('QA Raw Material (GTS)', 'QA Raw Material (GTS)', 'QA Raw Material (GTS)'),
                ('QA/QC (Adidas)', 'QA/QC (Adidas)', 'QA/QC (Adidas)'),
                ('QC Cutting (Adidas)', 'QC Cutting (Adidas)', 'QC Cutting (Adidas)'),
                ('QC Sewing (Adidas)', 'QC Sewing (Adidas)', 'QC Sewing (Adidas)'),
                ('QC Upstream', 'QC Upstream', 'QC Upstream'),
                ('QC Upstream (GTS)', 'QC Upstream (GTS)', 'QC Upstream (GTS)'),
                ('QE (Adidas)', 'QE (Adidas)', 'QE (Adidas)'),
                ('QA/QC (Non-Adidas)', 'QA/QC (Non-Adidas)', 'QA/QC (Non-Adidas)'),
                ('QC Sewing (Non-Adidas)', 'QC Sewing (Non-Adidas)', 'QC Sewing (Non-Adidas)')");

            // Seed operation_master
            $conn->query("INSERT INTO operation_master (operation_name_th, operation_name_en, operation_name_my) VALUES 
                ('Accounting', 'Accounting', 'Accounting'),
                ('Purchasing', 'Purchasing', 'Purchasing'),
                ('Cost Accounting', 'Cost Accounting', 'Cost Accounting'),
                ('General Accounting', 'General Accounting', 'General Accounting'),
                ('HR Business Partner', 'HR Business Partner', 'HR Business Partner'),
                ('People Development', 'People Development', 'People Development'),
                ('People Development & Employee Engagement', 'People Development & Employee Engagement', 'People Development & Employee Engagement'),
                ('Compliance', 'Compliance', 'Compliance'),
                ('Administration', 'Administration', 'Administration'),
                ('Payroll', 'Payroll', 'Payroll'),
                ('Total Reward', 'Total Reward', 'Total Reward'),
                ('Talent Acquisition', 'Talent Acquisition', 'Talent Acquisition'),
                ('Human Resource', 'Human Resource', 'Human Resource'),
                ('Merchandising (NB)', 'Merchandising (NB)', 'Merchandising (NB)'),
                ('Merchandising Development (NB)', 'Merchandising Development (NB)', 'Merchandising Development (NB)'),
                ('Merchandising (Puma)', 'Merchandising (Puma)', 'Merchandising (Puma)'),
                ('Merchandising Production (Puma)', 'Merchandising Production (Puma)', 'Merchandising Production (Puma)'),
                ('Procurement', 'Procurement', 'Procurement'),
                ('GTS Merchandising (Adidas)', 'GTS Merchandising (Adidas)', 'GTS Merchandising (Adidas)'),
                ('Merchandising (Adidas)', 'Merchandising (Adidas)', 'Merchandising (Adidas)'),
                ('Merchandising ILA Labeling (Adidas)', 'Merchandising ILA Labeling (Adidas)', 'Merchandising ILA Labeling (Adidas)'),
                ('Merchandising Production (Adidas)', 'Merchandising Production (Adidas)', 'Merchandising Production (Adidas)'),
                ('Automation & Innovation', 'Automation & Innovation', 'Automation & Innovation'),
                ('Business Process Improvement', 'Business Process Improvement', 'Business Process Improvement'),
                ('Upstream', 'Upstream', 'Upstream'),
                ('Infrastructure', 'Infrastructure', 'Infrastructure'),
                ('Sewing Mechanic (Adidas)', 'Sewing Mechanic (Adidas)', 'Sewing Mechanic (Adidas)'),
                ('Sewing Mechanic (Non-Adidas)', 'Sewing Mechanic (Non-Adidas)', 'Sewing Mechanic (Non-Adidas)'),
                ('CMD', 'CMD', 'CMD'),
                ('CMD Stock Mechanic', 'CMD Stock Mechanic', 'CMD Stock Mechanic'),
                ('Stock Mechanic Sample Room', 'Stock Mechanic Sample Room', 'Stock Mechanic Sample Room'),
                ('Maintenance', 'Maintenance', 'Maintenance'),
                ('Maintenance Engineer Specialist / TPM', 'Maintenance Engineer Specialist / TPM', 'Maintenance Engineer Specialist / TPM'),
                ('Mechanic PPA', 'Mechanic PPA', 'Mechanic PPA'),
                ('Upstream Mechanic', 'Upstream Mechanic', 'Upstream Mechanic'),
                ('Cutting', 'Cutting', 'Cutting'),
                ('Fusing', 'Fusing', 'Fusing'),
                ('Piping', 'Piping', 'Piping'),
                ('Planning', 'Planning', 'Planning'),
                ('Factory Management', 'Factory Management', 'Factory Management'),
                ('IE Sewing Adidas', 'IE Sewing Adidas', 'IE Sewing Adidas'),
                ('IE Sewing Non-Adidas', 'IE Sewing Non-Adidas', 'IE Sewing Non-Adidas'),
                ('Industrial Engineer', 'Industrial Engineer', 'Industrial Engineer'),
                ('Industrial Engineer Adidas', 'Industrial Engineer Adidas', 'Industrial Engineer Adidas'),
                ('Industrial Engineer Non-Adidas', 'Industrial Engineer Non-Adidas', 'Industrial Engineer Non-Adidas'),
                ('Auto Printing', 'Auto Printing', 'Auto Printing'),
                ('Digital Printing', 'Digital Printing', 'Digital Printing'),
                ('Manual Printing', 'Manual Printing', 'Manual Printing'),
                ('Pad Printing', 'Pad Printing', 'Pad Printing'),
                ('Printing', 'Printing', 'Printing'),
                ('Printing Planner', 'Printing Planner', 'Printing Planner'),
                ('Printing Planner (Clerk)', 'Printing Planner (Clerk)', 'Printing Planner (Clerk)'),
                ('Printing Technician', 'Printing Technician', 'Printing Technician'),
                ('Block room', 'Block room', 'Block room'),
                ('Color room', 'Color room', 'Color room'),
                ('Printing Development', 'Printing Development', 'Printing Development'),
                ('Bonding', 'Bonding', 'Bonding'),
                ('Embroidery', 'Embroidery', 'Embroidery'),
                ('Embroidery (Adidas)', 'Embroidery (Adidas)', 'Embroidery (Adidas)'),
                ('Embroidery (Non-Adidas)', 'Embroidery (Non-Adidas)', 'Embroidery (Non-Adidas)'),
                ('Embroidery Develop', 'Embroidery Develop', 'Embroidery Develop'),
                ('Finish Goods', 'Finish Goods', 'Finish Goods'),
                ('Finish Goods (Adidas)', 'Finish Goods (Adidas)', 'Finish Goods (Adidas)'),
                ('Heat Transfer', 'Heat Transfer', 'Heat Transfer'),
                ('Heat Transfer & Bonding', 'Heat Transfer & Bonding', 'Heat Transfer & Bonding'),
                ('Marker', 'Marker', 'Marker'),
                ('Pattern', 'Pattern', 'Pattern'),
                ('Pattern (Adidas)', 'Pattern (Adidas)', 'Pattern (Adidas)'),
                ('Pattern (Non-Adidas)', 'Pattern (Non-Adidas)', 'Pattern (Non-Adidas)'),
                ('PPA', 'PPA', 'PPA'),
                ('Cutting Sample', 'Cutting Sample', 'Cutting Sample'),
                ('Sample Room', 'Sample Room', 'Sample Room'),
                ('Sample Room Technician', 'Sample Room Technician', 'Sample Room Technician'),
                ('Sample Room Technician (Adidas)', 'Sample Room Technician (Adidas)', 'Sample Room Technician (Adidas)'),
                ('Sample Room Technician (Non-Adidas)', 'Sample Room Technician (Non-Adidas)', 'Sample Room Technician (Non-Adidas)'),
                ('Supermarket (Chaiyaphum) Non-Adidas', 'Supermarket (Chaiyaphum) Non-Adidas', 'Supermarket (Chaiyaphum) Non-Adidas'),
                ('Supermarket (Roi-et) Adidas', 'Supermarket (Roi-et) Adidas', 'Supermarket (Roi-et) Adidas'),
                ('Supermarket (Roi-et) Non-Adidas', 'Supermarket (Roi-et) Non-Adidas', 'Supermarket (Roi-et) Non-Adidas'),
                ('Technical Sewing', 'Technical Sewing', 'Technical Sewing'),
                ('Technical Sewing (Adidas)', 'Technical Sewing (Adidas)', 'Technical Sewing (Adidas)'),
                ('Technical Sewing (Non-Adidas)', 'Technical Sewing (Non-Adidas)', 'Technical Sewing (Non-Adidas)'),
                ('Template', 'Template', 'Template'),
                ('Folding', 'Folding', 'Folding'),
                ('Ironing', 'Ironing', 'Ironing'),
                ('Packing', 'Packing', 'Packing'),
                ('Production Sewing', 'Production Sewing', 'Production Sewing'),
                ('Sewing', 'Sewing', 'Sewing'),
                ('Accessory Store', 'Accessory Store', 'Accessory Store'),
                ('Fabric Store', 'Fabric Store', 'Fabric Store'),
                ('Packing Store', 'Packing Store', 'Packing Store'),
                ('QA Raw Material', 'QA Raw Material', 'QA Raw Material'),
                ('Heat Transfer Store', 'Heat Transfer Store', 'Heat Transfer Store'),
                ('Issuing ERP', 'Issuing ERP', 'Issuing ERP'),
                ('Thread Purchasing', 'Thread Purchasing', 'Thread Purchasing'),
                ('Warehouse', 'Warehouse', 'Warehouse'),
                ('Lab', 'Lab', 'Lab'),
                ('QC Cutting (Non-Adidas)', 'QC Cutting (Non-Adidas)', 'QC Cutting (Non-Adidas)'),
                ('QC Heat Transfer & Bonding', 'QC Heat Transfer & Bonding', 'QC Heat Transfer & Bonding'),
                ('QC Printing & Embroidery', 'QC Printing & Embroidery', 'QC Printing & Embroidery'),
                ('QC Upstream', 'QC Upstream', 'QC Upstream'),
                ('QA Raw Material (GTS)', 'QA Raw Material (GTS)', 'QA Raw Material (GTS)'),
                ('QA Certify (Adidas)', 'QA Certify (Adidas)', 'QA Certify (Adidas)'),
                ('QA/QC (Adidas)', 'QA/QC (Adidas)', 'QA/QC (Adidas)'),
                ('QC Cutting (Adidas)', 'QC Cutting (Adidas)', 'QC Cutting (Adidas)'),
                ('QC Sewing (Adidas)', 'QC Sewing (Adidas)', 'QC Sewing (Adidas)'),
                ('QA Certify (GTS)', 'QA Certify (GTS)', 'QA Certify (GTS)'),
                ('QE (Adidas)', 'QE (Adidas)', 'QE (Adidas)'),
                ('QA Certify (Non-Adidas) (NB)', 'QA Certify (Non-Adidas) (NB)', 'QA Certify (Non-Adidas) (NB)'),
                ('QA Certify (Non-Adidas) (Puma)', 'QA Certify (Non-Adidas) (Puma)', 'QA Certify (Non-Adidas) (Puma)'),
                ('QA/QC (Non-Adidas)', 'QA/QC (Non-Adidas)', 'QA/QC (Non-Adidas)'),
                ('QC Sewing (Non-Adidas)', 'QC Sewing (Non-Adidas)', 'QC Sewing (Non-Adidas)')");

            // Seed position_master
            $conn->query("INSERT INTO position_master (position_name_th, position_name_en, position_name_my) VALUES
                ('Accounting', 'Accounting', 'Accounting'),
                ('Purchasing', 'Purchasing', 'Purchasing'),
                ('Cost Accounting', 'Cost Accounting', 'Cost Accounting'),
                ('General Accounting', 'General Accounting', 'General Accounting'),
                ('HR Business Partner', 'HR Business Partner', 'HR Business Partner'),
                ('People Development', 'People Development', 'People Development'),
                ('People Development & Employee Engagement', 'People Development & Employee Engagement', 'People Development & Employee Engagement'),
                ('Compliance', 'Compliance', 'Compliance'),
                ('Administration', 'Administration', 'Administration'),
                ('Payroll', 'Payroll', 'Payroll'),
                ('Total Reward', 'Total Reward', 'Total Reward'),
                ('Talent Acquisition', 'Talent Acquisition', 'Talent Acquisition'),
                ('Human Resource', 'Human Resource', 'Human Resource'),
                ('Merchandising (NB)', 'Merchandising (NB)', 'Merchandising (NB)'),
                ('Merchandising Development (NB)', 'Merchandising Development (NB)', 'Merchandising Development (NB)'),
                ('Merchandising (Puma)', 'Merchandising (Puma)', 'Merchandising (Puma)'),
                ('Merchandising Production (Puma)', 'Merchandising Production (Puma)', 'Merchandising Production (Puma)'),
                ('Procurement', 'Procurement', 'Procurement'),
                ('GTS Merchandising (Adidas)', 'GTS Merchandising (Adidas)', 'GTS Merchandising (Adidas)'),
                ('Merchandising (Adidas)', 'Merchandising (Adidas)', 'Merchandising (Adidas)'),
                ('Merchandising ILA Labeling (Adidas)', 'Merchandising ILA Labeling (Adidas)', 'Merchandising ILA Labeling (Adidas)'),
                ('Merchandising Production (Adidas)', 'Merchandising Production (Adidas)', 'Merchandising Production (Adidas)'),
                ('Automation & Innovation', 'Automation & Innovation', 'Automation & Innovation'),
                ('Business Process Improvement', 'Business Process Improvement', 'Business Process Improvement'),
                ('Upstream', 'Upstream', 'Upstream'),
                ('Infrastructure', 'Infrastructure', 'Infrastructure'),
                ('Sewing Mechanic (Adidas)', 'Sewing Mechanic (Adidas)', 'Sewing Mechanic (Adidas)'),
                ('Sewing Mechanic (Non-Adidas)', 'Sewing Mechanic (Non-Adidas)', 'Sewing Mechanic (Non-Adidas)'),
                ('CMD', 'CMD', 'CMD'),
                ('CMD Stock Mechanic', 'CMD Stock Mechanic', 'CMD Stock Mechanic'),
                ('Stock Mechanic Sample Room', 'Stock Mechanic Sample Room', 'Stock Mechanic Sample Room'),
                ('Maintenance', 'Maintenance', 'Maintenance'),
                ('Maintenance Engineer Specialist / TPM', 'Maintenance Engineer Specialist / TPM', 'Maintenance Engineer Specialist / TPM'),
                ('Mechanic PPA', 'Mechanic PPA', 'Mechanic PPA'),
                ('Upstream Mechanic', 'Upstream Mechanic', 'Upstream Mechanic'),
                ('Cutting', 'Cutting', 'Cutting'),
                ('Fusing', 'Fusing', 'Fusing'),
                ('Piping', 'Piping', 'Piping'),
                ('Planning', 'Planning', 'Planning'),
                ('Factory Management', 'Factory Management', 'Factory Management'),
                ('IE Sewing Adidas', 'IE Sewing Adidas', 'IE Sewing Adidas'),
                ('IE Sewing Non-Adidas', 'IE Sewing Non-Adidas', 'IE Sewing Non-Adidas'),
                ('Industrial Engineer', 'Industrial Engineer', 'Industrial Engineer'),
                ('Industrial Engineer Adidas', 'Industrial Engineer Adidas', 'Industrial Engineer Adidas'),
                ('Industrial Engineer Non-Adidas', 'Industrial Engineer Non-Adidas', 'Industrial Engineer Non-Adidas'),
                ('Auto Printing', 'Auto Printing', 'Auto Printing'),
                ('Digital Printing', 'Digital Printing', 'Digital Printing'),
                ('Manual Printing', 'Manual Printing', 'Manual Printing'),
                ('Pad Printing', 'Pad Printing', 'Pad Printing'),
                ('Printing', 'Printing', 'Printing'),
                ('Printing Planner', 'Printing Planner', 'Printing Planner'),
                ('Printing Planner (Clerk)', 'Printing Planner (Clerk)', 'Printing Planner (Clerk)'),
                ('Printing Technician', 'Printing Technician', 'Printing Technician'),
                ('Block room', 'Block room', 'Block room'),
                ('Color room', 'Color room', 'Color room'),
                ('Printing Development', 'Printing Development', 'Printing Development'),
                ('Bonding', 'Bonding', 'Bonding'),
                ('Embroidery', 'Embroidery', 'Embroidery'),
                ('Embroidery (Adidas)', 'Embroidery (Adidas)', 'Embroidery (Adidas)'),
                ('Embroidery (Non-Adidas)', 'Embroidery (Non-Adidas)', 'Embroidery (Non-Adidas)'),
                ('Embroidery Develop', 'Embroidery Develop', 'Embroidery Develop'),
                ('Finish Goods', 'Finish Goods', 'Finish Goods'),
                ('Finish Goods (Adidas)', 'Finish Goods (Adidas)', 'Finish Goods (Adidas)'),
                ('Heat Transfer', 'Heat Transfer', 'Heat Transfer'),
                ('Heat Transfer & Bonding', 'Heat Transfer & Bonding', 'Heat Transfer & Bonding'),
                ('Marker', 'Marker', 'Marker'),
                ('Pattern', 'Pattern', 'Pattern'),
                ('Pattern (Adidas)', 'Pattern (Adidas)', 'Pattern (Adidas)'),
                ('Pattern (Non-Adidas)', 'Pattern (Non-Adidas)', 'Pattern (Non-Adidas)'),
                ('PPA', 'PPA', 'PPA'),
                ('Cutting Sample', 'Cutting Sample', 'Cutting Sample'),
                ('Sample Room', 'Sample Room', 'Sample Room'),
                ('Sample Room Technician', 'Sample Room Technician', 'Sample Room Technician'),
                ('Sample Room Technician (Adidas)', 'Sample Room Technician (Adidas)', 'Sample Room Technician (Adidas)'),
                ('Sample Room Technician (Non-Adidas)', 'Sample Room Technician (Non-Adidas)', 'Sample Room Technician (Non-Adidas)'),
                ('Supermarket (Chaiyaphum) Non-Adidas', 'Supermarket (Chaiyaphum) Non-Adidas', 'Supermarket (Chaiyaphum) Non-Adidas'),
                ('Supermarket (Roi-et) Adidas', 'Supermarket (Roi-et) Adidas', 'Supermarket (Roi-et) Adidas'),
                ('Supermarket (Roi-et) Non-Adidas', 'Supermarket (Roi-et) Non-Adidas', 'Supermarket (Roi-et) Non-Adidas'),
                ('Technical Sewing', 'Technical Sewing', 'Technical Sewing'),
                ('Technical Sewing (Adidas)', 'Technical Sewing (Adidas)', 'Technical Sewing (Adidas)'),
                ('Technical Sewing (Non-Adidas)', 'Technical Sewing (Non-Adidas)', 'Technical Sewing (Non-Adidas)'),
                ('Template', 'Template', 'Template'),
                ('Folding', 'Folding', 'Folding'),
                ('Ironing', 'Ironing', 'Ironing'),
                ('Packing', 'Packing', 'Packing'),
                ('Production Sewing', 'Production Sewing', 'Production Sewing'),
                ('Sewing', 'Sewing', 'Sewing'),
                ('Accessory Store', 'Accessory Store', 'Accessory Store'),
                ('Fabric Store', 'Fabric Store', 'Fabric Store'),
                ('Packing Store', 'Packing Store', 'Packing Store'),
                ('QA Raw Material', 'QA Raw Material', 'QA Raw Material'),
                ('Heat Transfer Store', 'Heat Transfer Store', 'Heat Transfer Store'),
                ('Issuing ERP', 'Issuing ERP', 'Issuing ERP'),
                ('Thread Purchasing', 'Thread Purchasing', 'Thread Purchasing'),
                ('Warehouse', 'Warehouse', 'Warehouse'),
                ('Lab', 'Lab', 'Lab'),
                ('QC Cutting (Non-Adidas)', 'QC Cutting (Non-Adidas)', 'QC Cutting (Non-Adidas)'),
                ('QC Heat Transfer & Bonding', 'QC Heat Transfer & Bonding', 'QC Heat Transfer & Bonding'),
                ('QC Printing & Embroidery', 'QC Printing & Embroidery', 'QC Printing & Embroidery'),
                ('QC Upstream', 'QC Upstream', 'QC Upstream'),
                ('QA Raw Material (GTS)', 'QA Raw Material (GTS)', 'QA Raw Material (GTS)'),
                ('QA Certify (Adidas)', 'QA Certify (Adidas)', 'QA Certify (Adidas)'),
                ('QA/QC (Adidas)', 'QA/QC (Adidas)', 'QA/QC (Adidas)'),
                ('QC Cutting (Adidas)', 'QC Cutting (Adidas)', 'QC Cutting (Adidas)'),
                ('QC Sewing (Adidas)', 'QC Sewing (Adidas)', 'QC Sewing (Adidas)'),
                ('QA Certify (GTS)', 'QA Certify (GTS)', 'QA Certify (GTS)'),
                ('QE (Adidas)', 'QE (Adidas)', 'QE (Adidas)'),
                ('QA Certify (Non-Adidas) (NB)', 'QA Certify (Non-Adidas) (NB)', 'QA Certify (Non-Adidas) (NB)'),
                ('QA Certify (Non-Adidas) (Puma)', 'QA Certify (Non-Adidas) (Puma)', 'QA Certify (Non-Adidas) (Puma)'),
                ('QA/QC (Non-Adidas)', 'QA/QC (Non-Adidas)', 'QA/QC (Non-Adidas)'),
                ('QC Sewing (Non-Adidas)', 'QC Sewing (Non-Adidas)', 'QC Sewing (Non-Adidas)')");

            // Seed operation_master
            $conn->query("INSERT INTO operation_master (operation_name_th, operation_name_en, operation_name_my) VALUES 
                ('Accounting', 'Accounting', 'Accounting'),
                ('Purchasing', 'Purchasing', 'Purchasing'),
                ('Cost Accounting', 'Cost Accounting', 'Cost Accounting'),
                ('General Accounting', 'General Accounting', 'General Accounting'),
                ('HR Business Partner', 'HR Business Partner', 'HR Business Partner'),
                ('People Development', 'People Development', 'People Development'),
                ('People Development & Employee Engagement', 'People Development & Employee Engagement', 'People Development & Employee Engagement'),
                ('Compliance', 'Compliance', 'Compliance'),
                ('Administration', 'Administration', 'Administration'),
                ('Payroll', 'Payroll', 'Payroll'),
                ('Total Reward', 'Total Reward', 'Total Reward'),
                ('Talent Acquisition', 'Talent Acquisition', 'Talent Acquisition'),
                ('Human Resource', 'Human Resource', 'Human Resource'),
                ('Merchandising (NB)', 'Merchandising (NB)', 'Merchandising (NB)'),
                ('Merchandising Development (NB)', 'Merchandising Development (NB)', 'Merchandising Development (NB)'),
                ('Merchandising (Puma)', 'Merchandising (Puma)', 'Merchandising (Puma)'),
                ('Merchandising Production (Puma)', 'Merchandising Production (Puma)', 'Merchandising Production (Puma)'),
                ('Procurement', 'Procurement', 'Procurement'),
                ('GTS Merchandising (Adidas)', 'GTS Merchandising (Adidas)', 'GTS Merchandising (Adidas)'),
                ('Merchandising (Adidas)', 'Merchandising (Adidas)', 'Merchandising (Adidas)'),
                ('Merchandising ILA Labeling (Adidas)', 'Merchandising ILA Labeling (Adidas)', 'Merchandising ILA Labeling (Adidas)'),
                ('Merchandising Production (Adidas)', 'Merchandising Production (Adidas)', 'Merchandising Production (Adidas)'),
                ('Automation & Innovation', 'Automation & Innovation', 'Automation & Innovation'),
                ('Business Process Improvement', 'Business Process Improvement', 'Business Process Improvement'),
                ('Upstream', 'Upstream', 'Upstream'),
                ('Infrastructure', 'Infrastructure', 'Infrastructure'),
                ('Sewing Mechanic (Adidas)', 'Sewing Mechanic (Adidas)', 'Sewing Mechanic (Adidas)'),
                ('Sewing Mechanic (Non-Adidas)', 'Sewing Mechanic (Non-Adidas)', 'Sewing Mechanic (Non-Adidas)'),
                ('CMD', 'CMD', 'CMD'),
                ('CMD Stock Mechanic', 'CMD Stock Mechanic', 'CMD Stock Mechanic'),
                ('Stock Mechanic Sample Room', 'Stock Mechanic Sample Room', 'Stock Mechanic Sample Room'),
                ('Maintenance', 'Maintenance', 'Maintenance'),
                ('Maintenance Engineer Specialist / TPM', 'Maintenance Engineer Specialist / TPM', 'Maintenance Engineer Specialist / TPM'),
                ('Mechanic PPA', 'Mechanic PPA', 'Mechanic PPA'),
                ('Upstream Mechanic', 'Upstream Mechanic', 'Upstream Mechanic'),
                ('Cutting', 'Cutting', 'Cutting'),
                ('Fusing', 'Fusing', 'Fusing'),
                ('Piping', 'Piping', 'Piping'),
                ('Planning', 'Planning', 'Planning'),
                ('Factory Management', 'Factory Management', 'Factory Management'),
                ('IE Sewing Adidas', 'IE Sewing Adidas', 'IE Sewing Adidas'),
                ('IE Sewing Non-Adidas', 'IE Sewing Non-Adidas', 'IE Sewing Non-Adidas'),
                ('Industrial Engineer', 'Industrial Engineer', 'Industrial Engineer'),
                ('Industrial Engineer Adidas', 'Industrial Engineer Adidas', 'Industrial Engineer Adidas'),
                ('Industrial Engineer Non-Adidas', 'Industrial Engineer Non-Adidas', 'Industrial Engineer Non-Adidas'),
                ('Auto Printing', 'Auto Printing', 'Auto Printing'),
                ('Digital Printing', 'Digital Printing', 'Digital Printing'),
                ('Manual Printing', 'Manual Printing', 'Manual Printing'),
                ('Pad Printing', 'Pad Printing', 'Pad Printing'),
                ('Printing', 'Printing', 'Printing'),
                ('Printing Planner', 'Printing Planner', 'Printing Planner'),
                ('Printing Planner (Clerk)', 'Printing Planner (Clerk)', 'Printing Planner (Clerk)'),
                ('Printing Technician', 'Printing Technician', 'Printing Technician'),
                ('Block room', 'Block room', 'Block room'),
                ('Color room', 'Color room', 'Color room'),
                ('Printing Development', 'Printing Development', 'Printing Development'),
                ('Bonding', 'Bonding', 'Bonding'),
                ('Embroidery', 'Embroidery', 'Embroidery'),
                ('Embroidery (Adidas)', 'Embroidery (Adidas)', 'Embroidery (Adidas)'),
                ('Embroidery (Non-Adidas)', 'Embroidery (Non-Adidas)', 'Embroidery (Non-Adidas)'),
                ('Embroidery Develop', 'Embroidery Develop', 'Embroidery Develop'),
                ('Finish Goods', 'Finish Goods', 'Finish Goods'),
                ('Finish Goods (Adidas)', 'Finish Goods (Adidas)', 'Finish Goods (Adidas)'),
                ('Heat Transfer', 'Heat Transfer', 'Heat Transfer'),
                ('Heat Transfer & Bonding', 'Heat Transfer & Bonding', 'Heat Transfer & Bonding'),
                ('Marker', 'Marker', 'Marker'),
                ('Pattern', 'Pattern', 'Pattern'),
                ('Pattern (Adidas)', 'Pattern (Adidas)', 'Pattern (Adidas)'),
                ('Pattern (Non-Adidas)', 'Pattern (Non-Adidas)', 'Pattern (Non-Adidas)'),
                ('PPA', 'PPA', 'PPA'),
                ('Cutting Sample', 'Cutting Sample', 'Cutting Sample'),
                ('Sample Room', 'Sample Room', 'Sample Room'),
                ('Sample Room Technician', 'Sample Room Technician', 'Sample Room Technician'),
                ('Sample Room Technician (Adidas)', 'Sample Room Technician (Adidas)', 'Sample Room Technician (Adidas)'),
                ('Sample Room Technician (Non-Adidas)', 'Sample Room Technician (Non-Adidas)', 'Sample Room Technician (Non-Adidas)'),
                ('Supermarket (Chaiyaphum) Non-Adidas', 'Supermarket (Chaiyaphum) Non-Adidas', 'Supermarket (Chaiyaphum) Non-Adidas'),
                ('Supermarket (Roi-et) Adidas', 'Supermarket (Roi-et) Adidas', 'Supermarket (Roi-et) Adidas'),
                ('Supermarket (Roi-et) Non-Adidas', 'Supermarket (Roi-et) Non-Adidas', 'Supermarket (Roi-et) Non-Adidas'),
                ('Technical Sewing', 'Technical Sewing', 'Technical Sewing'),
                ('Technical Sewing (Adidas)', 'Technical Sewing (Adidas)', 'Technical Sewing (Adidas)'),
                ('Technical Sewing (Non-Adidas)', 'Technical Sewing (Non-Adidas)', 'Technical Sewing (Non-Adidas)'),
                ('Template', 'Template', 'Template'),
                ('Folding', 'Folding', 'Folding'),
                ('Ironing', 'Ironing', 'Ironing'),
                ('Packing', 'Packing', 'Packing'),
                ('Production Sewing', 'Production Sewing', 'Production Sewing'),
                ('Sewing', 'Sewing', 'Sewing'),
                ('Accessory Store', 'Accessory Store', 'Accessory Store'),
                ('Fabric Store', 'Fabric Store', 'Fabric Store'),
                ('Packing Store', 'Packing Store', 'Packing Store'),
                ('QA Raw Material', 'QA Raw Material', 'QA Raw Material'),
                ('Heat Transfer Store', 'Heat Transfer Store', 'Heat Transfer Store'),
                ('Issuing ERP', 'Issuing ERP', 'Issuing ERP'),
                ('Thread Purchasing', 'Thread Purchasing', 'Thread Purchasing'),
                ('Warehouse', 'Warehouse', 'Warehouse'),
                ('Lab', 'Lab', 'Lab'),
                ('QC Cutting (Non-Adidas)', 'QC Cutting (Non-Adidas)', 'QC Cutting (Non-Adidas)'),
                ('QC Heat Transfer & Bonding', 'QC Heat Transfer & Bonding', 'QC Heat Transfer & Bonding'),
                ('QC Printing & Embroidery', 'QC Printing & Embroidery', 'QC Printing & Embroidery'),
                ('QC Upstream', 'QC Upstream', 'QC Upstream'),
                ('QA Raw Material (GTS)', 'QA Raw Material (GTS)', 'QA Raw Material (GTS)'),
                ('QA Certify (Adidas)', 'QA Certify (Adidas)', 'QA Certify (Adidas)'),
                ('QA/QC (Adidas)', 'QA/QC (Adidas)', 'QA/QC (Adidas)'),
                ('QC Cutting (Adidas)', 'QC Cutting (Adidas)', 'QC Cutting (Adidas)'),
                ('QC Sewing (Adidas)', 'QC Sewing (Adidas)', 'QC Sewing (Adidas)'),
                ('QA Certify (GTS)', 'QA Certify (GTS)', 'QA Certify (GTS)'),
                ('QE (Adidas)', 'QE (Adidas)', 'QE (Adidas)'),
                ('QA Certify (Non-Adidas) (NB)', 'QA Certify (Non-Adidas) (NB)', 'QA Certify (Non-Adidas) (NB)'),
                ('QA Certify (Non-Adidas) (Puma)', 'QA Certify (Non-Adidas) (Puma)', 'QA Certify (Non-Adidas) (Puma)'),
                ('QA/QC (Non-Adidas)', 'QA/QC (Non-Adidas)', 'QA/QC (Non-Adidas)'),
                ('QC Sewing (Non-Adidas)', 'QC Sewing (Non-Adidas)', 'QC Sewing (Non-Adidas)')");

            // Seed position_level_master
            $conn->query("INSERT INTO position_level_master (level_name_th, level_name_en, level_name_my) VALUES 
                ('ระดับ 1', 'Level 1', 'အဆင့် ၁'),
                ('ระดับ 2', 'Level 2', 'အဆင့် ၂'),
                ('ระดับ 3', 'Level 3', 'အဆင့် ၃'),
                ('ระดับ 4', 'Level 4', 'အဆင့် ၄'),
                ('ระดับ 5', 'Level 5', 'အဆင့် ၅')");

            // Seed labour_cost_master
            $conn->query("INSERT INTO labour_cost_master (cost_name_th, cost_name_en, cost_name_my) VALUES 
                ('Direct', 'Direct', 'Direct'),
                ('Indirect', 'Indirect', 'Indirect'),
                ('Decoration', 'Decoration', 'Decoration'),
                ('Support', 'Support', 'Support')");

            // Seed hiring_type_master
            $conn->query("INSERT INTO hiring_type_master (type_name_th, type_name_en, type_name_my) VALUES 
                ('พนักงานประจำ', 'Permanent', 'အမြဲတမ်း'),
                ('พนักงานสัญญาจ้าง', 'Contract', 'စာချုပ်စနစ်'),
                ('พนักงานรายวัน', 'Daily', 'နေ့စား'),
                ('พนักงานชั่วคราว', 'Temporary', 'ယာယီ'),
                ('พนักงานทดลองงาน', 'Probation', 'စမ်းသပ်ကာလ')");

            // Seed customer_zone_master
            $conn->query("INSERT INTO customer_zone_master (zone_name_th, zone_name_en, zone_name_my) VALUES 
                ('โซน 1', 'Zone 1', 'ဇုန် 1'),
                ('โซน 2', 'Zone 2', 'ဇုန် 2'),
                ('โซน 3', 'Zone 3', 'ဇုန် 3'),
                ('โซน 4', 'Zone 4', 'ဇုန် 4')");

            // Seed contribution_level_master
            $conn->query("INSERT INTO contribution_level_master (level_name_th, level_name_en, level_name_my) VALUES 
                ('C1', 'C1', 'C1'),
                ('C2', 'C2', 'C2'),
                ('C4', 'C4', 'C4')");

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

            // Seed sample employees (same as before)
            $password_hash = password_hash('password123', PASSWORD_DEFAULT);
            $employees_data = [
                ['ADM001', 1, 'พิชาภพ บุญฑล', 'Phichaphop Boonthon', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '1985-05-15', 6, '081-234-5678', 'อุดรธานี', '2020-01-01', 1, 'admin', 1],
                ['OFC001', 2, 'สมหญิง รักงาน', 'Somying Rakngaan', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '1990-08-20', 6, '082-345-6789', 'อุดรธานี', '2021-03-15', 1, 'officer', 2],
                ['EMP001', 1, 'สมศักดิ์ ขยัน', 'Somsak Kayan', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '1995-12-10', 4, '083-456-7890', 'อุดรธานี', '2022-06-01', 1, 'emp001', 3],
                ['EMP002', 3, 'สมหมาย ดีงาม', 'Sommai Deengaam', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '1992-03-25', 3, '084-567-8901', 'อุดรธานี', '2022-07-15', 1, 'emp002', 3],
                ['EMP003', 1, 'มานะ ทำงาน', 'Mana Tamngaan', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '1998-11-05', 3, '085-678-9012', 'อุดรธานี', '2023-01-10', 1, 'emp003', 3],
                ['EMP004', 2, 'มาลี สวยงาม', 'Malee Suayngaam', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '1993-07-18', 5, '086-789-0123', 'อุดรธานี', '2022-09-01', 1, 'emp004', 3],
                ['EMP005', 1, 'วิชัย ฉลาด', 'Wichai Chalat', 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, '1988-09-30', 6, '087-890-1234', 'อุดรธานี', '2021-11-20', 1, 'emp005', 3]
            ];

            foreach ($employees_data as $emp) {
                $birthday = new DateTime($emp[17]);
                $now = new DateTime();
                $age = $now->diff($birthday)->y;

                $hire_date = new DateTime($emp[21]);
                $years_service = $now->diff($hire_date)->y;

                $stmt = $conn->prepare("INSERT INTO employees 
                    (employee_id, prefix_id, full_name_th, full_name_en, function_id, division_id, department_id, 
                    section_id, operation_id, position_id, position_level_id, labour_cost_id, hiring_type_id, 
                    customer_zone_id, contribution_level_id, sex_id, nationality_id, birthday, age, education_level_id, 
                    phone_no, address_province, date_of_hire, year_of_service, status_id, username, password, role_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                $stmt->bind_param(
                    "sissiiiiiiiiiiiississssiissi",
                    $emp[0],
                    $emp[1],
                    $emp[2],
                    $emp[3],
                    $emp[4],
                    $emp[5],
                    $emp[6],
                    $emp[7],
                    $emp[8],
                    $emp[9],
                    $emp[10],
                    $emp[11],
                    $emp[12],
                    $emp[13],
                    $emp[14],
                    $emp[15],
                    $emp[16],
                    $emp[17],
                    $age,
                    $emp[18],
                    $emp[19],
                    $emp[20],
                    $emp[21],
                    $years_service,
                    $emp[22],
                    $emp[23],
                    $password_hash,
                    $emp[24]
                );
                $stmt->execute();
                $stmt->close();
            }

            // Seed company info
            $conn->query("INSERT INTO company_info (company_name_th, company_name_en, phone, fax, address, representative_name) VALUES 
                ('บริษัท แทร็กซ์ อินเตอร์เทรด จำกัด', 'Trax intertrade co., ltd.', '043-507-089-92', '043-507-091', '61 หมู่ 5 ถนนร้อยเอ็ด-กาฬสินธุ์ ต.จังหาร อ.จังหาร จ.ร้อยเอ็ด 45000', 'นายธีรภัทร์  เสมแก้ว')");

            // Seed localization
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

            // IMPORTANT: Call DatabaseSeeder to seed additional data (certificate_types and complaint_categories)
            DatabaseSeeder::seedCertificateTypes();
            DatabaseSeeder::seedComplaintCategories();

            return ['success' => true, 'message' => 'All tables created and data seeded successfully'];
        } catch (Exception $e) {
            $conn->rollback();
            $conn->close();
            return ['success' => false, 'message' => 'Error seeding data: ' . $e->getMessage()];
        }
    }
}

/**
 * Database Seeder Class (NEW)
 * Handles seeding of additional master data
 */
class DatabaseSeeder
{

    /**
     * Insert initial certificate types with Thai templates
     * @return array Result with success status and message
     */
    public static function seedCertificateTypes()
    {
        $conn = getDbConnection();
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }

        // Check if data already exists
        $check = $conn->query("SELECT COUNT(*) as count FROM certificate_types");
        $row = $check->fetch_assoc();
        if ($row['count'] > 0) {
            $conn->close();
            return ['success' => false, 'message' => 'Certificate types already exist. Skipping seed.'];
        }

        // Define certificate types with templates
        $certificates = [
            [
                'type_name_th' => 'หนังสือรับรองสถานะพนักงาน',
                'type_name_en' => 'Employee Status Certificate',
                'type_name_my' => 'ဝန်ထမ်းအဆင့်အတန်း လက်မှတ်',
                'template_content' => '<div style="text-align: center; line-height: 1.8;">
    <h2 style="margin-bottom: 0;">หนังสือรับรองสถานะพนักงาน</h2>
    <p style="margin-top: 0;">เลขที่ {certificate_no}</p><br>
    <p style="text-indent: 50px; text-align: justify;">
        หนังสือรับรองฉบับนี้ ขอรับรองว่าบุคคลผู้มีนามข้างล่างนี้ ปัจจุบันเป็นพนักงานของ<br>
        <b>{company_name}</b>
    </p>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ชื่อ-สกุล: {employee_name}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        รหัสพนักงาน: {employee_id}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ตำแหน่ง: {position}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        สังกัด: {division}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ประเภทพนักงาน: {hiring_type}
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ได้เข้าทำงานกับบริษัทฯ ตั้งแต่วันที่ {date_of_hire} จนถึงปัจจุบัน
    </div>
    <br>
    <p style="text-indent: 50px; text-align: justify;">
        ขอรับรองว่า ข้อความข้างต้นเป็นความจริงทุกประการ
    </p>
</div>'
            ],
            [
                'type_name_th' => 'หนังสือรับรองเงินเดือน',
                'type_name_en' => 'Salary Certificate',
                'type_name_my' => 'လစာ လက်မှတ်',
                'template_content' => '<div style="text-align: center; line-height: 1.8;">
    <h2 style="margin-bottom: 0;">หนังสือรับรองเงินเดือน</h2>
    <p style="margin-top: 0;">เลขที่ {certificate_no}</p><br>
    <p style="text-indent: 50px; text-align: justify;">
        หนังสือรับรองฉบับนี้ ขอรับรองว่าบุคคลผู้มีนามข้างล่างนี้ ปัจจุบันเป็นพนักงานของ<br>
        <b>{company_name}</b>
    </p>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ชื่อ-สกุล: {employee_name}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        รหัสพนักงาน: {employee_id}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ตำแหน่ง: {position}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        สังกัด: {division}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ประเภทพนักงาน: {hiring_type}
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ได้เข้าทำงานกับบริษัทฯ ตั้งแต่วันที่ {date_of_hire} จนถึงปัจจุบัน
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        โดยมีฐานเงินเดือน <b>{base_salary}</b>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        รายได้ที่กล่าวมาข้างต้นไม่รวมรายได้อื่นที่พนักงานได้รับต่อเดือน
    </div>
    <br>
    <p style="text-indent: 50px; text-align: justify;">
        ขอรับรองว่า ข้อความข้างต้นเป็นความจริงทุกประการ
    </p>
</div>'
            ],
            [
                'type_name_th' => 'หนังสือรับรองการผ่านงาน',
                'type_name_en' => 'Work Experience Certificate',
                'type_name_my' => 'အလုပ်အတွေ့အကြုံ လက်မှတ်',
                'template_content' => '<div style="text-align: center; line-height: 1.8;">
    <h2 style="margin-bottom: 0;">หนังสือรับรองการผ่านงาน</h2>
    <p style="margin-top: 0;">เลขที่ {certificate_no}</p><br>
    <p style="text-indent: 50px; text-align: justify;">
        หนังสือรับรองฉบับนี้ ขอรับรองว่าบุคคลผู้มีนามข้างล่างนี้ ปัจจุบันเป็นพนักงานของ<br>
        <b>{company_name}</b>
    </p>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ชื่อ-สกุล: {employee_name}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        รหัสพนักงาน: {employee_id}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ตำแหน่ง: {position}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        สังกัด: {division}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ประเภทพนักงาน: {hiring_type}
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ได้เข้าทำงานกับบริษัทฯ ตั้งแต่วันที่ {date_of_hire} จนถึงปัจจุบัน
    </div>
    <br>
    <p style="text-indent: 50px; text-align: justify;">
        ขอรับรองว่า ข้อความข้างต้นเป็นความจริงทุกประการ
    </p>
</div>'
            ],
            [
                'type_name_th' => 'หนังสือแสดงสลิปเงินเดือนย้อนหลัง',
                'type_name_en' => 'Historical Salary Slip',
                'type_name_my' => 'အတိတ်လစာဇယား',
                'template_content' => '<div style="text-align: center; line-height: 1.8;">
    <h2 style="margin-bottom: 0;">หนังสือแสดงสลิปเงินเดือนย้อนหลัง</h2>
    <p style="margin-top: 0;">เลขที่ {certificate_no}</p><br>
    <p style="text-indent: 50px; text-align: justify;">
        หนังสือรับรองฉบับนี้ ขอรับรองว่าบุคคลผู้มีนามข้างล่างนี้ ปัจจุบันเป็นพนักงานของ<br>
        <b>{company_name}</b>
    </p>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ชื่อ-สกุล: {employee_name}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        รหัสพนักงาน: {employee_id}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ตำแหน่ง: {position}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        สังกัด: {division}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ประเภทพนักงาน: {hiring_type}
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ได้เข้าทำงานกับบริษัทฯ ตั้งแต่วันที่ {date_of_hire} จนถึงปัจจุบัน
    </div>
    <br>
    <p style="text-indent: 50px; text-align: justify;">
        ขอรับรองว่า ข้อความข้างต้นเป็นความจริงทุกประการ
    </p>
</div>'
            ],
            [
                'type_name_th' => 'หนังสือรับรองสำหรับสถานทูต',
                'type_name_en' => 'Certificate for Embassy',
                'type_name_my' => 'သံရုံးအတွက် လက်မှတ်',
                'template_content' => '<div style="text-align: center; line-height: 1.8;">
    <h2 style="margin-bottom: 0;">หนังสือรับรองสำหรับสถานทูต</h2>
    <p style="margin-top: 0;">เลขที่ {certificate_no}</p><br>
    <p style="text-indent: 50px; text-align: justify;">
        หนังสือรับรองฉบับนี้ ขอรับรองว่าบุคคลผู้มีนามข้างล่างนี้ ปัจจุบันเป็นพนักงานของ<br>
        <b>{company_name}</b>
    </p>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ชื่อ-สกุล: {employee_name}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        รหัสพนักงาน: {employee_id}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ตำแหน่ง: {position}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        สังกัด: {division}<br>
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ประเภทพนักงาน: {hiring_type}
    </div>
    <div style="text-indent: 80px; text-align: justify; margin-top: 10px;">
        ได้เข้าทำงานกับบริษัทฯ ตั้งแต่วันที่ {date_of_hire} จนถึงปัจจุบัน
    </div>
    <br>
    <p style="text-indent: 50px; text-align: justify;">
        ขอรับรองว่า ข้อความข้างต้นเป็นความจริงทุกประการ
    </p>
</div>'
            ]
        ];

        // Insert certificate types
        $inserted = 0;
        $stmt = $conn->prepare("INSERT INTO certificate_types 
            (type_name_th, type_name_en, type_name_my, template_content, is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, 1, NOW(), NOW())");

        foreach ($certificates as $cert) {
            $stmt->bind_param(
                "ssss",
                $cert['type_name_th'],
                $cert['type_name_en'],
                $cert['type_name_my'],
                $cert['template_content']
            );

            if ($stmt->execute()) {
                $inserted++;
            }
        }

        $stmt->close();
        $conn->close();

        return [
            'success' => true,
            'message' => "Successfully inserted {$inserted} certificate types"
        ];
    }

    /**
     * Insert initial complaint categories (TH/EN/MY)
     * @return array Result with success status and message
     */
    public static function seedComplaintCategories()
    {
        $conn = getDbConnection();
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }

        // Check if data already exists
        $check = $conn->query("SELECT COUNT(*) as count FROM complaint_category_master");
        $row = $check->fetch_assoc();
        if ($row['count'] > 0) {
            $conn->close();
            return ['success' => false, 'message' => 'Complaint categories already exist. Skipping seed.'];
        }

        // Define complaint categories
        $categories = [
            [
                'category_name_th' => 'การล่วงละเมิดทางเพศ',
                'category_name_en' => 'Sexual Harassment',
                'category_name_my' => 'လိင်ပိုင်းဆိုင်ရာ နှောင့်ယှက်ခြင်း',
                'description_th' => 'การกระทำที่ไม่เหมาะสมทางเพศในที่ทำงาน',
                'description_en' => 'Inappropriate sexual behavior in workplace',
                'description_my' => 'အလုပ်ခွင်တွင် မသင့်လျော်သော လိင်ပိုင်းဆိုင်ရာ အပြုအမူ'
            ],
            [
                'category_name_th' => 'การใช้อำนาจในทางที่ผิด',
                'category_name_en' => 'Abuse of Power',
                'category_name_my' => 'အာဏာ အလွဲသုံးစား',
                'description_th' => 'การใช้ตำแหน่งหน้าที่ในทางที่ไม่เหมาะสม',
                'description_en' => 'Misuse of authority or position',
                'description_my' => 'ရာထူး သို့မဟုတ် အာဏာကို မသင့်လျော်စွာ အသုံးပြုခြင်း'
            ],
            [
                'category_name_th' => 'การเลือกปฏิบัติ',
                'category_name_en' => 'Discrimination',
                'category_name_my' => 'ခွဲခြားဆက်ဆံမှု',
                'description_th' => 'การปฏิบัติที่ไม่เป็นธรรมต่อบุคคลหรือกลุ่มบุคคล',
                'description_en' => 'Unfair treatment based on personal characteristics',
                'description_my' => 'ကိုယ်ရေးကိုယ်တာ လက္ခဏာများကို အခြေခံ၍ မတရားသော ဆက်ဆံမှု'
            ],
            [
                'category_name_th' => 'สภาพแวดล้อมการทำงาน',
                'category_name_en' => 'Work Environment',
                'category_name_my' => 'အလုပ်ပတ်ဝန်းကျင်',
                'description_th' => 'ปัญหาเกี่ยวกับความปลอดภัย สุขอนามัย หรือสภาพแวดล้อมในการทำงาน',
                'description_en' => 'Safety, health, or environmental concerns',
                'description_my' => 'ဘေးကင်းရေး၊ ကျန်းမာရေး သို့မဟုတ် ပတ်ဝန်းကျင် စိုးရိမ်ပူပန်မှုများ'
            ],
            [
                'category_name_th' => 'ความขัดแย้งระหว่างเพื่อนร่วมงาน',
                'category_name_en' => 'Workplace Conflict',
                'category_name_my' => 'လုပ်ဖော်ကိုင်ဖက်များကြား ပဋိပက္ခ',
                'description_th' => 'ความขัดแย้งหรือปัญหาระหว่างพนักงานด้วยกัน',
                'description_en' => 'Conflicts or disputes between employees',
                'description_my' => 'ဝန်ထမ်းများကြား ပဋိပက္ခများ သို့မဟုတ် အငြင်းပွားမှုများ'
            ],
            [
                'category_name_th' => 'ค่าตอบแทนและสวัสดิการ',
                'category_name_en' => 'Compensation & Benefits',
                'category_name_my' => 'လစာနှင့် အကျိုးခံစားခွင့်များ',
                'description_th' => 'ปัญหาเกี่ยวกับเงินเดือน โบนัส หรือสวัสดิการ',
                'description_en' => 'Issues regarding salary, bonus, or benefits',
                'description_my' => 'လစာ၊ ဆုကြေး သို့မဟုတ် အကျိုးခံစားခွင့်များနှင့် ပတ်သက်သော ပြဿနာများ'
            ],
            [
                'category_name_th' => 'การทุจริตและความไม่ซื่อสัตย์',
                'category_name_en' => 'Fraud & Dishonesty',
                'category_name_my' => 'လိမ်လည်မှုနှင့် ရိုးသားမှုမရှိခြင်း',
                'description_th' => 'การกระทำทุจริต การโกงหรือการให้ข้อมูลเท็จ',
                'description_en' => 'Fraudulent activities or dishonest behavior',
                'description_my' => 'လိမ်လည်လှည့်ဖြားမှု လုပ်ရပ်များ သို့မဟုတ် ရိုးသားမှုမရှိသော အပြုအမူ'
            ],
            [
                'category_name_th' => 'การละเมิดนโยบายบริษัท',
                'category_name_en' => 'Policy Violation',
                'category_name_my' => 'ကုမ္ပဏီမူဝါဒ ချိုးဖောက်မှု',
                'description_th' => 'การฝ่าฝืนกฎระเบียบหรือนโยบายของบริษัท',
                'description_en' => 'Violation of company policies or regulations',
                'description_my' => 'ကုမ္ပဏီမူဝါဒများ သို့မဟုတ် စည်းမျဉ်းများကို ချိုးဖောက်ခြင်း'
            ],
            [
                'category_name_th' => 'อื่นๆ',
                'category_name_en' => 'Other',
                'category_name_my' => 'အခြား',
                'description_th' => 'เรื่องร้องเรียนอื่นๆ ที่ไม่อยู่ในหมวดหมู่ข้างต้น',
                'description_en' => 'Other complaints not listed above',
                'description_my' => 'အထက်ဖော်ပြပါ စာရင်းတွင် မပါဝင်သော အခြားတိုင်ကြားချက်များ'
            ]
        ];

        // Insert complaint categories
        $inserted = 0;
        $stmt = $conn->prepare("INSERT INTO complaint_category_master 
            (category_name_th, category_name_en, category_name_my, 
             description_th, description_en, description_my, 
             is_active, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())");

        foreach ($categories as $cat) {
            $stmt->bind_param(
                "ssssss",
                $cat['category_name_th'],
                $cat['category_name_en'],
                $cat['category_name_my'],
                $cat['description_th'],
                $cat['description_en'],
                $cat['description_my']
            );

            if ($stmt->execute()) {
                $inserted++;
            }
        }

        $stmt->close();
        $conn->close();

        return [
            'success' => true,
            'message' => "Successfully inserted {$inserted} complaint categories"
        ];
    }

    /**
     * Seed all master data at once
     * @return array Combined results
     */
    public static function seedAllMasterData()
    {
        $results = [];

        // Seed certificate types
        $results['certificate_types'] = self::seedCertificateTypes();

        // Seed complaint categories
        $results['complaint_categories'] = self::seedComplaintCategories();

        return $results;
    }
}
