<?php
/**
 * Employee Model
 * Handles employee data operations
 */

require_once __DIR__ . '/../config/db_config.php';

class Employee {
    
    /**
     * Get employee by ID
     * @param string $employee_id
     * @return array|null
     */
    public static function getById($employee_id) {
        $conn = getDbConnection();
        
        if (!$conn) {
            return null;
        }
        
        $stmt = $conn->prepare("SELECT * FROM employees WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $employee = $result->fetch_assoc();
        
        $stmt->close();
        $conn->close();
        
        return $employee;
    }
    
    /**
     * Get all employees with pagination
     * @param int $page
     * @param int $per_page
     * @param array $filters
     * @return array
     */
    public static function getAll($page = 1, $per_page = 20, $filters = []) {
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['data' => [], 'total' => 0];
        }
        
        $offset = ($page - 1) * $per_page;
        
        // Build WHERE clause based on filters
        $where_conditions = [];
        $params = [];
        $types = '';
        
        if (!empty($filters['status_id'])) {
            $where_conditions[] = "status_id = ?";
            $params[] = $filters['status_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['function_id'])) {
            $where_conditions[] = "function_id = ?";
            $params[] = $filters['function_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['division_id'])) {
            $where_conditions[] = "division_id = ?";
            $params[] = $filters['division_id'];
            $types .= 'i';
        }
        
        if (!empty($filters['search'])) {
            $where_conditions[] = "(employee_id LIKE ? OR full_name_th LIKE ? OR full_name_en LIKE ?)";
            $search_term = '%' . $filters['search'] . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
            $types .= 'sss';
        }
        
        $where_sql = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Get total count
        $count_sql = "SELECT COUNT(*) as total FROM employees $where_sql";
        
        if (!empty($params)) {
            $stmt = $conn->prepare($count_sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $total = $result->fetch_assoc()['total'];
            $stmt->close();
        } else {
            $result = $conn->query($count_sql);
            $total = $result->fetch_assoc()['total'];
        }
        
        // Get paginated data
        $data_sql = "SELECT * FROM employees $where_sql ORDER BY employee_id LIMIT ? OFFSET ?";
        $params[] = $per_page;
        $params[] = $offset;
        $types .= 'ii';
        
        $stmt = $conn->prepare($data_sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return [
            'data' => $employees,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ];
    }
    
/**
 * Create new employee
 * @param array $data Employee data
 * @return array Result
 */
public static function create($data) {
    $conn = getDbConnection();
    
    if (!$conn) {
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    // Check if employee_id already exists
    $stmt = $conn->prepare("SELECT employee_id FROM employees WHERE employee_id = ?");
    $stmt->bind_param("s", $data['employee_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Employee ID already exists'];
    }
    $stmt->close();
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT username FROM employees WHERE username = ?");
    $stmt->bind_param("s", $data['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Username already exists'];
    }
    $stmt->close();
    
    // Insert new employee
    $sql = "INSERT INTO employees (
        employee_id, prefix_id, full_name_th, full_name_en, sex_id, birthday,
        nationality_id, education_level_id, phone_no, address_village, address_subdistrict,
        address_district, address_province, function_id, division_id, department_id,
        section_id, operation_id, position_id, position_level_id, labour_cost_id,
        hiring_type_id, customer_zone_id, contribution_level_id, date_of_hire,
        status_id, username, password, role_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("siisississsiiiiiiiiiiiissi",
        $data['employee_id'],
        $data['prefix_id'],
        $data['full_name_th'],
        $data['full_name_en'],
        $data['sex_id'],
        $data['birthday'],
        $data['nationality_id'],
        $data['education_level_id'],
        $data['phone_no'],
        $data['address_village'],
        $data['address_subdistrict'],
        $data['address_district'],
        $data['address_province'],
        $data['function_id'],
        $data['division_id'],
        $data['department_id'],
        $data['section_id'],
        $data['operation_id'],
        $data['position_id'],
        $data['position_level_id'],
        $data['labour_cost_id'],
        $data['hiring_type_id'],
        $data['customer_zone_id'],
        $data['contribution_level_id'],
        $data['date_of_hire'],
        $data['status_id'],
        $data['username'],
        $data['password'],
        $data['role_id']
    );
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Employee created successfully'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Failed to create employee: ' . $error];
    }
}
    
    /**
     * Update employee
     * @param string $employee_id
     * @param array $data
     * @return array
     */
    public static function update($employee_id, $data) {
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        // Build UPDATE query dynamically
        $fields = [];
        $params = [];
        $types = '';
        
        $allowed_fields = [
            'prefix_id' => 'i', 'full_name_th' => 's', 'full_name_en' => 's',
            'function_id' => 'i', 'division_id' => 'i', 'department_id' => 'i',
            'section_id' => 'i', 'operation_id' => 'i', 'position_id' => 'i',
            'position_level_id' => 'i', 'labour_cost_id' => 'i', 'hiring_type_id' => 'i',
            'customer_zone_id' => 'i', 'contribution_level_id' => 'i', 'sex_id' => 'i',
            'nationality_id' => 'i', 'birthday' => 's', 'education_level_id' => 'i',
            'phone_no' => 's', 'address_village' => 's', 'address_subdistrict' => 's',
            'address_district' => 's', 'address_province' => 's', 'date_of_hire' => 's',
            'date_of_termination' => 's', 'status_id' => 'i', 'reason_for_termination_id' => 'i',
            'suggestion' => 's', 'remark' => 's', 'profile_pic_path' => 's'
        ];
        
        foreach ($allowed_fields as $field => $type) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
                $types .= $type;
            }
        }
        
        if (empty($fields)) {
            $conn->close();
            return ['success' => false, 'message' => 'No fields to update'];
        }
        
        $sql = "UPDATE employees SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE employee_id = ?";
        $params[] = $employee_id;
        $types .= 's';
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Employee updated successfully'];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Failed to update employee: ' . $error];
        }
    }
    
    /**
     * Delete employee
     * @param string $employee_id
     * @return array
     */
    public static function delete($employee_id) {
        $conn = getDbConnection();
        
        if (!$conn) {
            return ['success' => false, 'message' => 'Database connection failed'];
        }
        
        $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Employee deleted successfully'];
        } else {
            $error = $stmt->error;
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Failed to delete employee: ' . $error];
        }
    }
    
    /**
     * Get employee dropdown list (ID and Name only)
     * @return array
     */
    public static function getDropdownList() {
        $conn = getDbConnection();
        
        if (!$conn) {
            return [];
        }
        
        $result = $conn->query("SELECT employee_id, full_name_th, full_name_en FROM employees WHERE status_id = 1 ORDER BY employee_id");
        
        $employees = [];
        while ($row = $result->fetch_assoc()) {
            $employees[] = $row;
        }
        
        $conn->close();
        return $employees;
    }
}
?>