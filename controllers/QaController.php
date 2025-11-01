<?php
/**
 * Q&A Controller
 * Handles Q&A system logic (questions, answers, comments, votes)
 * 
 * Endpoints:
 * - /api/qa_search.php → QaController::search()
 * - /api/qa_question.php → QaController::askQuestion()
 * - /api/qa_answer.php → QaController::answerQuestion()
 * - /api/qa_comment.php → QaController::addComment()
 * - /api/qa_vote.php → QaController::vote()
 * - /views/employee/qa_help.php → QaController::listQuestions()
 * - /views/admin/qa_management.php → QaController::manageQuestions()
 */

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../models/Question.php';
require_once __DIR__ . '/../db/Localization.php';

class QaController {
    
    /**
     * Ask a question
     * POST /api/qa_question.php
     * 
     * Parameters:
     * - category_id (required): Question category
     * - question_th (required): Thai question
     * - question_en (required): English question
     * - question_my (required): Myanmar question
     * - is_anonymous (optional): 1 or 0
     */
    public static function askQuestion() {
        // Check authentication
        if (!AuthController::isAuthenticated()) {
            return [
                'success' => false,
                'message' => 'Authentication required'
            ];
        }
        
        // Validate input
        $category_id = intval($_POST['category_id'] ?? 0);
        $question_th = trim($_POST['question_th'] ?? '');
        $question_en = trim($_POST['question_en'] ?? '');
        $question_my = trim($_POST['question_my'] ?? '');
        
        if ($category_id <= 0 || empty($question_th) || empty($question_en)) {
            return [
                'success' => false,
                'message' => 'Missing required fields'
            ];
        }
        
        // Validate category
        $categories = Question::getCategories();
        $valid_category = false;
        
        foreach ($categories as $cat) {
            if ($cat['category_id'] === $category_id) {
                $valid_category = true;
                break;
            }
        }
        
        if (!$valid_category) {
            return ['success' => false, 'message' => 'Invalid category'];
        }
        
        // Check for duplicate questions (prevent spam)
        $conn = getDbConnection();
        $stmt = $conn->prepare("
            SELECT question_id FROM qa_questions
            WHERE question_th = ? OR question_en = ?
            AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $stmt->bind_param('ss', $question_th, $question_en);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            $conn->close();
            return [
                'success' => false,
                'message' => 'Similar question already exists. Please check existing questions first.'
            ];
        }
        
        $stmt->close();
        $conn->close();
        
        // Prepare data
        $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
        
        $data = [
            'category_id' => $category_id,
            'question_th' => $question_th,
            'question_en' => $question_en,
            'question_my' => $question_my,
            'created_by' => $is_anonymous ? null : $_SESSION['user_id'],
            'is_anonymous' => $is_anonymous
        ];
        
        // Create question
        $result = Question::create($data);
        
        return $result;
    }
    
    /**
     * Answer a question (Admin/Officer only)
     * POST /api/qa_answer.php
     * 
     * Parameters:
     * - question_id (required)
     * - answer_th (required)
     * - answer_en (required)
     * - answer_my (required)
     * - make_faq (optional): 1 or 0
     */
    public static function answerQuestion() {
        AuthController::requireRole(['admin', 'officer']);
        
        $question_id = intval($_POST['question_id'] ?? 0);
        $answer_th = trim($_POST['answer_th'] ?? '');
        $answer_en = trim($_POST['answer_en'] ?? '');
        $answer_my = trim($_POST['answer_my'] ?? '');
        $make_faq = isset($_POST['make_faq']) ? 1 : 0;
        
        if ($question_id <= 0 || empty($answer_th) || empty($answer_en)) {
            return [
                'success' => false,
                'message' => 'Missing required fields'
            ];
        }
        
        // Verify question exists
        $question = Question::getById($question_id);
        if (!$question) {
            return ['success' => false, 'message' => 'Question not found'];
        }
        
        // Answer question
        $result = Question::answerQuestion($question_id, $answer_th, $answer_en, $answer_my, $_SESSION['user_id']);
        
        // Mark as FAQ if requested
        if ($result['success'] && $make_faq) {
            Question::markAsFaq($question_id, true);
        }
        
        return $result;
    }
    
