@ -0,0 +1,460 @@
<?php
/**
 * GET CERTIFICATE TEMPLATE API
 * ================================================================
 * File: api/get_certificate_template.php
 * Purpose: Fetch certificate template with employee data
 * Features:
 *   - Get template by request_id
 *   - Get template by cert_type_id
 *   - Return full employee data auto-fill
 *   - Multi-language support (TH/EN/MY)
 *   - JSON response format
 * ================================================================
 */

header('Content-Type: application/json');
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Include configuration
require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// ========== AUTHENTICATION & VALIDATION ==========
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized - Please login']);
    exit();
}

// ========== HELPER FUNCTIONS ==========

/**
 * Get Certificate Template by Request ID
 * 
 * @param mysqli $conn Database connection
 * @param int $request_id Certificate request ID
 * @param string $lang Language code (th/en/my)
 * @return array Template data with employee info
 */
function getCertificateTemplateByRequest($conn, $request_id, $lang = 'th') {
    try {
        // Validate request_id
        if (!is_numeric($request_id) || $request_id <= 0) {
            throw new Exception('Invalid request ID');
        }

        // Prepare SQL query to fetch certificate request with employee data
        $sql = "
            SELECT 
                cr.request_id,
                cr.employee_id,
                cr.cert_type_id,
                cr.certificate_no,
                cr.purpose,
                cr.status,
                cr.base_salary,
                cr.created_at,
                cr.updated_at,
                
                -- Employee Information
                e.full_name_th,
                e.full_name_en,
                e.prefix_th,
                e.prefix_en,
                e.date_of_hire,
                e.phone_no as employee_phone,
                e.address_village,
                e.address_subdistrict,
                e.address_district,
                e.address_province,
                
                -- Position & Department
                COALESCE(p.position_name_th, '') as position_name_th,
                COALESCE(p.position_name_en, '') as position_name_en,
                COALESCE(p.position_name_my, '') as position_name_my,
                
                -- Division
                COALESCE(d.division_name_th, '') as division_name_th,
                COALESCE(d.division_name_en, '') as division_name_en,
                COALESCE(d.division_name_my, '') as division_name_my,
                
                -- Department
                COALESCE(dep.department_name_th, '') as department_name_th,
                COALESCE(dep.department_name_en, '') as department_name_en,
                COALESCE(dep.department_name_my, '') as department_name_my,
                
                -- Section
                COALESCE(sec.section_name_th, '') as section_name_th,
                COALESCE(sec.section_name_en, '') as section_name_en,
                COALESCE(sec.section_name_my, '') as section_name_my,
                
                -- Hiring Type
                COALESCE(ht.type_name_th, '') as hiring_type_th,
                COALESCE(ht.type_name_en, '') as hiring_type_en,
                COALESCE(ht.type_name_my, '') as hiring_type_my,
                
                -- Certificate Type Template
                COALESCE(ct.type_name_th, '') as cert_type_name_th,
                COALESCE(ct.type_name_en, '') as cert_type_name_en,
                COALESCE(ct.type_name_my, '') as cert_type_name_my,
                ct.template_content,
                
                -- Company Information
                comp.company_name_th,
                comp.company_name_en,
                comp.company_name_my,
                comp.address as company_address,
                comp.phone as company_phone,
                comp.fax as company_fax,
                comp.representative_name,
                comp.company_logo_path
            
            FROM certificate_requests cr
            LEFT JOIN employees e ON cr.employee_id = e.employee_id
            LEFT JOIN position_master p ON e.position_id = p.position_id
            LEFT JOIN division_master d ON e.division_id = d.division_id
            LEFT JOIN department_master dep ON e.department_id = dep.department_id
            LEFT JOIN section_master sec ON e.section_id = sec.section_id
            LEFT JOIN hiring_type_master ht ON e.hiring_type_id = ht.hiring_type_id
            LEFT JOIN certificate_types ct ON cr.cert_type_id = ct.cert_type_id
            LEFT JOIN company_info comp ON comp.company_id = 1
            
            WHERE cr.request_id = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }

        $stmt->bind_param('i', $request_id);
        if (!$stmt->execute()) {
            throw new Exception('Database execute error: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception('Certificate request not found');
        }

        $data = $result->fetch_assoc();
        $stmt->close();

        // Process and format data
        $processedData = processTemplateData($data, $lang);

        return [
            'success' => true,
            'request_id' => $data['request_id'],
            'employee_id' => $data['employee_id'],
            'cert_type_id' => $data['cert_type_id'],
            'certificate_no' => $data['certificate_no'],
            'status' => $data['status'],
            'employee' => [
                'id' => $data['employee_id'],
                'name' => getDisplayName($data, $lang),
                'position' => getFieldByLang($data, 'position_name', $lang),
                'division' => getFieldByLang($data, 'division_name', $lang),
                'department' => getFieldByLang($data, 'department_name', $lang),
                'section' => getFieldByLang($data, 'section_name', $lang),
                'hiring_type' => getFieldByLang($data, 'hiring_type', $lang),
                'date_of_hire' => $data['date_of_hire'],
                'phone' => $data['employee_phone'],
                'address' => [
                    'village' => $data['address_village'],
                    'subdistrict' => $data['address_subdistrict'],
                    'district' => $data['address_district'],
                    'province' => $data['address_province']
                ]
            ],
            'certificate' => [
                'type_name' => getFieldByLang($data, 'cert_type_name', $lang),
                'template' => $data['template_content'],
                'base_salary' => $data['base_salary'],
                'certificate_no' => $data['certificate_no'],
                'purpose' => $data['purpose']
            ],
            'company' => [
                'name' => getFieldByLang($data, 'company_name', $lang),
                'address' => $data['company_address'],
                'phone' => $data['company_phone'],
                'fax' => $data['company_fax'],
                'representative' => $data['representative_name'],
                'logo_path' => $data['company_logo_path']
            ],
            'generated_content' => renderTemplate(
                $data['template_content'],
                $data,
                $lang
            )
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get Certificate Template by Type ID
 * For creating new certificate request
 * 
 * @param mysqli $conn Database connection
 * @param int $cert_type_id Certificate type ID
 * @param string $lang Language code
 * @return array Template data
 */
function getCertificateTemplateByType($conn, $cert_type_id, $lang = 'th') {
    try {
        if (!is_numeric($cert_type_id) || $cert_type_id <= 0) {
            throw new Exception('Invalid certificate type ID');
        }

        $sql = "
            SELECT 
                ct.cert_type_id,
                ct.type_name_th,
                ct.type_name_en,
                ct.type_name_my,
                ct.template_content,
                ct.is_active,
                comp.company_name_th,
                comp.company_name_en,
                comp.company_name_my,
                comp.company_logo_path,
                comp.representative_name
            
            FROM certificate_types ct
            LEFT JOIN company_info comp ON comp.company_id = 1
            
            WHERE ct.cert_type_id = ? AND ct.is_active = 1
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database prepare error: ' . $conn->error);
        }

        $stmt->bind_param('i', $cert_type_id);
        if (!$stmt->execute()) {
            throw new Exception('Database execute error: ' . $stmt->error);
        }

        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            throw new Exception('Certificate type not found or inactive');
        }

        $data = $result->fetch_assoc();
        $stmt->close();

        return [
            'success' => true,
            'cert_type_id' => $data['cert_type_id'],
            'type_name' => getFieldByLang($data, 'type_name', $lang),
            'template_content' => $data['template_content'],
            'company' => [
                'name' => getFieldByLang($data, 'company_name', $lang),
                'logo_path' => $data['company_logo_path'],
                'representative' => $data['representative_name']
            ],
            'template_variables' => getTemplateVariables($data['template_content'])
        ];

    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Helper: Get field by language
 * 
 * @param array $data Data array
 * @param string $fieldPrefix Field name prefix (without _th/_en/_my)
 * @param string $lang Language code
 * @return string Field value
 */
function getFieldByLang($data, $fieldPrefix, $lang = 'th') {
    $langMap = ['th' => '_th', 'en' => '_en', 'my' => '_my'];
    $suffix = $langMap[$lang] ?? '_th';
    
    $key = $fieldPrefix . $suffix;
    if (isset($data[$key]) && !empty($data[$key])) {
        return $data[$key];
    }
    
    // Fallback to Thai
    $fallback_key = $fieldPrefix . '_th';
    return $data[$fallback_key] ?? '';
}

/**
 * Helper: Get display name by language
 * 
 * @param array $data Employee data
 * @param string $lang Language code
 * @return string Display name
 */
function getDisplayName($data, $lang = 'th') {
    if ($lang === 'en') {
        $prefix = $data['prefix_en'] ?? '';
        $name = $data['full_name_en'] ?? '';
    } else {
        $prefix = $data['prefix_th'] ?? '';
        $name = $data['full_name_th'] ?? '';
    }
    
    return trim($prefix . ' ' . $name);
}

/**
 * Process template data for rendering
 * 
 * @param array $data Raw data
 * @param string $lang Language code
 * @return array Processed data
 */
function processTemplateData($data, $lang = 'th') {
    return [
        'employee_name' => getDisplayName($data, $lang),
        'employee_id' => $data['employee_id'],
        'position' => getFieldByLang($data, 'position_name', $lang),
        'division' => getFieldByLang($data, 'division_name', $lang),
        'date_of_hire' => formatDate($data['date_of_hire'], $lang),
        'base_salary' => formatCurrency($data['base_salary'], $lang),
        'hiring_type' => getFieldByLang($data, 'hiring_type', $lang)
    ];
}

/**
 * Render template with data substitution
 * 
 * @param string $template Template string with {variable} placeholders
 * @param array $data Data to substitute
 * @param string $lang Language code
 * @return string Rendered content
 */
function renderTemplate($template, $data, $lang = 'th') {
    $processed = processTemplateData($data, $lang);
    
    $content = $template;
    foreach ($processed as $key => $value) {
        $content = str_replace('{' . $key . '}', $value, $content);
    }
    
    return $content;
}

/**
 * Extract template variables from template string
 * 
 * @param string $template Template string
 * @return array List of required variables
 */
function getTemplateVariables($template) {
    preg_match_all('/\{(\w+)\}/', $template, $matches);
    return array_unique($matches[1] ?? []);
}

/**
 * Format date by language
 * 
 * @param string $date Date string (YYYY-MM-DD)
 * @param string $lang Language code
 * @return string Formatted date
 */
function formatDate($date, $lang = 'th') {
    if (empty($date)) return '';
    
    $timestamp = strtotime($date);
    
    if ($lang === 'th') {
        // Thai format: DD/MM/YYYY (Buddhist Era)
        $day = date('d', $timestamp);
        $month = date('m', $timestamp);
        $year = date('Y', $timestamp) + 543; // Thai Buddhist Era
        return sprintf('%s/%s/%d', $day, $month, $year);
    } elseif ($lang === 'my') {
        // Myanmar format: DD-MM-YYYY
        return date('d-m-Y', $timestamp);
    } else {
        // English format: DD/MM/YYYY
        return date('d/m/Y', $timestamp);
    }
}

/**
 * Format currency by language
 * 
 * @param float $amount Amount
 * @param string $lang Language code
 * @return string Formatted currency
 */
function formatCurrency($amount, $lang = 'th') {
    if (empty($amount) || !is_numeric($amount)) return '0.00';
    
    $amount = (float)$amount;
    
    if ($lang === 'th') {
        return number_format($amount, 2, '.', ',') . ' บาท';
    } elseif ($lang === 'my') {
        return number_format($amount, 2, '.', ',') . ' ကျပ်';
    } else {
        return number_format($amount, 2, '.', ',') . ' THB';
    }
}

// ========== MAIN HANDLER ==========

try {
    $conn = getDbConnection();
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Get request parameters
    $request_id = isset($_GET['request_id']) ? intval($_GET['request_id']) : 0;
    $cert_type_id = isset($_GET['cert_type_id']) ? intval($_GET['cert_type_id']) : 0;
    $lang = $_GET['lang'] ?? 'th';

    // Validate language
    if (!in_array($lang, ['th', 'en', 'my'])) {
        $lang = 'th';
    }

    // Determine which template to fetch
    if ($request_id > 0) {
        // Get template by certificate request ID
        $result = getCertificateTemplateByRequest($conn, $request_id, $lang);
    } elseif ($cert_type_id > 0) {
        // Get template by certificate type ID
        $result = getCertificateTemplateByType($conn, $cert_type_id, $lang);
    } else {
        throw new Exception('Either request_id or cert_type_id must be provided');
    }

    $conn->close();

    // Return JSON response
    http_response_code($result['success'] ? 200 : 404);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Server error: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>