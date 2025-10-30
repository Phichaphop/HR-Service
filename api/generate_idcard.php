<?php
/**
 * API: Generate Employee ID Card
 * File: /api/generate_idcard.php
 * 
 * ✅ ทำงานกับทั้ง URL parameters และ POST data
 * ✅ เข้ากันได้กับ existing request_management.php
 * ✅ สนับสนุน 3 ภาษา: TH, EN, MY
 * ✅ มี QR code เสมอ
 */

header('Content-Type: text/html; charset=utf-8');

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Require authentication
AuthController::requireAuth();

// Get parameters from URL or POST
$request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : (isset($_POST['request_id']) ? intval($_POST['request_id']) : 0);
$employee_id = isset($_GET['employee_id']) ? trim($_GET['employee_id']) : (isset($_POST['employee_id']) ? trim($_POST['employee_id']) : '');
$table = isset($_GET['table']) ? trim($_GET['table']) : 'id_card_requests';
$lang = isset($_GET['lang']) ? trim($_GET['lang']) : ($_SESSION['language'] ?? 'th');

// Validate language
if (!in_array($lang, ['th', 'en', 'my'])) {
    $lang = 'th';
}

if (!$request_id || !$employee_id) {
    die('❌ Invalid parameters: request_id and employee_id are required');
}

$conn = getDbConnection();
if (!$conn) {
    die('❌ Database connection failed');
}

try {
    // ✅ Fetch employee data and request info
    $sql = "
        SELECT 
            e.employee_id,
            e.prefix_th,
            e.prefix_en,
            e.full_name_th,
            e.full_name_en,
            pm.position_name_th,
            pm.position_name_en,
            dm.division_name_th,
            dm.division_name_en,
            e.phone_no,
            e.profile_pic_path,
            ir.request_id,
            ir.employee_id as req_emp_id,
            ir.status,
            ir.created_at,
            c.company_name,
            c.company_logo_path
        FROM id_card_requests ir
        JOIN employees e ON ir.employee_id = e.employee_id
        LEFT JOIN position_master pm ON e.position_id = pm.position_id
        LEFT JOIN division_master dm ON e.division_id = dm.division_id
        LEFT JOIN company_info c ON c.company_id = 1
        WHERE ir.request_id = ? AND e.employee_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("is", $request_id, $employee_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        die('❌ ID Card request not found');
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    // Get display name by language
    $prefix = ($lang === 'en') ? ($row['prefix_en'] ?? '') : ($row['prefix_th'] ?? '');
    $name = ($lang === 'en') ? ($row['full_name_en'] ?? '') : ($row['full_name_th'] ?? '');
    $position = ($lang === 'en') ? ($row['position_name_en'] ?? '') : ($row['position_name_th'] ?? '');
    $division = ($lang === 'en') ? ($row['division_name_en'] ?? '') : ($row['division_name_th'] ?? '');
    
    $display_name = trim($prefix . ' ' . $name);

    // Generate QR code data (employee ID)
    $qr_data = $row['employee_id'];

    // Get base path for images
    $base_path = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2);

    // Build profile pic URL
    $profile_pic = '';
    if (!empty($row['profile_pic_path'])) {
        $profile_pic = $base_path . '/' . ltrim($row['profile_pic_path'], '/');
    }

    // Build logo URL
    $logo_url = '';
    if (!empty($row['company_logo_path'])) {
        $logo_url = $base_path . '/' . ltrim($row['company_logo_path'], '/');
    }

    $conn->close();

    // Generate QR Code using free service
    $qr_code_url = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($qr_data);

} catch (Exception $e) {
    $conn->close();
    die('❌ Error: ' . htmlspecialchars($e->getMessage()));
}

