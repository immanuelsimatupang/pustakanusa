<?php
session_start();

// Load security functions and database connection
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/two_factor_auth.php';

// Require user to be logged in
requireLogin();

// Initialize variables
$error_message = "";
$success_message = "";
$show_verification = false;
$qr_code_url = "";
$secret = "";
$backup_codes = [];

// Get user ID
$user_id = $_SESSION['user_id'];

// Initialize TwoFactorAuth
$twoFA = new TwoFactorAuth($conn);

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['setup_2fa'])) {
        // Generate new secret
        $secret = $twoFA->generateSecret();
        
        // Generate QR code URL
        $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        $qr_code_url = $twoFA->getQRCodeUrl($user['username'], $secret);
        $show_verification = true;
    }
    
    elseif (isset($_POST['verify_2fa'])) {
        $secret = $_POST['secret'] ?? '';
        $verification_code = $_POST['verification_code'] ?? '';
        
        // Verify the code
        if ($twoFA->verifyTOTP($secret, $verification_code)) {
            // Enable 2FA for the user
            if ($twoFA->enable2FA($user_id, $secret)) {
                // Generate backup codes
                $backup_codes = generateBackupCodes(10);
                
                // Store backup codes
                storeBackupCodes($user_id, $backup_codes);
                
                $success_message = "Two-factor authentication has been enabled successfully!";
                
                // Update session
                $_SESSION['two_factor_enabled'] = 1;
                
                // Log the 2FA enablement
                logSecurityEvent('2fa_enabled', ['user_id' => $user_id]);
            } else {
                $error_message = "Failed to enable 2FA. Please try again.";
            }
        } else {
            $error_message = "Invalid verification code. Please try again.";
        }
    }
    
    elseif (isset($_POST['disable_2fa'])) {
        $password = $_POST['password'] ?? '';
        
        // Verify password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            // Disable 2FA
            if ($twoFA->disable2FA($user_id)) {
                $success_message = "Two-factor authentication has been disabled.";
                
                // Update session
                $_SESSION['two_factor_enabled'] = 0;
                
                // Log the 2FA disablement
                logSecurityEvent('2fa_disabled', ['user_id' => $user_id]);
            } else {
                $error_message = "Failed to disable 2FA. Please try again.";
            }
        } else {
            $error_message = "Incorrect password. 2FA could not be disabled.";
        }
    }
}

// Check if 2FA is already enabled
$is_2fa_enabled = $twoFA->is2FAEnabled($user_id);
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
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="assets/img/logo.png" alt="PustakaNusa" height="40">
                <span class="ms-2 fw-bold">PustakaNusa</span>
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Two-Factor Authentication</h4>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                        <?php endif; ?>
                        
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                        <?php endif; ?>
                        
                        <?php if ($is_2fa_enabled): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                Two-factor authentication is currently <strong>enabled</strong> on your account.
                            </div>
                            
                            <div class="text-center mb-4">
                                <i class="fas fa-shield-alt text-success" style="font-size: 4rem;"></i>
                            </div>
                            
                            <form method="post" class="mt-4">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Enter your password to disable 2FA:</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <button type="submit" name="disable_2fa" class="btn btn-danger w-100">
                                    <i class="fas fa-times me-2"></i>Disable Two-Factor Authentication
                                </button>
                            </form>
                        <?php else: ?>
                            <?php if ($show_verification): ?>
                                <div class="text-center mb-4">
                                    <i class="fas fa-qrcode text-primary" style="font-size: 4rem;"></i>
                                </div>
                                
                                <h5 class="text-center mb-4">Scan QR Code with Authenticator App</h5>
                                
                                <div class="text-center mb-4">
                                    <img src="<?php echo $qr_code_url; ?>" alt="QR Code" class="img-fluid" style="max-width: 200px;">
                                </div>
                                
                                <p class="text-center text-muted mb-4">
                                    Use an authenticator app (like Google Authenticator or Authy) to scan the QR code above.
                                </p>
                                
                                <form method="post">
                                    <input type="hidden" name="secret" value="<?php echo htmlspecialchars($secret); ?>">
                                    <div class="mb-3">
                                        <label for="verification_code" class="form-label">Enter 6-digit code from your app:</label>
                                        <input type="text" class="form-control" id="verification_code" name="verification_code" maxlength="6" required>
                                    </div>
                                    <button type="submit" name="verify_2fa" class="btn btn-success w-100">
                                        <i class="fas fa-check me-2"></i>Verify and Enable 2FA
                                    </button>
                                </form>
                                
                                <div class="text-center mt-3">
                                    <a href="?cancel=1" class="btn btn-link">Cancel Setup</a>
                                </div>
                            <?php else: ?>
                                <div class="text-center mb-4">
                                    <i class="fas fa-shield-alt text-success" style="font-size: 4rem;"></i>
                                </div>
                                
                                <h5 class="text-center mb-4">Enable Two-Factor Authentication</h5>
                                
                                <p class="text-center text-muted">
                                    Two-factor authentication adds an extra layer of security to your account by requiring a second form of verification.
                                </p>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    You'll need an authenticator app (like Google Authenticator, Authy, or Microsoft Authenticator) to use this feature.
                                </div>
                                
                                <form method="post">
                                    <button type="submit" name="setup_2fa" class="btn btn-success w-100">
                                        <i class="fas fa-plus me-2"></i>Setup Two-Factor Authentication
                                    </button>
                                </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>