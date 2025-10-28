<?php
/**
 * CERTIFICATE GENERATION API - Professional Edition
 * ================================================================
 * File: api/generate_certificate.php
 * Purpose: Generate Professional Salary Certificate PDF
 * Features:
 *   - Thai/English/Burmese Text Support
 *   - Company Logo Integration
 *   - Auto-calculated Salary in Thai Text
 *   - Signature Management
 *   - Watermark & Security Features
 *   - Printable PDF Format (A4)
 * ================================================================
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Include configuration
require_once __DIR__ . '/../config/db_config.php';

// ========== AUTHENTICATION & AUTHORIZATION ==========
/**
 * Validate User Authentication & Authorization
 */
function validateAccess()
{
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        die(json_encode(['error' => 'Unauthorized - Please login']));
    }

    $user_role = strtolower(trim($_SESSION['user_role'] ?? $_SESSION['role'] ?? ''));

    if (!$user_role && isset($_SESSION['user_id'])) {
        $conn = getDbConnection();
        if (!$conn) {
            http_response_code(500);
            die(json_encode(['error' => 'Database connection failed']));
        }

        $stmt = $conn->prepare("
            SELECT r.role_name FROM employees e 
            LEFT JOIN roles r ON e.role_id = r.role_id 
            WHERE e.employee_id = ?
        ");

        if ($stmt) {
            $stmt->bind_param("s", $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $user_role = strtolower(trim($row['role_name']));
                $_SESSION['user_role'] = $user_role;
            }
            $stmt->close();
        }
        $conn->close();
    }

    $allowed_roles = ['admin', 'officer', 'administrator'];
    if (!in_array($user_role, $allowed_roles)) {
        http_response_code(403);
        die(json_encode(['error' => 'Access denied - Admin/Officer only']));
    }

    return $user_role;
}

// ========== MAIN PROCESSING ==========
try {
    // Validate access
    validateAccess();

    // Get request parameters
    $request_id = intval($_GET['request_id'] ?? 0);
    $lang = $_GET['lang'] ?? 'th';

    // Validate inputs
    if ($request_id <= 0) {
        http_response_code(400);
        die(json_encode(['error' => 'Invalid request ID']));
    }

    if (!in_array($lang, ['th', 'en', 'my'])) {
        $lang = 'th';
    }

    // Get database connection
    $conn = getDbConnection();
    if (!$conn) {
        http_response_code(500);
        die(json_encode(['error' => 'Database connection failed']));
    }

    // Fetch certificate request data
    $certificate_data = fetchCertificateData($conn, $request_id);

    if (!$certificate_data) {
        http_response_code(404);
        die(json_encode(['error' => 'Certificate request not found']));
    }

    // Validate salary information
    if (empty($certificate_data['base_salary']) || $certificate_data['base_salary'] <= 0) {
        http_response_code(400);
        displayMissingSalaryMessage($lang);
        exit;
    }

    // Generate/Update certificate number
    if (empty($certificate_data['certificate_no'])) {
        $certificate_data['certificate_no'] = generateCertificateNumber($conn);
        updateCertificateNumber($conn, $request_id, $certificate_data['certificate_no']);
    }

    $conn->close();

    // Generate PDF
    generateCertificatePDF($certificate_data, $lang);

} catch (Exception $e) {
    error_log('Certificate Generation Error: ' . $e->getMessage());
    http_response_code(500);
    die(json_encode(['error' => 'Error generating certificate: ' . $e->getMessage()]));
}

// ========== HELPER FUNCTIONS ==========

/**
 * Fetch Certificate Data from Database
 */
