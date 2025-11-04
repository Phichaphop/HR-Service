<?php
/**
 * System Status Checker
 * แสดงสถานะระบบทั้งหมด
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/db_config.php';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>System Status</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .card { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        h2 { color: #007bff; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .status-good { color: green; font-weight: bold; }
        .status-bad { color: red; font-weight: bold; }
        .status-warn { color: orange; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 10px; text-align: left; border: 1px solid #ddd; }
        th { background: #007bff; color: white; }
        tr:nth-child(even) { background: #f8f9fa; }
        .info-grid { display: grid; grid-template-columns: 200px 1fr; gap: 10px; }
        .info-label { font-weight: bold; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 HR System Status Report</h1>
        
        <?php
        $conn = getDbConnection();
        
        if (!$conn) {
            echo "<div class='card'>";
            echo "<div class='status-bad'>❌ Database Connection Failed!</div>";
            echo "<p>Cannot connect to database. Please check your configuration.</p>";
            echo "</div></body></html>";
            die();
        }
        ?>
        
        <!-- 1. Server Information -->
        <div class="card">
            <h2>💻 Server Information</h2>
            <div class="info-grid">
                <div class="info-label">PHP Version:</div>
                <div><?php echo phpversion(); ?></div>
                
                <div class="info-label">Server Software:</div>
                <div><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></div>
                
                <div class="info-label">Max Execution Time:</div>
                <div><?php echo ini_get('max_execution_time'); ?>s</div>
                
                <div class="info-label">Memory Limit:</div>
                <div><?php echo ini_get('memory_limit'); ?></div>
                
                <div class="info-label">Upload Max Size:</div>
                <div><?php echo ini_get('upload_max_filesize'); ?></div>
                
                <div class="info-label">MySQL Extension:</div>
                <div class="<?php echo extension_loaded('mysqli') ? 'status-good' : 'status-bad'; ?>">
                    <?php echo extension_loaded('mysqli') ? '✓ Loaded' : '✗ Not Loaded'; ?>
                </div>
                
                <div class="info-label">PDO Extension:</div>
                <div class="<?php echo extension_loaded('pdo') ? 'status-good' : 'status-bad'; ?>">
                    <?php echo extension_loaded('pdo') ? '✓ Loaded' : '✗ Not Loaded'; ?>
                </div>
            </div>
        </div>
        
        <!-- 2. Database Information -->
        <div class="card">
            <h2>🗄️ Database Information</h2>
            <div class="info-grid">
                <div class="info-label">Server:</div>
                <div><?php echo DB_SERVER; ?></div>
                
                <div class="info-label">Database Name:</div>
                <div><?php echo DB_NAME; ?></div>
                
                <div class="info-label">MySQL Version:</div>
                <div><?php echo $conn->server_info; ?></div>
                
                <div class="info-label">Connection Status:</div>
                <div class="status-good">✓ Connected</div>
                
                <div class="info-label">Character Set:</div>
                <div><?php echo $conn->character_set_name(); ?></div>
            </div>
        </div>
        
        <!-- 3. Tables Status -->
        <div class="card">
            <h2>📊 Tables Status</h2>
            <?php
            $result = $conn->query("SHOW TABLES");
            
            if ($result && $result->num_rows > 0) {
                echo "<p class='status-good'>✓ Found " . $result->num_rows . " tables</p>";
                
                echo "<table>";
                echo "<tr><th>Table Name</th><th>Row Count</th><th>Engine</th><th>Collation</th><th>Status</th></tr>";
                
                while ($row = $result->fetch_array()) {
                    $table_name = $row[0];
                    
                    // Get row count
                    $count_result = $conn->query("SELECT COUNT(*) as cnt FROM `$table_name`");
                    $count = $count_result ? $count_result->fetch_assoc()['cnt'] : 'Error';
                    
                    // Get table status
                    $status_result = $conn->query("SHOW TABLE STATUS LIKE '$table_name'");
                    $status = $status_result ? $status_result->fetch_assoc() : null;
                    
                    $engine = $status ? $status['Engine'] : 'Unknown';
                    $collation = $status ? $status['Collation'] : 'Unknown';
                    
                    $status_class = $count > 0 ? 'status-good' : 'status-warn';
                    $status_text = $count > 0 ? '✓ Has Data' : '⚠ Empty';
                    
                    echo "<tr>";
                    echo "<td><strong>$table_name</strong></td>";
                    echo "<td>$count</td>";
                    echo "<td>$engine</td>";
                    echo "<td>$collation</td>";
                    echo "<td class='$status_class'>$status_text</td>";
                    echo "</tr>";
                }
                
                echo "</table>";
            } else {
                echo "<p class='status-bad'>✗ No tables found in database</p>";
                echo "<p>Please run the table creation script.</p>";
            }
            ?>
        </div>
        
        <!-- 4. Critical Tables Check -->
        <div class="card">
            <h2>🔑 Critical Tables Check</h2>
            <?php
            $critical_tables = [
                'roles' => 'User Roles',
                'employees' => 'Employees',
                'prefix_master' => 'Name Prefixes',
                'sex_master' => 'Gender',
                'nationality_master' => 'Nationality',
                'education_level_master' => 'Education Levels',
                'status_master' => 'Employee Status',
                'function_master' => 'Functions',
                'division_master' => 'Divisions',
                'department_master' => 'Departments',
                'section_master' => 'Sections',
                'operation_master' => 'Operations',
                'position_master' => 'Positions',
                'position_level_master' => 'Position Levels',
                'labour_cost_master' => 'Labour Cost',
                'hiring_type_master' => 'Hiring Types',
                'customer_zone_master' => 'Customer Zones',
                'contribution_level_master' => 'Contribution Levels',
                'certificate_types' => 'Certificate Types',
                'company_info' => 'Company Information'
            ];
            
            $all_good = true;
            
            echo "<table>";
            echo "<tr><th>Table</th><th>Description</th><th>Status</th><th>Row Count</th></tr>";
            
            foreach ($critical_tables as $table => $desc) {
                $check = $conn->query("SHOW TABLES LIKE '$table'");
                
                if ($check && $check->num_rows > 0) {
                    $count_result = $conn->query("SELECT COUNT(*) as cnt FROM `$table`");
                    $count = $count_result ? $count_result->fetch_assoc()['cnt'] : 0;
                    
                    if ($count > 0) {
                        echo "<tr>";
                        echo "<td><strong>$table</strong></td>";
                        echo "<td>$desc</td>";
                        echo "<td class='status-good'>✓ OK</td>";
                        echo "<td>$count</td>";
                        echo "</tr>";
                    } else {
                        echo "<tr>";
                        echo "<td><strong>$table</strong></td>";
                        echo "<td>$desc</td>";
                        echo "<td class='status-warn'>⚠ Empty</td>";
                        echo "<td>0</td>";
                        echo "</tr>";
                        $all_good = false;
                    }
                } else {
                    echo "<tr>";
                    echo "<td><strong>$table</strong></td>";
                    echo "<td>$desc</td>";
                    echo "<td class='status-bad'>✗ Missing</td>";
                    echo "<td>-</td>";
                    echo "</tr>";
                    $all_good = false;
                }
            }
            
            echo "</table>";
            
            if ($all_good) {
                echo "<div class='status-good' style='margin-top: 15px; padding: 15px; background: #d4edda; border-radius: 4px;'>";
                echo "<h3>✓ All Critical Tables are OK!</h3>";
                echo "<p>Your system has all necessary data and is ready to use.</p>";
                echo "</div>";
            } else {
                echo "<div class='status-warn' style='margin-top: 15px; padding: 15px; background: #fff3cd; border-radius: 4px;'>";
                echo "<h3>⚠ Some Tables Need Attention</h3>";
                echo "<p>Some tables are missing or empty. Please run the seed data script.</p>";
                echo "</div>";
            }
            ?>
        </div>
        
        <!-- 5. Sample Data from Key Tables -->
        <div class="card">
            <h2>👥 Sample Data</h2>
            
            <?php
            $sample_tables = ['roles', 'employees', 'company_info'];
            
            foreach ($sample_tables as $table) {
                $check = $conn->query("SHOW TABLES LIKE '$table'");
                if ($check && $check->num_rows > 0) {
                    $result = $conn->query("SELECT * FROM `$table` LIMIT 3");
                    
                    if ($result && $result->num_rows > 0) {
                        echo "<h3>Table: $table</h3>";
                        echo "<table>";
                        
                        // Header
                        $first_row = $result->fetch_assoc();
                        echo "<tr>";
                        foreach (array_keys($first_row) as $col) {
                            echo "<th>$col</th>";
                        }
                        echo "</tr>";
                        
                        // Data
                        echo "<tr>";
                        foreach ($first_row as $val) {
                            echo "<td>" . htmlspecialchars(substr($val, 0, 100)) . "</td>";
                        }
                        echo "</tr>";
                        
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            foreach ($row as $val) {
                                echo "<td>" . htmlspecialchars(substr($val, 0, 100)) . "</td>";
                            }
                            echo "</tr>";
                        }
                        
                        echo "</table>";
                    } else {
                        echo "<h3>Table: $table</h3>";
                        echo "<p class='status-warn'>⚠ No data found</p>";
                    }
                }
            }
            ?>
        </div>
        
        <!-- 6. Foreign Key Checks -->
        <div class="card">
            <h2>🔗 Foreign Key Integrity</h2>
            <?php
            echo "<p>Checking if all foreign key references are valid...</p>";
            
            $fk_checks = [
                "Check employees.role_id" => "SELECT COUNT(*) as cnt FROM employees e LEFT JOIN roles r ON e.role_id = r.role_id WHERE e.role_id IS NOT NULL AND r.role_id IS NULL",
                "Check employees.prefix_id" => "SELECT COUNT(*) as cnt FROM employees e LEFT JOIN prefix_master p ON e.prefix_id = p.prefix_id WHERE e.prefix_id IS NOT NULL AND p.prefix_id IS NULL",
                "Check employees.sex_id" => "SELECT COUNT(*) as cnt FROM employees e LEFT JOIN sex_master s ON e.sex_id = s.sex_id WHERE e.sex_id IS NOT NULL AND s.sex_id IS NULL",
            ];
            
            echo "<table>";
            echo "<tr><th>Check</th><th>Result</th></tr>";
            
            foreach ($fk_checks as $check_name => $query) {
                $result = $conn->query($query);
                if ($result) {
                    $row = $result->fetch_assoc();
                    $orphans = $row['cnt'];
                    
                    if ($orphans == 0) {
                        echo "<tr><td>$check_name</td><td class='status-good'>✓ OK (0 orphans)</td></tr>";
                    } else {
                        echo "<tr><td>$check_name</td><td class='status-bad'>✗ Found $orphans orphaned records</td></tr>";
                    }
                } else {
                    echo "<tr><td>$check_name</td><td class='status-warn'>⚠ Cannot check (table may not exist)</td></tr>";
                }
            }
            
            echo "</table>";
            ?>
        </div>
        
        <!-- 7. Quick Actions -->
        <div class="card">
            <h2>⚡ Quick Actions</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                <a href="debug_connection.php" style="display: block; padding: 15px; background: #007bff; color: white; text-decoration: none; text-align: center; border-radius: 4px;">
                    🔍 Test Connection
                </a>
                <a href="debug_seed_step_by_step.php" style="display: block; padding: 15px; background: #28a745; color: white; text-decoration: none; text-align: center; border-radius: 4px;">
                    🌱 Seed Data
                </a>
                <a href="views/admin/db_manager.php" style="display: block; padding: 15px; background: #ffc107; color: black; text-decoration: none; text-align: center; border-radius: 4px;">
                    ⚙️ DB Manager
                </a>
                <a href="check_system_status.php" style="display: block; padding: 15px; background: #17a2b8; color: white; text-decoration: none; text-align: center; border-radius: 4px;">
                    🔄 Refresh Status
                </a>
            </div>
        </div>
        
        <?php $conn->close(); ?>
        
    </div>
</body>
</html>