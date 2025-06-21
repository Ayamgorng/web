<?php
defined("BASEPATH") or exit("No direct script access allowed.");

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Load voucher helper
require_once BASEPATH.'helpers/voucher_helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// CSRF protection
if ($method === 'POST' && !validate_csrf_token($input['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

switch ($method) {
    case 'GET':
        handleGetRequest();
        break;
    case 'POST':
        handlePostRequest($input);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

function handleGetRequest() {
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'list':
            getVoucherList();
            break;
        case 'user_vouchers':
            getUserVouchers();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

function handlePostRequest($input) {
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'check':
            checkVoucher($input);
            break;
        case 'apply':
            applyVoucher($input);
            break;
        case 'create':
            createVoucher($input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

function getVoucherList() {
    $vm = get_voucher_manager();
    $vouchers = $vm->getActiveVouchers(20);
    
    echo json_encode([
        'success' => true,
        'vouchers' => $vouchers
    ]);
}

function getUserVouchers() {
    global $db;
    $user_id = $_SESSION['user']['id'];
    
    // Get vouchers used by user
    $stmt = $db->prepare("
        SELECT v.*, vu.used_at, vu.discount_amount as used_discount
        FROM vouchers v
        JOIN voucher_usage vu ON v.id = vu.voucher_id
        WHERE vu.user_id = ?
        ORDER BY vu.used_at DESC
        LIMIT 20
    ");
    $stmt->execute([$user_id]);
    $used_vouchers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'used_vouchers' => $used_vouchers
    ]);
}

function checkVoucher($input) {
    $code = sanitize_input($input['code'] ?? '');
    $amount = floatval($input['amount'] ?? 0);
    
    if (empty($code)) {
        echo json_encode([
            'valid' => false,
            'message' => 'Kode voucher tidak boleh kosong'
        ]);
        return;
    }
    
    $validation = validate_voucher_code($code, $amount);
    
    if ($validation['valid']) {
        $voucher = $validation['voucher'];
        $vm = get_voucher_manager();
        $discount = $vm->calculateDiscount($voucher, $amount);
        
        echo json_encode([
            'valid' => true,
            'voucher' => $voucher,
            'discount_amount' => $discount,
            'final_amount' => max(0, $amount - $discount)
        ]);
    } else {
        echo json_encode($validation);
    }
}

function applyVoucher($input) {
    $code = sanitize_input($input['code'] ?? '');
    $user_id = $_SESSION['user']['id'];
    $order_id = sanitize_input($input['order_id'] ?? '');
    $amount = floatval($input['amount'] ?? 0);
    
    if (empty($code) || empty($order_id) || $amount <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Data tidak lengkap'
        ]);
        return;
    }
    
    $result = apply_voucher_discount($code, $user_id, $order_id, $amount);
    echo json_encode($result);
}

function createVoucher($input) {
    // Check admin permission
    if ($_SESSION['user']['level'] !== 'Admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Admin access required']);
        return;
    }
    
    $vm = get_voucher_manager();
    
    $data = [
        'code' => sanitize_input($input['code'] ?? $vm->generateVoucherCode()),
        'type' => in_array($input['type'], ['percentage', 'fixed']) ? $input['type'] : 'percentage',
        'value' => floatval($input['value'] ?? 0),
        'min_purchase' => floatval($input['min_purchase'] ?? 0),
        'max_discount' => !empty($input['max_discount']) ? floatval($input['max_discount']) : null,
        'usage_limit' => !empty($input['usage_limit']) ? intval($input['usage_limit']) : null,
        'valid_from' => $input['valid_from'] ?? date('Y-m-d H:i:s'),
        'valid_until' => $input['valid_until'] ?? date('Y-m-d H:i:s', strtotime('+30 days')),
        'created_by' => $_SESSION['user']['username']
    ];
    
    if ($vm->createVoucher($data)) {
        echo json_encode([
            'success' => true,
            'message' => 'Voucher berhasil dibuat',
            'voucher_code' => $data['code']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Gagal membuat voucher'
        ]);
    }
}
?>
