<?php
session_start();

// Load security functions and database connection
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/two_factor_auth.php';

// Check if user is already logged in but not verified with 2FA
if (!isset($_SESSION['user_id']) || !isset($_SESSION['login_verified']) || !$_SESSION['login_verified']) {
    header("Location: login.php");
    exit;
}

// Initialize variables
$error_message = "";
$success_message = "";

// Get user ID
$user_id = $_SESSION['user_id'];

// Initialize TwoFactorAuth
$twoFA = new TwoFactorAuth($conn);

// Check if 2FA is enabled for this user
if (!$twoFA->is2FAEnabled($user_id)) {
    // If 2FA is not enabled, redirect to dashboard
    header("Location: dashboard.php");
    exit;
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $verification_code = $_POST['verification_code'] ?? '';
    $use_backup_code = isset($_POST['use_backup_code']);
    
    if ($use_backup_code) {
        // Use backup code verification
        if (verifyBackupCode($user_id, $verification_code)) {
            // Set 2FA verification in session
            $_SESSION['2fa_verified'] = true;
            
            // Log the 2FA verification using backup code
            logSecurityEvent('2fa_verified_backup', ['user_id' => $user_id]);
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $error_message = "Invalid backup code. Please try again.";
            logSecurityEvent('2fa_backup_failed', ['user_id' => $user_id]);
        }
    } else {
        // Get user's 2FA secret
        $secret = $twoFA->getSecret($user_id);
        
        if ($secret && $twoFA->verifyTOTP($secret, $verification_code)) {
            // Set 2FA verification in session
            $_SESSION['2fa_verified'] = true;
            
            // Log the 2FA verification
            logSecurityEvent('2fa_verified', ['user_id' => $user_id]);
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $error_message = "Invalid verification code. Please try again.";
            logSecurityEvent('2fa_verification_failed', ['user_id' => $user_id]);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - PustakaNusa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white text-center">
                        <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>
                        
                        <div class="text-center mb-4">
                            <i class="fas fa-lock text-success" style="font-size: 4rem;"></i>
                        </div>
                        
                        <h5 class="text-center mb-4">Enter Verification Code</h5>
                        
                        <p class="text-center text-muted mb-4">
                            Open your authenticator app and enter the 6-digit code.
                        </p>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="verification_code" class="form-label">Verification Code</label>
                                <input type="text" class="form-control form-control-lg text-center" id="verification_code" name="verification_code" maxlength="6" inputmode="numeric" required autofocus>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100 py-2">
                                <i class="fas fa-check me-2"></i>Verify Code
                            </button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <p class="text-muted">Can't access your authenticator app?</p>
                            <button type="button" class="btn btn-link text-success" data-bs-toggle="modal" data-bs-target="#backupModal">
                                Use Backup Code
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Code Modal -->
    <div class="modal fade" id="backupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Use Backup Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Enter one of your backup codes to access your account.</p>
                    
                    <form method="post" id="backupForm">
                        <div class="mb-3">
                            <label for="backup_code" class="form-label">Backup Code</label>
                            <input type="text" class="form-control" id="backup_code" name="verification_code" required>
                            <input type="hidden" name="use_backup_code" value="1">
                        </div>
                        
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check me-2"></i>Use Backup Code
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-focus on the input field when modal is shown
        document.getElementById('backupModal').addEventListener('shown.bs.modal', function () {
            document.getElementById('backup_code').focus();
        });
        
        // Auto-format the verification code input
        document.getElementById('verification_code').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Remove non-digit characters
            if (value.length > 6) {
                value = value.substring(0, 6);
            }
            e.target.value = value;
        });
    </script>
</body>
</html>