// HTML Output
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee ID Card - <?php echo htmlspecialchars($row['employee_id']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }

        .print-controls {
            display: block;
            text-align: center;
            margin-bottom: 20px;
            gap: 10px;
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
            transition: background 0.3s ease;
        }

        .print-btn:hover {
            background: #1a3a5c;
        }

        .print-btn.close-btn {
            background: #dc2626;
        }

        .print-btn.close-btn:hover {
            background: #991b1b;
        }

        /* ID Card Container */
        .card-container {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .id-card {
            width: 540px;
            height: 340px;
            background: linear-gradient(135deg, #ffffff 0%, #f9f9f9 100%);
            border: 1px solid #ddd;
            border-radius: 12px;
            display: flex;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
            position: relative;
        }

        /* Left Section - Company Info & Logo */
        .card-left {
            width: 25%;
            background: linear-gradient(135deg, #2c5aa0 0%, #1a3a5c 100%);
            color: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-between;
            padding: 15px 10px;
            text-align: center;
        }

        .company-logo {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .company-logo img {
            max-width: 90%;
            max-height: 90%;
            object-fit: contain;
        }

        .company-name {
            font-size: 12px;
            font-weight: bold;
            line-height: 1.2;
            margin-top: 5px;
        }

        .card-issue-date {
            font-size: 9px;
            opacity: 0.8;
            margin-top: auto;
        }

        /* Middle Section - Employee Photo & QR Code */
        .card-middle {
            width: 25%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
            background: #fafafa;
        }

        .employee-photo {
            width: 80px;
            height: 100px;
            background: white;
            border: 2px solid #ddd;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .employee-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .placeholder-photo {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #e5e7eb, #d1d5db);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #9ca3af;
        }

        .qr-code {
            width: 60px;
            height: 60px;
            border: 2px solid #2c5aa0;
            padding: 2px;
            border-radius: 4px;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .qr-code img {
            width: 100%;
            height: 100%;
        }

        /* Right Section - Employee Info */
        .card-right {
            width: 50%;
            padding: 20px 15px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .employee-info-row {
            margin-bottom: 10px;
            display: flex;
            align-items: flex-start;
        }

        .info-label {
            font-size: 10px;
            font-weight: bold;
            color: #666;
            width: 35%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 13px;
            font-weight: 600;
            color: #333;
            width: 65%;
            word-wrap: break-word;
        }

        .employee-name {
            font-size: 16px !important;
            font-weight: bold !important;
            color: #2c5aa0 !important;
            margin-bottom: 5px;
        }

        .employee-id {
            font-size: 11px;
            color: #666;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }

        .footer-line {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
            font-size: 9px;
            color: #999;
            text-align: right;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .print-controls {
                display: none !important;
            }

            .card-container {
                margin: 0;
                padding: 0;
            }

            .id-card {
                margin: 0;
                box-shadow: none;
                page-break-after: avoid;
            }

            @page {
                size: landscape;
                margin: 0.5cm;
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .id-card {
                width: 100%;
                max-width: 540px;
                height: auto;
                aspect-ratio: 540/340;
            }

            .print-btn {
                padding: 10px 16px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="print-controls">
        <button class="print-btn" onclick="window.print()">
            🖨️ <?php echo ($lang === 'th') ? 'พิมพ์บัตร' : (($lang === 'my') ? 'ပုံနှိပ်ကဒ်' : 'Print Card'); ?>
        </button>
        <button class="print-btn close-btn" onclick="window.close()">
            ✕ <?php echo ($lang === 'th') ? 'ปิด' : (($lang === 'my') ? 'ပိတ်ရန်' : 'Close'); ?>
        </button>
    </div>

    <!-- ID Card -->
    <div class="card-container">
        <div class="id-card">
            <!-- Left Section: Company Info & Logo -->
            <div class="card-left">
                <div>
                    <div class="company-logo">
                        <?php if (!empty($logo_url)): ?>
                            <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Company Logo" onerror="this.style.display='none'">
                        <?php endif; ?>
                    </div>
                    <div class="company-name">
                        <?php echo htmlspecialchars($row['company_name'] ?? 'บริษัท'); ?>
                    </div>
                </div>
                <div class="card-issue-date">
                    <?php 
                        $date = new DateTime($row['created_at']);
                        if ($lang === 'th') {
                            $day = $date->format('d');
                            $month = $date->format('m');
                            $year = $date->format('Y') + 543;
                            echo "$day/$month/$year";
                        } elseif ($lang === 'my') {
                            echo $date->format('d-m-Y');
                        } else {
                            echo $date->format('d/m/Y');
                        }
                    ?>
                </div>
            </div>

            <!-- Middle Section: Photo & QR -->
            <div class="card-middle">
                <div class="employee-photo">
                    <?php if (!empty($profile_pic)): ?>
                        <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Employee Photo" onerror="this.parentElement.innerHTML='<div class=\"placeholder-photo\">👤</div>'">
                    <?php else: ?>
                        <div class="placeholder-photo">👤</div>
                    <?php endif; ?>
                </div>
                <div class="qr-code">
                    <img src="<?php echo htmlspecialchars($qr_code_url); ?>" alt="QR Code">
                </div>
            </div>

            <!-- Right Section: Employee Info -->
            <div class="card-right">
                <div>
                    <div class="employee-info-row">
                        <div class="info-value employee-name">
                            <?php echo htmlspecialchars($display_name); ?>
                        </div>
                    </div>
                    <div class="employee-id">
                        ID: <?php echo htmlspecialchars($row['employee_id']); ?>
                    </div>

                    <div class="employee-info-row" style="margin-top: 15px;">
                        <div class="info-label">
                            <?php echo ($lang === 'th') ? 'ตำแหน่ง' : (($lang === 'my') ? 'ရာထူး' : 'Position'); ?>
                        </div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($position ?? '-'); ?>
                        </div>
                    </div>

                    <div class="employee-info-row">
                        <div class="info-label">
                            <?php echo ($lang === 'th') ? 'แผนก' : (($lang === 'my') ? 'বিভাग' : 'Department'); ?>
                        </div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($division ?? '-'); ?>
                        </div>
                    </div>

                    <div class="employee-info-row">
                        <div class="info-label">
                            <?php echo ($lang === 'th') ? 'เบอร์โทร' : (($lang === 'my') ? 'ဖုန်း' : 'Phone'); ?>
                        </div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($row['phone_no'] ?? '-'); ?>
                        </div>
                    </div>
                </div>

                <div class="footer-line">
                    <?php echo ($lang === 'th') ? 'บัตรพนักงาน' : (($lang === 'my') ? 'ဝန်ထမ်းကဒ်' : 'Employee ID Card'); ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>