<?php
/**
 * Minimal endpoint to approve content materials
 * Only updates approval_status field - no other changes
 * Uses PDO like other working endpoints
 */

// Set headers first to ensure JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Suppress any HTML error output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Load PDO connection using same method as working endpoints
try {
    require_once __DIR__ . '/../src/Config/db_connect.php';
    
    // Check if PDO connection was successful
    if (!isset($pdo) || !$pdo) {
        http_response_code(500);
        echo json_encode(['error' => 'Database connection failed: PDO not initialized']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit;
} catch (Error $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get content ID from request
$input = json_decode(file_get_contents('php://input'), true);
$contentId = isset($input['id']) ? (int)$input['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : null);

if (!$contentId) {
    http_response_code(400);
    echo json_encode(['error' => 'Content ID is required']);
    exit;
}

try {
    // Check if last_updated column exists before using it
    $columnCheck = $pdo->query("SHOW COLUMNS FROM campaign_department_content_items LIKE 'last_updated'")->fetch();
    $hasLastUpdated = !empty($columnCheck);
    
    // Only update approval_status to 'approved' (minimal update, no other fields)
    if ($hasLastUpdated) {
        $stmt = $pdo->prepare("UPDATE campaign_department_content_items SET approval_status = 'approved', last_updated = NOW() WHERE id = :id");
    } else {
        $stmt = $pdo->prepare("UPDATE campaign_department_content_items SET approval_status = 'approved' WHERE id = :id");
    }
    
    $stmt->execute(['id' => $contentId]);
    
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Content not found']);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Content approved successfully',
        'id' => $contentId
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

