<?php

/**
 * ENHANCED: Generate Certificate - Dynamic Template Version
 * File: /api/generate_certificate.php (UPDATED)
 * 
 * IMPROVEMENTS:
 * 1. ดึง template จากฐานข้อมูล (certificate_types.template_content)
 * 2. Replace placeholder ด้วยข้อมูลพนักงาน
 * 3. รองรับประเภทหนังสือรับรองหลายแบบ
 * 4. Dynamic title ตามประเภท
 */

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// ========== AUTHENTICATION ==========
try {
    AuthController::requireRole(['admin', 'officer']);

    $request_id = (int)($_GET['request_id'] ?? 0);
    $lang = $_GET['lang'] ?? $_SESSION['language'] ?? 'th';

    if ($request_id <= 0) {
        http_response_code(400);
        die(json_encode(['error' => 'Invalid request ID']));
    }

    if (!in_array($lang, ['th', 'en', 'my'])) {
        $lang = 'th';
    }

    // ========== DATABASE CONNECTION ==========
    $conn = getDbConnection();
    if (!$conn) {
        http_response_code(500);
        die('Database connection failed');
    }

    // ========== FETCH CERTIFICATE REQUEST WITH TEMPLATE ==========
    $sql = "
        SELECT 
            cr.*,
            ct.cert_type_id,
            ct.type_name_th,
            ct.type_name_en,
            ct.type_name_my,
            ct.template_content,
            e.employee_id,
            e.full_name_th,
            e.full_name_en,
            e.date_of_hire,
            COALESCE(p.position_name_th, '') as position_name_th,
            COALESCE(p.position_name_en, '') as position_name_en,
            COALESCE(d.division_name_th, '') as division_name_th,
            COALESCE(d.division_name_en, '') as division_name_en,
            COALESCE(ht.type_name_th, '') as hiring_type_th,
            COALESCE(ht.type_name_en, '') as hiring_type_en,
            comp.company_name_th,
            comp.company_name_en,
            comp.company_name_my,
            comp.address,
            comp.phone,
            comp.fax,
            comp.company_logo_path,
            comp.representative_name
        FROM certificate_requests cr
        LEFT JOIN certificate_types ct ON cr.cert_type_id = ct.cert_type_id
        LEFT JOIN employees e ON cr.employee_id = e.employee_id
        LEFT JOIN position_master p ON e.position_id = p.position_id
        LEFT JOIN division_master d ON e.division_id = d.division_id
        LEFT JOIN hiring_type_master ht ON e.hiring_type_id = ht.hiring_type_id
        LEFT JOIN company_info comp ON comp.company_id = 1
        WHERE cr.request_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    if (!$data) {
        http_response_code(404);
        die('Certificate request not found');
    }

    // ========== VALIDATE REQUIRED DATA ==========
    if (empty($data['template_content'])) {
        http_response_code(400);
        die('Certificate template not found');
    }

    if (empty($data['base_salary']) || $data['base_salary'] <= 0) {
        http_response_code(400);
        die('Base salary is missing or invalid');
    }

    // ========== PREPARE REPLACEMENT DATA ==========
    $replacements = [
        '{employee_name}' => htmlspecialchars($lang === 'en'
            ? ($data['full_name_en'] ?? $data['full_name_th'] ?? '')
            : ($data['full_name_th'] ?? '')),

        '{employee_id}' => htmlspecialchars($data['employee_id']),

        '{position}' => htmlspecialchars($lang === 'en'
            ? ($data['position_name_en'] ?? $data['position_name_th'] ?? '')
            : ($data['position_name_th'] ?? '')),

        '{division}' => htmlspecialchars($lang === 'en'
            ? ($data['division_name_en'] ?? $data['division_name_th'] ?? '')
            : ($data['division_name_th'] ?? '')),

        '{date_of_hire}' => formatDate($data['date_of_hire'], $lang),

        '{hiring_type}' => htmlspecialchars($lang === 'en'
            ? ($data['hiring_type_en'] ?? $data['hiring_type_th'] ?? '')
            : ($data['hiring_type_th'] ?? '')),

        '{base_salary}' => number_format((float)$data['base_salary'], 2),

        '{base_salary_text}' => $lang === 'th' ? numberToThaiText((float)$data['base_salary']) : '',

        '{company_name}' => htmlspecialchars($lang === 'en'
            ? ($data['company_name_en'] ?? $data['company_name_th'] ?? '')
            : ($data['company_name_th'] ?? '')),

        '{company_address}' => htmlspecialchars($data['address'] ?? ''),

        '{company_phone}' => htmlspecialchars($data['phone'] ?? ''),

        '{representative_name}' => htmlspecialchars($data['representative_name'] ?? ''),

        '{issued_date}' => formatDate(date('Y-m-d'), $lang),
    ];

    // ========== GENERATE CERTIFICATE NUMBER IF NOT EXISTS ==========
    if (empty($data['certificate_no'])) {
        $cert_no = 'CERT-' . date('Ymd') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $update_sql = "UPDATE certificate_requests SET certificate_no = ? WHERE request_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $cert_no, $request_id);
        $update_stmt->execute();
        $update_stmt->close();
        $data['certificate_no'] = $cert_no;
    }

    $replacements['{certificate_no}'] = htmlspecialchars($data['certificate_no']);

    // ========== GET CERTIFICATE TYPE TITLE ==========
    $cert_title_th = $data['type_name_th'] ?? 'หนังสือรับรอง';
    $cert_title_en = $data['type_name_en'] ?? 'Certificate';
    $cert_title_my = $data['type_name_my'] ?? 'လက်မှတ်';

    $cert_title = $lang === 'en' ? $cert_title_en : ($lang === 'my' ? $cert_title_my : $cert_title_th);

    // ========== REPLACE PLACEHOLDERS IN TEMPLATE ==========
    $template_html = $data['template_content'];
    foreach ($replacements as $placeholder => $value) {
        $template_html = str_replace($placeholder, $value, $template_html);
    }

    // ========== BUILD COMPLETE HTML DOCUMENT ==========
    $logo_path = '';
    if (!empty($data['company_logo_path'])) {
        $logo_file = __DIR__ . '/../uploads/company/' . basename($data['company_logo_path']);
        if (file_exists($logo_file)) {
            $logo_path = '../uploads/company/' . basename($data['company_logo_path']);
        }
    }

    $html = <<<HTML
    <!DOCTYPE html>
    <html lang="$lang">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>$cert_title</title>

         <!-- Favicon -->
        <link rel="icon" type="image/x-icon" href="../assets/images/favicons/favicon.ico">
        <link rel="icon" type="image/png" sizes="16x16" href="../assets/images/favicons/favicon-16x16.png">
        <link rel="icon" type="image/png" sizes="32x32" href=../assets/images/favicons/favicon-32x32.png">

        <!-- Apple Touch Icon -->
        <link rel="apple-touch-icon" sizes="180x180" href="../assets/images/favicons/apple-touch-icon.png">

        <!-- Android Chrome Icons -->
        <link rel="icon" type="image/png" sizes="192x192" href="../assets/images/favicons/android-chrome-192x192.png">
        <link rel="icon" type="image/png" sizes="512x512" href="../assets/images/favicons/android-chrome-512x512.png">

        <!-- Web App Manifest (Optional) -->
        <link rel="manifest" href="/site.webmanifest">

        <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Sarabun', 'TH Sarabun New', 'Cordia New', sans-serif;
                background: #f0f0f0;
                padding: 20px;
            }
            
            .certificate-page {
                width: 210mm;
                height: 297mm;
                margin: 20px auto;
                padding: 40px;
                background: white;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                page-break-after: avoid;
            }
            
            .certificate-header {
                margin-bottom: 30px;
                display: flex;
                gap: 20px;
                border-bottom: 1px solid #ccc;
                padding-bottom: 10px;
            }
            
            .logo-container {
                width: 80px;
                height: 80px;
                background: #000000;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 3px;
            }
            
            .logo-container img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
            }
            
            .company-info h2 {
                font-size: 26px;
                color: #000;
                margin-bottom: 5px;
            }
            
            .company-info p {
                font-size: 16px;
                color: #666;
                margin: 2px 0;
            }
            
            .certificate-content {
                padding: 30px;
                border: 2px solid #333;
                border-radius: 8px;
                min-height: 400px;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            
            .certificate-title {
                font-size: 32px;
                font-weight: 700;
                text-align: center;
                margin-bottom: 30px;
                color: #000;
                text-transform: uppercase;
                letter-spacing: 2px;
            }
            
            .certificate-content p {
                font-size: 16px;
                line-height: 1.8;
                margin-bottom: 15px;
                text-align: justify;
            }
            
            .signature-section {
                display: flex;
                justify-content: center;
                margin-top: 50px;
            }
            
            .signature-box {
                text-align: center;
                width: auto;
            }
            
            .signature-line {
                border-top: 1px solid #000;
                width: 100%;
                height: 10px;
            }
            
            .signature-label {
                font-size: 16px;
                font-weight: 600;
            }
            
            .no-print {
                display: block;
                text-align: center;
                padding: 20px;
            }
            
            .print-btn {
                background: #2c5aa0;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 6px;
                cursor: pointer;
                font-size: 16px;
                font-weight: 600;
                margin: 10px;
            }
            
            .print-btn:hover {
                background: #1a3a5c;
            }
            
            @media print {
                body {
                    background: white;
                    padding: 0;
                }
                
                .certificate-page {
                    width: 100%;
                    height: auto;
                    margin: 0;
                    padding: 40px;
                    box-shadow: none;
                }
                
                .no-print {
                    display: none !important;
                }
            }
        </style>
    </head>
    <body>
        <!-- Print Controls -->
        <div class="no-print">
            <button class="print-btn" onclick="window.print()">🖨️ พิมพ์เอกสาร</button>
            <button class="print-btn" onclick="window.close()">✕ ปิด</button>
        </div>
        
        <!-- Certificate -->
        <div class="certificate-page">
            <div class="certificate-header">
HTML;

    if (!empty($logo_path)) {
        $html .= <<<HTML
                <div class="logo-container">
                    <img src="$logo_path" alt="Logo">
                </div>
HTML;
    }

    $html .= <<<HTML
                <div class="company-info">
                    <h2>{$replacements['{company_name}']}</h2>
                    <p>{$replacements['{company_address}']}</p>
                    <p>โทร: {$replacements['{company_phone}']}</p>
                </div>
            </div>
            
            {$template_html}
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-label">ให้ ไว้ ณ วันที่ {$replacements['{issued_date}']}</div><br><br><br><br>
                    <div class="signature-line"></div>
                    <div class="signature-label">({$replacements['{representative_name}']})</div>
                    <div class="signature-label">ผู้จัดการฝ่ายทรัพยากรมนุษย์</div>
                </div>
            </div>
        </div>
    </body>
    </html>
HTML;

    $conn->close();

    // ========== OUTPUT HTML ==========
    header('Content-Type: text/html; charset=utf-8');
    echo $html;
} catch (Exception $e) {
    error_log('Certificate Generation Error: ' . $e->getMessage());
    http_response_code(500);
?>
    <!DOCTYPE html>
    <html>

    <head>
        <meta charset="UTF-8">
        <title>Error</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>

    <body class="bg-red-50 flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md text-center">
            <h1 class="text-2xl font-bold text-red-600 mb-4">⚠️ Error</h1>
            <p class="text-gray-600"><?php echo htmlspecialchars($e->getMessage()); ?></p>
            <button onclick="window.close()" class="mt-6 bg-red-600 text-white px-6 py-2 rounded-lg">ปิด</button>
        </div>
    </body>

    </html>
<?php
}

