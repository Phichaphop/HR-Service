<?php

/**
 * Generate Employee ID Card
 * File: /api/generate_id_card.php
 * 
 * PRINT SIZE: 85.6mm × 53.98mm (Thai ID Card Size)
 * 
 * LAYOUT:
 * ✅ Header: Company Logo + Name
 * ✅ Body: Left (Photo + Name + Info) | Right (ID + QR)
 * ✅ Footer: Issue Date + Signature Line
 */

header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
AuthController::requireAuth();

$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : (isset($_POST['request_id']) ? intval($_POST['request_id']) : 0);
$employee_id = isset($_GET['employee_id']) ? trim($_GET['employee_id']) : (isset($_POST['employee_id']) ? trim($_POST['employee_id']) : '');
$lang = isset($_GET['lang']) ? trim($_GET['lang']) : ($_SESSION['language'] ?? 'th');

if (!in_array($lang, ['th', 'en', 'my'])) {
    $lang = 'th';
}

if (!$request_id || !$employee_id) {
    die('<div style="padding: 20px; background: #fee2e2; color: #991b1b; border-radius: 8px;">❌ Missing parameters</div>');
}

$conn = getDbConnection();
if (!$conn) {
    die('<div style="padding: 20px; background: #fee2e2; color: #991b1b;">❌ Database connection failed</div>');
}

