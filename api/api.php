<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

// Set JSON response headers
header('Content-Type: application/json');

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Authenticate API request
authenticateAPI();

// Get request parameters
$resource = $_GET['resource'] ?? '';
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Get request body for POST/PUT requests
$requestBody = json_decode(file_get_contents('php://input'), true);

try {
    switch ($resource) {
        case 'leads':
            handleLeadsRequest($method, $action, $requestBody);
            break;
            
        case 'opportunities':
            handleOpportunitiesRequest($method, $action, $requestBody);
            break;
            
        case 'contacts':
            handleContactsRequest($method, $action, $requestBody);
            break;
            
        case 'tasks':
            handleTasksRequest($method, $action, $requestBody);
            break;
            
        case 'reports':
            handleReportsRequest($method, $action, $requestBody);
            break;
            
        default:
            throw new Exception('Invalid resource requested');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleLeadsRequest($method, $action, $data) {
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                $leads = fetchAll("SELECT * FROM leads ORDER BY created_at DESC");
                echo json_encode(['success' => true, 'data' => $leads]);
            } elseif ($action === 'get' && isset($_GET['id'])) {
                $lead = fetchOne("SELECT * FROM leads WHERE id = ?", [$_GET['id']]);
                echo json_encode(['success' => true, 'data' => $lead]);
            }
            break;
            
        case 'POST':
            if ($action === 'create') {
                $id = insert('leads', $data);
                if ($id) {
                    echo json_encode(['success' => true, 'id' => $id]);
                } else {
                    throw new Exception('Failed to create lead');
                }
            }
            break;
            
        case 'PUT':
            if ($action === 'update' && isset($_GET['id'])) {
                $success = update('leads', $data, 'id = ?', [$_GET['id']]);
                echo json_encode(['success' => $success]);
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete' && isset($_GET['id'])) {
                $success = delete('leads', 'id = ?', [$_GET['id']]);
                echo json_encode(['success' => $success]);
            }
            break;
    }
}

function handleOpportunitiesRequest($method, $action, $data) {
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                $opportunities = fetchAll("
                    SELECT o.*, l.company_name, u.name as assigned_to_name 
                    FROM opportunities o 
                    LEFT JOIN leads l ON o.lead_id = l.id 
                    LEFT JOIN users u ON o.assigned_to = u.id 
                    ORDER BY o.created_at DESC
                ");
                echo json_encode(['success' => true, 'data' => $opportunities]);
            } elseif ($action === 'get' && isset($_GET['id'])) {
                $opportunity = fetchOne("
                    SELECT o.*, l.company_name, u.name as assigned_to_name 
                    FROM opportunities o 
                    LEFT JOIN leads l ON o.lead_id = l.id 
                    LEFT JOIN users u ON o.assigned_to = u.id 
                    WHERE o.id = ?
                ", [$_GET['id']]);
                echo json_encode(['success' => true, 'data' => $opportunity]);
            }
            break;
            
        case 'POST':
            if ($action === 'create') {
                $id = insert('opportunities', $data);
                if ($id) {
                    echo json_encode(['success' => true, 'id' => $id]);
                } else {
                    throw new Exception('Failed to create opportunity');
                }
            }
            break;
            
        case 'PUT':
            if ($action === 'update' && isset($_GET['id'])) {
                $success = update('opportunities', $data, 'id = ?', [$_GET['id']]);
                echo json_encode(['success' => $success]);
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete' && isset($_GET['id'])) {
                $success = delete('opportunities', 'id = ?', [$_GET['id']]);
                echo json_encode(['success' => $success]);
            }
            break;
    }
}