function fetchCertificateData($conn, $request_id)
{
    $sql = "
        SELECT 
            cr.*,
            e.employee_id,
            e.full_name_th,
            e.full_name_en,
            e.date_of_hire,
            e.phone_no as employee_phone,
            cr.base_salary,
            COALESCE(p.position_name_th, '') as position_name_th,
            COALESCE(p.position_name_en, '') as position_name_en,
            COALESCE(d.division_name_th, '') as division_name_th,
            COALESCE(d.division_name_en, '') as division_name_en,
            COALESCE(d.division_name_my, '') as division_name_my,
            COALESCE(ht.type_name_th, '') as hiring_type_th,
            COALESCE(ht.type_name_en, '') as hiring_type_en,
            COALESCE(ht.type_name_my, '') as hiring_type_my,
            comp.company_name_th,
            comp.company_name_en,
            comp.company_name_my,
            comp.address,
            comp.phone,
            comp.fax,
            comp.representative_name,
            comp.company_logo_path
        FROM certificate_requests cr
        LEFT JOIN employees e ON cr.employee_id = e.employee_id
        LEFT JOIN position_master p ON e.position_id = p.position_id
        LEFT JOIN division_master d ON e.division_id = d.division_id
        LEFT JOIN hiring_type_master ht ON e.hiring_type_id = ht.hiring_type_id
        LEFT JOIN company_info comp ON comp.company_id = 1
        WHERE cr.request_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("i", $request_id);
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    return $data;
}

/**
 * Generate Certificate Number
 * Format: CERT-YYYY-MM-XXXX
 */
function generateCertificateNumber($conn)
{
    $year = date('Y');
    $month = date('m');

    $count_sql = "
        SELECT COUNT(*) as count FROM certificate_requests 
        WHERE YEAR(created_at) = YEAR(CURDATE())
    ";

    $result = $conn->query($count_sql);
    $row = $result->fetch_assoc();
    $next_num = ($row['count'] ?? 0) + 1;

    return sprintf('CERT-%s-%s-%04d', $year, $month, $next_num);
}

/**
 * Update Certificate Number in Database
 */
