<?php
/**
 * ========================================================
 * COMPLAINT CATEGORIES API
 * File: /api/complaint_categories.php
 * ========================================================
 * UPDATED: Removed icon_class field completely
 * Features:
 * ✅ CRUD operations for complaint categories
 * ✅ Multi-language support (Thai, English, Myanmar)
 * ✅ JSON response format
 * ✅ Error handling
 * ========================================================
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../controllers/AuthController.php';

// Set JSON header
header('Content-Type: application/json; charset=utf-8');

// Ensure authenticated
AuthController::requireAuth();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Database connection
$conn = getDbConnection();

if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

/**
 * GET: Fetch all categories or single category
 */
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        // Get single category
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("
            SELECT 
                category_id,
                category_name_th,
                category_name_en,
                category_name_my,
                description_th,
                description_en,
                description_my,
                is_active,
                created_at,
                updated_at
            FROM complaint_category_master 
            WHERE category_id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo json_encode([
                'success' => true,
                'data' => $row
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Category not found'
            ]);
        }
        $stmt->close();
    } else {
        // Get all categories
        $query = "
            SELECT 
                category_id,
                category_name_th,
                category_name_en,
                category_name_my,
                description_th,
                description_en,
                description_my,
                is_active,
                created_at,
                updated_at
            FROM complaint_category_master 
            ORDER BY category_name_th ASC
        ";
        
        if (isset($_GET['active_only']) && $_GET['active_only'] == '1') {
            $query = "
                SELECT 
                    category_id,
                    category_name_th,
                    category_name_en,
                    category_name_my,
                    description_th,
                    description_en,
                    description_my,
                    is_active,
                    created_at,
                    updated_at
                FROM complaint_category_master 
                WHERE is_active = 1
                ORDER BY category_name_th ASC
            ";
        }
        
        $result = $conn->query($query);
        $categories = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
            echo json_encode([
                'success' => true,
                'data' => $categories,
                'count' => count($categories)
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error fetching categories'
            ]);
        }
    }
}

/**
 * POST: Create new category
 */
elseif ($method === 'POST') {
    // Admin only
    AuthController::requireRole(['admin']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($data['category_name_th']) || 
        empty($data['category_name_en']) || 
        empty($data['category_name_my'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Required fields missing'
        ]);
        exit;
    }
    
    $stmt = $conn->prepare("
        INSERT INTO complaint_category_master 
        (category_name_th, category_name_en, category_name_my, 
         description_th, description_en, description_my, is_active) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;
    
    $stmt->bind_param(
        "ssssssi",
        $data['category_name_th'],
        $data['category_name_en'],
        $data['category_name_my'],
        $data['description_th'],
        $data['description_en'],
        $data['description_my'],
        $is_active
    );
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Category created successfully',
            'id' => $conn->insert_id
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error creating category: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

/**
 * PUT: Update existing category
 */
elseif ($method === 'PUT') {
    // Admin only
    AuthController::requireRole(['admin']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (empty($data['category_id']) || 
        empty($data['category_name_th']) || 
        empty($data['category_name_en']) || 
        empty($data['category_name_my'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Required fields missing'
        ]);
        exit;
    }
    
    $stmt = $conn->prepare("
        UPDATE complaint_category_master 
        SET 
            category_name_th = ?,
            category_name_en = ?,
            category_name_my = ?,
            description_th = ?,
            description_en = ?,
            description_my = ?,
            is_active = ?
        WHERE category_id = ?
    ");
    
    $is_active = isset($data['is_active']) ? intval($data['is_active']) : 1;
    
    $stmt->bind_param(
        "ssssssii",
        $data['category_name_th'],
        $data['category_name_en'],
        $data['category_name_my'],
        $data['description_th'],
        $data['description_en'],
        $data['description_my'],
        $is_active,
        $data['category_id']
    );
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No changes made or category not found'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating category: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

/**
 * DELETE: Delete category
 */
elseif ($method === 'DELETE') {
    // Admin only
    AuthController::requireRole(['admin']);
    
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data['category_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Category ID required'
        ]);
        exit;
    }
    
    // Check if category is used in complaints
    $check_stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM complaints 
        WHERE category_id = ?
    ");
    $check_stmt->bind_param("i", $data['category_id']);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();
    
    if ($check_row['count'] > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot delete: Category is used in ' . $check_row['count'] . ' complaint(s)'
        ]);
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();
    
    // Delete category
    $stmt = $conn->prepare("
        DELETE FROM complaint_category_master 
        WHERE category_id = ?
    ");
    $stmt->bind_param("i", $data['category_id']);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Category not found'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting category: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
}

/**
 * Invalid method
 */
else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}

$conn->close();