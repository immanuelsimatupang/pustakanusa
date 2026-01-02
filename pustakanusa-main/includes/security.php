<?php
/**
 * Security Middleware for PustakaNusa E-commerce Platform
 * Provides common security functions and protections
 */

// Define BASEPATH if not already defined
if (!defined('BASEPATH')) {
    define('BASEPATH', 1);
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Sanitize Input
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate Email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate Phone Number
 */
function validatePhone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

/**
 * Validate Indonesian Postal Code
 */
function validatePostalCode($postalCode) {
    return preg_match('/^[0-9]{5}$/', $postalCode);
}

/**
 * Secure Session Start
 */
function secureSessionStart() {
    // Set secure session configuration
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // Only if using HTTPS
    ini_set('session.use_strict_mode', 1);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Regenerate session ID to prevent session fixation
    if (!isset($_SESSION['initialized'])) {
        session_regenerate_id(true);
        $_SESSION['initialized'] = true;
        $_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
    } else {
        // Validate session hasn't been hijacked
        if (($_SESSION['IPaddress'] != ($_SERVER['REMOTE_ADDR'] ?? '')) || 
            ($_SESSION['userAgent'] != ($_SERVER['HTTP_USER_AGENT'] ?? ''))) {
            session_destroy();
            header("Location: login.php");
            exit;
        }
    }
}

/**
 * Require User Login
 */
function requireLogin() {
    secureSessionStart();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Require Admin Access
 */
function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        header("Location: index.php");
        exit;
    }
}

/**
 * Add Security Headers
 */
function addSecurityHeaders() {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("Referrer-Policy: strict-origin-when-cross-origin");
    header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
}

/**
 * Log Security Event
 */
function logSecurityEvent($event, $details = '') {
    $logFile = 'logs/security-' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logEntry = "[$timestamp] IP: $ip | User-Agent: $userAgent | Event: $event | Details: $details" . PHP_EOL;
    error_log($logEntry, 3, $logFile);
}

/**
 * Rate Limiting Function
 */
function checkRateLimit($identifier, $maxRequests = 10, $timeWindow = 300) { // 300 seconds = 5 minutes
    $rateLimitFile = 'logs/rate_limit_' . md5($identifier) . '.log';
    
    if (!file_exists($rateLimitFile)) {
        file_put_contents($rateLimitFile, time() . ':' . 1);
        return true;
    }
    
    $data = file_get_contents($rateLimitFile);
    list($lastRequestTime, $requestCount) = explode(':', $data);
    
    $currentTime = time();
    
    // If within time window
    if ($currentTime - $lastRequestTime < $timeWindow) {
        if ($requestCount >= $maxRequests) {
            return false; // Rate limit exceeded
        } else {
            // Increment request count
            file_put_contents($rateLimitFile, $lastRequestTime . ':' . ($requestCount + 1));
            return true;
        }
    } else {
        // Reset counter for new time window
        file_put_contents($rateLimitFile, $currentTime . ':' . 1);
        return true;
    }
}

/**
 * Validate User Input Against Common Attack Patterns
 */
function isPotentialAttack($input) {
    $attackPatterns = [
        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',  // XSS
        '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',  // XSS
        '/(union\s+select|select.*from|insert\s+into|update|delete|drop\s+table|create\s+table|alter\s+table)/i',  // SQL Injection
        '/(cmd\.exe|shell32|mshta|powershell)/i',  // Command Injection
        '/(\.\.\/|\.\.\\)/',  // Directory Traversal
    ];
    
    foreach ($attackPatterns as $pattern) {
        if (preg_match($pattern, $input)) {
            return true;
        }
    }
    
    return false;
}

/**
 * Generate Secure Random String
 */
function generateSecureRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Validate Password Strength
 */
function validatePasswordStrength($password) {
    // Minimal 8 karakter dengan huruf besar, huruf kecil, angka, dan karakter spesial
    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}

// Add security headers automatically
addSecurityHeaders();

// Initialize secure session
secureSessionStart();
?>