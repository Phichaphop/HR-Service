<?php
/**
 * Localization Helper
 * Manages multi-language text retrieval
 */

require_once __DIR__ . '/../config/db_config.php';

class Localization {
    private static $cache = [];
    
    /**
     * Get text by key and language
     * @param string $key_id The localization key
     * @param string $lang Language code (th, en, my)
     * @return string The localized text
     */

    public static function getText($key_id, $lang = null) {
        // Get language from session if not provided
        if ($lang === null) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $lang = $_SESSION['language'] ?? DEFAULT_LANGUAGE;
        }
        
        // Check cache first
        $cache_key = $key_id . '_' . $lang;
        if (isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }
        
        $conn = getDbConnection();
        if (!$conn) {
            return $key_id; // Return key if DB fails
        }
        
        $stmt = $conn->prepare("SELECT {$lang}_text FROM localization_master WHERE key_id = ?");
        $stmt->bind_param("s", $key_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $text = $row[$lang . '_text'] ?? $key_id;
            self::$cache[$cache_key] = $text;
            $stmt->close();
            $conn->close();
            return $text;
        }
        
        $stmt->close();
        $conn->close();
        return $key_id; // Return key if not found
    }
    
    /**
     * Get all texts for a category
     * @param string $category The category
     * @param string $lang Language code
     * @return array Associative array of key => text
     */
    public static function getCategory($category, $lang = null) {
        if ($lang === null) {
            session_start();
            $lang = $_SESSION['language'] ?? DEFAULT_LANGUAGE;
        }
        
        $conn = getDbConnection();
        if (!$conn) {
            return [];
        }
        
        $stmt = $conn->prepare("SELECT key_id, {$lang}_text FROM localization_master WHERE category = ?");
        $stmt->bind_param("s", $category);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $texts = [];
        while ($row = $result->fetch_assoc()) {
            $texts[$row['key_id']] = $row[$lang . '_text'];
        }
        
        $stmt->close();
        $conn->close();
        return $texts;
    }
    
    /**
     * Get master data by ID with localization
     * @param string $table The master table name
     * @param int $id The record ID
     * @param string $lang Language code
     * @return string The localized value
     */
    public static function getMasterData($table, $id, $lang = null) {
        if ($lang === null) {
            $lang = $_SESSION['language'] ?? DEFAULT_LANGUAGE;
        }
        
        $conn = getDbConnection();
        if (!$conn) {
            return '';
        }
        
        // Determine column pattern based on table
        $column_map = [
            'prefix_master' => ['id' => 'prefix_id', 'col' => 'prefix'],
            'function_master' => ['id' => 'function_id', 'col' => 'function_name'],
            'division_master' => ['id' => 'division_id', 'col' => 'division_name'],
            'department_master' => ['id' => 'department_id', 'col' => 'department_name'],
            'section_master' => ['id' => 'section_id', 'col' => 'section_name'],
            'operation_master' => ['id' => 'operation_id', 'col' => 'operation_name'],
            'position_master' => ['id' => 'position_id', 'col' => 'position_name'],
            'position_level_master' => ['id' => 'level_id', 'col' => 'level_name'],
            'labour_cost_master' => ['id' => 'labour_cost_id', 'col' => 'cost_name'],
            'hiring_type_master' => ['id' => 'hiring_type_id', 'col' => 'type_name'],
            'customer_zone_master' => ['id' => 'zone_id', 'col' => 'zone_name'],
            'contribution_level_master' => ['id' => 'contribution_id', 'col' => 'level_name'],
            'sex_master' => ['id' => 'sex_id', 'col' => 'sex_name'],
            'nationality_master' => ['id' => 'nationality_id', 'col' => 'nationality'],
            'education_level_master' => ['id' => 'education_id', 'col' => 'level_name'],
            'status_master' => ['id' => 'status_id', 'col' => 'status_name'],
            'termination_reason_master' => ['id' => 'reason_id', 'col' => 'reason'],
            'service_category_master' => ['id' => 'category_id', 'col' => 'category_name'],
            'service_type_master' => ['id' => 'type_id', 'col' => 'type_name'],
            'doc_type_master' => ['id' => 'doc_type_id', 'col' => 'type_name']
        ];
        
        if (!isset($column_map[$table])) {
            $conn->close();
            return '';
        }
        
        $id_col = $column_map[$table]['id'];
        $name_col = $column_map[$table]['col'] . '_' . $lang;
        
        $sql = "SELECT $name_col FROM $table WHERE $id_col = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $value = '';
        if ($row = $result->fetch_assoc()) {
            $value = $row[$name_col] ?? '';
        }
        
        $stmt->close();
        $conn->close();
        return $value;
    }
    
    /**
     * Clear localization cache
     */
    public static function clearCache() {
        self::$cache = [];
    }
}

/**
 * Global helper function for quick text retrieval
 * @param string $key_id The localization key
 * @return string The localized text
 */
function __($key_id) {
    return Localization::getText($key_id);
}

/**
 * Get master data with localization
 * @param string $table The master table name
 * @param int $id The record ID
 * @return string The localized value
 */
function get_master($table, $id) {
    return Localization::getMasterData($table, $id);
}

?>