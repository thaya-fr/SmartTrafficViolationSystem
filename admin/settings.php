<?php
/**
 * TrafficLens AI — Admin Account Settings
 * Allows updating admin username, email, and password.
 */
$page_title = 'Account Settings';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/db.php';

$admin_id = $_SESSION['admin_id'];
$success = '';
$error = '';

// Fetch current admin profile
try {
    $stmt = $pdo->prepare("SELECT admin_id, username, email, password_hash FROM admins WHERE admin_id = :id LIMIT 1");
    $stmt->execute([':id' => $admin_id]);
    $admin = $stmt->fetch();

    if (!$admin) {
        // Fallback: If session admin_id not found, fetch by username
        $stmt = $pdo->prepare("SELECT admin_id, username, email, password_hash FROM admins WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $_SESSION['username']]);
        $admin = $stmt->fetch();
        if ($admin) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            $admin_id = $admin['admin_id'];
        }
    }
} catch (PDOException $e) {
    error_log("Settings fetch error: " . $e->getMessage());
    $admin = null;
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email)) {
        $error = 'Username and Email Address are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check if username is taken by another admin
            $chk_user = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = :u AND admin_id != :id");
            $chk_user->execute([':u' => $username, ':id' => $admin_id]);
            if ($chk_user->fetchColumn() > 0) {
                $error = 'Username is already taken by another account.';
            }

            // Check if email is taken by another admin
            if (!$error) {
                $chk_email = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = :e AND admin_id != :id");
                $chk_email->execute([':e' => $email, ':id' => $admin_id]);
                if ($chk_email->fetchColumn() > 0) {
                    $error = 'Email address is already registered to another account.';
                }
            }

            // If changing password or current password supplied
            $is_password_change = !empty($new_password);
            if (!$error && ($is_password_change || !empty($current_password))) {
                if (empty($current_password)) {
                    $error = 'Please enter your current password to save security changes.';
                } elseif (!password_verify($current_password, $admin['password_hash'])) {
                    $error = 'Current password is incorrect.';
                } elseif ($is_password_change) {
                    if (strlen($new_password) < 6) {
                        $error = 'New password must be at least 6 characters long.';
                    } elseif ($new_password !== $confirm_password) {
                        $error = 'New password and confirmation do not match.';
                    }
                }
            }

            // Update database if no error
            if (!$error) {
                if ($is_password_change) {
                    $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
                    $upd = $pdo->prepare("UPDATE admins SET username = :u, email = :e, password_hash = :hash WHERE admin_id = :id");
                    $upd->execute([':u' => $username, ':e' => $email, ':hash' => $new_hash, ':id' => $admin_id]);
                } else {
                    $upd = $pdo->prepare("UPDATE admins SET username = :u, email = :e WHERE admin_id = :id");
                    $upd->execute([':u' => $username, ':e' => $email, ':id' => $admin_id]);
                }

                // Update session variables
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;

                // Re-fetch admin details
                $stmt->execute([':id' => $admin_id]);
                $admin = $stmt->fetch();

                $success = 'Account settings updated successfully!';
            }
        } catch (PDOException $e) {
            error_log("Settings update error: " . $e->getMessage());
            $error = 'Failed to update account settings. Please try again.';
        }
    }
}
?>

<div style="max-width: 900px; margin: 0 auto;">
    
    <!-- Success Alert -->
    <?php if ($success): ?>
        <div class="toast toast-success mb-24" style="position: static; animation: none; width: 100%; max-width: 100%;">
            <i class="fas fa-check-circle toast-icon"></i>
            <div><?php echo htmlspecialchars($success); ?></div>
        </div>
    <?php endif; ?>

    <!-- Error Alert -->
    <?php if ($error): ?>
        <div class="toast toast-error mb-24" style="position: static; animation: none; width: 100%; max-width: 100%;">
            <i class="fas fa-exclamation-circle toast-icon"></i>
            <div><?php echo htmlspecialchars($error); ?></div>
        </div>
    <?php endif; ?>

    <form method="POST" action="settings.php">
        <div class="form-grid single-col gap-24">
            
            <!-- Card 1: Account Information -->
            <div class="card">
                <div class="card-header">
                    <div class="flex items-center gap-12">
                        <i class="fas fa-user-circle" style="color: #00e5ff; font-size: 20px;"></i>
                        <h3 class="card-title">Profile Information</h3>
                    </div>
                    <span class="text-meta">Account ID: <?php echo htmlspecialchars(substr($admin['admin_id'] ?? '1000', 0, 8)); ?></span>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="username">
                            Username <span class="required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($_POST['username'] ?? $admin['username'] ?? 'admin'); ?>" 
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">
                            Email Address <span class="required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($_POST['email'] ?? $admin['email'] ?? 'admin@trafficlens.ai'); ?>" 
                            required
                        >
                    </div>
                </div>
            </div>

            <!-- Card 2: Security & Password -->
            <div class="card">
                <div class="card-header">
                    <div class="flex items-center gap-12">
                        <i class="fas fa-shield-alt" style="color: #00e5ff; font-size: 20px;"></i>
                        <h3 class="card-title">Change Password</h3>
                    </div>
                    <span class="text-meta">Optional — Leave blank to keep current password</span>
                </div>

                <div class="form-group mb-20">
                    <label class="form-label" for="current_password">
                        Current Password
                    </label>
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        class="form-input" 
                        placeholder="Enter current password to save changes"
                    >
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="new_password">
                            New Password
                        </label>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            class="form-input" 
                            placeholder="Enter new password (min. 6 chars)"
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">
                            Confirm New Password
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-input" 
                            placeholder="Confirm new password"
                        >
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between" style="padding-top: 8px;">
                <a href="../dashboard/index.php" class="btn-ghost">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Save Settings
                </button>
            </div>

        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