function updateCertificateNumber($conn, $request_id, $cert_no)
{
    $sql = "UPDATE certificate_requests SET certificate_no = ? WHERE request_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("si", $cert_no, $request_id);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Display Missing Salary Message
 */
function displayMissingSalaryMessage($lang)
{
    $messages = [
        'th' => ['title' => 'ไม่พบข้อมูลเงินเดือน', 'message' => 'กรุณากรอกข้อมูลเงินเดือนก่อนสร้างหนังสือรับรอง'],
        'en' => ['title' => 'Missing Salary Information', 'message' => 'Please enter salary information before generating certificate'],
        'my' => ['title' => 'လစာ အချက်အလက် မရှိပါ', 'message' => 'လက်မှတ်စာတင် ဖန်တီးမည့်အလျင် လစာ အချက်အလက်ကို ထည့်သွင်းပါ']
    ];

    $msg = $messages[$lang] ?? $messages['th'];
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo $lang; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $msg['title']; ?></title>
        <script src="https://cdn.tailwindcss.com"></script>
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap');
            body {
                font-family: 'Sarabun', sans-serif !important;
            }
        </style>
    </head>
    <body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full">
            <div class="text-center">
                <div class="text-6xl mb-6">⚠️</div>
                <h1 class="text-2xl font-bold text-red-600 mb-3"><?php echo htmlspecialchars($msg['title']); ?></h1>
                <p class="text-gray-600 mb-6 leading-relaxed"><?php echo htmlspecialchars($msg['message']); ?></p>
                <button onclick="window.close()" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 transform hover:scale-105">
                    <?php echo $lang === 'th' ? '✕ ปิดหน้าต่าง' : ($lang === 'en' ? '✕ Close' : '✕ ပိတ်မည်'); ?>
                </button>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Convert Number to Thai Text
 * Example: 17000 -> "หนึ่งหมื่นเจ็ดพันบาทถ้วน"
 */
function numberToThaiText($number)
{
    $baht_int = intval($number);
    $satang = round(($number - $baht_int) * 100);

    $thai_ones = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
    $thai_tens = ['', 'สิบ', 'ยี่สิบ', 'สามสิบ', 'สี่สิบ', 'ห้าสิบ', 'หกสิบ', 'เจ็ดสิบ', 'แปดสิบ', 'เก้าสิบ'];

    $text = '';

    // Process millions
    $millions = intval($baht_int / 1000000);
    if ($millions > 0) {
        $text .= convertThaiGroup($millions) . 'ล้าน';
    }

    // Process thousands
    $remainder = $baht_int % 1000000;
    $thousands = intval($remainder / 1000);
    if ($thousands > 0) {
        $text .= convertThaiGroup($thousands) . 'พัน';
    }

    // Process hundreds and tens
    $remainder = $remainder % 1000;
    $text .= convertThaiGroup($remainder);
    $text .= 'บาท';

    // Add satang if any
    if ($satang > 0) {
        $text .= convertThaiGroup($satang) . 'สตางค์';
    } else {
        $text .= 'ถ้วน';
    }

    return $text;
}

/**
 * Convert 0-999 to Thai Text
 */
function convertThaiGroup($num)
{
    $thai_ones = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
    $thai_tens = ['', 'สิบ', 'ยี่สิบ', 'สามสิบ', 'สี่สิบ', 'ห้าสิบ', 'หกสิบ', 'เจ็ดสิบ', 'แปดสิบ', 'เก้าสิบ'];

    $text = '';

    // Hundreds
    $hundreds = intval($num / 100);
    if ($hundreds > 0) {
        $text .= $thai_ones[$hundreds] . 'ร้อย';
    }

    // Tens and ones
    $remainder = $num % 100;
    if ($remainder >= 10) {
        $tens = intval($remainder / 10);
        $ones = $remainder % 10;

        if ($tens === 1) {
            $text .= 'สิบ';
        } else {
            $text .= $thai_tens[$tens];
        }

        if ($ones > 0) {
            if ($ones === 1 && $tens === 2) {
                $text .= 'เอ็ด';
            } else {
                $text .= $thai_ones[$ones];
            }
        }
    } else if ($remainder > 0) {
        $text .= $thai_ones[$remainder];
    }

    return $text;
}

/**
 * Format Date based on Language
 */
function formatDate($date_string, $lang = 'th')
{
    if (empty($date_string)) return '';

    $date = new DateTime($date_string);
    $day = $date->format('d');
    $month_num = intval($date->format('m'));
    $year = intval($date->format('Y'));

    $thai_months = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
        5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
        9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];

    $eng_months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    if ($lang === 'th') {
        $year_buddhist = $year + 543;
        return "$day " . $thai_months[$month_num] . " $year_buddhist";
    } elseif ($lang === 'en') {
        return $eng_months[$month_num - 1] . " $day, $year";
    } else { // Burmese
        return "$day/" . str_pad($month_num, 2, '0', STR_PAD_LEFT) . "/$year";
    }
}

/**
 * Generate PDF HTML Content
 */
function generateCertificatePDF($data, $lang = 'th')
{
    $cert_no = htmlspecialchars($data['certificate_no']);
    $employee_id = htmlspecialchars($data['employee_id']);

    // Get localized content
    $content = getLocalizedContent($data, $lang);

    // Format dates
    $date_issued = formatDate(date('Y-m-d'), $lang);
    $date_of_hire = formatDate($data['date_of_hire'], $lang);

    // Format salary
    $salary_numeric = number_format($data['base_salary'], 2, '.', ',');
    $salary_text = ($lang === 'th') ? numberToThaiText($data['base_salary']) : '';

    // Company logo path
    $logo_path = !empty($data['company_logo_path']) && file_exists(__DIR__ . '/../uploads/company/' . basename($data['company_logo_path']))
        ? '../uploads/company/' . basename($data['company_logo_path'])
        : '';

    // Output HTML
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo $lang; ?>">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($content['title']); ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            /* ================================================================
   CERTIFICATE PRINT STYLESHEET - Professional Edition
   File: assets/css/certificate-print.css
   Purpose: A4 Print-Ready Layout for Salary Certificate
   ================================================================ */

@import url('https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap');

/* ===== RESET & BASE STYLES ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html,
body {
    width: 100%;
    height: 100%;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Sarabun', 'TH Sarabun New', 'Cordia New', 'Courier New', sans-serif;
    font-size: 15px;
    line-height: 1.6;
    color: #1a1a1a;
    background: #f0f0f0;
}

/* ===== PAGE SETUP (A4 210x297mm) ===== */
@page {
    size: A4;
    margin: 1.5cm;
    padding: 0;
    orphans: 3;
    widows: 3;
}

.certificate-container {
    width: 210mm;
    min-height: 297mm;
    height: 297mm;
    margin: 20px auto;
    padding: 25px;
    background: white;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    page-break-after: avoid;
    position: relative;
    overflow: hidden;
}

/* ===== WATERMARK (Background) ===== */
.certificate-watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    font-size: 120px;
    font-weight: 300;
    color: rgba(44, 90, 160, 0.08);
    white-space: nowrap;
    z-index: 0;
    pointer-events: none;
    letter-spacing: 20px;
}

.certificate-watermark::before {
    content: 'OFFICIAL CERTIFICATE';
}

/* ===== HEADER SECTION ===== */
.header-section {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 3px solid #000000;
    position: relative;
    z-index: 1;
}

.logo-container {
    flex-shrink: 0;
    width: 90px;
    height: 90px;
    background: #000000;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 5px;
    border: 1px solid #e0e0e0;
}

.company-logo {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.company-header {
    flex: 1;
    text-align: left;
}

.company-name {
    font-size: 20px;
    font-weight: 700;
    color: #000000;
    margin-bottom: 4px;
    letter-spacing: 0.5px;
}

.company-address {
    font-size: 13px;
    color: #333;
    margin-bottom: 3px;
    line-height: 1.4;
}

.company-contact {
    font-size: 12px;
    color: #555;
    font-style: italic;
}

/* ===== CERTIFICATE TITLE ===== */
.certificate-title {
    text-align: center;
    font-size: 32px;
    font-weight: 700;
    margin: 20px 0 10px 0;
    color: #000;
    text-transform: uppercase;
    letter-spacing: 2px;
    position: relative;
    z-index: 1;
}

/* ===== CERTIFICATE NUMBER ===== */
.certificate-number {
    text-align: center;
    font-size: 13px;
    margin: 8px 0 15px 0;
    color: #666;
    position: relative;
    z-index: 1;
}

/* ===== CONTENT SECTION ===== */
.content-section {
    position: relative;
    z-index: 1;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    margin-bottom: 20px;
}

.opening-statement {
    text-align: justify;
    font-size: 15px;
    line-height: 1.7;
    margin-bottom: 15px;
    text-indent: 2.5cm;
    color: #1a1a1a;
}

/* ===== EMPLOYEE DETAILS SECTION ===== */
.employee-section {
    margin: 10px 0 15px 0;
    padding-left: 2cm;
    line-height: 1.8;
}

.detail-row {
    display: grid;
    grid-template-columns: 4.5cm 0.7cm 1fr;
    gap: 8px;
    margin-bottom: 6px;
    align-items: baseline;
    font-size: 14px;
}

.label {
    font-weight: 600;
    color: #000;
    padding-right: 5px;
}

.separator {
    text-align: center;
    font-weight: normal;
    color: #000;
}

.value {
    color: #333;
    font-weight: normal;
    padding-left: 5px;
}

/* ===== SALARY HIGHLIGHT BOX ===== */
.salary-highlight {
    background: linear-gradient(135deg, #f5f5f5 0%, #efefef 100%);
    border-left: 5px solid #000000;
    border-radius: 6px;
    padding: 12px 15px;
    margin: 15px 0;
    position: relative;
    z-index: 2;
}

.salary-box-title {
    font-size: 13px;
    font-weight: 600;
    color: #000000;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.salary-amount {
    font-size: 24px;
    font-weight: 700;
    color: #d32f2f;
    margin-bottom: 4px;
    font-family: 'Courier New', monospace;
    letter-spacing: 1px;
}

.salary-text-thai {
    font-size: 13px;
    color: #333;
    font-style: italic;
    font-weight: 500;
    line-height: 1.6;
}

/* ===== CLOSING STATEMENT ===== */
.closing-statement {
    text-align: justify;
    font-size: 14px;
    line-height: 1.7;
    margin: 12px 0;
    text-indent: 2.5cm;
    color: #1a1a1a;
    position: relative;
    z-index: 1;
}

/* ===== SIGNATURE SECTION ===== */
.signature-section {
    display: flex;
    flex-direction: column;
    position: relative;
    z-index: 1;
    gap: 20px;
    width: 300px;
    margin: 30px auto 10px auto;
}

.date-issued {
    flex: 1;
    text-align: center;
    font-size: 13px;
    line-height: 1.8;
}

.date-issued strong {
    font-weight: 600;
    color: #000;
}

.date-issued span {
    color: #333;
    font-weight: 500;
}

.signature-box {
    flex: 1;
    text-align: center;
    min-width: 250px;
}

.signature-line {
    border-bottom: 1px solid #000;
    height: 70px;
    margin-bottom: 8px;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    position: relative;
}

.signature-label {
    font-size: 13px;
    line-height: 1.6;
    color: #000;
}

.signature-label strong {
    display: block;
    font-weight: 700;
    margin-bottom: 3px;
}

.position-title {
    font-size: 12px;
    color: #555;
    font-style: italic;
}

/* ===== FOOTER SECTION ===== */
.certificate-footer {
    text-align: center;
    font-size: 11px;
    color: #888;
    margin-top: auto;
    padding-top: 15px;
    border-top: 1px dashed #ccc;
    line-height: 1.5;
    position: relative;
    z-index: 1;
}

.certificate-footer p {
    margin: 3px 0;
}

.print-date {
    font-style: italic;
    color: #999;
    margin-top: 8px;
}

/* ===== PRINT BUTTONS (Hidden in Print) ===== */
.no-print {
    display: block !important;
}

.print-btn,
.close-btn {
    padding: 10px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.print-btn {
    background: linear-gradient(135deg, #2c5aa0 0%, #1a3a5c 100%);
    color: white;
}

.print-btn:hover {
    background: linear-gradient(135deg, #1a3a5c 0%, #0f1f3a 100%);
    box-shadow: 0 4px 12px rgba(44, 90, 160, 0.3);
    transform: translateY(-2px);
}

.close-btn {
    background: #f0f0f0;
    color: #666;
    width: 40px;
    height: 40px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.close-btn:hover {
    background: #e0e0e0;
    color: #000;
}

/* ===== MEDIA QUERIES (SCREEN VIEW) ===== */
@media screen {
    body {
        background: #ddd;
        padding: 20px;
    }

    .certificate-container {
        max-width: 100%;
        margin: 20px auto;
    }
}

/* ===== PRINT MEDIA RULES ===== */
@media print {
    * {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    html,
    body {
        margin: 0;
        padding: 0;
        background: white;
        width: 100%;
        height: auto;
    }

    .certificate-container {
        width: 100%;
        height: auto;
        min-height: auto;
        margin: 0;
        padding: 1.5cm;
        box-shadow: none;
        page-break-after: avoid;
    }

    .header-section,
    .certificate-title,
    .certificate-number,
    .content-section,
    .signature-section,
    .certificate-footer {
        page-break-inside: avoid;
    }

    .employee-section {
        page-break-inside: avoid;
    }

    .salary-highlight {
        background: #f5f5f5 !important;
        border: 1px solid #000000 !important;
    }

    /* Hide screen-only elements */
    .no-print,
    button {
        display: none !important;
    }

    /* Remove scrollbars and adjust sizing for print */
    body,
    html {
        overflow: visible;
        height: auto;
    }
}

/* ===== RESPONSIVE DESIGN (Mobile Preview) ===== */
@media screen and (max-width: 768px) {
    .certificate-container {
        width: 100%;
        margin: 10px auto;
        padding: 15px;
        min-height: auto;
        height: auto;
    }

    .certificate-title {
        font-size: 24px;
    }

    .company-name {
        font-size: 16px;
    }

    .detail-row {
        grid-template-columns: 1fr;
        gap: 2px;
    }

    .label::after {
        content: ': ';
    }

    .separator {
        display: none;
    }

    .signature-section {
        flex-direction: column;
        gap: 30px;
    }

    .salary-amount {
        font-size: 20px;
    }
}

/* ===== ACCESSIBILITY ===== */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* ===== DARK MODE SUPPORT ===== */
@media (prefers-color-scheme: dark) {
    @media print {
        .certificate-container {
            background: white;
            color: black;
        }

        .company-name,
        .label,
        .signature-label strong {
            color: #000 !important;
        }
    }
}

/* ===== SPECIAL PRINT STYLES FOR CHROME/FIREFOX ===== */
@-webkit-page {
    size: A4;
    margin: 1.5cm;
}

/* ===== TEXT SELECTION STYLING ===== */
::selection {
    background: rgba(44, 90, 160, 0.2);
    color: inherit;
}

::-moz-selection {
    background: rgba(44, 90, 160, 0.2);
    color: inherit;
}
        </style>
    </head>
    <body>

        <div class="no-print fixed top-4 right-4 z-50 flex gap-2">
            <button onclick="window.print()" class="print-btn" title="<?php echo $content['print_title']; ?>">
                🖨️ <?php echo $content['print_button']; ?>
            </button>
            <button onclick="window.close()" class="close-btn" title="<?php echo $content['close_title']; ?>">
                ✕
            </button>
        </div>

        <!-- Certificate Container -->
        <div class="certificate-container">
            <!-- Header with Watermark -->
            <div class="certificate-watermark"></div>

            <!-- Company Header -->
            <div class="header-section">
                <?php if (!empty($logo_path)): ?>
                    <div class="logo-container">
                        <img src="<?php echo htmlspecialchars($logo_path); ?>" alt="Company Logo" class="company-logo">
                    </div>
                <?php endif; ?>

                <div class="company-header">
                    <h1 class="company-name"><?php echo htmlspecialchars($content['company_name']); ?></h1>
                    <div class="company-address"><?php echo htmlspecialchars($data['address']); ?></div>
                    <div class="company-contact">
                        <span><?php echo $content['phone_label']; ?>: <?php echo htmlspecialchars($data['phone']); ?></span>
                        <?php if (!empty($data['fax'])): ?>
                            <span> | <?php echo $content['fax_label']; ?>: <?php echo htmlspecialchars($data['fax']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Certificate Title -->
            <div class="certificate-title">
                <?php echo htmlspecialchars($content['title']); ?>
            </div>

            <!-- Certificate Number -->
            <div class="certificate-number">
                <strong><?php echo $content['cert_no_label']; ?>:</strong> <?php echo htmlspecialchars($cert_no); ?>
            </div>

            <!-- Content Section -->
            <div class="content-section">
                <p class="opening-statement">
                    <?php echo nl2br(htmlspecialchars($content['opening_statement'])); ?> <?php echo nl2br(htmlspecialchars($content['company'])); ?>
                </p>

                <!-- Employee Details -->
                <div class="employee-section">
                    <div class="detail-row">
                        <span class="label"><?php echo $content['name_label']; ?></span>
                        <span class="separator">:</span>
                        <span class="value"><?php echo htmlspecialchars($content['employee_name']); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="label"><?php echo $content['emp_id_label']; ?></span>
                        <span class="separator">:</span>
                        <span class="value"><?php echo htmlspecialchars($employee_id); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="label"><?php echo $content['position_label']; ?></span>
                        <span class="separator">:</span>
                        <span class="value"><?php echo htmlspecialchars($content['position']); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="label"><?php echo $content['division_label']; ?></span>
                        <span class="separator">:</span>
                        <span class="value"><?php echo htmlspecialchars($content['division']); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="label"><?php echo $content['start_date_label']; ?></span>
                        <span class="separator">:</span>
                        <span class="value"><?php echo $date_of_hire; ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="label"><?php echo $content['emp_type_label']; ?></span>
                        <span class="separator">:</span>
                        <span class="value"><?php echo htmlspecialchars($content['hiring_type']); ?></span>
                    </div>

                    <div class="detail-row">
                        <span class="label"><?php echo $content['salary_label']; ?></span>
                        <span class="separator">:</span>
                        <span class="value"><?php echo $salary_numeric; ?> <?php if (!empty($salary_text)): ?> (<?php echo htmlspecialchars($salary_text); ?>)<?php endif; ?></span>
                    </div>
                </div>

                <!-- Closing Statement -->
                <p class="closing-statement">
                    <?php echo nl2br(htmlspecialchars($content['closing_statement'])); ?> <br> <?php echo nl2br(htmlspecialchars($content['closing_statement_s'])); ?>
                </p>
            </div>

            <div class="signature-section">
                <div class="date-issued">
                    <strong><?php echo $content['issued_label']; ?>:</strong><br>
                    <span><?php echo $date_issued; ?></span>
                </div>

                <div class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">
                        <strong>(<?php echo htmlspecialchars($data['representative_name']); ?>)</strong><br>
                        <span class="position-title"><?php echo $content['sig_position']; ?></span>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="certificate-footer">
                <p><?php echo htmlspecialchars($content['footer_text']); ?></p>
                <p class="print-date"><?php echo $content['print_date_label']; ?>: <?php echo date('d/m/Y H:i:s'); ?></p>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Get Localized Content
 */
function getLocalizedContent($data, $lang)
{
    $content = [
        'th' => [
            'title' => 'หนังสือรับรองเงินเดือน',
            'cert_no_label' => 'เลขที่',
            'phone_label' => 'โทร',
            'fax_label' => 'แฟกซ์',
            'name_label' => 'ชื่อ-สกุล',
            'emp_id_label' => 'รหัสพนักงาน',
            'position_label' => 'ตำแหน่ง',
            'division_label' => 'สังกัด',
            'start_date_label' => 'วันเริ่มงาน',
            'emp_type_label' => 'ประเภทพนักงาน',
            'salary_label' => 'เงินเดือนประจำปัจจุบัน',
            'opening_statement' => 'หนังสือรับรองฉบับนี้ ขอรับรองว่าบุคคลผู้มีนามข้างล่างนี้ ปัจจุบันเป็นพนักงานของ',
            'company' => htmlspecialchars($data['company_name_th'] ?? ''),
            'closing_statement' => 'รายได้ที่กล่าวมาข้างต้นไม่รวมรายได้อื่นที่พนักงานได้รับต่อเดือน',
            'closing_statement_s' => 'และขอรับรองว่าข้อมูลข้างต้นเป็นความจริงทั้งสิ้น',
            'issued_label' => 'ให้ไว้',
            'sig_position' => 'ผู้จัดการฝ่ายทรัพยากรมนุษย์',
            'print_button' => 'พิมพ์',
            'print_title' => 'พิมพ์เอกสาร',
            'close_title' => 'ปิดหน้าต่าง',
            'footer_text' => 'เอกสารนี้เป็นเอกสารอย่างการ และรับรองโดยบริษัท',
            'print_date_label' => 'สร้างเมื่อ',
            'company_name' => htmlspecialchars($data['company_name_th'] ?? ''),
            'employee_name' => htmlspecialchars($data['full_name_th'] ?? ''),
            'position' => htmlspecialchars($data['position_name_th'] ?? ''),
            'division' => htmlspecialchars($data['division_name_th'] ?? ''),
            'hiring_type' => htmlspecialchars($data['hiring_type_th'] ?? ''),
        ],
        'en' => [
            'title' => 'SALARY CERTIFICATE',
            'cert_no_label' => 'Certificate No.',
            'phone_label' => 'Tel',
            'fax_label' => 'Fax',
            'name_label' => 'Full Name',
            'emp_id_label' => 'Employee ID',
            'position_label' => 'Position',
            'division_label' => 'Department',
            'start_date_label' => 'Start Date',
            'emp_type_label' => 'Employment Type',
            'salary_label' => 'Current Monthly Salary',
            'opening_statement' => 'This is to certify that the person named below is currently an employee of',
            'company' => htmlspecialchars($data['company_name_en'] ?? ''),
            'closing_statement' => 'The above salary does not include other monthly income that the employee receives.',
            'closing_statement_s' => 'I hereby certify that the above information is true and correct in all respects.',
            'issued_label' => 'Issued on',
            'sig_position' => 'Human Resources Manager',
            'print_button' => 'PRINT',
            'print_title' => 'Print Document',
            'close_title' => 'Close Window',
            'footer_text' => 'This is an official document certified by the company.',
            'print_date_label' => 'Generated on',
            'company_name' => htmlspecialchars($data['company_name_en'] ?? ''),
            'employee_name' => htmlspecialchars($data['full_name_en'] ?? ''),
            'position' => htmlspecialchars($data['position_name_en'] ?? ''),
            'division' => htmlspecialchars($data['division_name_en'] ?? ''),
            'hiring_type' => htmlspecialchars($data['hiring_type_en'] ?? ''),
        ],
        'my' => [
            'title' => 'လစာ အက်ခံလက်မှတ်',
            'cert_no_label' => 'နံပါတ်',
            'phone_label' => 'ဖုန်း',
            'fax_label' => 'ဖက်စ်',
            'name_label' => 'အမည်အပြည့်အစုံ',
            'emp_id_label' => 'အလုပ်သမားအသိအတCount်',
            'position_label' => 'ရာထူး',
            'division_label' => 'ဌာန',
            'start_date_label' => 'စတင်သည့်နေ့',
            'emp_type_label' => 'အလုပ်သမားအမျိုးအစား',
            'salary_label' => 'လစာဖြည့်ဆည်းမှု',
            'opening_statement' => 'ဤအက်ခံလက်မှတ်သည် အောက်ဖော်ပြပါ လူသည်',
            'company' => htmlspecialchars($data['company_name_my'] ?? ''),
            'closing_statement' => 'အပ်ခံထားသော လစာသည် လစုတစ်လအတွင်း',
            'closing_statement_s' => ' ရရှိသည့် အခြား ဝင်ငွေများ မပါဝင်ပါ',
            'issued_label' => 'အက်ခံသည့်နေ့',
            'sig_position' => 'လူမှုဆက်ဆံရေးကဏ္ဍ မန်နေဂျာ',
            'print_button' => 'ပုံနှိုပ်',
            'print_title' => 'စာ류ပုံနှိုပ်ခြင်း',
            'close_title' => 'အခန်းကျင့်ပိတ်မည်',
            'footer_text' => 'ဤစာရွက်သည် ကုမ္ပဏီမှ အက်ခံလက်မှတ်ရှိသော နည်းဥပဒေအရ စာရွက်ဖြစ်သည်',
            'print_date_label' => 'ဖန်တီးသည့်နေ့',
            'company_name' => htmlspecialchars($data['company_name_my'] ?? ''),
            'employee_name' => htmlspecialchars($data['full_name_en'] ?? ''),
            'position' => htmlspecialchars($data['position_name_my'] ?? ''),
            'division' => htmlspecialchars($data['division_name_my'] ?? ''),
            'hiring_type' => htmlspecialchars($data['hiring_type_my'] ?? ''),
        ],
    ];

    return $content[$lang] ?? $content['th'];
}

?>