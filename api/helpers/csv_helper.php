<?php
/**
 * CSV Helper Functions
 * รองรับภาษาไทยและการ encode ต่างๆ
 */

class CSVHelper {
    
    /**
     * อ่านไฟล์ CSV และแปลงเป็น UTF-8
     * @param string $file_path
     * @return array|false
     */
    public static function readCSVWithEncoding($file_path) {
        if (!file_exists($file_path)) {
            return false;
        }
        
        // อ่านเนื้อหาทั้งหมด
        $content = file_get_contents($file_path);
        
        // ลบ BOM ถ้ามี
        $content = self::removeBOM($content);
        
        // ตรวจสอบและแปลง encoding
        $content = self::convertToUTF8($content);
        
        // แยก lines
        $lines = explode("\n", $content);
        
        $result = [];
        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            // Parse CSV line
            $data = str_getcsv($line);
            $result[] = $data;
        }
        
        return $result;
    }
    
    /**
     * ลบ BOM (Byte Order Mark)
     * @param string $content
     * @return string
     */
    public static function removeBOM($content) {
        // UTF-8 BOM
        if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
            $content = substr($content, 3);
        }
        
        // UTF-16 BE BOM
        if (substr($content, 0, 2) === "\xFE\xFF") {
            $content = substr($content, 2);
        }
        
        // UTF-16 LE BOM
        if (substr($content, 0, 2) === "\xFF\xFE") {
            $content = substr($content, 2);
        }
        
        return $content;
    }
    
    /**
     * แปลง content เป็น UTF-8
     * @param string $content
     * @return string
     */
    public static function convertToUTF8($content) {
        // รายการ encoding ที่ต้องตรวจสอบ (เรียงตามความนิยมในไทย)
        $encodings = [
            'UTF-8',
            'TIS-620',        // Thai encoding
            'Windows-874',    // Thai Windows
            'ISO-8859-11',    // Thai ISO
            'CP874',          // Thai Code Page
            'UTF-16',
            'UTF-16LE',
            'UTF-16BE'
        ];
        
        // ตรวจสอบ encoding ปัจจุบัน
        $detected = mb_detect_encoding($content, $encodings, true);
        
        // ถ้าไม่ใช่ UTF-8 ให้แปลง
        if ($detected && $detected !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $detected);
        }
        
        // ถ้าตรวจสอบไม่ได้ ลองแปลงจาก TIS-620 (encoding ยอดนิยมของไทย)
        if (!$detected) {
            $content = mb_convert_encoding($content, 'UTF-8', 'TIS-620');
        }
        
        return $content;
    }
    
    /**
     * เขียนข้อมูลเป็น CSV พร้อม UTF-8 BOM
     * @param array $data
     * @param string $filename
     * @return bool
     */
    public static function writeCSVWithBOM($data, $filename) {
        $fp = fopen($filename, 'w');
        
        if (!$fp) {
            return false;
        }
        
        // เขียน UTF-8 BOM
        fwrite($fp, "\xEF\xBB\xBF");
        
        // เขียนข้อมูล
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        
        fclose($fp);
        return true;
    }
    
    /**
     * ตรวจสอบว่าเป็นภาษาไทยหรือไม่
     * @param string $text
     * @return bool
     */
    public static function containsThai($text) {
        return preg_match('/[\x{0E00}-\x{0E7F}]/u', $text) === 1;
    }
    
    /**
     * ทดสอบการอ่านไฟล์ CSV
     * @param string $file_path
     * @return array
     */
    public static function testReadCSV($file_path) {
        $info = [
            'file_exists' => file_exists($file_path),
            'file_size' => file_exists($file_path) ? filesize($file_path) : 0,
            'mime_type' => file_exists($file_path) ? mime_content_type($file_path) : '',
        ];
        
        if (file_exists($file_path)) {
            $content = file_get_contents($file_path);
            $info['original_encoding'] = mb_detect_encoding($content, ['UTF-8', 'TIS-620', 'Windows-874'], true);
            $info['has_bom'] = substr($content, 0, 3) === "\xEF\xBB\xBF";
            $info['contains_thai'] = self::containsThai($content);
            $info['first_100_chars'] = mb_substr($content, 0, 100, 'UTF-8');
        }
        
        return $info;
    }
}
?>