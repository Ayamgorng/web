<?php
defined("BASEPATH") or exit("No direct script access allowed.");
require_once BASEPATH.'helpers/default_helper.php';
require_once BASEPATH.'database.php';

if (!function_exists('load')) {
	function load($type, $name) {
		global $db;
		if ($type == 'helper') {
			$location = BASEPATH.'helpers/'.$name.'_helper.php';
		} else if ($type == 'middleware') {
			$location = BASEPATH.'middlewares/'.$name.'_middleware.php';
		} else {
			return die("Type [{$type}] not found.");
		}

		return (file_exists($location)) ? require_once($location):die(ucfirst("{$type} [{$name}] not found."));
	}
}

function opt_get($opt_name) {
    global $db;
    
    $opt_name = base64_decode($opt_name);
    
    // Use prepared statement for security
    $stmt = $db->prepare("SELECT opt_value FROM options WHERE opt_name = ?");
    $stmt->bind_param("s", $opt_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        return $result->fetch_assoc()['opt_value'];
    } else {
        return '-';
    }
}

function opt_update($opt_name, $opt_value) {
    global $db;
    
    $opt_name = base64_decode($opt_name);
    
    // Use prepared statement for security
    $stmt = $db->prepare("UPDATE options SET opt_value = ? WHERE opt_name = ?");
    $stmt->bind_param("ss", $opt_value, $opt_name);
    return $stmt->execute();
}

// Enhanced security functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function generate_secure_token($length = 32) {
    return bin2hex(random_bytes($length));
}

function hash_password($password) {
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ]);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function rate_limit($identifier, $max_attempts = 5, $time_window = 300) {
    global $db;
    
    $current_time = time();
    $window_start = $current_time - $time_window;
    
    // Clean old attempts
    $stmt = $db->prepare("DELETE FROM rate_limits WHERE identifier = ? AND attempt_time < ?");
    $stmt->bind_param("si", $identifier, $window_start);
    $stmt->execute();
    
    // Count current attempts
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM rate_limits WHERE identifier = ?");
    $stmt->bind_param("s", $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    
    if ($count >= $max_attempts) {
        return false;
    }
    
    // Record this attempt
    $stmt = $db->prepare("INSERT INTO rate_limits (identifier, attempt_time) VALUES (?, ?)");
    $stmt->bind_param("si", $identifier, $current_time);
    $stmt->execute();
    
    return true;
}

date_default_timezone_set('Asia/Jakarta');
error_reporting((config('web', 'environment') == 'development') ? E_ALL:0);
ini_set("display_errors", (config('web', 'environment') == 'development') ? 1:0);
if (!isset($_SESSION['csrf_token'])) generate_csrf_token();
if (isset($_SESSION['user'])) { $session = $_SESSION['user']['username']; }
function LannRed($red) { return '<font color="red"><pre>'.$red.'</pre></font>'; }
function LannGreen($green) { return '<font color="green"><pre>'.$green.'</pre></font>'; } 
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