    /**
     * Add comment to question
     * POST /api/qa_comment.php
     * 
     * Parameters:
     * - question_id (required)
     * - comment_text (required)
     * - is_anonymous (optional): 1 or 0
     */
    public static function addComment() {
        if (!AuthController::isAuthenticated()) {
            return ['success' => false, 'message' => 'Authentication required'];
        }
        
        $question_id = intval($_POST['question_id'] ?? 0);
        $comment_text = trim($_POST['comment_text'] ?? '');
        $is_anonymous = isset($_POST['is_anonymous']) ? 1 : 0;
        
        if ($question_id <= 0 || empty($comment_text)) {
            return ['success' => false, 'message' => 'Missing required fields'];
        }
        
        // Verify question exists
        $question = Question::getById($question_id);
        if (!$question) {
            return ['success' => false, 'message' => 'Question not found'];
        }
        
        // Check minimum length
        if (strlen($comment_text) < 10) {
            return ['success' => false, 'message' => 'Comment too short (minimum 10 characters)'];
        }
        
        // Prepare data
        $author_id = $is_anonymous ? null : $_SESSION['user_id'];
        $author_name = $is_anonymous ? 'Anonymous' : ($_SESSION['full_name_th'] ?? 'User');
        $is_admin = in_array($_SESSION['role'], ['admin', 'officer']) ? 1 : 0;
        
        // Add comment
        $result = Question::addComment(
            $question_id,
            $comment_text,
            $author_id,
            $author_name,
            $is_admin
        );
        
        return $result;
    }
    
    /**
     * Vote on question or comment
     * POST /api/qa_vote.php
     * 
     * Parameters:
     * - question_id or comment_id (required)
     * - vote_type (required): helpful, not_helpful, useful, not_useful
     */
    public static function vote() {
        // Don't require auth - allow anonymous voting
        $question_id = intval($_POST['question_id'] ?? 0);
        $comment_id = intval($_POST['comment_id'] ?? 0);
        $vote_type = $_POST['vote_type'] ?? '';
        
        if (($question_id <= 0 && $comment_id <= 0) || empty($vote_type)) {
            return ['success' => false, 'message' => 'Invalid parameters'];
        }
        
        // Validate vote type
        $valid_types = ['helpful', 'not_helpful', 'useful', 'not_useful'];
        if (!in_array($vote_type, $valid_types)) {
            return ['success' => false, 'message' => 'Invalid vote type'];
        }
        
        // Get voter info
        $employee_id = AuthController::isAuthenticated() ? $_SESSION['user_id'] : null;
        $voter_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Record vote
        $result = Question::vote(
            $question_id ?: null,
            $comment_id ?: null,
            $employee_id,
            $voter_ip,
            $vote_type
        );
        
        return $result;
    }
    
    /**
     * Search questions
     * GET /api/qa_search.php
     * 
     * Parameters:
     * - search (optional): Search term
     * - category_id (optional): Filter by category
     * - is_answered (optional): 0=unanswered, 1=answered
     * - is_faq (optional): 1 for FAQs only
     * - page (optional): Page number
     * - language (optional): th, en, my
     */
    public static function search() {
        // Get parameters
        $search = $_GET['search'] ?? null;
        $category_id = intval($_GET['category_id'] ?? 0);
        $is_answered = isset($_GET['is_answered']) ? intval($_GET['is_answered']) : null;
        $is_faq = isset($_GET['is_faq']) ? 1 : null;
        $language = $_GET['language'] ?? 'th';
        
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = intval($_GET['limit'] ?? 20);
        $offset = ($page - 1) * $limit;
        
        // Build filters
        $filters = [];
        
        if (!empty($search)) {
            $filters['search'] = $search;
        }
        
        if ($category_id > 0) {
            $filters['category_id'] = $category_id;
        }
        
        if ($is_answered !== null) {
            $filters['is_answered'] = $is_answered;
        }
        
        if ($is_faq) {
            $filters['is_faq'] = 1;
        }
        
        // Search
        $questions = Question::search($filters, $language, $limit, $offset);
        
        // Get categories for filter options
        $categories = Question::getCategories();
        
        return [
            'success' => true,
            'questions' => $questions,
            'categories' => $categories,
            'page' => $page,
            'limit' => $limit,
            'total' => count($questions) // Note: This should be total from DB
        ];
    }
    
