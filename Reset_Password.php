<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GreenTrack - Reset Password</title>
    <link rel="stylesheet" type="text/css" href="Login.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php 
require_once 'procedure.php';
require_once 'database.php';
session_start();

// Create database connection
$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = '';
$validToken = false;
$email = '';

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Verify token
    $stmt = $conn->prepare("CALL VerifyPasswordResetToken(?, @email, @valid)");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    $result = $conn->query("SELECT @email as email, @valid as valid");
    $row = $result->fetch_assoc();
    
    if ($row['valid']) {
        $validToken = true;
        $email = $row['email'];
    } else {
        $message = "Invalid or expired reset link. Please request a new one.";
        $messageType = "error";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $validToken) {
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    if ($newPassword === $confirmPassword) {
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $conn->prepare("CALL UpdateUserPassword(?, ?)");
        $stmt->bind_param("ss", $email, $hashedPassword);
        
        if ($stmt->execute()) {
            // Delete used token
            $stmt = $conn->prepare("CALL DeletePasswordResetToken(?)");
            $stmt->bind_param("s", $token);
            $stmt->execute();
            
            $message = "Password has been reset successfully. You can now login with your new password.";
            $messageType = "success";
            
            // Redirect to login page after 3 seconds
            header("refresh:3;url=login.php");
        } else {
            $message = "Failed to reset password. Please try again.";
            $messageType = "error";
        }
    } else {
        $message = "Passwords do not match.";
        $messageType = "error";
    }
}
?>

<header class="navbar">
    <div class="brand">
        <img src="LOGO.png" class="logo" alt="GreenTrack Logo" />
        <h1><span>GREEN</span>TRACK</h1>
    </div>
    <a class="button" href="Homepage.php">
        <i class="fa-solid fa-house"></i>
    </a>
</header>

<?php if ($message): ?>
    <div class="alert <?php echo $messageType === 'success' ? 'alert-success' : 'alert-error'; ?> alert-front">
        <?php echo $message; ?>
    </div>
<?php endif; ?>

<div class="form-container">
    <div class="login-section">
        <h2>Reset Password</h2>
        <?php if ($validToken): ?>
            <form method="POST" action="Reset_Password.php?token=<?php echo htmlspecialchars($token); ?>">
                <label>Enter your new password</label>
                <div class="input-group password-group">
                    <input type="password" name="new_password" id="new_password" placeholder="New Password" required>
                    <i class="fa-solid fa-eye" id="toggleNewPassword"></i>
                </div>
                <div class="input-group password-group">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                    <i class="fa-solid fa-eye" id="toggleConfirmPassword"></i>
                </div>
                <button type="submit" class="login">Reset Password</button>
            </form>
        <?php else: ?>
            <div class="no-account">
                <a href="Forgot_Password.php">Request a new reset link</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="welcome-section"></div>
</div>

<script>
    // Toggle password visibility
    function togglePasswordVisibility(inputId, toggleId) {
        const input = document.getElementById(inputId);
        const toggle = document.getElementById(toggleId);
        
        toggle.addEventListener("click", () => {
            const type = input.type === "password" ? "text" : "password";
            input.type = type;
            toggle.classList.toggle("fa-eye");
            toggle.classList.toggle("fa-eye-slash");
        });
    }
    
    togglePasswordVisibility("new_password", "toggleNewPassword");
    togglePasswordVisibility("confirm_password", "toggleConfirmPassword");
</script>

</body>
</html> 