<?php
defined("BASEPATH") or exit("No direct script access allowed.");

// Set JSON header
header('Content-Type: application/json');

// Load language helper
require_once BASEPATH.'helpers/language_helper.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

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
        case 'current':
            getCurrentLanguage();
            break;
        case 'available':
            getAvailableLanguages();
            break;
        case 'translations':
            getTranslations();
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
        case 'set_language':
            setLanguage($input);
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
}

/**
 * Mendapatkan bahasa saat ini
 */
function getCurrentLanguage() {
    $current = get_current_language();
    $languages = get_available_languages();
    
    echo json_encode([
        'success' => true,
        'current_language' => $current,
        'language_info' => $languages[$current] ?? null
    ]);
}

/**
 * Mendapatkan daftar bahasa yang tersedia
 */
function getAvailableLanguages() {
    $languages = get_available_languages();
    
    echo json_encode([
        'success' => true,
        'languages' => $languages
    ]);
}

/**
 * Mendapatkan terjemahan untuk bahasa tertentu
 */
function getTranslations() {
    $lang = $_GET['lang'] ?? get_current_language();
    $keys = $_GET['keys'] ?? null;
    
    $lm = get_language_manager();
    
    if ($keys) {
        // Ambil terjemahan untuk key tertentu saja
        $key_array = explode(',', $keys);
        $translations = [];
        foreach ($key_array as $key) {
            $translations[trim($key)] = $lm->get(trim($key));
        }
    } else {
        // Ambil semua terjemahan (untuk development/debugging)
        $translations = [
            'home' => lang('home'),
            'services' => lang('services'),
            'price_list' => lang('price_list'),
            'check_transaction' => lang('check_transaction'),
            'deposit' => lang('deposit'),
            'voucher' => lang('voucher'),
            'flash_sale' => lang('flash_sale'),
            'support' => lang('support'),
            'login' => lang('login'),
            'register' => lang('register'),
            'logout' => lang('logout'),
            'profile' => lang('profile'),
            'admin_panel' => lang('admin_panel'),
            'dashboard' => lang('dashboard'),
            'welcome_back' => lang('welcome_back'),
            'balance' => lang('balance'),
            'total_transactions' => lang('total_transactions'),
            'pending_orders' => lang('pending_orders'),
            'recent_activities' => lang('recent_activities')
        ];
    }
    
    echo json_encode([
        'success' => true,
        'language' => $lang,
        'translations' => $translations
    ]);
}

/**
 * Mengatur bahasa
 */
function setLanguage($input) {
    $lang_code = sanitize_input($input['language'] ?? '');
    
    if (empty($lang_code)) {
        echo json_encode([
            'success' => false,
            'message' => 'Kode bahasa tidak boleh kosong'
        ]);
        return;
    }
    
    $available_languages = get_available_languages();
    
    if (!array_key_exists($lang_code, $available_languages)) {
        echo json_encode([
            'success' => false,
            'message' => 'Bahasa tidak didukung'
        ]);
        return;
    }
    
    // Set bahasa baru
    set_language($lang_code);
    
    echo json_encode([
        'success' => true,
        'message' => 'Bahasa berhasil diubah',
        'current_language' => $lang_code,
        'language_info' => $available_languages[$lang_code]
    ]);
}

/**
 * Sanitize input untuk keamanan
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
?>