    /**
     * List questions (with filtering)
     * GET /views/employee/qa_help.php
     */
    public static function listQuestions() {
        // Get parameters
        $category_id = intval($_GET['category_id'] ?? 0);
        $language = $_SESSION['language'] ?? 'th';
        $sort = $_GET['sort'] ?? 'latest'; // latest, popular, unanswered
        
        // Build filters
        $filters = [];
        
        if ($category_id > 0) {
            $filters['category_id'] = $category_id;
        }
        
        if ($sort === 'unanswered') {
            $filters['is_answered'] = 0;
        } elseif ($sort === 'faq') {
            $filters['is_faq'] = 1;
        }
        
        // Get questions
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $questions = Question::search($filters, $language, $limit, $offset);
        
        // Get categories
        $categories = Question::getCategories();
        
        // Get statistics
        $stats = Question::getStatistics();
        
        return [
            'success' => true,
            'questions' => $questions,
            'categories' => $categories,
            'stats' => $stats,
            'page' => $page,
            'limit' => $limit
        ];
    }
    
    /**
     * Get question details with comments
     * GET /api/qa_detail.php
     * 
     * Parameters:
     * - question_id (required)
     * - language (optional): th, en, my
     */
    public static function getDetails() {
        $question_id = intval($_GET['question_id'] ?? 0);
        $language = $_GET['language'] ?? 'th';
        
        if ($question_id <= 0) {
            return ['success' => false, 'message' => 'Invalid question ID'];
        }
        
        // Get question
        $question = Question::getById($question_id, $language);
        
        if (!$question) {
            return ['success' => false, 'message' => 'Question not found'];
        }
        
        // Get comments
        $comments = Question::getComments($question_id);
        
        return [
            'success' => true,
            'question' => $question,
            'comments' => $comments
        ];
    }
    
    /**
     * Admin Q&A Management
     * GET /views/admin/qa_management.php
     */
    public static function manageQuestions() {
        AuthController::requireRole(['admin', 'officer']);
        
        $filter_type = $_GET['filter'] ?? 'all'; // all, unanswered, faqs, pinned
        $language = $_SESSION['language'] ?? 'th';
        
        $filters = [];
        
        if ($filter_type === 'unanswered') {
            $filters['is_answered'] = 0;
        } elseif ($filter_type === 'faqs') {
            $filters['is_faq'] = 1;
        } elseif ($filter_type === 'pinned') {
            $filters['is_pinned'] = 1;
        }
        
        // Get questions
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $questions = Question::search($filters, $language, $limit, $offset);
        
        // Get statistics
        $stats = Question::getStatistics();
        
        return [
            'success' => true,
            'questions' => $questions,
            'stats' => $stats,
            'filter_type' => $filter_type,
            'page' => $page
        ];
    }
    
    /**
     * Pin/Unpin question
     * POST /api/qa_pin.php
     */
    public static function togglePin() {
        AuthController::requireRole(['admin']);
        
        $question_id = intval($_POST['question_id'] ?? 0);
        $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
        
        if ($question_id <= 0) {
            return ['success' => false, 'message' => 'Invalid question ID'];
        }
        
        return Question::pin($question_id, $is_pinned);
    }
    
    /**
     * Mark/Unmark as FAQ
     * POST /api/qa_faq.php
     */
    public static function toggleFaq() {
        AuthController::requireRole(['admin']);
        
        $question_id = intval($_POST['question_id'] ?? 0);
        $is_faq = isset($_POST['is_faq']) ? 1 : 0;
        
        if ($question_id <= 0) {
            return ['success' => false, 'message' => 'Invalid question ID'];
        }
        
        return Question::markAsFaq($question_id, $is_faq);
    }
}

// API Endpoint Usage Example
/*
// Ask question
$result = QaController::askQuestion();
echo json_encode($result);

// Answer question
$result = QaController::answerQuestion();
echo json_encode($result);

// Add comment
$result = QaController::addComment();
echo json_encode($result);

// Vote
$result = QaController::vote();
echo json_encode($result);

// Search
$result = QaController::search();
echo json_encode($result);
*/
?>