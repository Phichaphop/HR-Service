<?php
/**
 * Question Model Class (Q&A System)
 * Handles Questions, Answers, Comments, and Votes CRUD Operations
 * 
 * Used by:
 * - /controllers/QaController.php
 * - /views/employee/qa_help.php (Employee viewing)
 * - /views/employee/ask_question.php (Employee asking)
 * - /views/admin/qa_management.php (Admin managing)
 * - /api/qa_search.php (Search API)
 * - /api/qa_vote.php (Voting API)
 */

class Question {
    
    // ===== CATEGORY OPERATIONS =====
    
    /**
     * Get all Q&A categories
     * @param bool $active_only
     * @return array
     */
    public static function getCategories($active_only = true) {
        $conn = getDbConnection();
        
        $query = "SELECT * FROM qa_categories";
        if ($active_only) {
            $query .= " WHERE is_active = 1";
        }
        $query .= " ORDER BY category_order ASC";
        
        $result = $conn->query($query);
        $categories = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        
        $conn->close();
        return $categories;
    }
    
    /**
     * Get category name based on language
     * @param int $category_id
     * @param string $language (th, en, my)
     * @return string
     */
    public static function getCategoryName($category_id, $language = 'th') {
        $conn = getDbConnection();
        
        $field = "category_name_" . $language;
        $stmt = $conn->prepare("SELECT $field as name FROM qa_categories WHERE category_id = ?");
        $stmt->bind_param('i', $category_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $name = ($row = $result->fetch_assoc()) ? $row['name'] : 'Unknown';
        
        $stmt->close();
        $conn->close();
        
        return $name;
    }
    
    // ===== QUESTION OPERATIONS =====
    
    /**
     * Create new question
     * @param array $data
     * @return array
     */
    public static function create($data) {
        $conn = getDbConnection();
        
        try {
            $stmt = $conn->prepare("
                INSERT INTO qa_questions (
                    category_id, question_th, question_en, question_my,
                    created_by, is_anonymous, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            
            if (!$stmt) {
                return ['success' => false, 'message' => $conn->error];
            }
            
            $is_anonymous = !empty($data['is_anonymous']) ? 1 : 0;
            
            $stmt->bind_param(
                'issssi',
                $data['category_id'],
                $data['question_th'],
                $data['question_en'],
                $data['question_my'],
                $data['created_by'] ?? null,
                $is_anonymous
            );
            
            if ($stmt->execute()) {
                $question_id = $conn->insert_id;
                $stmt->close();
                $conn->close();
                
                return [
                    'success' => true,
                    'message' => 'Question posted successfully',
                    'question_id' => $question_id
                ];
            } else {
                $stmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Failed to create question'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get question by ID
     * @param int $question_id
     * @param string $language
     * @return array|false
     */
    public static function getById($question_id, $language = 'th') {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("
            SELECT 
                q.*,
                c.category_name_th, c.category_name_en, c.category_name_my,
                e.full_name_th as created_by_name,
                e.full_name_en as created_by_name_en,
                e.profile_pic_path,
                (SELECT COUNT(*) FROM qa_comments WHERE question_id = q.question_id) as comment_count,
                (SELECT COUNT(*) FROM qa_helpful_votes WHERE question_id = q.question_id AND vote_type IN ('helpful', 'useful')) as helpful_votes
            FROM qa_questions q
            LEFT JOIN qa_categories c ON q.category_id = c.category_id
            LEFT JOIN employees e ON q.created_by = e.employee_id
            WHERE q.question_id = ? AND q.is_active = 1
        ");
        
        $stmt->bind_param('i', $question_id);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $question = $result->fetch_assoc();
        
        $stmt->close();
        
        // Increment views
        if ($question) {
            $conn->query("UPDATE qa_questions SET views_count = views_count + 1 WHERE question_id = $question_id");
        }
        
        $conn->close();
        
        return $question ?: false;
    }
    
    /**
     * Search/Get questions with filtering
     * @param array $filters (category_id, search, is_answered, is_faq, etc.)
     * @param string $language
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function search($filters = [], $language = 'th', $limit = 20, $offset = 0) {
        $conn = getDbConnection();
        
        $field_question = "question_" . $language;
        $field_answer = "answer_" . $language;
        
        $query = "
            SELECT 
                q.question_id,
                q.category_id,
                q.$field_question as question_text,
                q.$field_answer as answer_text,
                q.is_answered,
                q.is_pinned,
                q.views_count,
                q.helpful_count,
                q.created_at,
                q.updated_at,
                c.category_name_th, c.category_name_en, c.category_name_my,
                e.full_name_th as created_by_name,
                CASE WHEN q.is_anonymous = 1 THEN 'Anonymous' ELSE e.full_name_th END as display_name,
                (SELECT COUNT(*) FROM qa_comments WHERE question_id = q.question_id) as comment_count
            FROM qa_questions q
            LEFT JOIN qa_categories c ON q.category_id = c.category_id
            LEFT JOIN employees e ON q.created_by = e.employee_id
            WHERE q.is_active = 1
        ";
        
        // Add filters
        if (!empty($filters['category_id'])) {
            $query .= " AND q.category_id = " . intval($filters['category_id']);
        }
        
        if (!empty($filters['search'])) {
            $search = $conn->escape_string($filters['search']);
            $query .= " AND (q.$field_question LIKE '%$search%' OR q.$field_answer LIKE '%$search%')";
        }
        
        if (isset($filters['is_answered'])) {
            $is_answered = $filters['is_answered'] ? 1 : 0;
            $query .= " AND q.is_answered = $is_answered";
        }
        
        if (!empty($filters['is_faq'])) {
            $query .= " AND q.is_faq = 1";
        }
        
        if (!empty($filters['is_pinned'])) {
            $query .= " AND q.is_pinned = 1";
        }
        
        // Order by pinned first, then by date
        $query .= " ORDER BY q.is_pinned DESC, q.created_at DESC LIMIT $limit OFFSET $offset";
        
        $result = $conn->query($query);
        $questions = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $questions[] = $row;
            }
        }
        
        $conn->close();
        return $questions;
    }
    
    /**
     * Answer a question
     * @param int $question_id
     * @param string $answer_th
     * @param string $answer_en
     * @param string $answer_my
     * @param string $answered_by
     * @return array
     */
    public static function answerQuestion($question_id, $answer_th, $answer_en, $answer_my, $answered_by) {
        $conn = getDbConnection();
        
        try {
            $stmt = $conn->prepare("
                UPDATE qa_questions
                SET 
                    answer_th = ?,
                    answer_en = ?,
                    answer_my = ?,
                    is_answered = 1,
                    answered_by = ?,
                    answered_at = NOW(),
                    updated_at = NOW()
                WHERE question_id = ?
            ");
            
            $stmt->bind_param('ssssi', $answer_th, $answer_en, $answer_my, $answered_by, $question_id);
            
            if ($stmt->execute()) {
                $stmt->close();
                $conn->close();
                
                return ['success' => true, 'message' => 'Answer posted successfully'];
            } else {
                $stmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Failed to answer question'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Mark question as FAQ
     * @param int $question_id
     * @param bool $is_faq
     * @return array
     */
    public static function markAsFaq($question_id, $is_faq = true) {
        $conn = getDbConnection();
        
        $faq_val = $is_faq ? 1 : 0;
        $stmt = $conn->prepare("UPDATE qa_questions SET is_faq = ? WHERE question_id = ?");
        $stmt->bind_param('ii', $faq_val, $question_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Question status updated'];
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Failed to update status'];
        }
    }
    
    /**
     * Pin/Unpin question
     * @param int $question_id
     * @param bool $is_pinned
     * @return array
     */
    public static function pin($question_id, $is_pinned = true) {
        $conn = getDbConnection();
        
        $pinned_val = $is_pinned ? 1 : 0;
        $stmt = $conn->prepare("UPDATE qa_questions SET is_pinned = ? WHERE question_id = ?");
        $stmt->bind_param('ii', $pinned_val, $question_id);
        
        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            return ['success' => true, 'message' => 'Question pinned status updated'];
        } else {
            $stmt->close();
            $conn->close();
            return ['success' => false, 'message' => 'Failed to update pinned status'];
        }
    }
    
    // ===== COMMENT OPERATIONS =====
    
    /**
     * Add comment to question
     * @param int $question_id
     * @param string $comment_text
     * @param string $comment_author (employee_id or null for anonymous)
     * @param string $author_name (display name)
     * @param bool $is_admin_reply
     * @return array
     */
    public static function addComment($question_id, $comment_text, $comment_author, $author_name, $is_admin_reply = false) {
        $conn = getDbConnection();
        
        try {
            $admin_flag = $is_admin_reply ? 1 : 0;
            
            $stmt = $conn->prepare("
                INSERT INTO qa_comments (
                    question_id, comment_text, comment_author, author_name,
                    is_admin_reply, created_at
                ) VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->bind_param('isssi', $question_id, $comment_text, $comment_author, $author_name, $admin_flag);
            
            if ($stmt->execute()) {
                $comment_id = $conn->insert_id;
                $stmt->close();
                
                // Increment question views
                $conn->query("UPDATE qa_questions SET views_count = views_count + 1 WHERE question_id = $question_id");
                
                $conn->close();
                
                return [
                    'success' => true,
                    'message' => 'Comment added successfully',
                    'comment_id' => $comment_id
                ];
            } else {
                $stmt->close();
                $conn->close();
                return ['success' => false, 'message' => 'Failed to add comment'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get comments for question
     * @param int $question_id
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getComments($question_id, $limit = 50, $offset = 0) {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("
            SELECT 
                c.*,
                e.profile_pic_path,
                (SELECT COUNT(*) FROM qa_helpful_votes WHERE comment_id = c.comment_id AND vote_type IN ('helpful', 'useful')) as helpful_count
            FROM qa_comments c
            LEFT JOIN employees e ON c.comment_author = e.employee_id
            WHERE c.question_id = ?
            ORDER BY c.is_admin_reply DESC, c.created_at ASC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->bind_param('iii', $question_id, $limit, $offset);
        $stmt->execute();
        
        $result = $stmt->get_result();
        $comments = [];
        
        while ($row = $result->fetch_assoc()) {
            $comments[] = $row;
        }
        
        $stmt->close();
        $conn->close();
        
        return $comments;
    }
    
    // ===== VOTING OPERATIONS =====
    
    /**
     * Add or update vote
     * @param int $question_id (optional)
     * @param int $comment_id (optional)
     * @param string $employee_id (optional, null if anonymous)
     * @param string $voter_ip
     * @param string $vote_type (helpful, not_helpful, useful, not_useful)
     * @return array
     */
    public static function vote($question_id, $comment_id, $employee_id, $voter_ip, $vote_type) {
        $conn = getDbConnection();
        
        try {
            // Check if already voted
            $check_query = "SELECT vote_id FROM qa_helpful_votes WHERE ";
            $check_query .= ($question_id ? "question_id = $question_id" : "comment_id = $comment_id");
            if ($employee_id) {
                $check_query .= " AND employee_id = '$employee_id'";
            } else {
                $check_query .= " AND voter_ip = '$voter_ip'";
            }
            
            $existing = $conn->query($check_query);
            
            if ($existing && $existing->num_rows > 0) {
                // Update existing vote
                $update_stmt = $conn->prepare("
                    UPDATE qa_helpful_votes
                    SET vote_type = ?
                    WHERE question_id = ? OR comment_id = ?
                ");
                
                $update_stmt->bind_param('sii', $vote_type, $question_id, $comment_id);
                $result = $update_stmt->execute();
                $update_stmt->close();
            } else {
                // Insert new vote
                $insert_stmt = $conn->prepare("
                    INSERT INTO qa_helpful_votes 
                    (question_id, comment_id, employee_id, voter_ip, vote_type)
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $q_id = $question_id ?: null;
                $c_id = $comment_id ?: null;
                
                $insert_stmt->bind_param('iisss', $q_id, $c_id, $employee_id, $voter_ip, $vote_type);
                $result = $insert_stmt->execute();
                $insert_stmt->close();
            }
            
            $conn->close();
            
            return $result ? 
                ['success' => true, 'message' => 'Vote recorded'] :
                ['success' => false, 'message' => 'Failed to record vote'];
                
        } catch (Exception $e) {
            $conn->close();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // ===== STATISTICS & UTILITIES =====
    
    /**
     * Get Q&A statistics
     * @return array
     */
    public static function getStatistics() {
        $conn = getDbConnection();
        
        $stats = [
            'total_questions' => 0,
            'answered' => 0,
            'unanswered' => 0,
            'total_views' => 0,
            'total_comments' => 0,
            'faqs' => 0,
            'recent_questions' => []
        ];
        
        // Total questions
        $result = $conn->query("SELECT COUNT(*) as count FROM qa_questions WHERE is_active = 1");
        if ($row = $result->fetch_assoc()) {
            $stats['total_questions'] = $row['count'];
        }
        
        // Answered vs Unanswered
        $result = $conn->query("
            SELECT 
                SUM(CASE WHEN is_answered = 1 THEN 1 ELSE 0 END) as answered,
                SUM(CASE WHEN is_answered = 0 THEN 1 ELSE 0 END) as unanswered
            FROM qa_questions WHERE is_active = 1
        ");
        if ($row = $result->fetch_assoc()) {
            $stats['answered'] = $row['answered'];
            $stats['unanswered'] = $row['unanswered'];
        }
        
        // Total views
        $result = $conn->query("SELECT SUM(views_count) as total FROM qa_questions WHERE is_active = 1");
        if ($row = $result->fetch_assoc()) {
            $stats['total_views'] = $row['total'] ?? 0;
        }
        
        // Total comments
        $result = $conn->query("SELECT COUNT(*) as count FROM qa_comments");
        if ($row = $result->fetch_assoc()) {
            $stats['total_comments'] = $row['count'];
        }
        
        // FAQs
        $result = $conn->query("SELECT COUNT(*) as count FROM qa_questions WHERE is_faq = 1");
        if ($row = $result->fetch_assoc()) {
            $stats['faqs'] = $row['count'];
        }
        
        $conn->close();
        return $stats;
    }
}
?>