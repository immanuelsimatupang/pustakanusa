<?php
/**
 * Two-Factor Authentication System for PustakaNusa
 * Provides TOTP-based 2FA functionality
 */

require_once __DIR__ . '/security.php';

class TwoFactorAuth {
    private $conn;
    
    public function __construct($database_connection) {
        $this->conn = $database_connection;
    }
    
    /**
     * Generate a secret key for TOTP
     */
    public function generateSecret() {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Generate TOTP code based on secret and time
     */
    public function generateTOTP($secret, $time = null) {
        if ($time === null) {
            $time = time();
        }
        
        // Calculate time window (30 seconds)
        $timeWindow = floor($time / 30);
        
        // Create the HMAC hash
        $hash = hash_hmac('sha1', pack('N*', $timeWindow), hex2bin($secret));
        
        // Dynamic truncation
        $offset = ord($hash[19]) & 0xf;
        $truncatedHash = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;
        
        // Pad with zeros if necessary
        return str_pad($truncatedHash, 6, '0', STR_PAD_LEFT);
    }
    
    /**
     * Verify TOTP code
     */
    public function verifyTOTP($secret, $code, $window = 1) {
        $currentTime = time();
        
        // Check current window and surrounding windows
        for ($i = -$window; $i <= $window; $i++) {
            $time = $currentTime + ($i * 30);
            if (hash_equals($this->generateTOTP($secret, $time), $code)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Enable 2FA for a user
     */
    public function enable2FA($userId, $secret) {
        $stmt = $this->conn->prepare("UPDATE users SET two_factor_secret = ?, two_factor_enabled = 1 WHERE id = ?");
        $stmt->bind_param("si", $secret, $userId);
        return $stmt->execute();
    }
    
    /**
     * Disable 2FA for a user
     */
    public function disable2FA($userId) {
        $stmt = $this->conn->prepare("UPDATE users SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }
    
    /**
     * Check if 2FA is enabled for a user
     */
    public function is2FAEnabled($userId) {
        $stmt = $this->conn->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['two_factor_enabled'] == 1;
        }
        
        return false;
    }
    
    /**
     * Get user's 2FA secret
     */
    public function getSecret($userId) {
        $stmt = $this->conn->prepare("SELECT two_factor_secret FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['two_factor_secret'];
        }
        
        return null;
    }
    
    /**
     * Generate QR code URL for Google Authenticator
     */
    public function getQRCodeUrl($username, $secret, $issuer = 'PustakaNusa') {
        $url = 'otpauth://totp/' . $issuer . ':' . $username . '?secret=' . $secret . '&issuer=' . $issuer;
        return 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . urlencode($url);
    }
}

/**
 * Generate backup codes for 2FA recovery
 */
function generateBackupCodes($count = 10) {
    $codes = [];
    for ($i = 0; $i < $count; $i++) {
        $codes[] = bin2hex(random_bytes(4)); // 8 character hex codes
    }
    return $codes;
}

/**
 * Store backup codes in database
 */
function storeBackupCodes($userId, $codes) {
    global $conn;
    $codes_json = json_encode($codes);
    
    $stmt = $conn->prepare("UPDATE users SET backup_codes = ? WHERE id = ?");
    $stmt->bind_param("si", $codes_json, $userId);
    return $stmt->execute();
}

/**
 * Verify a backup code
 */
function verifyBackupCode($userId, $code) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT backup_codes FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $backup_codes = json_decode($row['backup_codes'], true);
        
        if ($backup_codes && in_array($code, $backup_codes)) {
            // Remove used code
            $new_codes = array_diff($backup_codes, [$code]);
            $new_codes_json = json_encode(array_values($new_codes));
            
            $update_stmt = $conn->prepare("UPDATE users SET backup_codes = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_codes_json, $userId);
            $update_stmt->execute();
            
            return true;
        }
    }
    
    return false;
}
?>