function handleContactsRequest($method, $action, $data) {
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                $contacts = fetchAll("
                    SELECT c.*, l.company_name 
                    FROM contacts c 
                    LEFT JOIN leads l ON c.lead_id = l.id 
                    ORDER BY c.created_at DESC
                ");
                echo json_encode(['success' => true, 'data' => $contacts]);
            } elseif ($action === 'get' && isset($_GET['id'])) {
                $contact = fetchOne("
                    SELECT c.*, l.company_name 
                    FROM contacts c 
                    LEFT JOIN leads l ON c.lead_id = l.id 
                    WHERE c.id = ?
                ", [$_GET['id']]);
                echo json_encode(['success' => true, 'data' => $contact]);
            }
            break;
            
        case 'POST':
            if ($action === 'create') {
                $id = insert('contacts', $data);
                if ($id) {
                    echo json_encode(['success' => true, 'id' => $id]);
                } else {
                    throw new Exception('Failed to create contact');
                }
            }
            break;
            
        case 'PUT':
            if ($action === 'update' && isset($_GET['id'])) {
                $success = update('contacts', $data, 'id = ?', [$_GET['id']]);
                echo json_encode(['success' => $success]);
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete' && isset($_GET['id'])) {
                $success = delete('contacts', 'id = ?', [$_GET['id']]);
                echo json_encode(['success' => $success]);
            }
            break;
    }
}

function handleTasksRequest($method, $action, $data) {
    switch ($method) {
        case 'GET':
            if ($action === 'list') {
                $tasks = fetchAll("
                    SELECT t.*, u.name as assigned_to_name 
                    FROM tasks t 
                    LEFT JOIN users u ON t.assigned_to = u.id 
                    ORDER BY t.due_date ASC
                ");
                echo json_encode(['success' => true, 'data' => $tasks]);
            } elseif ($action === 'get' && isset($_GET['id'])) {
                $task = fetchOne("
                    SELECT t.*, u.name as assigned_to_name 
                    FROM tasks t 
                    LEFT JOIN users u ON t.assigned_to = u.id 
                    WHERE t.id = ?
                ", [$_GET['id']]);
                echo json_encode(['success' => true, 'data' => $task]);
            }
            break;
            
        case 'POST':
            if ($action === 'create') {
                $id = insert('tasks', $data);
                if ($id) {
                    echo json_encode(['success' => true, 'id' => $id]);
                } else {
                    throw new Exception('Failed to create task');
                }
            }
            break;
            
        case 'PUT':
            if ($action === 'update' && isset($_GET['id'])) {
                $success = update('tasks', $data, 'id = ?', [$_GET['id']]);
                echo json_encode(['success' => $success]);
            }
            break;
            
        case 'DELETE':
            if ($action === 'delete' && isset($_GET['id'])) {
                $success = delete('tasks', 'id = ?', [$_GET['id']]);
                echo json_encode(['success' => $success]);
            }
            break;
    }
}

function handleReportsRequest($method, $action, $data) {
    switch ($action) {
        case 'sales-summary':
            $summary = fetchOne("
                SELECT 
                    COUNT(DISTINCT o.id) as total_opportunities,
                    SUM(o.amount) as total_amount,
                    AVG(o.amount) as avg_amount,
                    COUNT(DISTINCT CASE WHEN o.stage = 'closed_won' THEN o.id END) as won_opportunities,
                    COUNT(DISTINCT CASE WHEN o.stage = 'closed_lost' THEN o.id END) as lost_opportunities
                FROM opportunities o
            ");
            echo json_encode(['success' => true, 'data' => $summary]);
            break;
            
        case 'pipeline':
            $pipeline = fetchAll("
                SELECT 
                    stage,
                    COUNT(*) as count,
                    SUM(amount) as total_amount
                FROM opportunities
                WHERE stage NOT IN ('closed_won', 'closed_lost')
                GROUP BY stage
                ORDER BY FIELD(stage, 'prospecting', 'qualification', 'proposal', 'negotiation')
            ");
            echo json_encode(['success' => true, 'data' => $pipeline]);
            break;
            
        case 'leads-by-status':
            $leadsByStatus = fetchAll("
                SELECT 
                    status,
                    COUNT(*) as count
                FROM leads
                GROUP BY status
            ");
            echo json_encode(['success' => true, 'data' => $leadsByStatus]);
            break;
    }
}