try {
    $sql = "
        SELECT 
            icr.request_id,
            icr.created_at as req_created_at,
            e.employee_id,
            e.full_name_th,
            e.full_name_en,
            e.phone_no,
            e.profile_pic_path,
            e.date_of_hire,
            COALESCE(pfm.prefix_th, '') as prefix_th,
            COALESCE(pfm.prefix_en, '') as prefix_en,
            COALESCE(pm.position_name_th, '') as position_name_th,
            COALESCE(pm.position_name_en, '') as position_name_en,
            COALESCE(pm.position_name_my, '') as position_name_my,
            COALESCE(plm.level_name_th, '') as level_name_th,
            COALESCE(plm.level_name_en, '') as level_name_en,
            COALESCE(plm.level_name_my, '') as level_name_my,
            COALESCE(dm.division_name_th, '') as division_name_th,
            COALESCE(dm.division_name_en, '') as division_name_en,
            COALESCE(dm.division_name_my, '') as division_name_my,
            COALESCE(sm.section_name_th, '') as section_name_th,
            COALESCE(sm.section_name_en, '') as section_name_en,
            COALESCE(sm.section_name_my, '') as section_name_my,
            COALESCE(c.company_name_th, '') as company_name_th,
            COALESCE(c.company_name_en, '') as company_name_en,
            c.company_logo_path
        FROM id_card_requests icr
        LEFT JOIN employees e ON icr.employee_id = e.employee_id
        LEFT JOIN prefix_master pfm ON e.prefix_id = pfm.prefix_id
        LEFT JOIN position_master pm ON e.position_id = pm.position_id
        LEFT JOIN position_level_master plm ON e.position_level_id = plm.level_id
        LEFT JOIN division_master dm ON e.division_id = dm.division_id
        LEFT JOIN section_master sm ON e.section_id = sm.section_id
        LEFT JOIN company_info c ON c.company_id = 1
        WHERE icr.request_id = ? AND e.employee_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $request_id, $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die('<div>❌ ID Card request not found</div>');
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    $prefix = ($lang === 'en') ? ($row['prefix_en'] ?? '') : ($row['prefix_th'] ?? '');
    $name = ($lang === 'en') ? ($row['full_name_en'] ?? '') : ($row['full_name_th'] ?? '');
    $position = ($lang === 'en') ? ($row['position_name_en'] ?? '') : (($lang === 'my') ? ($row['position_name_my'] ?? '') : ($row['position_name_th'] ?? ''));
    $level = ($lang === 'en') ? ($row['level_name_en'] ?? '') : (($lang === 'my') ? ($row['level_name_my'] ?? '') : ($row['level_name_th'] ?? ''));
    $division = ($lang === 'en') ? ($row['division_name_en'] ?? '') : (($lang === 'my') ? ($row['division_name_my'] ?? '') : ($row['division_name_th'] ?? ''));
    $section = ($lang === 'en') ? ($row['section_name_en'] ?? '') : (($lang === 'my') ? ($row['section_name_my'] ?? '') : ($row['section_name_th'] ?? ''));
    $company_name = ($lang === 'en') ? ($row['company_name_en'] ?? '') : ($row['company_name_th'] ?? '');

    $display_name = trim($prefix . ' ' . $name);
    $qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($row['employee_id']);

    $profile_pic = '';
    if (!empty($row['profile_pic_path'])) {
        $profile_pic = (strpos($row['profile_pic_path'], 'http') === 0)
            ? $row['profile_pic_path']
            : BASE_URL . '/' . ltrim($row['profile_pic_path'], '/');
    }

    $logo_url = '';
    if (!empty($row['company_logo_path'])) {
        $logo_url = (strpos($row['company_logo_path'], 'http') === 0)
            ? $row['company_logo_path']
            : BASE_URL . '/' . ltrim($row['company_logo_path'], '/');
    }

    $issue_date_obj = new DateTime($row['req_created_at']);
    if ($lang === 'th') {
        $day = $issue_date_obj->format('d');
        $month = $issue_date_obj->format('m');
        $year = (int)$issue_date_obj->format('Y') + 543;
        $issue_date_display = "$day/$month/$year";
    } else {
        $issue_date_display = $issue_date_obj->format('d/m/Y');
    }

    $hire_date_display = '';
    if (!empty($row['date_of_hire'])) {
        $hire_date_obj = new DateTime($row['date_of_hire']);
        if ($lang === 'th') {
            $day = $hire_date_obj->format('d');
            $month = $hire_date_obj->format('m');
            $year = (int)$hire_date_obj->format('Y') + 543;
            $hire_date_display = "$day/$month/$year";
        } else {
            $hire_date_display = $hire_date_obj->format('d/m/Y');
        }
    }

    $conn->close();

    $texts = [
        'th' => ['position' => 'ตำแหน่ง', 'level' => 'ระดับ', 'division' => 'แผนก', 'section' => 'กอง', 'phone' => 'เบอร์โทร', 'hire_date' => 'วันเริ่มงาน', 'print' => 'พิมพ์บัตร', 'close' => 'ปิด', 'issue_date' => 'วันออกบัตร', 'signature' => 'ลายเซ็นผู้จัดการแผนกบุคคล', 'valid' => 'บัตรประจำตัวพนักงาน'],
        'en' => ['position' => 'Position', 'level' => 'Level', 'division' => 'Department', 'section' => 'Section', 'phone' => 'Phone', 'hire_date' => 'Start Date', 'print' => 'Print Card', 'close' => 'Close', 'issue_date' => 'Issue Date', 'signature' => 'HR Manager Signature', 'valid' => 'Employee ID Card'],
        'my' => ['position' => 'ရာထူး', 'level' => 'အဆင့်', 'division' => 'ဌာန', 'section' => 'ဘာဂ်', 'phone' => 'ဖုန်း', 'hire_date' => 'စတင်ရက်', 'print' => 'ပုံနှိပ်ကဒ်', 'close' => 'ပိတ်ရန်', 'issue_date' => 'ထုတ်ပြန်ချက်', 'signature' => 'HR Manager လက်မှတ်', 'valid' => 'အလုပ်သမား ID ကဒ်']
    ];

    $t = $texts[$lang] ?? $texts['en'];
} catch (Exception $e) {
    $conn->close();
    die('<div>❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>');
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee ID Card</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f0f0;
            min-height: 100vh;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .print-controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
        }

        .btn-print {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-close {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .card-wrapper {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .id-card {
            width: 540px;
            height: 340px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
        }

        .card-header {
            background: #000000;
            color: white;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }

        .company-logo {
            width: 48px;
            height: 48px;
            background: #000000;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .company-logo img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            line-height: 1.3;
            text-transform: uppercase;
        }

        .company-subtitle {
            font-size: 8px;
            opacity: 0.8;
            margin-top: 2px;
            text-transform: uppercase;
        }

        .card-body {
            display: flex;
            grid-template-columns: 0.9fr 1.2fr;
            gap: 10px;
            flex: 1;
            padding: 10px;
            min-height: 0;
        }

        .card-left {
            display: flex;
            flex-direction: row;
            gap: 8px;
            border-right: 1px solid #e5e7eb;
            padding-right: 10px;
            min-height: 0;
            overflow-y: auto;
        }

        .employee-photo {
            width: 100px;
            height: 100px;
            background: white;
            border: 2px solid #3b82f6;
            border-radius: 3px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .employee-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder-photo {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #cbd5e1, #94a3b8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }

        .employee-name {
            font-size: 12px;
            font-weight: bold;
            color: #1e40af;
            line-height: 1.3;
        }

        .employee-info {
            display: flex;
            flex-direction: column;
            gap: 2px;
            flex: 1;
            min-height: 0;
        }

        .info-row {
            display: grid;
            grid-template-columns: 60px 1fr;
            gap: 6px;
            align-items: flex-start;
            font-size: 8px;
        }

        .info-label {
            font-size: 7px;
            font-weight: bold;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .info-value {
            font-size: 8px;
            font-weight: 500;
            color: #1f2937;
            word-break: break-word;
            line-height: 1.2;
        }

        .card-right {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 8px;
            align-items: center;
            background: #f9fafb;
            padding: 8px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }

        .employee-id-badge {
            font-size: 11px;
            font-weight: bold;
            color: #1e40af;
            background: white;
            padding: 6px 10px;
            border: 1px solid #3b82f6;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.5px;
            width: 100%;
            text-align: center;
        }

        .qr-code-wrapper {
            width: 100px;
            height: 100px;
            background: white;
            border: 2px solid #3b82f6;
            border-radius: 3px;
            padding: 2px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .qr-code-wrapper img {
            width: 100%;
            height: 100%;
        }

        .card-footer {
            border-top: 1px solid #e5e7eb;
        }

        .footer-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .footer-label {
            font-size: 7px;
            font-weight: bold;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .footer-date {
            font-size: 9px;
            font-weight: 600;
            color: #1f2937;
        }

        .signature-line {
            border-top: 1px solid #1f2937;
            width: 100%;
            height: 18px;
        }

        @media print {
            * {
                margin: 0;
                padding: 0;
            }

            html,
            body {
                margin: 0;
                padding: 0;
                background: white;
            }

            .print-controls {
                display: none !important;
            }

            .card-wrapper {
                margin: 0;
                padding: 0;
            }

            @page {
                size: 85.6mm 53.98mm landscape;
                margin: 0;
                padding: 0;
            }

            .id-card {
                width: 85.6mm;
                height: 53.98mm;
                margin: 0;
                padding: 0;
                box-shadow: none;
                border-radius: 0;
                page-break-after: avoid;
                page-break-inside: avoid;
            }

            .card-header {
                padding: 6px 8px;
            }

            .company-logo {
                width: 32px;
                height: 32px;
            }

            .company-name {
                font-size: 9px;
            }

            .company-subtitle {
                font-size: 6px;
            }

            .card-body {
                padding: 6px;
                gap: 6px;
            }

            .card-left {
                gap: 4px;
                padding-right: 6px;
            }

            .employee-photo {
                width: 60px;
                height: 72px;
            }

            .employee-name {
                font-size: 10px;
            }

            .info-row {
                grid-template-columns: 50px 1fr;
                gap: 4px;
            }

            .info-label {
                font-size: 6px;
            }

            .info-value {
                font-size: 6px;
            }

            .card-right {
                padding: 4px;
                gap: 4px;
            }

            .employee-id-badge {
                font-size: 8px;
                padding: 3px 6px;
            }

            .qr-code-wrapper {
                width: 70px;
                height: 70px;
            }

            .card-footer {
                padding: 6px 8px;
                gap: 8px;
            }

            .footer-label {
                font-size: 6px;
            }

            .footer-date {
                font-size: 7px;
            }

            .signature-line {
                height: 12px;
            }
        }

        @media (max-width: 640px) {
            .id-card {
                width: 95vw;
                height: auto;
                aspect-ratio: 540 / 340;
            }

            .card-header {
                padding: 10px 12px;
            }

            .company-logo {
                width: 40px;
                height: 40px;
            }

            .company-name {
                font-size: 11px;
            }

            .card-body {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
                padding: 8px;
            }

            .card-left {
                gap: 6px;
                padding-right: 8px;
            }

            .employee-photo {
                width: 85px;
                height: 85px;
            }

            .employee-name {
                font-size: 11px;
            }

            .info-row {
                grid-template-columns: 50px 1fr;
                gap: 4px;
            }

            .info-label {
                font-size: 6px;
            }

            .info-value {
                font-size: 7px;
            }

            .card-right {
                padding: 6px;
                gap: 6px;
            }

            .employee-id-badge {
                font-size: 9px;
                padding: 4px 8px;
            }

            .qr-code-wrapper {
                width: 85px;
                height: 85px;
            }

            .card-footer {
                padding: 8px 10px;
                gap: 12px;
            }

            .footer-label {
                font-size: 6px;
            }

            .footer-date {
                font-size: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="print-controls">
        <button class="btn btn-print" onclick="window.print()"><span>🖨️</span><span><?php echo $t['print']; ?></span></button>
        <button class="btn btn-close" onclick="window.close()"><span>✕</span><span><?php echo $t['close']; ?></span></button>
    </div>

    <div class="card-wrapper">
        <div class="id-card">
            <div class="card-header">
                <div class="company-logo">
                    <?php if (!empty($logo_url)): ?>
                        <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo" onerror="this.style.display='none'">
                    <?php endif; ?>
                </div>
                <div class="company-info">
                    <div class="company-name"><?php echo htmlspecialchars($company_name ?: 'COMPANY'); ?></div>
                    <div class="company-subtitle"><?php echo $t['valid']; ?></div>
                </div>
            </div>

            <div class="card-body">
                <div class="card-left">
                    <div class="employee-photo">
                        <?php if (!empty($profile_pic)): ?>
                            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Photo" onerror="this.parentElement.innerHTML='<div class=\" placeholder-photo\">
                    </div>
                <?php else: ?>
                    <div class="placeholder-photo">👤</div>
                <?php endif; ?>
                </div>

                <div class="card-right">
                    <div class="employee-name"><?php echo htmlspecialchars($display_name); ?></div>

                    <div class="employee-info">
                        <div class="info-row">
                            <div class="info-label"><?php echo $t['position']; ?></div>
                            <div class="info-value"><?php echo htmlspecialchars($position ?: '—'); ?></div>
                        </div>
                        <?php if (!empty($level)): ?>
                            <div class="info-row">
                                <div class="info-label"><?php echo $t['level']; ?></div>
                                <div class="info-value"><?php echo htmlspecialchars($level); ?></div>
                            </div>
                        <?php endif; ?>
                        <div class="info-row">
                            <div class="info-label"><?php echo $t['division']; ?></div>
                            <div class="info-value"><?php echo htmlspecialchars($division ?: '—'); ?></div>
                        </div>
                        <?php if (!empty($section)): ?>
                            <div class="info-row">
                                <div class="info-label"><?php echo $t['section']; ?></div>
                                <div class="info-value"><?php echo htmlspecialchars($section); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($row['phone_no'])): ?>
                            <div class="info-row">
                                <div class="info-label"><?php echo $t['phone']; ?></div>
                                <div class="info-value"><?php echo htmlspecialchars($row['phone_no']); ?></div>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($hire_date_display)): ?>
                            <div class="info-row">
                                <div class="info-label"><?php echo $t['hire_date']; ?></div>
                                <div class="info-value"><?php echo htmlspecialchars($hire_date_display); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-left">
                    <div class="qr-code-wrapper">
                        <img src="<?php echo htmlspecialchars($qr_code_url); ?>" alt="QR Code">
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="footer-item">
                    <div class="footer-label"><?php echo $t['issue_date']; ?></div>
                    <div class="footer-date"><?php echo htmlspecialchars($issue_date_display); ?></div>
                </div>

                <div class="footer-item">
                    <div class="footer-label"><?php echo $t['signature']; ?></div>
                    <div class="signature-line"></div>
                </div>
            </div>

        </div>
    </div>
</body>

</html>