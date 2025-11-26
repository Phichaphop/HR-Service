<?php
/**
 * Rating Controller
 * File: /controllers/RatingController.php
 * 
 * Purpose: Check if user has pending ratings before creating new requests
 * 
 * ✅ ตรวจสอบคำขอที่ Complete แล้วแต่ยังไม่ได้ให้คะแนน
 * ✅ รองรับทุกประเภทคำขอ (ยกเว้น document_submissions)
 * ✅ ส่งคืนข้อมูลรายละเอียดสำหรับแสดงให้ผู้ใช้
 */

class RatingController
{
    /**
     * Check if employee has pending ratings
     * 
     * @param string $employee_id รหัสพนักงาน
     * @param mysqli $conn Database connection
     * @return array ['has_pending' => bool, 'pending_requests' => array]
     */
    public static function checkPendingRatings($employee_id, $conn)
    {
        // ประเภทคำขอทั้งหมด (ยกเว้น document_submissions ที่ให้คะแนนพร้อมส่ง)
        $request_tables = [
            'leave_requests' => ['name_th' => 'ใบลา', 'name_en' => 'Leave Request', 'name_my' => 'အငြိုးပြုစု'],
            'certificate_requests' => ['name_th' => 'หนังสือรับรอง', 'name_en' => 'Certificate Request', 'name_my' => 'လက်မှတ်'],
            'id_card_requests' => ['name_th' => 'บัตรพนักงาน', 'name_en' => 'ID Card Request', 'name_my' => 'အိုင်ဒီကဒ်'],
            'shuttle_bus_requests' => ['name_th' => 'รถรับส่ง', 'name_en' => 'Shuttle Bus Request', 'name_my' => 'ကားရီးယား'],
            'locker_requests' => ['name_th' => 'ตู้ล็อกเกอร์', 'name_en' => 'Locker Request', 'name_my' => 'အိတ်'],
            'supplies_requests' => ['name_th' => 'เบิกอุปกรณ์', 'name_en' => 'Supplies Request', 'name_my' => 'ပရိယာယ်'],
            'skill_test_requests' => ['name_th' => 'ทดสอบทักษะ', 'name_en' => 'Skill Test Request', 'name_my' => 'အရည်အချင်း']
        ];

        $pending_requests = [];

        foreach ($request_tables as $table => $names) {
            $sql = "SELECT 
                        request_id,
                        status,
                        created_at,
                        COALESCE(satisfaction_score, 0) as satisfaction_score
                    FROM $table
                    WHERE employee_id = ? 
                        AND status = 'Complete'
                        AND (satisfaction_score IS NULL OR satisfaction_score = 0)
                    ORDER BY created_at DESC";

            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                error_log("RatingController: Failed to prepare query for $table - " . $conn->error);
                continue;
            }

            $stmt->bind_param("s", $employee_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $pending_requests[] = [
                    'table' => $table,
                    'request_id' => $row['request_id'],
                    'type_name_th' => $names['name_th'],
                    'type_name_en' => $names['name_en'],
                    'type_name_my' => $names['name_my'],
                    'created_at' => $row['created_at'],
                    'status' => $row['status']
                ];
            }

            $stmt->close();
        }

        return [
            'has_pending' => count($pending_requests) > 0,
            'pending_count' => count($pending_requests),
            'pending_requests' => $pending_requests
        ];
    }

    /**
     * Get pending ratings HTML message for display
     * 
     * @param array $pending_data ข้อมูลจาก checkPendingRatings()
     * @param string $lang ภาษา (th, en, my)
     * @return string HTML message
     */
    public static function getPendingRatingsMessage($pending_data, $lang = 'th')
    {
        $messages = [
            'th' => [
                'title' => '⚠️ กรุณาให้คะแนนคำขอเดิมก่อน',
                'description' => 'คุณมีคำขอที่เสร็จสิ้นแล้ว แต่ยังไม่ได้ให้คะแนน กรุณาให้คะแนนก่อนสร้างคำขอใหม่:',
                'go_to_rating' => 'ไปหน้าให้คะแนน →',
                'request' => 'คำขอ',
                'date' => 'วันที่'
            ],
            'en' => [
                'title' => '⚠️ Please Rate Previous Requests',
                'description' => 'You have completed requests that need rating. Please rate them before creating a new request:',
                'go_to_rating' => 'Go to Rating Page →',
                'request' => 'Request',
                'date' => 'Date'
            ],
            'my' => [
                'title' => '⚠️ ယခင်တောင်းခံမှုများကို အဆင့်သတ်မှတ်ပါ',
                'description' => 'သင့်တွင် အဆင့်သတ်မှတ်ရန် လိုအပ်သော ပြီးစီးသောတောင်းခံမှုများရှိသည်။ အသစ်တောင်းခံမှု ဖန်တီးမီ အဆင့်ပေးပါ:',
                'go_to_rating' => 'အဆင့်သတ်မှတ်သို့သွားမည် →',
                'request' => 'တောင်းခံမှု',
                'date' => 'ရက်စွဲ'
            ]
        ];

        $t = $messages[$lang] ?? $messages['th'];

        $html = '<div class="bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-6 rounded-lg mb-6 shadow-md">';
        $html .= '<div class="flex items-start">';
        $html .= '<div class="flex-shrink-0">';
        $html .= '<svg class="h-6 w-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">';
        $html .= '<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>';
        $html .= '</svg>';
        $html .= '</div>';
        $html .= '<div class="ml-3 flex-1">';
        $html .= '<h3 class="text-lg font-bold text-yellow-800 dark:text-yellow-200 mb-2">' . $t['title'] . '</h3>';
        $html .= '<p class="text-sm text-yellow-700 dark:text-yellow-300 mb-4">' . $t['description'] . '</p>';
        
        $html .= '<div class="space-y-2 mb-4">';
        foreach ($pending_data['pending_requests'] as $request) {
            $type_name_key = 'type_name_' . $lang;
            $type_name = $request[$type_name_key] ?? $request['type_name_th'];
            $date = date('d/m/Y', strtotime($request['created_at']));
            
            $html .= '<div class="flex items-center justify-between bg-white dark:bg-gray-800 rounded-lg px-4 py-3 shadow-sm">';
            $html .= '<div class="flex items-center space-x-3">';
            $html .= '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200">';
            $html .= '#' . $request['request_id'];
            $html .= '</span>';
            $html .= '<span class="text-sm font-medium text-gray-900 dark:text-gray-100">' . $type_name . '</span>';
            $html .= '</div>';
            $html .= '<span class="text-xs text-gray-500 dark:text-gray-400">' . $t['date'] . ': ' . $date . '</span>';
            $html .= '</div>';
        }
        $html .= '</div>';
        
        $html .= '<a href="' . (defined('BASE_PATH') ? BASE_PATH : '') . '/views/employee/my_requests.php" ';
        $html .= 'class="inline-flex items-center px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded-lg transition-all duration-200 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">';
        $html .= $t['go_to_rating'];
        $html .= '<svg class="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">';
        $html .= '<path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>';
        $html .= '</svg>';
        $html .= '</a>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
?>