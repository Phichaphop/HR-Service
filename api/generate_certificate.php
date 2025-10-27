<?php
/**
 * API: Generate Salary Certificate PDF
 * File: api/generate_certificate.php
 * 
 * Generate PDF certificate based on request data
 * FIXED: Better role checking with database fallback
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/db_config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    die('Unauthorized access - Please login');
}

// Get user role with database fallback
$user_role = null;

// Try to get from session
if (isset($_SESSION['user_role'])) {
    $user_role = $_SESSION['user_role'];
} elseif (isset($_SESSION['role'])) {
    $user_role = $_SESSION['role'];
}

error_log('Certificate Generation - User ID: ' . $_SESSION['user_id'] . ', Role from session: ' . ($user_role ?? 'NULL'));

// If no role in session, get from database
if (!$user_role) {
    $conn = getDbConnection();
    if ($conn) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT r.role_name FROM employees e 
                               LEFT JOIN roles r ON e.role_id = r.role_id 
                               WHERE e.employee_id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $user_role = $row['role_name'];
                $_SESSION['user_role'] = $user_role; // Save to session
                error_log('Certificate Generation - Role from database: ' . $user_role);
            }
            $stmt->close();
        }
        $conn->close();
    }
}

// Normalize role (lowercase, trim)
$user_role = strtolower(trim($user_role ?? ''));

error_log('Certificate Generation - Final role: ' . $user_role);

// Check authorization - allow admin, officer, and administrator
$allowed_roles = ['admin', 'officer', 'administrator'];

if (!in_array($user_role, $allowed_roles)) {
    error_log('Certificate Generation - Access denied for role: ' . $user_role);
    
    // Show detailed error page for debugging
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Access Denied</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                padding: 50px;
                background: #f5f5f5;
            }
            .error-box {
                background: white;
                border: 2px solid #e74c3c;
                border-radius: 10px;
                padding: 30px;
                max-width: 600px;
                margin: 0 auto;
            }
            h1 { color: #e74c3c; }
            .info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0; }
            .info strong { color: #2c3e50; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <h1>🚫 Access Denied</h1>
            <p>คุณไม่มีสิทธิ์เข้าถึงฟังก์ชันนี้</p>
            <p>You don't have permission to access this function</p>
            
            <div class="info">
                <strong>Debug Information:</strong><br>
                User ID: <?php echo htmlspecialchars($_SESSION['user_id'] ?? 'Not set'); ?><br>
                Current Role: <span style="color: red;"><?php echo htmlspecialchars($user_role ?: 'Not set'); ?></span><br>
                Required Roles: <?php echo implode(', ', $allowed_roles); ?><br>
                Session Keys: <?php echo implode(', ', array_keys($_SESSION)); ?>
            </div>
            
            <div class="info">
                <strong>💡 วิธีแก้ไข (How to fix):</strong><br>
                1. ตรวจสอบว่า role ใน database เป็น "Admin" หรือ "Officer"<br>
                2. ลอง Logout แล้ว Login ใหม่<br>
                3. ติดต่อผู้ดูแลระบบเพื่อตรวจสอบสิทธิ์ของคุณ
            </div>
            
            <button onclick="window.close()" style="padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Close Window
            </button>
        </div>
    </body>
    </html>
    <?php
    exit();
}

try {
    // Get request ID
    $request_id = intval($_GET['request_id'] ?? 0);
    
    if ($request_id <= 0) {
        die('Invalid request ID');
    }
    
    // Get certificate request data
    $conn = getDbConnection();
    if (!$conn) {
        die('Database connection failed');
    }
    
    $sql = "SELECT 
                cr.*,
                e.employee_id,
                e.full_name_th,
                e.full_name_en,
                e.date_of_hire,
                e.base_salary,
                COALESCE(p.position_name_th, '') as position_name_th,
                COALESCE(p.position_name_en, '') as position_name_en,
                COALESCE(d.division_name_th, '') as division_name_th,
                COALESCE(d.division_name_en, '') as division_name_en,
                COALESCE(ht.type_name_th, '') as hiring_type_th,
                COALESCE(ht.type_name_en, '') as hiring_type_en,
                comp.company_name_th,
                comp.company_name_en,
                comp.address_th,
                comp.address_en,
                comp.phone,
                comp.representative_name_th,
                comp.representative_name_en,
                comp.representative_position_th,
                comp.representative_position_en
            FROM certificate_requests cr
            LEFT JOIN employees e ON cr.employee_id = e.employee_id
            LEFT JOIN position_master p ON e.position_id = p.position_id
            LEFT JOIN division_master d ON e.division_id = d.division_id
            LEFT JOIN hiring_type_master ht ON e.hiring_type_id = ht.type_id
            LEFT JOIN company_info comp ON comp.company_id = 1
            WHERE cr.request_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    
    if (!$data) {
        die('Certificate request not found');
    }
    
    $stmt->close();
    
    // Generate certificate number if not exists
    if (empty($data['certificate_no'])) {
        $year = date('Y');
        $month = date('m');
        
        // Get next number
        $count_sql = "SELECT COUNT(*) as count FROM certificate_requests 
                     WHERE YEAR(created_at) = YEAR(CURDATE())";
        $count_result = $conn->query($count_sql);
        $count_row = $count_result->fetch_assoc();
        $next_num = ($count_row['count'] ?? 0) + 1;
        
        $cert_no = sprintf('CERT-%s-%s-%04d', $year, $month, $next_num);
        
        // Update certificate number
        $update_sql = "UPDATE certificate_requests SET certificate_no = ? WHERE request_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $cert_no, $request_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        $data['certificate_no'] = $cert_no;
    }
    
    $conn->close();
    
    // Determine language
    $lang = $_GET['lang'] ?? 'th';
    if (!in_array($lang, ['th', 'en'])) {
        $lang = 'th';
    }
    
    // Generate PDF
    generateCertificatePDF($data, $lang);
    
} catch (Exception $e) {
    error_log('generate_certificate.php error: ' . $e->getMessage());
    die('Error generating certificate: ' . $e->getMessage());
}

/**
 * Generate Certificate PDF
 */