// ========== HELPER FUNCTIONS ==========

function formatDate($date_string, $lang = 'th')
{
    if (empty($date_string)) return '';

    $date = new DateTime($date_string);
    $day = $date->format('d');
    $month = (int)$date->format('m');
    $year = (int)$date->format('Y');

    $thai_months = [
        '',
        'มกราคม',
        'กุมภาพันธ์',
        'มีนาคม',
        'เมษายน',
        'พฤษภาคม',
        'มิถุนายน',
        'กรกฎาคม',
        'สิงหาคม',
        'กันยายน',
        'ตุลาคม',
        'พฤศจิกายน',
        'ธันวาคม'
    ];

    if ($lang === 'th') {
        $year_buddhist = $year + 543;
        return "$day " . $thai_months[$month] . " $year_buddhist";
    } elseif ($lang === 'en') {
        $months = [
            '',
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];
        return $months[$month] . " $day, $year";
    }

    return "$day/" . str_pad($month, 2, '0', STR_PAD_LEFT) . "/$year";
}

function numberToThaiText($num)
{
    $num = (int)$num;
    if ($num === 0) return 'ศูนย์';

    $thaiNumbers = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];

    $text = '';
    $millions = (int)($num / 1000000);
    if ($millions > 0) {
        $text .= convertThaiGroup($millions) . 'ล้าน';
    }

    $thousands = (int)(($num % 1000000) / 1000);
    if ($thousands > 0) {
        $text .= convertThaiGroup($thousands) . 'พัน';
    }

    $hundreds = $num % 1000;
    if ($hundreds > 0) {
        $text .= convertThaiGroup($hundreds);
    }

    return trim($text) . 'บาท';
}

function convertThaiGroup($num)
{
    $ones = ['', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า'];
    $tens = ['', 'สิบ', 'ยี่สิบ', 'สามสิบ', 'สี่สิบ', 'ห้าสิบ', 'หกสิบ', 'เจ็ดสิบ', 'แปดสิบ', 'เก้าสิบ'];

    $text = '';
    $hundreds = (int)($num / 100);
    if ($hundreds > 0) {
        $text .= $ones[$hundreds] . 'ร้อย';
    }

    $remainder = $num % 100;
    if ($remainder >= 10) {
        $tens_digit = (int)($remainder / 10);
        $ones_digit = $remainder % 10;
        $text .= $tens[$tens_digit];
        if ($ones_digit > 0) {
            $text .= ($ones_digit === 1 && $tens_digit === 2) ? 'เอ็ด' : $ones[$ones_digit];
        }
    } else if ($remainder > 0) {
        $text .= $ones[$remainder];
    }

    return $text;
}
?>