function generateCertificatePDF($data, $lang = 'th') {
    // Use FPDF or TCPDF library
    // For now, we'll use HTML/CSS that can be printed as PDF
    
    $cert_no = htmlspecialchars($data['certificate_no']);
    $employee_id = htmlspecialchars($data['employee_id']);
    $date_issued = date('d/m/Y');
    
    if ($lang === 'th') {
        $company_name = $data['company_name_th'];
        $company_address = $data['address_th'];
        $employee_name = $data['full_name_th'];
        $position = $data['position_name_th'];
        $division = $data['division_name_th'];
        $hiring_type = $data['hiring_type_th'];
        $representative_name = $data['representative_name_th'];
        $representative_position = $data['representative_position_th'];
    } else {
        $company_name = $data['company_name_en'];
        $company_address = $data['address_en'];
        $employee_name = $data['full_name_en'];
        $position = $data['position_name_en'];
        $division = $data['division_name_en'];
        $hiring_type = $data['hiring_type_en'];
        $representative_name = $data['representative_name_en'];
        $representative_position = $data['representative_position_en'];
    }
    
    $salary = number_format($data['base_salary'], 2);
    $date_of_hire = date('d/m/Y', strtotime($data['date_of_hire']));
    $phone = $data['phone'];
    
    // Calculate years of service
    $hire_date = new DateTime($data['date_of_hire']);
    $current_date = new DateTime();
    $years_of_service = $hire_date->diff($current_date)->y;
    
    // Generate HTML
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo $lang; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $lang === 'th' ? 'หนังสือรับรองเงินเดือน' : 'Salary Certificate'; ?></title>
        <style>
            @page {
                size: A4;
                margin: 2cm;
            }
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Sarabun', 'Arial', sans-serif;
                font-size: 14pt;
                line-height: 1.8;
                color: #000;
                background: #fff;
                padding: 40px;
            }
            
            .certificate-container {
                max-width: 800px;
                margin: 0 auto;
                border: 3px double #333;
                padding: 40px;
                position: relative;
            }
            
            .header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 2px solid #333;
                padding-bottom: 20px;
            }
            
            .company-logo {
                width: 100px;
                height: 100px;
                margin: 0 auto 15px;
            }
            
            .company-name {
                font-size: 20pt;
                font-weight: bold;
                margin-bottom: 5px;
            }
            
            .company-address {
                font-size: 12pt;
                color: #555;
            }
            
            .certificate-title {
                text-align: center;
                font-size: 24pt;
                font-weight: bold;
                margin: 30px 0;
                text-decoration: underline;
            }
            
            .cert-number {
                text-align: right;
                font-size: 12pt;
                margin-bottom: 20px;
            }
            
            .content {
                text-align: justify;
                margin: 20px 0;
                text-indent: 50px;
            }
            
            .info-table {
                width: 100%;
                margin: 20px 0;
                border-collapse: collapse;
            }
            
            .info-table td {
                padding: 8px;
                border: 1px solid #ccc;
            }
            
            .info-table td:first-child {
                font-weight: bold;
                background: #f5f5f5;
                width: 30%;
            }
            
            .signature-section {
                margin-top: 60px;
                text-align: center;
            }
            
            .signature-line {
                display: inline-block;
                width: 300px;
                border-bottom: 1px solid #000;
                margin-top: 60px;
            }
            
            .signature-label {
                margin-top: 10px;
            }
            
            .footer {
                margin-top: 40px;
                text-align: center;
                font-size: 11pt;
                color: #666;
                border-top: 1px solid #ccc;
                padding-top: 15px;
            }
            
            @media print {
                body {
                    padding: 0;
                }
                
                .no-print {
                    display: none !important;
                }
                
                .certificate-container {
                    border: 3px double #333;
                    page-break-inside: avoid;
                }
            }
            
            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4CAF50;
                color: white;
                border: none;
                padding: 15px 30px;
                font-size: 16px;
                border-radius: 5px;
                cursor: pointer;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                z-index: 1000;
            }
            
            .print-button:hover {
                background: #45a049;
            }
            
            .watermark {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%) rotate(-45deg);
                font-size: 80pt;
                color: rgba(0, 0, 0, 0.05);
                font-weight: bold;
                pointer-events: none;
                z-index: -1;
            }
        </style>
    </head>
    <body>
        <button class="print-button no-print" onclick="window.print()">
            <?php echo $lang === 'th' ? '🖨️ พิมพ์เอกสาร' : '🖨️ Print'; ?>
        </button>
        
        <div class="certificate-container">
            <div class="watermark">OFFICIAL</div>
            
            <div class="header">
                <div class="company-name"><?php echo $company_name; ?></div>
                <div class="company-address"><?php echo $company_address; ?></div>
                <div class="company-address"><?php echo $lang === 'th' ? 'โทร' : 'Tel'; ?>: <?php echo $phone; ?></div>
            </div>
            
            <div class="cert-number">
                <?php echo $lang === 'th' ? 'เลขที่' : 'No.'; ?> <?php echo $cert_no; ?><br>
                <?php echo $lang === 'th' ? 'วันที่' : 'Date'; ?>: <?php echo $date_issued; ?>
            </div>
            
            <div class="certificate-title">
                <?php echo $lang === 'th' ? 'หนังสือรับรองเงินเดือน' : 'SALARY CERTIFICATE'; ?>
            </div>
            
            <div class="content">
                <?php if ($lang === 'th'): ?>
                    ข้าพเจ้า <?php echo $representative_name; ?> ตำแหน่ง <?php echo $representative_position; ?> 
                    ขอรับรองว่า <strong><?php echo $employee_name; ?></strong> 
                    รหัสพนักงาน <?php echo $employee_id; ?> 
                    เป็นพนักงานของ <?php echo $company_name; ?> 
                    โดยได้เริ่มทำงานตั้งแต่วันที่ <?php echo $date_of_hire; ?> 
                    (รวมระยะเวลาการทำงาน <?php echo $years_of_service; ?> ปี) 
                    ปัจจุบันดำรงตำแหน่ง <?php echo $position; ?> 
                    สังกัด <?php echo $division; ?> 
                    ประเภทการจ้างงาน <?php echo $hiring_type; ?>
                <?php else: ?>
                    This is to certify that <strong><?php echo $employee_name; ?></strong>, 
                    Employee ID: <?php echo $employee_id; ?>, 
                    has been employed by <?php echo $company_name; ?> 
                    since <?php echo $date_of_hire; ?> 
                    (<?php echo $years_of_service; ?> years of service). 
                    Currently holding the position of <?php echo $position; ?> 
                    in <?php echo $division; ?> Department, 
                    under <?php echo $hiring_type; ?> employment type.
                <?php endif; ?>
            </div>
            
            <table class="info-table">
                <tr>
                    <td><?php echo $lang === 'th' ? 'รหัสพนักงาน' : 'Employee ID'; ?></td>
                    <td><?php echo $employee_id; ?></td>
                </tr>
                <tr>
                    <td><?php echo $lang === 'th' ? 'ชื่อ-นามสกุล' : 'Full Name'; ?></td>
                    <td><?php echo $employee_name; ?></td>
                </tr>
                <tr>
                    <td><?php echo $lang === 'th' ? 'ตำแหน่ง' : 'Position'; ?></td>
                    <td><?php echo $position; ?></td>
                </tr>
                <tr>
                    <td><?php echo $lang === 'th' ? 'แผนก' : 'Department'; ?></td>
                    <td><?php echo $division; ?></td>
                </tr>
                <tr>
                    <td><?php echo $lang === 'th' ? 'วันที่เริ่มงาน' : 'Date of Hire'; ?></td>
                    <td><?php echo $date_of_hire; ?></td>
                </tr>
                <tr>
                    <td><?php echo $lang === 'th' ? 'ประเภทการจ้าง' : 'Employment Type'; ?></td>
                    <td><?php echo $hiring_type; ?></td>
                </tr>
                <tr>
                    <td><?php echo $lang === 'th' ? 'เงินเดือนปัจจุบัน' : 'Current Salary'; ?></td>
                    <td><strong><?php echo $salary; ?> <?php echo $lang === 'th' ? 'บาท' : 'THB'; ?></strong></td>
                </tr>
            </table>
            
            <div class="content">
                <?php if ($lang === 'th'): ?>
                    จึงออกหนังสือรับรองฉบับนี้ให้ไว้เพื่อเป็นหลักฐานในการติดต่อราชการ
                <?php else: ?>
                    This certificate is issued upon the request of the above-mentioned employee for official purposes.
                <?php endif; ?>
            </div>
            
            <div class="signature-section">
                <div class="signature-line"></div>
                <div class="signature-label">
                    <strong>(<?php echo $representative_name; ?>)</strong><br>
                    <?php echo $representative_position; ?>
                </div>
            </div>
            
            <div class="footer">
                <?php if ($lang === 'th'): ?>
                    เอกสารฉบับนี้ออกโดยระบบอัตโนมัติ ไม่ต้องลงลายมือชื่อ<br>
                    หากมีข้อสงสัยกรุณาติดต่อฝ่ายทรัพยากรบุคคล โทร. <?php echo $phone; ?>
                <?php else: ?>
                    This document is automatically generated. No signature required.<br>
                    For any inquiries, please contact HR Department at <?php echo $phone; ?>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}
?>